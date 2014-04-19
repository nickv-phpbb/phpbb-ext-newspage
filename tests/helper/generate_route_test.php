<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\helper;

class generate_route_test extends \phpbb_test_case
{
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
	 */
	public function test_generate_route($config_category, $config_archive, $set_category, $set_archive, $set_page, $expected)
	{
		$config = new \phpbb\config\config(array(
			'news_cat_show' => $config_category,
			'news_archive_show' => $config_archive,
		));
		$helper = new \nickvergessen\newspage\helper(
			new \nickvergessen\newspage\tests\mock\controller_helper(),
			$config
		);

		$route = $helper->generate_route($set_category, $set_archive, $set_page);

		$this->assertEquals($expected, $route->get_params());
	}
}
