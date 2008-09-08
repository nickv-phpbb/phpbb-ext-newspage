<?php
/** 
*
* @package - NV newspage
* @version $Id$
* @copyright (c) nickvergessen ( http://www.flying-bits.org/ )
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
$user->add_lang('viewtopic');
$user->add_lang('mods/info_acp_newspage');

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$newspage_file = (defined('NEWSPAGE_FILE')) ? NEWSPAGE_FILE : 'newspage';

// Get some variables
$forums = ($config['news_forums']) ? $config['news_forums'] : 0;
$only_news = request_var('news', 0);
$archiv_start = request_var('start', 0);
$archiv_end = request_var('end', 0);

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
// Grab ranks and icons
$ranks = $cache->obtain_ranks();
$icons = $cache->obtain_icons();

/**
* build news-list
*/
$sql_array['SELECT'] = 't.*, f.*, p.*, s.*, u.*, z.*';
$sql_array['FROM'] = array(TOPICS_TABLE => 't');
$sql_array['LEFT_JOIN'] = array();

$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(FORUMS_TABLE => 'f'),
	'ON'	=> 't.forum_id = f.forum_id'
);
$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(POSTS_TABLE => 'p'),
	'ON'	=> 'p.post_id = t.topic_first_post_id'
);
$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(SESSIONS_TABLE => 's'),
	'ON'	=> 'p.poster_id = s.session_user_id'
);
$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(USERS_TABLE => 'u'),
	'ON'	=> 'u.user_id = p.poster_id'
);
$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(ATTACHMENTS_TABLE => 'a'),
	'ON'	=> 'p.post_id = a.post_msg_id'
);
$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(ZEBRA_TABLE => 'z'),
	'ON'	=> 'z.user_id = ' . $user->data['user_id'] . ' AND z.zebra_id = p.poster_id'
);

$sql_array['GROUP_BY'] = 't.topic_id, s.session_user_id';
$sql_array['ORDER_BY'] = 'p.post_time DESC';
$sql_array['WHERE'] = $db->sql_in_set('t.forum_id', $forum_ary) . " AND t.forum_id IN ($forums)";
if ($only_news)
{
	$sql_array['WHERE'] .= " AND t.topic_id = $only_news";
}
if ($archiv_start || $archiv_end)
{
	$sql_array['WHERE'] .= " AND p.post_time >= $archiv_start";
	$sql_array['WHERE'] .= " AND p.post_time <= $archiv_end";
}
$sql = $db->sql_build_query('SELECT', $sql_array);

$result = (!$archiv_start) ? $db->sql_query_limit($sql, $config['news_number']) : $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	//set some default vars
	$post_id = $row['post_id'];
	$poster_id = $row['poster_id'];
	$topic_id = $row['topic_id'];
	$forum_id = $row['forum_id'];
	$post_list = $post_edit_list = $attach_list = array();
	$has_attachments = $display_notice = false;
	$news_title = censor_text($row['post_subject']);

	$hide_post = ($row['foe'] && ($view != 'show' || $post_id != $row['post_id'])) ? true : false;
	$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);
	$post_unread = (isset($topic_tracking_info[$row['topic_id']]) && $row['post_time'] > $topic_tracking_info[$row['topic_id']]) ? true : false;

	//parse message for display
	$row['post_text'] = ((utf8_strlen($row['post_text']) > $config['news_char_limit'] + 50) && !$only_news) ? (utf8_substr($row['post_text'], 0, $config['news_char_limit']) . '...') : $row['post_text'];
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
			$l_edited_by = sprintf($l_edit_time_total, $display_username, $user->format_date($row['post_edit_time']), $row['post_edit_count']);
		}
		else
		{
			if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
			{
				$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
			}
			else
			{
				$display_username = get_username_string('full', $row['post_edit_user'], $user_cache[$row['post_edit_user']]['username'], $user_cache[$row['post_edit_user']]['user_colour']);
			}
			$l_edited_by = sprintf($l_edit_time_total, $display_username, $user->format_date($row['post_edit_time']), $row['post_edit_count']);
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
	$row['user_icq'] = (!empty($row['user_icq'])) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '';
	$row['user_icq_status_img'] = (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '';
	$row['user_yim'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
	$row['user_aim'] = ($row['user_aim'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=aim&amp;u=$poster_id") : '';
	$row['user_jabber'] = ($row['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '';

	$template->assign_block_vars('postrow', array(
		'POST_ID'				=> $post_id,
		'S_IGNORE_POST'			=> ($hide_post) ? true : false,
		'L_IGNORE_POST'			=> ($hide_post) ? sprintf($user->lang['POST_BY_FOE'], get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']), '<a href="' . $viewtopic_url . "&amp;p={$row['post_id']}&amp;view=show#p{$row['post_id']}" . '">', '</a>') : '',
		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($row['session_viewonline']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : (($row['session_viewonline']) ? true : false),

		'U_EDIT'				=> (!$user->data['is_registered']) ? '' : ((($user->data['user_id'] == $row['poster_id'] && $auth->acl_get('f_edit', $forum_id) && ($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'])) || $auth->acl_get('m_edit', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
		'U_QUOTE'				=> ($auth->acl_get('f_reply', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_INFO'				=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $user->session_id) : '',
		'U_DELETE'				=> (!$user->data['is_registered']) ? '' : ((($user->data['user_id'] == $row['poster_id'] && $auth->acl_get('f_delete', $forum_id) && $row['topic_last_post_id'] == $row['post_id'] && ($row['post_time'] > time() - ($config['edit_time'] * 60) || !$config['edit_time'])) || $auth->acl_get('m_delete', $forum_id)) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
		'U_REPORT'				=> ($auth->acl_get('f_report', $forum_id)) ? append_sid("{$phpbb_root_path}report.$phpEx", 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
		'U_NOTES'				=> ($auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $user->session_id) : '',
		'U_WARN'				=> ($auth->acl_get('m_warn') && $poster_id != $user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $user->session_id) : '',
		'U_NEWS'				=> append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", 'news=' . $topic_id),

		'POST_ICON_IMG'			=> ($row['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
		'POST_ICON_IMG_WIDTH'	=> ($row['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
		'POST_ICON_IMG_HEIGHT'	=> ($row['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
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

		'S_HAS_ATTACHMENTS'		=> (!empty($attachments[$row['post_id']])) ? true : false,
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
}
$db->sql_freeresult($result);

/**
* build archiv-list
*/
$sql_array = $archiv_years = $archiv_months = array();
$sql_array['SELECT'] = 't.*, p.*';
$sql_array['FROM'] = array(TOPICS_TABLE => 't');
$sql_array['LEFT_JOIN'] = array();

$sql_array['LEFT_JOIN'][] = array(
	'FROM'	=> array(POSTS_TABLE => 'p'),
	'ON'	=> 'p.post_id = t.topic_first_post_id'
);

$sql_array['ORDER_BY'] = 'p.post_time DESC';
$sql_array['WHERE'] = $db->sql_in_set('t.forum_id', $forum_ary) . " AND t.forum_id IN ($forums)";
$sql = $db->sql_build_query('SELECT', $sql_array);

$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
{
	$archiv_year = $user->format_date($row['post_time'], 'Y');
	if (!in_array($archiv_year, $archiv_years))
	{
		$archiv_years[] = $archiv_year;
	}
	$archiv_month = $user->format_date($row['post_time'], 'F Y');
	if (!in_array($archiv_month, $archiv_months))
	{
		$archiv_months[] = $archiv_month;
		$archiv_months[$archiv_year][$archiv_month]['name'] = $archiv_month;
		$archiv_months[$archiv_year][$archiv_month]['start'] = $row['post_time'];
		$archiv_months[$archiv_year][$archiv_month]['end'] = $row['post_time'];
	}
	else
	{
		$archiv_months[$archiv_year][$archiv_month]['start'] = $row['post_time'];
	}
}
$db->sql_freeresult($result);
foreach ($archiv_years as $archive_year)
{
	$template->assign_block_vars('archive_block', array(
		'NEWS_YEAR'		=> $archive_year,
	));
	foreach ($archiv_months[$archive_year] as $archive_month)
	{
		$template->assign_block_vars('archive_block.archive_row', array(
			'U_NEWS_MONTH'		=> append_sid("{$phpbb_root_path}{$newspage_file}.$phpEx", 'start=' . $archive_month['start'] . '&amp;end=' . $archive_month['end']),
			'NEWS_MONTH'		=> $archive_month['name'],
		));
	}
}
$template->assign_vars(array(
	'QUOTE_IMG' 			=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
	'EDIT_IMG' 				=> $user->img('icon_post_edit', 'EDIT_POST'),
	'DELETE_IMG' 			=> $user->img('icon_post_delete', 'DELETE_POST'),
	'INFO_IMG' 				=> $user->img('icon_post_info', 'VIEW_INFO'),
	'PROFILE_IMG'			=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'SEARCH_IMG' 			=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
	'PM_IMG' 				=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'EMAIL_IMG' 			=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'WWW_IMG' 				=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
	'ICQ_IMG' 				=> $user->img('icon_contact_icq', 'ICQ'),
	'AIM_IMG' 				=> $user->img('icon_contact_aim', 'AIM'),
	'MSN_IMG' 				=> $user->img('icon_contact_msnm', 'MSNM'),
	'YIM_IMG' 				=> $user->img('icon_contact_yahoo', 'YIM'),
	'JABBER_IMG'			=> $user->img('icon_contact_jabber', 'JABBER') ,
	'REPORT_IMG'			=> $user->img('icon_post_report', 'REPORT_POST'),
	'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'WARN_IMG'				=> $user->img('icon_user_warn', 'WARN_USER'),
	'NEWS_USER_INFO'		=> $config['news_user_info'],
	'NEWS_POST_BUTTONS'		=> $config['news_post_buttons'],
	'NEWS_ONLY'				=> $only_news,
	'NEWS_TITLE'			=> $news_title,
));

page_header($user->lang['NEWS']);

$template->set_filenames(array(
	'body' => 'newspage_body.html')
);

page_footer();

?>