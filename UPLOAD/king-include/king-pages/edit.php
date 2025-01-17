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

if ( ! defined( 'QA_VERSION' ) ) {
	// don't allow this page to be requested directly from browser
	header( 'Location: ../' );
	exit;
}

require_once QA_INCLUDE_DIR . 'king-app/format.php';
require_once QA_INCLUDE_DIR . 'king-app/limits.php';
require_once QA_INCLUDE_DIR . 'king-app/posts.php';
require_once QA_INCLUDE_DIR . 'king-db/selects.php';
require_once QA_INCLUDE_DIR . 'king-util/sort.php';
require_once QA_INCLUDE_DIR . 'king-app-video.php';
require_once QA_INCLUDE_DIR . 'king-util/string.php';
require_once QA_INCLUDE_DIR . 'king-app/post-update.php';


$in               = array();
$userid           = qa_get_logged_in_userid();
$handle           = qa_get_logged_in_handle();
$followpostid     = qa_get( 'post' );
$in['categoryid'] = qa_clicked( 'doask' ) ? qa_get_category_field_value( 'category' ) : qa_get( 'cat' );

list( $categories, $post, $completetags, $extravalue ) = qa_db_select_with_pending(
	qa_db_category_nav_selectspec( $in['categoryid'], true ),
	isset( $followpostid ) ? qa_db_full_post_selectspec( $userid, $followpostid ) : null,
	qa_db_popular_tags_selectspec( 0, QA_DB_RETRIEVE_COMPLETE_TAGS ),
	qa_db_post_meta_selectspec( $followpostid, 'qa_q_extra' )
);

if ( ! isset( $categories[$in['categoryid']] ) ) {
	$in['categoryid'] = null;
}

$isuserid = false;

if ( $post['userid'] == $userid ) {
	$isuserid = true;
}

$poster      = king_get_uploads( $post['content'] );
$permiterror = qa_user_post_permit_error( $isuserid ? null : 'permit_edit_q', $post );

if ( $permiterror ) {
	$qa_content = qa_content_prepare();

	// The 'approve', 'login', 'confirm', 'limit', 'userblock', 'ipblock' permission errors are reported to the user here
	// The other option ('level') prevents the menu option being shown, in qa_content_prepare(...)

	switch ( $permiterror ) {
		case 'login':
			$qa_content['error'] = qa_insert_login_links( qa_lang_html( 'question/ask_must_login' ), qa_request(), isset( $followpostid ) ? array( 'follow' => $followpostid ) : null );
			break;

		case 'confirm':
			$qa_content['error'] = qa_insert_login_links( qa_lang_html( 'question/ask_must_confirm' ), qa_request(), isset( $followpostid ) ? array( 'follow' => $followpostid ) : null );
			break;

		default:
			$qa_content['error'] = qa_lang_html( 'users/no_permission' );
			break;
	}

	return $qa_content;
}

if ( empty( $post['postid'] ) ) {
	$qa_content          = qa_content_prepare();
	$qa_content['error'] = qa_lang_html( 'users/no_permission' );

	return $qa_content;
}

$captchareason = qa_user_captcha_reason();

if ( qa_clicked( 'doask' ) ) {
	require_once QA_INCLUDE_DIR . 'king-app/post-create.php';
	require_once QA_INCLUDE_DIR . 'king-util/string.php';

	$categoryids = array_keys( qa_category_path( $categories, @$in['categoryid'] ) );
	$userlevel   = qa_user_level_for_categories( $categoryids );

	$in['title']    = qa_get_post_title( 'title' ); // allow title and tags to be posted by an external form
	$in['extra']    = qa_post_text( 'extra' );
	$in['pcontent'] = qa_post_text( 'pcontent' );

	if ( qa_using_tags() ) {
		$in['tags'] = qa_get_tags_field_value( 'tags' );
	}

	$in['name']       = qa_post_text( 'name' );
	$in['notify']     = strlen( qa_post_text( 'notify' ) ) > 0;
	$in['nsfw']       = qa_post_text( 'nsfw' );
	$in['email']      = qa_post_text( 'email' );
	$in['queued']     = qa_user_moderation_reason( $userlevel ) !== false;
	$in['news_thumb'] = qa_post_text( 'news_thumb' );

	$editthumb = isset( $in['news_thumb'] ) ? $in['news_thumb'] : $post['content'];

	qa_get_post_content( 'editor', 'content', $in['editor'], $in['content'], $in['format'], $in['text'] );

	$errors = array();

	if ( ! qa_check_form_security_code( 'ask', qa_post_text( 'code' ) ) ) {
		$errors['page'] = qa_lang_html( 'misc/form_security_again' );
	} else {
		$filtermodules = qa_load_modules_with( 'filter', 'filter_question' );

		foreach ( $filtermodules as $filtermodule ) {
			$oldin = $in;
			$filtermodule->filter_question( $in, $errors, null );
			qa_update_post_text( $in, $oldin );
		}

		if ( qa_using_categories() && count( $categories ) && ( ! qa_opt( 'allow_no_category' ) ) && ! isset( $in['categoryid'] ) ) {
			$errors['categoryid'] = qa_lang_html( 'question/category_required' );
		}

		// check this here because we need to know count($categories)
		elseif ( qa_user_permit_error( 'permit_post_q', null, $userlevel ) ) {
			$errors['categoryid'] = qa_lang_html( 'question/category_ask_not_allowed' );
		}

		if ( $captchareason ) {
			require_once QA_INCLUDE_DIR . 'king-app/captcha.php';
			qa_captcha_validate_post( $errors );
		}

		if ( empty( $errors ) ) {
			$cookieid = isset( $userid ) ? qa_cookie_get() : qa_cookie_get_create();

			if ( 'list' == $post['postformat'] ) {
				if ( ! isset( $_POST['out'] ) || ! is_array( $_POST['out'] ) ) {
					$in['poll'] = null;
				} else {
					$in['poll'] = qa_gpc_to_string( $_POST['out'] );
					$poll       = serialize( $in['poll'] );
				}

				qa_db_query_sub( "UPDATE ^poll SET content=$, extra=$, created=NOW() WHERE postid=#", $poll, 'list', $post['postid'] );
			} elseif ( 'music' == $post['postformat'] ) {
				if ( ! isset( $_POST['out'] ) || ! is_array( $_POST['out'] ) ) {
					$in['poll'] = null;
				} else {
					$in['poll'] = qa_gpc_to_string( $_POST['out'] );
					$poll       = serialize( $in['poll'] );
				}

				qa_db_query_sub( "UPDATE ^poll SET content=$, created=NOW() WHERE postid=#", $poll, $post['postid'] );
			} elseif ( 'V' == $post['postformat'] ) {
				$in['extra'] = ! empty( $in['extra'] ) ? $in['extra'] : $extravalue;
			} elseif ( 'I' == $post['postformat'] ) {
				$in['submit_image'] = qa_post_array( 'submit_image' );
				$in['extra']        = serialize( $in['submit_image'] );
			}

			qa_question_set_content( $post, $in['title'], $editthumb, $in['format'], $in['text'], qa_tags_to_tagstring( $in['tags'] ),
				$in['notify'], $userid, $handle, $cookieid, $in['extra'], @$in['name'], $in['queued'], $in['silent'], $in['pcontent'], $in['nsfw'] );

			$answers         = qa_post_get_question_answers( $post['postid'] );
			$commentsfollows = qa_post_get_question_commentsfollows( $post['postid'] );
			$closepost       = qa_post_get_question_closepost( $post['postid'] );

			if ( qa_using_categories() && strcmp( $in['categoryid'], $post['categoryid'] ) ) {
				qa_question_set_category( $post, $in['categoryid'], $userid, $handle, $cookieid,
					$answers, $commentsfollows, $closepost, $in['silent'] );
			}

			qa_redirect( qa_q_request( $post['postid'], $in['title'] ) ); // our work is done here
		}
	}
}

$qa_content = qa_content_prepare( false, array_keys( qa_category_path( $categories, @$in['categoryid'] ) ) );

$qa_content['title'] = qa_lang_html( 'question/ask_title' );
$qa_content['error'] = @$errors['page'];

$field['label'] = qa_lang_html( 'question/q_content_label' );
$field['error'] = qa_html( @$errors['content'] );

$custom    = qa_opt( 'show_custom_ask' ) ? trim( qa_opt( 'custom_ask' ) ) : '';
$purl      = isset( $poster['furl'] ) ? $poster['furl'] : '';
$thumbnail = '<div class="king-dropzone-edit"><img src="' . $purl . '" class="edit-prev" /><div id="newsthumb" class="dropzone king-poll-file"></div></div>';
$thlabel   = qa_lang_html( 'misc/select_thumb' );

if ( 'music' == $post['postformat'] ) {
	$extraz = isset( $extravalue ) ? $extravalue : '';
	$poll = '';
	$nginit = '';
	if (!$extraz) {


		$lsources = get_poll($post['postid'], 'music', true);
		$lists = unserialize($lsources['content']);
		$nginit = 'inputs = [';
		foreach ( $lists as $list ) {
			$mp3 = king_get_uploads( $list['music'] );
			$nginit .= '{choices:\'' . $list['ptitle'] . '\', columnimg:\'' . $list['music'] . '\', prev:\''.$mp3['furl'].'\'},';
		}
		$nginit .= ']';
		}
	if ( qa_opt( 'enable_music_upload' ) ) {
		$poll .= '<ul class="nav-tabs" role="tablist">';
		$poll .= '<li class="' . ($extraz ? 'active' : '') . '"><a href="#vidurl" aria-controls="vidurl" class="king-vidurl" role="tab" data-toggle="tab"><i class="fas fa-link"></i></a></li>
				<li class="' . (!$extraz ? 'active' : '') . '"><a href="#vidup" aria-controls="vidup" class="king-vidup" role="tab" data-toggle="tab"><i class="fas fa-cloud-upload-alt"></i></a></li>';
		$poll .= '</ul>';


	}
	$poll .= '<div id="vidurl" role="tabpanel" class="tabcontent ' . ($extraz ? 'active' : '') . '">
					<div class="videoembedup">
					<input placeholder="' . qa_lang_html( 'question/q_content_label' ) . '" name="extra" value="' . qa_html( $extraz ) . '"  id="content2" autocomplete="off" type="text" class="king-form-tall-text" onchange="video_add(this.value)" onInput="video_add(this.value)">
					<div id="videoembed"><i class="fas fa-compact-disc"></i></div>
					</div>
				</div>';
	if ( qa_opt( 'enable_music_upload' ) ) {
		$poll .= '<div class="tabcontent ' . (!$extraz ? 'active' : '') . ' musica" role="tabpanel" id="vidup">';
				$poll .= '<div class="king-ang" ng-model="inputs" ng-init="' . $nginit . '">
				<div class="inputarea" ng-repeat="input in inputs track by $index | limitTo:24">
					<input class="king-form-tall-text" ng-model="input.choices" type="text" name="out[{{$index+1}}][ptitle]" autocomplete="off" maxlength="300" placeholder="'.qa_lang_html('misc/music_title').'"/>
					<div class="king-dropzone-edit">
					<video id="my-video" class="video-js vjs-theme-forest" controls preload="auto"  width="236" height="170" data-setup="{}" ><source src="{{input.prev}}" type="video/mp4" ng-show="input.prev"/></video>

						<div id="dropzone1" class="dropzone king-poll-file" dropzonemusic="dropzoneConfig" ng-dropzone></div>
						<input class="hide" type="text" ng-model="input.columnimg" name="out[{{$index+1}}][music]" autocomplete="off" maxlength="40"/>
					</div>
				</div>
			</div>
			<div class="king-poll-grid paddnew" ng-click="addList()"><i class="fas fa-plus"></i></div>';
		$poll .= '</div>';
	}
} elseif ( 'list' == $post['postformat'] ) {
	$list_source = get_poll( $post['postid'], '', true );
	$lists       = @unserialize( $list_source['content'] );
	$nginit      = 'inputs = [';

	foreach ( $lists as $list ) {
		if ( $list['img'] ) {
			$imgg = king_get_uploads( $list['img'] );
			$nginit .= '{polltab:\'grid2\', choices:\'' . $list['choices'] . '\', img:\'' . $list['img'] . '\', prev:\'' . $imgg['furl'] . '\', desc:\'' . $list['desc'] . '\'},';
		} elseif ( $list['video'] ) {
			$nginit .= '{polltab:\'grid1\', choices:\'' . $list['choices'] . '\', video:\'' . $list['video'] . '\', desc:\'' . $list['desc'] . '\'},';
		} else {
			$nginit .= '{polltab:\'grid1\', choices:\'' . $list['choices'] . '\', desc:\'' . $list['desc'] . '\'},';

		}
	}

	$nginit .= ']';

	$poll = '<div ng-model="inputs" ui-sortable="sortableOptions" ng-init="' . $nginit . '">
<div class="king-ang" ng-repeat="input in inputs track by $index | limitTo:24">
	<div class="kingp-left">
		<div class="kingp-leftin">
		<div class="kingp-tabs">
			<div class="number-list">{{$index+1}}</div>
			<label><input class="hide" type="radio" ng-model="input.polltab" id="grid1" value="grid1" name="grid[{{$index+1}}]"><i class="fas fa-video"></i></label>
			<label><input class="hide" type="radio" ng-model="input.polltab" id="grid2" value="grid2" name="grid[{{$index+1}}]"><i class="fas fa-image"></i></label>
			<label><i ng-click="removeInput(inputs, $index)" ng-if="!$first" class="far fa-trash-alt"></i></label>
			<div class="pbutton listhandle"><i class="fas fa-arrows-alt"></i></div>
			</div>
		</div>
		<div ng-if="$last" class="tblack" ng-click="addList()"><i class="fas fa-plus"></i></div>
	</div>
	<div class="inputarea">
		<div  class="king-lists">
			<div  class="king-list">
				<div class="inright">
					<input class="king-form-tall-text" type="text" ng-model="input.choices" name="out[{{$index+1}}][choices]" required autocomplete="off" maxlength="200"/>
						<div class="king-dropzone-edit" ng-show="input.polltab !== \'grid1\'">

							<img src="{{input.prev}}" class="edit-prev" ng-show="input.prev" />
							<div ng-model="input.files" id="dropzone1" class="dropzone king-poll-file" dropzone="dropzoneConfig" ng-dropzone></div>
							<input type="text" ng-model="input.img" class="hide" name="out[{{$index+1}}][img]" autocomplete="off" maxlength="20"/>
						</div>
						<div ng-show="input.polltab !== \'grid2\'">
							<input class="king-form-tall-text" type="text" ng-model="input.video" name="out[{{$index+1}}][video]" autocomplete="off" maxlength="250" placeholder="' . qa_lang_html( 'misc/video_url' ) . '" onchange="video_add(this, this.value)" onInput="video_add(this, this.value)"/>
							<div id="videoembed"><i class="fas fa-compact-disc"></i></div>
						</div>
					<input class="hide" type="text" ng-model="input.id" name="out[{{$index+1}}][id]" value="{{$index+1}}"/>
					<textarea class="king-form-tall-text" ng-model="input.desc" type="textarea" name="out[{{$index+1}}][desc]" maxlength="400"></textarea>
			</div>
		</div>
	</div>
	</div>
	</div>
	</div>';
} elseif ( 'V' == $post['postformat'] ) {
	$edit   = ! is_numeric( $extravalue ) ? $extravalue : '';
	$classv = is_numeric( $extravalue ) ? 'active' : '';
	$classm = ! is_numeric( $extravalue ) ? 'active' : '';

	if ( qa_opt( 'links_in_new_window' ) ) {
		$poll = '<ul class="nav-tabs" role="tablist">';
		$poll .= '<li class="' . $classm . '"><a href="#vidurl" aria-controls="vidurl" class="king-vidurl" role="tab" data-toggle="tab"><i class="fas fa-link"></i></a></li>
				<li class="' . $classv . '"><a href="#vidup" aria-controls="vidup" class="king-vidup" role="tab" data-toggle="tab"><i class="fas fa-cloud-upload-alt"></i></a></li>';
		$poll .= '</ul>';
	}

	$poll .= '<div id="vidurl" role="tabpanel" class="tabcontent ' . $classm . '">
					<div class="videoembedup">
					<input placeholder="' . qa_lang_html( 'question/q_content_label' ) . '" name="extra" value="' . qa_html( $edit ) . '"  id="content2" autocomplete="off" type="text" class="king-form-tall-text" onchange="video_add(this.value)" onInput="video_add(this.value)">
					<div id="videoembed"><i class="fas fa-compact-disc"></i></div>
					</div>
				</div>';

	if ( qa_opt( 'links_in_new_window' ) ) {
		$vid = king_get_uploads( $extravalue );
		$poll .= '<div class="tabcontent ' . $classv . '" role="tabpanel" id="vidup">';
		$poll .= '<div class="king-dropzone-edit" role="tabpanel" id="vidup">';

		if ( is_numeric( $extravalue ) ) {
			$poll .= '<video id="my-video" class="video-js vjs-theme-forest" controls preload="auto"  width="960" height="540" data-setup="{}" ><source src="' . $vid['furl'] . '" type="video/mp4" /></video>';
		}

		$poll .= '<div id="viddropzone" class="dropzone king-poll-file"></div>';
		$poll .= '</div>';
		$poll .= '</div>';
	}
} elseif ( 'I' == $post['postformat'] ) {
	$edit   = isset( $in['extra'] ) ? $in['extra'] : $extravalue;
	$images = unserialize( $edit );
	$poll   = '<div id="dropzone" class="dropzone king-poll-file dz-started">';

	if ( $images ) {
		foreach ( $images as $image ) {
			$thumb = $image - 1;
			$text2 = king_get_uploads( $thumb );

			if ( $post['content'] == $thumb ) {
				$checked = 'checked="true"';
			} else {
				$checked = '';
			}

			$poll .= '<div class="dz-preview dz-processing dz-image-preview dz-success dz-complete">';
			$poll .= '<img class="editi-prev" src="' . ( isset($text2['furl']) ? $text2['furl'] :'') . '" />';
			$poll .= '<input type="hidden" name="submit_image[]" id="submit_image" value="' . $image . '" />';
			$poll .= '<input value="' . $thumb . '" type="radio" name="thumb" id="thumb_' . $image . '" class="thumb-radio" ' . $checked . ' />';
			$poll .= '<label title="set as thumb" class="thumb-radio-label" for="thumb_' . $image . '"></label>';
			$poll .= '<div class="dz-remove" id="dzremove"><i class="fas fa-trash-alt"></i></div>';
			$poll .= '</div>';
		}

	}

	$poll .= '</div>';
} elseif ( 'N' == $post['postformat'] ) {
	$poll = '';
} elseif ( 'trivia' == $post['postformat'] ) {
	$poll = '';
} elseif ( 'poll' == $post['postformat'] ) {
	$poll = '';
}

if ( qa_opt( 'enable_aws' ) ) {
	$awscla = 'pcontentaws';
} else {
	$awscla = 'pconteno';
}

$qa_content['form'] = array(
	'tags'    => 'name="ask" method="post" ENCTYPE="multipart/form-data" action="' . qa_self_html() . '" ng-controller="MyCtrl" ng-app="plunker"',

	'style'   => 'tall',

	'fields'  => array(
		'custom'  => array(
			'type' => 'custom',
			'html' => '<div class="snote">' . $custom . '</div>',
		),

		'imgprev' => array(
			'label' => $thlabel,
			'type'  => 'custom',
			'html'  => $thumbnail,
		),

		'title'   => array(
			'label' => qa_lang_html( 'question/q_title_label' ),
			'tags'  => 'name="title" id="title" autocomplete="off" minlength="' . qa_opt( 'min_len_q_title' ) . '" required',
			'value' => qa_html( isset( $in['title'] ) ? $in['title'] : $post['title'] ),
			'error' => qa_html( @$errors['title'] ),
		),

		'similar' => array(
			'type' => 'custom',
			'html' => '<span id="similar"></span>',
		),

		'tiny'    => array(
			'label' => qa_lang_html( 'main/news_content' ),
			'type'  => 'custom',
			'html'  => '<div id="pcontent" class="' . $awscla . '">' . qa_sanitize_html( isset( $in['pcontent'] ) ? $in['pcontent'] : $post['pcontent'] ) . '</div>',
		),

		'poll'    => array(
			'type' => 'custom',
			'html' => $poll,
		),

	),

	'buttons' => array(
		'ask' => array(
			'tags'  => 'onclick="qa_show_waiting_after(this, false); "',
			'label' => qa_lang_html('question/ask_button'),
		),
	),

	'hidden'  => array(
		'code'  => qa_get_form_security_code( 'ask' ),
		'doask' => '1',
	),
);
script_options( $qa_content );

if ( ! strlen( $custom ) ) {
	unset( $qa_content['form']['fields']['custom'] );
}

if ( qa_opt( 'do_ask_check_qs' ) || qa_opt( 'do_example_tags' ) ) {
	$qa_content['script_rel'][] = 'king-content/king-ask.js?' . QA_VERSION;
	$qa_content['form']['fields']['title']['tags'] .= ' onchange="qa_title_change(this.value);"';

	if ( strlen( $in['title'] ?? '' ) ) {
		$qa_content['script_onloads'][] = 'qa_title_change(' . qa_js( $in['title'] ) . ');';
	}
}

if ( qa_using_categories() && count( $categories ) ) {
	$field = array(
		'label' => qa_lang_html( 'question/q_category_label' ),
		'error' => qa_html( @$errors['categoryid'] ),
	);

	qa_set_up_category_field( $qa_content, $field, 'category', $categories,
		isset( $in['categoryid'] ) ? $in['categoryid'] : $post['categoryid'],
		qa_opt( 'allow_no_category' ) || ! isset( $post['categoryid'] ), qa_opt( 'allow_no_sub_category' ) );

	if ( ! qa_opt( 'allow_no_category' ) ) // don't auto-select a category even though one is required
	{
		$field['options'][''] = '';
	}

	qa_array_insert( $qa_content['form']['fields'], 'similar', array( 'category' => $field ) );
}

if ( qa_using_tags() ) {
	$field = array(
		'error' => qa_html( @$errors['tags'] ),
	);

	qa_set_up_tag_field( $qa_content, $field, 'tags', isset( $in['tags'] ) ? $in['tags'] : qa_tagstring_to_tags( $post['tags'] ),
		array(), qa_opt( 'do_complete_tags' ) ? array_keys( $completetags ) : array(), qa_opt( 'page_size_ask_tags' ) );

	qa_array_insert( $qa_content['form']['fields'], null, array( 'tags' => $field ) );
}

if ( ! isset( $userid ) ) {
	qa_set_up_name_field( $qa_content, $qa_content['form']['fields'], @$in['name'] );
}

if ( qa_opt( 'enable_nsfw' ) ) {
	$field = array(
		'type' => 'custom',
		'html' => '<input name="nsfw" id="king_nsfw" type="checkbox" ' . qa_html( isset( $post['nsfw'] ) ? 'checked' : '' ) . '><label for="king_nsfw" class="king-nsfw">' . qa_lang_html( 'misc/nsfw' ) . '</label>',
	);
	qa_array_insert( $qa_content['form']['fields'], null, array( 'nsfw' => $field ) );
}

if ( $captchareason ) {
	require_once QA_INCLUDE_DIR . 'king-app/captcha.php';
	qa_set_up_captcha_field( $qa_content, $qa_content['form']['fields'], @$errors, qa_captcha_reason_note( $captchareason ) );
}

$qa_content['focusid'] = 'title';

return $qa_content;

/*
Omit PHP closing tag to help avoid accidental output
 */
