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

class config_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $phpbb_root_path, $config, $db, $template, $request, $table_prefix, $user, $phpbb_log, $cache, $phpbb_filesystem;

		$user->add_lang('acp/attachments');

		$default_config = array();

		$this->tpl_name = 'acp_knowlegebase_body';
		$this->page_title = $user->lang('ACP_KNOWLEDGE_BASE_CONFIGURE');
		$phpbb_log->set_log_table($table_prefix . 'kb_log');

		$upload_dir = $phpbb_root_path . 'ext/sheer/knowledgebase/files/';

		if (!file_exists($phpbb_root_path . $upload_dir))
		{
			@mkdir($upload_dir, 0777);
			@mkdir($upload_dir . 'plupload/', 0777);
		}

		$result = @tempnam($upload_dir, 'i_w');
		if (!$result)
		{
			try
			{
				$phpbb_filesystem->phpbb_chmod($upload_dir, CHMOD_READ | CHMOD_WRITE);
				$phpbb_filesystem->phpbb_chmod($upload_dir . 'plupload/', CHMOD_READ | CHMOD_WRITE);
			}
			catch (\phpbb\filesystem\exception\filesystem_exception $e)
			{
				// Do nothing
			}
		}
		else
		{
			@unlink($result);
		}

		$sql = 'SELECT *
			FROM ' . $table_prefix . 'kb_config';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$config_name = $row['config_name'];
			$config_value = $row['config_value'];
			$default_config[$config_name] = isset($_POST['submit']) ? str_replace("'", "\'", $config_value) : $config_value;
			$new[$config_name] = $request->variable($config_name, $default_config[$config_name]);
		}
		$db->sql_freeresult($result);
		$new['anounce'] = $request->variable('anounce', 0);
		$extensions = unserialize($new['extensions']);

		$extension_list = $request->variable('extensions', array('' => array('')));
		$disabled_list	= $request->variable('diasabled_extensions', array('' => array('')));
		$max_filesize	= $request->variable('max_filesize', 0);
		$size_select	= $request->variable('size_select', 'b');

		$max_filesize = get_formatted_filesize($new['max_filesize'], false, array('mb', 'kb', 'b'));
		$identifier = $max_filesize['si_identifier'];
		$size_format = $max_filesize['si_identifier'];
		$max_filesize = $max_filesize['value'];

		$sql = 'SELECT e.extension, g.group_name
			FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
			WHERE e.group_id = g.group_id';
		$db->sql_query($sql);

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$all_extensions[] = $row;
		}
		$db->sql_freeresult($result);

		foreach ($all_extensions as $all_extension)
		{
			$all_names[] = $all_extension['group_name'];
		}
		$all_names = array_values(array_unique($all_names));

		foreach ($all_extensions as $all_extension)
		{
			foreach($all_names as $all_name)
			{
				foreach($all_extension as $eext)
				{
					if ($all_extension['group_name'] == $all_name)
					{
						$all[$all_name][] = $all_extension['extension'];
					}
				}
			}
		}

		foreach ($all as $group => $etxt)
		{
			$dd[$group] = array_unique($etxt);
			if (empty($extensions[$group]))
			{
				$extensions[$group] = array();
			}
		}
		krsort($extensions);
		foreach ($extensions as $extensions_group => $ext)
		{
			$disabled_ext = array_diff($dd[$extensions_group], $ext);
			$s_options = $assigned_extensions = $disabled_extensions = '';
			foreach ($disabled_ext as $disabled)
			{
				$disabled_extensions .= '<option value="' . $disabled . '">' . $disabled . '</option>';
			}
			foreach (array_values(array_unique($ext)) as $extension)
			{
				$s_options .= '<option value="' . $extension . '" selected="selected">' . $extension . '</option>';
				$assigned_extensions .= '' . $extension . ', ';
			}
			$assigned_extensions = substr($assigned_extensions, 0, -2);
			$template->assign_block_vars('row', array(
				'GROUP'					=> $extensions_group,
				'EXTENSIONS_GROUP'		=> (isset($user->lang['EXT_GROUP_' . $extensions_group])) ? $user->lang['EXT_GROUP_' . $extensions_group] : $extensions_group,
				'S_OPTIONS'				=> $s_options,
				'DIASABLED_EXTENSIONS'	=> $disabled_extensions,
				'ASSIGNED_EXTENSIONS'	=> $assigned_extensions,
			));
		}

		add_form_key('sheer/knowledgebase');
		if ($request->is_set_post('submit'))
		{
			foreach ($extensions as $group_name => $ext)
			{
				if (empty($extension_list[$group_name]))
				{
					$extension_list[$group_name] = array();
				}

				if (sizeof($disabled_list[$group_name]))
				{
					$extension_list[$group_name] = array_merge($extension_list[$group_name], $disabled_list[$group_name]);
				}
			}

			$extension_list = serialize($extension_list);
			$max_filesize	= ($size_select == 'kb') ? round($max_filesize * 1024) : (($size_select == 'mb') ? round($max_filesize * 1048576) : $max_filesize);
			$new['max_filesize'] = $max_filesize;

			if (!check_form_key('sheer/knowledgebase'))
			{
				trigger_error('FORM_INVALID');
			}

			$new[$config_name] = str_replace(",", ".", $new[$config_name]);
			foreach($new as $key => $value)
			{
				$sql = 'UPDATE ' . $table_prefix . 'kb_config
					SET config_value = \'' . $db->sql_escape($value) . '\'
					WHERE config_name = \'' . $key . '\'';
				$db->sql_query($sql);
			}
			$sql = 'UPDATE ' . $table_prefix . 'kb_config
				SET config_value = \'' . $db->sql_escape($extension_list) . '\'
				WHERE config_name = \'extensions\'';
			$db->sql_query($sql);

			$cache->destroy('_kb_config');
			$phpbb_log->add('admin', $user->data['user_id'], $user->data['user_ip'], 'LOG_LIBRARY_CONFIG', time());
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'S_CONFIGURE'				=> true,
			'S_EXT_GROUP_SIZE_OPTIONS'	=> size_select_options($identifier),
			'MAX_ATTACHMENTS'			=> $new['max_attachments'],
			'EXTGROUP_FILESIZE'			=> $max_filesize,
			'ADVANCED_FORM_ON'			=> (isset($default_config['anounce']) && $default_config['anounce']) ? 'checked="checked"' : '',
			'ADVANCED_FORM'				=> (isset($default_config['anounce']) && $default_config['anounce']) ? '' : 'none',
			'PER_PAGE'					=> (isset($new['articles_per_page'])) ? $new['articles_per_page'] : 10,
			'S_YES_ATTACH'				=> (isset($new['allow_attachments']) && $default_config['allow_attachments']) ? true : false,
			'S_YES_THUMBNAIL'			=> (isset($new['thumbnail']) && $default_config['thumbnail']) ? true : false,
			'S_FORUM_POST'				=> (isset($default_config['anounce']) && $default_config['anounce']) ? make_forum_select($new['forum_id'], 0, true, true, false) : make_forum_select(0, false, true, true, false),
			'S_ACTION'					=> $this->u_action,
		));
	}
}
