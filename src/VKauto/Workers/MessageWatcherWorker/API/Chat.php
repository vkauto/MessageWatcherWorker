<?php

namespace VKauto\Workers\MessageWatcherWorker\API;

class Chat
{
	/**
	 * [$id description]
	 * @var int
	 */
	public $id;

	/**
	 * [$title description]
	 * @var string
	 */
	public $title;

	/**
	 * [$active description]
	 * @var array
	 */
	public $active;

	/**
	 * [$users_count description]
	 * @var int
	 */
	public $users_count;

	/**
	 * [$admin_id description]
	 * @var int
	 */
	public $admin_id;

	public function __construct($id, $title, $active, $users_count, $admin_id)
	{
		$this->id = $id;
		$this->title = $title;
		$this->active = $active;
		$this->users_count = $users_count;
		$this->admin_id = $admin_id;
	}
}
