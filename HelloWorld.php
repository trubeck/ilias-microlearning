<?php

// save this file as hello_world.php in the ILIAS main directory

global $ilDB;
include ("./include/inc.header.php");

$type = "mcst";
$id = 270;
//$result = $ilDB->getDBVersion();
$result = $ilDB->query("SELECT * FROM object_data WHERE type = ".$ilDB->quote($type, "text"));
//$result = $ilDB->query("SELECT * FROM object_data WHERE obj_id = ".$ilDB->quote($id, "integer"));
//$result = $ilDB->query("SELECT * FROM il_meta_meta_data WHERE obj_id = ".$ilDB->quote($id, "integer"));
//$result = $ilDB->query("SELECT lo_a FROM il_meta_keyword WHERE obj_id = ".$ilDB->quote($id, "integer"));
//$data = $ilDB->fetchAssoc($result);
$data = $ilDB->fetchObject($result);
//while (($row = $ilDB->fetchArray($result)))
//{
//    $data.= $row["type"] . "\n";
//}

$parse = implode(",", $data);
$entry = $data["type"];
$tpl->getStandardTemplate();
$tpl->setContent($entry);
$tpl->show();

?>