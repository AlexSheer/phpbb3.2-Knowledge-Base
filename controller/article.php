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

class article
{
	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	protected $db;
	/** @var \phpbb\db\driver\driver_interface */

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

	/** @var string ARTICLES_TABLE */
	protected $articles_table;

	/** @var string KB_ATTACHMENTS_TABLE */
	protected $attachments_table;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\controller\helper $helper,
		$phpbb_root_path,
		$php_ext,
		\sheer\knowledgebase\inc\functions_kb $kb,
		$articles_table,
		$attachments_table
	)
	{
		$this->config			= $config;
		$this->request			= $request;
		$this->db				= $db;
		$this->auth				= $auth;
		$this->template			= $template;
		$this->user				= $user;
		$this->helper			= $helper;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->kb				= $kb;
		$this->articles_table	= $articles_table;
		$this->attachments_table= $attachments_table;
	}

	public function show()
	{
		if (!$this->auth->acl_get('u_kb_view') || !$this->auth->acl_get('a_manage_kb'))
		{
			trigger_error($this->user->lang['NOT_AUTHORISED']);
		}

		$art_id = $this->request->variable('k', 0);
		$mode = $this->request->variable('mode', '');
		$download_path = $this->config['upload_path'];

		if (empty($art_id))
		{
			trigger_error ($this->user->lang['NO_ID_SPECIFIED']);
		}

		$sql = 'SELECT a.*, u.user_colour, u.username
			FROM '. $this->articles_table . ' a, ' . USERS_TABLE . ' u
			WHERE a.article_id = ' . (int) $art_id . '
			AND u.user_id = a.author_id ';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($row))
		{
			trigger_error('ARTICLE_NO_EXISTS');
		}

		$kb_data = $this->kb->obtain_kb_config();
		$fid = $kb_data['forum_id'];

		$cat_id = $row['article_category_id'];

		if (!$row['approved'] && !($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_m_approve')))
		{
			redirect($this->helper->route('sheer_knowledgebase_category', array('id' => $cat_id)));
		}

		$catrow = $this->kb->get_cat_info($row['article_category_id']);
		if (empty($catrow))
		{
			trigger_error($this->user->lang['CAT_NO_EXISTS']);
		}
		$path = $catrow['category_name'];

		$this->template->assign_vars(array(
			'ARTICLE_CATEGORY'	=>  '<a href="' . $this->helper->route('sheer_knowledgebase_category', array('id' => $catrow['category_id'])) . '">' . $catrow['category_name'] . '</a>',
			'CATS_DROPBOX'		=> $this->kb->make_category_dropbox($cat_id, false, true, false, false),
			'CATS_BOX'			=> '<option value="0">' . $this->user->lang['CATEGORIES_LIST'] . '</option>' . $this->kb->make_category_select($cat_id, false, true, false, false) . '',
			'S_ACTION'			=> $this->helper->route('sheer_knowledgebase_category', array('id' => $cat_id)),
			)
		);

		$comment_topic_id = $row['topic_id'];

		// Get comments
		if ($comment_topic_id)
		{
			$count = -1;
			$sql = 'SELECT DISTINCT p.poster_id, p.post_time, p.post_subject, p.post_text, p.bbcode_uid, p.bbcode_bitfield, u.user_id, u.username
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE p.topic_id = ' . $comment_topic_id . '
				AND (p.poster_id = u.user_id)
				ORDER BY p.post_time ASC';
			$res = $this->db->sql_query($sql);
			while ($postrow = $this->db->sql_fetchrow($res))
			{
				$count++;
				if ($count > 0)
				{
					$this->template->assign_block_vars('postrow', array(
						'POSTER_NAME'	=> $postrow['username'],
						'POST_DATE'		=> $this->user->format_date ($postrow['post_time']),
						'POST_SUBJECT'	=> $postrow['post_subject'],
						'MESSAGE'		=> generate_text_for_display($postrow['post_text'], $postrow['bbcode_uid'], $postrow['bbcode_bitfield'], 3, true),
						)
					);
				}
			}
			$this->db->sql_freeresult($res);

			$temp_url = append_sid("{$this->phpbb_root_path}viewtopic." . $this->php_ext . "", 'f=' . $fid . '&amp;t=' . $row['topic_id']);
		}
		$views = $row['views'];
		$article = $row['article_id'];
		$text = generate_text_for_display($row['article_body'], $row['bbcode_uid'], false, 3, true);

		$sql = 'SELECT *
			FROM ' . $this->attachments_table . '
			WHERE article_id = ' .  (int) $art_id . '
			ORDER BY attach_id DESC';
		$result = $this->db->sql_query($sql);

		while ($attach_row = $this->db->sql_fetchrow($result))
		{
			$attachments[] = $attach_row;
		}
		$this->db->sql_freeresult($result);

		$update_count = array();

		// Parse attacments
		if (isset($attachments) && sizeof($attachments))
		{
			$this->kb->parse_att($text, $attachments);
		}

		$this->template->assign_vars(array(
			'ARTICLE_AUTHOR'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour']),
			'ARTICLE_DESCRIPTION' 	=> $row['article_description'],
			'ARTICLE_DATE'			=> $this->user->format_date($row['article_date']),
			'ART_VIEWS'				=> $row['views'],
			'ARTICLE_TITLE'			=> $row['article_title'],
			'ARTICLE_TEXT'			=> $text,
			'VIEWS'					=> $views,
			'U_EDIT_ART'			=> $this->helper->route('sheer_knowledgebase_posting', array('mode' => 'edit', 'id' => $cat_id, 'k' => $art_id)),
			'U_DELETE_ART'			=> $this->helper->route('sheer_knowledgebase_posting', array('mode' => 'delete', 'id' => $cat_id, 'k' => $art_id)),
			'U_APPROVE_ART'			=> $this->helper->route('sheer_knowledgebase_approve', array('id' => $art_id)),
			'U_PRINT'				=> $this->helper->route('sheer_knowledgebase_article', array('mode' => 'print', 'k' => $art_id)),
			'U_ARTICLE'				=> '[url=' . generate_board_url() . '/knowledgebase/article?k=' . $art_id . ']' . $row['article_title'] . '[/url]',
			'U_DIRECT_LINK'			=> generate_board_url() . '/knowledgebase/article?k=' . $art_id,
			'COMMENTS'				=> ($comment_topic_id) ? '' . $this->user->lang['COMMENTS'] . '' . $this->user->lang['COLON'] . ' ' . $count . '' : '',
			'U_COMMENTS'			=> ($comment_topic_id) ? $temp_url : '',
			'S_CAN_EDIT'			=> ($this->kb->acl_kb_get($cat_id, 'kb_m_edit')   || ($this->user->data['user_id'] == $row['author_id'] && $this->kb->acl_kb_get($cat_id, 'kb_u_edit')   || $this->auth->acl_get('a_manage_kb'))) ? true : false,
			'S_CAN_DELETE'			=> ($this->kb->acl_kb_get($cat_id, 'kb_m_delete') || ($this->user->data['user_id'] == $row['author_id'] && $this->kb->acl_kb_get($cat_id, 'kb_u_delete') || $this->auth->acl_get('a_manage_kb'))) ? true : false,
			'S_CAN_APPROOVE'		=> ($this->auth->acl_get('a_manage_kb') || $this->kb->acl_kb_get($cat_id, 'kb_m_approve')) ? true : false,
			'COUNT_COMMENTS'		=> ($comment_topic_id) ? '[' . $this->user->lang['LEAVE_COMMENTS'] . ']' : '',
			'U_FORUM'				=> generate_board_url() . '/',
			'S_APPROVED'			=> $row['approved'],
			'S_VIEWTOPIC'			=> true, // Need for Extension Highslide (bb3mobi/highslide)
			'S_KNOWLEDGEBASE'		=> true,
			)
		);

		if ($mode != 'print' && $row['approved'])
		{
		// Increase the number of views
			++$views;
			$sql = 'UPDATE ' . $this->articles_table . '
				SET views = ' . $views . '
				WHERE article_id = ' . (int) $article;
			$this->db->sql_query($sql);
		}

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

		$html_template = (($mode != 'print')) ? 'kb_article_body.html' : 'kb_article_body_print.html';

		page_header($this->user->lang('LIBRARY'));
		$this->template->set_filenames(array(
			'body' => $html_template));

		page_footer();
		return new Response($this->template->return_display('body'), 200);
	}
}
