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

class permissions_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\knowledgebase\acp\permissions_module',
			'version'	=> '1.0.0',
			'title' => 'ACP_LIBRARY_PERMISSIONS',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_LIBRARY_PERMISSIONS',
					'auth' => 'ext_sheer/knowledgebase && acl_a_board && acl_a_manage_kb',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
				'mask'	=> array(
					'title' => 'ACP_LIBRARY_PERMISSIONS_MASK',
					'auth' => 'ext_sheer/knowledgebase && acl_a_board && acl_a_manage_kb',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
			),
		);
	}
}
