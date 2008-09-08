<?php

/**
*
* @package - NV newspage
* @version $Id$
* @copyright (c) nickvergessen ( http://mods.flying-bits.org/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang('mods/info_acp_newspage');
$new_mod_version = '1.0.0';
$page_title = 'NV newspage v' . $new_mod_version;

$mode = request_var('mode', 'else', true);
if ($user->data['user_type'] != USER_FOUNDER)
{
	$mode  = '';
}
function split_sql_file($sql, $delimiter)
{
	$sql = str_replace("\r" , '', $sql);
	$data = preg_split('/' . preg_quote($delimiter, '/') . '$/m', $sql);

	$data = array_map('trim', $data);

	// The empty case
	$end_data = end($data);

	if (empty($end_data))
	{
		unset($data[key($data)]);
	}

	return $data;
}
switch ($mode)
{
	case 'install':
		$install = request_var('install', 0);
		$installed = false;
		if ($install == 1)
		{
			set_config('news_number', 5);
			set_config('news_forums', '0');
			set_config('news_char_limit', 500);
			set_config('news_user_info', 1);
			set_config('news_post_buttons', 1);
			set_config('newspage_mod_version', $new_mod_version, true);

			// create the acp modules
			$modules = new acp_modules();
			$newspage = array(
				'module_basename'	=> '',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> 31,
				'module_class'		=> 'acp',
				'module_langname'	=> 'NEWS',
				'module_mode'		=> '',
				'module_auth'		=> ''
			);
			$modules->update_module_data($newspage);
			$adjust_news = array(
				'module_basename'	=> 'newspage',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $newspage['module_id'],
				'module_class'		=> 'acp',
				'module_langname'	=> 'NEWS_CONFIG',
				'module_mode'		=> 'overview',
				'module_auth'		=> ''
			);
			$modules->update_module_data($adjust_news);
			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'NV newspage v' . $new_mod_version . ' installed');
			$installed = true;
		}
	break;
	case 'update002':
	case 'update010':
		$update = request_var('update', 0);
		$version = request_var('v', '0.0.0', true);
		$updated = false;
		if ($update == 1)
		{
			set_config('newspage_mod_version', $new_mod_version, true);
			// clear cache and log what we did
			$cache->purge();
			add_log('admin', 'NV newspage updated to v' . $new_mod_version);
			$updated = true;
		}
	break;
	default:
		//we had a little cheater
	break;
}

include($phpbb_root_path . 'install_news/layout.'.$phpEx);
?>