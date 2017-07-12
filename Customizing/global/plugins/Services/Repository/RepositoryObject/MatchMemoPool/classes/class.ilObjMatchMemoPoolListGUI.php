<?php


include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";
include_once dirname(__FILE__) . '/class.ilObjMatchMemoPool.php';

/**
* ListGUI implementation for Gallery object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*/
class ilObjMatchMemoPoolListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	function initType()
	{
		$this->setType("xmpl");
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
		return "ilObjMatchMemoPoolGUI";
	}
	
	/**
	* Get commands
	*/
	function initCommands()
	{
		return array
		(
			array(
				'permission' => 'read',
				'cmd'        => 'infoScreen',
				'default'    => true
			),
			array(
				'permission' => 'write',
				'cmd'        => 'pairs',
				'txt'        => $this->txt('edit'),
				'default'    => false
			)
		);
	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	public function getProperties()
	{
		global $lng, $ilUser;

		$props = array();

		$this->plugin->includeClass("class.ilObjMatchMemoPoolAccess.php");
		$this->plugin->includeClass("class.ilObjMatchMemoPool.php");

		if(!ilObjMatchMemoPoolAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array(
				'alert' => true, 'property' => $lng->txt('status'),
				'value' => $lng->txt('offline')
			);
		}

		if(ilObjMatchMemoPool::_lookupPairCount($this->obj_id) < ilObjMatchMemoPool::MIN_PAIRS_NUM)
		{
			$props[] = array(
				'alert' => true, 'property' => $lng->txt('preconditions'),
				'value' => $this->plugin->txt('not_enough_pairs')
			);
		}

		return $props;
	}
}
