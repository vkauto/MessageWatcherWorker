<?php

namespace VKauto\Workers\MessageWatcherWorker;

use Exception;
use ReflectionClass;
use VKauto\Interfaces\WorkerInterface;
use VKauto\Auth\Account;
use VKauto\Utils\Log;
use VKauto\Utils\Request;
use VKauto\Utils\QueryBuilder;
use VKauto\Workers\MessageWatcherWorker\API\Message;
use VKauto\Workers\MessageWatcherWorker\API\User;
use VKauto\Workers\MessageWatcherWorker\API\Chat;

use VKauto\Workers\MessageWatcherWorker\Modules\CommandProcessorModule\CommandProcessor;

class MessageWatcher implements WorkerInterface
{
	/**
	 * Состояние работы воркера
	 * @var boolean
	 */
	protected $workInProcess = false;

	/**
	 * Промежуток между запросами в секундах
	 * @var int
	 */
	public $seconds;

	/**
	 * Класс аккаунта, с которым работает воркер
	 * @var VKauto\Auth\Account
	 */
	public $account;

	/**
	 * Данные для работы с LongPoll-сервером
	 * @var array
	 */
	private $longPollServerData;

	/**
	 * Модули воркера
	 * @var array
	 */
	private $modules;

	public function __construct($seconds = 1, array $modules, Account $account)
	{
		$this->seconds = $seconds;
		$this->account = $account;

		foreach ($modules as $module => $parameters)
		{
			if (is_int($module) and !is_array($parameters))
			{
				$module = $parameters;
				$parameters = [];
			}

			if (class_exists($module))
			{
				array_push($parameters, $this->account);

				$module = (new ReflectionClass($module))->newInstanceArgs($parameters);

				if (!method_exists($module, 'onMessage') or !method_exists($module, 'onChatMessage') or !method_exists($module, 'stop'))
				{
					continue;
				}

				$this->modules[] = $module;
			}
		}

		if (is_null($this->modules))
		{
			throw new Exception("Worker can't be started without any modules!");
		}
	}

	private function getLongPollServerData()
	{
		$this->longPollServerData = Request::VK(QueryBuilder::buildURL('messages.getLongPollServer', ['v' => 5.28, 'access_token' => $this->account->access_token, 'use_ssl' => true, 'need_pts' => true]), $this->account->captcha)->response;
	}

	private function getLongPollServerURL()
	{
		return QueryBuilder::buildURL('messages.getLongPollHistory', ['v' => 5.28, 'access_token' => $this->account->access_token, 'ts' => $this->longPollServerData->ts, 'pts' => $this->longPollServerData->pts]);
	}

	private function loop()
	{
		while ($this->workInProcess)
		{
			sleep($this->seconds);
			$response = Request::VK($this->getLongPollServerURL(), $this->account->captcha)->response;
			// die(var_dump($response));
			$this->longPollServerData->pts = $response->new_pts;
			$this->messageHandler($response);
		}
	}

	private function messageHandler($response)
	{
		if ($response->messages->count == 0)
		{
			Log::write('No new messages', ['MessageWatcher']);
			return false;
		}

		foreach ($response->messages->items as $message)
		{
			$incomming_message = new Message($message->id, $message->date, $message->read_state, $message->body, $message->out);
			$sender = new User($message->user_id, $this->account);

			foreach ($this->modules as $module)
			{
				if (isset($message->chat_id))
				{
					$chat = new Chat($message->chat_id, $message->title, $message->chat_active, $message->users_count, $message->admin_id);
					$module->onChatMessage($incomming_message, $sender, $chat);
				}
				else
				{
					$module->onMessage($incomming_message, $sender);
				}

				if ($module::stop($incomming_message))
				{
					break;
				}
			}
		}
	}

	public function start()
	{
		$this->workInProcess = true;
		$this->getLongPollServerData();
		$this->loop();
	}

	public function stop()
	{
		$this->workInProcess = false;
	}

	public static function needsAccountClass()
	{
		return true;
	}
}
