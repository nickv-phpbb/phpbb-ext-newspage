<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2013 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\event;

use nickvergessen\newspage\helper;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class menu_link_listener
 * Creates the link in the head navbar
 *
 * @package nickvergessen\newspage\event
 */
class menu_link_listener implements EventSubscriberInterface
{
	/* @var helper */
	protected $helper;

	/* @var template */
	protected $template;

	/**
	 * Register to the events
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
		);
	}

	/**
	 * Constructor
	 *
	 * @param helper $helper
	 * @param template $template
	 */
	public function __construct(helper $helper, template $template)
	{
		$this->helper = $helper;
		$this->template = $template;
	}

	/**
	 * @param array $event
	 * @return null
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'nickvergessen/newspage',
			'lang_set' => 'newspage',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * @return null
	 */
	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_NEWSPAGE'	=> $this->helper->generate_route()->get_url(),
		));
	}
}
