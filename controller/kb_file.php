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

class kb_file
{
	/** @var \phpbb\user $user User object */
	protected $user;

	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	//** @var string php_ext */
	protected $php_ext;

	//** @var string \sheer\knowledgebase\inc\functions_kb */
	protected $kb;

	/** @var string KB_ATTACHMENTS_TABLE */
	protected $attachments_table;

	public function __construct(
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request_interface $request,
		\sheer\knowledgebase\inc\functions_kb $kb,
		$phpbb_root_path,
		$php_ext,
		$attachments_table
	)
	{
		$this->user				= $user;
		$this->config			= $config;
		$this->db				= $db;
		$this->request			= $request;
		$this->kb				= $kb;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->attachments_table= $attachments_table;
	}

	public function main()
	{
		$attach_id =		$this->request->variable('id', 0);
		$mode =				$this->request->variable('mode', '');
		$thumbnail =		$this->request->variable('t', false);

		$this->user->add_lang('viewtopic');

		if (!$attach_id)
		{
			send_status_line(404, 'Not Found');
			trigger_error('NO_ATTACHMENT_SELECTED');
		}

		$upload_path = 'ext/sheer/knowledgebase/files';
		require($this->phpbb_root_path . 'includes/functions_download' . '.' . $this->php_ext);

		$sql = 'SELECT attach_id, is_orphan, physical_filename, real_filename, extension, mimetype, filesize
			FROM ' . $this->attachments_table . "
			WHERE attach_id = $attach_id";
		$result = $this->db->sql_query($sql);
		$attachment = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$attachment)
		{
			send_status_line(404, 'Not Found');
			trigger_error('ERROR_NO_ATTACHMENT');
		}

		if ($thumbnail)
		{
			$attachment['physical_filename'] = 'thumb_' . $attachment['physical_filename'];
		}
		$this->kb->check_is_img($attachment['extension'], $extensions);
		$display_cat = $extensions[$attachment['extension']]['display_cat'];

		send_file_to_browser($attachment, $upload_path, $display_cat);
		file_gc();

		return new Response('', 200);
	}

}
