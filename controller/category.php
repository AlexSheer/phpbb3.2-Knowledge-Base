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

class category
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
		$this->config			= $config;
		$this->request			= $request;
		$this->pagination		= $pagination;
		$this->db				= $db;
		$this->auth				= $auth;
		$this->template			= $template;
		$this->user				= $user;
		$this->helper			= $helper;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->kb				= $kb;
		$this->articles_table	= $articles_table;
		$this->categories_table	= $categories_table;
	}

	public function cat()
	{
		$cat_id = $this->request->variable('id', 0);
		$start = $this->request->variable('start', 0);

		if (!$cat_id)
		{
			redirect($this->helper->route('sheer_knowledgebase_index'));
		}
		$sql_where = ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_m_approve')) ? '' : 'AND a.approved = 1';

		$kb_config = $this->kb->obtain_kb_config();
		$per_page = $kb_config['articles_per_page'];

		$sql = 'SELECT category_id
			FROM '. $this->categories_table .'
			WHERE category_id = '.$cat_id.'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($row))
		{
			trigger_error ('CAT_NO_EXISTS');
		}

		$sql = 'SELECT COUNT(a.article_id) as article_count
			FROM ' . $this->articles_table . ' a
			WHERE a.article_category_id = ' . (int) $cat_id . '
			' . $sql_where . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$article_count = $row['article_count'];
		$this->db->sql_freeresult($result);

		$pagination_url = $this->helper->route('sheer_knowledgebase_category', array('id' => $cat_id));
		if ($article_count)
		{
			$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $article_count, $per_page, $start);
		}
		$current_page_number =  $this->pagination->get_on_page($per_page, $start);

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang['LIBRARY'],
			'U_VIEW_FORUM'	=> $this->helper->route('sheer_knowledgebase_index'),
			)
		);

		$parents_cats = array();
		foreach ($this->kb->get_category_branch($cat_id, 'parents') as $row)
		{
			$parents_cats[] = $row['category_id'];
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $row['category_name'],
				'U_VIEW_FORUM'	=> $this->helper->route('sheer_knowledgebase_category', array('id' => $row['category_id'])),
				)
			);
		}

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE parent_id = ' . $cat_id . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($cat_row = $this->db->sql_fetchrow($result))
		{
			$exclude_cats = array();
			foreach ($this->kb->get_category_branch($cat_row['category_id'], 'children') as $row)
			{
				$exclude_cats[] = $row['category_id'];
			}
			array_shift($exclude_cats);

			$this->template->assign_block_vars('cat_row', array(
				'CAT_ID'	=> $cat_row['category_id'],
				'CAT_NAME'	=> $cat_row['category_name'],
				'U_CAT'		=> $this->helper->route('sheer_knowledgebase_category', array('id' => $row['category_id'])),
				'ARTICLES'	=> $cat_row['number_articles'],
				'SUBCATS'	=> $this->kb->get_cat_list ($cat_row['parent_id'], $exclude_cats),
				)
			);
		}

		if (!isset($per_page))
		{
			$per_page = 10;
		}

		$sql = 'SELECT a.*, u.user_colour, username
			FROM ' . $this->articles_table . ' a, ' . USERS_TABLE . ' u
			WHERE a.article_category_id = ' . (int) $cat_id . '
			' . $sql_where . '
			AND u.user_id = a.author_id
			ORDER BY a.display_order ASC';

		$result = $this->db->sql_query_limit($sql, $per_page, $start);
		while($art_row = $this->db->sql_fetchrow($result))
		{
			$art_id		= $art_row['article_id'];
			$this->template->assign_block_vars('art_row', array(
				'ID'					=> $art_id,
				'ORDER_ID'				=> $art_row['display_order'],
				'U_ARTICLE'				=> $this->helper->route('sheer_knowledgebase_article', array('k' => $art_row['article_id'])),
				'ARTICLE_TITLE'			=> $art_row['article_title'],
				'ARTICLE_AUTHOR'		=> get_username_string('full', $art_row['author_id'], $art_row['username'], $art_row['user_colour']),//$author_kb_art,
				'ARTICLE_DESCRIPTION'	=> $art_row['article_description'],
				'ARTICLE_DATE'			=> $this->user->format_date($art_row['article_date']),
				'ART_VIEWS'				=> $art_row['views'],
				'U_DELETE'				=> $this->helper->route('sheer_knowledgebase_posting', array('id' => $cat_id, 'mode' => 'delete', 'k' => $art_id)),
				'U_EDIT_ART'			=> $this->helper->route('sheer_knowledgebase_posting', array('id' => $cat_id, 'mode' => 'edit', 'k' => $art_id)),
				'S_CAN_DELETE'			=> ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_m_delete') || ($this->kb->acl_kb_get($cat_id, 'kb_u_delete') && $this->user->data['user_id'] == $author_id)) ? true : false,
				'S_CAN_EDIT'			=> ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_m_edit')   || ($this->kb->acl_kb_get($cat_id, 'kb_u_edit')   && $this->user->data['user_id'] == $author_id)) ? true : false,
				'S_APPROVED'			=> ($art_row['approved']) ? true : false,
				)
			);
		}
		$this->db->sql_freeresult($result);

		if (empty($art_id))
		{
			$this->template->assign_block_vars('no_articles', array('COMMENT' => $this->user->lang['NO_ARTICLES']));
		}

		$move_allowed = ($this->kb->acl_kb_get($cat_id, 'kb_m_edit') || $this->auth->acl_get('a_manage_kb'));

		$this->template->assign_vars(array(
			'CATS_DROPBOX'			=> $this->kb->make_category_dropbox($cat_id, false, true, false, false),
			'CATS_BOX'				=> $this->kb->make_category_select($cat_id, false, true, false, false),
			'CATEGORY'				=> $row['category_name'],
			'CATEGORY_ID'			=> $row['category_id'],
			'TOTAL_ITEMS'			=> $this->user->lang('TOTAL_ITEMS', (int) $article_count),
			'PAGE_NUMBER'			=> $this->pagination->on_page($article_count, $per_page, $start),
			'U_ADD_ARTICLE'			=> $this->helper->route('sheer_knowledgebase_posting', array('id' => $cat_id)),
			'U_KB'					=> $this->helper->route('sheer_knowledgebase_index'),
			'U_KB_SEARCH'			=> $this->helper->route('sheer_knowledgebase_library_search'),
			'S_CAN_ADD'				=> ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_u_add')) ? true : false,
			'S_ACTION'				=> $this->helper->route('sheer_knowledgebase_category', array('id' => $cat_id)),
			'S_IS_SEARCH'			=> ($this->config['kb_search']) ? true : false,
			'S_KB_SEARCH_ACTION'	=> $this->helper->route('sheer_knowledgebase_library_search'),
			'S_KNOWLEDGEBASE'		=> true,
			'S_CAN_MOVE'			=> ($move_allowed) ? true : false,
			'CURRENT_PAGE_NUMBER'	=> $current_page_number,
			)
		);

		$this->kb->gen_kb_auth_level($cat_id);

		page_header(''. $this->user->lang('LIBRARY'). ' &raquo; ' . $this->user->lang('CATEGORY') . '');
		$this->template->set_filenames(array(
			'body' => 'kb_cat_body.html'));

		page_footer();
		return new Response($this->template->return_display('body'), 200);
	}
}
