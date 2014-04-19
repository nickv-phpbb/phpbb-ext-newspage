<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\controller;

require __DIR__ . '/../../../../../includes/functions.php';

class settings_test extends \phpbb_test_case
{
	protected $path_helper;

	protected function get_path_helper()
	{
		if (!($this->path_helper instanceof \phpbb\path_helper))
		{
			$this->path_helper = new \phpbb\path_helper(
				new \phpbb\symfony_request(
					new \phpbb_mock_request()
				),
				new \phpbb\filesystem(),
				$this->phpbb_root_path,
				'php'
			);
		}
		return $this->path_helper;
	}

	protected function setUp()
	{
		parent::setUp();

		global $user;

		$user = new \nickvergessen\newspage\tests\mock\user();
		$user->lang = array('INSECURE_REDIRECT' => 'insecure');
	}


	public function manage_data()
	{
		return array(
			array(
				array(
					array('a_board', 0, false),
				), array(), array(), 403, 'NO_AUTH_OPERATION',
			),
			array(
				array(
					array('a_board', 0, true),
				), array(), array(), 400, 'FORM_INVALID',
			),
			array(
				array(
					array('a_board', 0, true),
				), array(
					array('submit', true)
				), array(), 400, 'FORM_INVALID',
			),
			array(
				array(
					array('a_board', 0, true),
				), array(
					array('submit', true)
				), array(
					array('creation_time', 0, false, \phpbb\request\request_interface::REQUEST, 0),
					array('form_token', '', false, \phpbb\request\request_interface::REQUEST, sha1('0newspage')),
					array('news_forums', array(0), false, \phpbb\request\request_interface::REQUEST, array()),
				), 200, 'NEWS_SAVED',
			),
		);
	}

	/**
	 * @dataProvider manage_data
	 */
	public function test_manage($auth_map, $request_map, $variable_map, $status_code, $page_content)
	{
		$auth = $this->getMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap($auth_map));

		$request = $this->getMock('\phpbb\request\request');
		$request->expects($this->any())
			->method('is_set_post')
			->with($this->anything())
			->will($this->returnValueMap($request_map));

		$request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap($variable_map));
		request_var(false, false, false, false, $request);

		$controller = new \nickvergessen\newspage\tests\mock\settings_controller(
			$auth,
			new \phpbb\config\config(array()),
			$request,
			new \nickvergessen\newspage\tests\mock\user(),
			new \nickvergessen\newspage\tests\mock\controller_helper()
		);

		$_POST['creation_time'] = $_POST['form_token'] = 0;
		define('DEBUG_TEST', true);

		$response = $controller->manage();
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals($status_code, $response->getStatusCode());
		$this->assertEquals($page_content, $response->getContent());
	}
}
