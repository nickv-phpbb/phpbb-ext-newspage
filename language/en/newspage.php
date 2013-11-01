<?php

/**
*
* newspage [British English]
*
* @package language
* @version $Id$
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
	'ACP_NEWSPAGE_TITLE'		=> 'NV Newspage',
	'ACP_NEWSPAGE_CONFIG'		=> 'Newspage settings',

	'NEWS'						=> 'News',
	'NEWS_ARCHIVE_SHOW'			=> 'Show archive',
	'NEWS_ARCHIVE_SHOW_EXPLAIN'	=> 'Display a list with the months and the number of news posted.',
	'NEWS_ARCHIVE_SHOW_PER_YEAR'=> 'Seperated blocks per year',
	'NEWS_ARCHIVE'				=> 'Archive',
	'NEWS_ARCHIVE_OF'			=> 'Archive %s',
	'NEWS_ATTACH_SHOW'			=> 'Show attachments',
	'NEWS_ATTACH_SHOW_EXPLAIN'	=> 'Attachments will be displayed on the newspage.<br /><strong>Note:</strong> inline attachments will always be displayed.',
	'NEWS_CAT'					=> 'Categories',
	'NEWS_CAT_SHOW'				=> 'Show categories',
	'NEWS_CAT_SHOW_EXPLAIN'		=> 'The selected forums will appear as categories.',
	'NEWS_CHAR_LIMIT'			=> 'Number of characters, displayed on the news-page',
	'NEWS_COMMENTS'				=> 'Comments',
	'NEWS_FORUMS'				=> 'Select News-Forums',
	'NEWS_GO_TO_TOPIC'			=> 'Link to topic',
	'NEWS_NUMBER'				=> 'Number of news viewed per page',
	'NEWS_PAGES'				=> 'Number of pages',
	'NEWS_PAGES_EXPLAIN'		=> 'Archive-links will always view as many pages as they have.',
	'NEWS_POLL'					=> 'Poll',
	'NEWS_POLL_GOTO'			=> 'Click here to vote!',
	'NEWS_POST_BUTTONS'			=> 'Show post buttons',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> '(quote, edit, ...)',
	'NEWS_READ_FULL'			=> 'Read full news',
	'NEWS_READ_HERE'			=> 'Here',
	'NEWS_SAVED'				=> 'Settings updated successfully.',
	'NEWS_SHADOW_SHOW'			=> 'Show shadow topics',
	'NEWS_SHADOW_SHOW_EXPLAIN'	=> 'show news, if a shadow topic is left in place',
	'NEWS_USER_INFO'			=> 'Show user info',
	'NEWS_USER_INFO_EXPLAIN'	=> '(posts, location, ...)',

	'NO_NEWS'					=> 'There are no news.',
	'NO_NEWS_ARCHIVE'			=> 'There are no news in this archive.',
	'NO_NEWS_CATEGORY'			=> 'There are no news in this category.',

	'NEWSPAGE'					=> 'NV Newspage',
	'INSTALL_NEWSPAGE'			=> 'Install Newspage',
	'INSTALL_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to install the Newspage?',
	'UPDATE_NEWSPAGE'			=> 'Update Newspage',
	'UPDATE_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to update the Newspage?',
	'UNINSTALL_NEWSPAGE'		=> 'Uninstall Newspage',
	'UNINSTALL_NEWSPAGE_CONFIRM'	=> 'Are you sure you want to uninstall the Newspage?',

	'VIEW_NEWS_POSTS'			=> array(
		0	=> 'No news',
		1	=> '1 news',
		2	=> '%d news',
	),
));
