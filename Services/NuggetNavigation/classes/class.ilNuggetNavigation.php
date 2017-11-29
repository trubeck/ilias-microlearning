<?php
/**
 * Author: Markus Heikamp
 * Date: 27.10.17
 * Time: 10:24
 */

class ilNuggetNavigation
{
    function __construct()
    {
        global $ilDB, $ilCtrl;

    }

	/**
	* Get Nugget name by object ID.
	*/
	function getNuggetNameByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_data WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["title"];

		return $entry;
	}

	/**
	* Get Ref Id from Exam by object ID.
	*/
	function getRefIdFromExamByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_reference WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$refIdFromNugget = $data["ref_id"];

		return $refIdFromNugget;
	}

	/**
	* Get link to selected nugget.
	*/
	function getLinkToNugget($obj_id)
	{
		global $ilDB;
		
		$referenceId = $this->getRefIdFromExamByObjId($obj_id);
		$result = $ilDB->query("SELECT * FROM object_data WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$nuggetType = $data["type"];

		switch ($nuggetType) {
			case 'xpal':
				$cmdClass = "ilobjpalunoobjectgui";
				//$cmdNode = "jt:jq";
				$cmdNode = "js:jp";
				break;
			case 'xtxt':
				$cmdClass = "ilobjtextnuggetgui";
				//$cmdNode = "jt:lu";
				$cmdNode = "js:lt";
				break;
		}

		$link = "ilias.php?ref_id=".$referenceId."&cmd=showContent&cmdClass=".$cmdClass."&cmdNode=".$cmdNode."&baseClass=ilObjPluginDispatchGUI";

		return $link;
	}

	/**
	* get previous nugget
	*/
	function getPreviousNugget($obj_id)
	{	
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$objIdPrevious = $data["previous"];
		if($this->isObjectDeleted($objIdPrevious) || $data == null || $objIdPrevious == 0)
		{
			return null;
		}
		
		return $objIdPrevious;
	}

	/**
	* get next nugget
	*/
	function getNextNugget($obj_id)
	{	
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$objIdNext = $data["next"];
		if($this->isObjectDeleted($objIdNext) || $data == null || $objIdNext == 0)
		{
			return null;
		}
		
		return $objIdNext;
	}

	/**
	* checks if object is deleted
	*/
	function isObjectDeleted($obj_id)
	{	
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_reference WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		if($data["deleted"] == null)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

		/**
	* checks if object has a previe picture
	*/
	function hasPreviewPicture($obj_id)
	{	
		global $ilDB;

        $type = "mob";
        $result = $ilDB->query("SELECT * FROM object_data WHERE type = ".$ilDB->quote($type, "text"));

        $mobObjIds = "";
        while($data = $ilDB->fetchAssoc($result))
        {
            $mobObjIds .= $data["obj_id"] . ",";
        }

        $mobObjIds = explode(",", substr($mobObjIds, 0, -1));

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		foreach($mobObjIds as $id)
		{
			$mob = new ilObjMediaObject($id);
			if($mob->getImportId() == $obj_id)
			{
				return $mob->getId();
			}
		}

		return 0;
	}

}