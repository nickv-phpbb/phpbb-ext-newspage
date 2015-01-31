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

namespace nickvergessen\newspage\tests\controller;

use nickvergessen\newspage\tests\mock\settings_controller;
use phpbb\config\config;
use phpbb\exception\http_exception;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\Response;

require __DIR__ . '/../../../../../includes/functions.php';

/**
 * Class settings_test
 * Testing \nickvergessen\newspage\controller\settings
 *
 * @package nickvergessen\newspage\tests\controller
 */
class settings_test extends \phpbb_test_case
{
	/**
	 * @return array
	 */
	public function manage_data()
	{
		return array(
			array(
				array(
					array('a_board', 0, true),
				),
				array(
					array('submit', true),
					array('creation_time', true),
					array('form_token', true),
				),
				array(
					array('creation_time', 0, false, request_interface::REQUEST, 0),
					array('form_token', '', false, request_interface::REQUEST, sha1('0newspage')),
					array('news_forums', array(0), false, request_interface::REQUEST, array()),
				),
				200,
				'NEWS_SAVED',
			),
		);
	}

	/**
	 * @dataProvider manage_data
	 *
	 * @param array $auth_map
	 * @param array $request_map
	 * @param array $variable_map
	 * @param int $status_code
	 * @param string $page_content
	 */
	public function test_manage(array $auth_map, array $request_map, array $variable_map, $status_code, $page_content)
	{
		$controller = $this->get_controller($auth_map, $request_map, $variable_map);
		$response = $controller->manage();

		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals($page_content, $response->getContent());
		$this->assertEquals($status_code, $response->getStatusCode());
	}

	/**
	 * @return array
	 */
	public function manage_throws_data()
	{
		return array(
			array(
				array(
					array('a_board', 0, false),
				),
				array(),
				array(),
				403,
				'NO_AUTH_OPERATION',
			),
			array(
				array(
					array('a_board', 0, true),
				),
				array(),
				array(),
				400,
				'FORM_INVALID',
			),
			array(
				array(
					array('a_board', 0, true),
				),
				array(
					array('submit', true),
					array('creation_time', true),
					array('form_token', true),
				),
				array(),
				400,
				'FORM_INVALID',
			),
		);
	}

	/**
	 * @dataProvider manage_throws_data
	 *
	 * @param array $auth_map
	 * @param array $request_map
	 * @param array $variable_map
	 * @param int $status_code
	 * @param string $page_content
	 */
	public function test_manage_throws(array $auth_map, array $request_map, array $variable_map, $status_code, $page_content)
	{

		$controller = $this->get_controller($auth_map, $request_map, $variable_map);
		try
		{
			$controller->manage();
			$this->fail('Expected \phpbb\exception\http_exception to be thrown'
				. ' but no exception thrown');
		}
		catch (http_exception $exception)
		{
			$this->assertEquals($status_code, $exception->getStatusCode());
			$this->assertEquals($page_content, $exception->getMessage());
		}
		catch (\Exception $exception)
		{
			$this->fail('Expected \phpbb\exception\http_exception to be thrown'
				. ' but "' . get_class($exception) . '" thrown');
		}
	}

	/**
	 * @param array $auth_map
	 * @param array $request_map
	 * @param array $variable_map
	 * @return \nickvergessen\newspage\controller\settings
	 */
	protected function get_controller(array $auth_map, array $request_map, array $variable_map)
	{
		global $request;

		$_POST['creation_time'] = $_POST['form_token'] = 0;
		define('DEBUG_TEST', true);

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

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('message')
			->willReturnCallback(function ($message, $parameters = array(), $title = 'INFORMATION', $code = 200) {
				// TODO php 5.4: $this->assertInternalType('array', $parameters);
				// TODO php 5.4: $this->assertEmtpy($parameters);
				if (!is_array($parameters) || !empty($parameters))
				{
					throw new \InvalidArgumentException('Expected $parameters to be an empty array');
				}

				// TODO php 5.4: $this->assertEquals('INFORMATION', $title);
				if ($title !== 'INFORMATION')
				{
					throw new \InvalidArgumentException('Expected $title to be \'INFORMATION\'');
				}

				return new Response($message, $code);
			});

		/** @var \phpbb\auth\auth $auth */
		/** @var \phpbb\request\request $request */
		/** @var \phpbb\controller\helper $controller_helper */
		return new settings_controller(
			$auth,
			new config(array()),
			$request,
			$controller_helper
		);
	}
}
