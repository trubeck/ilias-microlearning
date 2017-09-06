<?php
/**
 * Created by IntelliJ IDEA.
 * User: trutz
 * Date: 19.07.17
 * Time: 09:24
 */

class ilNuggetRecommender
{
    function __construct()
    {
        global $ilDB, $ilUser;

    }

    function getNuggetIDs()
    {
        global $ilDB, $ilUser;

        //$user_id = $ilUser->getId();
        $type = "xpal";
        $result = $ilDB->query("SELECT obj_id FROM object_data WHERE type = ".$ilDB->quote($type, "text") );

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