<?php

/**
 * This file is part of the NV Newspage Extension package.
 *
 * @copyright (c) nickvergessen <https://github.com/nickvergessen>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the license.txt file.
 */

namespace nickvergessen\newspage;

use Nickvergessen\TrimMessage\TrimMessage;

/**
 * Class newspage
 *
 * @package nickvergessen\newspage
 */
class newspage
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\cache\service */
	protected $cache;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\request\request_interface */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var \phpbb\content_visibility */
	protected $content_visibility;
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \nickvergessen\newspage\helper */
	protected $news_helper;
	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string */
	protected $root_path;
	/** @var string */
	protected $php_ext;

	/** @var int */
	protected $num_pagination_items = 0;
	/** @var string */
	protected $page_title;
	/** @var int */
	protected $start;
	/** @var int */
	protected $news;
	/** @var array */
	protected $archive;
	/** @var int */
	protected $category;

	const ARCHIVE_SHOW = 1;
	const ARCHIVE_PER_YEAR = 2;

	/** @var int[] */
	protected $post_ids;
	/** @var int[] */
	protected $topic_ids;
	/** @var int[] */
	protected $forums;
	/** @var int[] */
	protected $poster_ids;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth		$auth		Auth object
	* @param \phpbb\cache\service	$cache		Cache object
	* @param \phpbb\config\config	$config		Config object
	* @param \phpbb\db\driver\driver_interface	$db		Database object
	* @param \phpbb\request\request_interface		$request	Request object
	* @param \phpbb\template\template	$template	Template object
	* @param \phpbb\user		$user		User object
	* @param \phpbb\content_visibility		$content_visibility	Content visibility object
	* @param \phpbb\controller\helper		$helper				Controller helper object
	* @param \nickvergessen\newspage\helper $news_helper				Controller helper object
	* @param \phpbb\pagination	$pagination	Pagination object
	* @param string			$root_path	phpBB root path
	* @param string			$php_ext	phpEx
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\content_visibility $content_visibility, \phpbb\controller\helper $helper, \nickvergessen\newspage\helper $news_helper, \phpbb\pagination $pagination, $root_path, $php_ext)
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
		$this->news_helper = $news_helper;
		$this->pagination = $pagination;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('bbcode'))
		{
			include($this->root_path . 'includes/bbcode.' . $this->php_ext);
		}
		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		$this->page_title = $this->user->lang['NEWS'];
	}

	/**
	 * @return string
	 */
	public function get_page_title()
	{
		return $this->page_title;
	}

	/**
	 * @param int $start
	 * @return $this
	 */
	public function set_start($start)
	{
		$this->start = (int) $start;

		return $this;
	}

	/**
	 * @param int $category
	 * @return $this
	 */
	public function set_category($category)
	{
		$this->category = (int) $category;

		return $this;
	}

	/**
	 * @param int $news
	 * @return $this
	 */
	public function set_news($news)
	{
		$this->news = (int) $news;

		return $this;
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return $this
	 */
	public function set_archive($year, $month)
	{
		$this->archive = array(
			'y'	=> (int) $year,
			'm'	=> (int) $month,
		);

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isset_archive()
	{
		return isset($this->archive['y']) && isset($this->archive['m']);
	}

	/**
	 * @return bool
	 */
	public function is_archive()
	{
		return $this->isset_archive() && $this->archive['y'] !== 0 && $this->archive['m'] !== 0;
	}

	/**
	 * Is the archive the current archive?
	 *
	 * @param int $year
	 * @param int $month
	 * @return bool
	 */
	protected function is_current_archive($year, $month)
	{
		return $this->is_archive() && $this->archive['y'] == $year && $this->archive['m'] == $month;
	}

	/**
	 * Is the archive important for the pagination?
	 *
	 * @param int $year
	 * @param int $month
	 * @return bool
	 */
	protected function is_paginated_archive($year, $month)
	{
		return !$this->isset_archive() ||
		$this->is_current_archive($year, $month) ||
		($this->archive['y'] == 0 && $this->archive['m'] == 0);
	}

	/**
	 * Get the news items we want to display
	 */
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
		if ($this->auth->acl_get('a_board'))
		{
			$this->display_newspage_settings();
		}

		$this->get_news_ids();

		if (empty($this->topic_ids))
		{
			$l_no_news = ($this->is_archive()) ? 'NO_NEWS_ARCHIVE' : (($this->category) ? 'NO_NEWS_CATEGORY' : 'NO_NEWS');
			$this->template->assign_var('L_NO_NEWS', $this->user->lang($l_no_news));

			return;
		}

		// Grab ranks, icons, online-status and attachments
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
			$post_edit_list = array();
			$display_notice = false;

			$post_unread = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;

			//parse message for display
			if ($this->news)
			{
				$this->page_title = censor_text($row['post_subject']);
			}
			else if (((int) $this->config['news_char_limit']) !== 0 && class_exists('\Nickvergessen\TrimMessage\TrimMessage'))
			{
				$trim = new TrimMessage($row['post_text'], $row['bbcode_uid'], $this->config['news_char_limit']);
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

			$rank_data = phpbb_get_user_rank($row, $row['user_posts']);
			$row['rank_title'] = $rank_data['title'];
			$row['rank_image'] = $rank_data['img'];
			$row['rank_image_src'] = $rank_data['img_src'];

			$row['user_jabber'] = ($row['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '';

			// Can this user receive a Private Message?
			$can_receive_pm = (
				// They must be a "normal" user
				$row['user_type'] != USER_IGNORE &&

				// They must not be deactivated by the administrator
				($row['user_type'] != USER_INACTIVE || $row['user_inactive_reason'] != INACTIVE_MANUAL) &&

				// They must be able to read PMs
				//@todo in_array($poster_id, $can_receive_pm_list) &&

				// They must not be permanently banned
				//@todo !in_array($poster_id, $permanently_banned_users) &&

				// They must allow users to contact via PM
				(($this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_')) || $row['user_allow_pm'])
			);

			$u_pm = $u_email = '';

			if ($this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && $can_receive_pm)
			{
				$u_pm = append_sid("{$this->root_path}ucp.{$this->php_ext}", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']);
			}

			if ((!empty($row['user_allow_viewemail']) && $this->auth->acl_get('u_sendemail')) || $this->auth->acl_get('a_email'))
			{
				$u_email = ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=email&amp;u=$poster_id") : (($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			}

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
				'U_NEWS'				=> $this->helper->route('nickvergessen_newspage_singlenews_controller', array('topic_id' => $topic_id)),

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

				'POSTER_AVATAR'			=> ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'U_POST_AUTHOR'			=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
				'RANK_TITLE'			=> $row['rank_title'],
				'RANK_IMG'				=> $row['rank_image'],
				'RANK_IMG_SRC'			=> $row['rank_image_src'],
				'POSTER_POSTS'			=> $row['user_posts'],
				'POSTER_JOINED'			=> $this->user->format_date($row['user_regdate']),

				'U_PM'					=> ($poster_id != ANONYMOUS && $this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_'))) ? append_sid("{$this->root_path}ucp.{$this->php_ext}", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']) : '',
				'U_EMAIL'				=> $row['user_email'],
				'U_JABBER'				=> $row['user_jabber'],
			));

			$contact_fields = array(
				array(
					'ID'		=> 'pm',
					'NAME' 		=> $this->user->lang['PRIVATE_MESSAGES'],
					'U_CONTACT'	=> $u_pm,
				),
				array(
					'ID'		=> 'email',
					'NAME'		=> $this->user->lang['SEND_EMAIL'],
					'U_CONTACT'	=> $u_email,
				),
				array(
					'ID'		=> 'jabber',
					'NAME'		=> $this->user->lang['JABBER'],
					'U_CONTACT'	=> ($row['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->root_path}memberlist.{$this->php_ext}", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '',
				),
			);

			foreach ($contact_fields as $field)
			{
				if ($field['U_CONTACT'])
				{
					$this->template->assign_block_vars('postrow.contact', $field);
				}
			}

			if (!empty($cp_row['blockrow']))
			{
				foreach ($cp_row['blockrow'] as $field_data)
				{
					$this->template->assign_block_vars('postrow.custom_fields', $field_data);

					if ($field_data['S_PROFILE_CONTACT'])
					{
						$this->template->assign_block_vars('postrow.contact', array(
							'ID'		=> $field_data['PROFILE_FIELD_IDENT'],
							'NAME'		=> $field_data['PROFILE_FIELD_NAME'],
							'U_CONTACT'	=> $field_data['PROFILE_FIELD_CONTACT'],
						));
					}
				}
			}

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
			'NEWS_USER_INFO'			=> true, // $this->config['news_user_info'],
			'NEWS_POST_BUTTONS'			=> true, // $this->config['news_post_buttons'],
			'S_NEWS_ARCHIVE_PER_YEAR'	=> $this->config['news_archive_show'] == self::ARCHIVE_PER_YEAR,

			'NEWS_ONLY'					=> $this->news,
			'NEWS_TITLE'				=> $this->get_page_title(),
		));

		return;
	}

	/**
	 * @param array $posters
	 * @return array
	 */
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

	/**
	 * @param array $post_ids
	 * @return array
	 */
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

	/**
	 * @return array
	 */
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

		if (!$this->news && !$this->config['news_shadow'])
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

		/** @var \nickvergessen\newspage\route $route */
		$route = $this->news_helper->generate_route($this->category, $this->archive);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$u_post = '';
			if ($this->auth->acl_get('f_post', $row['forum_id']) || $this->user->data['user_id'] == ANONYMOUS)
			{
				$u_post = append_sid("{$this->root_path}posting.{$this->php_ext}", 'mode=post&amp;f=' . $row['forum_id']);
			}
			$this->template->assign_block_vars('cat_block', array(
				'U_NEWS_CAT'		=> $route->get_url($row['forum_id'], ($this->category == $row['forum_id']) ? '' : false),
				'NEWS_CAT'			=> $row['forum_name'],
				'NEWS_COUNT'		=> $row['forum_topics_approved'],
				'S_SELECTED'		=> $this->category == $row['forum_id'],
				'U_POST_NEWS'		=> $u_post,
			));

			if ($this->category == $row['forum_id'])
			{
				$this->template->assign_vars(array(
					'NEWS_FILTER_CATEGORY'		=> $row['forum_name'],
					'U_REMOVE_CATEGORY_FILTER'	=> $route->get_url(true, ($this->category == $row['forum_id']) ? '' : false),
				));
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Generate the archive list of the news forums and populate it into the template
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

		$this->assign_archive_list($archiv_years, $archiv_months);
	}

	/**
	 * Assign the archive blocks to the template
	 *
	 * @param array $archiv_years
	 * @param array $archiv_months
	 */
	protected function assign_archive_list(array $archiv_years, array $archiv_months)
	{
		foreach ($archiv_years as $year => $news)
		{
			$this->template->assign_block_vars('archive_block', array(
				'NEWS_YEAR'		=> $year,
			));

			foreach ($archiv_months[$year] as $month => $archive)
			{
				$active_archive = false;
				if ($this->is_paginated_archive($year, $month))
				{
					$active_archive = $this->is_current_archive($year, $month);
					$this->num_pagination_items += $archive['count'];
				}

				/** @var \nickvergessen\newspage\route $route */
				$route = $this->news_helper->generate_route($this->category, $this->archive);
				$this->template->assign_block_vars('archive_block.archive_row', array(
					'U_NEWS_MONTH'		=> $route->get_url(($active_archive) ? '' : empty($this->config['news_cat_show']), $archive['url']),
					'NEWS_MONTH'		=> $archive['name'],
					'NEWS_COUNT'		=> $archive['count'],
					'S_SELECTED'		=> $active_archive,
				));

				if ($active_archive)
				{
					$this->template->assign_vars(array(
						'NEWS_FILTER_ARCHIVE_YEAR'		=> $year,
						'NEWS_FILTER_ARCHIVE_MONTH'		=> $archive['name'],
						'U_REMOVE_ARCHIVE_FILTER'		=> $route->get_url(($active_archive) ? '' : empty($this->config['news_cat_show']), true),
					));
				}
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

		/** @var \nickvergessen\newspage\route $route */
		$route = $this->news_helper->generate_route($this->category, $this->archive);
		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					$route->get_route(),
					$route->get_route(false, false, 2),
				),
				'params' => $route->get_params(),
			), 'pagination', 'page', $pagination_news, $this->config['news_number'], $this->start);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> $this->pagination->on_page($pagination_news, $this->config['news_number'], $this->start),
			'TOTAL_NEWS'		=> $this->user->lang('VIEW_NEWS_POSTS', $this->num_pagination_items),
		));
	}

	protected function display_newspage_settings()
	{
		if (!function_exists('make_forum_select'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		add_form_key('newspage');
		$this->template->assign_vars(array(
			'U_SETTING_ACTION'			=> $this->helper->route('nickvergessen_newspage_settings'),
			'SETTING_NEWS_CHAR_LIMIT'		=> (int) $this->config['news_char_limit'],
			'SETTING_NEWS_NUMBER'			=> (int) $this->config['news_number'],
			'SETTING_NEWS_PAGES'			=> (int) $this->config['news_pages'],
			'SETTING_NEWS_SHADOW_SHOW'		=> (bool) $this->config['news_shadow'],
			'SETTING_NEWS_ATTACH_SHOW'		=> (bool) $this->config['news_attach_show'],
			'SETTING_NEWS_CAT_SHOW'			=> (bool) $this->config['news_cat_show'],
			'SETTING_NEWS_ARCHIVE_SHOW'		=> (bool) $this->config['news_archive_show'],
			'SETTING_NEWS_FORUMS'			=> make_forum_select(explode(',', $this->config['news_forums'])),

			'SETTING_BUTTON_RESPONSIVE_FIXED'	=> false,
			'SETTING_NEWS_USER_INFO'		=> (bool) $this->config['news_user_info'],
			'SETTING_NEWS_POST_BUTTONS'		=> (bool) $this->config['news_post_buttons'],
		));
	}
}
