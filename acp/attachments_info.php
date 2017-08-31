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

class attachments_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\knowledgebase\acp\attachments_module',
			'version'	=> '1.0.0',
			'title' => 'ACP_LIBRARY_ATTACHMENTS',
			'modes'		=> array(
				'attachments'	=> array(
					'title' => 'ACP_LIBRARY_ATTACHMENTS',
					'auth' => 'ext_sheer/knowledgebase && acl_a_board && acl_a_manage_kb',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
				'orphan'	=> array(
					'title' => 'ACP_LIBRARY_ATTACHMENTS_ORPHAN',
					'auth' => 'ext_sheer/garage && acl_a_board',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
				'extra_files'	=> array(
					'title' => 'ACP_LIBRARY_ATTACHMENTS_EXTRA_FILES',
					'auth' => 'ext_sheer/garage && acl_a_board',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
				'lost_files'	=> array(
					'title' => 'ACP_LIBRARY_ATTACHMENTS_LOST_FILES',
					'auth' => 'ext_sheer/garage && acl_a_board',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
			),
		);
	}
}
