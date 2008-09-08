<?php

/**
*
* @package - NV newspage
* @version $Id: acp_newspage.php 63 2007-12-18 14:19:49Z nickvergessen $
* @copyright (c) nickvergessen ( http://www.flying-bits.org/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

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
			$news_char_limit	= request_var('news_char_limit', 500);
			$news_forums		= request_var('news_forums', '');
			$news_number		= request_var('news_number', 5);
			$news_post_buttons	= request_var('news_post_buttons', 0);
			$news_user_info		= request_var('news_user_info', 0);
			if($news_char_limit !=$config['news_char_limit'])
			{
				set_config('news_char_limit', (($news_char_limit < 1)? 500 : $news_char_limit));
			}
			if($news_forums !=$config['news_forums'])
			{
				set_config('news_forums', $news_forums);
			}
			if($news_number !=$config['news_number'])
			{
				set_config('news_number', (($news_number < 1)? 5 : $news_number));
			}
			if($news_post_buttons !=$config['news_post_buttons'])
			{
				set_config('news_post_buttons', $news_post_buttons);
			}
			if($news_user_info !=$config['news_user_info'])
			{
				set_config('news_user_info', $news_user_info);
			}
			trigger_error($user->lang['NEWS_SAVED'] . adm_back_link($this->u_action));
		}
		$template->assign_vars(array(
			'NEWS_CHAR_LIMIT'		=> $config['news_char_limit'],
			'NEWS_FORUMS'			=> $config['news_forums'],
			'NEWS_NUMBER'			=> $config['news_number'],
			'NEWS_POST_BUTTONS'		=> $config['news_post_buttons'],
			'NEWS_USER_INFO'		=> $config['news_user_info'],
			'U_ACTION'				=> $this->u_action,
		));

	}
}

?>