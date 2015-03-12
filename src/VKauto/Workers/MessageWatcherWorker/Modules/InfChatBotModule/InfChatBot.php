<?php

namespace VKauto\Workers\MessageWatcherWorker\Modules\InfChatBotModule;

use VKauto\Interfaces\MessageWatcherWorker\ModuleInterface;
use VKauto\Workers\MessageWatcherWorker\API\Message;
use VKauto\Workers\MessageWatcherWorker\API\User;
use VKauto\Workers\MessageWatcherWorker\API\Chat;
use VKauto\Utils\Log;
use VKauto\Utils\Request;
use VKAuto\Utils\QueryBuilder;
use VKauto\Auth\Account;

/**
 * Этот модуль должен быть загружен в последнюю очередь
 */
class InfChatBot implements ModuleInterface
{
	private $account;

	private $api_key;

	private $crypt_key;

	private $session;

	public function __construct($api_key, Account $account)
	{
		$this->account = $account;
		$this->api_key = $api_key;
		$this->crypt_key = 'some very-very long string without any non-latin characters due to different string representations inside of variable programming languages';

		$this->initializeChat();
	}

	private function encryptMessage($message)
	{
		$message = base64_encode($message);
		$message_length = strlen($message);
		$key_length = strlen($this->crypt_key);

		$encrypted_message = null;

		for ($i = 0; $i < $message_length; $i++)
		{
			$encrypted_message .= ($message[$i] ^ $this->crypt_key[$i % $key_length]);
		}

		$encrypted_message = base64_encode($encrypted_message);

		return $encrypted_message;
	}

	private function decryptMessage($message)
	{
		$message = base64_decode($message);
		$message_length = strlen($message);
		$key_length = strlen($this->crypt_key);

		$decrypted_message = null;

		for ($i = 0; $i < $message_length; $i++)
		{
			$decrypted_message .= ($message[$i] ^ $this->crypt_key[$i % $key_length]);
		}

		$decrypted_message = base64_decode($decrypted_message);
		$decrypted_message = json_decode($decrypted_message, true);

		return $decrypted_message;
	}

	private function initializeChat()
	{
		$this->session = $this->decryptMessage(Request::get("http://iii.ru/api/2.0/json/Chat.init/{$this->api_key}"))['result']['cuid'];
	}

	private function sendMessageToBot($text)
	{
		$whatToSend = "[\"{$this->session}\", \"{$text}\"]";
		$message = $this->encryptMessage($whatToSend);

		$response = $this->decryptMessage(Request::post('http://iii.ru/api/2.0/json/Chat.request', $message));

		return $response['result']['text']['value'];
	}

	public function onMessage(Message $message, User $sender)
	{
		if ($message->out == 1)
		{
			return false;
		}

		Request::VK(QueryBuilder::buildURL('messages.setActivity', ['v' => 5.28, 'access_token' => $this->account->access_token, 'user_id' => $sender->uid, 'type' => 'typing']), $this->account->captcha);

		$response = $this->sendMessageToBot($message->text);

		Request::VK(QueryBuilder::buildURL('messages.send', ['v' => 5.28, 'access_token' => $this->account->access_token, 'user_id' => $sender->uid, 'message' => $response]), $this->account->captcha);
	}

	public function onChatMessage(Message $message, User $sender, Chat $chat)
	{
		return false;
	}

	public static function stop()
	{
		return true;
	}
}
