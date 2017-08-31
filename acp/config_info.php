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

class config_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\knowledgebase\acp\config_module',
			'version'	=> '1.0.0',
			'title' => 'ACP_KNOWLEDGE_BASE_CONFIGURE',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_KNOWLEDGE_BASE_CONFIGURE',
					'auth' => 'ext_sheer/knowledgebase && acl_a_board && acl_a_manage_kb',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
			),
		);
	}
}
