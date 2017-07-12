<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for Match & Memo game repository object.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*/
class ilObjMatchMemo extends ilObjectPlugin
{
	protected $plugin;
	protected $poolplugin;
	protected $_themes;
	protected $arrData;
	
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemo");
		$this->poolplugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
		$this->_themes = array();
		$this->arrData = array();
	}

	/**
	 *
	 */
	protected function cleanupHighScore()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		foreach(range(0, 3) as $game_level)
		{
			$white_list = array();
			$nicknames  = array();
			$high_score = $this->getHighScores($game_level);
			foreach($high_score as $score)
			{
				if(!$score['nickname'])
				{
					$white_list[] = $score['high_id'];
					continue;
				}

				if(!isset($nicknames[$score['nickname']]))
				{
					$white_list[]                  = $score['high_id'];
					$nicknames[$score['nickname']] = true;
				}
			}

			$in = $ilDB->in('high_id', $white_list, true, 'integer');
			$ilDB->manipulateF(
				"DELETE FROM rep_robj_xmry_high WHERE $in AND gamelevel = %s",
				array('integer'),
				array($game_level)
			);
		}
	}

	/**
	* Get type.
	* The initType() method must set the same ID as the plugin ID.
	*/
	final function initType()
	{
		$this->setType("xmry");
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
		// $myID = $this->getId();
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

		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmry WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$this->back_url = $row['back_url'];
			$this->heading = $row['heading'];
			$this->background = $row['background'];
			$this->fullscreen = $row['fullscreen'];
			$this->show_title = ($row['show_title']) ? 1 : 0;
			$this->highscore_single = ($row['highscore_single']) ? 1 : 0;
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->intro = ilRTE::_replaceMediaObjectImageSrc($row['intro'], 1);
		}

		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmry_themes WHERE obj_fi = %s ORDER BY theme_id",
			array("integer"),
			array($this->getId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$this->plugin->includeClass("class.ilMatchMemoTheme.php");
				$theme = new ilMatchMemoTheme($row['title'], $row['pool_easy'], $row['pool_medium'], $row['pool_hard'], $row['theme_id']);
				$theme->mixedpools = ($row['pools_mixed'] > 1) ? $row['pools_mixed'] : 3;
				$mixedresult = $ilDB->queryF("SELECT * FROM rep_robj_xmry_tmixed WHERE theme_fi = %s ORDER BY sequence",
					array("integer"),
					array($row['theme_id'])
				);
				$mixedpools = array();
				while ($mixedrow = $ilDB->fetchAssoc($mixedresult))
				{
					array_push($mixedpools, array('obj_id' => $mixedrow['obj_fi'], 'percent' => $mixedrow['percent']));
				}
				$theme->mixed = $mixedpools;
				$this->addThemeObject($theme);
			}
		}
	}
	
	/**
	* Update data
	* This method is called, when an existing object is updated.
	*/
	function doUpdate()
	{
		global $ilDB;

		include_once("./Services/RTE/classes/class.ilRTE.php");
		
		$result = $ilDB->queryF("SELECT mry_id FROM rep_robj_xmry WHERE obj_fi = %s",
			array("integer"),
			array($this->getId())
		);
		if($ilDB->numRows($result))
		{
			$current_row = $ilDB->fetchAssoc($result);
			$affectedRows = $ilDB->manipulateF("UPDATE rep_robj_xmry SET back_url = %s, background = %s, fullscreen = %s, heading = %s, intro = %s, show_title = %s, highscore_single = %s, updated = %s WHERE obj_fi = %s",
				array('text', 'text', 'integer', 'text', 'text', 'integer', 'integer', 'integer', 'integer'),
				array($this->back_url, (strlen($this->background)) ? $this->background : null, $this->fullscreen, $this->heading, ilRTE::_replaceMediaObjectImageSrc($this->intro, 0), $this->show_title, $this->highscore_single, time(), $this->getId())
			);

			if(!$current_row['highscore_single'] && $this->highscore_single)
			{
				$this->cleanupHighScore();
			}
		}
		else
		{
			$next_id = $ilDB->nextId('rep_robj_xmry');
			$result = $ilDB->manipulateF("INSERT INTO rep_robj_xmry (mry_id, obj_fi, back_url, background, fullscreen, heading, intro, show_title, highscore_single, created, updated) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','text','text','text','text','text','integer','integer','integer','integer'),
				array(
					$next_id,
					$this->getId(),
					$this->back_url,
					(strlen($this->background)) ? $this->background : null,
					$this->fullscreen,
					$this->heading,
					ilRTE::_replaceMediaObjectImageSrc($this->intro, 0),
					$this->show_title,
					$this->highscore_single,
					time(),
					time()
				)
			);
		}
		
		$this->cleanupMediaObjectUsage();
		
		if (!$this->highScoresExist())
		{
			$this->deleteThemesAndData();
			
			// save themes
			$copy_mpl = array();
			foreach ($this->_themes as $theme)
			{
				$next_id = $ilDB->nextId('rep_robj_xmry_themes');
				$affectedRows = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_themes (theme_id, obj_fi, title, pool_easy, pool_medium, pool_hard, pools_mixed) VALUES (%s, %s, %s, %s, %s, %s, %s)",
					array('integer', 'integer', 'text', 'integer','integer','integer','integer'),
					array($next_id, $this->getId(), $theme->title, $theme->easy, $theme->medium, $theme->hard, $theme->mixedpools)
				);
				$last_insert_id = $next_id;
				if (($theme->easy > 0) && !in_array($theme->easy, $copy_mpl)) array_push($copy_mpl, $theme->easy);
				if (($theme->medium > 0) && !in_array($theme->medium, $copy_mpl)) array_push($copy_mpl, $theme->medium);
				if (($theme->hard > 0) && !in_array($theme->hard, $copy_mpl)) array_push($copy_mpl, $theme->hard);
				// save mixed pools
				$seq = 0;
				foreach ($theme->mixed as $mixedarray)
				{
					if ($mixedarray['obj_id'])
					{
						$mry_themes_mixed_id = $ilDB->nextId('rep_robj_xmry_tmixed');
						$affectedRows = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_tmixed (mry_themes_mixed_id, theme_fi, obj_fi, percent, sequence) VALUES (%s, %s, %s, %s, %s)",
							array('integer', 'integer', 'integer', 'float','integer'),
							array($mry_themes_mixed_id, $last_insert_id, $mixedarray['obj_id'], ($mixedarray['percent'] > 0) ? $mixedarray['percent'] : null, $seq)
						);
						if (($mixedarray['obj_id'] > 0) && !in_array($mixedarray['obj_id'], $copy_mpl)) array_push($copy_mpl, $mixedarray['obj_id']);
						$seq++;
					}
				}
			}
			
			// save pair duplicates for memory games
			$this->poolplugin->includeClass("class.ilMatchMemoPair.php");
			foreach ($copy_mpl as $mpl)
			{
				$result = $ilDB->queryF("SELECT * FROM rep_robj_xmpl_pair WHERE obj_fi = %s AND original_id IS NULL AND tstamp > %s",
					array('integer', 'integer'),
					array($mpl, 0)
				);
				while ($row = $ilDB->fetchAssoc($result))
				{
					$obj = new ilMatchMemoPair($row['title'], $row['author'], $row['description'], ilRTE::_replaceMediaObjectImageSrc($row['card1'], 1), 
						ilRTE::_replaceMediaObjectImageSrc($row['card2'], 1), ilRTE::_replaceMediaObjectImageSrc($row['solution'], 1), 
						$row['created'], $row['updated'], $row['pair_id'], $row['obj_fi']
					);
					$new_id = $obj->duplicateForMatchMemo();
					$next_id = $ilDB->nextId('rep_robj_xmry_pair');
					$affectedRows = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_pair (mry_pair_id, obj_fi, pair_fi) VALUES (%s, %s, %s)",
						array('integer', 'integer', 'integer'),
						array($next_id, $this->getId(), $new_id)
					);
				}
			}
		}
	}

	/**
	* Delete data from db
	* This method is called, when a repository object is finally deleted from the system.
	* It is not called if an object is moved to the trash.
	*/
	function doDelete()
	{
		global $ilDB;
		// $myID = $this->getId();
		$this->deleteThemesAndData();
	}
	
	/**
	* Do Cloning
	* This method is called, when a repository object is copied.
	*/
	function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$this->cloneMetaData($new_obj);

		$new_obj->back_url = $this->back_url;
		$new_obj->heading = $this->heading;
		$new_obj->background = $this->background;
		$new_obj->fullscreen = $this->fullscreen;
		$new_obj->show_title = $this->show_title;
		$new_obj->highscore_single = $this->highscore_single;
		$new_obj->intro = $this->intro;
		$new_obj->themes = $this->themes;
		$new_obj->doUpdate();

		return $new_obj;

	}

	public function highScoresExist()
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT high_id FROM rep_robj_xmry_high WHERE obj_fi = %s",
			array("integer"),
			array($this->getId())
		);
		return $result->numRows() > 0;
	}

	public function saveHighScore($moves, $startingtime, $endingtime, $level, $topic, $cards, $nickname = null)
	{
		global $ilDB;

		$savehighscore = true;
		if ($nickname != null && $this->highscore_single)
		{
			$result = $ilDB->queryF("SELECT * FROM rep_robj_xmry_high WHERE obj_fi = %s AND nickname = %s AND gamelevel = %s",
				array("integer", 'text', 'integer'),
				array($this->getId(), $nickname, $level)
			);
			if ($result->numRows())
			{
				$row = $ilDB->fetchAssoc($result);
				if ($row['moves'] > $moves)
				{
					$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_high WHERE obj_fi = %s AND nickname = %s AND gamelevel = %s",
						array("integer", 'text', 'integer'),
						array($this->getId(), $nickname, $level)
					);
				}
				else
				{
					$savehighscore = false;
				}
			}
		}
		
		if ($savehighscore)
		{
			$next_id = $ilDB->nextId('rep_robj_xmry_high');
			$result = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_high (high_id, obj_fi, moves, time_start, time_end, gamelevel, cards, theme_fi, nickname) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','integer','integer','integer','integer','integer','text'),
				array(
					$next_id,
					$this->getId(),
					$moves,
					$startingtime,
					$endingtime,
					$level,
					$cards,
					$topic,
					$nickname
				)
			);
		}
	}
	
	public function deleteAllData()
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_high WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
	}

	public function deleteSelectedData($ids)
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_high WHERE obj_fi = %s AND " . $ilDB->in('high_id', $ids, false, 'integer'),
			array('integer'),
			array($this->getId())
		);
	}

	public function getMaintenanceData($ids = null)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT *, (time_end-time_start) time_total FROM rep_robj_xmry_high WHERE obj_fi = %s ORDER BY moves, time_total DESC",
			array("integer"),
			array($this->getId())
		);
		$rows = array();
		$counter = 1;
		$t = array();
		foreach ($this->themes as $theme)
		{
			$t[$theme->id] = $theme;
		}
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (((!is_null($ids)) && in_array($row['high_id'], $ids)) || is_null($ids))
			{
				array_push($rows, array(
					'id' => $row['high_id'],
					'rank' => $counter,
					'moves' => $row['moves'],
					'nickname' => $row['nickname'],
					'cards' => $row['cards'],
					'time' => $row['time_total'],
					'topic' => $t[$row['theme_fi']]->title,
					'level' => $row['gamelevel']
				));
				$counter++;
			}
		}
		return $rows;
	}

	public function getRank($moves, $level)
	{
		global $ilDB;
		
		$result = $ilDB->queryF(
			"SELECT *, (time_end-time_start) time_total
			FROM rep_robj_xmry_high
			INNER JOIN rep_robj_xmry_themes ON rep_robj_xmry_themes.theme_id = theme_fi
			WHERE rep_robj_xmry_high.obj_fi = %s AND gamelevel = %s AND moves < %s
			ORDER BY rep_robj_xmry_themes.title ASC, cards ASC, moves ASC, (time_end - time_start) ASC, nickname ASC",
			array("integer", "integer", "integer"),
			array($this->getId(), $level, $moves)
		);
		return $result->numRows() + 1;
	}

	public function getHighScores($level)
	{
		global $ilDB;
		
		$result = $ilDB->queryF(
			"SELECT *, (time_end-time_start) time_total
			FROM rep_robj_xmry_high
			INNER JOIN rep_robj_xmry_themes ON rep_robj_xmry_themes.theme_id = theme_fi
			WHERE rep_robj_xmry_high.obj_fi = %s AND gamelevel = %s
			ORDER BY rep_robj_xmry_themes.title ASC, cards ASC, moves ASC, (time_end - time_start) ASC, nickname ASC",
			array("integer", "integer"),
			array($this->getId(), $level)
		);
		$rows = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($rows, $row);
		}
		return $rows;
	}

	/**
	* synchronises appearances of media objects in the pair object with media
	* object usage table
	*/
	protected function cleanupMediaObjectUsage()
	{
		$combinedtext = $this->intro;
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($combinedtext, "xmry:html", $this->getId());
	}

	protected function deleteThemesAndData()
	{
		global $ilDB;
		
		// delete all mixed pools
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_tmixed WHERE theme_fi IN (SELECT theme_id FROM rep_robj_xmry_themes WHERE obj_fi = %s)",
			array('integer'),
			array($this->getId())
		);
		
		// delete all themes
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_themes WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);

		// delete all pair duplicates
		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmpl_pair WHERE pair_id IN (SELECT pair_fi FROM rep_robj_xmry_pair WHERE obj_fi = %s)",
			array('integer'),
			array($this->getId())
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_pair WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM rep_robj_xmry_themes WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);

	}

	public static function _getConfigurationValue($key)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting("xmry");
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
		$setting = new ilSetting("xmry");
		$setting->set($key, $value);
	}

	public function flushThemes()
	{
		$this->_themes = array();
	}
	
	public function addTheme($title = '', $easy = null, $medium = null, $hard = null, $theme_id = null)
	{
		$this->plugin->includeClass("class.ilMatchMemoTheme.php");
		$theme = new ilMatchMemoTheme($title, $easy, $medium, $hard, $theme_id);
		if (!is_array($this->_themes)) $this->_themes = array();
		array_push($this->_themes, $theme);
	}
	
	public function addThemeObject($theme)
	{
		if (!is_array($this->_themes)) $this->_themes = array();
		array_push($this->_themes, $theme);
	}

	public function insertTheme($position, $title = '', $easy = null, $medium = null, $hard = null)
	{
		$this->plugin->includeClass("class.ilMatchMemoTheme.php");
		$theme = new ilMatchMemoTheme($title, $easy, $medium, $hard);
		if (array_key_exists($position, $this->_themes))
		{
			$newthemes = array();
			for ($i = 0; $i < $position; $i++)
			{
				array_push($newthemes, $this->_themes[$i]);
			}
			array_push($newthemes, $theme);
			for ($i = $position; $i < count($this->_themes); $i++)
			{
				array_push($newthemes, $this->_themes[$i]);
			}
			$this->_themes = $newthemes;
		}
		else
		{
			$this->addTheme($title, $easy, $medium, $hard);
		}
	}
	
	public function removeTheme($position)
	{
		if (array_key_exists($position, $this->_themes))
		{
			unset($this->_themes[$position]);
			$this->_themes = array_values($this->_themes);
		}
	}

	/**
	* Returns the image path for web accessable images of a memory.
	*/
	public function getImagePath()
	{
		return CLIENT_WEB_DIR . "/memory/" . $this->getId() . "/";
	}
	
	/**
	* Returns the web image path for web accessable images of a memory.
	*/
	public function getImagePathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/memory/" . $this->getId() . "/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	function createNewImageFileName($image_filename)
	{
		$extension = "";
		if (preg_match("/.*\.(png|jpg|gif|jpeg)$/i", $image_filename, $matches))
		{
			$extension = "." . $matches[1];
		}
		$image_filename = md5(time()) . $extension;
		return $image_filename;
	}
	
	public function getThumbPrefix()
	{
		return 'thumb_';
	}
	
	public function deleteBackground()
	{
		ilUtil::delDir($this->getImagePath());
	}

	protected function generateThumbForFile($path, $file)
	{
		$filename = $path . $file;
		if (@file_exists($filename))
		{
			$thumbpath = $path . $this->getThumbPrefix() . $file;
			$path_info = @pathinfo($filename);
			$ext = "";
			switch (strtoupper($path_info['extension']))
			{
				case 'PNG':
					$ext = 'PNG';
					break;
				case 'GIF':
					$ext = 'GIF';
					break;
				default:
					$ext = 'JPEG';
					break;
			}
			ilUtil::convertImage($filename, $thumbpath, $ext, '250x');
		}
	}

	/**
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param array $imagedata Image upload data
	* @return string The image filename of the uploaded file
	* @access public
	*/
	function setImageFile($imagedata)
	{
		$result = false;
		if (strlen($imagedata['tmp_name']))
		{
			$this->deleteBackground();
			$image_filename = $this->createNewImageFileName($imagedata['name']);
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			if (!ilUtil::moveUploadedFile($imagedata['tmp_name'], $image_filename, $imagepath.$image_filename))
			{
				$result = false;
			}
			else
			{
				include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mimetype = ilObjMediaObject::getMimeType($imagepath . $image_filename);
				if (!preg_match("/^image/", $mimetype))
				{
					unlink($imagepath . $image_filename);
					$result = false;
				}
				else
				{
					$this->generateThumbForFile($this->getImagePath(), $image_filename);
					$result = $image_filename;
				}
			}
		}
		return $result;
	}
	
	public function __get($value)
	{
		switch ($value)
		{
			case 'themes':
				return $this->_themes;
				break;
			case 'fullscreen':
			case 'show_title':
			case 'highscore_single':
				return ($this->arrData[$value]) ? true : false;
				break;
			case 'back_url':
			case 'background':
			case 'heading':
			case 'intro':
				return $this->arrData[$value];
				break;
			case 'complete':
				return (count($this->themes)) ? true : false;
				break;
		}
	}
	
	public function __set($key, $value)
	{
		switch ($key)
		{
			case 'themes':
				$this->_themes = $value;
				break;
			case 'fullscreen':
			case 'show_title':
			case 'highscore_single':
				$this->arrData[$key] = ($value) ? 1 : 0;
				break;
			case 'back_url':
			case 'heading':
			case 'background':
			case 'intro':
				$this->arrData[$key] = $value;
				break;
		}
	}

	/**
	* Checks if a given string contains HTML or not
	*
	* @param string $a_text Text which should be checked
	* @return boolean 
	* @access public
	*/
	function isHTML($a_text)
	{
		if (preg_match("/<[^>]*?>/", $a_text))
		{
			return TRUE;
		}
		else
		{
			return FALSE; 
		}
	}
}
?>
