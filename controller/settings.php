<?php

/**
 *
 * @package NV Newspage Extension
 * @copyright (c) 2014 nickvergessen
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace nickvergessen\newspage\controller;

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

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth			$auth		Auth object
	 * @param \phpbb\config\config		$config		Config object
	 * @param \phpbb\request\request		$request	Request object
	 * @param \phpbb\user				$user		User object
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\request\request $request, \phpbb\user $user, \phpbb\controller\helper $helper)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->helper = $helper;
	}

	/**
	 * Newspage controller to display multiple news
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function manage()
	{
		// Redirect non admins back to the newspage
		if (!$this->auth->acl_get('a_board'))
		{
			return $this->finish('NO_AUTH_OPERATION', 403);
		}

		// Is someone trying to fool us?
		if (!check_form_key('newspage') || !$this->request->is_set_post('submit'))
		{
			return $this->finish('FORM_INVALID', 400);
		}

		$this->config->set('news_char_limit',	max(100, $this->request->variable('news_char_limit', 0)));
		$this->config->set('news_forums',		implode(',', $this->request->variable('news_forums', array(0))));
		$this->config->set('news_number',		max(1, $this->request->variable('news_number', 0)));
		$this->config->set('news_pages',		max(1, $this->request->variable('news_pages', 0)));
		$this->config->set('news_post_buttons',	$this->request->variable('news_post_buttons', false));
		$this->config->set('news_user_info',	$this->request->variable('news_user_info', false));
		$this->config->set('news_shadow',		$this->request->variable('news_shadow_show', false));
		$this->config->set('news_attach_show',	$this->request->variable('news_attach_show', false));
		$this->config->set('news_cat_show',		$this->request->variable('news_cat_show', false));
		$this->config->set('news_archive_show',	$this->request->variable('news_archive_show', false));

		return $this->finish('NEWS_SAVED', 200, 3);
	}

	/**
	 * @param string $message
	 * @param int $status_code
	 * @param int $redirect_time
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function finish($message, $status_code, $redirect_time = 10)
	{
		$this->meta_refresh($redirect_time);
		return $this->helper->error($this->user->lang($message), $status_code);
	}

	/**
	 * @param int $redirect_time
	 * @return null
	 */
	public function meta_refresh($redirect_time)
	{
		meta_refresh($redirect_time, $this->helper->route('newspage_controller'));
	}
}
