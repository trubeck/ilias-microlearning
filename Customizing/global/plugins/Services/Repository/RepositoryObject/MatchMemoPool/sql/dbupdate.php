<#1>
<?php
if (!$ilDB->tableExists('rep_robj_xmpl_object'))
{
	$ilDB->createTable("rep_robj_xmpl_object", array(
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'isonline' => array(
			'type'     => 'text',
			'length'   => 1,
			'notnull' => false,
			'default' => '0'
		),
		'paircount' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		),
		'tstamp' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addIndex('rep_robj_xmpl_object',array('obj_fi'),'i1');
}

?>
<#2>
<?php

if (!$ilDB->tableExists('rep_robj_xmpl_pair'))
{
	$ilDB->createTable("rep_robj_xmpl_pair", array(
		'pair_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'owner' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'original_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => false 
		),
		'title' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'author' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'description' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'card1' => array(
			'type'     => 'text',
			'length'   => 4000,
			'notnull' => false
		),
		'card2' => array(
			'type'     => 'text',
			'length'   => 4000,
			'notnull' => false
		),
		'solution' => array(
			'type'     => 'text',
			'length'   => 4000,
			'notnull' => false
		),
		'created' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		),
		'tstamp' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmpl_pair", array("pair_id"));
	$ilDB->addIndex('rep_robj_xmpl_pair',array('obj_fi'),'i1');
	$ilDB->addIndex('rep_robj_xmpl_pair',array('original_id'),'i2');
	$ilDB->createSequence("rep_robj_xmpl_pair");
}

?>

<#3>
<?php

if($ilDB->tableExists('mpl'))
{
	$result = $ilDB->query("SELECT * FROM mpl");
	if ($result->numRows())
	{
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_object (obj_fi, isonline, paircount, tstamp) VALUES (%s, %s, %s, %s)",
				array('integer','integer','integer','integer'),
				array($row['obj_fi'], $row['isonline'], $row['paircount'], $row['tstamp'])
			);
		}
	}

	$result = $ilDB->query("SELECT * FROM mpl_pair");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_pair (pair_id, obj_fi, owner, title, author, description, card1, card2, solution, created, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','text','text','text','text','text','text','integer','integer'),
				array(
					$row['pair_id'],
					$row['obj_fi'],
					$row['owner'],
					$row['title'],
					$row['author'],
					$row['description'],
					$row['card1'],
					$row['card2'],
					$row['solution'],
					$row['created'],
					$row['tstamp']
				)
			);
			if ($row['pair_id'] > $max_index) $max_index = $row['pair_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmpl_pair_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmpl_pair_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// change object types
	$result = $ilDB->manipulateF("UPDATE object_data SET type = %s WHERE type = %s",
		array('text', 'text'),
		array('xmpl', 'mpl')
	);
	
	$ilDB->dropTable('mpl', false);
	$ilDB->dropTable('mpl_pair', false);
}

?>