<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\inc;

//use Symfony\Component\DependencyInjection\ContainerInterface;

class functions_kb
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	//** @var string php_ext */
	protected $php_ext;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user $user User object */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var log_interface */
	protected $log;

	/**
	* Constructor
	*
	* @access public
	*/

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\cache\service $cache,
		\phpbb\user $user,
		\phpbb\template\template $template,
		\phpbb\auth\auth $auth,
		\phpbb\log\log_interface $log,
		$phpbb_root_path,
		$php_ext,
		$config_table,
		$articles_table,
		$categories_table,
		$options_table,
		$kb_groups_table,
		$kb_users_table,
		$kb_logs_table,
		$attachments_table
	)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->phpbb_cache		= $cache;
		$this->user				= $user;
		$this->template			= $template;
		$this->auth				= $auth;
		$this->phpbb_log		= $log;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->config_table		= $config_table;
		$this->articles_table	= $articles_table;
		$this->categories_table	= $categories_table;
		$this->options_table	= $options_table;
		$this->kb_groups_table	= $kb_groups_table;
		$this->kb_users_table	= $kb_users_table;
		$this->kb_logs_table	= $kb_logs_table;
		$this->attachments_table= $attachments_table;

		$this->phpbb_log->set_log_table($this->kb_logs_table);
	}

	public function get_category_branch($category_id, $type = 'all', $order = 'descending', $include_category = true)
	{
		switch ($type)
		{
			case 'parents':
				$condition = 'f1.left_id BETWEEN f2.left_id AND f2.right_id';
			break;

			case 'children':
				$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id';
			break;

			default:
				$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id OR f1.left_id BETWEEN f2.left_id AND f2.right_id';
			break;
		}

		$rows = array();

		$sql = 'SELECT f2.*
			FROM ' . $this->categories_table . ' f1
			LEFT JOIN ' . $this->categories_table . " f2 ON ($condition)
			WHERE f1.category_id = $category_id
			ORDER BY f2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$include_category && $row['category_id'] == $category_id)
			{
				continue;
			}
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}

	public function get_cat_list ($select_id = false, $ignore_id = false)
	{
		$right = $articles = 0;
		$padding_store = array('0' => '');
		$padding = $cat_list = '';

		$sql = 'SELECT category_id, category_name, parent_id, left_id, right_id, number_articles
			FROM ' . $this->categories_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql, 600);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];

			if ((is_array($ignore_id) && in_array($row['category_id'], $ignore_id)) || $row['category_id'] == $ignore_id)
			{
				$sql_where = ($this->auth->acl_get('a_manage_kb') || $this->acl_kb_get($row['category_id'], 'kb_m_approve')) ? '' : 'AND approved = 1';
				$sql = 'SELECT COUNT(article_id) AS articles
					FROM ' . $this->articles_table . '
					WHERE article_category_id = ' . (int) $row['category_id'] . '
						' . $sql_where;
				$res = $this->db->sql_query($sql);
				$art_row = $this->db->sql_fetchrow($res);
				$this->db->sql_freeresult($res);
				$cat_list .= ''. $padding .'<a href="' . append_sid("{$this->phpbb_root_path}knowledgebase/category?id=$row[category_id]") . '"/>' . $row['category_name'] . '</a> (' . $this->user->lang['ARTICLES'] . ': ' . $art_row['articles'] . ')<br />';
			}
		}
		$this->db->sql_freeresult($result);
		unset($padding_store);
		return $cat_list;
	}

	public function make_category_dropbox($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
	{
		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql, 600);

		$right = 0;
		$padding_store = array('0' => '');
		$cats_list = $padding = '';

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];
			$selected = false;
			$open = $close = '';
			if($row['category_id'] == $select_id)
			{
				$open = '<b>';
				$close = '</b>';
			}
			$cats_list .= '<li>' . $padding . '<a href="'. append_sid("{$this->phpbb_root_path}knowledgebase/category", 'id=' . $row['category_id'] .' ') .'">' . $open . ''. $row['category_name'] . '' . $close . '</a></li>';
		}
		$this->db->sql_freeresult($result);
		unset($padding_store);
		return $cats_list;
	}

	public function make_category_select($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
	{
		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql, 600);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$cats_list = ($return_array) ? array() : '';

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];
			$disabled = false;

			if ((is_array($ignore_id) && in_array($row['category_id'], $ignore_id)) || $row['category_id'] == $ignore_id)
			{
				$disabled = true;
			}

			if ($return_array)
			{
				// Include some more information...
				$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? true : false) : (($row['category_id'] == $select_id) ? true : false);
				$cats_list[$row['category_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? ' selected="selected"' : '') : (($row['category_id'] == $select_id) ? ' selected="selected"' : '');
				$cats_list .= '<option value="' . $row['category_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['category_name'] . '</option>';
			}
		}
		$this->db->sql_freeresult($result);
		unset($padding_store);
		return $cats_list;
	}

	public function obtain_kb_config()
	{
		if (($kb_config = $this->phpbb_cache->get('_kb_config')) === false)
		{
			$sql = 'SELECT *
				FROM ' . $this->config_table . '';
			$result = $this->db->sql_query($sql);
			while($row = $this->db->sql_fetchrow($result))
			{
				if ($row['config_name'] == 'extensions')
				{
					$extensions = unserialize($row['config_value']);

					foreach ($extensions as $group => $extension)
					{
						foreach ($extension as $ext)
						{
							$enabled_extensions[] = $ext;
						};
					}
					$kb_config[$row['config_name']] = $enabled_extensions;

				}
				else
				{
					$kb_config[$row['config_name']] = $row['config_value'];
				}
			}
			$this->db->sql_freeresult($result);
			$this->phpbb_cache->put('_kb_config', $kb_config);
		}
		return($kb_config);
	}

	public function get_cat_info($category_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->categories_table . "
			WHERE category_id = $category_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}

		return $row;
	}

	public function kb_delete_article($id, $article_title)
	{
		include_once($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);

		$info = $this->get_kb_article_info($id);
		$category_id = $info['article_category_id'];

		$cat_info = $this->get_cat_info($category_id);
		$articles_count = $cat_info['number_articles'];

		$sql = 'UPDATE ' . $this->articles_table . ' SET display_order = display_order - 1
			WHERE article_category_id = ' . $category_id . '
			AND display_order > ' . $info['display_order'];
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM '. $this->articles_table .'
			WHERE article_id = ' . (int) $id;
		$this->db->sql_query($sql);

		$sql = 'SELECT attach_id, physical_filename, thumbnail
			FROM ' . $this->attachments_table . '
			WHERE article_id = ' . $id;
		$result = $this->db->sql_query($sql);
		while($row = $this->db->sql_fetchrow($result))
		{
			$attachment_data[] = $row;
			$ids[] = $row['attach_id'];
		}
		$this->db->sql_freeresult($result);
		if (sizeof($ids))
		{
			$upload_dir = 'ext/sheer/knowledgebase/files/';
			foreach ($attachment_data as $attachment)
			{
				@unlink($upload_dir . $attachment['physical_filename']);
				if ($attachment['thumbnail'])
				{
					@unlink($upload_dir . 'thumb_'. $attachment['physical_filename']);
				}
			}
			$sql = 'DELETE FROM ' . $this->attachments_table . '
				WHERE ' . $this->db->sql_in_set('attach_id', $ids) . '';
			$this->db->sql_query($sql);
		}
		$articles_count--;
		if ($articles_count > 0)
		{
			$sql = 'UPDATE ' . $this->categories_table .'
				SET number_articles = ' . $articles_count . '
				WHERE category_id = ' . (int) $category_id;
			$this->db->sql_query($sql);
		}
		$this->phpbb_cache->destroy('sql', $this->categories_table);

		delete_topics('topic_id', array($info['topic_id']), true, true, true);
		$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_LIBRARY_DEL_ARTICLE', time(), array($article_title, $cat_info['category_name']));

		return;
	}

	public function get_kb_article_info($art_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->articles_table . '
			WHERE article_id = ' . (int) $art_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$row)
		{
			trigger_error($this->user->lang['ARTICLE_NO_EXISTS']);
		}
		return $row;
	}

	public function kb_move_article($k, $article_title, $cat_id, $id, $order)
	{
		// Change category id in the table of articles
		$sql = 'UPDATE ' . $this->articles_table . '
			SET article_category_id = ' . (int) $id . ', display_order = 0
			WHERE article_id = ' . (int) $k;
		$this->db->sql_query($sql);

		// Change display order in categories
		$sql = 'UPDATE ' . $this->articles_table . ' SET display_order = display_order + 1
			WHERE article_category_id = ' . $id;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->articles_table . ' SET display_order = display_order - 1
			WHERE article_category_id = ' . $cat_id . '
			AND display_order > ' . $order;
		$this->db->sql_query($sql);

		// recalculate the number of articles in source category
		$cat_info = $this->get_cat_info($cat_id);
		$articles_count = $cat_info['number_articles'];
		$articles_count--;
		// ... and a new category
		$to_cat_info = $this->get_cat_info($id);
		$to_articles_count = $to_cat_info['number_articles'];
		$to_articles_count++;
		// change in DB
		$sql = 'UPDATE ' . $this->categories_table . '
			SET number_articles = ' . $articles_count . '
			WHERE category_id = ' . (int) $cat_id;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->categories_table . '
			SET number_articles = '. $to_articles_count . '
			WHERE category_id = ' . (int) $id;
		$this->db->sql_query($sql);
		$this->phpbb_cache->destroy('sql', $this->categories_table);
		$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->data['user_ip'], 'LOG_LIBRARY_MOVED_ARTICLE', time(), array($article_title, $cat_info['category_name'], $to_cat_info['category_name']));
		return;
	}

	public function acl_kb_get($category_id, $auth)
	{
		$sql = 'SELECT auth_option_id
			FROM ' . $this->options_table . '
			WHERE auth_option LIKE \'' . $auth . '\'
			AND is_local = 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$auth_option_id = $row['auth_option_id'];
		$this->db->sql_freeresult($result);

		$sql = 'SELECT auth_setting FROM ' . $this->kb_users_table . '
			WHERE category_id = ' . $category_id . '
			AND auth_option_id = ' . $auth_option_id . '
			AND user_id = ' . (int) $this->user->data['user_id'].'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if(!$row)
		{
			$sql = 'SELECT group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_groups[] = $row['group_id'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'SELECT auth_setting
				FROM ' . $this->kb_groups_table . '
				WHERE category_id = ' . (int) $category_id . '
				AND auth_option_id = ' . (int) $auth_option_id . '
				AND group_id IN(' . implode(',', $user_groups) . ')';
			$result = $this->db->sql_query($sql);
			while($row = $this->db->sql_fetchrow($result))
			{
				$auth_setting[] = $row['auth_setting'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'SELECT auth_setting
				FROM ' . $this->kb_groups_table . '
				WHERE category_id = ' . (int) $category_id . '
				AND auth_option_id = ' . (int) $auth_option_id . '
				AND group_id = ' . (int) $this->user->data['group_id'] . '';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$default_auth = $row['auth_setting'];
			$this->db->sql_freeresult($result);
		}
		else
		{
			$default_auth = true;
		}

		(!isset($row['auth_setting'])) ? $auth_setting[] = -1 : $auth_setting[] = $row['auth_setting'];
		return (in_array (1, $auth_setting) && $default_auth) ? true : false;
	}

	public function gen_kb_auth_level($category_id)
	{
		$rules = array(
			($this->acl_kb_get($category_id, 'kb_u_add') || $this->auth->acl_get('a_manage_kb')) ? $this->user->lang['RULES_KB_ADD_CAN'] : $this->user->lang['RULES_KB_ADD_CANNOT'],
		);

		if ($this->auth->acl_get('a_manage_kb') || $this->acl_kb_get($category_id, 'kb_m_delete'))
		{
			$rules = array_merge($rules, array(
				$this->user->lang['RULES_KB_DELETE_MOD_CAN'],
			));
		}
		else
		{
			$rules = array_merge($rules, array(
				($this->acl_kb_get($category_id, 'kb_u_delete')) ? $this->user->lang['RULES_KB_DELETE_CAN'] : $this->user->lang['RULES_KB_DELETE_CANNOT'],
			));
		}

		if ($this->auth->acl_get('a_manage_kb') || $this->acl_kb_get($category_id, 'kb_m_edit'))
		{
			$rules = array_merge($rules, array(
				$this->user->lang['RULES_KB_EDIT_MOD_CAN'],
			));
		}
		else
		{
			$rules = array_merge($rules, array(
				($this->acl_kb_get($category_id, 'kb_u_edit')) ? $this->user->lang['RULES_KB_EDIT_CAN'] : $this->user->lang['RULES_KB_EDIT_CANNOT'],
			));
		}

		if ($this->auth->acl_get('a_manage_kb') || $this->acl_kb_get($category_id, 'kb_m_approve'))
		{
			$rules = array_merge($rules, array(
				$this->user->lang['RULES_KB_APPROVE_MOD_CAN'],
			));
		}

		foreach ($rules as $rule)
		{
			$this->template->assign_block_vars('rules', array('RULE' => $rule));
		}

		return;
	}

	public function setup_kb_search()
	{
		if (!$this->config['kb_search_type'])
		{
			$this->config['kb_search_type'] = 'kb_fulltext_native';
		}
		$kb_search = false;
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
		if ($kb_search == false)
		{
			trigger_error('SEARCH_DISABLED');
		}
		return $kb_search;
	}

	public function get_id_by_username($username)
	{
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE username_clean = \'' . $this->db->sql_escape(utf8_clean_string($username)) . '\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $row['user_id'];
	}

	public function submit_article($cat_id, $fid, $article_title, $article_description, $article_author, $category_name, $new)
	{
		$options = '';

		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $fid;
		$result = $this->db->sql_query($sql);
		if ($row = $this->db->sql_fetchrow($result))
		{
			$topic_title = '[ ' . $this->user->lang['LIBRARY'] . ' ]';
			$topic_title .= $article_title;
		}
		else
		{
			trigger_error($this->user->lang['NO_FORUM']);
		}
		$this->db->sql_freeresult($result);

		$topic_text = '[b]' . $this->user->lang['ARTICLE_TITLE'] . ':[/b] ' . $article_title . '';
		$topic_text .= "\n";
		$topic_text .= '[b]' . $this->user->lang['ARTICLE_AUTHOR'] . ':[/b] ' . $article_author . ' ';
		$topic_text .= "\n";
		$topic_text .= '[b]' . $this->user->lang['ARTICLE_DESCRIPTION'] . ':[/b] ' . $article_description . ' ';
		$topic_text .= "\n";
		$topic_text .= '[b]' . $this->user->lang['CATEGORY'] . ':[/b] ' . $category_name . ' ';
		$topic_text .= "\n\n";
		$topic_text .= '[b][url=' . generate_board_url() . '/' . append_sid("knowledgebase/article",'k=' . $new . ' ').']&raquo;' . $this->user->lang['READ_FULL'] . '[/url][/b]';

		generate_text_for_storage($topic_text, $uid, $bitfield, $options, true, true, true);

		$data = array(
			'topic_title'			=> $topic_title,
			'forum_id'				=> $fid,
			'forum_name'			=> '',
			'icon_id'				=> 0,
			'poster_id'				=> (int) $this->user->data['user_id'],
			'enable_bbcode'			=> (bool) true,
			'enable_smilies'		=> (bool) true,
			'enable_urls'			=> (bool) true,
			'enable_sig' 			=> (bool) true,
			'notify'				=> 0,
			'notify_set'			=> '',
			'enable_indexing'		=> (bool) false,
			'message'				=> htmlspecialchars_decode($topic_text),
			'message_md5'			=> (string) '',
			'bbcode_bitfield'		=> $bitfield,
			'bbcode_uid'			=> $uid,
			'post_edit_locked'		=> 0,
		);

		submit_post('post', $topic_title, $this->user->data['username'], 0, $poll, $data, false);

		$sql = 'SELECT MAX(topic_id) AS last_topic
			FROM ' . TOPICS_TABLE . '
			WHERE forum_id = ' . (int) $fid;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$last_topic = $row['last_topic'];
		$sql = 'UPDATE ' . $this->articles_table . '
			SET topic_id = ' . (int) $last_topic . '
			WHERE article_id = ' . (int) $new;
		$this->db->sql_query($sql);

		return;
	}

	public function parse_att(&$text, $attachments)
	{
		krsort($attachments);
		preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $text, $matches, PREG_PATTERN_ORDER);
		$replace = array();
		foreach ($matches[0] as $num => $capture)
		{
			$index = $matches[1][$num];
			$comment = ($attachments[$index]['attach_comment']) ?  '<dd>' . $attachments[$index]['attach_comment'] . '</dd>' : '';
			if ($attachments[$index]['thumbnail'] == 1)
			{
				$replacement = '<dl class="thumbnail"><dt><a href="kb_file?id=' . $attachments[$index]['attach_id'] . '"><img src="kb_file?id=' . $attachments[$index]['attach_id'] . '&amp;t=1" class="postimage" alt="' . $attachments[$index]['real_filename'] . '" title="' . $attachments[$index]['real_filename'] . '"></a></dt>' . $comment . '</dl>';
			}
			else
			{
				if ($this->check_is_img($attachments[$index]['extension'], $extensions))
				{
					$replacement = '<dl class="file"><dt class="attach-image"><img src="kb_file?id=' . $attachments[$index]['attach_id'] . '" class="postimage" alt="' . $attachments[$index]['real_filename'] . '" onclick="viewableArea(this);"></dt><dd>' . $comment . '</dd></dl>';
				}
				else
				{
					$icon = ($extensions[$attachments[$index]['extension']]['upload_icon']) ? '<img src="./../images/upload_icons/' . $extensions[$attachments[$index]['extension']]['upload_icon'] . '" alt="">' : '';
					$replacement = '<dl class="file"><dt>' . $icon . ' <a class="postlink" href="kb_file?id=' . $attachments[$index]['attach_id'] . '">' . $attachments[$index]['real_filename'] . '</a></dt></dl>';
				}
			}
			$replace['from'][] = $matches[0][$num];
			$replace['to'][] = (isset($attachments[$index])) ? $replacement : sprintf($this->user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);
			$unset_tpl[] = $index;
		}

		if (isset($replace['from']))
		{
			$text = str_replace($replace['from'], $replace['to'], $text);
		}
		foreach ($attachments as $num => $attach)
		{
			unset($attachments[$unset_tpl[$num]]);
		}

		if(sizeof($attachments))
		{
			$text .= '</div><dl class="attachbox"><dt>' . $this->user->lang['ATTACHMENTS'] . '</dt>';
			foreach ($attachments as $key => $value)
			{
				$comment = ($value['attach_comment']) ?  '<dd>' . $value['attach_comment'] . '</dd>' : '';
				if ($value['thumbnail'])
				{
					$text .= '<dd><dl class="thumbnail"><dt><a href="kb_file?id=' . $value['attach_id'] . '"><img src="kb_file?id=' . $value['attach_id'] . '&amp;t=1"></a></dt>' . $comment . '</dl></dd>';
				}
				else
				{
					$class = ' class="attach-image"';
					if ($this->check_is_img($value['extension'], $extensions))
					{
						$text .= '<dd><dl class="file">';
						$text .= '<dt class="attach-image"><img src="kb_file?id=' . $value['attach_id'] . '" class="postimage" alt="' . $value['real_filename'] . '" onclick="viewableArea(this);"></dt>' . $comment . '';
					}
					else
					{
						$icon = ($extensions[$value['extension']]['upload_icon']) ? '<img src="./../images/upload_icons/' . $extensions[$value['extension']]['upload_icon'] . '" alt="">' : '';
						$text .= '<dd><dl class="file"><dt>' . $icon . ' <a class="postlink" href="kb_file?id=' . $value['attach_id'] . '">' . $value['real_filename'] . '</a></dt>' . $comment . '';
					}
					$text .= '</dl></dd>';
				}
			}
			$text .= '</dl><div>';
		}
	}

	public function check_is_img($ext, &$extensions = array())
	{
		if (($extensions = $this->phpbb_cache->get('_kb_extension')) === false)
		{
			$sql = 'SELECT e.extension, g.*
				FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
				WHERE e.group_id = g.group_id';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$extension = strtolower(trim($row['extension']));

				$extensions[$extension] = array(
					'display_cat'	=> (int) $row['cat_id'],
					'download_mode'	=> (int) $row['download_mode'],
					'upload_icon'	=> trim($row['upload_icon']),
					'max_filesize'	=> (int) $row['max_filesize'],
					'allow_group'	=> $row['allow_group'],
					'allow_in_pm'	=> $row['allow_in_pm'],
					'group_name'	=> $row['group_name'],
				);
			}
			$this->db->sql_freeresult($result);
			$this->phpbb_cache->put('_kb_extension', $extensions);
		}

		$is_image = (isset($extensions[$ext]['display_cat'])) ? $extensions[$ext]['display_cat'] == ATTACHMENT_CATEGORY_IMAGE : false;
		return $is_image;
	}
}
