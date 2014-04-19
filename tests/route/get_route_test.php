<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\route;

class get_route_test extends \phpbb_test_case
{
	public function get_route_page_data()
	{
		return array(
			array(false, 2, 'newspage_category_archive_page_controller'),
			array(false, 1, 'newspage_category_archive_controller'),
			array(false, false, 'newspage_category_archive_controller'),
			array(false, true, 'newspage_category_archive_controller'),
			array(5, 2, 'newspage_category_archive_page_controller'),
			array(5, 1, 'newspage_category_archive_controller'),
			array(5, false, 'newspage_category_archive_page_controller'),
			array(5, true, 'newspage_category_archive_controller'),
		);
	}

	/**
	 * @dataProvider get_route_page_data
	 */
	public function test_get_route_page($set_page, $force_page, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => 1,
			'news_archive_show' => 1,
		), false, false, $set_page, 3, '2014/14', $force_page, $expected);
	}

	public function get_route_category_data()
	{
		return array(
			array(true, false, 3, 'newspage_category_page_controller'),
			array(false, false, 3, 'newspage_page_controller'),
			array(true, false, false, 'newspage_page_controller'),
			array(false, false, false, 'newspage_page_controller'),
			array(true, false, true, 'newspage_page_controller'),
			array(false, false, true, 'newspage_page_controller'),
			array(true, 30, 3, 'newspage_category_page_controller'),
			array(false, 30, 3, 'newspage_page_controller'),
			array(true, 30, false, 'newspage_category_page_controller'),
			array(false, 30, false, 'newspage_page_controller'),
			array(true, 30, true, 'newspage_page_controller'),
			array(false, 30, true, 'newspage_page_controller'),
		);
	}

	/**
	 * @dataProvider get_route_category_data
	 */
	public function test_get_route_category($config, $set_category, $force_category, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => $config,
			'news_archive_show' => 0,
		), $set_category, false, false, $force_category, false, 2, $expected);
	}

	public function get_route_archive_data()
	{
		return array(
			array(true, false, '2014/04', 'newspage_archive_page_controller'),
			array(false, false, '2014/04', 'newspage_page_controller'),
			array(true, false, false, 'newspage_page_controller'),
			array(false, false, false, 'newspage_page_controller'),
			array(true, false, true, 'newspage_page_controller'),
			array(false, false, true, 'newspage_page_controller'),
			array(true, '2012/03', '2014/04', 'newspage_archive_page_controller'),
			array(false, '2012/03', '2014/04', 'newspage_page_controller'),
			array(true, '2012/03', false, 'newspage_archive_page_controller'),
			array(false, '2012/03', false, 'newspage_page_controller'),
			array(true, '2012/03', true, 'newspage_page_controller'),
			array(false, '2012/03', true, 'newspage_page_controller'),
		);
	}

	/**
	 * @dataProvider get_route_archive_data
	 */
	public function test_get_route_archive($config, $set_archive, $force_archive, $expected)
	{
		$this->get_route(array(
			'news_cat_show' => 1,
			'news_archive_show' => $config,
		), false, $set_archive, false, false, $force_archive, 2, $expected);
	}

	public function get_route($config, $set_category, $set_archive, $set_page, $force_category, $force_archive, $force_page, $expected)
	{
		$config = new \phpbb\config\config($config);
		$route = new \nickvergessen\newspage\route(
			new \nickvergessen\newspage\tests\mock\controller_helper(),
			$config
		);

		if ($set_category)
		{
			$route = $route->set_category($set_category);
		}
		if ($set_archive)
		{
			list($year, $month) = explode('/', $set_archive);
			$route = $route->set_archive_month($month);
			$route = $route->set_archive_year($year);
		}
		if ($set_page)
		{
			$route = $route->set_page($set_page);
		}

		$this->assertEquals($expected, $route->get_route($force_category, $force_archive, $force_page));
	}
}
