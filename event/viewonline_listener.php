<?php

/**
 * This file is part of the NV Newspage Extension package.
 *
 * @copyright (c) nickvergessen <https://github.com/nickvergessen>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the license.txt file.
 */

namespace nickvergessen\newspage\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class viewonline_listener
 * Handles displaying the route name and link on viewonline.php
 *
 * @package nickvergessen\newspage\event
 */
class viewonline_listener implements EventSubscriberInterface
{
	/**
	 * Register to the events
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewonline_overwrite_location'	=> 'add_newspage_viewonline',
		);
	}

	/* @var \nickvergessen\newspage\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/* @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \nickvergessen\newspage\helper $helper
	 * @param \phpbb\user $user
	 * @param string $php_ext
	 */
	public function __construct(\nickvergessen\newspage\helper $helper, \phpbb\user $user, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param array $event
	 * @return null
	 */
	public function add_newspage_viewonline($event)
	{
		$route = $this->helper->generate_route();
		if ($event['on_page'][1] == 'app')
		{
			if ($event['row']['session_page'] === 'app.' . $this->php_ext . '/news')
			{
				$event['location_url'] = $route->get_url();
				$event['location'] = $this->user->lang('VIEWONLINE_NEWS');
			}
			else if (($forum_id = $this->get_category_from_route($event['row']['session_page'])) &&
				($archive = $this->get_archive_from_route($event['row']['session_page'])))
			{
				$archive_start = $this->user->get_timestamp_from_format('Y/n-d H:i:s', $archive . '-01 0:00:00');

				$event['location_url'] = $route->get_url($forum_id, $archive);
				$event['location'] = $this->user->lang('VIEWONLINE_NEWS_CATEGORY_ARCHIVE', $event['forum_data'][$forum_id]['forum_name'], $this->user->format_date($archive_start, 'F Y'));
			}
			else if ($forum_id = $this->get_category_from_route($event['row']['session_page']))
			{
				$event['location_url'] = $route->get_url($forum_id);
				$event['location'] = $this->user->lang('VIEWONLINE_NEWS_CATEGORY', $event['forum_data'][$forum_id]['forum_name']);
			}
			else if ($archive = $this->get_archive_from_route($event['row']['session_page']))
			{
				$archive_start = $this->user->get_timestamp_from_format('Y/n-d H:i:s', $archive . '-01 0:00:00');

				$event['location_url'] = $route->get_url(false, $archive);
				$event['location'] = $this->user->lang('VIEWONLINE_NEWS_ARCHIVE', $this->user->format_date($archive_start, 'F Y'));
			}
		}
	}

	/**
	 * @param string $route
	 * @return int|bool
	 */
	protected function get_category_from_route($route)
	{
		$route_ary = explode('/', $route);

		if (sizeof($route_ary) >= 3 && $route_ary[0] === 'app.' . $this->php_ext && $route_ary[1] === 'news' && $route_ary[2] === 'category')
		{
			return (int) $route_ary[3];
		}

		return false;
	}

	/**
	 * @param string $route
	 * @return string|bool
	 */
	protected function get_archive_from_route($route)
	{
		$route_ary = explode('/', $route);

		if (sizeof($route_ary) >= 4 && $route_ary[0] === 'app.' . $this->php_ext && $route_ary[1] === 'news' && $route_ary[2] === 'archive')
		{
			return (int) $route_ary[3] . '/' . (int) $route_ary[4];
		}
		else if (sizeof($route_ary) >= 6 && $route_ary[0] === 'app.' . $this->php_ext && $route_ary[1] === 'news' && $route_ary[4] === 'archive')
		{
			return (int) $route_ary[5] . '/' . (int) $route_ary[6];
		}

		return false;
	}
}
