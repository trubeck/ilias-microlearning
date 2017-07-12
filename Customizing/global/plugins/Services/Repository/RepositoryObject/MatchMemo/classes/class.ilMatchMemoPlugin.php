<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * Match & Memo game repository object plugin
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 */
class ilMatchMemoPlugin extends ilRepositoryObjectPlugin
{
	/**
	 * @var string
	 */
	const CTYPE = 'Services';

	/**
	 * @var string
	 */
	const CNAME = 'Repository';

	/**
	 * @var string
	 */
	const SLOT_ID = 'robj';

	/**
	 * @var string
	 */
	const PNAME = 'MatchMemo';

	/**
	 * @return string
	 */
	public function getPluginName()
	{
		return self::PNAME;
	}

	/**
	 * @var ilMatchMemoPlugin|null
	 */
	private static $instance = null;

	/**
	 * @return ilMatchMemoPlugin
	 */
	public static function getInstance()
	{
		if(null === self::$instance)
		{
			require_once 'Services/Component/classes/class.ilPluginAdmin.php';
			return self::$instance = ilPluginAdmin::getPluginObject(
				self::CTYPE,
				self::CNAME,
				self::SLOT_ID,
				self::PNAME
			);
		}

		return self::$instance;
	}

	protected function uninstallCustom()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if($ilDB->tableExists('rep_robj_xmry_themes'))
		{
			$ilDB->dropTable('rep_robj_xmry_themes');
		}
		if($ilDB->sequenceExists('rep_robj_xmry_themes'))
		{
			$ilDB->dropSequence('rep_robj_xmry_themes');
		}

		if($ilDB->tableExists('rep_robj_xmry_pair'))
		{
			$ilDB->dropTable('rep_robj_xmry_pair');
		}
		if($ilDB->sequenceExists('rep_robj_xmry_pair'))
		{
			$ilDB->dropSequence('rep_robj_xmry_pair');
		}

		if($ilDB->tableExists('rep_robj_xmry'))
		{
			$ilDB->dropTable('rep_robj_xmry');
		}
		if($ilDB->sequenceExists('rep_robj_xmry'))
		{
			$ilDB->dropSequence('rep_robj_xmry');
		}

		if($ilDB->tableExists('rep_robj_xmry_high'))
		{
			$ilDB->dropTable('rep_robj_xmry_high');
		}
		if($ilDB->sequenceExists('rep_robj_xmry_high'))
		{
			$ilDB->dropSequence('rep_robj_xmry_high');
		}

		if($ilDB->tableExists('rep_robj_xmry_tmixed'))
		{
			$ilDB->dropTable('rep_robj_xmry_tmixed');
		}
		if($ilDB->sequenceExists('rep_robj_xmry_tmixed'))
		{
			$ilDB->dropSequence('rep_robj_xmry_tmixed');
		}
	}
}
