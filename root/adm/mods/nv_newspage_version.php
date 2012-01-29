<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) nickvergessen ( http://www.flying-bits.org/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package nv_altt
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class nv_newspage_version
{
	function version()
	{
		return array(
			'author'	=> 'nickvergessen',
			'title'		=> 'NV Newspage',
			'tag'		=> 'nv_newspage',
			'version'	=> '1.0.6',
			'file'		=> array('www.flying-bits.org', 'updatecheck', 'nv_newspage.xml'),
		);
	}
}

?>