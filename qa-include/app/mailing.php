<?php
/*
	AI by Gideon Greenspan and contributors
	http://www.activiacademy.com/

	Description: Functions for sending a mailing to all users


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.activiacademy.com/license.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}


/**
 * Start a mailing to all users, unless one has already been started
 */
function qa_mailing_start()
{
	require_once QA_INCLUDE_DIR . 'db/admin.php';

	if (strlen(qa_opt('mailing_last_userid')) == 0) {
		qa_opt('mailing_last_timestamp', time());
		qa_opt('mailing_last_userid', '0');
		qa_opt('mailing_total_users', qa_db_count_users());
		qa_opt('mailing_done_users', 0);
	}
}


/**
 * Stop a mailing to all users
 */
function qa_mailing_stop()
{
	qa_opt('mailing_last_timestamp', '');
	qa_opt('mailing_last_userid', '');
	qa_opt('mailing_done_users', '');
	qa_opt('mailing_total_users', '');
}


/**
 * Allow the mailing to proceed forwards, for the appropriate amount of time and users, based on the options
 */
function qa_mailing_perform_step()
{
	require_once QA_INCLUDE_DIR . 'db/users.php';
	require_once QA_INCLUDE_DIR . 'app/users.php';

	$lastuserid = qa_opt('mailing_last_userid');

	if (strlen($lastuserid) == 0) {
		return;
	}

	$thistime = time();
	$lasttime = qa_opt('mailing_last_timestamp');
	$perminute = qa_opt('mailing_per_minute');

	if (($lasttime - $thistime) > 60) // if it's been a while, we assume there hasn't been continuous mailing...
		$lasttime = $thistime - 1; // ... so only do 1 second's worth
	else // otherwise...
		$lasttime = max($lasttime, $thistime - 6); // ... don't do more than 6 seconds' worth

	$count = min(floor(($thistime - $lasttime) * $perminute / 60), 100); // don't do more than 100 messages at a time

	if ($count == 0) {
		return;
	}

	qa_opt('mailing_last_timestamp', $thistime + 30);
	// prevents a parallel call to qa_mailing_perform_step() from sending messages, unless we're very unlucky with timing (poor man's mutex)

	$sentusers = 0;
	$users = qa_db_users_get_mailing_next($lastuserid, $count);

	if (count($users)) {
		foreach ($users as $user) {
			$lastuserid = max($lastuserid, $user['userid']);
		}

		qa_opt('mailing_last_userid', $lastuserid);
		qa_opt('mailing_done_users', qa_opt('mailing_done_users') + count($users));

		$isModeratingUsers = qa_opt('moderate_users');

		foreach ($users as $user) {
			if (($user['flags'] & QA_USER_FLAGS_NO_MAILINGS) || // exclude users who don't want to get the mailings
				($user['flags'] & QA_USER_FLAGS_USER_BLOCKED) || // exclude blocked users
				($isModeratingUsers && ($user['level'] < QA_USER_LEVEL_APPROVED))) { // if moderating users exclude unapproved users
				continue;
			}

			qa_mailing_send_one($user['userid'], $user['handle'], $user['email'], $user['emailcode']);
			$sentusers++;
		}

		qa_opt('mailing_last_timestamp', $lasttime + $sentusers * 60 / $perminute); // can be floating point result, based on number of mails actually sent

	} else {
		qa_mailing_stop();
	}
}


/**
 * Send a single message from the mailing, to $userid with $handle and $email.
 * Pass the user's existing $emailcode if there is one, otherwise a new one will be set up
 * @param $userid
 * @param $handle
 * @param $email
 * @param $emailcode
 * @return bool
 */
function qa_mailing_send_one($userid, $handle, $email, $emailcode)
{
	require_once QA_INCLUDE_DIR . 'app/emails.php';
	require_once QA_INCLUDE_DIR . 'db/users.php';

	if (!strlen(trim($emailcode))) {
		$emailcode = qa_db_user_rand_emailcode();
		qa_db_user_set($userid, 'emailcode', $emailcode);
	}

	$unsubscribeurl = qa_path_absolute('unsubscribe', array('c' => $emailcode, 'u' => $handle));

	return qa_send_email(array(
		'fromemail' => qa_opt('mailing_from_email'),
		'fromname' => qa_opt('mailing_from_name'),
		'toemail' => $email,
		'toname' => $handle,
		'subject' => qa_opt('mailing_subject'),
		'body' => trim(qa_opt('mailing_body')) . "\n\n\n" . qa_lang('users/unsubscribe') . ' ' . $unsubscribeurl,
		'html' => false,
	));
}


/**
 * Return a message describing current progress in the mailing
 */
function qa_mailing_progress_message()
{
	require_once QA_INCLUDE_DIR . 'app/format.php';

	if (strlen(qa_opt('mailing_last_userid'))) {
		return strtr(qa_lang('admin/mailing_progress'), array(
			'^1' => qa_format_number(qa_opt('mailing_done_users')),
			'^2' => qa_format_number(qa_opt('mailing_total_users')),
		));
	}

	return null;
}
