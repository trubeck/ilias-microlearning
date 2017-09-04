<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
require_once("./Services/Tracking/classes/class.ilLearningProgress.php");
require_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
require_once("./Services/Tracking/classes/status/class.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilPalunoObjectPlugin.php");
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

	private $additionalPurposes = array ("VideoPortable", "AudioPortable");
	private $purposeSuffixes = array ();
	private $mimeTypes = array();

	protected $itemId;

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilCtrl, $ilTabs, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->tpl = $tpl;
		//$this->ctrl->saveParameter($this, "ref_item_id");
		//$this->ctrl->setParameterByClass("ilobjpalunoobjectgui", "item_id", "");

		include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilPalunoObjectSettings.php');
		$settings = ilPalunoObjectSettings::_getInstance();
		$this->purposeSuffixes = $settings->getPurposeSuffixes();

		$this->mimeTypes = array();
		$mime_types = $settings->getMimeTypes();
		foreach ($mime_types as $mt)
		{
			$this->mimeTypes[$mt] = $mt;
		}
		
		include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
		foreach (ilMimeTypeUtil::getExt2MimeMap() as $mt)
		{
			$this->mimeTypes[$mt] = $mt;
		}
		asort($this->mimeTypes);
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
			case "showAdmin":
			case "addVideoObject":
			case "saveVideo":
			case "confirmDeletion":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showContent":   // list all commands that need read permission here
			case "showUpload":
			case "setStatusToCompleted":
			case "setStatusToFailed":
			case "setStatusToInProgress":
			case "setStatusToNotAttempted":
			case "handlePlayerEvent":
			case "moveToDesktop":
			case "removeFromDesktop":		
			case "goToExam":
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

		// upload tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->tabs->addTab("upload", $this->txt("upload"), $ilCtrl->getLinkTarget($this, "showUpload"));
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

	/**
	 * Show content
	 * 
	 * @param
	 * @return
	 */
	protected function showContent() 
	{
		//tpl->addJavascript("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/js/PalunoObject.js");
		
		$this->tabs->activateTab("content");
		$this->setSubTabs("view");
		$this->tabs->activateSubTab("view");
		
		$tpl = new ilTemplate("tpl.paluno.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject");

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
		$med_items = $this->object->getSortedItemsArray();

		if (count($med_items) != 0)
		{
			foreach ($med_items as $item)
			{
				$this->ctrl->setParameterByClass("ilobjpalunoobjectgui", "item_id", $item["id"]);
				$mob = new ilObjMediaObject($item["mob_id"]);
				$med = $mob->getMediaItem("Standard");
			
				$tpl->setCurrentBlock("paluno_block");
				//$tpl->setVariable("TYP", $item["title"]);
				$tpl->setVariable("SRC_ADDNEW", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/video-placeholder-thumbnail.png");
				$tpl->setVariable("ARROW_LEFT", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/arrow left.png");
				$tpl->setVariable("ARROW_RIGHT", "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/templates/images/arrow right.png");
				$tpl->setVariable("TITLE", $item["title"]);
				$tpl->setVariable("DESCRIPTION", $item["content"]);

				$this->ctrl->setParameter($this, "item_ref_id", $this->object->getRefId());
				//Merken-Button
				$form = new ilPropertyFormGUI();
				$form->setTitle($item["title"]);
				$form->setFormAction($this->ctrl->getFormAction($this, "goToExam"));
				$form->addCommandButton("goToExam", $this->plugin->txt("check_yourself"));
				if(!$this->isObjectOnDesktop())
				{
					$form->addCommandButton("moveToDesktop", $this->plugin->txt("save_to_desktop"));
				}
				else
				{
					$form->addCommandButton("removeFromDesktop", $this->plugin->txt("remove"));
				}
				$tpl->setVariable("FORM", $form->getHTML());

				// player
				if (is_object($med))
				{
					include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");

					// the news id will be used as player id, see also ilMediaCastTableGUI
					$mpl = new ilMediaPlayerGUI($item["id"], $this->ctrl->getLinkTarget($this, "handlePlayerEvent", "", true, false));

					if (strcasecmp("Reference", $med->getLocationType()) == 0)
					{
						ilWACSignedPath::signFolderOfStartFile($med->getLocation());
						$mpl->setFile($med->getLocation());
					}
					else
					{
						$path_to_file = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
						ilWACSignedPath::signFolderOfStartFile($path_to_file);
						$mpl->setFile($path_to_file);
					}
					$mpl->setMimeType ($med->getFormat());
					//$mpl->setDisplayHeight($med->getHeight());
					$mpl->setDisplayHeight("480");
					$mpl->setDisplayWidth("640");
					$mpl->setVideoPreviewPic(ilWACSignedPath::signFile($mob->getVideoPreviewPic()));
					$mpl->setTitle($item["title"]);
					$mpl->setDescription($item["content"]);
					$mpl->setForceAudioPreview(true);

					//$this->ctrl->setParameterByClass("ilobjpalunoobjectgui", "item_id", $item["id"]);

					$med_alt = $mob->getMediaItem("VideoAlternative");
					if (is_object($med_alt))
					{
						$mpl->setAlternativeVideoFile(ilWACSignedPath::signFile(ilObjMediaObject::_getURL($mob->getId())."/".
							$med_alt->getLocation()));
						$mpl->setAlternativeVideoMimeType($med_alt->getFormat());
					}
				
					$tpl->setVariable("PLAYER", $mpl->getPalunoPlayerHtml(true));
				}
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("empty_block");
			$tpl->setVariable("NO_FILE", $this->txt("no_content"));
		
			$tpl->parseCurrentBlock();
		}

		//$parse = implode(",", $med_items);
		//$parse = $med_items["mob_id"];
		
		//$mob = new ilObjMediaObject($item["mob_id"]);
		//$med = $mob->getMediaItem("Standard");
			
		//$tpl->setCurrentBlock("paluno_block");
		//$tpl->setVariable("TYP", $parse);
		
		//$tpl->parseCurrentBlock();
		
		$html = $tpl->get();	
		$this->tpl->setContent($html);
	}

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
					$this->tabs->addSubTab('admin', $this->txt("admin"), $this->ctrl->getLinkTarget($this, "showAdmin"));
				}
				
				break;
		}
	}
	
	//Seite mit Add-Button
	protected function showAdmin() 
	{
		global $ilToolbar;

		$med_items = $this->object->getSortedItemsArray();
		$count_items = count($med_items);

		$this->setSubTabs("view");
		$this->tabs->activateTab("content");
		$this->tabs->activateSubTab("admin");
		if ($count_items == 0)
		{
			$ilToolbar->addButton($this->txt("add"), $this->ctrl->getLinkTarget($this, "addVideoObject"));
		}
		else
		{
			$ilToolbar->addButton($this->txt("delete"), $this->ctrl->getLinkTarget($this, "confirmDeletion"));
		}
		//$tpl = new ilTemplate("tpl.upload.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject");
		//$tpl->setCurrentBlock("paluno_block");
		//$tpl->setVariable("TYP", $this->txt("obj_xpal"));
		
		//$tpl->parseCurrentBlock();
		
		//$html = $tpl->get();	
		//$this->tpl->setContent($html);
	}

	protected function showUpload() {
		$this->tabs->activateTab("upload");
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt("obj_xpal"));
		$this->addValuesToForm($form);
		$tpl = new ilTemplate("tpl.upload.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject");
		$tpl->setCurrentBlock("paluno_block");
		//$tpl->setVariable("TYP", $this->txt("obj_xpal"));
		$tpl->setVariable("TYP", $form->getHTML());
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
	* Add video
	*/
	//Form-Seite mit File-Button
	function addVideoObject()
	{
		//global $tpl;

		$this->checkPermission("write");
		
		$this->initAddVideoForm();
		$this->tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Init add video form.
	*/
	function initAddVideoForm($a_mode = "create")
	{		
		global $tpl;

		$this->checkPermission("write");
		$this->tabs->activateTab("admin");
		//ilUtil::sendSuccess($this->txt("obj_xpal"), true);

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setMultipart(true);

		$this->form_gui->setTitle($this->txt("add"));
		// Property Title
		$text_input = new ilTextInputGUI($this->txt("title"), "title");
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property Content
		$text_area = new ilTextAreaInputGUI($this->txt("description"), "description");
		$text_area->setRequired(false);
		$this->form_gui->addItem($text_area);

		// Duration
		$dur = new ilDurationInputGUI($this->txt("duration"), "duration");
		$dur->setInfo($this->txt("duration_info"));
		$dur->setShowDays(false);
		$dur->setShowHours(true);
		$dur->setShowSeconds(true);
		$this->form_gui->addItem($dur);

		foreach (ilObjPalunoObject::$purposes as $purpose)
		{
			if ($purpose == "VideoAlternative" &&
				$a_mode == "create")
			{
				continue;
			}
			
    		$section = new ilFormSectionHeaderGUI();    		
    		$section->setTitle($this->txt("xpal_".strtolower($purpose)."_title"));
    		$this->form_gui->addItem($section);
    		if ($a_mode != "create")
    		{
    		    $value = new ilHiddenInputGUI("value_".$purpose);
    		    $label = new ilNonEditableValueGUI($this->txt("value"));
    		    $label->setPostVar("label_value_".$purpose);	
    		    $label->setInfo($this->txt("xpal_current_value_info"));
    		    $this->form_gui->addItem($label);
    		    $this->form_gui->addItem($value);

    		}
    		$file = new ilFileInputGUI($this->txt("add"), "file_".$purpose);		
    		$file->setSuffixes($this->purposeSuffixes[$purpose]);
    		$this->form_gui->addItem($file);
    		$text_input = new ilRegExpInputGUI($this->txt("url"), "url_".$purpose);
    		$text_input->setPattern("/https?\:\/\/.+/i");
    		$text_input->setInfo($this->txt("xpal_reference_info"));
    		$this->form_gui->addItem($text_input);
    		if ($purpose != "Standard")
    		{
        		$clearCheckBox = new ilCheckboxInputGUI();
        		$clearCheckBox->setPostVar("delete_".$purpose);
        		$clearCheckBox->setTitle($this->txt("xpal_clear_purpose_title"));
        		$this->form_gui->addItem($clearCheckBox);
    		} else {
    			
    			// mime type selection
    			$mimeTypeSelection = new ilSelectInputGUI();
    			$mimeTypeSelection->setPostVar("mimetype_".$purpose);
    			$mimeTypeSelection->setTitle($this->txt("xpal_mimetype"));
    			$mimeTypeSelection->setInfo($this->txt("xpal_mimetype_info")); 
    			$options = array("" => $this->txt("xpal_automatic_detection"));
    			$options = array_merge($options, $this->mimeTypes);
    			$mimeTypeSelection->setOptions($options);    			
    			$this->form_gui->addItem($mimeTypeSelection);
    		}
    		
		}

		// save/cancel button
		if ($a_mode == "create")
		{
		    $this->form_gui->setTitle($this->txt("add"));		    
		    $this->form_gui->addCommandButton("saveVideo", $this->txt("save"));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, "saveVideo"));
		}
	
		$this->form_gui->addCommandButton("showAdmin", $this->txt("cancel"));	
		//$this->form_gui->setFormAction($this->ctrl->getFormAction($this, "saveVideo"));
	}

	/**
	* Save new video
	function saveVideoObject()
	{
		global $tpl, $ilCtrl, $ilUser;

		$this->checkPermission("write");
		$ilTabs->activateTab("admin");
		
		$this->initAddVideoForm();
		$this->tpl->setContent($this->form_gui->getHTML());
		ilUtil::sendSuccess($this->txt("obj_xpal"), true);
	}
	*/
	function saveVideo()
	{
		global $ilUser;

		$this->checkPermission("write");
		$this->tabs->activateTab("admin");
		
		$this->initAddVideoForm();

		if (!$this->form_gui->checkInput() ||
			($_POST["url_Standard"] == "" && !$_FILES['file_Standard']['tmp_name']))
		{
			if (($_POST["url_Standard"] == "" && !$_FILES['file_Standard']['tmp_name']))
			{
				ilUtil::sendFailure($this->txt("xpal_input_either_file_or_url"));
			}
			$this->populateFormFromPost();
		}
		else
		{
			// create dummy object in db (we need an id)
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			$mob = new ilObjMediaObject();
			$mob->create();

			//handle standard purpose
			$file = $this->createMediaItemForPurpose($mob, "Standard");						

			// set title and description
			// set title to basename of file if left empty
			$title = $this->form_gui->getInput("title") != "" ? $this->form_gui->getInput("title") : basename($file);
			$description = $this->form_gui->getInput("description");
			$mob->setTitle($title);
			$mob->setDescription($description);

			// save preview pic
			//$prevpic = $this->form_gui->getInput("preview_pic");
			//if ($prevpic["size"] > 0)
			//{
			//	$mob->uploadVideoPreviewPic($prevpic);
			//}
			
			// determine duration for standard purpose			
			$duration = $this->getDuration($file);						
			
			// handle other purposes
			foreach ($this->additionalPurposes as $purpose) 
			{
			    // check if some purpose has been uploaded
				$file_gui = $this->form_gui->getInput("file_".$purpose);
				$url_gui = $this->form_gui->getInput("url_".$purpose);
				if ($url_gui || $file_gui["size"] > 0) 
			    {
			        $this->createMediaItemForPurpose ($mob, $purpose);
				}
			}
		
			$mob->update();
		
			// re-read media object
			$mob = new ilObjMediaObject($mob->getId());
			$mob->generatePreviewPic(320, 240);
			
			//
			// @todo: save usage
			//
			
			//$news_set = new ilSetting("news");
			//$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			// create new media cast item
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$mc_item = new ilNewsItem();
			$mc_item->setMobId($mob->getId());
			$mc_item->setContentType(NEWS_AUDIO);
			$mc_item->setContextObjId($this->object->getId());
			$mc_item->setContextObjType($this->object->getType());
			$mc_item->setUserId($ilUser->getId());
			$mc_item->setPlaytime($duration);
			$mc_item->setTitle($title);
			$mc_item->setContent($description);
			$mc_item->setLimitation(false);
		
			$mc_item->create();
		
			$this->ctrl->redirect($this, "showContent");
		}	
	}	

	/**
	 * get duration from form or from file analyzer 
	 *
	 * @param unknown_type $file
	 * @return unknown
	 */
	private function getDuration($file)
	{
	    $duration = isset($this->form_gui) 
			? $this->form_gui->getInput("duration") 
			: array("hh"=>0, "mm"=>0, "ss"=>0);
	    if ($duration["hh"] == 0 && $duration["mm"] == 0 && $duration["ss"] == 0 && is_file($file))
	    {
	        include_once("./Services/MediaObjects/classes/class.ilMediaAnalyzer.php");
	        $ana = new ilMediaAnalyzer();
	        $ana->setFile($file);
	        $ana->analyzeFile();
	        $dur = $ana->getPlaytimeString();
	        $dur = explode(":", $dur);
	        $duration["mm"] = $dur[0];
	        $duration["ss"] = $dur[1];
	    }
	    $duration = str_pad($duration["hh"], 2 , "0", STR_PAD_LEFT).":".
	                str_pad($duration["mm"], 2 , "0", STR_PAD_LEFT).":".
	                str_pad($duration["ss"], 2 , "0", STR_PAD_LEFT);
	    return $duration;
	}

	/**
	 * handle media item for given purpose
	 *
	 * @param ilMediaObject $mob
	 * @param string file
	 */
	private function createMediaItemForPurpose ($mob, $purpose) 	   
	{
	    $mediaItem = new ilMediaItem();
		$mob->addMediaItem($mediaItem);
		$mediaItem->setPurpose($purpose);		
		return $this->updateMediaItem($mob, $mediaItem);
	}

	/**
	 * update media item from form
	 *
	 * @param IlObjectMediaObject $mob
	 * @param IlMediaItem $mediaItem
	 * @return string file
	 */
	private function updateMediaItem ($mob, & $mediaItem)
	{
	    $purpose = $mediaItem->getPurpose();
	    $url_gui = $this->form_gui->getInput ("url_".$purpose);
	    $file_gui = $this->form_gui->getInput ("file_".$purpose);
	    if ($url_gui)
	    {
	        // http
	        $file = $this->form_gui->getInput ("url_".$purpose);
	        $title = basename ($file);
	        $location = $this->form_gui->getInput ("url_".$purpose);
	        $locationType = "Reference";
	    } elseif ($file_gui["size"] > 0){
	        // lokal
	        // determine and create mob directory, move uploaded file to directory
	        $mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
	        if (!is_dir($mob_dir))
	            $mob->createDirectory();
	        
	        $file_name = ilUtil::getASCIIFilename($_FILES['file_'.$purpose]['name']);
	        $file_name = str_replace(" ", "_", $file_name);

	        $file = $mob_dir."/".$file_name;
	        $title = $file_name;
	        $locationType = "LocalFile";
	        $location = $title;
	        ilUtil::moveUploadedFile($_FILES['file_'.$purpose]['tmp_name'], $file_name, $file);
	        ilUtil::renameExecutables($mob_dir);
	        
	    }
	    
	    // check if not automatic mimetype detection
	    if ($_POST["mimetype_".$purpose] != "")
	    {
        	$mediaItem->setFormat($_POST["mimetype_".$purpose]);
	    }
	    elseif ($mediaItem->getLocation () != "")
	    {
	    	$format = ilObjMediaObject::getMimeType($mediaItem->getLocation(), ($locationType == "Reference"));
	    	$mediaItem->setFormat($format);
	    }

	    if (isset($file))
	    {
	        // get mime type, if not already set!
	        if (!isset($format))
	        {
	        	$format = ilObjMediaObject::getMimeType($file, ($locationType == "Reference"));
	        }

	        // set real meta and object data
	        $mediaItem->setFormat($format);
	        $mediaItem->setLocation($location);
	        $mediaItem->setLocationType($locationType);
	        $mediaItem->setHAlign("Left");
	        $mediaItem->setHeight(self::isAudio($format)?0:180);	        
	    } 
	        	    
	    if ($purpose == "Standard")
	    {
	        if (isset($title))
	            $mob->setTitle ($title);
	        if (isset($format))
	            $mob->setDescription($format);
	    }

	    return $file;
	}

	/**
	 * detect audio mimetype
	 *
	 * @param string $extension
	 * @return true, if extension contains string "audio"
	 */
	protected static function isAudio($extension) {
		return strpos($extension,"audio") !== false;
	}

	private function populateFormFromPost() 
	{
	    //issue: we have to display the current settings
	    // problem: POST does not contain values of disabled textfields
	    // solution: use hidden field and label to display-> here we need to synchronize the labels
	    // with the values from the hidden fields. 
		foreach (ilObjPalunoObject::$purposes as $purpose) 
		{
		    if ($_POST["value_".$purpose])
		    {
		        $_POST["label_value_".$purpose] = $_POST["value_".$purpose]; 
		    }
		}					    
		
		$this->form_gui->setValuesByPost();
		$this->tpl->setContent($this->form_gui->getHTML());			    
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
	*/
	function goToExam()
	{	
        
		ilUtil::sendSuccess($this->txt("obj_xpal"), true);
		
		$this->ctrl->redirect($this, "showContent");
	}

	/**
	* Confirmation Screen.
	*/
	function confirmDeletion()
	{
		$this->checkPermission("write");
		$this->tabs->activateTab("admin");
		
		//include_once("./Services/News/classes/class.ilNewsItem.php");
		ilUtil::sendSuccess($this->txt("obj_xpal"), true);
		//$mc_item = new ilNewsItem(289);
		//$mc_item->delete();
		$this->ctrl->redirect($this, "editProperties");
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

	/**
	 * Handle player event
	 *
	 * @param
	 * @return
	 */
	function handlePlayerEvent()
	{
		if ($_GET["event"] == "play")
		{
			$player = explode("_", $_GET["player"]);
			$news_id = (int) $player[1];
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$item = new ilNewsItem($news_id);
			$item->increasePlayCounter();
			
			$mob_id = $item->getMobId();
			if($mob_id)
			{						
				global $ilUser;
				$this->object->handleLPUpdate($ilUser->getId(), $mob_id);
			}
		}
		exit;
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