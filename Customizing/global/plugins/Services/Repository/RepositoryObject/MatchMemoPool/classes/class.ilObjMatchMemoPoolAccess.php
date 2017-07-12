<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
* Access/Condition checking for MatchMemoPool object
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*/
class ilObjMatchMemoPoolAccess extends ilObjectPluginAccess
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
				if (!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
				#	return false;
				}
				break;
		}

		return true;
	}
	
	public static function _lookupOnline($a_obj_id, $is_reference = false)
	{
		global $ilDB;

		if ($is_reference) $a_obj_id = ilObject::_lookupObjId($a_obj_id);
		$result = $ilDB->queryF("SELECT isonline FROM rep_robj_xmpl_object WHERE obj_fi = %s",
			array("integer"),
			array($a_obj_id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["isonline"];
		}
		return 0;
	}
}

?>
