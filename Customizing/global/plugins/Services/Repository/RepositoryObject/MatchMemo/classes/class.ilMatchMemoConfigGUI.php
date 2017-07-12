<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * Match & Memo configuration user interface class
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 */
class ilMatchMemoConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{

		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl;

		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
	}
	
	//
	// From here on, this is just an gallery implementation using
	// a standard form (without saving anything)
	//
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;
		
		$pl = $this->getPluginObject();
		$pl->includeClass("class.ilObjMatchMemo.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("save", $lng->txt("save"));
	                
		$form->setTitle($pl->txt("matchmemo_plugin_configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));
			
		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			/*
			$pl->includeClass("class.ilObjMatchMemo.php");
			ilObjMatchMemo::_setConfiguration('ext_img', $_POST['ext_img']);
			*/
			ilUtil::sendSuccess($pl->txt("configuration_saved"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

}
?>
