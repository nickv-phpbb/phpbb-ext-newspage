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
* @package module_install
*/
class acp_newspage_info
{
	function module()
	{		
		return array(
			'filename'	=> 'acp_newspage',
			'title'		=> 'NEWS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'adjust_news'	=> array(
					'title'		=> 'NEWS_CONFIG',
					'auth'		=> 'acl_a_board',
					'cat'		=> array('ACP_BOARD_CONFIGURATION'),
				),
			),
		);
	}
}

?>