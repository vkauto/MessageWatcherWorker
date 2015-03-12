<?php

namespace VKauto\Workers\MessageWatcherWorker\API;

use VKauto\Auth\Account;
use VKauto\Utils\Request;
use VKauto\Utils\QueryBuilder;

class User
{
	use \VKauto\Utils\MagicProperties;

	public function __construct($id, Account $account)
	{
		$this->data = Request::VK(QueryBuilder::buildURL('users.get', ['access_token' => $account->access_token, 'user_ids' => $id,
			'fields' => 'sex,country,city,home_town,bdate,blacklisted,photo_max,online,domain,site,status,last_seen,can_see_all_posts,can_see_audio,can_write_private_message,can_send_friend_request,is_friend,friend_status,screen_name'
		]), $account->captcha)->response[0];
	}
}
