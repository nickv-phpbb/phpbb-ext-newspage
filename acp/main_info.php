<?php

/**
*
* @package NV Newspage Extension
* @copyright (c) 2013 nickvergessen
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace nickvergessen\newspage\acp;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\nickvergessen\newspage\acp\main_module',
			'title'		=> 'ACP_NEWSPAGE_TITLE',
			'version'	=> '1.0.1',
			'modes'		=> array(
				'config_newspage'	=> array('title' => 'ACP_NEWSPAGE_CONFIG', 'auth' => 'acl_a_board', 'cat' => array('ACP_NEWSPAGE_TITLE')),
			),
		);
	}
}
