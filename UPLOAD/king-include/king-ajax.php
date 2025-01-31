<?php
/*

	File: king-include/king-ajax.php
	Description: Front line of response to Ajax requests, routing as appropriate


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: LICENCE.html
*/

//	Output this header as early as possible

	header('Content-Type: text/plain; charset=utf-8');


//	Ensure no PHP errors are shown in the Ajax response

	@ini_set('display_errors', 0);


//	Load the KINGMEDIA base file which sets up a bunch of crucial functions

	require 'king-base.php';

	qa_report_process_stage('init_ajax');


//	Get general Ajax parameters from the POST payload, and clear $_GET

	qa_set_request(qa_post_text('qa_request'), qa_post_text('qa_root'));

	$_GET=array(); // for qa_self_html()


//	Database failure handler

	function qa_ajax_db_fail_handler()
	{
		echo "QA_AJAX_RESPONSE\n0\nA database error occurred.";
		qa_exit('error');
	}


//	Perform the appropriate Ajax operation

	$routing=array(
		'notice' => 'notice.php',
		'favorite' => 'favorite.php',
		'follow' => 'follow.php',
		'vote' => 'vote.php',
		'recalc' => 'recalc.php',
		'mailing' => 'mailing.php',
		'version' => 'version.php',
		'category' => 'category.php',
		'asktitle' => 'asktitle.php',
		'answer' => 'answer.php',
		'comment' => 'comment.php',
		'click_a' => 'click-answer.php',
		'click_c' => 'click-comment.php',
		'click_admin' => 'click-admin.php',
		'show_cs' => 'show-comments.php',
		'wallpost' => 'wallpost.php',
		'pmessage' => 'pmessage.php',
		'click_wall' => 'click-wall.php',
		'click_pm' => 'click-pm.php',
		'poll_click' => 'poll.php',
		'trivia_click' => 'trivia.php',
		'featured_click' => 'featured.php',
		'video_add' => 'video-add.php',
		'reac_click' => 'reaction.php',
		'live_search' => 'livesearch.php',
		'follow_tc' => 'followtc.php',
		'make_verify' => 'verify.php',
		'bookmark' => 'bookmark.php',
		'mdelete' => 'multipledelete.php',
		'aigenerate' => 'aigenerate.php',
		'uploadai' => 'uploadai.php',
		'prompter' => 'prompter.php',
		'aiask'	=> 'aiask.php',
	);

	$operation=qa_post_text('qa_operation');

	if (isset($routing[$operation])) {
		qa_db_connect('qa_ajax_db_fail_handler');

		require QA_INCLUDE_DIR.'king-ajax/'.$routing[$operation];

		qa_db_disconnect();
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/