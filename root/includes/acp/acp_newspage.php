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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_newspage
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/common');
		$this->tpl_name = 'acp_newspage';
		$this->page_title = $user->lang['NEWS'];
		add_form_key('newspage');

		$submit = (isset($_POST['submit'])) ? true : false;
		if ($submit)
		{
			if (!check_form_key('newspage'))
			{
				trigger_error('FORM_INVALID');
			}

			set_config('news_char_limit', max(100, request_var('news_char_limit', 0)));
			set_config('news_forums', implode(',', request_var('news_forums', array(0))));
			set_config('news_number', max(1, request_var('news_number', 0)));
			set_config('news_pages', max(1, request_var('news_pages', 0)));
			set_config('news_post_buttons', request_var('news_post_buttons', 0));
			set_config('news_user_info', request_var('news_user_info', 0));
			set_config('news_attach_show', request_var('news_attach_show', 0));
			set_config('news_cat_show', request_var('news_cat_show', 0));
			set_config('news_archive_per_year', request_var('news_archive_per_year', 0));

			trigger_error($user->lang['NEWS_SAVED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'NEWS_CHAR_LIMIT'		=> $config['news_char_limit'],
			'NEWS_NUMBER'			=> $config['news_number'],
			'NEWS_PAGES'			=> $config['news_pages'],
			'NEWS_POST_BUTTONS'		=> $config['news_post_buttons'],
			'NEWS_USER_INFO'		=> $config['news_user_info'],
			'NEWS_ATTACH_SHOW'		=> $config['news_attach_show'],
			'NEWS_CAT_SHOW'			=> $config['news_cat_show'],
			'NEWS_ARCHIVE_PER_YEAR'			=> $config['news_archive_per_year'],
			'S_SELECT_FORUMS'		=> make_forum_select(explode(',', $config['news_forums'])),
		));

	}
}

?>