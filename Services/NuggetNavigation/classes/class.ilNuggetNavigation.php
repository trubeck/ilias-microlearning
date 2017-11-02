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
	* Get link to selected nugget.
	*/
	function getLinkToNugget($referenceId)
	{
		ilUtil::sendSuccess("hallo");
		//require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilPalunoObjectPlugin.php");
		$ilCtrl->setParameterByClass("ilobjpalunoobjectgui", "ref_id", $referenceId);
		$link = $this->ctrl->getLinkTargetByClass('ilobjpalunoobjectgui', 'showContent');

		return $link;
	}

	/**
	* get previous nugget
	*/
	function getPreviousNugget($obj_id)
	{	
		global $ilDB, $ilLog;

		include_once "Services/Logging/classes/class.ilLog.php";

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$parse = implode(",", $data);
		$ilLog->write("usdgkasgaks ".$data["previous"]);
		if($data == null || $data["previous"] == 0)
		{
			$ilLog->write("null");
			return null;
		}
		
		return $data["previous"];
	}

	/**
	* get next nugget
	*/
	function getNextNugget($obj_id)
	{	
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		if($data == null || $data["next"] == 0)
		{
			return null;
		}
		
		return $data["next"];
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

    function getRefIDFromObjID($objID)
    {
        global $ilDB;

        $result = $ilDB->query("SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($objID, "integer"));

        $data = $ilDB->fetchAssoc($result);

        return $data["ref_id"];
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