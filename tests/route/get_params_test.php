<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\route;

class get_params_test extends \phpbb_test_case
{
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
	 */
	public function test_get_params_page($set_page, $force_page, $expected)
	{
		$this->get_params(array(
			'news_cat_show' => 1,
			'news_archive_show' => 1,
		), false, false, $set_page, 3, '2014/04', $force_page, $expected);
	}

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
	 */
	public function test_get_params_category($config, $set_category, $force_category, $expected)
	{
		$this->get_params(array(
			'news_cat_show' => $config,
			'news_archive_show' => 0,
		), $set_category, false, false, $force_category, false, 2, $expected);
	}

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
	 */
	public function test_get_params_archive($config, $set_archive, $force_archive, $expected)
	{
		$this->get_params(array(
			'news_cat_show' => 1,
			'news_archive_show' => $config,
		), false, $set_archive, false, false, $force_archive, 2, $expected);
	}

	public function get_params($config, $set_category, $set_archive, $set_page, $force_category, $force_archive, $force_page, $expected)
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

		$this->assertEquals($expected, $route->get_params($force_category, $force_archive, $force_page));
	}
}
