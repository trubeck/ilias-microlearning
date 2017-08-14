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

    function getVisitedNuggets()
    {
        global $ilDB, $ilUser;

        //$user_id = $ilUser->getId();
        $type = "mcst";
        $result = $ilDB->query("SELECT title FROM object_data WHERE type = ".$ilDB->quote($type, "text") );

        $entry = "";
        while($data = $ilDB->fetchAssoc($result))
        {
            $entry .= $data["title"] . ",";
        }

        $entry = explode(",", substr($entry, 0, -1));


        return $entry;

    }

    function recommend()
    {
        $titles = $this->getVisitedNuggets();

        $result = array();

        if(count($titles) <= 3)
        {
            $result = $titles;
        }

        else
        {
            while(count($result) != 3)
            {
                $random = mt_rand(0, count($titles));
                $result[] = $titles[$random];
                unset($titles[$random]);
                $titles = array_values($titles);
            }


        }

        return $result;


    }
}