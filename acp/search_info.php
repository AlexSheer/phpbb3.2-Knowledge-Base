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

class search_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\knowledgebase\acp\search_module',
			'version'	=> '1.0.0',
			'title' => 'ACP_LIBRARY_SEARCH',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_LIBRARY_SEARCH',
					'auth' => 'ext_sheer/knowledgebase && acl_a_board && acl_a_manage_kb',
					'cat' => array('ACP_KNOWLEDGE_BASE')
				),
			),
		);
	}
}
