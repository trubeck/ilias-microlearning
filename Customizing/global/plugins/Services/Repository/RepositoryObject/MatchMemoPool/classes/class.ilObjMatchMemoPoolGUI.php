<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for gallery repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjMatchMemoPoolGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjMatchMemoPoolGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjMatchMemoPoolGUI: ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjMatchMemoPoolGUI: ilMDEditorGUI
*
*/
class ilObjMatchMemoPoolGUI extends ilObjectPluginGUI
{
	public $plugin;
	
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - gallery: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xmpl";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
  */
	function performCommand($cmd)
	{
		global $ilAccess, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			case 'ilmdeditorgui':
				global $ilErr;
				if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->WARNING);
				}
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui = new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				$ilTabs->setTabActive("meta_data");
				return $this->ctrl->forwardCommand($md_gui);
				break;

			case 'ilcommonactiondispatchergui':
				require_once 'Services/Object/classes/class.ilCommonActionDispatcherGUI.php';
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				return $this->ctrl->forwardCommand($gui);
				break;
		}
		
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "saveProperties":
			case "addPair":
			case "editPair":
			case "savePair":
			case "savePairNew":
			case "deletePairs":
			case "savePairClose":
			case "importCSV":
			case "copy":
			case "move":
			case "paste":
			case "importPairs":
			case "pairs":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			case "gallery":			// list all commands that need read permission here
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
		return "pairs";
	}

	/**
	* Get standard command
  */
	function getStandardCmd()
	{
		return "pairs";
	}


	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $lng, $ilCtrl, $tpl, $ilTabs;

		$ilTabs->setTabActive("info_short");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->addSection($this->txt("plugininfo"));
		$info->addProperty('Name', 'Match & Memo Pool');
		$info->addProperty('Version', xmpl_version);
		$info->addProperty("Match &amp; Memo", "Konzeption: Lt. Florian Andresen; Leitung: RA Anja Krüger, Prof. Dr. jur. Günter Reiner, Professur für Bürgerliches Recht, Handels-, Gesellschafts-, Wirtschafts- und Steuerrecht,	Helmut Schmidt Universität, Universität der Bundeswehr Hamburg");
		$info->addProperty('Developer', 'Helmut Schottmüller / Michael Jansen');

		$info->addProperty('Kontakt', 'ilias [at] aurealis [dot] de / mjansen [at] databay [dot] de');
		$info->addProperty('&nbsp;', 'Aurealis / Databay AG');
		$info->addProperty('&nbsp;', '');
		$info->addProperty('&nbsp;', "http://www.aurealis.de / http://www.databay.de");



		$info->enablePrivateNotes();

		// general information
		$lng->loadLanguageModule("meta");

		$this->addInfoItems($info);


		// forward the command
		$ret = $ilCtrl->forwardCommand($info);


		//$tpl->setContent($ret);
	}
	//
	// DISPLAY TABS
	//

	protected function setSubTabs($cmd)
	{
	}

	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("pairs", $this->txt("pairs"), $ilCtrl->getLinkTarget($this, "pairs"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		if($ilAccess->checkAccess('write', "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("meta_data", $this->ctrl->getLinkTargetByClass("ilmdeditorgui",''), "", "ilmdeditorgui");
		}

		// standard epermission tab
		$this->addPermissionTab();
	}


	// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
	// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

	//
	// Edit properties form
	//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $ilAccess;
		global $tpl, $ilTabs;

		$ilTabs->activateTab("properties");
		
		$save = ((strcmp($this->ctrl->getCmd(), "saveProperties") == 0)) ? true : false;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'properties'));
		$form->setTitle($this->lng->txt("properties"));
		$form->setMultipart(false);
		$form->setId("properties");

		// online
		$online = new ilCheckboxInputGUI($this->txt("mpl_online_property"), "online");
		if(ilObjMatchMemoPool::_lookupPairCount($this->object->getId()) < ilObjMatchMemoPool::MIN_PAIRS_NUM)
		{
			$online->setInfo(implode('<br />', array(
				$this->txt("mpl_online_property_description"),
				'<span style="color:red">' . $this->txt("cannot_set_online_not_enough_pairs") . '</span>'
			)));
		}
		else
		{
			$online->setInfo($this->txt("mpl_online_property_description"));
		}
		$online->setChecked($this->object->online);
		$form->addItem($online);

		// add entry to navigation history
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("saveProperties", $this->lng->txt("save"));

		if ($save)
		{
			$form->checkInput();
		}
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	* list pairs
	*/
	public function pairs($arrFilter = null)
	{
		global $rbacsystem;
		global $ilUser;
		global $ilTabs;

		$ilTabs->activateTab("pairs");

		$this->object->purgePairs();

		$template = $this->plugin->getTemplate("tpl.pairbrowser.html");
		$this->plugin->includeClass("class.ilMatchMemoPairBrowserTableGUI.php");
		$table_gui = new ilMatchMemoPairBrowserTableGUI($this, 'pairs', (($rbacsystem->checkAccess('write', $this->ref_id) ? true : false)));
		$table_gui->setEditable($rbacsystem->checkAccess('write', $this->ref_id));
		$data = $this->object->getPairBrowserData();
		$table_gui->setData($data);
		$template->setVariable('TABLE', $table_gui->getHTML());	
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
	}
	
	public function addPair()
	{
		$id = $this->object->createEmptyPair();
		$this->ctrl->setParameter($this, 'pid', $id);
		$this->ctrl->redirect($this, 'editPair');
	}
	
	public function savePair()
	{
		if ($this->writePostData() == 0)
		{
			$this->object->savePair();
			ilUtil::sendInfo($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'editPair');
		}
	}
	
	public function savePairClose()
	{
		if ($this->writePostData() == 0)
		{
			$this->object->savePair();
			ilUtil::sendInfo($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'pairs');
		}
	}
	
	public function savePairNew()
	{
		if ($this->writePostData() == 0)
		{
			$this->object->savePair();
			ilUtil::sendInfo($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->setParameter($this, 'pid', '');
			$this->ctrl->redirect($this, 'addPair');
		}
	}
	
	/**
	* Evaluates a posted edit form and writes the form data in a memory pair object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	*/
	public function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editPair(true) : false;
		if (!$hasErrors)
		{
			include_once './Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
			$this->plugin->includeClass("class.ilMatchMemoPair.php");
			$this->object->pair = new ilMatchMemoPair(
				ilUtil::stripSlashes($_POST["title"]),
				ilUtil::stripSlashes($_POST["author"]),
				ilUtil::stripSlashes($_POST["description"]),
				ilUtil::stripSlashes($_POST["card1"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory")),
				ilUtil::stripSlashes($_POST["card2"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory")),
				ilUtil::stripSlashes($_POST["solution"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory"))
			);
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editPair($checkonly = FALSE)
	{
		global $ilTabs;
		$ilTabs->activateTab("pairs");

		$this->ctrl->saveParameter($this, 'pid');
		$save = ((strcmp($this->ctrl->getCmd(), "savePair") == 0) || (strcmp($this->ctrl->getCmd(), "savePairClose") == 0) || (strcmp($this->ctrl->getCmd(), "savePairNew") == 0)) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->txt('edit_pair'));
		$form->setTableWidth("100%");
		$form->setId("pairform");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->pair->title);
		$title->setRequired(TRUE);
		$form->addItem($title);

		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->pair->author);
		$author->setRequired(TRUE);
		$form->addItem($author);

		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "description");
		$description->setValue($this->object->pair->description);
		$description->setRequired(FALSE);
		$form->addItem($description);

		// card1
		$card1 = new ilTextAreaInputGUI($this->txt("card") . " 1", "card1");
		$card1->setValue(ilUtil::prepareTextareaOutput($this->object->pair->card1));
		$card1->setRequired(TRUE);
		$card1->setRteTagSet('full');
		$card1->setRows(10);
		$card1->setCols(80);
		$card1->setUseRte(TRUE);
		$card1->addPlugin("latex");
		$card1->addButton("latex");
		$card1->addButton("pastelatex");
		$card1->setRTESupport($_GET['pid'], "mpl", "memory", null, false);
		$form->addItem($card1);

		// card2
		$card2 = new ilTextAreaInputGUI($this->txt("card") . " 2", "card2");
		$card2->setValue(ilUtil::prepareTextareaOutput($this->object->pair->card2));
		$card2->setRequired(TRUE);
		$card2->setRteTagSet('full');
		$card2->setRows(10);
		$card2->setCols(80);
		$card2->setUseRte(TRUE);
		$card2->addPlugin("latex");
		$card2->addButton("latex");
		$card2->addButton("pastelatex");
		$card2->setRTESupport($_GET['pid'], "mpl", "memory", null, false);
		$form->addItem($card2);

		// solution
		$solution = new ilTextAreaInputGUI($this->txt("pair_solution"), "solution");
		$solution->setValue(ilUtil::prepareTextareaOutput($this->object->pair->solution));
		$solution->setRequired(false);
		$solution->setRteTagSet('full');
		$solution->setRows(10);
		$solution->setCols(80);
		$solution->setUseRte(TRUE);
		$solution->addPlugin("latex");
		$solution->addButton("latex");
		$solution->addButton("pastelatex");
		$solution->setRTESupport($_GET['pid'], "mpl", "memory", null, false);
		$form->addItem($solution);

		$form->addCommandButton("savePair", $this->lng->txt("save"));
		$form->addCommandButton("savePairClose", $this->txt("save_close"));
		$form->addCommandButton("savePairNew", $this->txt("save_new"));
		$form->addCommandButton("pairs", $this->lng->txt("cancel"));
	
		$errors = false;
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if ($errors) $checkonly = false;
		}
		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	public function deletePairs()
	{
		if (count($_POST['p_id']))
		{
			foreach ($_POST['p_id'] as $id)
			{
				$this->object->deletePair($id);
			}

			ilUtil::sendInfo($this->txt("msg_pairs_deleted"), true);

			if(
				strlen($this->object->getOnline()) &&
				ilObjMatchMemoPool::_lookupPairCount($this->obj_id) < ilObjMatchMemoPool::MIN_PAIRS_NUM
			)
			{
				$this->object->setOnline(false);
				$this->object->update();
				ilUtil::sendFailure($this->txt("set_offline_not_enough_pairs"), true);
			}

			$this->ctrl->redirect($this, 'pairs');
		}
		else
		{
			ilUtil::sendInfo($this->txt("msg_pairs_no_selection"), true);
			$this->ctrl->redirect($this, 'pairs');
		}
	}
	
	/**
	* Save questionpool properties
	*/
	function saveProperties()
	{
		if(
			strlen($_POST['online']) &&
			ilObjMatchMemoPool::_lookupPairCount($this->obj_id) < ilObjMatchMemoPool::MIN_PAIRS_NUM
		)
		{
			$this->object->setOnline(0);
			$this->object->doUpdate();
			ilUtil::sendInfo($this->txt("cannot_set_online_not_enough_pairs"), true);
			$this->ctrl->redirect($this, "editProperties");
		}

		$this->object->setOnline(strlen($_POST["online"]) ? $_POST["online"] : 0);
		$this->object->doUpdate();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "editProperties");
	}

	function importCSV()
	{
		$this->importPairs();
	}

	function importPairs()
	{
		global $ilTabs;
		$ilTabs->activateTab("pairs");

		$save = ((strcmp($this->ctrl->getCmd(), "importCSV") == 0)) ? true : false;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'importCSV'));
		$form->setTitle($this->lng->txt("import"));
		$form->setMultipart(true);
		$form->setId("import");

		$upload = new ilFileInputGUI($this->txt("upload_file"), "upload");
		$upload->setInfo($this->txt("upload_file_description"));
		$upload->setRequired(true);
		$upload->setSuffixes(array('txt','csv'));
		$form->addItem($upload);

		$form->addCommandButton("importCSV", $this->lng->txt("import"));
		$form->addCommandButton("pairs", $this->lng->txt("cancel"));

		if ($save)
		{
			if ($form->checkInput())
			{
				$temp_name = $_FILES['upload']["tmp_name"];
				$content = file($temp_name, FILE_SKIP_EMPTY_LINES + FILE_TEXT);
				$imported = 0;
				if (is_array($content))
				{
					foreach ($content as $line)
					{
						$items = preg_split('{(?<!\\\);}', $line);
						$card1 = str_replace("\\;", ";", $items[0]);
						$card2 = str_replace("\\;", ";", $items[1]);
						$solution = str_replace("\\;", ";", $items[2]);
						$title = str_replace("\\;", ";", $items[3]);
						$author = str_replace("\\;", ";", $items[4]);
						$description = str_replace("\\;", ";", $items[5]);
						if ($card1 && $card2)
						{
							include_once './Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
							$this->plugin->includeClass("class.ilMatchMemoPair.php");
							$this->object->createEmptyPair(
								ilUtil::stripSlashes(($title) ? $title : $this->txt('import_no_title')),
								ilUtil::stripSlashes(($author) ? $author : $this->txt('import_unknown')),
								ilUtil::stripSlashes(($description) ? $description : $this->txt('import_no_description')),
								ilUtil::stripSlashes($card1, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory")),
								ilUtil::stripSlashes($card2, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory")),
								ilUtil::stripSlashes($solution, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory"))
							);
							$imported++;
						}
					}
				}
				if ($imported > 0)
				{
					ilUtil::sendInfo(sprintf($this->txt('mpl_pairs_imported'), $imported), true);
					$this->object->updatePairCount();
					$this->ctrl->redirect($this, 'pairs');
				}
				else
				{
					ilUtil::sendInfo($this->txt('mpl_nothing_imported'));
				}
			}
		}
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	* copy one or more memory pair objects to the clipboard
	*/
	function copy()
	{
		if (count($_POST["p_id"]) > 0)
		{
			foreach ($_POST["p_id"] as $key => $value)
			{
				$this->object->copyToClipboard($value);
			}
			ilUtil::sendInfo($this->txt("copy_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->txt("copy_select_none"), true);
		}
		$this->ctrl->redirect($this, "pairs");
	}
	
	/**
	* mark one or more memory pair objects for moving
	*/
	function move()
	{
		if (count($_POST["p_id"]) > 0)
		{
			foreach ($_POST["p_id"] as $key => $value)
			{
				$this->object->moveToClipboard($value);
			}
			ilUtil::sendInfo($this->txt("move_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->txt("move_select_none"), true);
		}
		$this->ctrl->redirect($this, "pairs");
	}

	/**
	* paste memory pairs from the clipboard into the memory pool
	*/
	function paste()
	{
		if($this->object->clipboardContainsValidItems())
		{
			$this->object->pasteFromClipboard();
			ilUtil::sendInfo($this->txt("paste_success"), true);
		}
		else
		{
			ilUtil::sendInfo($this->txt("paste_no_objects"), true);
		}
		$this->ctrl->redirect($this, "pairs");
	}

}
?>