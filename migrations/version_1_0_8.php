<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\migrations;

class version_1_0_8 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['knowledge_base_version']) && version_compare($this->config['knowledge_base_version'], '1.0.8', '>=');
	}

	static public function depends_on()
	{
		return array('\sheer\knowledgebase\migrations\version_1_0_7');
	}

	public function update_schema()
	{
		return array();
	}


	public function revert_schema()
	{
		return array();
	}

	public function update_data()
	{
		return array(
			// Update configs
			array('config.update', array('knowledge_base_version', '1.0.8')),
		);
	}
}
