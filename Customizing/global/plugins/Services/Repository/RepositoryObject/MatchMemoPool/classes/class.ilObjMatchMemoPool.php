<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for match & memo pool repository object.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*/
class ilObjMatchMemoPool extends ilObjectPlugin
{
	const MIN_PAIRS_NUM = 16;

	protected $plugin;
	protected $online;
	protected $pair;
	
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
		$this->online = 0;
	}
	

	/**
	* Get type.
	* The initType() method must set the same ID as the plugin ID.
	*/
	final function initType()
	{
		$this->setType("xmpl");
	}
	
	/**
	* Create object
	* This method is called, when a new repository object is created.
	* The Object-ID of the new object can be obtained by $this->getId().
	* You can store any properties of your object that you need.
	* It is also possible to use multiple tables.
	* Standard properites like title and description are handled by the parent classes.
	*/
	function doCreate()
	{
		global $ilDB;

		$ilDB->manipulateF(
			"INSERT INTO rep_robj_xmpl_object (obj_fi, isonline) VALUES(%s, %s)",
			array('integer', 'integer'),
			array($this->getId(), $this->getOnline())
		);

		$this->createMetaData();
	}
	
	/**
	* Read data from db
	* This method is called when an instance of a repository object is created and an existing Reference-ID is provided to the constructor.
	* All you need to do is to read the properties of your object from the database and to call the corresponding set-methods.
	*/
	function doRead()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$this->setOnline($row['isonline']);
		}
		else
		{
			$this->setOnline(0);
		}
	}
	
	public function createEmptyPair($title = null, $author = null, $description = null, $card1 = null, $card2 = null, $solution = null)
	{
		global $ilDB, $ilUser;
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$next_id = $ilDB->nextId('rep_robj_xmpl_pair');
		$result = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_pair (pair_id, obj_fi, owner, title, author, description, card1, card2, solution, created, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','text','text','text','text','text','text','integer','integer'),
			array(
				$next_id,
				$this->getId(),
				$ilUser->getId(),
				(!$title) ? null : $title,
				(!$author) ? null : $author,
				(!$description) ? null : $description,
				(!$card1) ? null : ilRTE::_replaceMediaObjectImageSrc($card1, 0),
				(!$card2) ? null : ilRTE::_replaceMediaObjectImageSrc($card2, 0),
				(!$solution) ? null : ilRTE::_replaceMediaObjectImageSrc($solution, 0),
				time(),
				(!$card1) ? 0 : time()
			)
		);
		return $next_id;
	}

	public function savePair()
	{
		global $ilDB, $ilUser;
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$affectedRows = $ilDB->manipulateF("UPDATE rep_robj_xmpl_pair SET title = %s, author = %s, description = %s, card1 = %s, card2 = %s, solution = %s, tstamp = %s WHERE pair_id = %s",
			array('text','text','text','text','text','text','integer','integer'),
			array(
				$this->pair->title,
				$this->pair->author,
				$this->pair->description,
				ilRTE::_replaceMediaObjectImageSrc($this->pair->card1, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->pair->card2, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->pair->solution, 0),
				time(),
				$_GET['pid']
			)
		);
		$this->updatePairCount();
		$this->cleanupMediaObjectUsage();
	}

	/**
	* Update data
	* This method is called, when an existing object is updated.
	*/
	function doUpdate()
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT * FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if($ilDB->numRows($res) == 0)
		{
			$ilDB->manipulateF(
				"INSERT INTO rep_robj_xmpl_object (obj_fi, isonline) VALUES(%s, %s)",
				array('integer', 'integer'),
				array($this->getId(), $this->getOnline())
			);
		}
		else
		{
			$ilDB->manipulateF("UPDATE rep_robj_xmpl_object SET isonline = %s WHERE obj_fi = %s",
				array('integer','integer'),
				array($this->getOnline(), $this->getId())
			);
		}

		$this->updatePairCount();
	}

	/**
	* Delete data from db
	* This method is called, when a repository object is finally deleted from the system.
	* It is not called if an object is moved to the trash.
	*/
	function doDelete()
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
		
		// @todo: Delete pairs? wtf!?!?
	}
	
	/**
	* Copies a memory pair into another memory pool
	*
	* @param integer $id Database id of the pair
	* @param integer $pool_id Database id of the target memory pool
	*/
	public function copyPair($id, $pool_id)
	{
		$this->plugin->includeClass("class.ilMatchMemoPair.php");
		$pair = ilMatchMemoPair::_duplicate($id, $pool_id);
	}

	/**
	* Do Cloning
	* This method is called, when a repository object is copied.
	*/
	function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$this->cloneMetaData($new_obj);
		$new_obj->online = $this->online;
		$new_obj->doUpdate();

		// clone the pairs in the match & memo pool
		$pairs = $this->getPairBrowserData();
		foreach ($pairs as $pair)
		{
			$new_obj->copyPair($pair['pair_id'], $new_obj->getId());
		}

		return $new_obj;
	}


	public function getOnline()
	{
		return $this->online;
	}

	public function setOnline($online)
	{
		$this->online = $online;
	}
	
	public static function _getConfigurationValue($key)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting("xmpl");
		if (strcmp($key, 'theme') == 0 && strlen($setting->get($key)) == 0)
		{
			return "dark_rounded";
		}
		else
		{
			return $setting->get($key);
		}
	}

	public static function _setConfiguration($key, $value)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting("xmpl");
		$setting->set($key, $value);
	}

	public static function _randomPairsForGame($game_id, $pool_ids, $nr_of_pairs)
	{
		global $ilDB;

		$random_pairs = array();
		if(is_array($pool_ids))
		{
			$pools_by_percent = array();
			foreach($pool_ids as $data)
			{
				if($data['obj_id'] <= 0)
				{
					continue;
				}

				$key = (string)(float)$data['percent'];
				$pools_by_percent[$key][] = $data;
			}

			foreach($pools_by_percent  as $percent => &$pools)
			{
				shuffle($pools);

				$prio =  1;
				foreach($pools as &$data)
				{
					$data['prio'] = $prio++;
				}
			}

			$valid_pools = array();
			foreach($pools_by_percent as $pools)
			{
				foreach($pools as $pool)
				{
					$valid_pools[] = $pool;
				}
			}

			$props_per_pool = array();
			$valid_pools = ilUtil::sortArray($valid_pools, 'percent', 'desc', true);
			foreach($valid_pools as $data)
			{
				$pairs  = array();
				$result = $ilDB->queryF("SELECT rep_robj_xmpl_pair.pair_id FROM rep_robj_xmpl_pair, rep_robj_xmry_pair WHERE rep_robj_xmry_pair.pair_fi = rep_robj_xmpl_pair.pair_id AND rep_robj_xmpl_pair.obj_fi = %s AND rep_robj_xmry_pair.obj_fi = %s", 
					array('integer', 'integer'),
					array($data['obj_id'], $game_id)
				);

				if($ilDB->numRows($result) == 0)
				{
					continue;
				}

				while($row = $ilDB->fetchAssoc($result))
				{
					$pairs[] = $row['pair_id'];
				}

				shuffle($pairs);

				$percent = $data['percent'];
				if($percent === null)
				{
					$percent = 100 / count($valid_pools);
				}

				$num_pairs = count($pairs);
				$step      = $percent / ($num_pairs - 1);
				foreach(array_flip(array_values($pairs)) as $pair_id => $key)
				{
					$probability = max(0.0, $percent - (float)$key * $step);
					$pair_data = array(
						'prop'    => $probability,
						'prio'    => $data['prio'],
						'pair_id' => $pair_id,
						'pool_id' => $data['obj_id'],
					);

					$props_per_pool[$data['obj_id']][] = $pair_data;
					$random_pairs[]                    = $pair_data;
				}
			}

			usort($random_pairs, function($a, $b) {
				if($b['prop'] == $a['prop'])
				{
					return $b['prio'] < $a['prio'] ? 1 : -1;
				}
				return $b['prop'] > $a['prop'] ? 1 : -1;
			});

			$random_pairs = array_slice($random_pairs, 0, $nr_of_pairs);

			$pairs_per_pool = array();
			foreach($random_pairs as $pair)
			{
				$pairs_per_pool[$pair['pool_id']][] = $pair;
			}

			$random_pairs = array_map(function($pair) {
				return $pair['pair_id'];
			}, $random_pairs);
		}
		else
		{
			$pairs = array();
			$result = $ilDB->queryF("SELECT rep_robj_xmpl_pair.pair_id FROM rep_robj_xmpl_pair, rep_robj_xmry_pair WHERE rep_robj_xmry_pair.pair_fi = rep_robj_xmpl_pair.pair_id AND rep_robj_xmpl_pair.obj_fi = %s AND rep_robj_xmry_pair.obj_fi = %s",
				array("integer", "integer"),
				array($pool_ids, $game_id)
			);
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($pairs, $row['pair_id']);
			}
			$rnd = array_rand($pairs, $nr_of_pairs);
			foreach ($rnd as $index)
			{
				array_push($random_pairs, $pairs[$index]);
			}
		}

		return $random_pairs;
	}

	public function deletePair($id)
	{
		global $ilDB;
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmpl_pair WHERE pair_id = %s",
			array('integer'),
			array($id)
		);
		$this->updatePairCount();
	}

	public function updatePairCount()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT pair_id FROM rep_robj_xmpl_pair WHERE obj_fi = %s AND original_id IS NULL AND tstamp > %s",
			array("integer", "integer"),
			array($this->getId(), 0)
		);
		$affectedRows = $ilDB->manipulateF("UPDATE rep_robj_xmpl_object SET paircount = %s WHERE obj_fi = %s",
			array("integer", "integer"),
			array($result->numRows(), $this->getId())
		);
	}

	public static function _updatePairCount($mpl)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT pair_id FROM rep_robj_xmpl_pair WHERE obj_fi = %s AND original_id IS NULL AND tstamp > %s",
			array("integer", "integer"),
			array($mpl, 0)
		);
		$affectedRows = $ilDB->manipulateF("UPDATE rep_robj_xmpl_object SET paircount = %s WHERE obj_fi = %s",
			array("integer", "integer"),
			array($result->numRows(), $mpl)
		);
	}

	/*
	* Remove all pairs with tstamp = 0
	*/
	public function purgePairs()
	{
		global $ilDB, $ilUser;

		$result = $ilDB->queryF("SELECT pair_id FROM rep_robj_xmpl_pair WHERE owner = %s AND tstamp = %s",
			array("integer", "integer"),
			array($ilUser->getId(), 0)
		);
		while ($data = $ilDB->fetchAssoc($result))
		{
			$this->deletePair($data["pair_id"]);
		}
	}

	/**
	* synchronises appearances of media objects in the pair object with media
	* object usage table
	*/
	protected function cleanupMediaObjectUsage()
	{
		$combinedtext = $this->pair->card1 . $this->pair->card2 . $this->pair->solution;
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($combinedtext, "xmpl:html", $_GET['pid']);
	}

	/*
	* Load a pair
	*/
	public function loadPair($id)
	{
		global $ilDB, $ilUser;

		$this->plugin->includeClass("class.ilMatchMemoPair.php");
		$this->pair = ilMatchMemoPair::_loadFromDB($id);
	}

	public function getPairBrowserData()
	{
		global $ilDB;

		$data = array();
		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmpl_pair WHERE obj_fi = %s AND original_id IS NULL",
			array("integer"),
			array($this->getId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($data, $row);
		}
		return $data;
	}

	/**
	* Copies a question to the clipboard
	*
	* @param integer $id Object id of the memory pair
	* @access private
	*/
	function copyToClipboard($id)
	{
		if (!array_key_exists("mpl_clipboard", $_SESSION))
		{
			$_SESSION["mpl_clipboard"] = array();
		}
		$_SESSION["mpl_clipboard"][$id] = array("id" => $id, "action" => "copy");
	}

	/**
	* Moves a memory pair to the clipboard
	*
	* @param integer $id Object id of the memory pair
	* @access private
	*/
	function moveToClipboard($id)
	{
		if (!array_key_exists("mpl_clipboard", $_SESSION))
		{
			$_SESSION["mpl_clipboard"] = array();
		}
		$_SESSION["mpl_clipboard"][$id] = array("id" => $id, "action" => "move");
	}

	/**
	 * @return bool
	 */
	public function clipboardContainsValidItems()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if(!array_key_exists('mpl_clipboard', $_SESSION))
		{
			return false;
		}
		
		$pairs = $_SESSION['mpl_clipboard'];

		$valid = false;
		foreach($pairs as $pair)
		{
			if(!strlen($pair['id']) || !is_numeric($pair['id']))
			{
				continue;
			}

			$result = $ilDB->queryF(
				"SELECT obj_fi
				FROM rep_robj_xmpl_pair
				INNER JOIN object_data ON object_data.obj_id = obj_fi
				WHERE pair_id = %s
				",
				array("integer"),
				array($pair["id"])
			);
			if($result->numRows() == 1)
			{
				$valid = true;
				break;
			}
		}

		return $valid;
	}

	/**
	* Copies/Moves a memory pair from the clipboard
	*
	* @access private
	*/
	function pasteFromClipboard()
	{
		global $ilDB;

		if (array_key_exists("mpl_clipboard", $_SESSION))
		{
			$pairs = $_SESSION["mpl_clipboard"];
			foreach($pairs as $pair)
			{
				if (strcmp($pair["action"], "move") == 0)
				{
					$result = $ilDB->queryF(
						"SELECT obj_fi
						FROM rep_robj_xmpl_pair
						INNER JOIN object_data ON object_data.obj_id = obj_fi
						WHERE pair_id = %s
						",
						array("integer"),
						array($pair["id"])
					);
					if ($result->numRows() == 1)
					{
						$row = $ilDB->fetchAssoc($result);
						$source_pool = $row["obj_fi"];
						// change the pool id in the mpl_pair table
						$affectedRows = $ilDB->manipulateF("UPDATE rep_robj_xmpl_pair SET obj_fi = %s WHERE pair_id = %s",
							array('integer', 'integer'),
							array($this->getId(), $pair['id'])
						);
						ilObjMatchMemoPool::_updatePairCount($source_pool);
					}
				}
				else
				{
					$this->copyPair($pair["id"], $this->getId());
				}
			}
			$this->updatePairCount();
		}
		unset($_SESSION["mpl_clipboard"]);
	}


	public static function _lookupOnline($a_obj_id, $is_reference = false)
	{
		global $ilDB;

		if ($is_reference) $a_obj_id = ilObject::_lookupObjId($a_obj_id);
		$result = $ilDB->queryF("SELECT isonline FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array("integer"),
			array($a_obj_id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["isonline"];
		}
		return 0;
	}

	public static function _lookupPairCount($a_obj_id, $is_reference = false)
	{
		global $ilDB;

		if ($is_reference) $a_obj_id = ilObject::_lookupObjId($a_obj_id);
		$result = $ilDB->queryF("SELECT paircount FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array("integer"),
			array($a_obj_id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["paircount"];
		}
		return 0;
	}

	public function __get($value)
	{
		switch ($value)
		{
			case 'online':
				return $this->online;
				break;
			case 'pair':
				if ($this->pair == null)
				{
					if ($_GET['pid'])
					{
						$this->loadPair($_GET['pid']);
					}
					else
					{
						$this->plugin->includeClass("class.ilMatchMemoPair.php");
						$this->pair = new ilMatchMemoPair();
					}
				}
				return $this->pair;
				break;
		}
	}

	public function __set($key, $value)
	{
		switch ($key)
		{
			case 'online':
				$this->online = $value;
				break;
			case 'pair':
				$this->pair = $value;
				break;
		}
	}
}
?>
