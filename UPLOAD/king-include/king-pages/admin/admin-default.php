<?php
	
	/*
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

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'king-db/admin.php';
	require_once QA_INCLUDE_DIR.'king-db/maxima.php';
	require_once QA_INCLUDE_DIR.'king-db/selects.php';
	require_once QA_INCLUDE_DIR.'king-app/options.php';
	require_once QA_INCLUDE_DIR.'king-app/admin.php';
	
	
	$adminsection=strtolower(qa_request_part(1) ?? '');
	
	
//	Get list of categories and all options
	
	$categories=qa_db_select_with_pending(qa_db_category_nav_selectspec(null, true));
	

//	See if we need to redirect

	if (empty($adminsection)) {
		$subnav=qa_admin_sub_navigation();

		if (isset($subnav[@$_COOKIE['qa_admin_last']]))
			qa_redirect($_COOKIE['qa_admin_last']);
		elseif (count($subnav)) {
			reset($subnav);
			qa_redirect(key($subnav));
		}
	}
	if ( ! qa_opt(base64_decode('a2luZ19rZXk=') ) ) {
		qa_redirect(base64_decode('YWRtaW4va2luZw=='));
	}
	

//	Check admin privileges (do late to allow one DB query)

	if (!qa_admin_check_privileges($qa_content))
		return $qa_content;


//	For non-text options, lists of option types, minima and maxima
	
	$optiontype=array(
		'ulimit' => 'number',
		'avatar_message_list_size' => 'number',
		'avatar_profile_size' => 'number',
		'avatar_q_list_size' => 'number',
		'avatar_q_page_a_size' => 'number',
		'avatar_q_page_c_size' => 'number',
		'avatar_q_page_q_size' => 'number',
		'avatar_store_size' => 'number',
		'avatar_users_size' => 'number',
		'columns_tags' => 'number',
		'columns_users' => 'number',
		'feed_number_items' => 'number',
		'flagging_hide_after' => 'number',
		'flagging_notify_every' => 'number',
		'flagging_notify_first' => 'number',
		'hot_weight_a_age' => 'number',
		'hot_weight_answers' => 'number',
		'hot_weight_q_age' => 'number',
		'hot_weight_views' => 'number',
		'hot_weight_votes' => 'number',
		'logo_height' => 'number-blank',
		'logo_width' => 'number-blank',
		'mailing_per_minute' => 'number',
		'max_len_q_title' => 'number',
		'max_num_q_tags' => 'number',
		'max_rate_ip_as' => 'number',
		'max_rate_ip_cs' => 'number',
		'max_rate_ip_flags' => 'number',
		'max_rate_ip_logins' => 'number',
		'max_rate_ip_messages' => 'number',
		'max_rate_ip_qs' => 'number',
		'max_rate_ip_registers' => 'number',
		'max_rate_ip_uploads' => 'number',
		'max_rate_ip_votes' => 'number',
		'max_rate_user_as' => 'number',
		'max_rate_user_cs' => 'number',
		'max_rate_user_flags' => 'number',
		'max_rate_user_messages' => 'number',
		'max_rate_user_qs' => 'number',
		'max_rate_user_uploads' => 'number',
		'max_rate_user_votes' => 'number',
		'min_len_a_content' => 'number',
		'min_len_c_content' => 'number',
		'credits_size' => 'number',
		'min_len_q_title' => 'number',
		'min_num_q_tags' => 'number',
		'moderate_points_limit' => 'number',
		'page_size_activity' => 'number',
		'page_size_ask_check_qs' => 'number',
		'page_size_ask_tags' => 'number',
		'page_size_home' => 'number',
		'page_size_hot_qs' => 'number',
		'page_size_pms' => 'number',
		'page_size_q_as' => 'number',
		'image_max_upload' => 'number',
		'video_max_upload' => 'number',
		'image_max_file_count' => 'number',
		'page_size_qs' => 'number',
		'page_size_related_qs' => 'number',
		'page_size_search' => 'number',
		'page_size_tag_qs' => 'number',
		'page_size_tags' => 'number',
		'page_size_una_qs' => 'number',
		'page_size_users' => 'number',
		'page_size_wall' => 'number',
		'pages_prev_next' => 'number',
		'q_urls_title_length' => 'number',
		'show_fewer_cs_count' => 'number',
		'show_fewer_cs_from' => 'number',
		'show_full_date_days' => 'number',
		'smtp_port' => 'number',
		'plan_n_1' => 'number',
		'plan_n_2' => 'number',
		'plan_n_3' => 'number',
		'plan_n_4' => 'number',
		'c_news' => 'number',
		'usercre' => 'number',
		'c_img' => 'number',
		'c_video' => 'number',
		'c_list' => 'number',
		'c_quiz' => 'number',
		'c_poll' => 'number',
		'c_msc' => 'number',
		'post_ai' => 'number',
		'post_cre' => 'number',
		'rate_number' => 'number',
		'rate_auth' => 'checkbox',
		'allow_change_usernames' => 'checkbox',
		'allow_close_questions' => 'checkbox',
		'hide_default_comment' => 'checkbox',
		'hide_fb_comment' => 'checkbox',
		'allow_login_email_only' => 'checkbox',
		'allow_multi_answers' => 'checkbox',
		'allow_private_messages' => 'checkbox',
		'allow_user_walls' => 'checkbox',
		'footer_rss' => 'checkbox',
		'allow_self_answer' => 'checkbox',
		'allow_view_q_bots' => 'checkbox',
		'approve_user_required' => 'checkbox',
		'avatar_allow_gravatar' => 'checkbox',
		'avatar_allow_upload' => 'checkbox',
		'avatar_default_show' => 'checkbox',
		'watermark_default_show' => 'checkbox',
		'captcha_on_anon_post' => 'checkbox',
		'captcha_on_feedback' => 'checkbox',
		'captcha_on_register' => 'checkbox',
		'captcha_on_reset_password' => 'checkbox',
		'captcha_on_unapproved' => 'checkbox',
		'captcha_on_unconfirmed' => 'checkbox',
		'comment_on_as' => 'checkbox',
		'comment_on_qs' => 'checkbox',
		'confirm_user_emails' => 'checkbox',
		'confirm_user_required' => 'checkbox',
		'do_ask_check_qs' => 'checkbox',
		'do_complete_tags' => 'checkbox',
		'do_count_q_views' => 'checkbox',
		'do_example_tags' => 'checkbox',
		'extra_field_active' => 'checkbox',
		'extra_field_display' => 'checkbox',
		'feed_for_activity' => 'checkbox',
		'feed_for_hot' => 'checkbox',
		'feed_for_qa' => 'checkbox',
		'feed_for_questions' => 'checkbox',
		'feed_for_search' => 'checkbox',
		'feed_for_tag_qs' => 'checkbox',
		'feed_for_unanswered' => 'checkbox',
		'feed_full_text' => 'checkbox',
		'feed_per_category' => 'checkbox',
		'feedback_enabled' => 'checkbox',
		'flagging_of_posts' => 'checkbox',
		'follow_on_as' => 'checkbox',
		'links_in_new_window' => 'checkbox',
		'video_iframe' => 'checkbox',
		'enable_music_upload' => 'checkbox',
		'logo_show' => 'checkbox',
		'night_logo_show' => 'checkbox',
		'mobile_logo' => 'checkbox',
		'mailing_enabled' => 'checkbox',
		'moderate_anon_post' => 'checkbox',
		'moderate_by_points' => 'checkbox',
		'moderate_edited_again' => 'checkbox',
		'moderate_notify_admin' => 'checkbox',
		'moderate_unapproved' => 'checkbox',
		'moderate_unconfirmed' => 'checkbox',
		'moderate_users' => 'checkbox',
		'neat_urls' => 'checkbox',
		'notify_admin_q_post' => 'checkbox',
		'notify_users_default' => 'checkbox',
		'q_urls_remove_accents' => 'checkbox',
		'register_notify_admin' => 'checkbox',
		'show_c_reply_buttons' => 'checkbox',
		'disable_image' => 'checkbox',
		'disable_video' => 'checkbox',
		'disable_news' => 'checkbox',
		'disable_poll' => 'checkbox',
		'disable_list' => 'checkbox',
		'disable_trivia' => 'checkbox',
		'disable_music' => 'checkbox',
		'enable_amp' => 'checkbox',
		'enable_pposts' => 'checkbox',
		'enable_aws' => 'checkbox',
		'enable_wasabi' => 'checkbox',
		'enable_nsfw' => 'checkbox',
		'show_custom_answer' => 'checkbox',
		'show_custom_ask' => 'checkbox',
		'show_custom_comment' => 'checkbox',
		'show_custom_footer' => 'checkbox',
		'show_custom_header' => 'checkbox',
		'show_custom_home' => 'checkbox',
		'show_custom_in_head' => 'checkbox',
		'show_custom_register' => 'checkbox',
		'show_custom_sidebar' => 'checkbox',
		'show_custom_sidepanel' => 'checkbox',
		'show_custom_welcome' => 'checkbox',
		'show_home_description' => 'checkbox',
		'king_analytic' => 'checkbox',
		'show_ad_post_below' => 'checkbox',
		'show_message_history' => 'checkbox',
		'show_gdpr' => 'checkbox',
		'enable_bookmark' => 'checkbox',
		'show_notice_visitor' => 'checkbox',
		'show_notice_welcome' => 'checkbox',
		'show_register_terms' => 'checkbox',
		'show_selected_first' => 'checkbox',
		'show_url_links' => 'checkbox',
		'show_user_points' => 'checkbox',
		'show_user_titles' => 'checkbox',
		'show_view_counts' => 'checkbox',
		'show_view_count_q_page' => 'checkbox',
		'show_when_created' => 'checkbox',
		'site_maintenance' => 'checkbox',
		'smtp_active' => 'checkbox',
		'smtp_authenticate' => 'checkbox',
		'suspend_register_users' => 'checkbox',
		'tag_separator_comma' => 'checkbox',
		'votes_separated' => 'checkbox',
		'voting_on_as' => 'checkbox',
		'voting_on_q_page_only' => 'checkbox',
		'voting_on_qs' => 'checkbox',
		'logo_url_box' => 'custom',
		'night_logo_url_box' => 'custom',
		'mobile_logo_url_box' => 'custom',
		'mobile_nlogo_url_box' => 'custom',
		'login_back_url' => 'custom',
		'login_logo_url' => 'custom',
		'smtp_password' => 'password',
		'enable_membership' => 'checkbox',
		'enable_credits' => 'checkbox',
		'enable_homepagelogin' => 'checkbox',
		'enable_m_msg' => 'checkbox',
		'plan_1' => 'checkbox',
		'plan_2' => 'checkbox',
		'plan_3' => 'checkbox',
		'plan_4' => 'checkbox',
		'enable_stripe' => 'checkbox',
		'enable_paypal' => 'checkbox',
		'paypal_sandbox' => 'checkbox',
		'plan_usd_1' => 'number',
		'plan_usd_2' => 'number',
		'plan_usd_3' => 'number',
		'plan_usd_4' => 'number',
		'desc_color' => 'color',
		'king_leo_enable' => 'checkbox',
		'enable_askai' => 'checkbox',
		'enable_sd' => 'checkbox',
		'sdnsfw' => 'checkbox',
		'enable_pro' => 'checkbox',
		'enable_odalle' => 'checkbox',
		'enable_pix' => 'checkbox',
		'enable_sdn' => 'checkbox',
		'enable_playg' => 'checkbox',
		'enable_realxl' => 'checkbox',
		'ennsfw' => 'checkbox',
		'eprompter' => 'checkbox',
		'oaprompter' => 'checkbox',
		'king_sd_steps' => 'number',
		'enable_dalle' => 'checkbox',
		'enable_de3' => 'checkbox',
		'kingai_imgn' => 'number',
		'enprompt' => 'checkbox',
		'eidown' => 'checkbox',
		'ulimits' => 'checkbox',
		'ailimits' => 'checkbox',
		'enable_adfree_mode' => 'checkbox',
		'adfree_pre' => 'checkbox',
		'adfree_verify' => 'checkbox',
		'adfree_logged' => 'checkbox',
		'adfree_points' => 'checkbox',
		'v_color' => 'color',
		'n_color' => 'color',
		'i_color' => 'color',
		'p_color' => 'color',
		'l_color' => 'color',
		'q_color' => 'color',
		'm_color' => 'color',
		'hide_trange' => 'checkbox',

	);
	
	$optionmaximum=array(
		'feed_number_items' => QA_DB_RETRIEVE_QS_AS,
		'max_len_q_title' => QA_DB_MAX_TITLE_LENGTH,
		'page_size_activity' => QA_DB_RETRIEVE_QS_AS,
		'page_size_ask_check_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_ask_tags' => QA_DB_RETRIEVE_QS_AS,
		'page_size_home' => QA_DB_RETRIEVE_QS_AS,
		'page_size_hot_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_pms' => QA_DB_RETRIEVE_MESSAGES,
		'page_size_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_related_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_search' => QA_DB_RETRIEVE_QS_AS,
		'page_size_tag_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_tags' => QA_DB_RETRIEVE_TAGS,
		'page_size_una_qs' => QA_DB_RETRIEVE_QS_AS,
		'page_size_users' => QA_DB_RETRIEVE_USERS,
		'page_size_wall' => QA_DB_RETRIEVE_MESSAGES,
	);

	$optionminimum=array(
		'flagging_hide_after' => 2,
		'flagging_notify_every' => 1,
		'flagging_notify_first' => 1,
		'max_num_q_tags' => 2,
		'max_rate_ip_logins' => 1,
		'page_size_activity' => 1,
		'page_size_ask_check_qs' => 3,
		'page_size_ask_tags' => 3,
		'page_size_home' => 1,
		'page_size_hot_qs' => 1,
		'page_size_pms' => 1,
		'image_max_upload' => 1,
		'video_max_upload' => 1,
		'image_max_file_count' => 1,
		'page_size_q_as' => 1,
		'page_size_qs' => 1,
		'page_size_search' => 1,
		'page_size_tag_qs' => 1,
		'page_size_tags' => 1,
		'page_size_users' => 1,
		'page_size_wall' => 1,
	);
	

//	Define the options to show (and some other visual stuff) based on request
	
	$formstyle='tall';
	$checkboxtodisplay=null;
	$subsubnav = '';
	$desc = '';
	$maxpermitpost=max(qa_opt('permit_post_q'), qa_opt('permit_post_a'));
	if (qa_opt('comment_on_qs') || qa_opt('comment_on_as'))
		$maxpermitpost=max($maxpermitpost, qa_opt('permit_post_c'));
			
	switch ($adminsection) {
		case 'general':
			$subtitle='admin/general_title';
			$showoptions=array('site_title', 'site_url', 'neat_urls', 'site_language', 'site_theme', 'site_theme_mobile', 'tags_or_categories', 'site_maintenance');
			break;
		case 'ai':
			$subtitle='admin/ai';
			$showoptions=array(
				'enable_askai', 'select_kingask', 'select_mask', 'king_leo_enable', 'king_sd_api', 'enable_sdn', 'enable_sd', 'sdnsfw', 'enable_pro', 'enable_odalle', 'enable_pix',
					 'enable_playg', 'enable_realxl', 'ennsfw', 'eprompter', 'oaprompter', 'king_sd_steps', '', 'enable_dalle', 'enable_de3', 'king_leo_api', '', 'kingai_imgn', 'enprompt', 'eidown', 'ulimits', 'ulimit', 'ailimits', 'plan_1_lmt', 'plan_2_lmt', 'plan_3_lmt', 'plan_4_lmt'
			);
			$checkboxtodisplay=array(

				'king_leo_api' => 'option_enable_dalle || option_enable_de3 && option_king_leo_enable || option_enable_askai',
				'oaprompter' =>  'option_eprompter && option_king_leo_enable',
				'sdnsfw' => 'option_king_leo_enable',
				'ennsfw' => 'option_king_leo_enable',
				'enable_dalle' => 'option_king_leo_enable',
				'enable_de3' => 'option_king_leo_enable',
				'eprompter' => 'option_king_leo_enable',
				'king_sd_api'  => 'option_king_leo_enable || option_enable_askai',
				'king_sd_steps'  => 'option_king_leo_enable',
				'enprompt' => 'option_king_leo_enable',
				'kingai_imgn'  => 'option_king_leo_enable',
				'kingh_title'  => 'option_king_leo_enable',
				'kingh_desc'   => 'option_king_leo_enable',
				'hsubmit'   => 'option_king_leo_enable',
				'eidown' => 'option_king_leo_enable',
				'aprvt' => 'option_king_leo_enable',
				'aupload' => 'option_king_leo_enable',
				'ulimits' => 'option_king_leo_enable',
				'ulimit' => 'option_ulimits && option_king_leo_enable',
				'ailimits' => 'option_king_leo_enable',
				'plan_1_lmt' => 'option_ailimits && option_king_leo_enable',
				'plan_2_lmt' => 'option_ailimits && option_king_leo_enable',
				'plan_3_lmt' => 'option_ailimits && option_king_leo_enable',
				'plan_4_lmt' => 'option_ailimits && option_king_leo_enable',
				'enable_sd'  => 'option_king_leo_enable',
				'enable_pro' => 'option_king_leo_enable',
				'enable_odalle'  => 'option_king_leo_enable',
				'enable_pix'  => 'option_king_leo_enable',
				'enable_sdn'  => 'option_king_leo_enable',
				'enable_playg'  => 'option_king_leo_enable',
				'enable_realxl'  => 'option_king_leo_enable',
				'select_kingask'  => 'option_enable_askai',
				'select_mask' => 'option_enable_askai'
			);
			break;			
		case 'emails':
			$subtitle='admin/emails_title';
			$showoptions=array(
				'from_email', 'feedback_email', 'notify_admin_q_post', 'feedback_enabled', 'email_privacy',
				 'smtp_active', 'smtp_address', 'smtp_port', 'smtp_secure', 'smtp_authenticate', 'smtp_username', 'smtp_password'
			);
			
			$checkboxtodisplay=array(
				'smtp_address' => 'option_smtp_active',
				'smtp_port' => 'option_smtp_active',
				'smtp_secure' => 'option_smtp_active',
				'smtp_authenticate' => 'option_smtp_active',
				'smtp_username' => 'option_smtp_active && option_smtp_authenticate',
				'smtp_password' => 'option_smtp_active && option_smtp_authenticate',
			);
			break;
			
		case 'users':
			$subtitle='admin/users_title';

			$showoptions=array('enable_bookmark', '', 'show_notice_visitor', 'notice_visitor');

			if (!QA_FINAL_EXTERNAL_USERS) {
				require_once QA_INCLUDE_DIR.'king-util/image.php';
				
				array_push($showoptions, 'show_gdpr', 'gdpr_box','show_custom_register', 'custom_register', 'show_register_terms', 'register_terms', 'show_notice_welcome', 'notice_welcome', 'show_custom_welcome', 'custom_welcome', 'allow_login_email_only');
			
				array_push($showoptions, 'allow_user_walls', 'allow_private_messages', 'show_message_history', 'page_size_pms', 'page_size_wall', '');
				
				if (qa_has_gd_image())
					array_push($showoptions, 'avatar_allow_upload', 'avatar_store_size', 'avatar_default_show');
			}
					
			
			if (!QA_FINAL_EXTERNAL_USERS)
				
			
			
			
			$checkboxtodisplay=array(
				'custom_register' => 'option_show_custom_register',
				'register_terms' => 'option_show_register_terms',
				'custom_welcome' => 'option_show_custom_welcome',
				'notice_welcome' => 'option_show_notice_welcome',
				'notice_visitor' => 'option_show_notice_visitor',
				'gdpr_box' => 'option_show_gdpr',
				'show_message_history' => 'option_allow_private_messages',
				'avatar_store_size' => 'option_avatar_allow_upload',
				'avatar_default_show' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
			);
			
			if (!QA_FINAL_EXTERNAL_USERS)
				$checkboxtodisplay=array_merge($checkboxtodisplay, array(
					'page_size_pms' => 'option_allow_private_messages && option_show_message_history',
					'page_size_wall' => 'option_allow_user_walls',
					'avatar_profile_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_users_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_q_page_q_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_q_page_a_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_q_page_c_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_q_list_size' => 'option_avatar_allow_gravatar || option_avatar_allow_upload',
					'avatar_message_list_size' => 'option_allow_private_messages || option_allow_user_walls',
				));
	
			$formstyle='wide';
			break;
			
		case 'layout':
			$subtitle='admin/layout_title';
			$showoptions=array('logo_show', 'logo_url_box', 'night_logo_show', 'night_logo_url_box', 'mobile_logo', 'mobile_logo_url_box', 'mobile_nlogo_url_box', '', 'hide_trange','hide_default_comment', 'hide_fb_comment', '', 'show_custom_sidebar', 'custom_sidebar', 'show_custom_header', 'custom_header', 'show_custom_footer', 'custom_footer', 'show_custom_in_head', 'custom_in_head', 'show_home_description', 'home_description', 'king_analytic', 'king_analytic_box', 'show_ad_post_below', 'ad_post_below', '', 'king_grids', 'king_grid_size', '', 'footer_fb', 'footer_twi', 'footer_google', 'footer_ytube', 'footer_pin', 'footer_rss');
			
			$checkboxtodisplay=array(
				'logo_url_box' => 'option_logo_show',
				'night_logo_url_box' => 'option_night_logo_show',
				'custom_sidebar' => 'option_show_custom_sidebar',
				'custom_header' => 'option_show_custom_header',
				'custom_footer' => 'option_show_custom_footer',
				'custom_in_head' => 'option_show_custom_in_head',
				'home_description' => 'option_show_home_description',
				'king_analytic_box' => 'option_king_analytic',
				'ad_post_below' => 'option_show_ad_post_below',
				'mobile_logo_url_box' => 'option_mobile_logo',
				'mobile_nlogo_url_box' => 'option_mobile_logo',
			);
			break;

		case 'widgets':
			$subtitle='misc/layout_widgets';
			$showoptions=array();
			
			$checkboxtodisplay=array();
			break;
		case 'membership':
			$subtitle='misc/layout_membership';
			$showoptions=array('enable_membership', 'currency', 'enable_stripe', 'stripe_pkey', 'stripe_skey', 'webhook_key', 'enable_paypal', 'paypal_email', 'paypal_sandbox', '', 'plan_1', 'plan_n_1', 'plan_t_1', 'plan_usd_1', 'plan_1_title', 'plan_1_desc', '', 'plan_2', 'plan_n_2', 'plan_t_2', 'plan_usd_2', 'plan_2_title', 'plan_2_desc', '', 'plan_3', 'plan_n_3', 'plan_t_3', 'plan_usd_3', 'plan_3_title', 'plan_3_desc', '', 'plan_4', 'plan_n_4', 'plan_t_4', 'plan_usd_4', 'plan_4_title', 'plan_4_desc', 'enable_m_msg', 'membership_msg');
			
			$checkboxtodisplay=array(
				'currency' => 'option_enable_membership',
				'enable_stripe' => 'option_enable_membership',
				'enable_paypal' => 'option_enable_membership',
				'paypal_sandbox' => 'option_enable_paypal && option_enable_membership',
				'stripe_pkey' => 'option_enable_stripe && option_enable_membership',
				'stripe_skey' => 'option_enable_stripe && option_enable_membership',
				'webhook_key' => 'option_enable_stripe && option_enable_membership',
				'paypal_email' => 'option_enable_paypal && option_enable_membership',
				'membership_msg' => 'option_enable_m_msg && option_enable_membership',
				'enable_m_msg' => 'option_enable_membership',
			);
			$args = array('1','2','3','4');
			foreach ($args as $arg) {
				$checkboxtodisplay['plan_'.$arg]='option_enable_membership';
				$checkboxtodisplay['plan_'.$arg.'_desc']='option_plan_'.$arg.' && option_enable_membership';
				$checkboxtodisplay['plan_usd_'.$arg]='option_plan_'.$arg.' && option_enable_membership';
				$checkboxtodisplay['plan_'.$arg.'_title']='option_plan_'.$arg.' && option_enable_membership';
				$checkboxtodisplay['plan_n_'.$arg]='option_plan_'.$arg.' && option_enable_membership';
				$checkboxtodisplay['plan_t_'.$arg]='option_plan_'.$arg.' && option_enable_membership';
			}
			$subsubnav='mem';
			break;
			case 'credits':
				$subtitle='misc/credits';
				$showoptions=array('enable_credits', 'credits_size', 'currency', 'enable_stripe', 'stripe_pkey', 'stripe_skey', 'webhook_key', 'enable_paypal', 'paypal_email', 'paypal_sandbox', 'enable_m_msg', 'membership_msg', 'membership_msg');
				
				$checkboxtodisplay=array(
					'currency' => 'option_enable_credits',
					'credits_size' => 'option_enable_credits',
					'enable_stripe' => 'option_enable_credits',
					'enable_paypal' => 'option_enable_credits',
					'paypal_sandbox' => 'option_enable_paypal && option_enable_credits',
					'stripe_pkey' => 'option_enable_stripe && option_enable_credits',
					'stripe_skey' => 'option_enable_stripe && option_enable_credits',
					'webhook_key' => 'option_enable_stripe && option_enable_credits',
					'paypal_email' => 'option_enable_paypal && option_enable_credits',
					'membership_msg' => 'option_enable_m_msg && option_enable_credits',

				);
				$subsubnav='mem';
				
			break;
			case 'creditsopt':
				$subtitle='misc/credits';
				
				$showoptions=array('usercre', '', 'c_news', 'c_img', 'c_video', 'c_list', 'c_quiz', 'c_poll', 'c_msc','', 'post_ai', 'post_cre', 'rate_auth', 'rate_number');
				$checkboxtodisplay=array(
					'rate_number' => 'option_rate_auth',
				);
				$desc = qa_lang('options/desc');
				$subsubnav='mem';
				$formstyle='wide';

			break;
			case 'homepage':
				$subtitle='misc/homepage';
				$showoptions=array('enable_homepagelogin', 'login_back_url', 'login_logo_url', 'hlogin_desc', 'desc_color', );
				
				$checkboxtodisplay=array(
					'hlogin_desc' => 'option_enable_homepagelogin',
					'login_back_url' => 'option_enable_homepagelogin',
					'login_logo_url' => 'option_enable_homepagelogin',
					'desc_color' => 'option_enable_homepagelogin',
					
				);

				$subsubnav='mem';
				break;
			case 'adfree':
				$subtitle='misc/adfree';
				$showoptions=array('enable_adfree_mode', 'adfree_pre', 'adfree_verify', 'adfree_logged', 'adfree_points', 'adfree_p');
				$checkboxtodisplay=array(
					'adfree_p' => 'option_adfree_points && option_enable_adfree_mode',
					'adfree_pre' => 'option_enable_adfree_mode',
					'adfree_verify' => 'option_enable_adfree_mode',
					'adfree_logged' => 'option_enable_adfree_mode',
					'adfree_points' => 'option_enable_adfree_mode',
				);
				$subsubnav='mem';
				break;
		case 'viewing':
			$subtitle='admin/viewing_title';
			$showoptions=array();
			
			array_push($showoptions, 'disable_image', 'disable_video', 'disable_news', 'disable_poll', 'disable_list', 'disable_trivia', 'disable_music', '', 'i_color', 'v_color', 'n_color', 'p_color', 'l_color', 'q_color', 'm_color', '', 'enable_amp', '', 'enable_pposts', '', 'enable_aws', 'aws_key', 'aws_secret', 'aws_bucket', 'aws_region', 'enable_wasabi', 'wasabi_key', 'wasabi_secret', 'wasabi_bucket', 'wasabi_region', '', 'links_in_new_window', 'video_iframe', 'enable_music_upload', 'enable_nsfw', 'image_max_upload', 'image_max_file_count', 'video_max_upload', 'video_ffmpeg', 'fb_user_token', 'watermark_default_show', 'watermark_position', '', 'sort_answers_by', 'page_size_q_as');
			
			if (qa_opt('comment_on_qs') || qa_opt('comment_on_as'))
				array_push($showoptions, 'show_fewer_cs_from', 'show_fewer_cs_count', 'show_c_reply_buttons');
				
			$showoptins[]='';
			
			$widgets=qa_db_single_select(qa_db_widgets_selectspec());
			
			foreach ($widgets as $widget)
				if ($widget['title']=='Related Posts') {
					array_push($showoptions, 'match_related_qs', 'page_size_related_qs', '');
					break;
				}
			
			$showoptions[]='pages_prev_next';

			$formstyle='wide';

			$checkboxtodisplay=array(
				'aws_key' => 'option_enable_aws',
				'aws_secret' => 'option_enable_aws',
				'aws_bucket' => 'option_enable_aws',
				'aws_region' => 'option_enable_aws',
				'wasabi_key' => 'option_enable_wasabi',
				'wasabi_secret' => 'option_enable_wasabi',
				'wasabi_bucket' => 'option_enable_wasabi',
				'wasabi_region' => 'option_enable_wasabi',
				'watermark_position' => 'option_watermark_default_show',
				'show_view_counts' => 'option_do_count_q_views',
				'show_view_count_q_page' => 'option_do_count_q_views',
				'votes_separated' => 'option_voting_on_qs || option_voting_on_as',
				'voting_on_q_page_only' => 'option_voting_on_qs',
				'show_full_date_days' => 'option_show_when_created',
				'v_color' => '!option_disable_video',
				'n_color' => '!option_disable_news',
				'i_color' => '!option_disable_image',
				'p_color' => '!option_disable_poll',
				'l_color' => '!option_disable_list',
				'q_color' => '!option_disable_trivia',
				'm_color' => '!option_disable_music',
			);
			break;
			
		case 'lists':
			$subtitle='admin/lists_title';
			
			$getoptions=qa_get_options(array('tags_or_categories'));
			
			$showoptions=array('show_custom_ask', 'custom_ask', 'show_custom_answer', 'custom_answer', '', 'page_size_qs');
						
			$showoptions[]='';		

			if (qa_using_tags())
				array_push($showoptions, 'page_size_tags');			

			array_push($showoptions, 'page_size_users', '');
				
			$formstyle='wide';
			
			if (count(qa_list_modules('editor'))>1) {
				array_push($showoptions, 'editor_for_qs', 'editor_for_as', 'editor_for_cs', '');
			}
				
			
			
			array_push($showoptions, 'min_len_q_title', 'max_len_q_title');
			
			if (qa_using_tags()) {
				array_push($showoptions, 'min_num_q_tags', 'max_num_q_tags', 'tag_separator_comma');
			}
			
			array_push($showoptions, 'min_len_a_content', 'notify_users_default');
			
			array_push($showoptions, '', 'block_bad_words', '', 'do_ask_check_qs', 'match_ask_check_qs', 'page_size_ask_check_qs', '');

			if (qa_using_tags()) {
				array_push($showoptions, 'do_example_tags', 'match_example_tags', 'do_complete_tags', 'page_size_ask_tags');
			}

			$formstyle='wide';

			$checkboxtodisplay=array(
				'editor_for_cs' => 'option_comment_on_qs || option_comment_on_as',
				'custom_ask' => 'option_show_custom_ask',
				'extra_field_prompt' => 'option_extra_field_active',
				'extra_field_display' => 'option_extra_field_active',
				'extra_field_label' => 'option_extra_field_active && option_extra_field_display',
				'extra_field_label_hidden' => '!option_extra_field_display',
				'extra_field_label_shown' => 'option_extra_field_display',
				'custom_answer' => 'option_show_custom_answer',
				'show_custom_comment' => 'option_comment_on_qs || option_comment_on_as',
				'custom_comment' => 'option_show_custom_comment && (option_comment_on_qs || option_comment_on_as)',
				'min_len_c_content' => 'option_comment_on_qs || option_comment_on_as',
				'match_ask_check_qs' => 'option_do_ask_check_qs',
				'page_size_ask_check_qs' => 'option_do_ask_check_qs',
				'match_example_tags' => 'option_do_example_tags',
				'page_size_ask_tags' => 'option_do_example_tags || option_do_complete_tags',
			);
			
			break;
		
			
		case 'permissions':
			$subtitle='admin/permissions_title';
			
			$permitoptions=qa_get_permit_options();
			
			$showoptions=array();
			$checkboxtodisplay=array();
			
			foreach ($permitoptions as $permitoption) {
				$showoptions[]=$permitoption;
				
				if ($permitoption=='permit_view_q_page') {
					$showoptions[]='allow_view_q_bots';
					$checkboxtodisplay['allow_view_q_bots']='option_permit_view_q_page<'.qa_js(QA_PERMIT_ALL);
				
				} else {
					$showoptions[]=$permitoption.'_points';
					$checkboxtodisplay[$permitoption.'_points']='(option_'.$permitoption.'=='.qa_js(QA_PERMIT_POINTS).
						')||(option_'.$permitoption.'=='.qa_js(QA_PERMIT_POINTS_CONFIRMED).')||(option_'.$permitoption.'=='.qa_js(QA_PERMIT_APPROVED_POINTS).')';
				}
				$showoptions[]=$permitoption.'_plan';
				$checkboxtodisplay[$permitoption.'_plan']='(option_'.$permitoption.'=='.qa_js(QA_PERMIT_MEMBERSHIP).')';
			}
			
			$formstyle='wide';
			break;
		
		case 'feeds':
			$subtitle='admin/feeds_title';
			
			$showoptions=array('feed_for_questions', 'feed_for_qa', 'feed_for_activity');
			
			array_push($showoptions, 'feed_for_hot', 'feed_for_unanswered');
			
			if (qa_using_tags())
				$showoptions[]='feed_for_tag_qs';
				
			if (qa_using_categories())
				$showoptions[]='feed_per_category';
			
			array_push($showoptions, 'feed_for_search', '', 'feed_number_items', 'feed_full_text');
							
			$formstyle='wide';

			$checkboxtodisplay=array(
				'feed_per_category' => 'option_feed_for_qa || option_feed_for_questions || option_feed_for_unanswered || option_feed_for_activity',
			);
			break;
		
		case 'spam':
			$subtitle='admin/spam_title';
			
			$showoptions=array();
			
			$getoptions=qa_get_options(array('feedback_enabled', 'permit_post_q', 'permit_post_a', 'permit_post_c'));
			
			if (!QA_FINAL_EXTERNAL_USERS)
				array_push($showoptions, 'confirm_user_emails', 'confirm_user_required', 'moderate_users', 'approve_user_required', 'register_notify_admin', 'suspend_register_users', '');
			
			$captchamodules=qa_list_modules('captcha');
			
			if (count($captchamodules)) {
				if (!QA_FINAL_EXTERNAL_USERS)
					array_push($showoptions, 'captcha_on_register', 'captcha_on_reset_password');
				
				if ($maxpermitpost > QA_PERMIT_USERS)
					$showoptions[]='captcha_on_anon_post';
					
				if ($maxpermitpost > QA_PERMIT_APPROVED)
					$showoptions[]='captcha_on_unapproved';
					
				if ($maxpermitpost > QA_PERMIT_CONFIRMED)
					$showoptions[]='captcha_on_unconfirmed';
					
				if ($getoptions['feedback_enabled'])
					$showoptions[]='captcha_on_feedback';
					
				$showoptions[]='captcha_module';
			}
			
			$showoptions[]='';
				
			if ($maxpermitpost > QA_PERMIT_USERS)
				$showoptions[]='moderate_anon_post';
				
			if ($maxpermitpost > QA_PERMIT_APPROVED)
				$showoptions[]='moderate_unapproved';
			
			if ($maxpermitpost > QA_PERMIT_CONFIRMED)
				$showoptions[]='moderate_unconfirmed';
				
			if ($maxpermitpost > QA_PERMIT_EXPERTS)
				array_push($showoptions, 'moderate_by_points', 'moderate_points_limit', 'moderate_edited_again', 'moderate_notify_admin', 'moderate_update_time', '');
			
			array_push($showoptions, 'flagging_of_posts', 'flagging_notify_first', 'flagging_notify_every', 'flagging_hide_after', '');
			
			array_push($showoptions, 'block_ips_write', '');

			if (!QA_FINAL_EXTERNAL_USERS)
				array_push($showoptions, 'max_rate_ip_registers', 'max_rate_ip_logins', '');
			
			array_push($showoptions, 'max_rate_ip_qs', 'max_rate_user_qs', 'max_rate_ip_as', 'max_rate_user_as');

			if (qa_opt('comment_on_qs') || qa_opt('comment_on_as'))
				array_push($showoptions, 'max_rate_ip_cs', 'max_rate_user_cs');
			
			$showoptions[]='';
			
			if (qa_opt('voting_on_qs') || qa_opt('voting_on_as'))
				array_push($showoptions, 'max_rate_ip_votes', 'max_rate_user_votes');

			array_push($showoptions, 'max_rate_ip_flags', 'max_rate_user_flags', 'max_rate_ip_uploads', 'max_rate_user_uploads');
			
			if (qa_opt('allow_private_messages') || qa_opt('allow_user_walls'))
				array_push($showoptions, 'max_rate_ip_messages', 'max_rate_user_messages');
			
			$formstyle='wide';

			$checkboxtodisplay=array(
				'confirm_user_required' => 'option_confirm_user_emails',
				'approve_user_required' => 'option_moderate_users',
				'captcha_on_unapproved' => 'option_moderate_users',
				'captcha_on_unconfirmed' => 'option_confirm_user_emails && !(option_moderate_users && option_captcha_on_unapproved)',
				'captcha_module' => 'option_captcha_on_register || option_captcha_on_anon_post || (option_confirm_user_emails && option_captcha_on_unconfirmed) || (option_moderate_users && option_captcha_on_unapproved) || option_captcha_on_reset_password || option_captcha_on_feedback',
				'moderate_unapproved' => 'option_moderate_users',
				'moderate_unconfirmed' => 'option_confirm_user_emails && !(option_moderate_users && option_moderate_unapproved)',
				'moderate_points_limit' => 'option_moderate_by_points',
				'moderate_points_label_off' => '!option_moderate_by_points',
				'moderate_points_label_on' => 'option_moderate_by_points',
				'moderate_edited_again' => 'option_moderate_anon_post || (option_confirm_user_emails && option_moderate_unconfirmed) || (option_moderate_users && option_moderate_unapproved) || option_moderate_by_points',
				'flagging_hide_after' => 'option_flagging_of_posts',
				'flagging_notify_every' => 'option_flagging_of_posts',
				'flagging_notify_first' => 'option_flagging_of_posts',
				'max_rate_ip_flags' =>  'option_flagging_of_posts',
				'max_rate_user_flags' => 'option_flagging_of_posts',
			);
			
			$checkboxtodisplay['moderate_notify_admin']=$checkboxtodisplay['moderate_edited_again'];
			$checkboxtodisplay['moderate_update_time']=$checkboxtodisplay['moderate_edited_again'];
			break;
		
		case 'mailing':
			require_once QA_INCLUDE_DIR.'king-app/mailing.php';
			
			$subtitle='admin/mailing_title';

			$showoptions=array('mailing_enabled', 'mailing_from_name', 'mailing_from_email', 'mailing_subject', 'mailing_body', 'mailing_per_minute');
			break;
		
		default:
			$pagemodules=qa_load_modules_with('page', 'match_request');
			$request=qa_request();
			
			foreach ($pagemodules as $pagemodule)
				if ($pagemodule->match_request($request))
					return $pagemodule->process_request($request);

			return include QA_INCLUDE_DIR.'king-page-not-found.php';
			break;
	}
	

//	Filter out blanks to get list of valid options
	
	$getoptions=array();
	foreach ($showoptions as $optionname)
		if (strlen((string)$optionname) && (strpos($optionname, '/')===false)) // empties represent spacers in forms
			$getoptions[]=$optionname;


//	Process user actions
	
	$errors=array();

	$recalchotness=false;
	$startmailing=false;
	$securityexpired=false;
	
	$formokhtml=null;
	
	if (qa_clicked('doresetoptions')) {
		if (!qa_check_form_security_code('admin/'.$adminsection, qa_post_text('code')))
			$securityexpired=true;

		else {
			qa_reset_options($getoptions);
			$formokhtml=qa_lang_html('admin/options_reset');
		}

	} elseif (qa_clicked('dosaveoptions')) {
		if (!qa_check_form_security_code('admin/'.$adminsection, qa_post_text('code')))
			$securityexpired=true;
		
		else {
			foreach ($getoptions as $optionname) {
				$optionvalue=qa_post_text('option_'.$optionname);
				
				if (
					(@$optiontype[$optionname]=='number') ||
					(@$optiontype[$optionname]=='checkbox') ||
					((@$optiontype[$optionname]=='number-blank') && strlen((string)$optionvalue))
				)
					$optionvalue=(int)$optionvalue;
					
				if (isset($optionmaximum[$optionname]))
					$optionvalue=min($optionmaximum[$optionname], $optionvalue);
	
				if (isset($optionminimum[$optionname]))
					$optionvalue=max($optionminimum[$optionname], $optionvalue);
					
				switch ($optionname) {
					case 'site_url':
						if (substr($optionvalue, -1)!='/') // seems to be a very common mistake and will mess up URLs
							$optionvalue.='/';
						break;
					
					case 'hot_weight_views':
					case 'hot_weight_answers':
					case 'hot_weight_votes':
					case 'hot_weight_q_age':
					case 'hot_weight_a_age':
						if (qa_opt($optionname) != $optionvalue)
							$recalchotness=true;
						break;
						
					case 'block_ips_write':
						require_once QA_INCLUDE_DIR.'king-app/limits.php';
						$optionvalue=implode(' , ', qa_block_ips_explode($optionvalue));
						break;
						
					case 'block_bad_words':
						require_once QA_INCLUDE_DIR.'king-util/string.php';
						$optionvalue=implode(' , ', qa_block_words_explode($optionvalue));
						break;
				}
							
				qa_set_option($optionname, $optionvalue);
			}
			
			$formokhtml=qa_lang_html('admin/options_saved');
	
		//	Uploading default avatar
	
			if (is_array(@$_FILES['avatar_default_file']) && $_FILES['avatar_default_file']['size']) {
				require_once QA_INCLUDE_DIR.'king-util/image.php';
				
				$oldblobid=qa_opt('avatar_default_blobid');
				
				$toobig=qa_image_file_too_big($_FILES['avatar_default_file']['tmp_name'], qa_opt('avatar_store_size'));
				
				if ($toobig)
					$errors['avatar_default_show']=qa_lang_sub('main/image_too_big_x_pc', (int)($toobig*100));
				
				else {
					$imagedata=qa_image_constrain_data(file_get_contents($_FILES['avatar_default_file']['tmp_name']), $width, $height, qa_opt('avatar_store_size'));
					
					if (isset($imagedata)) {
						require_once QA_INCLUDE_DIR.'king-app/blobs.php';
						
						$newblobid=qa_create_blob($imagedata, 'jpeg');
						
						if (isset($newblobid)) {
							qa_set_option('avatar_default_blobid', $newblobid);
							qa_set_option('avatar_default_width', $width);
							qa_set_option('avatar_default_height', $height);
							qa_set_option('avatar_default_show', 1);
						}
							
						if (strlen((string)$oldblobid))
							qa_delete_blob($oldblobid);
		
					} else
						$errors['avatar_default_show']=qa_lang_sub('main/image_not_read', implode(', ', qa_gd_image_formats()));
				}
			}
			if (is_array(@$_FILES['watermark_default_file']) && $_FILES['watermark_default_file']['size']) {

				$ImageType = $_FILES['watermark_default_file']['type'];
				$TempSrc   = $_FILES['watermark_default_file']['tmp_name'];
				switch (strtolower($ImageType)) {
					case 'image/png':
						$CreatedImage = imagecreatefrompng($TempSrc);
						break;
					default:
						die('Unsupported File!');
				}
				$directory = QA_INCLUDE_DIR . 'watermark/';
				$NewImageName = 'watermark.png';
				$path = $directory . $NewImageName;
				move_uploaded_file($TempSrc, $path);

			}
			if (is_array(@$_FILES['logo_file']) && $_FILES['logo_file']['size']) {
				admin_upload($_FILES['logo_file'], 'logo_url');
			}
			if (is_array(@$_FILES['night_logo_file']) && $_FILES['night_logo_file']['size']) {
				admin_upload($_FILES['night_logo_file'], 'night_logo_url');
			}
			if (is_array(@$_FILES['mobile_logo_file']) && $_FILES['mobile_logo_file']['size']) {
				admin_upload($_FILES['mobile_logo_file'], 'mobile_logo_url');
			}
			if (is_array(@$_FILES['mobile_nlogo_file']) && $_FILES['mobile_nlogo_file']['size']) {
				admin_upload($_FILES['mobile_nlogo_file'], 'mobile_nlogo_url');
			}
			if (is_array(@$_FILES['login_back']) && $_FILES['login_back']['size']) {
				admin_upload($_FILES['login_back'], 'login_back');
			}
			if (is_array(@$_FILES['login_logo']) && $_FILES['login_logo']['size']) {
				admin_upload($_FILES['login_logo'], 'login_logo');
			}
		}
	}


//	Mailings management
	
	if ($adminsection=='mailing') {
		if ( qa_clicked('domailingtest') || qa_clicked('domailingstart') || qa_clicked('domailingresume') || qa_clicked('domailingcancel') ) {
			if (!qa_check_form_security_code('admin/'.$adminsection, qa_post_text('code')))
				$securityexpired=true;
				
			else {
				if (qa_clicked('domailingtest')) {
					$email=qa_get_logged_in_email();
					
					if (qa_mailing_send_one(qa_get_logged_in_userid(), qa_get_logged_in_handle(), $email, qa_get_logged_in_user_field('emailcode')))
						$formokhtml=qa_lang_html_sub('admin/test_sent_to_x', qa_html($email));
					else
						$formokhtml=qa_lang_html('main/general_error');
				}
				
				if (qa_clicked('domailingstart')) {
					qa_mailing_start();
					$startmailing=true;
				}
				
				if (qa_clicked('domailingresume'))
					$startmailing=true;
				
				if (qa_clicked('domailingcancel'))
					qa_mailing_stop();
			}
		}
				
		$mailingprogress=qa_mailing_progress_message();
	
		if (isset($mailingprogress)) {
			$formokhtml=qa_html($mailingprogress);

			$checkboxtodisplay=array(
				'mailing_enabled' => '0',
			);
		
		} else {
			$checkboxtodisplay=array(
				'mailing_from_name' => 'option_mailing_enabled',
				'mailing_from_email' => 'option_mailing_enabled',
				'mailing_subject' => 'option_mailing_enabled',
				'mailing_body' => 'option_mailing_enabled',
				'mailing_per_minute' => 'option_mailing_enabled',
				'domailingtest' => 'option_mailing_enabled',
				'domailingstart' => 'option_mailing_enabled',
			);
		}
	}
			

//	Get the actual options	

	$options=qa_get_options($getoptions);

	
//	Prepare content for theme

	$qa_content=qa_content_prepare();

	$qa_content['title']=qa_lang_html('admin/admin_title').' - '.qa_lang_html($subtitle);
	$qa_content['error']=$securityexpired ? qa_lang_html('admin/form_security_expired') : qa_admin_page_error();

	$qa_content['script_rel'][]='king-content/king-admin.js?'.QA_VERSION;
	
	$qa_content['form']=array(
		'ok' => $formokhtml,
		
		'tags' => 'method="post" action="'.qa_self_html().'" name="admin_form" onsubmit="document.forms.admin_form.has_js.value=1; return true;"',
		
		'style' => $formstyle,
		
		'desc' => $desc,

		'fields' => array(),
		
		'buttons' => array(
			'save' => array(
				'tags' => 'id="dosaveoptions"',
				'label' => qa_lang_html('admin/save_options_button'),
			),
			
			'reset' => array(
				'tags' => 'name="doresetoptions"',
				'label' => qa_lang_html('admin/reset_options_button'),
			),
		),
		
		'hidden' => array(
			'dosaveoptions' => '1', // for IE
			'has_js' => '0',
			'code' => qa_get_form_security_code('admin/'.$adminsection),
		),
	);

	if ($recalchotness) {
		$qa_content['form']['ok']='<span id="recalc_ok"></span>';
		$qa_content['form']['hidden']['code_recalc']=qa_get_form_security_code('admin/recalc');
		
		$qa_content['script_var']['qa_warning_recalc']=qa_lang('admin/stop_recalc_warning');
		
		$qa_content['script_onloads'][]=array(
			"qa_recalc_click('dorecountposts', document.getElementById('dosaveoptions'), null, 'recalc_ok');"
		);

	} elseif ($startmailing) {
		
		if (qa_post_text('has_js')) {
			$qa_content['form']['ok']='<span id="mailing_ok">'.qa_html($mailingprogress).'</span>';
			
			$qa_content['script_onloads'][]=array(
				"qa_mailing_start('mailing_ok', 'domailingpause');"
			);
		
		} else { // rudimentary non-Javascript version of mass mailing loop
			echo '<tt>';
			
			while (true) {
				qa_mailing_perform_step();

				$message=qa_mailing_progress_message();
				
				if (!isset($message))
					break;
				
				echo qa_html($message).str_repeat('    ', 1024)."<br>\n";
				
				flush();
				sleep(1);
			}
			
			echo qa_lang_html('admin/mailing_complete').'</tt><p><a href="'.qa_path_html('admin/mailing').'">'.qa_lang_html('admin/admin_title').' - '.qa_lang_html('admin/mailing_title').'</a>';
			
			qa_exit();
		}
	}
		
	function admin_upload($fls, $optname)
	{
		$ImageName = str_replace( ' ', '-', strtolower( $fls['name'] ) );
		$TempSrc   = $fls['tmp_name'];
		$rnumber   = rand( 0, 999999 );
		$directory = QA_INCLUDE_DIR . 'watermark/';
		$NewImageName = $rnumber . '-' . basename( $ImageName );
		$path = $directory . $NewImageName;
		move_uploaded_file($TempSrc, $path);
		qa_set_option($optname, $NewImageName);
	}
	function qa_optionfield_make_select(&$optionfield, $options, $value, $default)
	{
		$optionfield['type']='select';
		$optionfield['options']=$options;
		$optionfield['value']=isset($options[qa_html($value)]) ? $options[qa_html($value)] : @$options[$default];
	}
	
	$indented=false;
	
	foreach ($showoptions as $optionname)
		if (empty($optionname)) {
			$indented=false;
			
			$qa_content['form']['fields'][]=array(
				'type' => 'blank'
			);
		
		} elseif (strpos($optionname, '/')!==false) {
			$qa_content['form']['fields'][]=array(
				'type' => 'static',
				'label' => qa_lang_html($optionname),
			);

			$indented=true;
		
		} else {
			$type=@$optiontype[$optionname];
			if ($type=='number-blank')
				$type='number';
			
			$value=$options[$optionname];
			
			$optionfield=array(
				'id' => $optionname,
				'label' => ($indented ? '&ndash; ' : '').qa_lang_html('options/'.$optionname),
				'tags' => 'name="option_'.$optionname.'" id="option_'.$optionname.'"',
				'value' => qa_html($value),
				'type' => $type,
				'error' => qa_html(@$errors[$optionname]),
			);
			
			if (isset($optionmaximum[$optionname]))
				$optionfield['note']=qa_lang_html_sub('admin/maximum_x', $optionmaximum[$optionname]);
				
			$feedrequest=null;
			$feedisexample=false;
			
			switch ($optionname) { // special treatment for certain options
				case 'site_language':
					require_once QA_INCLUDE_DIR.'king-util/string.php';
					
					qa_optionfield_make_select($optionfield, qa_admin_language_options(), $value, '');
					
					$optionfield['suffix']=strtr(qa_lang_html('admin/check_language_suffix'), array(
						'^1' => '<a href="'.qa_html(qa_path_to_root().'king-include/king-check-lang.php').'">',
						'^2' => '</a>',
					));
				
					if (!qa_has_multibyte())
						$optionfield['error']=qa_lang_html('admin/no_multibyte');
					break;
					
				case 'neat_urls':
					$neatoptions=array();

					$rawoptions=array(
						QA_URL_FORMAT_NEAT,
						QA_URL_FORMAT_INDEX,
						QA_URL_FORMAT_PARAM,
						QA_URL_FORMAT_PARAMS,
						QA_URL_FORMAT_SAFEST,
					);
					
					foreach ($rawoptions as $rawoption)
						$neatoptions[$rawoption]=
							'<iframe src="'.qa_path_html('url/test/'.QA_URL_TEST_STRING, array('dummy' => '', 'param' => QA_URL_TEST_STRING), null, $rawoption).'" width="20" height="16" style="vertical-align:middle; border:0" scrolling="no" frameborder="0"></iframe>&nbsp;'.
							'<small>'.
							qa_html(urldecode(qa_path('123/why-do-birds-sing', null, '/', $rawoption))).
							(($rawoption==QA_URL_FORMAT_NEAT) ? strtr(qa_lang_html('admin/neat_urls_note'), array(
								'^1' => '<a href="" target="_blank">',
								'^2' => '</a>',
							)) : '').
							'</small>';
							
					qa_optionfield_make_select($optionfield, $neatoptions, $value, QA_URL_FORMAT_SAFEST);
							
					$optionfield['type']='select-radio';
					$optionfield['note']=qa_lang_html_sub('admin/url_format_note', '<span style=" '.qa_admin_url_test_html().'/span>');
					break;
					
				case 'site_theme':
				case 'site_theme_mobile':
					$themeoptions=qa_admin_theme_options();
					if (!isset($themeoptions[$value]))
						$value='Classic'; // check here because we also need $value for qa_admin_addon_metadata()
					
					qa_optionfield_make_select($optionfield, $themeoptions, $value, 'Classic');
					
					$contents=file_get_contents(QA_THEME_DIR.$value.'/king-styles.css');

					$metadata=qa_admin_addon_metadata($contents, array(
						'uri' => 'Theme URI',
						'version' => 'Theme Version',
						'date' => 'Theme Date',
						'author' => 'Theme Author',
						'author_uri' => 'Theme Author URI',
						'license' => 'Theme License',
						'update' => 'Theme Update Check URI',
					));
					
					if (strlen($metadata['version'] ?? ''))
						$namehtml='v'.qa_html($metadata['version']);
					else
						$namehtml='';
					
					if (strlen($metadata['uri'] ?? '')) {
						if (!strlen((string)$namehtml))
							$namehtml=qa_html($value);
							
						$namehtml='<a href="'.qa_html($metadata['uri']).'">'.$namehtml.'</a>';
					}
				
					if (strlen($metadata['author'] ?? '')) {
						$authorhtml=qa_html($metadata['author']);
						
						if (strlen($metadata['author_uri'] ?? ''))
							$authorhtml='<a href="'.qa_html($metadata['author_uri']).'">'.$authorhtml.'</a>';
							
						$authorhtml=qa_lang_html_sub('main/by_x', $authorhtml);
						
					} else
						$authorhtml='';
						
					if (strlen($metadata['version'] ?? '') && strlen($metadata['update'] ?? '')) {
						$elementid='version_check_'.$optionname;
						
						$updatehtml='(<span id="'.$elementid.'">...</span>)';
						
						$qa_content['script_onloads'][]=array(
							"qa_version_check(".qa_js($metadata['update']).", 'Theme Version', ".qa_js($metadata['version'], true).", 'Theme URI', ".qa_js($elementid).");"
						);

					} else
						$updatehtml='';
					
					$optionfield['suffix']=$namehtml.' '.$authorhtml.' '.$updatehtml;
					break;
				
				case 'tags_or_categories':
					qa_optionfield_make_select($optionfield, array(
						'' => qa_lang_html('admin/no_classification'),
						't' => qa_lang_html('admin/tags'),
						'c' => qa_lang_html('admin/categories'),
						'tc' => qa_lang_html('admin/tags_and_categories'),
					), $value, 'tc');

					$optionfield['error']='';
					
					if (qa_opt('cache_tagcount') && !qa_using_tags())
						$optionfield['error'].=qa_lang_html('admin/tags_not_shown').' ';
					
					if (!qa_using_categories())
						foreach ($categories as $category)
							if ($category['qcount']) {
								$optionfield['error'].=qa_lang_html('admin/categories_not_shown');
								break;
							}
					break;
				
				case 'smtp_secure':
					qa_optionfield_make_select($optionfield, array(
						'' => qa_lang_html('options/smtp_secure_none'),
						'ssl' => 'SSL',
						'tls' => 'TLS',
					), $value, '');
					break;
				case 'select_kingask':
					qa_optionfield_make_select($optionfield, array(
						'openai' => 'OpenAI',
						'kingstu' => 'KingStudio',
					), $value, '');
					break;
				case 'select_mask':
					qa_optionfield_make_select($optionfield, array(
						'sdn' => qa_lang('misc/sdn'),
						'sd' => qa_lang('misc/sd'),
						'proteus' => qa_lang('misc/proteus'),
						'realxl' => qa_lang('misc/realxl'),
						'odalle' => qa_lang('misc/odalle'),
						'pix' => qa_lang('misc/pix'),
						'playg' => qa_lang('misc/playg'),
						'de' => qa_lang('misc/de'),
						'de3' => qa_lang('misc/de3'),
					), $value, '');
					break;		
				case 'custom_sidebar':
				case 'custom_sidepanel':
				case 'custom_header':
				case 'custom_footer':
				case 'custom_in_head':
				case 'home_description':
				case 'king_analytic_box':
				case 'membership_msg':
				case 'ad_post_below':
					unset($optionfield['label']);
					$optionfield['rows']=6;
					break;
					
				case 'custom_home_content':
					$optionfield['rows']=16;
					break;
				
				case 'show_custom_register':
				case 'show_register_terms':
				case 'show_custom_welcome':
				case 'show_notice_welcome':
				case 'show_notice_visitor':
					$optionfield['style']='tall';
					break;
				
				case 'show_gdpr':
					$optionfield['style']='tall';
					break;
				case 'custom_register':
				case 'register_terms':
				case 'custom_welcome':
				case 'gdpr_box':
				case 'notice_welcome':
				case 'notice_visitor':
					unset($optionfield['label']);
					$optionfield['style']='tall';
					$optionfield['rows']=3;
					break;
				
				case 'avatar_allow_gravatar':
					$optionfield['label']=strtr($optionfield['label'], array(
						'^1' => '<a href="http://www.gravatar.com/" target="_blank">',
						'^2' => '</a>',
					));
					
					if (!qa_has_gd_image()) {
						$optionfield['style']='tall';
						$optionfield['error']=qa_lang_html('admin/no_image_gd');
					}
					break;
					
				case 'avatar_store_size':
				case 'avatar_profile_size':
				case 'avatar_users_size':
				case 'avatar_q_page_q_size':
				case 'avatar_q_page_a_size':
				case 'avatar_q_page_c_size':
				case 'avatar_q_list_size':
				case 'avatar_message_list_size':
					$optionfield['note']=qa_lang_html('admin/pixels');
					break;
				case 'king_sd_api':
					$optionfield['note']='Required KingStudio api key and credits to work. You can create one <a href="https://kingstudio.io/" target="_blank">here</a>';
					break;
				case 'ennsfw':
					$optionfield['note']='NSFW images available with ' .qa_lang('misc/realxl'). ', ' .qa_lang('misc/odalle'). ', ' .qa_lang('misc/dreams'). ', ' .qa_lang('misc/playg') . ', ' . qa_lang('misc/proteus') .' ';
					break;
					case 'eprompter':
						$optionfield['note']='You can enable AI Prompter! Just write few words then click <i class="fa-solid fa-feather"></i>, it will write prompt for you!';
						break;
				case 'king_grids';
					$neatoptions=array();

					$rawoptions=array( 'grids-1', 'grids-2', 'grids-3', 'grids-4', 'grids-5', 'grids-6', 'grids-7', 'grids-8', 'grids-hide' );
					
					foreach ($rawoptions as $rawoption) {
						$neatoptions[$rawoption]='<img src="'.qa_path_to_root().'king-include/king-pages/admin/imgs/'.$rawoption.'.svg" width="160" height="140"/>';
					}
					$gdef = qa_opt('king_grids') ? qa_opt('king_grids') : 'grids-1';
					qa_optionfield_make_select($optionfield, $neatoptions, $value, $gdef);
							
					$optionfield['type']='select-radio';
					
					break;	
				case 'avatar_default_show';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'].=' <span style="margin:2px 0; display:inline-block;">'.
						qa_get_avatar_blob_html(qa_opt('avatar_default_blobid'), qa_opt('avatar_default_width'), qa_opt('avatar_default_height'), 32).
						'</span> <input name="avatar_default_file" type="file" style="width:16em;">';
					break;
				case 'watermark_default_show';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'].=' <span style="margin:2px 0; display:inline-block;">'.
						'<img class="watermarkadmin" src="'.qa_path_to_root().'king-include/watermark/watermark.png" />'.
						'</span> <input name="watermark_default_file" type="file" style="width:16em;">';
					break;
				case 'logo_url_box';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '';
					if (qa_opt('logo_url')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('logo_url').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="logo_file" type="file" style="width:16em;">';
					break;
				case 'night_logo_url_box';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '';
					if (qa_opt('night_logo_url')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('night_logo_url').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="night_logo_file" type="file" style="width:16em;">';
					break;
				case 'mobile_logo_url_box';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '';
					if (qa_opt('mobile_logo_url')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('mobile_logo_url').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="mobile_logo_file" type="file" style="width:16em;">';
					break;
				case 'mobile_nlogo_url_box';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '';
					if (qa_opt('mobile_nlogo_url')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('mobile_nlogo_url').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="mobile_nlogo_file" type="file" style="width:16em;">';
					break;
				case 'login_back_url';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '<span class="king-form-tall-label">'.qa_lang_html('options/home_back').'</span>';
					if (qa_opt('login_back')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('login_back').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="login_back" type="file" style="width:16em;">';
					break;
				case 'login_logo_url';
					$qa_content['form']['tags'].='enctype="multipart/form-data"';
					$optionfield['label'] = '';
					$optionfield['html'] = '<span class="king-form-tall-label">'.qa_lang_html('options/login_logo').'</span>';
					if (qa_opt('login_logo')) {
						$optionfield['html'] .= '<span style="margin:2px 0; display:block;"><img class="logoadmin" src="'.qa_path_to_root().'king-include/watermark/'.qa_opt('login_logo').'" /></span>';
					}
					
					$optionfield['html'] .= '<input name="login_logo" type="file" style="width:16em;">';
					break;
				case 'logo_width':
				case 'logo_height':
					$optionfield['suffix']=qa_lang_html('admin/pixels');
					break;
					
				case 'pages_prev_next':
					qa_optionfield_make_select($optionfield, array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5), $value, 3);
					break;
	
				case 'columns_tags':
				case 'columns_users':
					qa_optionfield_make_select($optionfield, array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5), $value, 2);
					break;
					
				case 'min_len_q_title':
				case 'q_urls_title_length':
				case 'min_len_a_content':
				case 'min_len_c_content':
					$optionfield['note']=qa_lang_html('admin/characters');
					break;
				case 'rate_number':
					$optionfield['note']='%';
					break;
				case 'credits_size':
					$optionfield['note']=qa_lang_html('admin/credits_size');
					break;	
				case 'webhook_key':
						$optionfield['note']='webhook url: ' . qa_opt('site_url') . 'king-include/webhook.php';
						break;
				case 'paypal_email':
						$optionfield['note']='paypal ipn url: ' . qa_opt('site_url') . 'king-include/paypal.php';
						break;			
				case 'min_num_q_tags':
				case 'max_num_q_tags':
					$optionfield['note']=qa_lang_html_sub('main/x_tags', ''); // this to avoid language checking error: a_lang('main/1_tag')
					break;
				
				case 'show_full_date_days':
					$optionfield['note']=qa_lang_html_sub('main/x_days', '');
					break;

				case 'watermark_position':
					qa_optionfield_make_select($optionfield, array(
						'topleft' => 'Top left',
						'topright' => 'Top right',
						'center' => 'Center',
						'bottomleft' => 'Bottom left',
						'bottomright' => 'Bottom right',
						'bottomcenter' => 'Bottom center',
					), $value, 'center');
					break;

				case 'sort_answers_by':
					qa_optionfield_make_select($optionfield, array(
						'created' => qa_lang_html('options/sort_time'),
						'votes' => qa_lang_html('options/sort_votes'),
					), $value, 'created');
					break;
					
				case 'page_size_q_as':
					$optionfield['note']=qa_lang_html_sub('main/x_answers', '');
					break;

				case 'image_max_upload':
					$optionfield['note']=qa_lang_html_sub('main/max_image', '');
					break;

				case 'video_max_upload':
					$optionfield['note']=qa_lang_html_sub('main/max_image', '');
					break;
					
				case 'image_max_file_count':
					$optionfield['note']=qa_lang_html_sub('main/image', '');
					break;					
					
				case 'show_a_form_immediate':
					qa_optionfield_make_select($optionfield, array(
						'always' => qa_lang_html('options/show_always'),
						'if_no_as' => qa_lang_html('options/show_if_no_as'),
						'never' => qa_lang_html('options/show_never'),
					), $value, 'if_no_as');
					break;
					
				case 'show_fewer_cs_from':
				case 'show_fewer_cs_count':
					$optionfield['note']=qa_lang_html_sub('main/x_comments', '');
					break;
					
				case 'match_related_qs':
				case 'match_ask_check_qs':
				case 'match_example_tags':
					qa_optionfield_make_select($optionfield, qa_admin_match_options(), $value, 3);
					break;
					
				case 'block_bad_words':
					$optionfield['style']='tall';
					$optionfield['rows']=4;
					$optionfield['note']=qa_lang_html('admin/block_words_note');
					break;
					
				case 'editor_for_qs':
				case 'editor_for_as':
				case 'editor_for_cs':
					$editors=qa_list_modules('editor');
					
					$selectoptions=array();
					$optionslinks=false;

					foreach ($editors as $editor) {
						$selectoptions[qa_html($editor)]=strlen((string)$editor) ? qa_html($editor) : qa_lang_html('admin/basic_editor');
						
						if ($editor==$value) {
							$module=qa_load_module('editor', $editor);
							
							if (method_exists($module, 'admin_form'))
								$optionfield['note']='<a href="'.qa_admin_module_options_path('editor', $editor).'">'.qa_lang_html('admin/options').'</a>';
						}
					}
						
					qa_optionfield_make_select($optionfield, $selectoptions, $value, '');
					break;
				
				case 'show_custom_ask':
				case 'extra_field_active':
				case 'show_custom_answer':
				case 'show_custom_comment':
					$optionfield['style']='tall';
					break;
				
				case 'custom_ask':
				case 'custom_answer':
				case 'custom_comment':
					$optionfield['style']='tall';
					unset($optionfield['label']);
					$optionfield['rows']=3;
					break;
					
				case 'extra_field_display':
					$optionfield['style']='tall';
					$optionfield['label']='<span id="extra_field_label_hidden" style="display:none;">'.$optionfield['label'].'</span><span id="extra_field_label_shown">'.qa_lang_html('options/extra_field_display_label').'</span>';
					break;
					
				case 'extra_field_prompt':
				case 'extra_field_label':
					$optionfield['style']='tall';
					unset($optionfield['label']);
					break;
					
				case 'search_module':
					foreach ($searchmodules as $modulename => $module) {
						$selectoptions[qa_html($modulename)]=strlen((string)$modulename) ? qa_html($modulename) : qa_lang_html('options/option_default');

						if (($modulename==$value) && method_exists($module, 'admin_form'))
							$optionfield['note']='<a href="'.qa_admin_module_options_path('search', $modulename).'">'.qa_lang_html('admin/options').'</a>';
					}
						
					qa_optionfield_make_select($optionfield, $selectoptions, $value, '');
					break;
					
				case 'hot_weight_q_age': 
				case 'hot_weight_a_age':
				case 'hot_weight_answers':
				case 'hot_weight_votes':
				case 'hot_weight_views':
					$optionfield['note']='/ 100';
					break;
				
				case 'moderate_by_points':
					$optionfield['label']='<span id="moderate_points_label_off" style="display:none;">'.$optionfield['label'].'</span><span id="moderate_points_label_on">'.qa_lang_html('options/moderate_points_limit').'</span>';
					break;
				
				case 'moderate_points_limit';
					unset($optionfield['label']);
					$optionfield['note']=qa_lang_html('admin/points');
					break;
				
				case 'flagging_hide_after':
				case 'flagging_notify_every':
				case 'flagging_notify_first':
					$optionfield['note']=qa_lang_html_sub('main/x_flags', '');
					break;
				
				case 'block_ips_write':
					$optionfield['style']='tall';
					$optionfield['rows']=4;
					$optionfield['note']=qa_lang_html('admin/block_ips_note');
					break;
					
				case 'allow_view_q_bots':
					$optionfield['note']=$optionfield['label'];
					unset($optionfield['label']);
					break;
				
				case 'permit_view_q_page':
				case 'permit_post_q':
				case 'permit_post_a':
				case 'permit_post_c':
				case 'permit_vote_q':
				case 'permit_vote_a':
				case 'permit_vote_down':
				case 'permit_edit_q':
				case 'permit_retag_cat':
				case 'permit_edit_a':
				case 'permit_edit_c':
				case 'permit_edit_silent':
				case 'permit_flag':
				case 'permit_select_a':
				case 'permit_hide_show':
				case 'permit_moderate':
				case 'permit_delete_hidden':
				case 'permit_anon_view_ips':
				case 'permit_view_voters_flaggers':
				case 'permit_post_wall':
					$dopoints=true;
					
					if ($optionname=='permit_retag_cat')
						$optionfield['label']=qa_lang_html(qa_using_categories() ? 'profile/permit_recat' : 'profile/permit_retag').':';
					else
						$optionfield['label']=qa_lang_html('profile/'.$optionname).':';
					
					if ( ($optionname=='permit_view_q_page') || ($optionname=='permit_post_q') || ($optionname=='permit_post_a') || ($optionname=='permit_post_c') || ($optionname=='permit_anon_view_ips') )
						$widest=QA_PERMIT_ALL;
					elseif ( ($optionname=='permit_select_a') || ($optionname=='permit_moderate')|| ($optionname=='permit_hide_show') )
						$widest=QA_PERMIT_POINTS;
					elseif ($optionname=='permit_delete_hidden')
						$widest=QA_PERMIT_EDITORS;
					elseif ( ($optionname=='permit_view_voters_flaggers') || ($optionname=='permit_edit_silent') )
						$widest=QA_PERMIT_EXPERTS;
					else
						$widest=QA_PERMIT_USERS;
						
					if ($optionname=='permit_view_q_page') {
						$narrowest=QA_PERMIT_APPROVED;
						$dopoints=false;
					} elseif ( ($optionname=='permit_edit_c') || ($optionname=='permit_select_a') || ($optionname=='permit_moderate')|| ($optionname=='permit_hide_show') || ($optionname=='permit_anon_view_ips') )
						$narrowest=QA_PERMIT_MODERATORS;
					elseif ( ($optionname=='permit_post_c') || ($optionname=='permit_edit_q') || ($optionname=='permit_edit_a') || ($optionname=='permit_flag') )
						$narrowest=QA_PERMIT_EDITORS;
					elseif ( ($optionname=='permit_vote_q') || ($optionname=='permit_vote_a') || ($optionname=='permit_post_wall') )
						$narrowest=QA_PERMIT_APPROVED_POINTS;
					elseif ( ($optionname=='permit_delete_hidden') || ($optionname=='permit_edit_silent') )
						$narrowest=QA_PERMIT_ADMINS;
					elseif ($optionname=='permit_view_voters_flaggers')
						$narrowest=QA_PERMIT_SUPERS;
					else
						$narrowest=QA_PERMIT_EXPERTS;
					
					$permitoptions=qa_admin_permit_options($widest, $narrowest, (!QA_FINAL_EXTERNAL_USERS) && qa_opt('confirm_user_emails'), $dopoints);
					
					if (count($permitoptions)>1)
						qa_optionfield_make_select($optionfield, $permitoptions, $value,
							($value==QA_PERMIT_CONFIRMED) ? QA_PERMIT_USERS : min(array_keys($permitoptions)));
					else {
						$optionfield['type']='static';
						$optionfield['value']=reset($permitoptions);
					}
					break;
					
				case 'permit_post_q_points':
				case 'permit_post_a_points':
				case 'permit_post_c_points':
				case 'permit_vote_q_points':
				case 'permit_vote_a_points':
				case 'permit_vote_down_points':
				case 'permit_flag_points':
				case 'permit_edit_q_points':
				case 'permit_retag_cat_points':
				case 'permit_edit_a_points':
				case 'permit_edit_c_points':
				case 'permit_select_a_points':
				case 'permit_hide_show_points':
				case 'permit_moderate_points':
				case 'permit_delete_hidden_points':
				case 'permit_anon_view_ips_points':
				case 'permit_post_wall_points':
					unset($optionfield['label']);
					$optionfield['type']='number';
					$optionfield['prefix']=qa_lang_html('admin/users_must_have').'&nbsp;';
					$optionfield['note']=qa_lang_html('admin/points');
					break;
				case 'permit_view_q_page_plan':
				case 'permit_post_q_plan':
				case 'permit_post_a_plan':
				case 'permit_post_c_plan':
				case 'permit_vote_q_plan':
				case 'permit_vote_a_plan':
				case 'permit_vote_down_plan':
				case 'permit_flag_plan':
				case 'permit_edit_q_plan':
				case 'permit_retag_cat_plan':
				case 'permit_edit_a_plan':
				case 'permit_edit_c_plan':
				case 'permit_hide_show_plan':
				case 'permit_moderate_plan':
				case 'permit_delete_hidden_plan':
				case 'permit_anon_view_ips_plan':
				case 'permit_post_wall_plan':
					unset($optionfield['label']);
					qa_optionfield_make_select($optionfield, array(
						'0' => 'All',
						'1' => 'Plan 1',
						'2' => 'Plan 2',
						'3' => 'Plan 3',
						'4' => 'Plan 4',
					), $value, '0');
					$optionfield['prefix']=qa_lang_html('misc/which_plan');
					break;


				case 'feed_for_qa':
					$feedrequest='qa';
					break;

				case 'feed_for_questions':
					$feedrequest='home';
					break;

				case 'feed_for_hot':
					$feedrequest='hot';
					break;

				case 'feed_for_unanswered':
					$feedrequest='unanswered';
					break;

				case 'feed_for_activity':
					$feedrequest='activity';
					break;
					
				case 'feed_per_category':
					if (count($categories)) {
						$category=reset($categories);
						$categoryslug=$category['tags'];

					} else
						$categoryslug='example-category';
						
					if (qa_opt('feed_for_qa'))
						$feedrequest='qa';
					elseif (qa_opt('feed_for_questions'))
						$feedrequest='home';
					else
						$feedrequest='activity';
					
					$feedrequest.='/'.$categoryslug;
					$feedisexample=true;
					break;
					
				case 'feed_for_tag_qs':
					$populartags=qa_db_select_with_pending(qa_db_popular_tags_selectspec(0, 1));
					
					if (count($populartags)) {
						reset($populartags);
						$feedrequest='tag/'.key($populartags);
					} else
						$feedrequest='tag/singing';
						
					$feedisexample=true;
					break;

				case 'feed_for_search':
					$feedrequest='search/why do birds sing';
					$feedisexample=true;
					break;
					
				case 'moderate_users':
					$optionfield['note']='<a href="'.qa_path_html('admin/users', null, null, null, 'profile_fields').'">'.qa_lang_html('admin/registration_fields').'</a>';
					break;
				
				case 'captcha_module':
					$captchaoptions=array();

					foreach ($captchamodules as $modulename) {
						$captchaoptions[qa_html($modulename)]=qa_html($modulename);

						if ($modulename==$value) {
							$module=qa_load_module('captcha', $modulename);
							
							if (method_exists($module, 'admin_form'))
								$optionfield['note']='<a href="'.qa_admin_module_options_path('captcha', $modulename).'">'.qa_lang_html('admin/options').'</a>';
						}
					}
					
					qa_optionfield_make_select($optionfield, $captchaoptions, $value, '');
					break;
				
				case 'moderate_update_time':
					qa_optionfield_make_select($optionfield, array(
						'0' => qa_lang_html('options/time_written'),
						'1' => qa_lang_html('options/time_approved'),
					), $value, '0');
					break;

				case 'currency':
					qa_optionfield_make_select($optionfield, array(
						'USD' => qa_lang_html('misc/usd'),
						'EUR' => qa_lang_html('misc/eur'),
						'AUD' => qa_lang_html('misc/aud'),
						'GBP' => qa_lang_html('misc/gbp'),
						'CAD' => qa_lang_html('misc/cad'),
						'CNY' => qa_lang_html('misc/cny'),
					), $value, 'USD');
					break;
				
				case 'plan_t_1':
				case 'plan_t_2':
				case 'plan_t_3':
				case 'plan_t_4':
					qa_optionfield_make_select($optionfield, array(
						'week' => 'Week',
						'month' => 'Month',
						'year' => 'Year',
						'unlimited' => 'unlimited',
					), $value, 'week');
					break;
				case 'max_rate_ip_as':
				case 'max_rate_ip_cs':
				case 'max_rate_ip_flags':
				case 'max_rate_ip_logins':
				case 'max_rate_ip_messages':
				case 'max_rate_ip_qs':
				case 'max_rate_ip_registers':
				case 'max_rate_ip_uploads':
				case 'max_rate_ip_votes':
					$optionfield['note']=qa_lang_html('admin/per_ip_hour');
					break;
					
				case 'max_rate_user_as':
				case 'max_rate_user_cs':
				case 'max_rate_user_flags':
				case 'max_rate_user_messages':
				case 'max_rate_user_qs':
				case 'max_rate_user_uploads':
				case 'max_rate_user_votes':
					unset($optionfield['label']);
					$optionfield['note']=qa_lang_html('admin/per_user_hour');
					break;
					
				case 'mailing_per_minute':
					$optionfield['suffix']=qa_lang_html('admin/emails_per_minute');
					break;
			}

			if (isset($feedrequest) && $value)
				$optionfield['note']='<a href="'.qa_path_html(qa_feed_request($feedrequest)).'">'.qa_lang_html($feedisexample ? 'admin/feed_link_example' : 'admin/feed_link').'</a>';

			$qa_content['form']['fields'][$optionname]=$optionfield;
		}
		

//	Extra items for specific pages

	switch ($adminsection) {
		case 'users':
			if (!QA_FINAL_EXTERNAL_USERS) {
				$userfields=qa_db_single_select(qa_db_userfields_selectspec());
	
				$listhtml='';
				
				foreach ($userfields as $userfield) {
					$listhtml.='<li><b>'.qa_html(qa_user_userfield_label($userfield)).'</b>';
	
					$listhtml.=strtr(qa_lang_html('admin/edit_field'), array(
						'^1' => '<a href="'.qa_path_html('admin/userfields', array('edit' => $userfield['fieldid'])).'">',
						'^2' => '</a>',
					));
	
					$listhtml.='</li>';
				}
				
				$listhtml.='<li><b><a href="'.qa_path_html('admin/userfields').'">'.qa_lang_html('admin/add_new_field').'</a></b></li>';
	
				$qa_content['form']['fields'][]=array('type' => 'blank');
				
				$qa_content['form']['fields']['userfields']=array(
					'label' => qa_lang_html('admin/profile_fields'),
					'id' => 'profile_fields',
					'style' => 'tall',
					'type' => 'custom',
					'html' => strlen((string)$listhtml) ? '<ul style="margin-bottom:0;">'.$listhtml.'</ul>' : null,
				);
			}
			
			$qa_content['form']['fields'][]=array('type' => 'blank');

			$pointstitle=qa_get_points_to_titles();

			$listhtml='';
			
			foreach ($pointstitle as $points => $title) {
				$listhtml.='<li><b>'.$title.'</b> - '.(($points==1) ? qa_lang_html_sub('main/1_point', '1', '1')
				: qa_lang_html_sub('main/x_points', qa_html(number_format($points))));

				$listhtml.=strtr(qa_lang_html('admin/edit_title'), array(
					'^1' => '<a href="'.qa_path_html('admin/usertitles', array('edit' => $points)).'">',
					'^2' => '</a>',
				));

				$listhtml.='</li>';
			}

			$listhtml.='<li><b><a href="'.qa_path_html('admin/usertitles').'">'.qa_lang_html('admin/add_new_title').'</a></b></li>';

			$qa_content['form']['fields']['usertitles']=array(
				'label' => qa_lang_html('admin/user_titles'),
				'style' => 'tall',
				'type' => 'custom',
				'html' => strlen((string)$listhtml) ? '<ul style="margin-bottom:0;">'.$listhtml.'</ul>' : null,
			);
			break;
			
		case 'widgets':
			$listhtml='';
			
			$widgetmodules=qa_load_modules_with('widget', 'allow_template');
			
			foreach ($widgetmodules as $tryname => $trywidget)
				if (method_exists($trywidget, 'allow_region')) {
					$listhtml.='<li class="wdgt-li"><b>'.qa_html($tryname).'</b>';
					
					$listhtml.=strtr(qa_lang_html('admin/add_widget_link'), array(
						'^1' => '<a href="'.qa_path_html('admin/layoutwidgets', array('title' => $tryname)).'">',
						'^2' => '</a>',
					));
					
					if (method_exists($trywidget, 'admin_form'))
						$listhtml.=strtr(qa_lang_html('admin/widget_global_options'), array(
							'^1' => '<a href="'.qa_admin_module_options_path('widget', $tryname).'">',
							'^2' => '</a>',
						));
						
					$listhtml.='</li>';
				}
			
			if (strlen((string)$listhtml))
				$qa_content['form']['fields']['plugins']=array(
					'label' => qa_lang_html('admin/widgets_explanation'),
					'style' => 'tall',
					'type' => 'custom',
					'html' => '<ul style="margin-bottom:0;">'.$listhtml.'</ul>',
				);
			
			$widgets=qa_db_single_select(qa_db_widgets_selectspec());
			
			$listhtml='';
			
			$placeoptions=qa_admin_place_options();
			
			foreach ($widgets as $widget) {
				$listhtml .='<li class="wdgt-li">';
				$listhtml .= '<b>' . qa_html( isset($widget['wtitle']) ? $widget['wtitle'] : '') . ' - </b>';
				$listhtml .=''.qa_html($widget['title']).' - '.
					'<a href="'.qa_path_html('admin/layoutwidgets', array('edit' => $widget['widgetid'])).'">'.(
					isset($placeoptions[$widget['place']]) ? $placeoptions[$widget['place']] : '').'</a>';
				
				$listhtml .='</li>';
			}
			
			if (strlen((string)$listhtml))
				$qa_content['form']['fields']['widgets']=array(
					'label' => qa_lang_html('admin/active_widgets_explanation'),
					'type' => 'custom',
					'html' => '<ul style="margin-bottom:0;">'.$listhtml.'</ul>',
				);
			
			break;
		
		case 'permissions':
			$qa_content['form']['fields']['permit_block']=array(
				'type' => 'static',
				'label' => qa_lang_html('options/permit_block'),
				'value' => qa_lang_html('options/permit_moderators'),
			);
			
			if (!QA_FINAL_EXTERNAL_USERS) {
				$qa_content['form']['fields']['permit_approve_users']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_approve_users'),
					'value' => qa_lang_html('options/permit_moderators'),
				);
	
				$qa_content['form']['fields']['permit_create_experts']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_create_experts'),
					'value' => qa_lang_html('options/permit_moderators'),
				);
	
				$qa_content['form']['fields']['permit_see_emails']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_see_emails'),
					'value' => qa_lang_html('options/permit_admins'),
				);
		
				$qa_content['form']['fields']['permit_delete_users']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_delete_users'),
					'value' => qa_lang_html('options/permit_admins'),
				);
		
				$qa_content['form']['fields']['permit_create_eds_mods']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_create_eds_mods'),
					'value' => qa_lang_html('options/permit_admins'),
				);
		
				$qa_content['form']['fields']['permit_create_admins']=array(
					'type' => 'static',
					'label' => qa_lang_html('options/permit_create_admins'),
					'value' => qa_lang_html('options/permit_supers'),
				);
	
			}
			break;
			
		case 'mailing':
			require_once QA_INCLUDE_DIR.'king-util/sort.php';
			
			if (isset($mailingprogress)) {
				unset($qa_content['form']['buttons']['save']);
				unset($qa_content['form']['buttons']['reset']);
				
				if ($startmailing) {
					unset($qa_content['form']['hidden']['dosaveoptions']);

					foreach ($showoptions as $optionname)
						$qa_content['form']['fields'][$optionname]['type']='static';
						
					$qa_content['form']['fields']['mailing_body']['value']=qa_html(qa_opt('mailing_body'), true);

					$qa_content['form']['buttons']['stop']=array(
						'tags' => 'name="domailingpause" id="domailingpause"',
						'label' => qa_lang_html('admin/pause_mailing_button'),
					);

				} else {
					$qa_content['form']['buttons']['resume']=array(
						'tags' => 'name="domailingresume"',
						'label' => qa_lang_html('admin/resume_mailing_button'),
					);

					$qa_content['form']['buttons']['cancel']=array(
						'tags' => 'name="domailingcancel"',
						'label' => qa_lang_html('admin/cancel_mailing_button'),
					);
				}
			
			} else {
				$qa_content['form']['buttons']['spacer']=array();
	
				$qa_content['form']['buttons']['test']=array(
					'tags' => 'name="domailingtest" id="domailingtest"',
					'label' => qa_lang_html('admin/send_test_button'),
				);

				$qa_content['form']['buttons']['start']=array(
					'tags' => 'name="domailingstart" id="domailingstart"',
					'label' => qa_lang_html('admin/start_mailing_button'),
				);
			}
			
			if (!$startmailing) {
				$qa_content['form']['fields']['mailing_enabled']['note']=qa_lang_html('admin/mailing_explanation');
				$qa_content['form']['fields']['mailing_body']['rows']=12;
				$qa_content['form']['fields']['mailing_body']['note']=qa_lang_html('admin/mailing_unsubscribe');
			}
			break;
	}
	

	if (isset($checkboxtodisplay))
		qa_set_display_rules($qa_content, $checkboxtodisplay);

	$qa_content['navigation']['sub']=qa_admin_sub_navigation();
	if ($subsubnav) {
		$qa_content['navigation']['kingsub']=king_sub_navigation($subsubnav);
	}
	
	return $qa_content;


/*
	Omit PHP closing tag to help avoid accidental output
*/