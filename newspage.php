<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2013 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage;

class newspage
{
	protected $num_pagination_items = 0;

	protected $page_title;

	protected $start;

	protected $news;

	protected $archive;

	protected $category;

	const ARCHIVE_SHOW = 1;
	const ARCHIVE_PER_YEAR = 2;

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param \phpbb\auth		$auth		Auth object
	* @param \phpbb\cache\service	$cache		Cache object
	* @param \phpbb\config	$config		Config object
	* @param \phpbb\db\driver	$db		Database object
	* @param \phpbb\request	$request	Request object
	* @param \phpbb\template	$template	Template object
	* @param \phpbb\user		$user		User object
	* @param \phpbb\content_visibility		$content_visibility	Content visibility object
	* @param \phpbb\controller\helper		$helper				Controller helper object
	* @param string			$root_path	phpBB root path
	* @param string			$php_ext	phpEx
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\content_visibility $content_visibility, \phpbb\controller\helper $helper, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->content_visibility = $content_visibility;
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
		$this->page_title = $this->user->lang['NEWS'];
	}

	public function get_page_title()
	{
		return $this->page_title;
	}

	public function set_start($start)
	{
		$this->start = (int) $start;

		return $this;
	}

	public function set_category($category)
	{
		$this->category = (int) $category;

		return $this;
	}

	public function set_news($news)
	{
		$this->news = (int) $news;

		return $this;
	}

	public function set_archive($year, $month)
	{
		$this->archive = array(
			'y'	=> (int) $year,
			'm'	=> (int) $month,
		);

		return $this;
	}

	public function is_archive()
	{
		return $this->archive['y'] !== 0 && $this->archive['m'] !== 0;
	}

	public function get_news_ids()
	{
		/**
		* Select topic_ids for the news
		*/
		$sql = $this->db->sql_build_query('SELECT', $this->get_newstopic_sql());
		if ($this->news)
		{
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$result = $this->db->sql_query_limit($sql, $this->config['news_number'], $this->start);
		}

		$this->forums = $this->topic_ids = $this->post_ids = $this->poster_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->post_ids[] = (int) $row['topic_first_post_id'];
			$this->topic_ids[] = (int) $row['topic_id'];
			$this->poster_ids[] = (int) $row['topic_poster'];
			$this->forums[(int) $row['forum_id']][] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);
	}

	public function base()
	{
		$this->get_news_ids();

		if (empty($this->topic_ids))
		{
			$l_no_news = ($this->is_archive()) ? 'NO_NEWS_ARCHIVE' : (($this->category) ? 'NO_NEWS_CATEGORY' : 'NO_NEWS');
			$this->template->assign_var('L_NO_NEWS', $this->user->lang($l_no_news));

			return;
		}

		// Grab ranks, icons, online-status and attachments
		$ranks = $this->cache->obtain_ranks();
		$icons = $this->cache->obtain_icons();
		$user_online_tracking_info = (!empty($this->poster_ids)) ? $this->get_online_posters($this->poster_ids) : array();
		$attachments = (!empty($this->post_ids)) ? $this->get_attachments($this->post_ids) : array();

		// Get topic tracking
		foreach ($this->forums as $forum_id => $topic_ids)
		{
			$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $topic_ids);
		}

		$sql_array = array(
			'SELECT'	=> 't.*, p.*, u.*',
			'FROM'		=> array(TOPICS_TABLE => 't'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(POSTS_TABLE => 'p'),
					'ON'	=> 'p.post_id = t.topic_first_post_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = p.poster_id',
				),
			),
			'ORDER_BY'	=> 't.topic_time ' . (($this->is_archive()) ? 'ASC' : 'DESC'),
			'WHERE'		=> $this->db->sql_in_set('t.topic_id', $this->topic_ids),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Set some variables
			$post_id = (int) $row['post_id'];
			$poster_id = (int) $row['poster_id'];
			$topic_id = (int) $row['topic_id'];
			$forum_id = (int) $row['forum_id'];
			$post_list = $post_edit_list = array();
			$display_notice = false;


			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

			//parse message for display
			if ($this->news)
			{
				$this->page_title = censor_text($row['post_subject']);
			}
			else if (class_exists('\nickvergessen\trimmessage\trim_message'))
			{
				/**
				* The BBCode engine is not yet finished, so currently we just ignore the cool shortening of
				* the trim message tool and hope, that the new engine supports a substr() on parsed texts.
				*/
				$trim = new \nickvergessen\trimmessage\trim_message($row['post_text'], $row['bbcode_uid'], $this->config['news_char_limit']);
				$row['post_text'] = $trim->message();
				unset($trim);
			}

			$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$message = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);

			if (!$this->auth->acl_get('f_download', $forum_id))
			{
				$display_notice = true;
			}
			else if (!empty($attachments[$post_id]))
			{
				parse_attachments($forum_id, $message, $attachments[$post_id], $update_count);
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

				if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
				{
					$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
				}
				else
				{
					$display_username = get_username_string('full', $row['post_edit_user'], $post_edit_list[$row['post_edit_user']]['username'], $post_edit_list[$row['post_edit_user']]['user_colour']);
				}

				if ($row['post_edit_reason'])
				{
					$l_edited_by = sprintf($l_edit_time_total, $display_username, $this->user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
				}
				else
				{
					$l_edited_by = sprintf($l_edit_time_total, $display_username, $this->user->format_date($row['post_edit_time'], false, true), $row['post_edit_count']);
				}
			}
			else
			{
				$l_edited_by = '';
			}

			$flags = ($row['user_sig_bbcode_bitfield'] && $row['enable_bbcode'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], $flags);

			get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);
			$row['user_email'] = ((!empty($row['user_allow_viewemail']) || $this->auth->acl_get('a_email')) && ($row['user_email'] != '')) ? ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=email&amp;u=$poster_id") : (($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']) : '';
			$row['user_msnm'] = ($row['user_msnm'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=msnm&amp;u=$poster_id") : '';
			$row['user_icq'] = (!empty($row['user_icq'])) ? 'http://www.icq.com/people/' . urlencode($row['user_icq']) . '/' : '';
			$row['user_icq_status_img'] = (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '';
			$row['user_yim'] = ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg' : '';
			$row['user_aim'] = ($row['user_aim'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=aim&amp;u=$poster_id") : '';
			$row['user_jabber'] = ($row['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '';

			$this->template->assign_block_vars('postrow', array(
				'POST_ID'				=> $post_id,
				'S_IGNORE_POST'			=> false,
				'L_IGNORE_POST'			=> '',
				'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$this->config['load_onlinetrack']) ? '' : ((in_array($poster_id, $user_online_tracking_info)) ? $this->user->img('icon_user_online', 'ONLINE') : $this->user->img('icon_user_offline', 'OFFLINE')),
				'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$this->config['load_onlinetrack']) ? false : ((in_array($poster_id, $user_online_tracking_info)) ? true : false),

				'U_EDIT'				=> (!$this->user->data['is_registered']) ? '' : ((($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('f_edit', $forum_id) && ($row['post_time'] > time() - ($this->config['edit_time'] * 60) || !$this->config['edit_time'])) || $this->auth->acl_get('m_edit', $forum_id)) ? append_sid("{$this->root_path}posting.{$this->php_ext}", "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
				'U_QUOTE'				=> ($this->auth->acl_get('f_reply', $forum_id)) ? append_sid("{$this->root_path}posting.{$this->php_ext}", "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
				'U_INFO'				=> ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $this->user->session_id) : '',
				'U_DELETE'				=> (!$this->user->data['is_registered']) ? '' : ((($this->user->data['user_id'] == $row['poster_id'] && $this->auth->acl_get('f_delete', $forum_id) && $row['topic_last_post_id'] == $row['post_id'] && ($row['post_time'] > time() - ($this->config['edit_time'] * 60) || !$this->config['edit_time'])) || $this->auth->acl_get('m_delete', $forum_id)) ? append_sid("{$this->root_path}posting.{$this->php_ext}", "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : ''),
				'U_REPORT'				=> ($this->auth->acl_get('f_report', $forum_id)) ? append_sid("{$this->root_path}report.{$this->php_ext}", 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
				'U_NOTES'				=> ($this->auth->acl_getf_global('m_')) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $this->user->session_id) : '',
				'U_WARN'				=> ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $post_id, true, $this->user->session_id) : '',
				'U_NEWS'				=> $this->helper->url('news/' . $topic_id),

				'POST_ICON_IMG'			=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
				'POST_ICON_IMG_WIDTH'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
				'POST_ICON_IMG_HEIGHT'	=> (!empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
				'U_MINI_POST'			=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'p=' . $row['post_id']) . (($row['topic_type'] == POST_GLOBAL) ? '&amp;f=' . $forum_id : '') . '#p' . $row['post_id'],
				'POST_SUBJECT'			=> censor_text($row['post_subject']),
				'MINI_POST_IMG'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'NEW_POST') : $this->user->img('icon_post_target', 'POST'),
				'POST_AUTHOR_FULL'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'POST_DATE'				=> $this->user->format_date($row['post_time']),

				'S_POST_UNAPPROVED'		=> $row['post_visibility'] == ITEM_UNAPPROVED,
				'S_POST_REPORTED'		=> ($row['post_reported'] && $this->auth->acl_get('m_report', $forum_id)) ? true : false,
				'U_MCP_REPORT'			=> ($this->auth->acl_get('m_report', $forum_id)) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
				'U_MCP_APPROVE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',

				'MESSAGE'				=> $row['post_text'],

				'S_HAS_POLL'			=> (!empty($row['poll_start'])) ? true : false,
				'POLL_QUESTION'			=> $row['poll_title'],
				'S_HAS_ATTACHMENTS'		=> (!empty($attachments[$row['post_id']]) && $this->config['news_attach_show']) ? true : false,
				'S_DISPLAY_NOTICE'		=> $display_notice && $row['post_attachment'],
				'EDITED_MESSAGE'		=> $l_edited_by,
				'EDIT_REASON'			=> $row['post_edit_reason'],
				'SIGNATURE'				=> ($row['enable_sig']) ? $row['user_sig'] : '',
				'NEWS_COMMENTS'			=> $this->content_visibility->get_count('topic_posts', $row, $forum_id) - 1,

				'POSTER_AVATAR'			=> ($this->user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
				'U_POST_AUTHOR'			=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'RANK_TITLE'			=> $row['rank_title'],
				'RANK_IMG'				=> $row['rank_image'],
				'RANK_IMG_SRC'			=> $row['rank_image_src'],
				'POSTER_POSTS'			=> $row['user_posts'],
				'POSTER_JOINED'			=> $this->user->format_date($row['user_regdate']),
				'POSTER_FROM'			=> $row['user_from'],

				'U_PM'					=> ($poster_id != ANONYMOUS && $this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_'))) ? append_sid("{$this->root_path}ucp.{$this->php_ext}", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']) : '',
				'U_EMAIL'				=> $row['user_email'],
				'U_WWW'					=> $row['user_website'],
				'U_MSN'					=> $row['user_msnm'],
				'U_ICQ'					=> $row['user_icq'],
				'U_YIM'					=> $row['user_yim'],
				'U_AIM'					=> $row['user_aim'],
				'U_JABBER'				=> $row['user_jabber'],
			));

			// Display not already displayed Attachments for this post, we already parsed them. ;)
			if (!empty($attachments[$post_id]))
			{
				foreach ($attachments[$post_id] as $attachment)
				{
					$this->template->assign_block_vars('postrow.attachment', array(
						'DISPLAY_ATTACHMENT'	=> $attachment,
					));
				}
			}
		}
		$this->db->sql_freeresult($result);

		// Specify some images
		$this->template->assign_vars(array(
			'NEWS_USER_INFO'			=> $this->config['news_user_info'],
			'NEWS_POST_BUTTONS'			=> $this->config['news_post_buttons'],
			'S_NEWS_ARCHIVE_PER_YEAR'	=> $this->config['news_archive_show'] == self::ARCHIVE_PER_YEAR,

			'NEWS_ONLY'					=> $this->news,
			'NEWS_TITLE'				=> $this->get_page_title(),
		));

		return;
	}

	public function get_online_posters(array $posters)
	{
		$users = array();
		$sql = 'SELECT session_user_id
			FROM ' . SESSIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('session_user_id', $posters) . '
				AND session_user_id <> ' . ANONYMOUS . '
				AND session_viewonline = 1';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = $row['session_user_id'];
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	public function get_attachments(array $post_ids)
	{
		$attachments = array();

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

		return $attachments;
	}

	public function get_newstopic_sql()
	{
		$sql_array = array(
			'SELECT'		=> 'forum_id, topic_id, topic_type, topic_poster, topic_first_post_id',
			'FROM'			=> array(TOPICS_TABLE => 't'),
			'WHERE'			=> $this->db->sql_in_set('forum_id', $this->get_forums($this->category), false, true) . '
				AND topic_visibility = ' . ITEM_APPROVED,
			'ORDER_BY'		=> 'topic_time DESC',
		);

		if ($this->news)
		{
			$sql_array['WHERE'] .= ' AND topic_id = ' . $this->news;
			$sql_array['ORDER_BY'] = '';
		}
		else if ($this->is_archive())
		{
			$archive_start = $this->user->get_timestamp_from_format('Y-n-d H:i:s', $this->archive['y'] . '-' . $this->archive['m'] . '-01 0:00:00');
			$archive_end = $this->user->get_timestamp_from_format('Y-n-d H:i:s', $this->archive['y'] . '-' . ($this->archive['m'] + 1) . '-01 0:00:00') - 1;
			$archive_name = $this->user->lang('NEWS_ARCHIVE_OF', $this->user->format_date($archive_start, 'F Y'));

			$this->page_title = $archive_name;
			$sql_array['WHERE'] .= ' AND topic_time >= ' . (int) $archive_start . ' AND topic_time <= ' . (int) $archive_end;
			$sql_array['ORDER_BY'] = 'topic_time ASC';
		}

		if (!$this->news && $this->config['news_shadow'])
		{
			$sql_array['WHERE'] .= ' AND topic_moved_id = 0';
		}

		return $sql_array;
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
	public function generate_category_list()
	{
		$sql = 'SELECT forum_id, forum_name, forum_topics_approved
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forums(), false, true) . '
				AND forum_topics_approved <> 0
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('cat_block', array(
				'U_NEWS_CAT'		=> $this->get_url($row['forum_id'], ($this->category == $row['forum_id']) ? '' : false),
				'NEWS_CAT'			=> $row['forum_name'],
				'NEWS_COUNT'		=> $row['forum_topics_approved'],
			));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Generate the achrive list of the news forums and populate it into the template
	*
	* @return	null
	*/
	public function generate_archive_list()
	{
		$this->num_pagination_items = 0;

		$archiv_years = $archiv_months = $checked_months = array();
		$sql = 'SELECT topic_time
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forums($this->category), false, true) . '
				AND topic_visibility = ' . ITEM_APPROVED . '
			ORDER BY topic_time DESC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$year = $this->user->format_date($row['topic_time'], 'Y');
			$month = $this->user->format_date($row['topic_time'], 'm');

			if (!isset($archiv_months[$year]))
			{
				$archiv_years[$year] = 0;
			}

			if (isset($archiv_months[$year][$month]))
			{
				$archiv_years[$year]++;
				$archiv_months[$year][$month]['count']++;
				continue;
			}

			$archiv_months[$year][$month] = array(
				'url'	=> $this->user->format_date($row['topic_time'], 'Y/m'),
				'name'	=> $this->user->format_date($row['topic_time'], 'F'),
				'count'	=> 1,
			);
		}
		$this->db->sql_freeresult($result);

		foreach ($archiv_years as $year => $news)
		{
			$this->template->assign_block_vars('archive_block', array(
				'NEWS_YEAR'		=> $year,
			));
			foreach ($archiv_months[$year] as $month => $archive)
			{
				$active_archive = false;
				if (empty($this->archive) || ($this->archive['y'] == $year && $this->archive['m'] == $month) || ($this->archive['y'] == 0 && $this->archive['m'] == 0))
				{
					$active_archive = ($this->archive['y'] == $year && $this->archive['m'] == $month);
					$this->num_pagination_items += $archive['count'];
				}

				$this->template->assign_block_vars('archive_block.archive_row', array(
					'U_NEWS_MONTH'		=> $this->get_url(($active_archive) ? '' : empty($this->config['news_cat_show']), $archive['url']),
					'NEWS_MONTH'		=> $archive['name'],
					'NEWS_COUNT'		=> $archive['count'],
				));
			}
		}

		return;
	}

	/**
	* Counts the total number of news for pagination
	*
	* @return	null
	*/
	public function count_num_pagination_items()
	{
		$this->num_pagination_items = 0;

		$archiv_years = $archiv_months = $checked_months = array();
		$sql = 'SELECT COUNT(topic_id) AS num_news
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forums($this->category), false, true) . '
				AND topic_visibility = ' . ITEM_APPROVED;
		$result = $this->db->sql_query($sql);
		$this->num_pagination_items = $this->db->sql_fetchfield('num_news');
		$this->db->sql_freeresult($result);

		return;
	}

	/**
	* Generate the pagination for the news list
	*
	* @return	null
	*/
	public function generate_pagination()
	{
		if (!$this->is_archive())
		{
			$max_num_news = $this->config['news_pages'] * $this->config['news_number'];
			$pagination_news = min($max_num_news, $this->num_pagination_items);
		}
		else
		{
			$pagination_news = $this->num_pagination_items;
		}

		$base_url = $this->get_url(false, false, '/page/%d');
		phpbb_generate_template_pagination($this->template, $base_url, 'pagination', '/page/%d', $pagination_news, $this->config['news_number'], $this->start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> phpbb_on_page($this->template, $this->user, $base_url, $pagination_news, $this->config['news_number'], $this->start),
			'TOTAL_NEWS'		=> $this->user->lang('VIEW_NEWS_POSTS', $this->num_pagination_items),
		));
	}

	/**
	* Generate the pagination for the news list
	*
	* @return	mixed	$force_category		Overwrites the category, false for disabled, integer otherwise
	* @return	mixed	$force_archive		Overwrites the archive, false for disabled, string otherwise
	* @return	string	$append_route		Additional string that should be appended to the route
	* @return		string		Full URL with append_sid performed on it
	*/
	public function get_url($force_category = false, $force_archive = false, $append_route = '')
	{
		$base_url = 'news';
		if ($force_category !== false)
		{
			$base_url .= ($force_category !== '') ? '/category/' . $force_category : '';
		}
		else if ($this->category)
		{
			$base_url .= '/category/' . $this->category;
		}

		if ($force_archive !== false)
		{
			$base_url .= ($force_archive !== '') ? '/archive/' . $force_archive : '';
		}
		else if ($this->is_archive())
		{
			$base_url .= '/archive/' . $this->archive['y'] . '/' . sprintf('%02d', $this->archive['m']);
		}

		return $this->helper->url($base_url . $append_route);
	}
}
