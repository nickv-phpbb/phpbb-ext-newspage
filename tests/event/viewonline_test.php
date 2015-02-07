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

namespace nickvergessen\newspage\tests\event;

use nickvergessen\newspage\event\viewonline_listener;
use nickvergessen\newspage\helper;
use phpbb\config\config;
use phpbb\event\data;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class viewonline_test
 * Testing \nickvergessen\newspage\event\viewonline_listener
 *
 * @package nickvergessen\newspage\tests\event
 */
class viewonline_test extends \phpbb_test_case
{
	/** @var user */
	protected $user;

	/** @var viewonline_listener */
	protected $listener;

	/**
	 * @return null
	 */
	public function setup_listener()
	{
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();
		$this->user->expects($this->any())
			->method('lang')
			->willReturnCallback(function () {
				return implode(' ', func_get_args());
			});
		$this->user->expects($this->any())
			->method('format_date')
			->with(null, 'F Y')
			->willReturn('April 2014');

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		/** @var \phpbb\controller\helper $controller_helper */
		$this->listener = new viewonline_listener(
			new helper(
				$controller_helper,
				new config(array(
					'news_cat_show' => 1,
					'news_archive_show' => 1,
				))
			),
			$this->user,
			'php'
		);
	}

	/**
	 * @return null
	 */
	public function test_construct()
	{
		$this->setup_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	/**
	 * @return null
	 */
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.viewonline_overwrite_location',
		), array_keys(viewonline_listener::getSubscribedEvents()));
	}

	/**
	 * @return array
	 */
	public function add_newspage_viewonline_data()
	{
		global $phpEx;

		return array(
			array(
				array(
					1 => 'index',
				),
				array(),
				array(),
				'$location_url',
				'$location',
				'$location_url',
				'$location',
			),
			array(
				array(
					1 => 'app',
				),
				array(
					'session_page' => 'app.' . $phpEx . '/news'
				),
				array(),
				'$location_url',
				'$location',
				'nickvergessen_newspage_controller#a:0:{}',
				'VIEWONLINE_NEWS',
			),
			array(
				array(
					1 => 'app',
				),
				array(
					'session_page' => 'app.' . $phpEx . '/news/category/30'
				),
				array(
					30 => array(
						'forum_name'	=> 'Forum30',
					),
				),
				'$location_url',
				'$location',
				'nickvergessen_newspage_category_controller#' . serialize(array('forum_id' => 30)),
				'VIEWONLINE_NEWS_CATEGORY Forum30',
			),
			array(
				array(
					1 => 'app',
				),
				array(
					'session_page' => 'app.' . $phpEx . '/news/archive/2014/04'
				),
				array(),
				'$location_url',
				'$location',
				'nickvergessen_newspage_archive_controller#' . serialize(array('year' => 2014, 'month' => '04')),
				'VIEWONLINE_NEWS_ARCHIVE April 2014',
			),
			array(
				array(
					1 => 'app',
				),
				array(
					'session_page' => 'app.' . $phpEx . '/news/category/30/archive/2014/04'
				),
				array(
					30 => array(
						'forum_name'	=> 'Forum30',
					),
				),
				'$location_url',
				'$location',
				'nickvergessen_newspage_category_archive_controller#' . serialize(array('forum_id' => 30, 'year' => 2014, 'month' => '04')),
				'VIEWONLINE_NEWS_CATEGORY_ARCHIVE Forum30 April 2014',
			),
		);
	}

	/**
	 * @dataProvider add_newspage_viewonline_data
	 *
	 * @param array $on_page
	 * @param array $row
	 * @param array $forum_data
	 * @param string $location_url
	 * @param string $location
	 * @param string $expected_location_url
	 * @param string $expected_location
	 */
	public function test_add_newspage_viewonline(array $on_page, array $row, array $forum_data, $location_url, $location, $expected_location_url, $expected_location)
	{
		$this->setup_listener();

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('core.viewonline_overwrite_location', array($this->listener, 'add_newspage_viewonline'));

		$event_data = array('on_page', 'row', 'location_url', 'location', 'forum_data');
		$event = new data(compact($event_data));
		$dispatcher->dispatch('core.viewonline_overwrite_location', $event);

		$event_data_after = $event->get_data_filtered($event_data);
		foreach ($event_data as $expected)
		{
			$this->assertArrayHasKey($expected, $event_data_after);
		}
		extract($event_data_after);

		$this->assertEquals($expected_location_url, $location_url);
		$this->assertEquals($expected_location, $location);
	}
}
