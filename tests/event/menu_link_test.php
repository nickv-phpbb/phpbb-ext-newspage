<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\tests\event;

class menu_link_test extends \phpbb_test_case
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \nickvergessen\newspage\event\menu_link_listener */
	protected $listener;

	public function setup_listener()
	{
		$this->template = new \nickvergessen\newspage\tests\mock\template();

		$this->listener = new \nickvergessen\newspage\event\menu_link_listener(
			new \nickvergessen\newspage\helper(
				new \nickvergessen\newspage\tests\mock\controller_helper(),
				new \phpbb\config\config(array())
			),
			$this->template
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
			'core.user_setup',
			'core.page_header',
		), array_keys(\nickvergessen\newspage\event\menu_link_listener::getSubscribedEvents()));
	}

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

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($this->listener, 'load_language_on_setup'));

		$event_data = array('lang_set_ext');
		$event = new \phpbb\event\data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		$lang_set_ext = $event->get_data_filtered($event_data);
		$lang_set_ext = $lang_set_ext['lang_set_ext'];

		foreach ($expected_contains as $expected)
		{
			$this->assertContains($expected, $lang_set_ext);
		}
	}

	public function test_add_page_header_link()
	{
		$this->setup_listener();

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header', array($this->listener, 'add_page_header_link'));
		$dispatcher->dispatch('core.page_header');

		$this->assertEquals(array(
			'U_NEWSPAGE' => 'newspage_controller#a:0:{}'
		), $this->template->get_template_vars());
	}
}
