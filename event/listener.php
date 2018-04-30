<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb	emplate	emplate */
	protected $template;

	/** @var helper */
	protected $helper;

	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user $user User object */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	//** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string KB_ATTACHMENTS_TABLE */
	protected $attachments_table;

/**
* Assign functions defined in this class to event listeners in the core
*
* @return array
* @static
* @access public
*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
			'core.page_header'						=> 'add_page_header_link',
			'core.viewonline_overwrite_location'	=> 'viewonline_location',
			'core.permissions'						=> 'add_permission',
		);
	}

	/**
	* Constructor
	*
	* @param \phpbb\template\template             $template              Template object
	* @param \phpbb\config\config                 $config                Config object
	* @param \phpbb\db\driver\driver_interface    $db                    DBAL object
	* @param \phpbb\user                          $user                  User object
	* @param string                               $phpbb_root_path       phpbb_root_path
	* @param string                               $attachments_table     KB_ATTACHMENTS_TABLE
	* @access public
	*/
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\controller\helper $helper,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		$phpbb_root_path,
		$attachments_table
	)
	{
		$this->template				= $template;
		$this->helper				= $helper;
		$this->config				= $config;
		$this->db					= $db;
		$this->user					= $user;
		$this->auth					= $auth;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->attachments_table	= $attachments_table;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'sheer/knowledgebase',
			'lang_set' => 'knowledgebase_lng',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_LIBRARY'			=> ($this->auth->acl_get('u_kb_view') || $this->auth->acl_get('a_manage_kb')) ? $this->helper->route('sheer_knowledgebase_index') : '',
		));
	}

	public function add_permission($event)
	{
		$permissions = $event['permissions'];
		$categories = $event['categories'];
		$permissions['a_manage_kb']	= array('lang' => 'ACL_A_MANAGE_KB', 'cat' => 'knowledgebase');
		$permissions['u_kb_view']	= array('lang' => 'ACL_U_KB_VIEW', 'cat' => 'knowledgebase');
		$event['permissions'] = $permissions;
		$event['categories'] = array_merge($categories, array('knowledgebase' => 'KNOWLEDGEBASE'));
	}

	public function viewonline_location($event)
	{
		$on_page = $event['row'];
		if ($on_page['session_page'] == 'app.php/knowledgebase')
		{
			$event['location'] = $this->user->lang['LIBRARY'];
			$event['location_url'] = $this->helper->route('sheer_knowledgebase_index');
		}
	}
}
