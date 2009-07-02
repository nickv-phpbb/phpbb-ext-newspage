<?php

/**
*
* newspage [British English]
*
* @package language
* @version $Id: info_acp_newspage.php 4 2007-06-02
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NEWS_TITLE'				=> 'NV newspage',
	'NEWS_CONFIG'				=> 'Configuration',

	'NEWS'						=> 'News',
	'NEWS_ARCHIVE'				=> 'Archive',
	'NEWS_ARCHIVE_OF'			=> 'Archive %s',
	'NEWS_CHAR_LIMIT'			=> 'Number of characters, displayed on the news-page',
	'NEWS_CHAR_LIMIT_EXPLAIN'	=> 'Please note: this might break BBCodes to view quite ugly and in non-strict HTML.',
	'NEWS_COMMENTS'				=> 'Comments',
	'NEWS_FORUMS'				=> 'Select News-Forums',
	'NEWS_GO_TO_TOPIC'			=> 'Link to topic',
	'NEWS_NONE'					=> 'no News',
	'NEWS_NUMBER'				=> 'Number of news viewed per page',
	'NEWS_PAGES'				=> 'Number of pages',
	'NEWS_PAGES_EXPLAIN'		=> 'Archive-links will always view as many pages as they have.',
	'NEWS_POST_BUTTONS'			=> 'Show post buttons',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> '(quote, edit, ...)',
	'NEWS_READ_FULL'			=> 'Read full news',
	'NEWS_READ_HERE'			=> 'Here',
	'NEWS_SAVED'				=> 'saved setup',
	'NEWS_USER_INFO'			=> 'Show user info',
	'NEWS_USER_INFO_EXPLAIN'	=> '(posts, location, ...)',

	'NEWSPAGE'					=> 'NV Newspage',
	'INSTALL_NEWSPAGE'			=> 'Install Newspage',
	'INSTALL_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to install the Newspage?',
	'UPDATE_NEWSPAGE'			=> 'Update Newspage',
	'UPDATE_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to update the Newspage?',
	'UNINSTALL_NEWSPAGE'		=> 'Uninstall Newspage',
	'UNINSTALL_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to uninstall the Newspage?',
));

?>