<?php
/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2013 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
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
	* @param \nickvergessen\newspage\helper	$helper		Newspage helper object
	* @param \phpbb\template\template	$template	Template object
	*/
	public function __construct(\nickvergessen\newspage\helper $helper, \phpbb\template\template $template)
	{
		$this->helper = $helper;
		$this->template = $template;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'nickvergessen/newspage',
			'lang_set' => 'newspage',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_NEWSPAGE'	=> $this->helper->generate_route()->get_url(),
		));
	}
}
