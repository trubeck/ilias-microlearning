<?php

// save this file as hello_world.php in the ILIAS main directory

global $ilDB, $ilToolbar;
include ("./include/inc.header.php");

$type = "xpal";
$id = 287;
//$result = $ilDB->getDBVersion();
//$result = $ilDB->query("SELECT * FROM object_data WHERE type = ".$ilDB->quote($type, "text"));
//$result = $ilDB->query("SELECT * FROM object_reference WHERE obj_id = ".$ilDB->quote($id, "integer"));
//$result = $ilDB->query("SELECT * FROM il_meta_meta_data WHERE obj_id = ".$ilDB->quote($id, "integer"));
//$result = $ilDB->query("SELECT lo_a FROM il_meta_keyword WHERE obj_id = ".$ilDB->quote($id, "integer"));
$result = $ilDB->query("SELECT * FROM copg_pobj_def ");
$data = $ilDB->fetchAssoc($result);
//$data = $ilDB->fetchObject($result);
//while (($row = $ilDB->fetchArray($result)))
//{
//    $data.= $row["type"] . "\n";
//}

var_dump($data);
foreach($data as $key => $value){
	echo($key." ".$value."<br>");
}


$parse = implode(",", $data);
//$entry = $data["ref_id"];
$tpl->getStandardTemplate();
$tpl->setContent($parse);
$tpl->show();
?>

_____________
create table il_meta_situation_model (meta_situation_model_id int(11), rbac_id int(11), obj_id int(11), obj_type varchar(6), competency_level int(1), previous int(11), next int(11));

alter table il_meta_situation_model add primary key (meta_situation_model_id);

alter table il_meta_situation_model change meta_situation_model_id meta_situation_model_id int(11) not null auto_increment;

create index i1_idx on il_meta_situation_model (rbac_id, obj_id);

create table il_meta_situation_model_seq (sequence int(11));

alter table il_meta_situation_model_seq add primary key (sequence);

alter table il_meta_situation_model_seq change sequence sequence int(11) not null auto_increment;

_____________
create table il_meta_responsible (meta_responsible_id int(11), rbac_id int(11), obj_id int(11), obj_type varchar(6), parent_type varchar(36), parent_id int(11), responsible_role varchar(100));

alter table il_meta_responsible add primary key (meta_responsible_id);

alter table il_meta_responsible change meta_responsible_id meta_responsible_id int(11) not null auto_increment;

create index i1_idx on il_meta_responsible (rbac_id, obj_id);

create table il_meta_responsible_seq (sequence int(11));

alter table il_meta_responsible_seq add primary key (sequence);

alter table il_meta_responsible_seq change sequence sequence int(11) not null auto_increment;

_______________
create table il_meta_stakeholder (meta_stakeholder_id int(11), rbac_id int(11), obj_id int(11), obj_type varchar(6), parent_type varchar(36), parent_id int(11), responsible_role varchar(100));

alter table il_meta_stakeholder add primary key (meta_stakeholder_id);

alter table il_meta_stakeholder change meta_stakeholder_id meta_stakeholder_id int(11) not null auto_increment;

create index i1_idx on il_meta_stakeholder (rbac_id, obj_id);

create table il_meta_stakeholder_seq (sequence int(11));

alter table il_meta_stakeholder_seq add primary key (sequence);

alter table il_meta_stakeholder_seq change sequence sequence int(11) not null auto_increment;
