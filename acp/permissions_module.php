<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\acp;

class permissions_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $template, $request, $cache, $phpbb_root_path, $phpEx, $auth, $user, $phpbb_ext_kb, $phpbb_log, $phpbb_container;
		$user->add_lang('acp/permissions');

		$this->config_table			= $phpbb_container->getParameter('tables.kb_config_table');
		$this->articles_table		= $phpbb_container->getParameter('tables.articles_table');
		$this->categories_table		= $phpbb_container->getParameter('tables.categories_table');
		$this->options_table		= $phpbb_container->getParameter('tables.kb_options_table');
		$this->kb_groups_table		= $phpbb_container->getParameter('tables.kb_groups_table');
		$this->kb_users_table		= $phpbb_container->getParameter('tables.kb_users_table');
		$this->kb_logs_table		= $phpbb_container->getParameter('tables.logs_table');
		$this->attachments_table	= $phpbb_container->getParameter('tables.kb_attachments_table');
		$controller_helper			= $phpbb_container->get('controller.helper');

		include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$phpbb_ext_kb = new \sheer\knowledgebase\inc\functions_kb(
			$config,
			$db,
			$cache,
			$user,
			$controller_helper,
			$template,
			$auth,
			$phpbb_log,
			$phpbb_root_path,
			$phpEx,
			$this->config_table,
			$this->articles_table,
			$this->categories_table,
			$this->options_table,
			$this->kb_groups_table,
			$this->kb_users_table,
			$this->kb_logs_table,
			$this->attachments_table
		);

		$this->tpl_name = 'acp_permissions_body';
		$this->mode = $mode;

		if ($this->mode == 'mask')
		{
			$this->page_title = $user->lang('ACP_LIBRARY_PERMISSIONS_MASK');
			$this->title = $user->lang['ACL_VIEW'];
			$this->title_explain = $user->lang['ACL_VIEW_EXPLAIN'];
			$this->title_edit_permissions = $this->title_add_permissions = $user->lang['VIEW_PERMISSIONS'];
		}
		else
		{
			$this->page_title = $user->lang('ACP_LIBRARY_PERMISSIONS');
			$this->title = $user->lang['ACP_LIBRARY_PERMISSIONS'];
			$this->title_explain = $user->lang['ACP_LIBRARY_PERMISSIONS_EXPLAIN'];
			$this->title_edit_permissions = $user->lang['EDIT_PERMISSIONS'];
			$this->title_add_permissions = $user->lang['ADD_PERMISSIONS'];
		}

		$category_id 	= (isset($category_id)) ? $request->variable('category_id', $category_id) : $request->variable('category_id', array(0));
		$user_id 		= $request->variable('user_id', array(0));
		$group_id 		= $request->variable('group_id', array(0));
		$username 		= $request->variable('username', array(''), true);
		$usernames 		= $request->variable('usernames', '', true);
		$all_cats 		= $request->variable('all_cats', 0);
		$mode 			= $request->variable('p_mode', '');
		$submit			= $request->variable('submit', false);
		$action			= (isset($action)) ? $request->variable('action', $action) : $request->variable('action', '');
		$delete			= $request->variable('delete', false);

		if ($action == 'trace')
		{
			$permission = $request->variable('auth', '');
			$user_id = $request->variable('user_id', 0);
			$category_id = $request->variable('category_id', 0);

			$this->tpl_name = 'permission_trace';

			if ($user_id && $auth->acl_get('a_viewauth'))
			{
				$this->page_title = sprintf($user->lang['TRACE_PERMISSION'], $user->lang[$permission]);
				$this->permission_trace($user_id, $category_id, $permission);
				return;
			}
			trigger_error('NO_MODE', E_USER_ERROR);
		}

		if ($all_cats)
		{
			$category_id = array();
			$sql = 'SELECT category_id
				FROM '. $this->categories_table;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$category_id[] = $row['category_id'];
			}
			$db->sql_freeresult($result);
		}

		// Map usernames to ids and vice versa
		if ($usernames)
		{
			$username = explode("\n", $usernames);
		}
		unset($usernames);

		if (sizeof($username) && !sizeof($user_id))
		{
			user_get_id_name($user_id, $username);

			if (!sizeof($user_id))
			{
				trigger_error($user->lang['SELECTED_USER_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}
		unset($username);

		if ($delete)
		{
			$action = 'delete';
		}

		// Handle actions
		switch ($action)
		{
			case 'settings':
				$settings = $this->get_mask($group_id, $category_id, $user_id, $mode);
			break;

			case 'delete':
				if (confirm_box(true))
				{
					$this->delete_permissions($group_id, $user_id, $category_id);
				}
				else
				{
					$s_hidden_fields = array(
						'i'				=> $id,
						'mode'			=> $mode,
						'action'		=> array($action => 1),
						'user_id'		=> $user_id,
						'group_id'		=> $group_id,
						'category_id'	=> $category_id,
						'delete'		=> true,
					);
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
				}
			break;

			case 'setting_group_local':
				$items = $this->permissions_v_mask($category_id, $user_id);
				$submit_edit_options = $request->variable('submit_edit_options', false);
				if ($submit_edit_options)
				{
					$action = 'settings';
				}

			break;

			default:
				$cats_box = $phpbb_ext_kb->make_category_select(0, false, true, false, false);
				$template->assign_vars(array(
					'L_TITLE'					=> $this->title,
					'L_EXPLAIN'					=> $this->title_explain,
					'S_SELECT_CATEGORY'			=> true, //($cats_box) ? true : false,
					'CATS_BOX'					=> $cats_box,
					'S_KB_PERMISSIONS_ACTION' 	=> $this->u_action . '&amp;action=setting_group_local',
					)
				);
			break;
		}

		if (sizeof($category_id))
		{
			$sql = 'SELECT category_name
				FROM ' . $this->categories_table . '
				WHERE ' . $db->sql_in_set('category_id', $category_id) . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			$category_names = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$category_names[] = $row['category_name'];
			}
			$db->sql_freeresult($result);

			$template->assign_vars(array(
				'S_CATEGORY_NAMES'		=> (sizeof($category_names)) ? true : false,
				'CATEGORY_NAMES'		=> implode($user->lang['COMMA_SEPARATOR'], $category_names))
			);
		}
	}

	function permissions_v_mask($category_id, $user_id)
	{
		global $db, $user, $template, $phpbb_root_path, $phpEx;

		$items = $this->retrieve_defined_user_groups('local', $category_id, 'kb_');

		if (empty($category_id))
		{
			$template->assign_vars(array(
				'L_TITLE'			=> $this->title,
				'L_EXPLAIN'			=> $this->title_explain,
				'S_SELECT_CATEGORY'	=> true)
			);
			return array();
		}

		$s_defined_group_options = $items['group_ids_options'];
		$s_defined_user_options = $items['user_ids_options'];
		$this->page_title = ($this->mode == 'mask') ? 'ACP_LIBRARY_PERMISSIONS_MASK' : 'ACP_LIBRARY_PERMISSIONS';

		$s_hidden_fields = array(
			'category_id'		=> $category_id,
			'user_id'			=> $user_id,
		);

		$template->assign_vars(array(
			'L_TITLE'					=> $this->title,
			'L_EXPLAIN'					=> $this->title_explain,
			'S_SELECT'					=> true,
			'S_CAN_SELECT_USER'			=> true,
			'S_CAN_SELECT_GROUP'		=> true,
			'S_ADD_GROUP_OPTIONS'		=> group_select_options(false, $items['group_ids'], false),	// Show all groups
			'U_FIND_USERNAME'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=add_user&amp;field=username&amp;select_single=true'),
			'S_DEFINED_GROUP_OPTIONS'	=> $s_defined_group_options,
			'S_DEFINED_USER_OPTIONS'	=> $s_defined_user_options,
			'S_KB_PERMISSIONS_ACTION' 	=> $this->u_action . '&amp;action=settings',
			'S_HIDDEN_FIELDS'			=> build_hidden_fields($s_hidden_fields),
			'MASK_MODE'					=> ($this->mode == 'mask') ? true : false,
			'L_EDIT_PERMISSIONS'		=> $this->title_edit_permissions,
			'L_ADD_PERMISSIONS'			=> $this->title_add_permissions,
			)
		);
		return $items;
	}

	/**
	* Get already assigned users/groups
	*/
	function retrieve_defined_user_groups($permission_scope, $category_id, $permission_type)
	{
		global $db, $user;
		$sql_where = '';

		$sql_category_id = ($permission_scope == 'global') ? 'AND a.category_id = 0' : ((sizeof($category_id)) ? 'AND ' . $db->sql_in_set('a.category_id', $category_id) : 'AND a.category_id <> 0');

		// Permission options are only able to be a permission set... therefore we will pre-fetch the possible options and also the possible roles
		$option_ids = array();

		$sql = 'SELECT auth_option_id
			FROM ' . $this->options_table . '
			WHERE auth_option ' . $db->sql_like_expression($permission_type . $db->get_any_char());
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$option_ids[] = (int) $row['auth_option_id'];
		}
		$db->sql_freeresult($result);

		if (sizeof($option_ids))
		{
			$sql_where = 'AND ' . $db->sql_in_set('a.auth_option_id', $option_ids);
		}

		// Not ideal, due to the filesort, non-use of indexes, etc.
		$sql = 'SELECT DISTINCT u.user_id, u.username, u.username_clean, u.user_regdate
			FROM ' . USERS_TABLE . ' u, ' . $this->kb_users_table . " a
			WHERE u.user_id = a.user_id
				$sql_where
				$sql_category_id
			ORDER BY u.username_clean, u.user_regdate ASC";
		$result = $db->sql_query($sql);

		$s_defined_user_options = '';
		$defined_user_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$s_defined_user_options .= '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
			$defined_user_ids[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT DISTINCT g.group_type, g.group_name, g.group_id
			FROM ' . GROUPS_TABLE . ' g, ' . $this->kb_groups_table . " a
			WHERE g.group_id = a.group_id
				$sql_where
				$sql_category_id
			ORDER BY g.group_type DESC, g.group_name ASC";
		$result = $db->sql_query($sql);

		$s_defined_group_options = '';
		$defined_group_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$s_defined_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
			$defined_group_ids[] = $row['group_id'];
		}
		$db->sql_freeresult($result);

		return array(
			'group_ids'			=> $defined_group_ids,
			'group_ids_options'	=> $s_defined_group_options,
			'user_ids'			=> $defined_user_ids,
			'user_ids_options'	=> $s_defined_user_options
		);
	}

	function get_mask($group_id, $category_id, $user_id, $user_mode)
	{
		global $db, $template, $user, $request;

		if (!empty($group_id))
		{
			$user_mode = 'group';
		}

		$view_user_mask = ($this->mode == 'mask' && $user_mode != 'group') ? true : false;

		if (empty($group_id) && empty($user_id))
		{
			$this->permissions_v_mask($category_id, $user_id);
			return;
		}

		$types = array('u_' => $user->lang['ACL_TYPE_U_'], 'm_' => $user->lang['ACL_TYPE_M_']);

		$apply_all_permissions = $request->variable('apply_all_permissions', false);

		if (!empty($user_id) && $user_mode != 'group')
		{
			$where = $db->sql_in_set('user_id', $user_id, false);
			if ($where == 'user_id = 0')
			{
				$where = 'user_id = 1';
			}
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE ' . $where . '';
			$result = $db->sql_query($sql);
			while ($users = $db->sql_fetchrow($result))
			{
				$user_name = $users['username'];
				$group_ids[] = $groups[$user_name] = $users['user_id'];
			}
			if (!$user_mode)
			{
				$user_mode = 'user';
			}
		}
		else
		{
			$sql = 'SELECT group_id, group_name
				FROM ' . GROUPS_TABLE . '
				WHERE ' . $db->sql_in_set('group_id', $group_id, false) . '';
			$result = $db->sql_query($sql);
			while ($group = $db->sql_fetchrow($result))
			{
				$group_name = ($user->lang['G_' . $group['group_name']]) ? $user->lang['G_' . $group['group_name']] : $group['group_name'];
				$group_ids[] = $groups[$group_name] = $group['group_id'];
			}
			if (!$user_mode)
			{
				$user_mode = 'group';
			}
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . $this->categories_table . '
			WHERE ' . $db->sql_in_set('category_id', $category_id, false) . '';
		$result = $db->sql_query($sql);

		$table = ($user_mode == 'user') ? $this->kb_users_table : $this->kb_groups_table;
		$id_field = $user_mode . '_id';

		while ($row = $db->sql_fetchrow($result)) // categories
		{
			$cat_id = $row['category_id'];
			$template->assign_block_vars('p_mask', array(
				'CATEGORY_ID'	=> $cat_id,
				'CATEGORY_NAME'	=> $row['category_name'],
				'S_VIEW'		=> ($this->mode == 'mask') ? true : false,
				)
			);

			foreach ($groups as $key => $group_id) // groups
			{
				$template->assign_block_vars('p_mask.g_mask', array(
					'GROUP_ID'		=> $group_id,
					'GROUP_NAME'	=> $key,
					'PADDING'		=> '',
					)
				);

				foreach($types as $key => $value)
				{
					$submit = $request->variable('submit', array(array(0)));
					$inherit = $request->variable('inherit', array(array(0)));

					$sql = 'SELECT *
						FROM ' . $this->options_table . '
						WHERE auth_option_id <> 0
							AND auth_option LIKE \'%' . $key . '%\'';
					$res = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($res))
					{
						$auth_option =  $row['auth_option'];
						$options[$auth_option] = $row['auth_option_id'];
					}
					$db->sql_freeresult($res);

					foreach ($options as $name => $option)
					{
						if ($view_user_mask)
						{
							$auth_setting[$option] = $auth[$option] = $this->permission_trace($group_id, $cat_id, $name);
							$_options[$name] = $this->permission_trace($group_id, $cat_id, $name);
						}
						else
						{
							$sql1 = 'SELECT auth_setting
								FROM ' . $table . '
								WHERE ' . $id_field . ' = ' . $group_id . '
									AND auth_option_id = ' . $option . '
									AND category_id = ' . $cat_id . '';
							$result1 = $db->sql_query($sql1);
							$auth = $db->sql_fetchrow($result1);

							if (!isset($auth['auth_setting']))
							{
								$auth['auth_setting'] = '';
							}
							$_options[$name] = $auth['auth_setting'];
						}
					}

					$option_settings = $request->variable('setting', array(0 => array(0 => array('' => 0))));

					$groups_ary[$group_id] = $_options;
					$hold_ary[$cat_id] = $groups_ary;

					$res = array_diff(array_count_values($_options), array('1'));
					$index = key($res);
					$v = $res[$index];
					if (sizeof($_options) == $v)
					{
						if ($index === 1)
						{
							$all_yes = true;
							$all_never = false;
							$all_no = false;
						}
						else if ($index === 0)
						{
							$all_yes = false;
							$all_never = true;
							$all_no = false;
						}
						else if ($index === '')
						{
							$all_yes = false;
							$all_never = false;
							$all_no = true;
						}
					}
					else
					{
						$all_yes = $all_never = $all_no = false;
					}

					$template->assign_block_vars('p_mask.g_mask.category', array(
						'PERMISSION_TYPE'	=> $value,
						'S_YES'				=> $all_yes,
						'S_NEVER'			=> $all_never,
						'S_NO'				=> $all_no,
						)
					);

					foreach ($_options as $name => $option)
					{
						if (!isset($_options[$name]) || $_options[$name] === '')
						{
							$_yes = false;
							$_no = true;
							$_never = false;
						}
						else if ($option)
						{
							$_yes = true;
							$_no = false;
							$_never = false;
						}
						else
						{
							$_yes = false;
							$_no = false;
							$_never = true;
						}

						$template->assign_block_vars('p_mask.g_mask.category.mask', array(
							'S_FIELD_NAME'	=> $name,
							'L_FIELD_NAME'	=> $user->lang[$name],
							'S_YES'			=> ($_yes) ? true : false,
							'S_NO'			=> ($_no) ? true : false,
							'S_NEVER'		=> ($_never) ? true : false,
							'U_TRACE'		=> ($user_mode == 'user') ? $this->u_action . '&amp;action=trace&amp;user_id=' . $group_id . '&amp;auth=' . $name . '&amp;category_id=' . $cat_id : '',
							)
						);
					}
					unset ($_options);
					unset($options);
				}
			}
		}
		$db->sql_freeresult($result);

		if ($submit)
		{
			foreach ($submit as $key => $value)
			{
				foreach ($submit[$key] as $second => $val)
				{
					$select[$key][$second] = $option_settings[$key][$second];
				}
			}
			$this->apply_all_permissions($select, $user_mode);
		}

		if($apply_all_permissions && !empty($inherit))
		{
			foreach ($inherit as $key => $value)
			{
				foreach ($inherit[$key] as $second => $val)
				{
					$select[$key][$second] = $option_settings[$key][$second];
				}
			}
			$this->apply_all_permissions($select, $user_mode);
		}

		$s_hidden_fields = array(
			'category_id'	=> $category_id,
			'group_id'		=> $group_ids,
			'user_id'		=> $group_ids,
			'p_mode'		=> $user_mode,
		);

		$template->assign_vars(array(
			'L_TITLE'					=> ($this->mode == 'mask') ? $user->lang['ACP_LIBRARY_PERMISSIONS_MASK'] : $user->lang['ACL_SET'],
			'L_EXPLAIN'					=> $this->title_explain,
			'S_VIEWING_PERMISSIONS'		=> true,
			'S_VIEWING_MASK'			=> ($this->mode == 'mask') ? true : false,
			'S_HIDDEN_FIELDS'			=> build_hidden_fields($s_hidden_fields),
			)
		);
		return;
	}

	function apply_all_permissions($hold_ary, $user_mode)
	{
		global $db, $user, $phpbb_admin_path, $phpEx;

		$sql = 'SELECT auth_option, auth_option_id
			FROM ' . $this->options_table;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$auth_option = $row['auth_option'];
			$auth_option_ids[$auth_option] = $row['auth_option_id'];
		}

		$table = ($user_mode == 'user') ? $this->kb_users_table : $this->kb_groups_table;
		$id_field = $user_mode . '_id';
		$group_id = $user_id = $category_id = array();

		foreach($hold_ary as $cat => $value)
		{
			foreach($value as $group => $settings)
			{
				$category_id[] = $cat;
				foreach($settings as $opt_name => $option)
				{
					if ($option == -1)
					{
						$sql = 'DELETE FROM ' . $table . '
							WHERE ' . $id_field . ' = ' . $group . '
							AND category_id = ' . $cat . '
							AND auth_option_id = ' . $auth_option_ids[$opt_name] . '';
						$db->sql_query($sql);
					}
					else
					{
						$sql = 'SELECT * FROM ' . $table . '
							WHERE ' . $id_field . ' = ' . $group . '
							AND category_id = ' . $cat . '
							AND auth_option_id = ' . $auth_option_ids[$opt_name] . '';

						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						if ($row)
						{
							$sql = 'UPDATE ' . $table . '
								SET auth_setting = ' . $option . '
								WHERE '.$id_field.' = ' . $group . '
									AND category_id = ' . $cat . '
									AND auth_option_id = ' . $auth_option_ids[$opt_name] . '';
							$db->sql_query($sql);
						}
						else
						{
							$sql = 'INSERT INTO ' . $table . ' (' . $id_field . ', category_id, auth_option_id, auth_setting)
								VALUES (' . $group . ', ' . $cat . ', ' . $auth_option_ids[$opt_name] . ', ' . $option . ')';
							$db->sql_query($sql);
						}
					}

					if ($user_mode == 'user')
					{
						$user_id[] = $group;
					}
					else
					{
						$group_id[] = $group;
					}
				}
			}
		}

		$this->add_kb_log($group_id, $user_id, $category_id, 'LOG_LIBRARY_PERMISSION_ADD');
		$url = '' . $this->u_action . '&amp;action=setting_group_local&amp;category_id[]=' . implode('&amp;category_id[]=', $category_id) . '';
		trigger_error($user->lang['AUTH_UPDATED'] . adm_back_link($url));
		return;
	}

	function delete_permissions($group_id, $user_id, $category_id)
	{
		global $db, $user, $phpbb_admin_path, $phpEx, $phpbb_log;

		if (empty($group_id) && empty($user_id))
		{
			return;
		}
		$phpbb_log->set_log_table($this->kb_logs_table);
		(empty($group_id)) ? $user_mode = 'user' : $user_mode = 'group';
		$table = ($user_mode == 'user') ? $this->kb_users_table : $this->kb_groups_table;
		$id_field = $user_mode . '_id';
		$where = ($user_mode == 'user') ? $user_id : $group_id;

		$sql = 'DELETE FROM ' . $table . '
			WHERE ' . $id_field . ' IN (' . implode(',', $where) . ')
				AND category_id IN (' . implode(',', $category_id).')';
		$db->sql_query($sql);

		$this->add_kb_log($group_id, $user_id, $category_id, 'LOG_LIBRARY_PERMISSION_DELETED');

		$cat_list = implode('&amp;category_id[]=', $category_id);
		$url = '' . $this->u_action . '&amp;action=setting_group_local&amp;category_id[]='.implode('&amp;category_id[]=', $category_id) . '';
		trigger_error($user->lang['AUTH_UPDATED'] . adm_back_link($url));
	}

	function add_kb_log($group_id, $user_id, $category_id, $log_type)
	{
		global $db, $user, $phpbb_log, $phpbb_container;

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$phpbb_log->set_log_table($this->kb_logs_table);

		$user_mode = (empty($group_id)) ? 'user' : 'group';

		$sql = 'SELECT category_name
			FROM ' . $this->categories_table . '
			WHERE ' . $db->sql_in_set('category_id', $category_id) . '
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql);

		$category_names = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$category_names[] = $row['category_name'];
		}
		$db->sql_freeresult($result);

		if ($user_mode == 'user')
		{
			$gr_name =  'username';
			$tbl = USERS_TABLE;
			$where = $db->sql_in_set('user_id', $user_id);
		}
		else
		{
			$gr_name =  'group_name';
			$tbl = GROUPS_TABLE;
			$where = $db->sql_in_set('group_id', $group_id);
		}

		$sql = 'SELECT ' . $gr_name . ' FROM ' . $tbl . '
				WHERE ' . $where;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$names[] = ($user_mode == 'user') ? $row['username'] : $group_helper->get_name($row['group_name']);
		}

		$db->sql_freeresult($result);

		foreach($names as $namekey => $name)
		{
			foreach($category_names as $key => $category_name)
			{
				$phpbb_log->add('admin', $user->data['user_id'], $user->data['user_ip'], $log_type, time(), array($category_name, $name));
			}
		}
	}

	/**
	* Display a complete trace tree for the selected permission to determine where settings are set/unset
	*/
	function permission_trace($user_id, $category_id, $permission)
	{
		global $db, $template, $user, $auth, $request, $phpbb_container;

		if ($user_id != $user->data['user_id'])
		{
			$userdata = $auth->obtain_user_data($user_id);
		}
		else
		{
			$userdata = $user->data;
		}

		if (!$userdata)
		{
			trigger_error('NO_USERS', E_USER_ERROR);
		}

		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $phpbb_container->get('group_helper');

		$category_name = false;

		if ($category_id)
		{
			$sql = 'SELECT category_name
				FROM ' . $this->categories_table . "
				WHERE category_id = $category_id";
			$result = $db->sql_query($sql, 3600);
			$cat_name = $db->sql_fetchfield('category_name');
			$db->sql_freeresult($result);
		}

		$back = $request->variable('back', 0);

		$template->assign_vars(array(
			'PERMISSION'			=> $user->lang($permission),
			'PERMISSION_USERNAME'	=> $userdata['username'],
			'FORUM_NAME'			=> $cat_name,
			)
		);

		$template->assign_block_vars('trace', array(
			'WHO'				=> $user->lang['DEFAULT'],
			'INFORMATION'		=> $user->lang['TRACE_DEFAULT'],
			'S_SETTING_NO'		=> true,
			'S_TOTAL_NO'		=> true)
		);

		$sql = 'SELECT DISTINCT g.group_name, g.group_id, g.group_type
			FROM ' . GROUPS_TABLE . ' g
				LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (ug.group_id = g.group_id)
			WHERE ug.user_id = ' . $user_id . '
				AND ug.user_pending = 0
				AND NOT (ug.group_leader = 1 AND g.group_skip_auth = 1)
			ORDER BY g.group_type DESC, g.group_id DESC';
		$result = $db->sql_query($sql);

		$groups = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$groups[$row['group_id']] = array(
				'auth_setting'		=> ACL_NO,
				'group_name'		=> $group_helper->get_name($row['group_name']),
			);
		}
		$db->sql_freeresult($result);

		$total = ACL_NO;
		$add_key = (($category_id) ? '_LOCAL' : '');

		if (sizeof($groups))
		{
			// Get group auth settings
			$hold_ary = $this->kb_acl_group_raw_data(array_keys($groups), $permission, $category_id);

			foreach ($hold_ary as $group_id => $category_ary)
			{
				$groups[$group_id]['auth_setting'] = $hold_ary[$group_id][$category_id][$permission];
			}
			unset($hold_ary);

			foreach ($groups as $id => $row)
			{
				switch ($row['auth_setting'])
				{
					case ACL_NO:
						$information = $user->lang['KB_TRACE_GROUP_NO' . $add_key];
					break;

					case ACL_YES:
						$information = ($total == ACL_YES) ? $user->lang['KB_TRACE_GROUP_YES_TOTAL_YES' . $add_key] : (($total == ACL_NEVER) ? $user->lang['KB_TRACE_GROUP_YES_TOTAL_NEVER' . $add_key] : $user->lang['KB_TRACE_GROUP_YES_TOTAL_NO' . $add_key]);
						$total = ($total == ACL_NO) ? ACL_YES : $total;
					break;

					case ACL_NEVER:
						$information = ($total == ACL_YES) ? $user->lang['KB_TRACE_GROUP_NEVER_TOTAL_YES' . $add_key] : (($total == ACL_NEVER) ? $user->lang['KB_TRACE_GROUP_NEVER_TOTAL_NEVER' . $add_key] : $user->lang['KB_TRACE_GROUP_NEVER_TOTAL_NO' . $add_key]);
						$total = ACL_NEVER;
					break;
				}

				$template->assign_block_vars('trace', array(
					'WHO'			=> $row['group_name'],
					'INFORMATION'	=> $information,

					'S_SETTING_NO'		=> ($row['auth_setting'] == ACL_NO) ? true : false,
					'S_SETTING_YES'		=> ($row['auth_setting'] == ACL_YES) ? true : false,
					'S_SETTING_NEVER'	=> ($row['auth_setting'] == ACL_NEVER) ? true : false,
					'S_TOTAL_NO'		=> ($total == ACL_NO) ? true : false,
					'S_TOTAL_YES'		=> ($total == ACL_YES) ? true : false,
					'S_TOTAL_NEVER'		=> ($total == ACL_NEVER) ? true : false)
				);
			}
		}

		// Get user specific permission... globally or for this category
		$hold_ary = $this->kb_acl_user_raw_data($user_id, $permission, $category_id);
		$auth_setting = (!sizeof($hold_ary)) ? ACL_NO : $hold_ary[$user_id][$category_id][$permission];

		switch ($auth_setting)
		{
			case ACL_NO:
				$information = ($total == ACL_NO) ? $user->lang['KB_TRACE_USER_NO_TOTAL_NO' . $add_key] : $user->lang['KB_KB_TRACE_USER_KEPT' . $add_key];
				$total = ($total == ACL_NO) ? ACL_NEVER : $total;
			break;

			case ACL_YES:
				$information = ($total == ACL_YES) ? $user->lang['KB_TRACE_USER_YES_TOTAL_YES' . $add_key] : (($total == ACL_NEVER) ? $user->lang['KB_TRACE_USER_YES_TOTAL_NEVER' . $add_key] : $user->lang['KB_TRACE_USER_YES_TOTAL_NO' . $add_key]);
				$total = ($total == ACL_NO) ? ACL_YES : $total;
			break;

			case ACL_NEVER:
				$information = ($total == ACL_YES) ? $user->lang['KB_TRACE_USER_NEVER_TOTAL_YES' . $add_key] : (($total == ACL_NEVER) ? $user->lang['KB_TRACE_USER_NEVER_TOTAL_NEVER' . $add_key] : $user->lang['KB_TRACE_USER_NEVER_TOTAL_NO' . $add_key]);
				$total = ACL_NEVER;
			break;
		}

		$template->assign_block_vars('trace', array(
			'WHO'			=> $userdata['username'],
			'INFORMATION'	=> $information,

			'S_SETTING_NO'		=> ($auth_setting == ACL_NO) ? true : false,
			'S_SETTING_YES'		=> ($auth_setting == ACL_YES) ? true : false,
			'S_SETTING_NEVER'	=> ($auth_setting == ACL_NEVER) ? true : false,
			'S_TOTAL_NO'		=> false,
			'S_TOTAL_YES'		=> ($total == ACL_YES) ? true : false,
			'S_TOTAL_NEVER'		=> ($total == ACL_NEVER) ? true : false)
		);

		// Take founder or admin status into account, overwriting the default values
		if ($userdata['user_type'] == USER_FOUNDER || (!empty($auth->acl_get_list($userdata['user_id'], 'a_manage_kb'))))
		{
			$information = ($userdata['user_type'] == USER_FOUNDER) ? $user->lang['KB_TRACE_USER_FOUNDER'] : $user->lang['KB_TRACE_USER_ADMIN'];
			$template->assign_block_vars('trace', array(
				'WHO'			=> $userdata['username'],
				'INFORMATION'	=> $information,

				'S_SETTING_NO'		=> ($auth_setting == ACL_NO) ? true : false,
				'S_SETTING_YES'		=> ($auth_setting == ACL_YES) ? true : false,
				'S_SETTING_NEVER'	=> ($auth_setting == ACL_NEVER) ? true : false,
				'S_TOTAL_NO'		=> false,
				'S_TOTAL_YES'		=> true,
				'S_TOTAL_NEVER'		=> false)
			);

			$total = ACL_YES;
		}

		// Total value...
		$template->assign_vars(array(
			'S_RESULT_NO'		=> ($total == ACL_NO) ? true : false,
			'S_RESULT_YES'		=> ($total == ACL_YES) ? true : false,
			'S_RESULT_NEVER'	=> ($total == ACL_NEVER) ? true : false,
		));

		return ($total) ? $total : 0;
	}

	/**
	* Get raw group based permission settings
	*/
	function kb_acl_group_raw_data($group_id = false, $opts = false, $category_id = false)
	{
		global $db;

		$sql_group = ($group_id !== false) ? ((!is_array($group_id)) ? 'group_id = ' . (int) $group_id : $db->sql_in_set('group_id', array_map('intval', $group_id))) : '';
		$sql_category = ($category_id !== false) ? ((!is_array($category_id)) ? 'AND a.category_id = ' . (int) $category_id : 'AND ' . $db->sql_in_set('a.category_id', array_map('intval', $category_id))) : '';

		$sql_opts = '';
		$hold_ary = $sql_ary = array();

		if ($opts !== false)
		{
			$this->build_auth_option_statement('ao.auth_option', $opts, $sql_opts);
		}

		// Grab group settings - non-role specific...
		$sql_ary[] = 'SELECT a.group_id, a.category_id, a.auth_setting, a.auth_option_id, ao.auth_option
			FROM ' . $this->kb_groups_table . ' a, ' . $this->options_table . ' ao
			WHERE a.auth_option_id = ao.auth_option_id ' .
				(($sql_group) ? 'AND a.' . $sql_group : '') . "
				$sql_category
				$sql_opts
			ORDER BY a.category_id, ao.auth_option";

		foreach ($sql_ary as $sql)
		{
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$hold_ary[$row['group_id']][$row['category_id']][$row['auth_option']] = $row['auth_setting'];
			}
			$db->sql_freeresult($result);
		}

		return $hold_ary;
	}

	/**
	* Get raw user based permission settings
	*/
	function kb_acl_user_raw_data($user_id = false, $opts = false, $category_id = false)
	{
		global $db;

		$sql_user = ($user_id !== false) ? ((!is_array($user_id)) ? 'user_id = ' . (int) $user_id : $db->sql_in_set('user_id', array_map('intval', $user_id))) : '';
		$sql_category = ($category_id !== false) ? ((!is_array($category_id)) ? 'AND a.category_id = ' . (int) $category_id : 'AND ' . $db->sql_in_set('a.category_id', array_map('intval', $category_id))) : '';

		$sql_opts = '';
		$hold_ary = $sql_ary = array();

		if ($opts !== false)
		{
			$this->build_auth_option_statement('ao.auth_option', $opts, $sql_opts);
		}

		// Grab user settings - non-role specific...
		$sql_ary[] = 'SELECT a.user_id, a.category_id, a.auth_setting, a.auth_option_id, ao.auth_option
			FROM ' . $this->kb_users_table . ' a, ' . $this->options_table . ' ao
			WHERE a.auth_option_id = ao.auth_option_id ' .
				(($sql_user) ? 'AND a.' . $sql_user : '') . "
				$sql_category
				$sql_opts
			ORDER BY a.category_id, ao.auth_option";

		foreach ($sql_ary as $sql)
		{
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$hold_ary[$row['user_id']][$row['category_id']][$row['auth_option']] = $row['auth_setting'];
			}
			$db->sql_freeresult($result);
		}

		return $hold_ary;
	}

	/**
	* Fill auth_option statement for later querying based on the supplied options
	*/
	function build_auth_option_statement($key, $auth_options, &$sql_opts)
	{
		global $db;

		if (!is_array($auth_options))
		{
			if (strpos($auth_options, '%') !== false)
			{
				$sql_opts = "AND $key " . $db->sql_like_expression(str_replace('%', $db->get_any_char(), $auth_options));
			}
			else
			{
				$sql_opts = "AND $key = '" . $db->sql_escape($auth_options) . "'";
			}
		}
		else
		{
			$is_like_expression = false;

			foreach ($auth_options as $option)
			{
				if (strpos($option, '%') !== false)
				{
					$is_like_expression = true;
				}
			}

			if (!$is_like_expression)
			{
				$sql_opts = 'AND ' . $db->sql_in_set($key, $auth_options);
			}
			else
			{
				$sql = array();

				foreach ($auth_options as $option)
				{
					if (strpos($option, '%') !== false)
					{
						$sql[] = $key . ' ' . $db->sql_like_expression(str_replace('%', $db->get_any_char(), $option));
					}
					else
					{
						$sql[] = $key . " = '" . $db->sql_escape($option) . "'";
					}
				}

				$sql_opts = 'AND (' . implode(' OR ', $sql) . ')';
			}
		}
	}
}
