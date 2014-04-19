<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\event;

class viewonline_test extends \phpbb_test_case
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \nickvergessen\newspage\event\menu_link_listener */
	protected $listener;

	public function setup_listener()
	{
		$this->user = new \nickvergessen\newspage\tests\mock\user();
		$this->user->timezone = new \DateTimeZone('UTC');
		$this->user->lang['datetime'] = array();

		$this->listener = new \nickvergessen\newspage\event\viewonline_listener(
			new \nickvergessen\newspage\helper(
				new \nickvergessen\newspage\tests\mock\controller_helper(),
				new \phpbb\config\config(array(
					'news_cat_show' => 1,
					'news_archive_show' => 1,
				))
			),
			$this->user,
			'php'
		);
	}

	public function test_construct()
	{
		$this->setup_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.viewonline_overwrite_location',
		), array_keys(\nickvergessen\newspage\event\viewonline_listener::getSubscribedEvents()));
	}

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
				'newspage_controller#a:0:{}',
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
				'newspage_category_controller#' . serialize(array('forum_id' => 30)),
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
				'newspage_archive_controller#' . serialize(array('year' => 2014, 'month' => '04')),
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
				'newspage_category_archive_controller#' . serialize(array('forum_id' => 30, 'year' => 2014, 'month' => '04')),
				'VIEWONLINE_NEWS_CATEGORY_ARCHIVE Forum30 April 2014',
			),
		);
	}

	/**
	 * @dataProvider add_newspage_viewonline_data
	 */
	public function test_add_newspage_viewonline($on_page, $row, $forum_data, $location_url, $location, $expected_location_url, $expected_location)
	{
		$this->setup_listener();

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewonline_overwrite_location', array($this->listener, 'add_newspage_viewonline'));

		$event_data = array('on_page', 'row', 'location_url', 'location', 'forum_data');
		$event = new \phpbb\event\data(compact($event_data));
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
