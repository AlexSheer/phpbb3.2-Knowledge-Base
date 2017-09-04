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

use phpbb\config\config;

class admin_controller
{
	/** @var \phpbb\user $user User object */
	protected $user;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\pagination\pagination */
	protected $pagination;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	//** @var string \sheer\knowledgebase\inc\functions_kb */
	protected $kb;

	/** @var string KB_ATTACHMENTS_TABLE */
	protected $attachments_table;

	/** @var string ARTICLES_TABLE */
	protected $articles_table;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\user $user,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\template\template $template,
		\phpbb\request\request_interface $request,
		\phpbb\pagination $pagination,
		$phpbb_root_path,
		\sheer\knowledgebase\inc\functions_kb $kb,
		$attachments_table,
		$articles_table
	)
	{
		$this->user					= $user;
		$this->cache				= $cache;
		$this->config				= $config;
		$this->db					= $db;
		$this->template				= $template;
		$this->request				= $request;
		$this->pagination			= $pagination;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->kb					= $kb;
		$this->attachments_table	= $attachments_table;
		$this->articles_table		= $articles_table;

		$this->user->add_lang_ext('sheer/knowledgebase', 'info_acp_knowlegebase');
		$this->user->add_lang(array('acp/attachments'));
	}

	/**
	* @return null
	* @access public
	*/
	public function main()
	{
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = $this->request->variable('action', '');

		$form_key = 'acp_attach';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			trigger_error($this->user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($submit)
		{
			$delete_files = (isset($_POST['delete'])) ? array_keys($this->request->variable('delete', array('' => 0))) : array();
			if (sizeof($delete_files))
			{
				$sql = 'SELECT attach_id, physical_filename
					FROM ' . $this->attachments_table . '
					WHERE ' . $this->db->sql_in_set('attach_id', $delete_files) . '';
				$result = $this->db->sql_query($sql);
				while ($attach_row = $this->db->sql_fetchrow($result))
				{
					$attachments_list[] = $attach_row['physical_filename'];
					$attachments_ids[] = $attach_row['attach_id'];
				}

				foreach ($attachments_list as $key => $attachments)
				{
					@unlink($this->phpbb_root_path . 'ext/sheer/knowledgebase/files/' . $attachments);
					@unlink($this->phpbb_root_path . 'ext/sheer/knowledgebase/files/thumb_' . $attachments);
				}
				$sql = 'DELETE FROM  ' . $this->attachments_table . '
					WHERE ' . $this->db->sql_in_set('attach_id', $attachments_ids) . '';
				$this->db->sql_query($sql);
				meta_refresh(3, $this->u_action);
				trigger_error($this->user->lang['FILES_DELETED_SUCCESS'] . adm_back_link($this->u_action));
			}
			else
			{
				trigger_error($this->user->lang['NO_FILES_SELECTED'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		$start		= $this->request->variable('start', 0);

		// Sort keys
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		// Sorting
		$limit_days = array(0 => $this->user->lang['ALL_ENTRIES'], 1 => $this->user->lang['1_DAY'], 7 => $this->user->lang['7_DAYS'], 14 => $this->user->lang['2_WEEKS'], 30 => $this->user->lang['1_MONTH'], 90 => $this->user->lang['3_MONTHS'], 180 => $this->user->lang['6_MONTHS'], 365 => $this->user->lang['1_YEAR']);
		$sort_by_text = array('f' => $this->user->lang['FILENAME'], 't' => $this->user->lang['FILEDATE'], 's' => $this->user->lang['FILESIZE'], 'x' => $this->user->lang['EXTENSION'], 'u' => $this->user->lang['AUTHOR']);
		$sort_by_sql = array('f' => 'a.real_filename', 't' => 'a.filetime', 's' => 'a.filesize', 'x' => 'a.extension', 'u' => 'u.username');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		$min_filetime = ($sort_days) ? (time() - ($sort_days * 86400)) : '';
		$limit_filetime = ($min_filetime) ? " AND a.filetime >= $min_filetime " : '';
		$start = ($sort_days && isset($_POST['sort'])) ? 0 : $start;

		$attachments_per_page = (int) $this->config['topics_per_page'];

		$stats = $this->get_kb_attachment_stats($limit_filetime);
		$num_files = $stats['num_files'];
		$total_size = $stats['upload_dir_size'];

		// If the user is trying to reach the second half of the attachments list, fetch it starting from the end
		$store_reverse = false;
		$sql_limit = $attachments_per_page;

		if ($start > $num_files / 2)
		{
			$store_reverse = true;

			// Select the sort order. Add time sort anchor for non-time sorting cases
			$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') : '';
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC') . $sql_sort_anchor;
			$sql_limit = $this->pagination->reverse_limit($start, $sql_limit, $num_files);
			$sql_start = $this->pagination->reverse_start($start, $sql_limit, $num_files);
		}
		else
		{
			// Select the sort order. Add time sort anchor for non-time sorting cases
			$sql_sort_anchor = ($sort_key != 't') ? ', a.filetime ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') : '';
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC') . $sql_sort_anchor;
			$sql_start = $start;
		}

		$attachments_list = array();

		$sql = 'SELECT a.*, u.username, u.user_colour, t.article_title
			FROM ' . $this->attachments_table . ' a
			LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = a.poster_id)
			LEFT JOIN ' . $this->articles_table . " t ON (a.article_id = t.article_id)
			WHERE a.is_orphan = 0
				$limit_filetime
			ORDER BY $sql_sort_order";

		$result = $this->db->sql_query_limit($sql, $sql_limit, $sql_start);

		$i = ($store_reverse) ? $sql_limit - 1 : 0;

		// Store increment value in a variable to save some conditional calls
		$i_increment = ($store_reverse) ? -1 : 1;
		while ($attach_row = $this->db->sql_fetchrow($result))
		{
			$attachments_list[$i] = $attach_row;
			$i = $i + $i_increment;
		}
		$this->db->sql_freeresult($result);

		$base_url = $this->u_action . "&amp;$u_sort_param";

		for ($i = 0, $end = sizeof($attachments_list); $i < $end; ++$i)
		{
			$row = $attachments_list[$i];
			$img_src = ($this->kb->check_is_img($row['extension'])) ? '<span class="kb_preview"><img src="' . $this->phpbb_root_path . 'knowledgebase/kb_file?id=' . $row['attach_id'] . '"></span>' : '';
			$this->template->assign_block_vars('attachments', array(
				'REAL_FILENAME'		=> utf8_basename($row['real_filename']),
				'FILETIME'			=> $this->user->format_date($row['filetime']),
				'ATTACHMENT_POSTER'	=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
				'U_FILE'			=> append_sid("{$this->phpbb_root_path}knowledgebase/kb_file?id=" . $row['attach_id'] . ""),
				'ATTACH_ID'			=> $row['attach_id'],
				'S_IS_ORPHAN'		=> $row['is_orphan'],
				'IMG_SRC'			=> $img_src,
				'U_ARTICLE'			=> append_sid("{$this->phpbb_root_path}knowledgebase/article?k=" . $row['article_id'] . ""),
				'ARTICLE_TITLE'		=> $row['article_title'],
				'FILESIZE'			=> get_formatted_filesize($row['filesize']),
				)
			);
		}

		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_files, $attachments_per_page, $start);

		$this->template->assign_vars(array(
			'S_ATTACHMENTS'		=> true,
			'L_TITLE'			=> $this->user->lang['ATTACHMENTS'],
			'L_TITLE_EXPLAIN'	=> $this->user->lang['ATTACHMENTS_EXPLAIN'],
			'TOTAL_FILES'		=> $num_files,
			'TOTAL_SIZE'		=> get_formatted_filesize($total_size),
			'S_LIMIT_DAYS'		=> $s_limit_days,
			'S_SORT_KEY'		=> $s_sort_key,
			'S_SORT_DIR'		=> $s_sort_dir,
			'S_ACTION'			=> $this->u_action,
			)
		);
	}

	/**
	* @return null
	* @access public
	*/
	public function orphan()
	{
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = $this->request->variable('action', '');

		if ($submit)
		{
			$delete_files = (isset($_POST['delete'])) ? array_keys($this->request->variable('delete', array('' => 0))) : array();
			$add_files = (isset($_POST['add'])) ? array_keys($this->request->variable('add', array('' => 0))) : array();
			$post_ids = $this->request->variable('post_id', array('' => 0));

			if (sizeof($delete_files))
			{
				$sql = 'SELECT attach_id, physical_filename
					FROM ' . $this->attachments_table . '
					WHERE ' . $this->db->sql_in_set('attach_id', $delete_files) . '';
				$result = $this->db->sql_query($sql);
				while ($attach_row = $this->db->sql_fetchrow($result))
				{
					$attachments_list[] = $attach_row['physical_filename'];
					$attachments_ids[] = $attach_row['attach_id'];
					$delete_files[$row['attach_id']] = $row['real_filename'];
				}

				foreach ($attachments_list as $key => $attachments)
				{
					@unlink($this->phpbb_root_path . 'ext/sheer/knowledgebase/files/' . $attachments);
					@unlink($this->phpbb_root_path . 'ext/sheer/knowledgebase/files/thumb_' . $attachments);
				}
				$sql = 'DELETE FROM  ' . $this->attachments_table . '
					WHERE ' . $this->db->sql_in_set('attach_id', $attachments_ids) . '';
				$this->db->sql_query($sql);
				meta_refresh(3, $this->u_action);
				trigger_error($this->user->lang['FILES_DELETED_SUCCESS'] . adm_back_link($this->u_action));
			}

			$upload_list = array();
			foreach ($add_files as $attach_id)
			{
				if (!isset($delete_files[$attach_id]) && !empty($post_ids[$attach_id]))
				{
					$upload_list[$attach_id] = $post_ids[$attach_id];
				}
			}

			unset($add_files);

			if (sizeof($upload_list))
			{
				$this->template->assign_var('S_UPLOADING_FILES', true);
				$sql = 'SELECT article_id
					FROM ' . $this->articles_table . '
					WHERE ' . $this->db->sql_in_set('article_id', $upload_list);
				$result = $this->db->sql_query($sql);

				$post_info = array();
				while ($row = $this->db->sql_fetchrow($result))
				{
					$post_info[$row['article_id']] = $row;
				}
				$this->db->sql_freeresult($result);

				// Select those attachments we want to change...
				$sql = 'SELECT *
					FROM ' . $this->attachments_table . '
					WHERE ' . $this->db->sql_in_set('attach_id', array_keys($upload_list)) . '
						AND is_orphan = 1';
				$result = $this->db->sql_query($sql);

				$files_added = $space_taken = 0;
				while ($row = $this->db->sql_fetchrow($result))
				{
					$post_row = $post_info[$upload_list[$row['attach_id']]];
					$mess = ($post_row['article_id']) ? sprintf($this->user->lang['POST_ROW_ARTICLE_INFO'], $post_row['article_id']) : '';
					$this->template->assign_block_vars('upload', array(
						'FILE_INFO'		=> sprintf($this->user->lang['UPLOADING_FILE_TO_ARTICLE'], $row['real_filename']) . $mess,
						'S_DENIED'		=> (!$post_row['article_id']) ? true : false,
						'DENIED'		=> $this->user->lang['UPLOAD_DENIED_ARTICLE'],
						)
					);

					if (!$post_row['article_id'])
					{
						continue;
					}

					// Adjust attachment entry
					$sql_ary = array(
						'is_orphan'		=> 0,
						'article_id'	=> $post_row['article_id'],
					);

					$sql = 'UPDATE ' . $this->attachments_table . '
						SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE attach_id = ' . $row['attach_id'];
					$this->db->sql_query($sql);
				}

				$this->db->sql_freeresult($result);
			}
		}
		// Just get the files with is_orphan set and older than 3 hours
		$sql = 'SELECT attach_id, real_filename, physical_filename, filesize, filetime, extension
			FROM ' . $this->attachments_table . '
			WHERE is_orphan = 1
				AND filetime < ' . (time() - 3*60*60) . '
			ORDER BY filetime DESC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$img_src = ($this->kb->check_is_img($row['extension'])) ? '<span class="kb_preview"><img src="' . $this->phpbb_root_path . 'knowledgebase/kb_file?id=' . $row['attach_id'] . '"></span>' : '';

			$this->template->assign_block_vars('orphan', array(
				'FILESIZE'			=> get_formatted_filesize($row['filesize']),
				'FILETIME'			=> $this->user->format_date($row['filetime']),
				'REAL_FILENAME'		=> utf8_basename($row['real_filename']),
				'PHYSICAL_FILENAME'	=> utf8_basename($row['physical_filename']),
				'ATTACH_ID'			=> $row['attach_id'],
				'IMG_SRC'			=> $img_src,
				'POST_IDS'			=> (!empty($post_ids[$row['attach_id']])) ? $post_ids[$row['attach_id']] : '',
				'U_FILE'			=> append_sid("{$this->phpbb_root_path}knowledgebase/kb_file?id=$row[attach_id]"),
				)
			);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'S_ORPHAN'			=> true,
			'L_TITLE'			=> $this->user->lang['ACP_ORPHAN_ATTACHMENTS'],
			'L_TITLE_EXPLAIN'	=> $this->user->lang['ORPHAN_EXPLAIN'],
			'S_ACTION'			=> $this->u_action,
			)
		);
	}

	/**
	* @return null
	* @access public
	*/
	public function extra_files()
	{
		$submit = $this->request->variable('submit', false);

		$batch_size = 500;
		$list = '';
		$files = $bd_files = $delete_list = $unsuccess = array();
		ignore_user_abort(true);
		set_time_limit(0);
		$dir = $this->phpbb_root_path . 'ext/sheer/knowledgebase/files';
		$files_list = $this->cache->get('_kb_prune_attachments'); // Try get data from cache

		if ($submit)
		{
			if(!$files_list)
			{
				$files = $this->scan($dir, $files);
				$sql = 'SELECT attach_id, physical_filename
					FROM ' . $this->attachments_table;
				$result = $this->db->sql_query($sql);
				while($data = $this->db->sql_fetchrow($result))
				{
					$bd_files[] = $dir . '/' . $data['physical_filename'];
					$bd_files[] = $dir . '/' . 'thumb_' . $data['physical_filename'];
				}
				$this->db->sql_freeresult($result);
				$files = array_diff($files, $bd_files);
				array_unique($files);
				array_map('trim', $files);
				sort($files);
				$this->cache->put('_kb_prune_attachments', $files);
			}
			else
			{
				$files = $files_list;
			}

			$count = 0;
			foreach ($files as $del_file)
			{
				if (file_exists($del_file) && !is_dir($del_file))
				{
					if (@unlink($del_file))
					{
						$delete_list[] = $del_file;
					}
					else
					{
						$unsuccess[] = $del_file;
					}

					$files = array_diff($files, array($del_file));

					sort($files);
					$count++;
				}
				if($count > ($batch_size - 1))
				{
					$this->cache->destroy('_kb_prune_attachments');
					$this->cache->put('_kb_prune_attachments', $files);
					break;
				}
			}

			if(sizeof($delete_list))
			{
				$list .= implode('<br />', $delete_list);
				$exit = false;
			}
			else
			{
				$list = (sizeof($unsuccess)) ? '' : $this->user->lang['PRUNE_ATTACHMENTS_FINISHED'];
				$exit = true;
			}

			if(sizeof($unsuccess))
			{
				$list .= '' . $this->user->lang['PRUNE_ATTACHMENTS_FAIL'] . '<br />' . implode('<br />', $unsuccess) . '';
			}

			if($exit)
			{
				$this->cache->destroy('_kb_prune_attachments');
				if ((sizeof($unsuccess)))
				{
					trigger_error('' . $list . '', E_USER_WARNING);
				}
				else
				{
					trigger_error('' . $list . '');
				}
			}

			else
			{
				meta_refresh(3, $this->u_action . '&amp;submit=1');
				trigger_error('' . $this->user->lang['PRUNE_ATTACHMENTS_PROGRESS'] . '<br />' . $list . '');
			}
		}

 		$this->template->assign_vars(array(
 			'S_PRUNE_ATTACHMENTS'	=> true,
			'L_TITLE'				=> $this->user->lang['ACP_LIBRARY_ATTACHMENTS_EXTRA_FILES'],
			'L_TITLE_EXPLAIN'		=> $this->user->lang['PRUNE_ATTACHMENTS_EXPLAIN'],
			'S_ACTION'				=> $this->u_action,
			)
		);
	}

	/**
	* @return null
	* @access public
	*/
	public function scan($path,&$res)
	{
		$mass = scandir($path);
		for($i = 0; $i <= count($mass) - 1; $i++)
		{
			if($mass[$i] != '..' && $mass[$i] != '.' && $mass[$i] != 'index.htm' && $mass[$i] != '.htaccess' && $mass[$i] != 'plupload')
			{
				array_push($res, '' . $path . '/' . $mass[$i] . '');
			}
			if(!strstr($mass[$i], '.'))
			{
				if(is_dir($path . '/' . $mass[$i]))
				{
					$this->scan($path . '/' . $mass[$i], $res);
				}
			}
		}
		return $res;
	}

	/**
	* @return null
	* @access public
	*/
	public function lost_files()
	{
		$submit = $this->request->variable('submit', false);
		$step	= $this->request->variable('step', 0);
		$batch_size = 500;
		$dir = $this->phpbb_root_path . 'ext/sheer/knowledgebase/files';
		$begin	= $batch_size * $step;

		if ($submit)
		{
			// Get the batch
			$sql = 'SELECT attach_id, physical_filename
				FROM ' . $this->attachments_table;
			$result	= $this->db->sql_query_limit($sql, $batch_size, $begin);
			$batch	= $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			if (empty($batch))
			{
				// Nothing to do
				trigger_error('RESYNC_ATTACHMENTS_FINISHED');
			}

			$delete_ids = array();

			foreach ($batch as $row)
			{
				// Does the file still exists?
				$path = $dir . "/{$row['physical_filename']}";

				if (file_exists($path))
				{
					// Yes, next please!
					continue;
				}

				$delete_ids[] = $row['attach_id'];
			}

			// Run all the queries
			if (!empty($delete_ids))
			{
				$this->db->sql_query('DELETE FROM ' . $this->attachments_table . ' WHERE ' . $this->db->sql_in_set('attach_id', $delete_ids));
			}

			// Next step
			meta_refresh(3, $this->u_action . '&amp;step=' . ++$step . '&amp;submit=1');
			trigger_error($this->user->lang['RESYNC_ATTACHMENTS_PROGRESS']);
		}

 		$this->template->assign_vars(array(
 			'S_PRUNE_ATTACHMENTS'	=> true,
			'L_TITLE'				=> $this->user->lang['ACP_LIBRARY_ATTACHMENTS_LOST_FILES'],
			'L_TITLE_EXPLAIN'		=> $this->user->lang['ACP_LIBRARY_ATTACHMENTS_LOST_FILES_EXPLAIN'],
			'S_ACTION'				=> $this->u_action,
			)
		);
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	* Get attachment file count and size of upload directory
	*
	* @param $limit string	Additional limit for WHERE clause to filter stats by.
	* @return array Returns array with stats: num_files and upload_dir_size
	*/
	public function get_kb_attachment_stats($limit = '')
	{
		$sql = 'SELECT COUNT(a.attach_id) AS num_files, SUM(a.filesize) AS upload_dir_size
			FROM ' . $this->attachments_table . " a
			WHERE a.is_orphan = 0
				$limit";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return array(
			'num_files'			=> (int) $row['num_files'],
			'upload_dir_size'	=> (float) $row['upload_dir_size'],
		);
	}
}
