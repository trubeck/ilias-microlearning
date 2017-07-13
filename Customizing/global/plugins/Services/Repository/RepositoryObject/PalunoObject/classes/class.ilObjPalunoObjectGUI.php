<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
require_once("./Services/Tracking/classes/class.ilLearningProgress.php");
require_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
require_once("./Services/Tracking/classes/status/class.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilPalunoObjectPlugin.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");

/**
 * @ilCtrl_isCalledBy ilObjPalunoObjectGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjPalunoObjectGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI, ilObjectMetaDataGUI
 */
class ilObjPalunoObjectGUI extends ilObjectPluginGUI
{
	const LP_SESSION_ID = 'xpal_lp_session_state';

	/** @var  ilCtrl */
	protected $ctrl;

	/** @var  ilTabsGUI */
	protected $tabs;

	/** @var  ilTemplate */
	public $tpl;

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilCtrl, $ilTabs, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->tpl = $tpl;
	}

	public function executeCommand() {
		global $tpl;

		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class) {
			case 'ilexportgui':
				// only if plugin supports it?
				$tpl->setTitle($this->object->getTitle());
				$tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
				$this->setLocator();			//Pfad: Repository->Nugget
				$tpl->getStandardTemplate();
				$this->setTabs();				//Tabs anzeigen zur Navigation
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$this->tabs->activateTab("export");
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				$tpl->show();
				return;
				break;
			case 'ilobjectmetadatagui':
				// only if plugin supports it?
				$tpl->setTitle($this->object->getTitle());
				$tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
				$this->setLocator();
				$tpl->getStandardTemplate();
				$this->setTabs();
				include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
				$this->tabs->activateTab("meta_data");
				$md_gui = new ilObjectMetaDataGUI($this->object);
				$this->ctrl->forwardCommand($md_gui);
				$tpl->show();
				return;
				break;
		}

		$return_value = parent::executeCommand();

		return $return_value;
	}

	/**
	 * Get type.
	 */
	final function getType()
	{
		return ilPalunoObjectPlugin::ID;
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":   // list all commands that need write permission here
			case "updateProperties":
			case "saveProperties":
			case "showExport":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showContent":   // list all commands that need read permission here
			case "setStatusToCompleted":
			case "setStatusToFailed":
			case "setStatusToInProgress":
			case "setStatusToNotAttempted":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd()
	{
		return "showContent";
	}

//
// DISPLAY TABS
//

	/**
	 * Set tabs
	 */
	function setTabs()
	{
		global $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->tabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
			$this->tabs->addTab("export", $this->txt("export"), $ilCtrl->getLinkTargetByClass("ilexportgui", ""));
		}

		// metadata tab
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
			$mdgui = new ilObjectMetaDataGUI($this->object);					
			$mdtab = $mdgui->getTab();
			if($mdtab)
			{
				$this->tabs->addTab("meta_data", $this->txt("meta_data"), $mdtab);
			}
		}

		// standard permission tab
		$this->addPermissionTab();
		$this->activateTab();
	}

	/**
	 * Edit Properties. This commands uses the form class to display an input form.
	 */
	protected function editProperties()
	{
		$this->tabs->activateTab("properties");
		$form = $this->initPropertiesForm();
		$this->addValuesToForm($form);
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function initPropertiesForm() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt("obj_xpal"));

		$title = new ilTextInputGUI($this->plugin->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);

		$description = new ilTextInputGUI($this->plugin->txt("description"), "description");
		$form->addItem($description);

		$online = new ilCheckboxInputGUI($this->plugin->txt("online"), "online");
		$form->addItem($online);

		$form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));
		$form->addCommandButton("saveProperties", $this->plugin->txt("update"));

		return $form;
	}

	/**
	 * @param $form ilPropertyFormGUI
	 */
	protected function addValuesToForm(&$form) {
		$form->setValuesByArray(array(
			"title" => $this->object->getTitle(),
			"description" => $this->object->getDescription(),
			"online" => $this->object->isOnline(),
		));
	}

	/**
	 *
	 */
	protected function saveProperties() {
		$form = $this->initPropertiesForm();
		$form->setValuesByPost();
		if($form->checkInput()) {
			$this->fillObject($this->object, $form);
			$this->object->update();
			ilUtil::sendSuccess($this->plugin->txt("update_successful"), true);
			$this->ctrl->redirect($this, "editProperties");
		}
		$this->tpl->setContent($form->getHTML());
	}

	protected function showContent() {
		$this->tabs->activateTab("content");
		$tpl = new ilTemplate("tpl.paluno.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject");
		$tpl->setCurrentBlock("paluno_block");
		$tpl->setVariable("TYP", $this->txt("obj_xpal"));
		$tpl->setVariable("SRC_ADDNEW", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/video-placeholder-thumbnail.png");
		$tpl->setVariable("ARROW_LEFT", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/arrow left.png");
		$tpl->setVariable("ARROW_RIGHT", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/arrow right.png");
		//$tpl->setVariable("TYP", $this->object->getID());
		$tpl->parseCurrentBlock();
		//$tpl->setCurrentBlock("addpic");
		//$tpl->setVariable("SRC_ADDNEW", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/icon_xpal.svg");
		//$tpl->setVariable("TYP", $this->object->getID());
		//$tpl->parseCurrentBlock();
		//$tpl->show();
		$html = $tpl->get();	
		$this->tpl->setContent($html);
	}

	/**
	 * @param $object ilObjPalunoObject
	 * @param $form ilPropertyFormGUI
	 */
	private function fillObject($object, $form) {
		$object->setTitle($form->getInput('title'));
		$object->setDescription($form->getInput('description'));
		$object->setOnline($form->getInput('online'));
	}

	protected function showExport() {
		require_once("./Services/Export/classes/class.ilExportGUI.php");
		$export = new ilExportGUI($this);
		$export->addFormat("xml");
		$ret = $this->ctrl->forwardCommand($export);

	}

	/**
	 * We need this method if we can't access the tabs otherwise...
	 */
	private function activateTab() {
		$next_class = $this->ctrl->getCmdClass();

		switch($next_class) {
			case 'ilexportgui':
				$this->tabs->activateTab("export");
				break;
		}

		return;
	}

	private function setStatusToCompleted() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
	}

	private function setStatusAndRedirect($status) {
		global $ilUser;
		$_SESSION[self::LP_SESSION_ID] = $status;
		ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
		$this->ctrl->redirect($this, $this->getStandardCmd());
	}

	protected function setStatusToFailed() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_FAILED_NUM);
	}

	protected function setStatusToInProgress() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
	}

	protected function setStatusToNotAttempted() {
		$this->setStatusAndRedirect(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
	}
}
?>