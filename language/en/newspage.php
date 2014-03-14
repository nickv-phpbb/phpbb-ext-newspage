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
	'NEWS_ADD_NEW'				=> 'New',
	'NEWS_ADD_NEW_TITLE'		=> 'Add new news',
	'NEWS_ARCHIVE_SHOW'			=> 'Allow to filter by date',
	'NEWS_ARCHIVE_SHOW_EXPLAIN'	=> 'Display a list with the months and the number of news posted.',
	'NEWS_ARCHIVE_SHOW_PER_YEAR'=> 'Seperated blocks per year',
	'NEWS_ARCHIVE'				=> 'Archive',
	'NEWS_ARCHIVE_OF'			=> 'Archive %s',
	'NEWS_ATTACH_SHOW'			=> 'Show attachments',
	'NEWS_ATTACH_SHOW_EXPLAIN'	=> 'Inline attachments will always be displayed.',
	'NEWS_CAT'					=> 'Categories',
	'NEWS_CAT_SHOW'				=> 'Allow to filter by forums',
	'NEWS_CAT_SHOW_EXPLAIN'		=> 'The selected forums will appear as categories.',
	'NEWS_CHAR_LIMIT'			=> 'Shorten news text',
	'NEWS_COMMENTS'				=> 'Comments',
	'NEWS_FORUMS'				=> 'Select News-Forums',
	'NEWS_FILTER_ARCHIVE'		=> 'Filter date',
	'NEWS_FILTER_BY_ARCHIVE'	=> 'Filter news by date',
	'NEWS_FILTER_BY_CATEGORY'	=> 'Filter news by forum',
	'NEWS_FILTER_CATEGORY'		=> 'Filter forum',
	'NEWS_FILTER_REMOVE'		=> 'Remove filter',
	'NEWS_GO_TO_TOPIC'			=> 'Link to topic',
	'NEWS_NUMBER'				=> 'News per page',
	'NEWS_PAGES'				=> 'Number of pages',
	'NEWS_POLL'					=> 'Poll',
	'NEWS_POLL_GOTO'			=> 'Click here to vote!',
	'NEWS_POST_BUTTONS'			=> 'Show post-related buttons',
	'NEWS_POST_BUTTONS_EXPLAIN'	=> 'Quote, Edit, etc.',
	'NEWS_READ_FULL'			=> 'Read full news',
	'NEWS_READ_HERE'			=> 'Here',
	'NEWS_SAVED'				=> 'Settings updated successfully.',
	'NEWS_SHADOW_SHOW'			=> 'Show moved topics',
	'NEWS_USER_INFO'			=> 'Show user information',
	'NEWS_USER_INFO_EXPLAIN'	=> 'Avatar, Profile Fields, etc.',

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

	'VIEWONLINE_NEWS'			=> 'Viewing news',
	'VIEWONLINE_NEWS_ARCHIVE'	=> 'Viewing news of %s',
	'VIEWONLINE_NEWS_CATEGORY'	=> 'Viewing news in %s',
	'VIEWONLINE_NEWS_CATEGORY_ARCHIVE'	=> 'Viewing news in %s of %s',
));
