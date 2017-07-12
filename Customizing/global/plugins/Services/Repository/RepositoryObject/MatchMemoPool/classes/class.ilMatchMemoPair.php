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
* Class ilMatchMemoPair
*
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version $Id: class.ilMatchMemoPair.php 23022 2010-02-21 22:11:44Z hschottm $
*
* @ingroup ModulesMatchMemoPool
*/

class ilMatchMemoPair
{
	protected $plugin;
	protected $arrData;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($title = '', $author = '', $description = '', $card1 = '', $card2 = '', $solution = '', $created = 0, $updated = 0, $id = 0, $mpl = 0)
	{
		global $ilUser;
		$author = (strlen($author)) ? $author : $ilUser->getFullname();
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
		$this->arrData = array(
			'id' => $id,
			'mpl' => $mpl,
			'title' => $title,
			'author' => $author,
			'description' => $description,
			'card1' => $card1,
			'card2' => $card2,
			'solution' => $solution,
			'created' => $created,
			'updated' => $updated
		);
	}
	
	public static function _duplicate($id, $mpl)
	{
		$source = ilMatchMemoPair::_loadFromDB($id);
		if($source)
		{
			$source->copyToPool($mpl);
		}
	}
	
	public static function _loadFromDB($id)
	{
		global $ilDB;
		$obj = null;
		$result = $ilDB->queryF("SELECT * FROM rep_robj_xmpl_pair WHERE pair_id = %s",
			array("integer"),
			array($id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			include_once "./Services/Component/classes/class.ilPlugin.php";
			$plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
			$plugin->includeClass("class.ilMatchMemoPair.php");
			$obj = new ilMatchMemoPair($row['title'], $row['author'], $row['description'], ilRTE::_replaceMediaObjectImageSrc($row['card1'], 1), 
				ilRTE::_replaceMediaObjectImageSrc($row['card2'], 1), ilRTE::_replaceMediaObjectImageSrc($row['solution'], 1), 
				$row['created'], $row['updated'], $row['pair_id'], $row['obj_fi']
			);
		}
		return $obj;
	}

	public function copyToPool($mpl)
	{
		global $ilDB, $ilUser;
		$next_id = $ilDB->nextId('rep_robj_xmpl_pair');
		$result = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_pair (pair_id, obj_fi, owner, title, author, description, card1, card2, solution, created, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','text','text','text','text','text','text','integer','integer'),
			array(
				$next_id,
				$mpl,
				$ilUser->getId(),
				$this->title,
				$this->author,
				$this->description,
				ilRTE::_replaceMediaObjectImageSrc($this->card1, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->card2, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->solution, 0),
				time(),
				time()
			)
		);
		$res = $next_id;
		ilObjMatchMemoPool::_updatePairCount($mpl);
		return $res;
	}
	
	protected function createEmptyPair()
	{
		global $ilDB, $ilUser;
		$next_id = $ilDB->nextId('rep_robj_xmpl_pair');
		$result = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_pair (pair_id, obj_fi, owner, title, author, description, card1, card2, solution, created, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','text','text','text','text','text','text','integer','integer'),
			array(
				$next_id,
				$this->mpl,
				$ilUser->getId(),
				null,
				null,
				null,
				null,
				null,
				null,
				time(),
				0
			)
		);
		return $next_id;
	}
	
	public function duplicateForMatchMemo()
	{
		global $ilDB, $ilUser;
		$new_id = $this->createEmptyPair();
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$result = $ilDB->manipulateF("UPDATE rep_robj_xmpl_pair SET title = %s, author = %s, description = %s, card1 = %s, card2 = %s, solution = %s, original_id = %s, tstamp = %s WHERE pair_id = %s",
			array('text','text','text','text','text','text','integer','integer','integer'),
			array(
				$this->title,
				$this->author,
				$this->description,
				ilRTE::_replaceMediaObjectImageSrc($this->card1, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->card2, 0),
				ilRTE::_replaceMediaObjectImageSrc($this->solution, 0),
				$this->id,
				time(),
				$new_id
			)
		);
		return $new_id;
	}
	
	public function __get($parameter)
	{
		switch ($parameter)
		{
			case 'id':
			case 'mpl':
			case 'title':
			case 'description':
			case 'author':
			case 'card1':
			case 'card2':
			case 'solution':
			case 'created':
			case 'updated':
				return $this->arrData[$parameter];
				break;
		}
	}
	
	public function __set($parameter, $value)
	{
		switch ($parameter)
		{
			case 'id':
			case 'mpl':
			case 'title':
			case 'description':
			case 'author':
			case 'card1':
			case 'card2':
			case 'solution':
			case 'created':
			case 'updated':
				$this->arrData[$parameter] = $value;
				break;
		}
	}
}
?>
