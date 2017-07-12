<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilMatchMemoTheme
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id: class.ilMatchMemoTheme.php 23022 2010-02-21 22:11:44Z hschottm $
*
* @ingroup ModulesMatchMemoPool
*/

class ilMatchMemoTheme
{
	protected $plugin;
	protected $arrData;
	protected $availablePools;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($title = '', $easy = null, $medium = null, $hard = null, $theme_id = null)
	{
		$this->arrData = array(
			'title' => $title,
			'easy' => $easy,
			'medium' => $medium,
			'hard' => $hard,
			'mixedpools' => 3,
			'mixed' => array(),
			'id' => $theme_id
		);
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemo");
	}
	
	public function hasMixedPools()
	{
		foreach ($this->arrData['mixed'] as $data)
		{
			if ($data['obj_id']) return true;
		}
		return false;
	}
	
	public function addMixedPool($obj_id, $percent)
	{
		array_push($this->arrData['mixed'], array('obj_id' => $obj_id, 'percent' => $percent));
	}
	
	public static function &_instanciate($id)
	{
		global $ilDB;
		$theme = null;
		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmry_themes WHERE theme_id = %s",
			array("integer"),
			array($id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$theme = new ilMatchMemoTheme($row['title'], $row['pool_easy'], $row['pool_medium'], $row['pool_hard'], $row['theme_id']);
			$theme->mixedpools = ($row['pools_mixed'] > 1) ? $row['pools_mixed'] : 3;
			$mixedresult = $ilDB->queryF("SELECT * FROM rep_robj_xmry_tmixed WHERE theme_fi = %s ORDER BY sequence",
				array("integer"),
				array($id)
			);
			$mixedpools = array();
			while ($mixedrow = $ilDB->fetchAssoc($mixedresult))
			{
				array_push($mixedpools, array('obj_id' => $mixedrow['obj_fi'], 'percent' => $mixedrow['percent']));
			}
			$theme->mixed = $mixedpools;
		}
		return $theme;
	}
	
	public function getAvailablePools()
	{
		if (count($this->availablePools)) return $this->availablePools;
		$this->plugin->includeClass("class.ilObjMatchMemoAccess.php");
		$this->availablePools = ilObjMatchMemoAccess::_getAvailablePools();
		return $this->availablePools;
	}
	
	public function __get($parameter)
	{
		switch ($parameter)
		{
			case 'title':
			case 'easy':
			case 'medium':
			case 'mixed':
			case 'hard':
			case 'mixedpools':
			case 'id':
				return $this->arrData[$parameter];
				break;
		}
	}
	
	public function __set($parameter, $value)
	{
		switch ($parameter)
		{
			case 'title':
			case 'easy':
			case 'medium':
			case 'hard':
			case 'mixed':
			case 'mixedpools':
			case 'id':
				$this->arrData[$parameter] = $value;
				break;
		}
	}
}
?>
