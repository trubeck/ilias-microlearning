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
				$cmdNode = "jt:jq";
				break;
			case 'xtxt':
				$cmdClass = "ilobjtextnuggetgui";
				$cmdNode = "jt:lu";
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

    function getNuggetIDs()
    {
        global $ilDB, $ilUser;

        //$user_id = $ilUser->getId();
        $type = "xpal";
        $result = $ilDB->query("SELECT DISTINCT obj_id FROM object_data NATURAL JOIN object_reference WHERE type = ".$ilDB->quote($type, "text")  . "AND deleted IS NULL");

        $entry = "";
        while($data = $ilDB->fetchAssoc($result))
        {
            $entry .= $data["obj_id"] . ",";
        }

        $entry = explode(",", substr($entry, 0, -1));


        return $entry;

    }

    function getTitleByID($id)
    {
        global $ilDB;

        $result = $ilDB->query("SELECT title FROM object_data WHERE obj_id = ".$ilDB->quote($id, "integer"));

        $data = $ilDB->fetchAssoc($result);

        return $data["title"];

    }

    function getRandom($count)
    {
        $ids = $this->getNuggetIDs();

        $result = array();

        if(count($ids) <= $count)
        {
            $result = $ids;
        }

        else
        {
            while(count($result) != $count)
            {
                $random = mt_rand(0, count($ids)-1);
                $result[] = $ids[$random];
                unset($ids[$random]);
                $ids = array_values($ids);
            }


        }

        return $result;


    }

    function recommend($count)
    {
        $result = array();
        $objIDs = $this->getRandom($count);


        for($i = 0; $i<count($objIDs); $i++)
        {
            $result[$this->getRefIDFromObjID($objIDs[$i])] = $this->getTitleByID($objIDs[$i]);
        }


        return $result;

    }


}