<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\core;

class helper
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                    DBAL object
	* @param \phpbb\notification\manager          $notification_manager  Notification manager object
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\notification\manager $notification_manager)
	{
		$this->db = $db;
		$this->notification_manager = $notification_manager;
	}

	// Add notifications
	public function add_notification($notification_data, $notification_type_name)
	{
		if (!$this->notification_exists($notification_data, $notification_type_name))
		{
			$this->notification_manager->add_notifications($notification_type_name, $notification_data);
		}
	}

	public function notification_exists($article_data, $notification_type_name)
	{
		$notification_type_id = $this->notification_manager->get_notification_type_id($notification_type_name);
		$sql = 'SELECT notification_id FROM ' . NOTIFICATIONS_TABLE . '
			WHERE notification_type_id = ' . (int) $notification_type_id . '
				AND item_id = ' . (int) $article_data['article_id'];
		$result = $this->db->sql_query($sql);
		$item_id = $this->db->sql_fetchfield('notification_id');
		$this->db->sql_freeresult($result);

		return ($item_id) ?: false;
	}
}
