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

class set_order
{	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	//** @var string \sheer\knowledgebase\inc\functions_kb */
	protected $kb;

	/** @var string KB_ARTICLES_TABLE */
	protected $articles_table;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request_interface $request,
		\sheer\knowledgebase\inc\functions_kb $kb,
		$articles_table
	)
	{
		$this->db				= $db;
		$this->request			= $request;
		$this->kb				= $kb;
		$this->articles_table	= $articles_table;
	}

	public function main()
	{		$list_order =	$this->request->variable('list_order', '');
		$page =			$this->request->variable('page', 0);
		$kb_config = $this->kb->obtain_kb_config();
		$per_page = $kb_config['articles_per_page'];		$list = explode(',', $list_order);
		$i = 1 + (($page - 1) * $per_page);
		foreach($list as $id)
		{			$sql  = 'UPDATE ' . $this->articles_table . ' SET display_order = ' . $i . ' WHERE article_id = ' . $id;
			$i++;
			$this->db->sql_query($sql);		} 		return new Response('', 200);
	}
}