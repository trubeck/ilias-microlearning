<#1>
<?php

if (!$ilDB->tableExists('rep_robj_xmry_themes'))
{
	$ilDB->createTable("rep_robj_xmry_themes", array(
		'theme_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'title' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'pool_easy' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'pool_medium' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'pool_hard' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'pools_mixed' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmry_themes", array("theme_id"));
	$ilDB->addIndex('rep_robj_xmry_themes',array('obj_fi'),'i1');
	$ilDB->createSequence("rep_robj_xmry_themes");
}

?>
<#2>
<?php

if (!$ilDB->tableExists('rep_robj_xmry_pair'))
{
	$ilDB->createTable("rep_robj_xmry_pair", array(
		'mry_pair_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'pair_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmry_pair", array("mry_pair_id"));
	$ilDB->addIndex('rep_robj_xmry_pair',array('obj_fi'),'i1');
	$ilDB->addIndex('rep_robj_xmry_pair',array('pair_fi'),'i2');
	$ilDB->createSequence("rep_robj_xmry_pair");
}

?>
<#3>
<?php

if (!$ilDB->tableExists('rep_robj_xmry'))
{
	$ilDB->createTable("rep_robj_xmry", array(
		'mry_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'heading' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'intro' => array(
			'type'     => 'text',
			'length'   => 4000,
			'notnull' => false
		),
		'back_url' => array(
			'type'     => 'text',
			'length'   => 1000,
			'notnull' => false
		),
		'background' => array(
			'type'     => 'text',
			'length'   => 255,
			'notnull' => false
		),
		'fullscreen' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		),
		'created' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		),
		'updated' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true,
			'default' => 0
		),
		'show_title' => array(
			'type' => 'integer',
			'length'   => 2,
			'notnull' => true,
			"default" => 0
		),
		'highscore_single' => array(
			'type' => 'integer',
			'length'   => 2,
			'notnull' => true,
			"default" => 0
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmry", array("mry_id"));
	$ilDB->addIndex('rep_robj_xmry',array('obj_fi'),'i1');
	$ilDB->createSequence("rep_robj_xmry");
}

?>
<#4>
<?php

if (!$ilDB->tableExists('rep_robj_xmry_high'))
{
	$ilDB->createTable("rep_robj_xmry_high", array(
		'high_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'theme_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'moves' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'gamelevel' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'cards' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'time_start' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'time_end' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'nickname' => array(
			'type'     => 'text',
			'length'   => 30,
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmry_high", array("high_id"));
	$ilDB->addIndex('rep_robj_xmry_high',array('obj_fi'),'i1');
	$ilDB->addIndex('rep_robj_xmry_high',array('theme_fi'),'i2');
	$ilDB->createSequence("rep_robj_xmry_high");
}

?>
<#5>
<?php

if (!$ilDB->tableExists('rep_robj_xmry_tmixed'))
{
	$ilDB->createTable("rep_robj_xmry_tmixed", array(
		'mry_themes_mixed_id' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'theme_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'obj_fi' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		),
		'percent' => array(
			'type'     => 'float',
			'notnull' => true 
		),
		'sequence' => array(
			'type'     => 'integer',
			'length'   => 4,
			'notnull' => true 
		)
	));
	$ilDB->addPrimaryKey("rep_robj_xmry_tmixed", array("mry_themes_mixed_id"));
	$ilDB->addIndex('rep_robj_xmry_tmixed',array('theme_fi'),'i1');
	$ilDB->addIndex('rep_robj_xmry_tmixed',array('obj_fi'),'i2');
	$ilDB->createSequence("rep_robj_xmry_tmixed");
}

?>
<#6>
<?php

if ($ilDB->tableExists('mpl'))
{
	// convert previous existing data

	// clone mry
	$result = $ilDB->query("SELECT * FROM mry");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry (mry_id, obj_fi, back_url, background, fullscreen, heading, intro, show_title, highscore_single, created, updated) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','text','text','text','text','text','integer','integer','integer','integer'),
				array(
					$row['mry_id'],
					$row['obj_fi'],
					$row['back_url'],
					$row['background'],
					$row['fullscreen'],
					$row['heading'],
					$row['intro'],
					$row['show_title'],
					$row['highscore_single'],
					$row['created'],
					$row['updated']
				)
			);
			if ($row['mry_id'] > $max_index) $max_index = $row['mry_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmry_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// clone mry_high
	$result = $ilDB->query("SELECT * FROM mry_high");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_high (high_id, obj_fi, moves, time_start, time_end, gamelevel, cards, theme_fi, nickname) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','integer','integer','integer','integer','integer','text'),
				array(
					$row['high_id'],
					$row['obj_fi'],
					$row['moves'],
					$row['time_start'],
					$row['time_end'],
					$row['gamelevel'],
					$row['cards'],
					$row['theme_fi'],
					$row['nickname']
				)
			);
			if ($row['high_id'] > $max_index) $max_index = $row['high_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmry_high_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_high_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// clone mry_pair
	$result = $ilDB->query("SELECT * FROM mry_pair");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_pair (mry_pair_id, obj_fi, pair_fi) VALUES (%s, %s, %s)",
				array('integer', 'integer', 'integer'),
				array($row['mry_pair_id'], $row['obj_fi'], $row['pair_fi'])
			);
			if ($row['mry_pair_id'] > $max_index) $max_index = $row['mry_pair_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmry_pair_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_pair_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// clone mry_themes
	$result = $ilDB->query("SELECT * FROM mry_themes");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_themes (theme_id, obj_fi, title, pool_easy, pool_medium, pool_hard, pools_mixed) VALUES (%s, %s, %s, %s, %s, %s, %s)",
				array('integer', 'integer', 'text', 'integer','integer','integer','integer'),
				array($row['theme_id'], $row['obj_fi'], $row['title'], $row['pool_easy'], $row['pool_medium'], $row['pool_hard'], $row['pools_mixed'])
			);
			if ($row['theme_id'] > $max_index) $max_index = $row['theme_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmry_themes_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_themes_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// clone mry_themes_mixed
	$result = $ilDB->query("SELECT * FROM mry_themes_mixed");
	if ($result->numRows())
	{
		$max_index = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_tmixed (mry_themes_mixed_id, theme_fi, obj_fi, percent, sequence) VALUES (%s, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'float','integer'),
				array($row['mry_themes_mixed_id'], $row['theme_fi'], $row['obj_fi'], (float)$row['percent'], (int)$row['sequence'])
			);
			if ($row['mry_themes_mixed_id'] > $max_index) $max_index = $row['mry_themes_mixed_id'];
		}
		$res = $ilDB->manipulate("DELETE FROM rep_robj_xmry_tmixed_seq");
		$res = $ilDB->manipulateF("INSERT INTO rep_robj_xmry_tmixed_seq (sequence) VALUES (%s)",
			array('integer'),
			array($max_index)
		);
	}

	// change object types
	$result = $ilDB->manipulateF("UPDATE object_data SET type = %s WHERE type = %s",
		array('text', 'text'),
		array('xmry', 'mry')
	);

	$ilDB->dropTable('mry', false);
	$ilDB->dropTable('mry_high', false);
	$ilDB->dropTable('mry_pair', false);
	$ilDB->dropTable('mry_themes', false);
	$ilDB->dropTable('mry_themes_mixed', false);
}

?>
<#7>
<?php
if($ilDB->tableColumnExists('rep_robj_xmry_tmixed', 'percent'))
{
	$ilDB->modifyTableColumn('rep_robj_xmry_tmixed', 'percent', array(
		'type'     => 'float',
		'default'  => null,
		'notnull'  => false
	));
}
?>