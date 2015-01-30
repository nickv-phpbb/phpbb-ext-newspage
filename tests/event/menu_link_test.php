<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\event;

use phpbb\config\config;
use nickvergessen\newspage\helper;
use nickvergessen\newspage\event\menu_link_listener;
use phpbb\event\data;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class menu_link_test
 * Testing \nickvergessen\newspage\event\menu_link_listener
 *
 * @package nickvergessen\newspage\tests\event
 */
class menu_link_test extends \phpbb_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $template;

	/** @var menu_link_listener */
	protected $listener;

	/**
	 * @return null
	 */
	public function setup_listener()
	{
		$this->template = $template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->expects($this->any())
			->method('route')
			->willReturnCallback(function ($route, array $params = array()) {
				return $route . '#' . serialize($params);
			});

		/** @var \phpbb\controller\helper $controller_helper */
		/** @var \phpbb\template\template $template */
		$this->listener = new menu_link_listener(
			new helper(
				$controller_helper,
				new config(array())
			),
			$template
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
			'core.user_setup',
			'core.page_header',
		), array_keys(menu_link_listener::getSubscribedEvents()));
	}

	/**
	 * @return null
	 */
	public function load_language_on_setup_data()
	{
		return array(
			array(
				array(),
				array(
					array(
						'ext_name' => 'nickvergessen/newspage',
						'lang_set' => 'newspage',
					),
				),
			),
			array(
				array(
					array(
						'ext_name' => 'board3/portal',
						'lang_set' => 'main',
					),
				),
				array(
					array(
						'ext_name' => 'board3/portal',
						'lang_set' => 'main',
					),
					array(
						'ext_name' => 'nickvergessen/newspage',
						'lang_set' => 'newspage',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider load_language_on_setup_data
	 */
	public function test_load_language_on_setup($lang_set_ext, $expected_contains)
	{
		$this->setup_listener();

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($this->listener, 'load_language_on_setup'));

		$event_data = array('lang_set_ext');
		$event = new data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		extract($event->get_data_filtered($event_data));

		foreach ($expected_contains as $expected)
		{
			$this->assertContains($expected, $lang_set_ext);
		}
	}

	/**
	 * @return null
	 */
	public function test_add_page_header_link()
	{
		$this->setup_listener();

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'U_NEWSPAGE' => 'newspage_controller#a:0:{}'
			));

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('core.page_header', array($this->listener, 'add_page_header_link'));
		$dispatcher->dispatch('core.page_header');
	}
}
