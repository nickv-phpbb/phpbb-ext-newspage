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

namespace nickvergessen\newspage\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class settings
 *
 * @package nickvergessen\newspage\controller
 */
class settings
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\user $user
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\controller\helper $helper, \phpbb\user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->request = $request;
		$this->helper = $helper;
		$this->user = $user;
	}

	/**
	 * Newspage controller to display multiple news
	 * @return Response A Symfony Response object
	 * @throws http_exception
	 */
	public function manage()
	{
		$this->meta_refresh();

		// Redirect non admins back to the newspage
		if (!$this->auth->acl_get('a_board'))
		{
			throw new http_exception(403, 'NO_AUTH_OPERATION');
		}

		// Is someone trying to fool us?
		if (!check_form_key('newspage') || !$this->request->is_set_post('submit'))
		{
			throw new http_exception(400, 'FORM_INVALID');
		}

		if ($this->request->variable('news_char_limit', 0) !== 0)
		{
			$this->config->set('news_char_limit',	max(100, $this->request->variable('news_char_limit', 0)));
		}
		else
		{
			// "0" means no trimming
			$this->config->set('news_char_limit', 0);
		}

		$this->config->set('news_forums',		implode(',', $this->request->variable('news_forums', array(0))));
		$this->config->set('news_number',		max(1, $this->request->variable('news_number', 0)));
		$this->config->set('news_pages',		max(1, $this->request->variable('news_pages', 0)));
		$this->config->set('news_user_info',	$this->request->variable('news_user_info', false));
		$this->config->set('news_post_buttons',	$this->request->variable('news_post_buttons', false));
		$this->config->set('news_shadow',		$this->request->variable('news_shadow_show', false));
		$this->config->set('news_attach_show',	$this->request->variable('news_attach_show', false));
		$this->config->set('news_cat_show',		$this->request->variable('news_cat_show', false));
		$this->config->set('news_archive_show',	$this->request->variable('news_archive_show', false));

		$this->user->add_lang_ext('nickvergessen/newspage', 'newspage');
		return $this->helper->message('NEWS_SAVED');
	}

	/**
	 * Only put into a method for better mockability
	 *
	 * @return null
	 */
	public function meta_refresh()
	{
		meta_refresh(3, $this->helper->route('nickvergessen_newspage_controller'));
	}
}
