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
 * Class get_params_test
 * Testing \nickvergessen\newspage\route::get_params()
 *
 * @package nickvergessen\newspage\tests\route
 */
class get_params_test extends \phpbb_test_case
{
	/**
	 * @return array
	 */
	public function get_params_page_data()
	{
		return array(
			array(false, 2, array('forum_id' => 3, 'year' => '2014', 'month' => '04', 'page' => 2)),
			array(false, 1, array('forum_id' => 3, 'year' => '2014', 'month' => '04')),
			array(false, false, array('forum_id' => 3, 'year' => '2014', 'month' => '04')),
			array(false, true, array('forum_id' => 3, 'year' => '2014', 'month' => '04')),
			array(5, 2, array('forum_id' => 3, 'year' => '2014', 'month' => '04', 'page' => 2)),
			array(5, 1, array('forum_id' => 3, 'year' => '2014', 'month' => '04')),
			array(5, false, array('forum_id' => 3, 'year' => '2014', 'month' => '04', 'page' => 5)),
			array(5, true, array('forum_id' => 3, 'year' => '2014', 'month' => '04')),
		);
	}

	/**
	 * @dataProvider get_params_page_data
	 *
	 * @param int|bool $set_page
	 * @param int|bool $force_page
	 * @param array $expected
	 */
	public function test_get_params_page($set_page, $force_page, array $expected)
	{
		$this->get_params(array(
			'news_cat_show' => 1,
			'news_archive_show' => 1,
		), false, false, $set_page, 3, '2014/04', $force_page, $expected);
	}

	/**
	 * @return array
	 */
	public function get_params_category_data()
	{
		return array(
			array(true, false, 3, array('forum_id' => 3, 'page' => 2)),
			array(false, false, 3, array('page' => 2)),
			array(true, false, false, array('page' => 2)),
			array(false, false, false, array('page' => 2)),
			array(true, false, true, array('page' => 2)),
			array(false, false, true, array('page' => 2)),
			array(true, 30, 3, array('forum_id' => 3, 'page' => 2)),
			array(false, 30, 3, array('page' => 2)),
			array(true, 30, false, array('forum_id' => 30, 'page' => 2)),
			array(false, 30, false, array('page' => 2)),
			array(true, 30, true, array('page' => 2)),
			array(false, 30, true, array('page' => 2)),
		);
	}

	/**
	 * @dataProvider get_params_category_data
	 *
	 * @param bool $config
	 * @param int|bool $set_category
	 * @param int|bool $force_category
	 * @param array $expected
	 */
	public function test_get_params_category($config, $set_category, $force_category, array $expected)
	{
		$this->get_params(array(
			'news_cat_show' => $config,
			'news_archive_show' => 0,
		), $set_category, false, false, $force_category, false, 2, $expected);
	}

	/**
	 * @return array
	 */
	public function get_params_archive_data()
	{
		return array(
			array(true, false, '2014/04', array('year' => 2014, 'month' => '04', 'page' => 2)),
			array(false, false, '2014/04', array('page' => 2)),
			array(true, false, false, array('page' => 2)),
			array(false, false, false, array('page' => 2)),
			array(true, false, true, array('page' => 2)),
			array(false, false, true, array('page' => 2)),
			array(true, '2012/03', '2014/04', array('year' => 2014, 'month' => '04', 'page' => 2)),
			array(false, '2012/03', '2014/04', array('page' => 2)),
			array(true, '2012/03', false, array('year' => 2012, 'month' => '03', 'page' => 2)),
			array(false, '2012/03', false, array('page' => 2)),
			array(true, '2012/03', true, array('page' => 2)),
			array(false, '2012/03', true, array('page' => 2)),

			array(true, '2012/3', '2014/4', array('year' => 2014, 'month' => '04', 'page' => 2)),
			array(false, '2012/3', '2014/4', array('page' => 2)),
			array(true, '2012/3', false, array('year' => 2012, 'month' => '03', 'page' => 2)),
			array(false, '2012/3', false, array('page' => 2)),
			array(true, '2012/3', true, array('page' => 2)),
			array(false, '2012/3', true, array('page' => 2)),
		);
	}

	/**
	 * @dataProvider get_params_archive_data
	 *
	 * @param bool $config
	 * @param mixed $set_archive
	 * @param mixed $force_archive
	 * @param array $expected
	 */
	public function test_get_params_archive($config, $set_archive, $force_archive, array $expected)
	{
		$this->get_params(array(
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
	 * @param array $expected
	 */
	protected function get_params(array $config, $set_category, $set_archive, $set_page, $force_category, $force_archive, $force_page, $expected)
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

		$this->assertEquals($expected, $route->get_params($force_category, $force_archive, $force_page));
	}
}
