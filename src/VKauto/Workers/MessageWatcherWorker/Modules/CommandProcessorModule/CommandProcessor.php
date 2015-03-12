<?php

namespace VKauto\Workers\MessageWatcherWorker\Modules\CommandProcessorModule;

use VKauto\Interfaces\MessageWatcherWorker\ModuleInterface;
use VKauto\Workers\MessageWatcherWorker\API\Message;
use VKauto\Workers\MessageWatcherWorker\API\User;
use VKauto\Workers\MessageWatcherWorker\API\Chat;
use VKauto\Utils\Log;
use VKauto\Auth\Account;

class CommandProcessor implements ModuleInterface
{
	private $account;

	public $commands;

	public function __construct(Account $account)
	{
		$this->account = $account;
	}

	private function isCommand(Message $message)
	{
		if (preg_match('/^!([a-zA-Zа-яёА-ЯЁ]+)\s(.*)$/', $message->text))
		{
			return true;
		}

		return false;
	}

	public function onMessage(Message $message, User $sender)
	{
		return false;
	}

	public function onChatMessage(Message $message, User $sender, Chat $chat)
	{
		if ($this->isCommand($message))
		{
			Log::write("New command! {$message->text}", ['MessageWatcher', 'CommandProcessor']);
		}
		else
		{
			return false;
		}
	}

	public static function stop()
	{
		$message = func_get_arg(0);

		if (preg_match('/^!([a-zA-Zа-яёА-ЯЁ]+)\s(.*)$/', $message->text))
		{
			return true;
		}

		return false;
	}
}
