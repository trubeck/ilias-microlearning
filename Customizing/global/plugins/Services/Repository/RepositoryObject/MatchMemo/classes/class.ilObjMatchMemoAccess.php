<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/MatchMemo/classes/class.ilMatchMemoPlugin.php");

/**
* Access/Condition checking for Match & Memo object
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*/
class ilObjMatchMemoAccess extends ilObjectPluginAccess
{

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "read":
			case "visible":
				if (!ilObjMatchMemoAccess::_lookupCreationComplete($a_obj_id) &&
					(!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, ilMatchMemoPlugin::getInstance()->txt("mry_warning_not_complete"));
					return false;
				}
				break;
		}

		switch ($a_cmd)
		{
			case "game":
				if (!ilObjMatchMemoAccess::_lookupCreationComplete($a_obj_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, ilMatchMemoPlugin::getInstance()->txt("mry_warning_not_complete"));
					return false;
				}
				break;
		}

		return true;
	}

	public static function _getAvailablePools()
	{
		global $ilDB, $ilUser;

		$availablePools = array();
		$permission = (strlen($permission) == 0) ? "read" : $permission;
		$found = ilUtil::_getObjectsByOperations("xmpl", $permission, $ilUser->getId(), -1);
		$mpls = array();
		$mpl_objs = array();
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$poolplugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
		$poolplugin->includeClass("class.ilObjMatchMemoPoolAccess.php");
		foreach ($found as $ref_id)
		{
			if (ilObjMatchMemoPoolAccess::_lookupOnline($ref_id, true))
			{
				array_push($mpls, $ref_id);
				array_push($mpl_objs, ilObject::_lookupObjId($ref_id));
			}
		}
		if (count($mpls))
		{
			$pools = ilObject::_prepareCloneSelection($mpls, "xmpl");
			foreach ($pools as $ref_id => $title)
			{
				$availablePools[ilObject::_lookupObjId($ref_id)] = $title;
			}
			$result = $ilDB->query("SELECT obj_fi, paircount FROM rep_robj_xmpl_object WHERE " . $ilDB->in('obj_fi', $mpl_objs, false, 'integer'));
			while ($row = $ilDB->fetchAssoc($result))
			{
				// must be 16 because the maximum game size has to be supported!!!
				if ($row['paircount'] < 16) unset($availablePools[$row['obj_fi']]);
			}
			asort($availablePools);
			return $availablePools;
		}
		else
		{
			$availablePools = array();
			return $availablePools;
		}
	}

	/**
	* checks wether all necessary parts of the test are given
	*/
	function _lookupCreationComplete($a_obj_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT theme_id FROM rep_robj_xmry_themes WHERE obj_fi = %s",
			array("integer"),
			array($a_obj_id)
		);
		if ($result->numRows())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>
