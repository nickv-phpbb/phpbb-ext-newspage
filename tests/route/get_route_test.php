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

namespace nickvergessen\newspage\tests\route;

use nickvergessen\newspage\route;
use phpbb\config\config;

/**
 * Class get_route_test
 * Testing \nickvergessen\newspage\route::get_route()
 *
 * @package nickvergessen\newspage\tests\route
 */
class get_route_test extends \phpbb_test_case
{
	/**
	 * @return array
	 */
	public function get_route_page_data()
	{
		return array(
			array(false, 2, 'nickvergessen_newspage_category_archive_page_controller'),
			array(false, 1, 'nickvergessen_newspage_category_archive_controller'),
			array(false, false, 'nickvergessen_newspage_category_archive_controller'),
			array(false, true, 'nickvergessen_newspage_category_archive_controller'),
			array(5, 2, 'nickvergessen_newspage_category_archive_page_controller'),
			array(5, 1, 'nickvergessen_newspage_category_archive_controller'),
			array(5, false, 'nickvergessen_newspage_category_archive_page_controller'),
			array(5, true, 'nickvergessen_newspage_category_archive_controller'),
		);
	}

	/**
	 * @dataProvider get_route_page_data
	 *
	 * @param int|bool $set_page
	 * @param int|bool $force_page
	 * @param string $expected
	 */
	public function test_get_route_page($set_page, $force_page, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => 1,
			'news_archive_show' => 1,
		), false, false, $set_page, 3, '2014/14', $force_page, $expected);
	}

	/**
	 * @return array
	 */
	public function get_route_category_data()
	{
		return array(
			array(true, false, 3, 'nickvergessen_newspage_category_page_controller'),
			array(false, false, 3, 'nickvergessen_newspage_page_controller'),
			array(true, false, false, 'nickvergessen_newspage_page_controller'),
			array(false, false, false, 'nickvergessen_newspage_page_controller'),
			array(true, false, true, 'nickvergessen_newspage_page_controller'),
			array(false, false, true, 'nickvergessen_newspage_page_controller'),
			array(true, 30, 3, 'nickvergessen_newspage_category_page_controller'),
			array(false, 30, 3, 'nickvergessen_newspage_page_controller'),
			array(true, 30, false, 'nickvergessen_newspage_category_page_controller'),
			array(false, 30, false, 'nickvergessen_newspage_page_controller'),
			array(true, 30, true, 'nickvergessen_newspage_page_controller'),
			array(false, 30, true, 'nickvergessen_newspage_page_controller'),
		);
	}

	/**
	 * @dataProvider get_route_category_data
	 *
	 * @param bool $config
	 * @param int|bool $set_category
	 * @param int|bool $force_category
	 * @param string $expected
	 */
	public function test_get_route_category($config, $set_category, $force_category, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => $config,
			'news_archive_show' => 0,
		), $set_category, false, false, $force_category, false, 2, $expected);
	}

	/**
	 * @return array
	 */
	public function get_route_archive_data()
	{
		return array(
			array(true, false, '2014/04', 'nickvergessen_newspage_archive_page_controller'),
			array(false, false, '2014/04', 'nickvergessen_newspage_page_controller'),
			array(true, false, false, 'nickvergessen_newspage_page_controller'),
			array(false, false, false, 'nickvergessen_newspage_page_controller'),
			array(true, false, true, 'nickvergessen_newspage_page_controller'),
			array(false, false, true, 'nickvergessen_newspage_page_controller'),
			array(true, '2012/03', '2014/04', 'nickvergessen_newspage_archive_page_controller'),
			array(false, '2012/03', '2014/04', 'nickvergessen_newspage_page_controller'),
			array(true, '2012/03', false, 'nickvergessen_newspage_archive_page_controller'),
			array(false, '2012/03', false, 'nickvergessen_newspage_page_controller'),
			array(true, '2012/03', true, 'nickvergessen_newspage_page_controller'),
			array(false, '2012/03', true, 'nickvergessen_newspage_page_controller'),
		);
	}

	/**
	 * @dataProvider get_route_archive_data
	 *
	 * @param bool $config
	 * @param mixed $set_archive
	 * @param mixed $force_archive
	 * @param string $expected
	 */
	public function test_get_route_archive($config, $set_archive, $force_archive, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => 1,
			'news_archive_show' => $config,
		), false, $set_archive, false, false, $force_archive, 2, $expected);
	}

	/**
	 * @param array $config
	 * @param int|bool $set_category
	 * @param mixed $set_archive
	 * @param int|bool $set_page
	 * @param int|bool $force_category
	 * @param mixed $force_archive
	 * @param int|bool $force_page
	 * @param string $expected
	 */
	public function get_route($config, $set_category, $set_archive, $set_page, $force_category, $force_archive, $force_page, $expected)
	{
		$config = new config($config);

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		/** @var \phpbb\controller\helper $controller_helper */
		$route = new route(
			$controller_helper,
			$config
		);

		if ($set_category)
		{
			$route->set_category($set_category);
		}
		if ($set_archive)
		{
			list($year, $month) = explode('/', $set_archive);
			$route->set_archive_month($month)
				->set_archive_year($year);
		}
		if ($set_page)
		{
			$route->set_page($set_page);
		}

		$this->assertEquals($expected, $route->get_route($force_category, $force_archive, $force_page));
	}
}
