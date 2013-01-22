<?php
/**
*
* @package - NV newspage
* @copyright (c) nickvergessen http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/trim_message/trim_message.' . $phpEx);
include($phpbb_root_path . 'includes/trim_message/bbcodes.' . $phpEx);

/*
* Load "ReIMG Image Resizer" by DavidIQ for displaying images and attachments
* https://www.phpbb.com/customise/db/mod/reimg_image_resizer/
*/
if (isset($config['reimg_version']))
{
	define('LOAD_REIMG', true);
}

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('viewtopic', 'mods/info_acp_newspage'));
$newspage_file = (defined('NEWSPAGE_FILE')) ? NEWSPAGE_FILE : 'newspage';

// Get some variables
$forums = ($config['news_forums']) ? $config['news_forums'] : 0;
$news_forums = array_map('intval', explode(',', $forums));
$only_category = request_var('f', 0);
$only_news = request_var('news', 0);
$archive_var = request_var('archive', '');
$start = request_var('start', 0);

$archive_start = $archive_end = 0;
$sql_single_news = $sql_archive_news = $archive_name = '';
$sql_show_shadow = ($config['news_shadow']) ? '' : 'AND topic_moved_id = 0';
$attachments = $attach_list = array();
$has_attachments = false;

if ($archive_var && preg_match("/(0[1-9]|1[0-2])_(19[7-9][0-9]|20([0-2][0-9]|3[0-7]))/", $archive_var))
{
	$archive = explode('_', $archive_var);
	$archive_start = gmmktime(0, 0, 0, (int) $archive[0], 1, (int) $archive[1]);
	$archive_start = $archive_start - $user->timezone;
	$archive_end = gmmktime(0, 0, 0, (int) $archive[0] + 1, 1, (int) $archive[1]);
	$archive_end = $archive_end - $user->timezone;

	$archive_name = sprintf($user->lang['NEWS_ARCHIVE_OF'], $user->format_date($archive_start, 'F Y'));
	$sql_archive_news = " AND topic_time >= $archive_start AND topic_time <= $archive_end";
}
else
{
	$archive_var = '';
}

if ($only_news)
{
	$sql_single_news = 'AND topic_id = ' . $only_news;
}

// Do not include those forums the user is not having read access to...
$news_title = '';
$forum_ary = array();
$forum_read_ary = $auth->acl_getf('f_read');
foreach ($forum_read_ary as $forum_id => $allowed)
{
	if ($allowed['f_read'])
	{
		$forum_ary[] = (int) $forum_id;
	}
}
$forum_ary = array_unique($forum_ary);
// There should not be too many news forums, so we just combine them here to a small array
$sql_forum_ary = array_intersect($news_forums, $forum_ary);

/**
* Select forumnames
*/
if ($config['news_cat_show'])
{
	$sql = 'SELECT forum_id, forum_name, forum_topics
		FROM ' . FORUMS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $sql_forum_ary, false, true) . '
			AND forum_topics <> 0
			ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	while ($cat = $db->sql_fetchrow($result))
	{
		$template->assign_block_vars('cat_block', array(
			'U_NEWS_CAT'		=> append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", 'f=' . $cat['forum_id']),
			'NEWS_CAT'			=> $cat['forum_name'],
			'NEWS_COUNT'		=> $cat['forum_topics'],
		));
	}

	$db->sql_freeresult($result);

	// Restrict to news-category
	if ($only_category)
	{
		$sql_forum_ary = array_intersect($sql_forum_ary, array($only_category));
	}
}

// Grab ranks and icons
$ranks = $cache->obtain_ranks();
$icons = $cache->obtain_icons();

/**
* Select topic_ids for the reqested news
*/
$sql = 'SELECT forum_id, topic_id, topic_type, topic_poster, topic_first_post_id
	FROM ' . TOPICS_TABLE . '
	WHERE ' . $db->sql_in_set('forum_id', $sql_forum_ary, false, true) . "
		$sql_single_news
		$sql_archive_news
		$sql_show_shadow
	ORDER BY topic_time " . (($archive_start) ? 'ASC' : 'DESC');
if ($only_news)
{
	$result = $db->sql_query($sql);
}
else
{
	$result = $db->sql_query_limit($sql, $config['news_number'], $start);
}

$forums = $ga_topic_ids = $topic_ids = $post_ids = $topic_posters = array();
while ($row = $db->sql_fetchrow($result))
{
	$post_ids[] = $row['topic_first_post_id'];
	$topic_ids[] = $row['topic_id'];
	$topic_posters[] = $row['topic_poster'];
	if ($row['topic_type'] == POST_GLOBAL)
	{
		$ga_topic_ids[] = $row['topic_id'];
	}
	else
	{
		$forums[$row['forum_id']][] = $row['topic_id'];
	}
}
$db->sql_freeresult($result);

// Get topic tracking
$topic_ids_ary = $topic_ids;
foreach ($forums as $forum_id => $topic_ids)
{
	$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $topic_ids, $ga_topic_ids);
}
$topic_ids = $topic_ids_ary;

// Get user online-status
$user_online_tracking_info = array();
$sql = 'SELECT session_user_id
	FROM ' . SESSIONS_TABLE . '
	WHERE ' . $db->sql_in_set('session_user_id', $topic_posters, false, true) . '
		AND session_user_id <> ' . ANONYMOUS . '
		AND session_viewonline = 1';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$user_online_tracking_info[] = $row['session_user_id'];
}
$db->sql_freeresult($result);

// Get attachments
if (sizeof($post_ids) && $config['news_attach_show'])
{
	if ($auth->acl_get('u_download'))
	{
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $post_ids) . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);
	}
}

$sql_array = array(
	'SELECT'	=> 't.*, i.icons_url, i.icons_width, i.icons_height, p.*, u.*',
	'FROM'		=> array(TOPICS_TABLE => 't'),
	'LEFT_JOIN'	=> array(
		array(
			'FROM'	=> array(POSTS_TABLE => 'p'),
			'ON'	=> 'p.post_id = t.topic_first_post_id'
		),
		array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 'u.user_id = p.poster_id'
		),
		array(
			'FROM'	=> array(ICONS_TABLE => 'i'),
			'ON'	=> 't.icon_id = i.icons_id'
		),
	),
	'ORDER_BY'	=> 't.topic_time ' . (($archive_start) ? 'ASC' : 'DESC'),
	'WHERE'		=> $db->sql_in_set('t.topic_id', $topic_ids, false, true),
);

$sql = $db->sql_build_query('SELECT', $sql_array);
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	//set some default vars
	$post_id = $row['post_id'];
	$poster_id = $row['poster_id'];
	$topic_id = $row['topic_id'];
	$forum_id = $row['forum_id'];
	$post_list = $post_edit_list = array();
	$display_notice = false;
	$news_title = censor_text($row['post_subject']);

	$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

	//parse message for display
	if (!$only_news)
	{
		$trim = new phpbb_trim_message($row['post_text'], $row['bbcode_uid'], $config['news_char_limit']);
		$row['post_text'] = $trim->message();
		unset($trim);
	}

	$message = $row['post_text'];
	$bbcode_bitfield = '';
	$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);
	if ($bbcode_bitfield !== '')
	{
		$bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}
	$message = censor_text($message);
	if ($row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
	}
	$message = str_replace("\n", '<br />', $message);
	$message = smiley_text($message);

	if (!$auth->acl_get('f_download', $forum_id))
	{
		$display_notice = true;
	}
	else if (!empty($attachments[$row['post_id']]))
	{
		parse_attachments($forum_id, $message, $attachments[$row['post_id']], $update_count);
	}

	$row['post_text'] = $message;

	// Edit Information
	if (($row['post_edit_count'] && $config['display_last_edited']) || $row['post_edit_reason'])
	{
		// Get usernames for all following posts if not already stored
		if (!sizeof($post_edit_list) && ($row['post_edit_reason'] || ($row['post_edit_user'] && !isset($user_cache[$row['post_edit_user']]))))
		{
			$sql2 = 'SELECT DISTINCT u.user_id, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE p.post_edit_count <> 0
					AND p.post_edit_user <> 0
					AND p.post_edit_user = u.user_id';
			$result2 = $db->sql_query($sql2);
			while ($user_edit_row = $db->sql_fetchrow($result2))
			{
				$post_edit_list[$user_edit_row['user_id']] = $user_edit_row;
			}
			$db->sql_freeresult($result2);
		}

		$l_edit_time_total = ($row['post_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];
		if ($row['post_edit_reason'])
		{
			if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
			{
				$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
			}
			else
			{
				$display_username = get_username_string('full', $row['post_edit_user'], $post_edit_list[$row['post_edit_user']]['username'], $post_edit_list[$row['post_edit_user']]['user_colour']);
			}
			$l_edited_by = sprintf($l_edit_time_total, $display_username, $user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
		}
		else
		{
			if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
			{
				$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
			}
			else
			{
				$display_username = get_username_string('full', $row['post_edit_user'], $post_edit_list[$row['post_edit_user']]['username'], $post_edit_list[$row['post_edit_user']]['user_colour']);
			}
			$l_edited_by = sprintf($l_edit_time_total, $display_username, $user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
		}
	}
	else
	{
		$l_edited_by = '';
	}
	$flags = (($row['enable_bbcode']) ? 1 : 0) + (($row['enable_smilies']) ? 2 : 0) + (($row['enable_magic_url']) ? 4 : 0);
	$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);

	get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);
	$row['user_email'] = ((!empty($row['user_allow_viewemail']) || $auth->acl_get('a_email')) && ($row['user_email'] != '')) ? ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$poster_id") : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']) : '';
	$row['user_msnm'] = ($row['user_msnm'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=msnm&amp;u=$poster_id") : '';
	$row['user_icq'] = (!empty($row['user_icq'])) ? 'http://www.icq.com/people/' . urlencode($row['user_icq']) . '/' : '';
	$row['user_icq_status_img'] = (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '';
	$row['user_yim'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
	$row['user_aim'] = ($row['user_aim'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=$poster_id") : '';
	$row['user_jabber'] = ($row['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '';

	$template->assign_block_vars('postrow', array(
		'POST_ID'				=> $post_id,
		'S_IGNORE_POST'			=> false,
		'L_IGNORE_POST'			=> '',
		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : ((in_array($poster_id, $user_online_tracking_info)) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : ((in_array($poster_id, $user_online_tracking_info)) ? true : false),

		'U_EDIT'				=> (!$user->data['is_registered']) ? '' : ((($user->data['user_id'] == $row['poster_id'] && $auth->acl_get('f_edit', $forum_id) && ($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'])) || $auth->acl_get('m_edit', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
		'U_QUOTE'				=> ($auth->acl_get('f_reply', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_INFO'				=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $user->session_id) : '',
		'U_DELETE'				=> (!$user->data['is_registered']) ? '' : ((($user->data['user_id'] == $row['poster_id'] && $auth->acl_get('f_delete', $forum_id) && $row['topic_last_post_id'] == $row['post_id'] && ($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'])) || $auth->acl_get('m_delete', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
		'U_REPORT'				=> ($auth->acl_get('f_report', $forum_id)) ? append_sid("{$phpbb_root_path}report.$phpEx", 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
		'U_NOTES'				=> ($auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $user->session_id) : '',
		'U_WARN'				=> ($auth->acl_get('m_warn') && $poster_id != $user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $user->session_id) : '',
		'U_NEWS'				=> append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", 'news=' . $topic_id),

		'POST_ICON_IMG'			=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
		'POST_ICON_IMG_WIDTH'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
		'POST_ICON_IMG_HEIGHT'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
		'U_MINI_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id']) . (($row['topic_type'] == POST_GLOBAL) ? '&amp;f=' . $forum_id : '') . '#p' . $row['post_id'],
		'POST_SUBJECT'			=> censor_text($row['post_subject']),
		'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', 'NEW_POST') : $user->img('icon_post_target', 'POST'),
		'POST_AUTHOR_FULL'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'POST_DATE'				=> $user->format_date($row['post_time']),

		'S_POST_UNAPPROVED'		=> ($row['post_approved']) ? false : true,
		'S_POST_REPORTED'		=> ($row['post_reported'] && $auth->acl_get('m_report', $forum_id)) ? true : false,
		'U_MCP_REPORT'			=> ($auth->acl_get('m_report', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MCP_APPROVE'			=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',

		'MESSAGE'				=> $row['post_text'],

		'S_HAS_POLL'			=> (!empty($row['poll_start'])) ? true : false,
		'POLL_QUESTION'			=> $row['poll_title'],
		'S_HAS_ATTACHMENTS'		=> (!empty($attachments[$row['post_id']]) && $config['news_attach_show']) ? true : false,
		'S_DISPLAY_NOTICE'		=> $display_notice && $row['post_attachment'],
		'EDITED_MESSAGE'		=> $l_edited_by,
		'EDIT_REASON'			=> $row['post_edit_reason'],
		'SIGNATURE'				=> ($row['enable_sig']) ? $row['user_sig'] : '',
		'NEWS_COMMENTS'			=> $row['topic_replies'],

		'POSTER_AVATAR'			=> ($user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
		'U_POST_AUTHOR'			=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'RANK_TITLE'			=> $row['rank_title'],
		'RANK_IMG'				=> $row['rank_image'],
		'RANK_IMG_SRC'			=> $row['rank_image_src'],
		'POSTER_POSTS'			=> $row['user_posts'],
		'POSTER_JOINED'			=> $user->format_date($row['user_regdate']),
		'POSTER_FROM'			=> $row['user_from'],

		'U_PM'					=> ($poster_id != ANONYMOUS && $config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']) : '',
		'U_EMAIL'				=> $row['user_email'],
		'U_WWW'					=> $row['user_website'],
		'U_MSN'					=> $row['user_msnm'],
		'U_ICQ'					=> $row['user_icq'],
		'U_YIM'					=> $row['user_yim'],
		'U_AIM'					=> $row['user_aim'],
		'U_JABBER'				=> $row['user_jabber'],
	));

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (!empty($attachments[$row['post_id']]))
	{
		foreach ($attachments[$row['post_id']] as $attachment)
		{
			$template->assign_block_vars('postrow.attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment,
			));
		}
	}
}
$db->sql_freeresult($result);

/**
* Build archiv-list
*/
$archiv_years = $archiv_months = $checked_months = array();
$sql = 'SELECT topic_time
	FROM ' . TOPICS_TABLE . '
	WHERE ' . $db->sql_in_set('forum_id', $sql_forum_ary, false, true) . '
	ORDER BY topic_time DESC';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$month_name = $user->format_date($row['topic_time'], 'F');
	$archiv_year = $user->format_date($row['topic_time'], 'Y');

	$archiv_month = $month_name . ' ' . $archiv_year;
	if (in_array($archiv_month, $checked_months))
	{
		$archiv_months[$archiv_year][$archiv_month]['count']++;
		continue;
	}

	if (!in_array($archiv_year, $archiv_years))
	{
		$archiv_years[] = $archiv_year;
	}

	$checked_months[] = $archiv_month;
	$archiv_months[$archiv_year][$archiv_month] = array(
		'url'	=> $user->format_date($row['topic_time'], 'm_Y'),
		'name'	=> $month_name,
		'count'	=> 1,
	);
}
$db->sql_freeresult($result);

$total_news = 0;
foreach ($archiv_years as $archive_year)
{
	$template->assign_block_vars('archive_block', array(
		'NEWS_YEAR'		=> $archive_year,
	));
	foreach ($archiv_months[$archive_year] as $archive_month)
	{
		$template->assign_block_vars('archive_block.archive_row', array(
			'U_NEWS_MONTH'		=> append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", 'archive=' . $archive_month['url'] . (($only_category && !empty($config['news_cat_show'])) ? "&amp;f=$only_category" : '')),
			'NEWS_MONTH'		=> $archive_month['name'],
			'NEWS_COUNT'		=> $archive_month['count'],
		));
		if (($archive_var == $archive_month['url']) || !$archive_var)
		{
			$total_news = $total_news + $archive_month['count'];
		}
	}
}

// Specify some images
if ($config['news_user_info'])
{
	$template->assign_vars(array(
		'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
		'SEARCH_IMG' 		=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
		'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
		'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
		'WWW_IMG' 			=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
		'ICQ_IMG' 			=> $user->img('icon_contact_icq', 'ICQ'),
		'AIM_IMG' 			=> $user->img('icon_contact_aim', 'AIM'),
		'MSN_IMG' 			=> $user->img('icon_contact_msnm', 'MSNM'),
		'YIM_IMG' 			=> $user->img('icon_contact_yahoo', 'YIM'),
		'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER'),
	));
}
if ($config['news_post_buttons'])
{
	$template->assign_vars(array(
		'QUOTE_IMG' 		=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
		'EDIT_IMG' 			=> $user->img('icon_post_edit', 'EDIT_POST'),
		'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_POST'),
		'INFO_IMG' 			=> $user->img('icon_post_info', 'VIEW_INFO'),
		'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_POST'),
		'WARN_IMG'			=> $user->img('icon_user_warn', 'WARN_USER'),
	));
}
$template->assign_vars(array(
	'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'NEWS_USER_INFO'		=> $config['news_user_info'],
	'NEWS_POST_BUTTONS'		=> $config['news_post_buttons'],
	'NEWS_ONLY'				=> $only_news,
	'NEWS_TITLE'			=> $news_title,
	'S_NEWS_ARCHIVE_PER_YEAR'		=> $config['news_archive_per_year'],
));

if (!$only_news)
{
	if (!$archive_var)
	{
		$total_paginated = $config['news_pages'] * $config['news_number'];
		$total_paginated = min($total_paginated, $total_news);
	}
	else
	{
		$total_paginated = $total_news;
	}
	$pagination = generate_pagination(append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", (($archive_var) ? 'archive=' . $archive_var : '').(($only_category) ? 'f=' . $only_category : '')), $total_paginated, $config['news_number'], $start);

	$template->assign_vars(array(
		'PAGINATION'		=> $pagination,
		'PAGE_NUMBER'		=> on_page($total_paginated, $config['news_number'], $start),
		'TOTAL_NEWS'		=> ($total_news == 1) ? $user->lang['VIEW_TOPIC_POST'] : sprintf($user->lang['VIEW_TOPIC_POSTS'], $total_news),
	));
}

page_header($user->lang['NEWS'] . (($archive_name) ? ' - ' . $archive_name : '') . (($only_news && $news_title) ? ' - ' . $news_title : ''));

$template->set_filenames(array(
	'body' => 'newspage_body.html')
);

page_footer();
