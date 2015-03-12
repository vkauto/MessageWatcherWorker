<?php

namespace VKauto\Interfaces\MessageWatcherWorker;

use VKauto\Workers\MessageWatcherWorker\API\Message;
use VKauto\Workers\MessageWatcherWorker\API\User;
use VKauto\Workers\MessageWatcherWorker\API\Chat;

interface ModuleInterface
{
	/**
	 * [onMessage description]
	 * @return void
	 */
	public function onMessage(Message $message, User $sender);

	/**
	 * [onChatMessage description]
	 * @return void
	 */
	public function onChatMessage(Message $message, User $sender, Chat $chat);

	/**
	 * [stop description]
	 * @return bool
	 */
	public static function stop();
}
