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
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

$mod_name = 'NEWSPAGE';

$version_config_name = 'newspage_mod_version';
$language_file = 'mods/info_acp_newspage';

$versions = array(
	// Version 1.0.0
	'1.0.0'	=> array(
		'config_add' => array(
			array('news_number', 5),
			array('news_forums', '0'),
			array('news_char_limit', 500),
			array('news_user_info', 1),
			array('news_post_buttons', 1),
		),
		'module_add' => array(
			array('acp', 'ACP_CAT_DOT_MODS', 'NEWS'),

			array('acp', 'NEWS', array(
					'module_basename'	=> 'newspage',
					'module_langname'	=> 'NEWS_CONFIG',
					'module_mode'		=> 'overview',
					'module_auth'		=> 'acl_a_board',
				),
			),
		),
	),

	// Version 1.0.1
	'1.0.1'	=> array(
		'config_add' => array(
			array('news_pages', 1),
		),
	),

	// Version 1.0.2
	'1.0.2'	=> array(
	),

	// Version 1.0.3
	'1.0.3' => array(
		'config_add' => array(
			array('news_attach_show', 1),
			array('news_cat_show', 1),
			array('news_archive_per_year', 1),
		),
	),

	// Version 1.0.4
	'1.0.4'	=> array(
	),

	// Version 1.0.5
	'1.0.5'	=> array(
	),

	// Version 1.0.5.1
	'1.0.5.1'	=> array(
	),

	// Version 1.0.6
	'1.0.6'	=> array(
	),

	// Version 1.0.7
	'1.0.7'	=> array(
		'config_add' => array(
			array('news_shadow', 0),
		),
	),

	// Version 1.0.8
	'1.0.8'	=> array(
	),
);

// Include the UMIL Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);
