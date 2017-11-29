<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
//require_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
require_once("./Services/Tracking/classes/class.ilLearningProgress.php");
require_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
require_once("./Services/Tracking/classes/status/class.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextNuggetPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditor.php");
require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");

/**
 * @ilCtrl_isCalledBy ilObjTextNuggetGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTextNuggetGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
 * @ilCtrl_Calls ilObjTextNuggetGUI: ilObjectMetaDataGUI, ilPersonalDesktopGUI, ilTextEditorGUI, ilObjStyleSheetGUI, ilObjTestGUI
 */
class ilObjTextNuggetGUI extends ilObjectPluginGUI
{
	const LP_SESSION_ID = 'xtxt_lp_session_state';

	/** @var  ilCtrl */
	protected $ctrl;

	/** @var  ilTabsGUI */
	protected $tabs;

	/** @var  ilTemplate */
	public $tpl;

	private $purposeSuffixes = array ();
	private $mimeTypes = array();
	private $examNuggets = array();
	private $tstObjects = array();

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
		global $tpl, $lng;

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
			case 'iltexteditorgui':
				$this->tabs->removeTab("export");
				$ret = $this->forwardToPageObject();
				$tpl->setContent($ret);
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
		return ilTextNuggetPlugin::ID;
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
			case "showAdmin":
			case "editText":
			case "setTstNuggets":
			case "setPreviewPicture":
			case "savePreviewPicture":
			case "saveExamNugget":
			case "confirmDeletion":
			case "makeTest":
			case "forwardToPageObject":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showContent":   // list all commands that need read permission here
			case "showExam":
			case "setStatusToCompleted":
			case "setStatusToFailed":
			case "setStatusToInProgress":
			case "setStatusToNotAttempted":
			case "moveToDesktop":
			case "removeFromDesktop":
			//case "goToExam":
			case "goToNugget":
			case "isObjectOnDesktop":
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
		$form->setTitle($this->plugin->txt("obj_xtxt"));

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

	/**
	 * Show content
	 * 
	 * @param
	 * @return
	 */
	protected function showContent() 
	{
		//tpl->addJavascript("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/js/TextNugget.js");
		
		$this->tabs->activateTab("content");
		$this->setSubTabs("view");
		$this->tabs->activateSubTab("view");
		
		$tpl = new ilTemplate("tpl.paluno.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget");
		
		$tpl->setCurrentBlock("paluno_block");


		//Merken-Button
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		if(!$this->isObjectOnDesktop())
		{
			//$this->ctrl->setParameterByClass("ilobjtextnuggetgui", "ref_id", $this->object->getRefId());
			$form->addCommandButton("moveToDesktop", $this->plugin->txt("save_to_desktop"));
		}
		else
		{
			$form->addCommandButton("removeFromDesktop", $this->plugin->txt("remove"));
		}
		$tpl->setVariable("FORM", $form->getHTML());

		include_once("./Services/NuggetNavigation/classes/class.ilNuggetNavigation.php");
		$navigation = new ilNuggetNavigation();

		$tpl->setVariable("TITLE", $this->object->getTitle());
		$tpl->setVariable("EXAM", $this->plugin->txt("check_yourself"));
		$tpl->setVariable('LINK_EXAM', $navigation->getLinkToNugget(278));

		$randomNuggetObjIds = $this->getRandomNuggetObjIds(3);
				
		//Nugget 1
		$objIdPrevious = $navigation->getPreviousNugget($this->object->getId());
		if($objIdPrevious != null)
		{
			$tpl->setVariable("NUGGET_1", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/previous-placeholder-thumbnail.png");
			$nameNugget1 = $navigation->getNuggetNameByObjId($objIdPrevious);
			$tpl->setVariable('LINK_NUG1', $navigation->getLinkToNugget($objIdPrevious));
		}
		else
		{
			$tpl->setVariable("NUGGET_1", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/video-placeholder-thumbnail.png");
			$nameNugget1 = $navigation->getNuggetNameByObjId($randomNuggetObjIds[0]);
			$tpl->setVariable('LINK_NUG1', $navigation->getLinkToNugget($randomNuggetObjIds[0]));
		}
		$tpl->setVariable("NAME_1", $nameNugget1);

		//Nugget 2
		$objIdNext = $navigation->getNextNugget($this->object->getId());
		if($objIdNext != null)
		{
			$tpl->setVariable("NUGGET_2", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/next-placeholder-thumbnail.png");
			$nameNugget2 = $navigation->getNuggetNameByObjId($objIdNext);
			$tpl->setVariable('LINK_NUG2', $navigation->getLinkToNugget($objIdNext));
		}
		else
		{
			$tpl->setVariable("NUGGET_2", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/video-placeholder-thumbnail.png");
			$nameNugget2 = $navigation->getNuggetNameByObjId($randomNuggetObjIds[1]);
			$tpl->setVariable('LINK_NUG2', $navigation->getLinkToNugget($randomNuggetObjIds[1]));
		}
		$tpl->setVariable("NAME_2", $nameNugget2);

		//Nugget 3
		$nameNugget3 = $navigation->getNuggetNameByObjId($randomNuggetObjIds[2]);
		$tpl->setVariable("NUGGET_3", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/video-placeholder-thumbnail.png");
		$tpl->setVariable('LINK_NUG3', $navigation->getLinkToNugget($randomNuggetObjIds[2]));
		$tpl->setVariable("NAME_3", $nameNugget3);		

		$tpl->setVariable("ARROW_LEFT", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/arrow left.png");
		$tpl->setVariable("ARROW_RIGHT", "Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/templates/images/arrow right.png");
		$tpl->setVariable("DESCRIPTION", $this->object->getDescription());
		
		// text preview
		// page object
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditor.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditorGUI.php");
		
		if (!ilTextEditor::_exists("txte",
			$this->object->getId()))
		{
			$tpl->setVariable("TEXT", "Noch kein Text vorhanden");
		}
		else
		{
			$page_gui = new ilTextEditorGUI($this->object->getId());
			$html = $page_gui->showPage();
			$tpl->setVariable("TEXT", $html);
		}
		
		// get page object
				
		$tpl->parseCurrentBlock();
		
		$html = $tpl->get();	
		$this->tpl->setContent($html);
	}

	/**
	* Go to selected nugget.
	
	function goToNugget($referenceId)
	{
		//$this->ctrl->setParameter(this, "ref_id", $referenceId);
		//$this->ctrl->setParameterByClass("ilobjpalunoobjectgui", "ref_id", $referenceId);
		//$link = $this->ctrl->getLinkTargetByClass('ilobjpalunoobjectgui', '');
		$ref = $referenceId;
		$link = "ilias.php?ref_id=73&cmd=showContent&cmdClass=ilobjpalunoobjectgui&cmdNode=jt:jq&baseClass=ilObjPluginDispatchGUI";

		return $link;
	}
	*/

		/**
	 * set content subtabs
	 *
	 * @param
	 * @return
	 */
	
	public function setSubTabs($a_tab)
	{
		global $ilAccess;


		switch($a_tab)
		{
			case 'view':
				$this->tabs->addSubTab('view', $this->txt("view"), $this->ctrl->getLinkTarget($this, "showContent"));
				if($ilAccess->checkAccess('write', '', $this->object->getRefId()))
				{
					//include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditorGUI.php");
					$this->tabs->addSubTab('admin', $this->txt("admin"), $this->ctrl->getLinkTarget($this, "showAdmin"));
					//$this->tabs->addSubTab('admin', $this->txt("admin"), $this->ctrl->getLinkTargetByClass('ilTextEditorGUI', 'edit'));
				}
				
				break;
		}
	}
	
	//Seite mit Add-Button
	protected function showAdmin() 
	{
		global $ilToolbar, $ilDB;

		$this->setSubTabs("view");
		$this->tabs->activateTab("content");
		$this->tabs->activateSubTab("admin");
		
		$ilToolbar->addButton($this->txt("exam"), $this->ctrl->getLinkTarget($this, "setTstNuggets"));
		$ilToolbar->addButton($this->txt("set_preview_picture"), $this->ctrl->getLinkTarget($this, "setPreviewPicture"));
		$ilToolbar->addButton($this->txt("edit_text"), $this->ctrl->getLinkTarget($this, "editText"));
	}
		
	function forwardToPageObject()
	{
		global $lng;

		$cmd = $this->ctrl->getCmd();

		$this->tabs->clearTargets();

		if ($_GET["redirectSource"] == "ilinternallinkgui")
		{
			exit;
		}

		$this->tabs->setBackTarget("back", "./goto.php?target=".$this->object->getType()."_".
				$this->object->getRefId(), "_top");

		// page object
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditor.php");
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditorGUI.php");

		$lng->loadLanguageModule("content");
		
		if (!ilTextEditor::_exists("txte",
			$this->object->getId()))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilTextEditor();
			$new_page_object->setParentId($this->object->getId());
			$new_page_object->setId($this->object->getId());
			$new_page_object->createFromXML();
		}
		
		// get page object
		$this->ctrl->setReturnByClass("iltexteditorgui", "edit");
		$page_gui = new ilTextEditorGUI($this->object->getId());
		
		$page_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_gui->setFileDownloadLink("");
		$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
		//$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);

		// style tab
		//$page_gui->setTabHook($this, "addPageTabs");
		
		$ret = $this->ctrl->forwardCommand($page_gui);

		//$ret =& $page_gui->executeCommand();
		return $ret;
	}

	/**
	* Edit Text
	*/
	function editText()
	{
		$this->checkPermission("write");
		//$this->tabs->activateTab("admin");
		// create new text
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditor.php");
		//$textEditor = new ilTextEditor();
		//$textEditor->create();
		//$ilCtrl->setParameterByClass("ilblogpostinggui", "blpg", $posting->getId());
		$this->ctrl->redirectByClass("iltexteditorgui", "edit");
		
		/**
		global $tpl;

		$this->checkPermission("write");
		$this->tabs->activateTab("admin");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setMultipart(true);

		$this->form_gui->setTitle($this->txt("set_exam_nugget"));
		$this->tpl->setContent($this->form_gui->getHTML());
		*/
	}

		/**
	* Set Preview Picture
	*/
	function setPreviewPicture()
	{
		global $tpl, $lng;

		$this->checkPermission("write");
		$this->tabs->activateTab("admin");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setMultipart(true);

		$this->form_gui->setTitle($this->txt("set_preview_picture"));

		// preview picture selection
		$pp = new ilImageFileInputGUI($this->txt("set_preview_picture"), "preview_pic");
    	$pp->setSuffixes(array("png", "jpeg", "jpg"));
    	$this->form_gui->addItem($pp);
    	
		//save/cancel
		if($this->hasPreviewPicture())
		{
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, "showAdmin"));
		}
		else
		{
			$this->form_gui->addCommandButton("savePreviewPicture", $this->txt("save"));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, "savePreviewPicture"));
		}
		
		$this->form_gui->addCommandButton("showAdmin", $this->txt("cancel"));

		$this->tpl->setContent($this->form_gui->getHTML());
	}

	function savePreviewPicture()
	{
		global $ilLog; 	

		$this->setPreviewPicture();

		if (!$this->form_gui->checkInput())
		{
			ilUtil::sendFailure($this->txt("xtxt_input_picture"));
		}
		else
		{
			// create dummy object in db (we need an id)
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			$mob = new ilObjMediaObject();
			$mob->create();

			// save preview pic
			$prevpic = $this->form_gui->getInput("preview_pic");
			if ($prevpic["size"] > 0)
			{
				$mob->uploadVideoPreviewPic($prevpic);
			}

			//save obj_id in database (importId is never in use)
			$mob->setImportId($this->object->getId());
			$mob->update();

			if ($prevpic["size"] == 0)
			{
				// re-read media object
				$mob = new ilObjMediaObject($mob->getId());
				$mob->generatePreviewPic(320, 240);
			}

			$boool = $this->hasPreviewPicture();
			include_once "Services/Logging/classes/class.ilLog.php";
			if($boool)
			{
				$ilLog->write("ja");
			}
			else
			{
				$ilLog->write("nein");
			}

			$this->ctrl->redirect($this, "showContent");
		}
	}

	/**
	* Does object have a previe picture?
	*/
	function hasPreviewPicture()
	{	
		global $ilDB;

		include_once "Services/Logging/classes/class.ilLog.php";
        $type = "mob";
        $result = $ilDB->query("SELECT * FROM object_data WHERE type = ".$ilDB->quote($type, "text"));

        $mobObjIds = "";
        while($data = $ilDB->fetchAssoc($result))
        {
            $mobObjIds .= $data["obj_id"] . ",";
        }

        $mobObjIds = explode(",", substr($mobObjIds, 0, -1));

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		foreach($mobObjIds as $id)
		{
			$mob = new ilObjMediaObject($id);
			if($mob->getImportId() == $this->object->getId())
			{
				return true;
			}
		}

		return false;
	}

	/**
	* Set Exam Nugget
	*/
	function setTstNuggets()
	{
		global $tpl;

		$this->checkPermission("write");
		$this->tabs->activateTab("admin");

		$this->examNuggets = $this->getExamNuggets();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setMultipart(true);

		$this->form_gui->setTitle($this->txt("set_exam_nugget"));

		// examNugget selection
    	$examNuggetSelection = new ilSelectInputGUI();
		$examNuggetSelection->setPostVar("selected_exam");
    	$examNuggetSelection->setTitle($this->txt("exam"));
    	$options = array("");
    	$options = $this->examNuggets;
    	$examNuggetSelection->setOptions($options);
    	$this->form_gui->addItem($examNuggetSelection);

		//save/cancel
		$this->form_gui->addCommandButton("saveExamNugget", $this->txt("save"));
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, "saveExamNugget"));
		$this->form_gui->addCommandButton("showAdmin", $this->txt("cancel"));

		$this->tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Save Exam Nugget.
	*/
	function saveExamNugget()
	{
		$this->checkPermission("write");
		$this->tabs->activateTab("admin");

		$number = $_POST["selected_exam"];
		$obj_id = $this->getObjectId($number);
		$this->object->setRefIdFromExam($obj_id);
		ilUtil::sendSuccess($this->object->getRefIdFromExam(), true);
		
		$this->ctrl->redirect($this, "showContent");
	}

	/**
	* Get Exam Nuggets.
	*/
	function getExamNuggets()
	{
		$tstNuggets = array();
		$this->tstObjects = $this->object->_getObjectsDataForType("tst", false);
		foreach($this->tstObjects as $tst)
		{
			$currentArray = $tst;
			$tstNuggets[] = $currentArray["title"];
		}
		
		return $tstNuggets;
	}

	/**
	* Get Object Id.
	*/
	function getObjectId($number)
	{
		$this->tstObjects = $this->object->_getObjectsDataForType("tst", false);

		$i = 0;
		foreach($this->tstObjects as $tst)
		{
			if($i == $number)
			{
				$obj_id = $tst["id"];
			}
			$i++;
		}
		
		return $obj_id;
	}

	/**
	* Is object on desktop?
	*/
	function isObjectOnDesktop()
	{	
		global $ilUser;
   
        if (ilObjUser::_isDesktopItem($ilUser->getId() ,(int) $this->object->getRefId(), $this->object->getType()))
		{
			return true;
        }
		else
		{
			return false;
		}
		
		$this->ctrl->redirect($this, "showContent");
	}

	/**
	* Move to desktop.
	*/
	function moveToDesktop()
	{	
		global $ilUser;
    
        if ($this->object->getRefId())
		{
			ilObjUser::_addDesktopItem($ilUser->getId() ,(int) $this->object->getRefId(), $this->object->getType());
			ilUtil::sendSuccess($this->txt("added_to_desktop"), true);
        }
        
		$this->ctrl->redirect($this, "showContent");
	}

	/**
	* Remove from desktop.
	*/
	function removeFromDesktop()
	{	
		global $ilUser;
     
        if ($this->object->getRefId())
		{
			ilObjUser::_dropDesktopItem($ilUser->getId() ,(int) $this->object->getRefId(), $this->object->getType());
			ilUtil::sendSuccess($this->txt("removed_from_desktop"), true);
        }
        
		$this->ctrl->redirect($this, "showContent");
	}

	/**
	* Go to exam.
	
	function goToExam()
	{
		$referenceIdFromExam = $this->object->getRefIdFromExam();
		$this->ctrl->setParameterByClass("ilObjPalunoObjectGUI", "ref_id", 72);
		$this->ctrl->redirectByClass("ilObjPalunoObjectGUI", "");
	}
	*/

	/**
	* Confirmation Screen.
	*/
	function confirmDeletion()
	{
		$this->checkPermission("write");
		$this->tabs->activateTab("admin");
		
		//include_once("./Services/Object/classes/class.ilObject.php");
		//$obj = new ilNewsItem();
		$this->tstObjects = $this->object->_getObjectsDataForType("tst", false);
		foreach($this->tstObjects as $tst)
		{
			$currentArray = $tst;
			$examNuggets[] = $currentArray["title"];
			//$currentArray = array();
		}
		$all = implode(",", $examNuggets);
		$firstest = $this->tstObjects[1];
		$lastTest = array_pop($this->tstObjects);
		$entry = $firstest["title"];
		//$examNuggets[] = $entry;
		//$parse = implode(",", $keys);
		ilUtil::sendSuccess($entry, true);
		//$mc_item = new ilNewsItem(289);
		//$mc_item->delete();
		//$this->ctrl->redirect($this, "editProperties");
		$this->ctrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
	}

	function getRandomNuggetObjIds($count)
	{
		include_once("Services/NuggetRecommender/classes/class.ilNuggetRecommender.php");
		$recommender = new ilNuggetRecommender();
		$nuggets = $recommender->getRandom($count);

		return $nuggets;
	}

	function makeTest()
	{
		//ilUtil::sendSuccess("eins", true);
		$result = $this->getRandomNuggetObjIds(3);
		ilUtil::sendSuccess($result[2], true);

		$this->ctrl->redirect($this, "showContent");
	}
	
	/**
	 * @param $object ilObjTextNugget
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