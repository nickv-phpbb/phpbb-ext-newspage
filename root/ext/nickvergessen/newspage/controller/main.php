<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2013 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_ext_nickvergessen_newspage_controller_main
{
	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param phpbb_auth		$auth		Auth object
	* @param phpbb_cache_service	$cache		Cache object
	* @param phpbb_config	$config		Config object
	* @param phpbb_db_driver	$db		Database object
	* @param phpbb_request	$request	Request object
	* @param phpbb_template	$template	Template object
	* @param phpbb_user		$user		User object
	* @param phpbb_controller_helper		$helper		Controller helper object
	* @param string			$root_path	phpBB root path
	* @param string			$php_ext	phpEx
	*/
	public function __construct(phpbb_auth $auth, phpbb_cache_service $cache, phpbb_config $config, phpbb_db_driver $db, phpbb_request $request, phpbb_template $template, phpbb_user $user, phpbb_controller_helper $helper, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('bbcode'))
		{
			include($this->root_path . 'includes/bbcode.' . $this->php_ext);
		}
		if (!function_exists('get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
	}

	/**
	* Base controller to be accessed with the URL /newspage/{page}
	* (where {page} is the placeholder for a value)
	*
	* @param int	$page	Page number taken from the URL
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base($page = null)
	{
		if (is_null($page))
		{
			$start = min(($this->config['news_pages'] * $this->config['news_number']), $this->request->variable('start', 0));
		}
		else
		{
			$start = min($this->config['news_pages'], $page) * $this->config['news_number'];
		}

		$limit_category = $this->request->variable('f', 0);
		$limit_news = $this->request->variable('news', 0);
		$is_archive = $this->request->variable('archive', '');
		$page_title = $this->user->lang['NEWS'];

		if ($this->config['news_cat_show'])
		{
			$this->generate_category_list();
		}

		$sql_array = array(
			'SELECT'		=> 'forum_id, topic_id, topic_type, topic_poster, topic_first_post_id',
			'FROM'			=> array(TOPICS_TABLE => 't'),
			'WHERE'			=> $this->db->sql_in_set('forum_id', $this->get_forums($limit_category), false, true),
			'ORDER_BY'		=> 'topic_time DESC',
		);

		if ($limit_news)
		{
			$sql_array['WHERE'] .= ' AND topic_id = ' . $limit_news;
			$sql_array['ORDER_BY'] = '';
		}
		else if ($is_archive && preg_match("/(0[1-9]|1[0-2])_(19[7-9][0-9]|20([0-2][0-9]|3[0-7]))/", $is_archive))
		{
			list($archive_month, $archive_year) = explode('_', $is_archive);

			$archive_start = gmmktime(0, 0, 0, (int) $archive_month, 1, (int) $archive_year);
			$archive_start = $archive_start - $this->user->timezone;

			$archive_end = gmmktime(0, 0, 0, (int) $archive_month + 1, 1, (int) $archive_year);
			$archive_end = $archive_end - $this->user->timezone;

			$archive_name = sprintf($this->user->lang['NEWS_ARCHIVE_OF'], $this->user->format_date($archive_start, 'F Y'));

			$page_title = $archive_name;
			$sql_array['WHERE'] .= ' AND topic_time >= ' . (int) $archive_start . ' AND topic_time <= ' . (int) $archive_end;
			$sql_array['ORDER_BY'] = 'topic_time ASC';
		}
		else if ($is_archive)
		{
			$is_archive = '';
		}

		if (!$limit_news && $this->config['news_shadow'])
		{
			$sql_array['WHERE'] .= ' AND topic_moved_id = 0';
		}

		/**
		* Select topic_ids for the news
		*/
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		if ($limit_news)
		{
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$result = $this->db->sql_query_limit($sql, $this->config['news_number'], $start);
		}

		$forums = $topic_ids = $post_ids = $topic_posters = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = $row['topic_first_post_id'];
			$topic_ids[] = $row['topic_id'];
			$topic_posters[] = $row['topic_poster'];
			$forums[$row['forum_id']][] = $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($topic_ids))
		{
			// Abort, no news found, dont waste time in here!
			$this->generate_archive_list($limit_category, $is_archive);

			$l_no_news = ($is_archive) ? $this->user->lang['NO_NEWS_ARCHIVE'] : (($limit_category) ? $this->user->lang['NO_NEWS_CATEGORY'] : $this->user->lang['NO_NEWS']);
			$this->template->assign_var('L_NO_NEWS', $l_no_news);

			return $this->helper->render('newspage_body.html', $page_title);
		}

		// Grab ranks and icons
		$ranks = $this->cache->obtain_ranks();
		$icons = $this->cache->obtain_icons();

		// Get topic tracking
		$topic_ids_ary = $topic_ids;
		foreach ($forums as $forum_id => $topic_ids)
		{
			$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $topic_ids);
		}
		$topic_ids = $topic_ids_ary;

		// Get user online-status
		$user_online_tracking_info = array();
		$sql = 'SELECT session_user_id
			FROM ' . SESSIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('session_user_id', $topic_posters, false, true) . '
				AND session_user_id <> ' . ANONYMOUS . '
				AND session_viewonline = 1';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_online_tracking_info[] = $row['session_user_id'];
		}
		$this->db->sql_freeresult($result);


		// Get attachments
		$attachments = array();
		if (sizeof($post_ids) && $this->config['news_attach_show'])
		{
			if ($this->auth->acl_get('u_download'))
			{
				$sql = 'SELECT *
					FROM ' . ATTACHMENTS_TABLE . '
					WHERE ' . $this->db->sql_in_set('post_msg_id', $post_ids) . '
						AND in_message = 0
					ORDER BY filetime DESC, post_msg_id ASC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$attachments[$row['post_msg_id']][] = $row;
				}
				$this->db->sql_freeresult($result);
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
			'ORDER_BY'	=> 't.topic_time ' . (($is_archive) ? 'ASC' : 'DESC'),
			'WHERE'		=> $this->db->sql_in_set('t.topic_id', $topic_ids, false, true),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Set some variables
			$post_id = $row['post_id'];
			$poster_id = $row['poster_id'];
			$topic_id = $row['topic_id'];
			$forum_id = $row['forum_id'];
			$post_list = $post_edit_list = array();
			$display_notice = false;

			if ($limit_news)
			{
				$page_title = censor_text($row['post_subject']);
			}

			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

			//parse message for display
			if (!$limit_news)
			{
				/**
				* The BBCode engine is not yet finished, so currently we just ignore the cool shortening of
				* the trim message tool and hope, that the new engine supports a substr() on parsed texts.
				*
				$trim = new phpbb_trim_message($row['post_text'], $row['bbcode_uid'], $this->config['news_char_limit']);
				$row['post_text'] = $trim->message();
				unset($trim);
				*/
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

			if (!$this->auth->acl_get('f_download', $forum_id))
			{
				$display_notice = true;
			}
			else if (!empty($attachments[$row['post_id']]))
			{
				parse_attachments($forum_id, $message, $attachments[$row['post_id']], $update_count);
			}

			$row['post_text'] = $message;

			// Edit Information
			if (($row['post_edit_count'] && $this->config['display_last_edited']) || $row['post_edit_reason'])
			{
				// Get usernames for all following posts if not already stored
				if (!sizeof($post_edit_list) && ($row['post_edit_reason'] || ($row['post_edit_user'] && !isset($user_cache[$row['post_edit_user']]))))
				{
					$sql2 = 'SELECT DISTINCT u.user_id, u.username, u.user_colour
						FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
						WHERE p.post_edit_count <> 0
							AND p.post_edit_user <> 0
							AND p.post_edit_user = u.user_id';
					$result2 = $this->db->sql_query($sql2);
					while ($user_edit_row = $this->db->sql_fetchrow($result2))
					{
						$post_edit_list[$user_edit_row['user_id']] = $user_edit_row;
					}
					$this->db->sql_freeresult($result2);
				}

				$l_edit_time_total = ($row['post_edit_count'] == 1) ? $this->user->lang['EDITED_TIME_TOTAL'] : $this->user->lang['EDITED_TIMES_TOTAL'];
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
					$l_edited_by = sprintf($l_edit_time_total, $display_username, $this->user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
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
					$l_edited_by = sprintf($l_edit_time_total, $display_username, $this->user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
				}
			}
			else
			{
				$l_edited_by = '';
			}
			$flags = (($row['enable_bbcode']) ? 1 : 0) + (($row['enable_smilies']) ? 2 : 0) + (($row['enable_magic_url']) ? 4 : 0);
			$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);

			get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);
			$row['user_email'] = ((!empty($row['user_allow_viewemail']) || $this->auth->acl_get('a_email')) && ($row['user_email'] != '')) ? ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.{$this->php_ext}", "mode=email&amp;u=$poster_id") : (($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']) : '';
			$row['user_msnm'] = ($row['user_msnm'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=msnm&amp;u=$poster_id") : '';
			$row['user_icq'] = (!empty($row['user_icq'])) ? 'http://www.icq.com/people/' . urlencode($row['user_icq']) . '/' : '';
			$row['user_icq_status_img'] = (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '';
			$row['user_yim'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
			$row['user_aim'] = ($row['user_aim'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=aim&amp;u=$poster_id") : '';
			$row['user_jabber'] = ($row['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '';

			$this->template->assign_block_vars('postrow', array(
				'POST_ID'				=> $post_id,
				'S_IGNORE_POST'			=> false,
				'L_IGNORE_POST'			=> '',
				'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$this->config['load_onlinetrack']) ? '' : ((in_array($poster_id, $user_online_tracking_info)) ? $this->user->img('icon_user_online', 'ONLINE') : $this->user->img('icon_user_offline', 'OFFLINE')),
				'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$this->config['load_onlinetrack']) ? false : ((in_array($poster_id, $user_online_tracking_info)) ? true : false),

				'U_EDIT'				=> (!$this->user->data['is_registered']) ? '' : ((($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('f_edit', $forum_id) && ($row['post_time'] > time() - ($this->config['edit_time'] * 60) || !$this->config['edit_time'])) || $this->auth->acl_get('m_edit', $forum_id)) ? append_sid("{$phpbb_root_path}posting.{$this->php_ext}", "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
				'U_QUOTE'				=> ($this->auth->acl_get('f_reply', $forum_id)) ? append_sid("{$phpbb_root_path}posting.{$this->php_ext}", "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
				'U_INFO'				=> ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.{$this->php_ext}", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $this->user->session_id) : '',
				'U_DELETE'				=> (!$this->user->data['is_registered']) ? '' : ((($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('f_delete', $forum_id) && $row['topic_last_post_id'] == $row['post_id'] && ($row['post_time'] > time() - ($this->config['edit_time'] * 60) || !$this->config['edit_time'])) || $this->auth->acl_get('m_delete', $forum_id)) ? append_sid("{$phpbb_root_path}posting.{$this->php_ext}", "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
				'U_REPORT'				=> ($this->auth->acl_get('f_report', $forum_id)) ? append_sid("{$phpbb_root_path}report.{$this->php_ext}", 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
				'U_NOTES'				=> ($this->auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $this->user->session_id) : '',
				'U_WARN'				=> ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.{$this->php_ext}", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '',
				//@todo: 'U_NEWS'				=> $this->helper->url('newspage', 'news=' . $topic_id),
				'U_NEWS'				=> $this->helper->url(array('newspage'), 'news=' . $topic_id),

				'POST_ICON_IMG'			=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
				'POST_ICON_IMG_WIDTH'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
				'POST_ICON_IMG_HEIGHT'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
				'U_MINI_POST'			=> append_sid("{$phpbb_root_path}viewtopic.{$this->php_ext}", 'p=' . $row['post_id']) . (($row['topic_type'] == POST_GLOBAL) ? '&amp;f=' . $forum_id : '') . '#p' . $row['post_id'],
				'POST_SUBJECT'			=> censor_text($row['post_subject']),
				'MINI_POST_IMG'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'NEW_POST') : $this->user->img('icon_post_target', 'POST'),
				'POST_AUTHOR_FULL'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'POST_DATE'				=> $this->user->format_date($row['post_time']),

				'S_POST_UNAPPROVED'		=> ($row['post_approved']) ? false : true,
				'S_POST_REPORTED'		=> ($row['post_reported'] && $this->auth->acl_get('m_report', $forum_id)) ? true : false,
				'U_MCP_REPORT'			=> ($this->auth->acl_get('m_report', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.{$this->php_ext}", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
				'U_MCP_APPROVE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.{$this->php_ext}", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',

				'MESSAGE'				=> $row['post_text'],

				'S_HAS_POLL'			=> (!empty($row['poll_start'])) ? true : false,
				'POLL_QUESTION'			=> $row['poll_title'],
				'S_HAS_ATTACHMENTS'		=> (!empty($attachments[$row['post_id']]) && $this->config['news_attach_show']) ? true : false,
				'S_DISPLAY_NOTICE'		=> $display_notice && $row['post_attachment'],
				'EDITED_MESSAGE'		=> $l_edited_by,
				'EDIT_REASON'			=> $row['post_edit_reason'],
				'SIGNATURE'				=> ($row['enable_sig']) ? $row['user_sig'] : '',
				'NEWS_COMMENTS'			=> $row['topic_replies'],

				'POSTER_AVATAR'			=> ($this->user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'U_POST_AUTHOR'			=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'RANK_TITLE'			=> $row['rank_title'],
				'RANK_IMG'				=> $row['rank_image'],
				'RANK_IMG_SRC'			=> $row['rank_image_src'],
				'POSTER_POSTS'			=> $row['user_posts'],
				'POSTER_JOINED'			=> $this->user->format_date($row['user_regdate']),
				'POSTER_FROM'			=> $row['user_from'],

				'U_PM'					=> ($poster_id != ANONYMOUS && $this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_'))) ? append_sid("{$phpbb_root_path}ucp.{$this->php_ext}", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']) : '',
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
					$this->template->assign_block_vars('postrow.attachment', array(
						'DISPLAY_ATTACHMENT'	=> $attachment,
					));
				}
			}
		}
		$this->db->sql_freeresult($result);

		// Build archiv-list
		$total_news = $this->generate_archive_list($limit_category, $is_archive);

		// Specify some images
		$this->template->assign_vars(array(
			'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', 'POST_REPORTED'),
			'UNAPPROVED_IMG'		=> $this->user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),

			'NEWS_USER_INFO'			=> $this->config['news_user_info'],
			'NEWS_POST_BUTTONS'			=> $this->config['news_post_buttons'],
			'S_NEWS_ARCHIVE_PER_YEAR'	=> $this->config['news_archive_per_year'],

			'NEWS_ONLY'					=> $limit_news,
			'NEWS_TITLE'				=> $page_title,
		));

		if ($this->config['news_user_info'])
		{
			$this->template->assign_vars(array(
				'PROFILE_IMG'		=> $this->user->img('icon_user_profile', 'READ_PROFILE'),
				'SEARCH_IMG'		=> $this->user->img('icon_user_search', 'SEARCH_USER_POSTS'),
				'PM_IMG'			=> $this->user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
				'EMAIL_IMG'			=> $this->user->img('icon_contact_email', 'SEND_EMAIL'),
				'WWW_IMG'			=> $this->user->img('icon_contact_www', 'VISIT_WEBSITE'),
				'ICQ_IMG'			=> $this->user->img('icon_contact_icq', 'ICQ'),
				'AIM_IMG'			=> $this->user->img('icon_contact_aim', 'AIM'),
				'MSN_IMG'			=> $this->user->img('icon_contact_msnm', 'MSNM'),
				'YIM_IMG'			=> $this->user->img('icon_contact_yahoo', 'YIM'),
				'JABBER_IMG'		=> $this->user->img('icon_contact_jabber', 'JABBER'),
			));
		}

		if ($this->config['news_post_buttons'])
		{
			$this->template->assign_vars(array(
				'QUOTE_IMG'			=> $this->user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
				'EDIT_IMG'			=> $this->user->img('icon_post_edit', 'EDIT_POST'),
				'DELETE_IMG'		=> $this->user->img('icon_post_delete', 'DELETE_POST'),
				'INFO_IMG'			=> $this->user->img('icon_post_info', 'VIEW_INFO'),
				'REPORT_IMG'		=> $this->user->img('icon_post_report', 'REPORT_POST'),
				'WARN_IMG'			=> $this->user->img('icon_user_warn', 'WARN_USER'),
			));
		}

		if (!$limit_news)
		{
			$this->generate_pagination($total_news, $start, $is_archive, $limit_category);
		}

		/*
		* The render method takes up to three other arguments
		* @param	string		Name of the template file to display
		*						Template files are searched for two places:
		*						- phpBB/styles/<style_name>/template/
		*						- phpBB/ext/<all_active_extensions>/styles/<style_name>/template/
		* @param	string		Page title
		* @param	int			Status code of the page (200 - OK [ default ], 403 - Unauthorized, 404 - Page not found)
		*/
		return $this->helper->render('newspage_body.html', $page_title);
	}

	/**
	* Get a list of the forums we use for the newspage
	*
	* @param	int		$limit_category		Limit the list to a given category
	* @return	array		Array with the forum ids that should be taken into account
	*/
	protected function get_forums($limit_category = 0)
	{
		static $_forums;


		if (isset($_forums[$limit_category]))
		{
			return $_forums[$limit_category];
		}

		$forums = array_map('intval', explode(',', ($this->config['news_forums'] ?: '0')));

		$forum_read_ary = array();

		// Do not include those forums the user is not having read access to...
		$forum_ary = $this->auth->acl_getf('f_read');
		foreach ($forum_ary as $forum_id => $allowed)
		{
			if ($allowed['f_read'])
			{
				$forum_read_ary[] = (int) $forum_id;
			}
		}

		// Remove duplicates
		$forum_read_ary = array_unique($forum_read_ary);

		// Now only keep the forums that are in "allowed to read", "news forum" list
		$_forums[$limit_category] = array_intersect($forum_read_ary, $forums);

		if ($limit_category)
		{
			$_forums[$limit_category] = array_intersect($_forums[$limit_category], array($limit_category));
		}

		return $_forums[$limit_category];
	}

	/**
	* Generate the category list of the news forums and populate it into the template
	*/
	protected function generate_category_list()
	{
		$sql = 'SELECT forum_id, forum_name, forum_topics
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forums(), false, true) . '
				AND forum_topics <> 0
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('cat_block', array(
				//@todo: 'U_NEWS_CAT'		=> $this->helper->url('newspage', 'f=' . $row['forum_id']),
				'U_NEWS_CAT'		=> $this->helper->url(array('newspage'), 'f=' . $row['forum_id']),
				'NEWS_CAT'			=> $row['forum_name'],
				'NEWS_COUNT'		=> $row['forum_topics'],
			));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Generate the achrive list of the news forums and populate it into the template
	*
	* @param	int		$limit_category	Limit the list to a given category
	* @param	string	$is_archive		Limit the total news count to a given archive
	* @return	int		Return the number of total news for the pagination
	*/
	protected function generate_archive_list($limit_category, $is_archive = '')
	{
		$archiv_years = $archiv_months = $checked_months = array();
		$sql = 'SELECT topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forums($limit_category), false, true) . '
			ORDER BY topic_time DESC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$month_name = $this->user->format_date($row['topic_time'], 'F');
			$archiv_year = $this->user->format_date($row['topic_time'], 'Y');

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
				'url'	=> $this->user->format_date($row['topic_time'], 'm_Y'),
				'name'	=> $month_name,
				'count'	=> 1,
			);
		}
		$this->db->sql_freeresult($result);

		$total_news = 0;
		foreach ($archiv_years as $archive_year)
		{
			$this->template->assign_block_vars('archive_block', array(
				'NEWS_YEAR'		=> $archive_year,
			));
			foreach ($archiv_months[$archive_year] as $archive_month)
			{
				$this->template->assign_block_vars('archive_block.archive_row', array(
					//@todo: 'U_NEWS_MONTH'		=> $this->helper->url('newspage', 'archive=' . $archive_month['url'] . (($limit_category && !empty($this->config['news_cat_show'])) ? "&amp;f=$limit_category" : '')),
					'U_NEWS_MONTH'		=> $this->helper->url(array('newspage'), 'archive=' . $archive_month['url'] . (($limit_category && !empty($this->config['news_cat_show'])) ? "&amp;f=$limit_category" : '')),
					'NEWS_MONTH'		=> $archive_month['name'],
					'NEWS_COUNT'		=> $archive_month['count'],
				));

				if (($is_archive == $archive_month['url']) || !$is_archive)
				{
					$total_news += $archive_month['count'];
				}
			}
		}

		return $total_news;
	}

	/**
	* Generate the pagination for the news list
	*
	* @param	int		$num_news		Number of total news
	* @param	int		$start			Start argument of current page
	* @param	string	$is_archive		Append archive to pagination url if required
	* @param	int		$limit_category	Limit the list to a given forum
	* @return	null
	*/
	protected function generate_pagination($num_news, $start, $is_archive = '', $limit_category = 0)
	{
		$base_url_params = array();

		if (!$is_archive)
		{
			$max_num_news = $this->config['news_pages'] * $this->config['news_number'];
			$pagination_news = min($max_num_news, $num_news);
		}
		else
		{
			$pagination_news = $num_news;
			$base_url_params['archive'] = $is_archive;
		}

		if ($limit_category)
		{
			$base_url_params['f'] = $limit_category;
		}

		//@todo: $base_url = $this->helper->url('newspage', $base_url_params);
		$base_url = $this->helper->url(array('newspage'), $base_url_params);
		phpbb_generate_template_pagination($this->template, $base_url, 'pagination', 'start', $pagination_news, $this->config['news_number'], $start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> phpbb_on_page($this->template, $this->user, $base_url, $pagination_news, $this->config['news_number'], $start),
			'TOTAL_NEWS'		=> $this->user->lang('VIEW_NEWS_POSTS', $num_news),
		));
	}
}