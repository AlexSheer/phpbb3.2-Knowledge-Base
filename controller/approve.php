<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\controller;

use Symfony\Component\HttpFoundation\Response;

class approve
{
	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user $user User object */
	protected $user;
	/** @var \phpbb\cache\driver\driver_interface */

	protected $cache;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	//** @var string php_ext */
	protected $php_ext;

	/** @var log_interface */
	protected $log;

	//** @var string \sheer\knowledgebase\inc\functions_kb */
	protected $kb;

	/** @var helper */
	protected $helper;

	/** @var string ARTICLES_TABLE */
	protected $articles_table;

	/**
	* Constructor
	*
	* @access public
	*/

	public function __construct(
			\phpbb\controller\helper $helper,
			\phpbb\config\config $config,
			\phpbb\request\request_interface $request,
			\phpbb\db\driver\driver_interface $db,
			\phpbb\auth\auth $auth,
			\phpbb\template\template $template,
			\phpbb\user $user,
			\phpbb\cache\service $cache,
			\phpbb\log\log_interface $log,
			$phpbb_root_path,
			$php_ext,
			\sheer\knowledgebase\inc\functions_kb $kb,
			$kb_helper,
			$articles_table
		)
	{
		$this->routing = $helper;
		$this->config = $config;
		$this->request = $request;
		$this->db = $db;
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_cache = $cache;
		$this->phpbb_log = $log;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->kb = $kb;
		$this->helper = $kb_helper;
		$this->articles_table	= $articles_table;
	}

	public function approve_article()
	{
		// If not logged in
		$dd = $this->user->data;
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$mode = '';
			login_box('', ((isset($this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)])) ? $this->user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)] : $this->user->lang['LOGIN_EXPLAIN_APPROVE']));
		}

		$art_id = $this->request->variable('id', 0);
		$approve = $this->request->variable('approve', false);
		$disapprove = $this->request->variable('disapprove', false);

		$kb_article_info = $this->kb->get_kb_article_info($art_id);
		$kb_category_info = $this->kb->get_cat_info($kb_article_info['article_category_id']);
		$category_name = $kb_category_info['category_name'];

		$redirect = $this->routing->route('sheer_knowledgebase_category', array('id' => $kb_article_info['article_category_id']));

		if (!$this->kb->acl_kb_get($kb_article_info['article_category_id'], 'kb_m_approve') && !$this->auth->acl_get('a_manage_kb'))
		{
			trigger_error('RULES_KB_APPROVE_MOD_CANNOT');
		}

		if ($kb_article_info['approved'])
		{
			trigger_error('NO_NEED_APPROVE');
		}

		if($approve)
		{
			include_once($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
			$kb_data = $this->kb->obtain_kb_config();

			if ($this->config['kb_search_type'])
			{
				if (preg_match('#^\w+$#', $this->config['kb_search_type']) || file_exists($this->phpbb_root_path . 'ext/sheer/knowledgebase/search/' . $this->config['kb_search_type'] . '.' . $this->php_ext))
				{
					include($this->phpbb_root_path . 'ext/sheer/knowledgebase/search/' . $this->config['kb_search_type'] . '.' . $this->php_ext);
					$class = '\sheer\knowledgebase\search\\' . $this->config['kb_search_type'] . '';
					if (class_exists($class))
					{
						$error = false;
						$kb_search = new $class($error, $this->phpbb_root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user);
					}
				}
			}

			$sql = 'UPDATE ' . $this->articles_table . '
				SET approved = 1
				WHERE article_id = ' . (int) $art_id;
			$this->db->sql_query($sql);

			if (isset($kb_search))
			{
				// Add search index
				$this->phpbb_cache->purge();
				$kb_search->index('add', $art_id, $kb_article_info['article_body'], $kb_article_info['article_title'], $kb_article_info['author']);
			}

			if (!empty($kb_data['forum_id']) && $kb_data['anounce'])
			{
				$this->kb->submit_article($kb_article_info['article_category_id'], $kb_data['forum_id'], $kb_article_info['article_title'], $kb_article_info['article_description'], $kb_article_info['author'], $category_name, $art_id);
			}
		}
		else if ($disapprove)
		{
			$sql = 'DELETE
				FROM ' . $this->articles_table . '
				WHERE article_id = ' . (int) $art_id;
			$this->db->sql_query($sql);
		}

		if ($approve || $disapprove)
		{
			// add log
			$log_type = ($approve) ? 'LOG_LIBRARY_APPROVED_ARTICLE' : 'LOG_LIBRARY_REJECTED_ARTICLE';
			$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], $log_type, time(), array($kb_article_info['article_title'], $kb_category_info['category_name'], $kb_article_info['author']));
			// Send notification
			$message = ($approve) ? 'ARTICLE_APPROVED_SUCESS' : 'ARTICLE_DISAPPROVED_SUCESS';
			$kb_article_info['moderator_id'] = $this->user->data['user_id'];
			$notification_type = ($approve) ? 'sheer.knowledgebase.notification.type.approve' : 'sheer.knowledgebase.notification.type.disapprove';
			$this->helper->add_notification($kb_article_info, $notification_type);
			$sql = 'SELECT notification_type_id
				FROM ' . NOTIFICATION_TYPES_TABLE . '
				WHERE notification_type_name
				LIKE \'sheer.knowledgebase.notification.type.need_approval\'';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row['notification_type_id'])
			{
				$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
					WHERE item_id = ' . $kb_article_info['article_id'] . '
					AND notification_type_id = ' . $row['notification_type_id'] . ' ';
				$this->db->sql_query($sql);
			}
			meta_refresh(3, $redirect);
			trigger_error($message);
		}

		$this->template->assign_vars(array(
			'S_ACTION'		=> $this->routing->route('sheer_knowledgebase_approve', array('id' => $art_id)),
			)
		);

		page_header('' . $this->user->lang('LIBRARY'). ' &raquo; ' . $this->user->lang('APPROVE_ARTICLE') . '');
		$this->template->set_filenames(array(
			'body' => 'kb_approve_body.html'));

		page_footer();
		return new Response($this->template->return_display('body'), 200);
	}
}
