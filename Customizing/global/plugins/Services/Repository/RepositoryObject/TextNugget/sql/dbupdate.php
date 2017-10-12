<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'option_one' => array(
		'type' => 'text',
		'length' => 10,
		'fixed' => false,
		'notnull' => false
	)
);

if(!$ilDB->tableExists("rep_robj_xtxt_data")) {
	$ilDB->createTable("rep_robj_xtxt_data", $fields);
	$ilDB->addPrimaryKey("rep_robj_xtxt_data", array("id"));
}
?>