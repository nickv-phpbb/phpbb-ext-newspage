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

namespace nickvergessen\newspage\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class menu_link_listener
 * Creates the link in the head navbar
 *
 * @package nickvergessen\newspage\event
 */
class menu_link_listener implements EventSubscriberInterface
{
	/* @var \nickvergessen\newspage\helper */
	protected $helper;

	/* @var \phpbb\template\template */
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
	 * @param \nickvergessen\newspage\helper $helper
	 * @param \phpbb\template\template $template
	 */
	public function __construct(\nickvergessen\newspage\helper $helper, \phpbb\template\template $template)
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
