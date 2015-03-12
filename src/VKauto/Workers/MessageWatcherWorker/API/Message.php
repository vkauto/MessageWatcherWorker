<?php

namespace VKauto\Workers\MessageWatcherWorker\API;

class Message
{
	/**
	 * ID сообщения
	 * @var int
	 */
	public $id;

	/**
	 * Время получения сообщения
	 * @var int
	 */
	public $date;

	/**
	 * Статус сообщения (прочиано ли оно)
	 * @var int
	 */
	public $read_state;

	/**
	 * Текст сообщения
	 * @var string
	 */
	public $text;

	public $out;

	public function __construct($id, $date, $read_state, $text, $out)
	{
		$this->id = $id;
		$this->date = $date;
		$this->read_state = $read_state;
		$this->text = $text;
		$this->out = $out;
	}
}
