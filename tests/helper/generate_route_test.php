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

namespace nickvergessen\newspage\tests\helper;

use phpbb\config\config;
use nickvergessen\newspage\helper;

/**
 * Class generate_route_test
 * Testing \nickvergessen\newspage\helper
 *
 * @package nickvergessen\newspage\tests\helper
 */
class generate_route_test extends \phpbb_test_case
{
	/**
	 * @return array
	 */
	public function generate_route_data()
	{
		return array(
			array(true, true, 30, '2014/04', 2, array('forum_id' => 30, 'year' => '2014', 'month' => '04', 'page' => 2)),
			array(true, true, 30, array('y' => '2014', 'm' => '04'), 2, array('forum_id' => 30, 'year' => '2014', 'month' => '04', 'page' => 2)),
			array(true, false, 30, '2014/04', 2, array('forum_id' => 30, 'page' => 2)),
			array(true, false, 30, array('y' => '2014', 'm' => '04'), 2, array('forum_id' => 30, 'page' => 2)),
			array(false, true, 30, '2014/04', 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(false, true, 30, array('y' => '2014', 'm' => '04'), 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(false, false, 30, '2014/04', 2, array('page' => 2)),
			array(false, false, 30, array('y' => '2014', 'm' => '04'), 2, array('page' => 2)),
			array(true, true, false, '2014/04', 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(true, true, false, array('y' => '2014', 'm' => '04'), 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(true, false, false, '2014/04', 2, array('page' => 2)),
			array(true, false, false, array('y' => '2014', 'm' => '04'), 2, array('page' => 2)),
			array(false, true, false, '2014/04', 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(false, true, false, array('y' => '2014', 'm' => '04'), 2, array('year' => '2014', 'month' => '04', 'page' => 2)),
			array(false, false, false, '2014/04', 2, array('page' => 2)),
			array(false, false, false, array('y' => '2014', 'm' => '04'), 2, array('page' => 2)),
			array(true, true, 30, false, 2, array('forum_id' => 30, 'page' => 2)),
			array(true, false, 30, false, 2, array('forum_id' => 30, 'page' => 2)),
			array(false, true, 30, false, 2, array('page' => 2)),
			array(false, false, 30, false, 2, array('page' => 2)),
			array(true, true, 30, '2014/04', 1, array('forum_id' => 30, 'year' => '2014', 'month' => '04')),
			array(true, true, 30, array('y' => '2014', 'm' => '04'), 1, array('forum_id' => 30, 'year' => '2014', 'month' => '04')),
			array(true, false, 30, '2014/04', 1, array('forum_id' => 30)),
			array(true, false, 30, array('y' => '2014', 'm' => '04'), 1, array('forum_id' => 30)),
			array(false, true, 30, '2014/04', 1, array('year' => '2014', 'month' => '04')),
			array(false, true, 30, array('y' => '2014', 'm' => '04'), 1, array('year' => '2014', 'month' => '04')),
			array(false, false, 30, '2014/04', 1, array()),
			array(false, false, 30, array('y' => '2014', 'm' => '04'), 1, array()),
		);
	}

	/**
	 * @dataProvider generate_route_data
	 *
	 * @param bool $config_category
	 * @param bool $config_archive
	 * @param int $set_category
	 * @param mixed $set_archive
	 * @param int $set_page
	 * @param array $expected
	 */
	public function test_generate_route($config_category, $config_archive, $set_category, $set_archive, $set_page, array $expected)
	{
		$config = new config(array(
			'news_cat_show' => $config_category,
			'news_archive_show' => $config_archive,
		));

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		/** @var \phpbb\controller\helper $controller_helper */
		$helper = new helper(
			$controller_helper,
			$config
		);

		$route = $helper->generate_route($set_category, $set_archive, $set_page);

		$this->assertEquals($expected, $route->get_params());
	}
}
