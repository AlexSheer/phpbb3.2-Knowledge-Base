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

class index
{
	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user $user User object */
	protected $user;

	/** @var helper */
	protected $helper;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	//** @var string php_ext */
	protected $php_ext;

	//** @var string \sheer\knowledgebase\inc\functions_kb */
	protected $kb;

	/** @var string KB_CATEGORIES_TABLE */
	protected $categories_table;

	/** @var string ARTICLES_TABLE */
	protected $articles_table;

	/**
	* Constructor
	*
	* @access public
	*/

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\request\request_interface $request,
		\phpbb\pagination $pagination,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\controller\helper $helper,
		$phpbb_root_path,
		$php_ext,
		\sheer\knowledgebase\inc\functions_kb $kb,
		$articles_table,
		$categories_table
	)
	{
		$this->config = $config;
		$this->request = $request;
		$this->pagination = $pagination;
		$this->db = $db;
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->kb = $kb;
		$this->articles_table	= $articles_table;
		$this->categories_table	= $categories_table;
	}

	public function main()
	{
		$category_id = $this->request->variable('id', 0);
		$sql = 'SELECT category_id, category_name, category_details, parent_id
			FROM  ' . $this->categories_table . '
			WHERE parent_id = 0
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($catrow = $this->db->sql_fetchrow($result))
		{
			$exclude_cats = array();
			foreach ($this->kb->get_category_branch($catrow['category_id'], 'children') as $row)
			{
				$exclude_cats[] = $row['category_id'];
			}
			array_shift($exclude_cats);

			$sql_where = ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($catrow['category_id'], 'kb_m_approve')) ? '' : 'AND approved = 1';
			$sql = 'SELECT COUNT(article_id) AS articles
				FROM ' . $this->articles_table . '
				WHERE article_category_id = '. (int) $catrow['category_id'] .'
					' . $sql_where;
			$res = $this->db->sql_query($sql);
			$art_row = $this->db->sql_fetchrow($res);
			$this->db->sql_freeresult($res);

			$this->template->assign_block_vars('catrow', array(
				'U_CATEGORY'		=> $this->helper->route('sheer_knowledgebase_category', array('id' => $catrow['category_id'])),
				'CAT_NAME'			=> $catrow['category_name'],
				'CAT_ARTICLES'		=> $art_row['articles'],
				'CAT_DESCRIPTION' 	=> $catrow['category_details'],
				'SUBCATS'			=> $this->kb->get_cat_list ($catrow['parent_id'], $exclude_cats),
				)
			);
		}
		$this->db->sql_freeresult($result);

		// Output the page
		$this->template->assign_vars(array(
			'LIBRARY_TITLE'	=> $this->user->lang('LIBRARY'),
		));

		$this->template->assign_vars(array(
			'S_ACTION'				=> $this->helper->route('sheer_knowledgebase_category', array('id' => $catrow['category_id'])),
			'U_KB_SEARCH'			=> $this->helper->route('sheer_knowledgebase_library_search'),
			'S_IS_SEARCH'			=> ($this->config['kb_search']) ? true : false,
			'S_KB_SEARCH_ACTION'	=> $this->helper->route('sheer_knowledgebase_library_search'),
			'CATS_DROPBOX'			=> $this->kb->make_category_dropbox(0, false, true, false, false),
			'CATS_BOX'				=> $this->kb->make_category_select(0, false, true, false, false),
			)
		);

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang['LIBRARY'],
			'U_VIEW_FORUM'	=> $this->helper->route('sheer_knowledgebase_index'),
			)
		);

		page_header($this->user->lang('LIBRARY'));
		$this->template->set_filenames(array(
			'body' => 'kb_index_body.html'));

		page_footer();
		return new Response($this->template->return_display('body'), 200);
	}
}
