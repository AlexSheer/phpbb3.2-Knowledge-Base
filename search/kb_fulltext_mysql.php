<?php
/**
 *
 * Knowledge base. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Sheer
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sheer\knowledgebase\search;

//use Symfony\Component\DependencyInjection\ContainerInterface;

//class kb_fulltext_mysql
class kb_fulltext_mysql extends \sheer\knowledgebase\search\kb_base
{
	/**
	 * Associative array holding index stats
	 * @var array
	 */
	protected $stats = array();

	/**
	 * Holds the words entered by user, obtained by splitting the entered query on whitespace
	 * @var array
	 */
	protected $split_words = array();

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Database connection
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Associative array stores the min and max word length to be searched
	 * @var array
	 */
	protected $word_length = array();

	/**
	 * Contains tidied search query.
	 * Operators are prefixed in search query and common words excluded
	 * @var string
	 */
	protected $search_query;

	/**
	 * Contains common words.
	 * Common words are words with length less/more than min/max length
	 * @var array
	 */
	protected $common_words = array();

	/**
	 * Constructor
	 * Creates a new \phpbb\search\fulltext_mysql, which is used as a search backend
	 *
	 * @param string|bool $error Any error that occurs is passed on through this reference variable otherwise false
	 * @param string $phpbb_root_path Relative path to phpBB root
	 * @param string $phpEx PHP file extension
	 * @param \phpbb\auth\auth $auth Auth object
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\db\driver\driver_interface Database object
	 * @param \phpbb\user $user User object
	 */
	public function __construct(&$error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user)
	{
		$this->config = $config;
		$this->db = $db;

		$this->user = $user;

		$this->word_length = array('min' => $this->config['fulltext_mysql_min_word_len'], 'max' => $this->config['fulltext_mysql_max_word_len']);

		/**
		 * Load the UTF tools
		 */
		if (!function_exists('utf8_strlen'))
		{
			include($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
		}

		global $table_prefix;

		$error = false;
		$this->articles_table = $table_prefix . 'kb_articles';
		$this->search_results_table = $table_prefix . 'kb_search_results';
	}

	/**
	* Returns the name of this search backend to be displayed to administrators
	*
	* @return string Name
	*/
	public function get_name()
	{
		return 'Knowledge Base MySQL Fulltext';
	}

	/**
	 * Returns the search_query
	 *
	 * @return string search query
	 */
	public function get_search_query()
	{
		return $this->search_query;
	}

	/**
	 * Returns the common_words array
	 *
	 * @return array common words that are ignored by search backend
	 */
	public function get_common_words()
	{
		return $this->common_words;
	}

	/**
	 * Returns the word_length array
	 *
	 * @return array min and max word length for searching
	 */
	public function get_word_length()
	{
		return $this->word_length;
	}

	/**
	* Checks for correct MySQL version and stores min/max word length in the config
	*
	* @return string|bool Language key of the error/incompatiblity occurred
	*/
	public function init()
	{
		if ($this->db->get_sql_layer() != 'mysql4' && $this->db->get_sql_layer() != 'mysqli')
		{
			return $this->user->lang['FULLTEXT_MYSQL_INCOMPATIBLE_DATABASE'];
		}

		$result = $this->db->sql_query('SHOW TABLE STATUS LIKE \'' . $this->articles_table . '\'');
		$info = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$engine = '';
		if (isset($info['Engine']))
		{
			$engine = $info['Engine'];
		}
		else if (isset($info['Type']))
		{
			$engine = $info['Type'];
		}

		$fulltext_supported =
			$engine === 'MyISAM' ||
			// FULLTEXT is supported on InnoDB since MySQL 5.6.4 according to
			// http://dev.mysql.com/doc/refman/5.6/en/innodb-storage-engine.html
			// We also require https://bugs.mysql.com/bug.php?id=67004 to be
			// fixed for proper overall operation. Hence we require 5.6.8.
			$engine === 'InnoDB' &&
			phpbb_version_compare($this->db->sql_server_info(true), '5.6.8', '>=');

		if (!$fulltext_supported)
		{
			return $this->user->lang['FULLTEXT_MYSQL_NOT_SUPPORTED'];
		}

		$sql = 'SHOW VARIABLES
			LIKE \'ft\_%\'';
		$result = $this->db->sql_query($sql);

		$mysql_info = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$mysql_info[$row['Variable_name']] = $row['Value'];
		}
		$this->db->sql_freeresult($result);

		$this->config->set('fulltext_mysql_max_word_len', $mysql_info['ft_max_word_len']);
		$this->config->set('fulltext_mysql_min_word_len', $mysql_info['ft_min_word_len']);

		return false;
	}

	/**
	* Splits keywords entered by a user into an array of words stored in $this->split_words
	* Stores the tidied search query in $this->search_query
	*
	* @param string &$keywords Contains the keyword as entered by the user
	* @param string $terms is either 'all' or 'any'
	* @return bool false if no valid keywords were found and otherwise true
	*/
	public function split_keywords(&$keywords, $terms)
	{
		if ($terms == 'all')
		{
			$match		= array('#\sand\s#iu', '#\sor\s#iu', '#\snot\s#iu', '#(^|\s)\+#', '#(^|\s)-#', '#(^|\s)\|#');
			$replace	= array(' +', ' |', ' -', ' +', ' -', ' |');

			$keywords = preg_replace($match, $replace, $keywords);
		}

		// Filter out as above
		$split_keywords = preg_replace("#[\n\r\t]+#", ' ', trim(htmlspecialchars_decode($keywords)));

		// Split words
		$split_keywords = preg_replace('#([^\p{L}\p{N}\'*"()])#u', '$1$1', str_replace('\'\'', '\' \'', trim($split_keywords)));
		$matches = array();
		preg_match_all('#(?:[^\p{L}\p{N}*"()]|^)([+\-|]?(?:[\p{L}\p{N}*"()]+\'?)*[\p{L}\p{N}*"()])(?:[^\p{L}\p{N}*"()]|$)#u', $split_keywords, $matches);
		$this->split_words = $matches[1];

		// We limit the number of allowed keywords to minimize load on the database
		if ($this->config['max_num_search_keywords'] && sizeof($this->split_words) > $this->config['max_num_search_keywords'])
		{
			trigger_error($this->user->lang('MAX_NUM_SEARCH_KEYWORDS_REFINE', (int) $this->config['max_num_search_keywords'], sizeof($this->split_words)));
		}

		// to allow phrase search, we need to concatenate quoted words
		$tmp_split_words = array();
		$phrase = '';
		foreach ($this->split_words as $word)
		{
			if ($phrase)
			{
				$phrase .= ' ' . $word;
				if (strpos($word, '"') !== false && substr_count($word, '"') % 2 == 1)
				{
					$tmp_split_words[] = $phrase;
					$phrase = '';
				}
			}
			else if (strpos($word, '"') !== false && substr_count($word, '"') % 2 == 1)
			{
				$phrase = $word;
			}
			else
			{
				$tmp_split_words[] = $word;
			}
		}
		if ($phrase)
		{
			$tmp_split_words[] = $phrase;
		}

		$this->split_words = $tmp_split_words;

		unset($tmp_split_words);
		unset($phrase);

		foreach ($this->split_words as $i => $word)
		{
			$clean_word = preg_replace('#^[+\-|"]#', '', $word);

			// check word length
			$clean_len = utf8_strlen(str_replace('*', '', $clean_word));
			if (($clean_len < $this->config['fulltext_mysql_min_word_len']) || ($clean_len > $this->config['fulltext_mysql_max_word_len']))
			{
				$this->common_words[] = $word;
				unset($this->split_words[$i]);
			}
		}

		if ($terms == 'any')
		{
			$this->search_query = '';
			foreach ($this->split_words as $word)
			{
				if ((strpos($word, '+') === 0) || (strpos($word, '-') === 0) || (strpos($word, '|') === 0))
				{
					$word = substr($word, 1);
				}
				$this->search_query .= $word . ' ';
			}
		}
		else
		{
			$this->search_query = '';
			foreach ($this->split_words as $word)
			{
				if ((strpos($word, '+') === 0) || (strpos($word, '-') === 0))
				{
					$this->search_query .= $word . ' ';
				}
				else if (strpos($word, '|') === 0)
				{
					$this->search_query .= substr($word, 1) . ' ';
				}
				else
				{
					$this->search_query .= '+' . $word . ' ';
				}
			}
		}

		$this->search_query = utf8_htmlspecialchars($this->search_query);

		if ($this->search_query)
		{
			$this->split_words = array_values($this->split_words);
			sort($this->split_words);
			return true;
		}
		return false;
	}

	/**
	* Turns text into an array of words
	* @param string $text contains post text/subject
	*/
	public function split_message($text)
	{
		// Split words
		$text = preg_replace('#([^\p{L}\p{N}\'*])#u', '$1$1', str_replace('\'\'', '\' \'', trim($text)));
		$matches = array();
		preg_match_all('#(?:[^\p{L}\p{N}*]|^)([+\-|]?(?:[\p{L}\p{N}*]+\'?)*[\p{L}\p{N}*])(?:[^\p{L}\p{N}*]|$)#u', $text, $matches);
		$text = $matches[1];

		// remove too short or too long words
		$text = array_values($text);
		for ($i = 0, $n = sizeof($text); $i < $n; $i++)
		{
			$text[$i] = trim($text[$i]);
			if (utf8_strlen($text[$i]) < $this->config['fulltext_mysql_min_word_len'] || utf8_strlen($text[$i]) > $this->config['fulltext_mysql_max_word_len'])
			{
				unset($text[$i]);
			}
		}

		return array_values($text);
	}

	/**
	* Performs a search on keywords depending on display specific params. You have to run split_keywords() first
	*
	* @param	string		$type				contains either posts or topics depending on what should be searched for
	* @param	string		$fields				contains either titleonly (topic titles should be searched), msgonly (only message bodies should be searched), firstpost (only subject and body of the first post should be searched) or all (all post bodies and subjects should be searched)
	* @param	string		$terms				is either 'all' (use query as entered, words without prefix should default to "have to be in field") or 'any' (ignore search query parts and just return all posts that contain any of the specified words)
	* @param	array		$sort_by_sql		contains SQL code for the ORDER BY part of a query
	* @param	string		$sort_key			is the key of $sort_by_sql for the selected sorting
	* @param	string		$sort_dir			is either a or d representing ASC and DESC
	* @param	string		$sort_days			specifies the maximum amount of days a post may be old
	* @param	array		$ex_fid_ary			specifies an array of forum ids which should not be searched
	* @param	string		$post_visibility	specifies which types of posts the user can view in which forums
	* @param	array		$author_ary			an array of author ids if the author should be ignored during the search the array is empty
	* @param	string		$author_name		specifies the author match, when ANONYMOUS is also a search-match
	* @param	array		&$id_ary			passed by reference, to be filled with ids for the page specified by $start and $per_page, should be ordered
	* @param	int			$start				indicates the first index of the page
	* @param	int			$per_page			number of ids each page is supposed to contain
	* @return	boolean|int						total number of results
	*/
	public function keyword_search($type, $fields, $terms, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $category_id, $author_ary, $author_name, $id_ary, $start, $per_page)
	{
		// No keywords? No posts
		if (!$this->search_query)
		{
			return false;
		}
		$search_result = array();

		// generate a search_key from all the options to identify the results
		$search_key_array = array(
			implode(', ', $this->split_words),
			$type,
			$fields,
			$terms,
			$sort_days,
			$sort_key,
			false,
			implode(',', $ex_fid_ary),
			true,
			implode(',', $author_ary)
		);

		$search_key = md5(implode('#', $search_key_array));

		if ($start < 0)
		{
			$start = 0;
		}

		// try reading the results from cache
		$result_count = 0;

		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == 1)
		{
			$search_result['total_matches'] = $result_count;
			$search_result['start'] = $start;
			$search_result['id_ary'] = $id_ary;

			return $search_result;
		}

		$id_ary = array();

		// Build sql strings for sorting
		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

		// Build some display specific sql strings
		switch ($fields)
		{
			case 'titleonly':
				$sql_match = 'p.article_title';
			break;

			case 'descronly':
				$sql_match = 'p.article_description';
			break;

			case 'msgonly':
				$sql_match = 'p.article_body';
			break;

			default:
				$sql_match = 'p.article_title, p.article_body, p.article_description';
			break;
		}

		$search_query = $this->search_query;

		$sql_select			= (!$result_count) ? 'SQL_CALC_FOUND_ROWS ' : '';
		$sql_select			= $sql_select . 'p.article_id';
		$field				= 'article_id';

		if (sizeof($author_ary) && $author_name)
		{
			// first one matches post of registered users, second one guests and deleted users
			$sql_author = ' AND (' . $this->db->sql_in_set('p.author_id', array_diff($author_ary, array(ANONYMOUS)), false, true) . ' OR p.author = \'' . $author_name . '\')';
		}
		else if (sizeof($author_ary))
		{
			$sql_author = ' AND ' . $this->db->sql_in_set('p.author_id', $author_ary);
		}
		else
		{
			$sql_author = '';
		}

		$sql_where_options = ($category_id) ? ' AND p.article_category_id = ' . $category_id : '';
		$sql_where_options .= (sizeof($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.article_category_id', $ex_fid_ary, true) : '';
		$sql_where_options .= $sql_author;
		$sql_where_options .= ($sort_days) ? ' AND p.article_date >= ' . (time() - ($sort_days * 86400)) : '';
		$sql_where_options .= $sql_author;
		$sql_where_options .= ' AND p.approved = 1 ';

		$sql = "SELECT $sql_select
			FROM " . $this->articles_table . " p
			WHERE MATCH ($sql_match) AGAINST ('" . $this->db->sql_escape(htmlspecialchars_decode($this->search_query)) . "' IN BOOLEAN MODE)
				$sql_where_options
			ORDER BY $sql_sort";
		$this->db->sql_return_on_error(true); // Fix bug with SQL error if empty index
		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = (int) $row[$field];
			$ids[] = array('article_id' => (int) $row[$field]);
		}
		$this->db->sql_freeresult($result);

		$id_ary = array_unique($id_ary);

		// if the total result count is not cached yet, retrieve it from the db
		if (!$result_count && sizeof($id_ary))
		{
			$sql_found_rows = 'SELECT FOUND_ROWS() as result_count';
			$result = $this->db->sql_query($sql_found_rows);
			$result_count = (int) $this->db->sql_fetchfield('result_count');
			$this->db->sql_freeresult($result);

			if (!$result_count)
			{
				return false;
			}
		}

		if ($start >= $result_count)
		{
			$start = floor(($result_count - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[$field];
			}
			$this->db->sql_freeresult($result);

			$id_ary = array_unique($id_ary);
		}

		// store the ids, from start on then delete anything that isn't on the current page because we only need ids for one page
		$this->save_ids($search_key, implode(' ', $this->split_words), $author_ary, $result_count, $id_ary, $start, $sort_dir);

		$search_result['total_matches'] = $result_count;
		$search_result['start'] = $start;
		$search_result['id_ary'] = array_slice($id_ary, 0, (int) $per_page);

		return $search_result;
	}


	/* Performs a search on an author's posts without caring about message contents. Depends on display specific params
	*/
	public function author_search($type, $sort_by_sql, $sort_key, $sort_dir, $sort_days, $ex_fid_ary, $category_id, $author_ary, $author_name, $id_ary, $start, $per_page)
	{
		// No author? No posts
		if (!sizeof($author_ary))
		{
			return 0;
		}
		$search_result = array();

		// generate a search_key from all the options to identify the results
		$search_key = crc32(implode('#', array(
			'',
			$type,
			'',
			'',
			'',
			$sort_days,
			$sort_key,
			$category_id,
			implode(',', $ex_fid_ary),
			true,
			implode(',', $author_ary),
			$author_name,
		)));

		if ($start < 0)
		{
			$start = 0;
		}

		// try reading the results from cache
		$result_count = 0;
		if ($this->obtain_ids($search_key, $result_count, $id_ary, $start, $per_page, $sort_dir) == 1)
		{
			$search_result['total_matches'] = $result_count;
			$search_result['start'] = $start;
			$search_result['id_ary'] = $id_ary;

			return $search_result;
		}

		$id_ary = array();

		// Create some display specific sql strings

		$sql_author = '' . $this->db->sql_in_set('p.author_id', $author_ary) . ' AND p.approved=1 ';

		$sql_fora = (sizeof($ex_fid_ary)) ? ' AND ' . $this->db->sql_in_set('p.article_category_id', $ex_fid_ary, true) : '';
		$sql_category_id = ($category_id) ? ' AND p.article_category_id = ' . (int) $category_id : '';
		$sql_time = ($sort_days) ? ' AND p.article_date >= ' . (time() - ($sort_days * 86400)) : '';

		// Build sql strings for sorting

		$sql_sort = $sort_by_sql[$sort_key] . (($sort_dir == 'a') ? ' ASC' : ' DESC');

		$m_approve_fid_sql = ' AND ' . $post_visibility;

		// If the cache was completely empty count the results
		$calc_results = ($result_count) ? '' : 'SQL_CALC_FOUND_ROWS ';

		// Build the query for really selecting the post_ids
		$sql = "SELECT {$calc_results}p.article_id
			FROM " . $this->articles_table . ' p' . "
			WHERE $sql_author
				$sql_category_id
				$sql_fora
				$sql_time
			ORDER BY $sql_sort";
			$field = 'article_id';

		// Only read one block of posts from the db and then cache it

		$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = (int) $row[$field];
		}
		$this->db->sql_freeresult($result);

		// retrieve the total result count if needed
		if (!$result_count)
		{
			$sql_found_rows = 'SELECT FOUND_ROWS() as result_count';
			$result = $this->db->sql_query($sql_found_rows);
			$result_count = (int) $this->db->sql_fetchfield('result_count');
			$this->db->sql_freeresult($result);

			if (!$result_count)
			{
				return false;
			}
		}

		if ($start >= $result_count)
		{
			$start = floor(($result_count - 1) / $per_page) * $per_page;

			$result = $this->db->sql_query_limit($sql, $this->config['search_block_size'], $start);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$id_ary[] = (int) $row[$field];
			}
			$this->db->sql_freeresult($result);

			$id_ary = array_unique($id_ary);
		}

		if (sizeof($id_ary))
		{
			$this->save_ids($search_key, '', $author_ary, $result_count, $id_ary, $start, $sort_dir);

			$search_result['total_matches'] = $result_count;
			$search_result['start'] = $start;
			$search_result['id_ary'] = array_slice($id_ary, 0, (int) $per_page);

			return $search_result;
		}
		return false;
	}

	/**
	* Destroys cached search results, that contained one of the new words in a post so the results won't be outdated
	*
	* @param	string		$mode contains the post mode: edit, post, reply, quote ...
	* @param	int			$post_id	contains the post id of the post to index
	* @param	string		$message	contains the post text of the post
	* @param	string		$subject	contains the subject of the post to index
	* @param	int			$poster_id	contains the user id of the poster
	*/
	public function index($mode, $article_id, &$message, &$subject, &$description, $poster_id)
	{
		// Split old and new post/subject to obtain array of words
		$split_text = $this->split_message($message);
		$split_title = ($subject) ? $this->split_message($subject) : array();
		// Add in vers 1.0.3 -->
		$split_descr = $this->split_message($description);
		//<--

		$kb_words = array_unique(array_merge($split_text, $split_title, $split_descr));

		unset($split_text);
		unset($split_title);
// Add in vers 1.0.3 -->
		unset($split_descr);
//<--

		// destroy cached search results
		$this->destroy_src_cache();

		unset($kb_words);
	}

	/**
	* Destroy cached results, that might be outdated after deleting a post
	*/
	public function index_remove($post_ids, $author_ids)
	{
		$this->destroy_src_cache();
	}

	/**
	* Destroy old cache entries
	*/
	public function tidy()
	{
		// destroy too old cached search results
		$this->destroy_src_cache();

		$this->config->set('search_last_gc', time(), false);
	}

	/**
	* Create fulltext index
	*
	* @return string|bool error string is returned incase of errors otherwise false
	*/
	public function create_index($acp_module, $u_action)
	{
		// Make sure we can actually use MySQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$alter_list = array();

		if (!isset($this->stats['article_title']))
		{
			$alter_entry = array();
			if ($this->db->get_sql_layer() == 'mysqli' || version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
			{
				$alter_entry[] = 'MODIFY article_title varchar(255) COLLATE utf8_unicode_ci DEFAULT \'\' NOT NULL';
			}
			else
			{
				$alter_entry[] = 'MODIFY article_title text NOT NULL';
			}
			$alter_entry[] = 'ADD FULLTEXT (article_title)';
			$alter_list[] = $alter_entry;
		}
// Add in vers 1.0.3 -->
		if (!isset($this->stats['article_description']))
		{
			$alter_entry = array();
			if ($this->db->get_sql_layer() == 'mysqli' || version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
			{
				$alter_entry[] = 'MODIFY article_description varchar(255) COLLATE utf8_unicode_ci DEFAULT \'\' NOT NULL';
			}
			else
			{
				$alter_entry[] = 'MODIFY article_description text NOT NULL';
			}
			$alter_entry[] = 'ADD FULLTEXT (article_description)';
			$alter_list[] = $alter_entry;
		}

		if (!isset($this->stats['article_body']))
		{
			$this->db->sql_query('ALTER TABLE ' . $this->articles_table . ' ADD FULLTEXT article_body (article_body)');
		}
//<--
		if (!isset($this->stats['article_content']))
		{
			$alter_entry = array();
			if ($this->db->get_sql_layer() == 'mysqli' || version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
			{
				$alter_entry[] = 'MODIFY article_body mediumtext COLLATE utf8_unicode_ci NOT NULL';
			}
			else
			{
				$alter_entry[] = 'MODIFY article_body mediumtext NOT NULL';
			}

			$alter_entry[] = 'ADD FULLTEXT article_content (article_body, article_title, article_description)'; // , add article_description in vers 1.0.3

			$alter_list[] = $alter_entry;
		}

		if (sizeof($alter_list))
		{
			foreach ($alter_list as $alter)
			{
				$this->db->sql_query('ALTER TABLE ' . $this->articles_table . ' ' . implode(', ', $alter));
			}
		}

		$this->destroy_src_cache();
		$this->db->sql_query('TRUNCATE TABLE ' . SEARCH_RESULTS_TABLE);

		return false;
	}

	/**
	* Drop fulltext index
	*
	* @return string|bool error string is returned incase of errors otherwise false
	*/
	public function delete_index($acp_module, $u_action)
	{
		// Make sure we can actually use MySQL with fulltext indexes
		if ($error = $this->init())
		{
			return $error;
		}

		if (empty($this->stats))
		{
			$this->get_stats();
		}

		$alter = array();

		if (isset($this->stats['article_title']))
		{
			$alter[] = 'DROP INDEX article_title';
		}
// Add in vers 1.0.3 -->
		if (isset($this->stats['article_description']))
		{
			$alter[] = 'DROP INDEX article_description';
		}

		if (isset($this->stats['article_body']))
		{
			$alter[] = 'DROP INDEX article_body';
		}
//<--
		if (isset($this->stats['article_content']))
		{
			$alter[] = 'DROP INDEX article_content';
		}

		if (sizeof($alter))
		{
			$this->db->sql_query('ALTER TABLE ' . $this->articles_table . ' ' . implode(', ', $alter));
		}

		$this->destroy_src_cache();
		$this->db->sql_query('TRUNCATE TABLE ' . $this->search_results_table);

		return false;
	}

	/**
	* Returns true if both FULLTEXT indexes exist
	*/
	public function index_created()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return isset($this->stats['article_title']) && isset($this->stats['article_content']) && isset($this->stats['article_description']) && isset($this->stats['article_body']);
	}

	/**
	* Returns an associative array containing information about the indexes
	*/
	public function index_stats()
	{
		if (empty($this->stats))
		{
			$this->get_stats();
		}

		return array(
			$this->user->lang['FULLTEXT_MYSQL_TOTAL_POSTS']			=> ($this->index_created()) ? $this->stats['total_posts'] : 0,
		);
	}

	/**
	 * Computes the stats and store them in the $this->stats associative array
	 */
	protected function get_stats()
	{
		if (strpos($this->db->get_sql_layer(), 'mysql') === false)
		{
			$this->stats = array();
			return;
		}

		$sql = 'SHOW INDEX
			FROM ' . $this->articles_table;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// deal with older MySQL versions which didn't use Index_type
			$index_type = (isset($row['Index_type'])) ? $row['Index_type'] : $row['Comment'];

			if ($index_type == 'FULLTEXT')
			{
				if ($row['Key_name'] == 'article_title')
				{
					$this->stats['article_title'] = $row;
				}
// Add in vers 1.0.3 -->
				else if ($row['Key_name'] == 'article_description')
				{
					$this->stats['article_description'] = $row;
				}

				else if ($row['Key_name'] == 'article_body')
				{
					$this->stats['article_body'] = $row;
				}
//<--
				else if ($row['Key_name'] == 'article_content')
				{
					$this->stats['article_content'] = $row;
				}
			}
		}
		$this->db->sql_freeresult($result);

		$this->stats['total_posts'] = empty($this->stats) ? 0 : $this->db->get_estimated_row_count($this->articles_table);
	}
}
