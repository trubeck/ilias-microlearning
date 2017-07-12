<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* User Interface class for Match & Memo game repository object.
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
* @ilCtrl_isCalledBy ilObjMatchMemoGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjMatchMemoGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjMatchMemoGUI: ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjMatchMemoGUI: ilMDEditorGUI
*
*/
class ilObjMatchMemoGUI extends ilObjectPluginGUI
{
	public $plugin;
	public $poolplugin;
	
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - gallery: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemo");
		$this->poolplugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xmry";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
  */
	function performCommand($cmd)
	{
		global $ilAccess, $ilTabs, $ilErr;

		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			case 'ilmdeditorgui':
				if (!$ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
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
			case "properties":		// list all commands that need write permission here
			case "writeProperties":
			case "saveProperties":
			case "themesadd":
			case "themesremove":
			case "setrowsthemes":
			case "maintenance":
			case "deleteResults":
			case "cancelDeleteSelected":
			case "deleteAllResults":
			case "cancelMaintenanceDelete":
			case "confirmDeleteAll":
			case "confirmDeleteSelected":
				$this->checkPermission("write");
				$this->$cmd();
				return;
				break;
			case "game":			// list all commands that need read permission here
			case "help":
			case "startPrevious":
			case "startNext":
			case "startGame":
			case "newgame":
			case "skipHighScore":
			case "saveHighScore":
			case "exitgame":
			case "finalScreen":
				if ($this->object->fullscreen)
				{
					global $tpl;
					$tpl = $this->plugin->getTemplate("tpl.fullscreen.html");
					$tpl->setVariable("LOCATION_STYLESHEET","./templates/default/delos.css");
					$this->tpl = $tpl;
				}
				$this->checkPermission("read");
				$this->$cmd();
				return;
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "properties";
	}

	/**
	* Get standard command
  */
	function getStandardCmd()
	{
		return "properties";
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
		$info->addProperty('Name', 'Match & Memo Spiel');
		$info->addProperty('Version', xmry_version);
		$info->addProperty('Developer', 'Helmut Schottmüller / Michael Jansen');
		$info->addProperty("Match &amp; Memo", "Konzeption: Lt. Florian Andresen; Leitung: RA Anja Krüger, Prof. Dr. jur. Günter Reiner, Professur für Bürgerliches Recht, Handels-, Gesellschafts-, Wirtschafts- und Steuerrecht,	Helmut Schmidt Universität, Universität der Bundeswehr Hamburg");
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

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->lng->txt("properties"), $ilCtrl->getLinkTarget($this, "properties"));
		}

		if ($ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("memory_game", $this->txt("memory_game"), $ilCtrl->getLinkTarget($this, "game"));
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("maintenance", $this->txt("maintenance"), $ilCtrl->getLinkTarget($this, "maintenance"));
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
	function properties()
	{
		global $ilTabs;
		$ilTabs->setTabActive("properties");
		$this->tpl->addCss($this->plugin->getDirectory() . "/templates/memory.css", "screen");

		$save = ((strcmp($this->ctrl->getCmd(), "saveProperties") == 0)) ? true : false;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'properties'));
		$form->setTitle($this->lng->txt("properties"));
		$form->setMultipart(true);
		$form->setId("properties");
		$form->addCommandButton("saveProperties", $this->lng->txt("save"));

		// heading
		$heading = new ilTextInputGUI($this->txt("heading"), "heading");
		$heading->setSize(60);
		$heading->setValue($this->object->heading);
		$heading->setRequired(false);
		$heading->setInfo($this->txt("heading_info"));
		$form->addItem($heading);

		// intro
		$intro = new ilTextAreaInputGUI($this->txt("intro"), "intro");
		$intro->setValue(ilUtil::prepareTextareaOutput($this->object->intro));
		$intro->setRequired(false);
		$intro->setInfo($this->txt("intro_info"));
		$intro->setRteTagSet('full');
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->addPlugin("latex");
		$intro->addButton("latex");
		$intro->addButton("pastelatex");
		$intro->setRTESupport($this->object->getId(), "mry", "memory", null, false, "latest");
		$form->addItem($intro);

/*
		// image file name
		$backgroundimage = new ilHiddenInputGUI('', "backgroundimage");
		$backgroundimage->setValue($this->object->background);
		$form->addItem($backgroundimage);

		// image file
		$background = new ilImageFileInputGUI($this->lng->txt("background_image"), "background");
		if (strlen($this->object->background)) $background->setImage($this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $this->object->background);
		$background->setRequired(false);
		$form->addItem($background);
*/
		// fullscreen
		$online = new ilCheckboxInputGUI($this->txt("fullscreen"), "fullscreen");
		$online->setInfo($this->txt("fullscreen_description"));
		$online->setChecked($this->object->fullscreen);
		$form->addItem($online);

		// backlink url
		$back_url = new ilTextInputGUI($this->txt("back_url"), "back_url");
		$back_url->setSize(60);
		$back_url->setValue($this->object->back_url);
		$back_url->setRequired(false);
		$back_url->setInfo($this->txt("back_url_info"));
		$form->addItem($back_url);

		// show title
		$show_title = new ilCheckboxInputGUI('', "show_title");
		$show_title->setOptionTitle($this->txt("mry_show_title"));
		$show_title->setValue(1);
		$show_title->setChecked($this->object->show_title);
		$form->addItem($show_title);

		// only single entries in highscore
		$highscore_single = new ilCheckboxInputGUI('', "highscore_single");
		$highscore_single->setOptionTitle($this->txt("mry_highscore_single"));
		$highscore_single->setValue(1);
		$highscore_single->setInfo($this->txt("mry_highscore_single_desc"));
		$highscore_single->setChecked($this->object->highscore_single);
		$form->addItem($highscore_single);

		// scoring properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->txt("themes"));
		$form->addItem($header);

		if (!count($this->object->themes))
		{
			$this->object->addTheme();
		}
		// Themes
		$this->plugin->includeClass("class.ilMatchMemoThemeInputGUI.php");
		$themes = new ilMatchMemoThemeInputGUI($this->txt("themes"), "themes");
		$themes->setThemes($this->object->themes);
		$themes->setRequired(TRUE);
		if ($this->object->highScoresExist()) $themes->setEnabled(false);
		$form->addItem($themes);

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

	/**
	* Evaluates a posted edit form and writes the form data
	*/
	public function writeProperties($always = false)
	{
		global $ilTabs;
		$ilTabs->setTabActive("properties");

		$hasErrors = (!$always) ? $this->properties(true) : false;
		if (!$hasErrors)
		{
			include_once './Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
			$this->object->back_url = ilUtil::stripSlashes($_POST['back_url']);
			$this->object->heading = ilUtil::stripSlashes($_POST['heading']);
			$this->object->fullscreen = ilUtil::stripSlashes($_POST['fullscreen']);
			$this->object->intro = ilUtil::stripSlashes($_POST["intro"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("memory"));
			$this->object->show_title = ($_POST['show_title']) ? 1 : 0;
			$this->object->highscore_single = ($_POST['highscore_single']) ? 1 : 0;
			if ($_POST['background_delete']) $this->object->deleteBackground();
			$filename = $_POST['backgroundimage'];
			if (strlen($_FILES['background']['name']))
			{
				// upload image
				$filename = $this->object->setImageFile($_FILES['background']);
			}
			else
			{
				$filename = $_POST['backgroundimage'];
			}
			$this->object->background = $filename;
			if (!$this->object->highScoresExist())
			{
				$this->object->flushThemes();
				foreach ($_POST['themes']['title'] as $idx => $title)
				{
					$this->plugin->includeClass("class.ilMatchMemoTheme.php");
					$theme = new ilMatchMemoTheme(ilUtil::stripSlashes($title), ilUtil::stripSlashes($_POST['themes']["easy"][$idx]), ilUtil::stripSlashes($_POST['themes']["medium"][$idx]), ilUtil::stripSlashes($_POST['themes']["hard"][$idx]));
					$theme->mixedpools = $_POST['themes']['rows'][$idx];
					foreach ((array)$_POST['themes']['mixed'][$idx] as $counter => $selection)
					{
						if ($selection > 0) $theme->addMixedPool($selection, $_POST['themes']['mixed_percent'][$idx][$counter]);
					}
					$this->object->addThemeObject($theme);
				}
			}
			return 0;
		}
		else
		{
			return 1;
		}
	}

	public function saveProperties()
	{
		if ($this->writeProperties() == 0)
		{
			$this->object->doUpdate();
			if(!$this->object->highScoresExist())
			{
				ilUtil::sendInfo($this->txt("msg_obj_modified_with_pool_sync"), true);
			}
			else
			{
				ilUtil::sendInfo($this->txt("msg_obj_modified"), true);
			}
			$this->ctrl->redirect($this, 'properties');
		}
	}

	public function themesadd()
	{
		$this->writeProperties(true);
		$position = key($_POST['cmd']['themesadd']);
		$this->object->insertTheme($position+1);
		$this->properties();
	}
	
	public function themesremove()
	{
		$this->writeProperties(true);
		$position = key($_POST['cmd']['themesremove']);
		$this->object->removeTheme($position);
		$this->properties();
	}

	public function setrowsthemes()
	{
		$this->writeProperties(true);
		$themeindex = key($_POST['cmd']['setrowsthemes']);
		$this->object->themes[$themeindex]->mixedpools = $_POST['themes']['rows'][$themeindex];
		$this->properties();
	}

	protected function getHighScoreOutput($scores)
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		if (count($scores) == 0) return $this->txt('highscore_empty');
		$template = $this->plugin->getTemplate("tpl.memory_highscore.html");
		$this->plugin->includeClass("class.ilMatchMemoTheme.php");
		$themes = array();
		$i = 1;
		foreach ($scores as $row)
		{
			$template->setCurrentBlock('row');
			$template->setVariable('TEXT_RANK', "$i.");
			$template->setVariable('TEXT_MOVES', $row['moves']);
			$template->setVariable('TEXT_NAME', $row['nickname']);
			$template->setVariable('TEXT_CARDS', $row['cards']);
			$template->setVariable('TEXT_TIME', $row['time_total']);
			if (!array_key_exists($row['theme_fi'], $themes)) $themes[$row['theme_fi']] = ilMatchMemoTheme::_instanciate($row['theme_fi']);
			$topic = $themes[$row['theme_fi']]->title;
			$template->setVariable('TEXT_TOPIC', ilUtil::prepareFormOutput($topic));
			$template->parseCurrentBlock();
			$i++;
		}
		$template->setVariable('H_RANK', $this->txt('rank'));
		$template->setVariable('H_MOVES', $this->txt('moves'));
		$template->setVariable('H_NAME', $this->txt('memory_nickname'));
		$template->setVariable('H_CARDS', $this->txt('cards'));
		$template->setVariable('H_TIME', $this->txt('time_s'));
		$template->setVariable('H_TOPIC', $this->txt('topic'));
		return $template->get();
	}

	public function game()
	{
		global $ilUser;
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		$help = $this->getHelpOutput();

		$template = $this->plugin->getTemplate('tpl.memory_game_intro.html');

		if(isset($_GET['finished_level']))
		{
			$template->setVariable('FINISHED_LEVEL', (int)$_GET['finished_level']);
		}

		$template->setCurrentBlock('accordion_page');
		$template->setVariable('ACCORDION_TITLE', $this->txt('level_easy'));
		$template->setVariable('ACCORDION_CONTENT', $this->getHighScoreOutput($this->object->getHighScores(0)));
		$template->parseCurrentBlock();
		$template->setCurrentBlock('accordion_page');
		$template->setVariable('ACCORDION_TITLE', $this->txt('level_medium'));
		$template->setVariable('ACCORDION_CONTENT', $this->getHighScoreOutput($this->object->getHighScores(1)));
		$template->parseCurrentBlock();
		$template->setCurrentBlock('accordion_page');
		$template->setVariable('ACCORDION_TITLE', $this->txt('level_hard'));
		$template->setVariable('ACCORDION_CONTENT', $this->getHighScoreOutput($this->object->getHighScores(2)));
		$template->parseCurrentBlock();
		$template->setCurrentBlock('accordion_page');
		$template->setVariable('ACCORDION_TITLE', $this->txt('level_mixed'));
		$template->setVariable('ACCORDION_CONTENT', $this->getHighScoreOutput($this->object->getHighScores(3)));
		$template->parseCurrentBlock();

		if (strlen($this->object->heading))
		{
			$template->setCurrentBlock('heading');
			$template->setVariable('TEXT_HEADING', ilUtil::prepareFormOutput($this->object->heading));
			$template->parseCurrentBlock();
		}
		if (strlen($this->object->intro))
		{
			$template->setCurrentBlock('intro');
			$template->setVariable("TEXT_INTRO", ilUtil::prepareTextareaOutput($this->object->intro, TRUE));
			$template->parseCurrentBlock();
		}
		if ($this->object->complete)
		{
			$template->setCurrentBlock('newgame');
			$template->setVariable('URL_NEWGAME', $this->ctrl->getLinkTarget($this, 'newgame'));
			$template->setVariable('TEXT_NEWGAME', $this->txt('new_game'));
			$template->parseCurrentBlock();
		}
		$template->setVariable('URL_HELP', $this->ctrl->getLinkTarget($this, 'help'));
		$template->setVariable('TEXT_HELP', $this->txt('memory_help'));
		$template->setVariable('URL_HIGHSCORE', $this->ctrl->getLinkTarget($this, 'highscores'));
		$template->setVariable('TEXT_HIGHSCORE', $this->txt('highscore'));
		$template->setVariable('HELP_TEXT', $help);
		$template->setVariable('HELP_TITLE', $this->lng->txt('help'));

		if($this->object->fullscreen)
		{
			$template->setVariable('TEXT_FINISH', $this->txt('exit_game'));
			$template->setVariable('URL_FINISH', $this->ctrl->getLinkTarget($this, 'exitgame'));
		}

		$this->tpl->setVariable($this->getContentBlockName(), $template->get());

		ilObjMatchMemoGUI::initMatchMemo();
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/accordion.js");
	}
	
	static function initMatchMemo()
	{
		global $tpl;
		
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		$tpl->addCss(ilYuiUtil::getLocalPath("button") ."/assets/skins/sam/button.css");
		$tpl->addCss(ilYuiUtil::getLocalPath("container") ."/assets/skins/sam/container.css");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("yahoo-dom-event") ."/yahoo-dom-event.js");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("element") ."/element-min.js");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("button") ."/button-min.js");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("container") ."/container-min.js");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("dragdrop") ."/dragdrop-min.js");
		$tpl->addJavaScript(ilYuiUtil::getLocalPath("animation") ."/animation-min.js");
	}
	
	public function getHelpOutput()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng, $ilTabs;

		$ilTabs->setTabActive("memory_game");

		$help = "";
		$path = $this->plugin->getDirectory() . "/templates/help_" . $lng->getLangKey() . ".html";
		if(@file_exists($path))
		{
			$tplhelp = $this->plugin->getTemplate('help_' . $lng->getLangKey()  . '.html');
			$tplhelp->touchBlock("__global__");
			$tplhelp->parseCurrentBlock();
			$help = $tplhelp->get();
		}
		else
		{
			$tplhelp = $this->plugin->getTemplate('help_en.html');
			$tplhelp->touchBlock("__global__");
			$tplhelp->parseCurrentBlock();
			$help = $tplhelp->get();
		}
		return $help;
	}

	public function help()
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		$this->tpl->setVariable('ADM_CONTENT', $this->getHelpOutput());
	}

	public function startPrevious()
	{
		$this->newgame(-1);
	}
	
	public function startNext()
	{
		$this->newgame(1);
	}

	public function newgame($direction = 0)
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'startGame'));
		$form->setTitle($this->txt("game_start"));
		$form->setTableWidth(500);
		$form->setMultipart(false);
		$form->setId("game_start_form");
		$step = (strlen($_POST['step'])) ? $_POST['step'] : 1;
		$step += $direction;

		$first_step_available  = true;
		$second_step_available = true;

		if(
			$step < 3 &&
			is_array($this->object->themes) && count($this->object->themes) == 1
		)
		{
			$step                 = 2;
			$_POST['topic']       = $this->object->themes[0]->id;
			$first_step_available = false;
		}

		if($step < 3)
		{
			$this->plugin->includeClass("class.ilMatchMemoTheme.php");
			$theme = ilMatchMemoTheme::_instanciate($_POST['topic']);
			if($theme)
			{
				$theme_levels = array_filter(array(
					0 => (bool)$theme->easy,
					1 => (bool)$theme->medium,
					2 => (bool)$theme->hard,
					3 => (bool)$theme->hasMixedPools()
				));
				if(count($theme_levels) == 1)
				{
					if($direction == -1 && $step == 2)
					{
						$step = 1;
					}
					else
					{
						$step = 3;
					}
					$_POST['level']        = key($theme_levels);
					$second_step_available = false;
				}
			}
		}

		$hidden = new ilHiddenInputGUI("step");
		$hidden->setValue($step);
		$form->addItem($hidden);
		
		if($step == 3)
		{
			if(!$first_step_available && !$second_step_available)
			{
				$form->addCommandButton("game", $this->lng->txt("back"));
			}
			else
			{
				$form->addCommandButton("startPrevious", $this->lng->txt("previous"));
			}
			$form->addCommandButton("startGame", $this->txt("start"));
		}
		else if($step == 2)
		{
			if(!$first_step_available)
			{
				$form->addCommandButton("game", $this->lng->txt("previous"));
			}
			else
			{
				$form->addCommandButton("startPrevious", $this->lng->txt("previous"));
			}
			$form->addCommandButton("startNext", $this->lng->txt("next"));
		}
		else
		{
			$form->addCommandButton("game", $this->lng->txt("previous"));
			$form->addCommandButton("startNext", $this->lng->txt("next"));
		}

		// topic
		$var = ($step > 1) ? 'topicdisabled' : 'topic';
		$topic = new ilRadioGroupInputGUI($this->txt("topic"), $var);
		$i = 0;
		foreach ($this->object->themes as $theme)
		{
			$topic->addOption(new ilRadioOption($theme->title, $theme->id));
			if ($i == 0) $topic->setValue($theme->id);
			$i++;
		}
		if ($_POST['topic']) $topic->setValue($_POST['topic']);
		$topic->setRequired(true);
		if ($step > 1) 
		{
			$topic->setDisabled(true);
		}
		$form->addItem($topic);
		if ($step > 1)
		{
			$topic_hidden = new ilHiddenInputGUI("topic");
			$topic_hidden->setValue($_POST['topic']);
			$form->addItem($topic_hidden);
		}

		if ($step > 1)
		{
			$this->plugin->includeClass("class.ilMatchMemoTheme.php");
			$theme = ilMatchMemoTheme::_instanciate($_POST['topic']);
			// level
			$var = ($step > 2) ? 'leveldisabled' : 'level';
			$level      = new ilRadioGroupInputGUI($this->lng->txt("level"), $var);
			$levels     = array();
			if($theme->easy)
			{
				$level->addOption(new ilRadioOption($this->txt('level_easy'), 0));
				$levels[] = 0;
			}
			if($theme->medium)
			{
				$level->addOption(new ilRadioOption($this->txt('level_medium'), 1));
				$levels[] = 1;
			}
			if($theme->hard)
			{
				$level->addOption(new ilRadioOption($this->txt('level_hard'), 2));
				$levels[] = 2;
			}
			if($theme->hasMixedPools())
			{
				$level->addOption(new ilRadioOption($this->txt('level_mixed'), 3));
				$levels[] = 3;
			}
			$level->setValue(isset($_POST['level']) ? $_POST['level'] : (count($levels) == 1 ? $levels[0] : ''));
			$level->setRequired(true);
			if($step > 2) 
			{
				$level->setDisabled(true);
			}
			$form->addItem($level);
			if ($step > 2)
			{
				$level_hidden = new ilHiddenInputGUI("level");
				$level_hidden->setValue($_POST['level']);
				$form->addItem($level_hidden);
			}
		}

		if ($step > 2)
		{
			// cards
			$cards = new ilRadioGroupInputGUI($this->txt("cards"), 'cards');
			$cards->addOption(new ilRadioOption(16, 16));
			$cards->setValue(16);
			$cards->addOption(new ilRadioOption(24, 24));
			$cards->addOption(new ilRadioOption(32, 32));
			$cards->setRequired(true);
			if (strlen($_POST['cards'])) $cards->setValue($_POST['cards']);
			$form->addItem($cards);
		}
		$this->tpl->setVariable($this->getContentBlockName(), $form->getHTML());
	}

	public function startGame()
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		$size = $_POST['cards'];
		$level = $_POST['level'];
		$topic = $_POST['topic'];
		if ($size < 16 || $topic < 1)
		{
			$this->ctrl->redirect($this, 'newgame');
		}
		$this->plugin->includeClass("class.ilMatchMemoTheme.php");
		$theme = ilMatchMemoTheme::_instanciate($topic);
		$themelevel = 0;
		switch ($level)
		{
			case 1:
				$themelevel = $theme->medium;
				break;
			case 2:
				$themelevel = $theme->hard;
				break;
			case 3:
				$themelevel = $theme->mixed;
				break;
			case 0:
			default:
				$themelevel = $theme->easy;
				break;
		}
		$template = $this->plugin->getTemplate('tpl.memory_game.html');
		$this->poolplugin->includeClass("class.ilObjMatchMemoPool.php");
		$pairs = ilObjMatchMemoPool::_randomPairsForGame($this->object->getId(), $themelevel, $size / 2);
		// create ID mapping table
		$mapping = array();
		$randomvalues = array();
		for ($i = 0; $i < $size + 30; $i++) array_push($randomvalues, mt_rand());
		$randomvalues = array_unique($randomvalues);
		$counter = 0;
		foreach ($pairs as $pair)
		{
			$mapping[$pair] = array($randomvalues[$counter++], $randomvalues[$counter++]);
		}
		$_SESSION['memory_mapping'][$this->object->getId()] = $mapping;
		$shuffledvalues = array();
		$this->poolplugin->includeClass("class.ilMatchMemoPair.php");
		foreach ($mapping as $pair => $tuple)
		{
			$pair = ilMatchMemoPair::_loadFromDB($pair);
			array_push($shuffledvalues, $tuple[0]);
			$template->setCurrentBlock('card');
			$template->setVariable('CARD_CONTENT_ID', $tuple[0]);
			$replacement = ($this->object->isHTML($pair->card1)) ? " " : "<br />";
			$template->setVariable('CARD_CONTENT_TEXT', preg_replace("/[\n\r]+/", $replacement, preg_replace("/'/", "\'", $pair->card1)));
			$template->parseCurrentBlock();
			$template->setCurrentBlock('card');
			$template->setVariable('CARD_CONTENT_ID', $tuple[1]);
			$replacement = ($this->object->isHTML($pair->card2)) ? " " : "<br />";
			$template->setVariable('CARD_CONTENT_TEXT', preg_replace("/[\n\r]+/", $replacement, preg_replace("/'/", "\'", $pair->card2)));
			$template->parseCurrentBlock();
			array_push($shuffledvalues, $tuple[1]);
			$template->setCurrentBlock('pair');
			$template->setVariable('LEFT', $tuple[0]);
			$template->setVariable('RIGHT', $tuple[1]);
			$replacement = ($this->object->isHTML($pair->solution)) ? " " : "<br />";
			$template->setVariable('SOLUTION', preg_replace("/[\n\r]+/", $replacement, preg_replace("/'/", "\'", $pair->solution)));
			$template->parseCurrentBlock();
		}
		shuffle($shuffledvalues);

		$counter = 0;
		switch ($size)
		{
			case 16:
				$rowcount = 4;
				$colcount = 4;
				break;
			case 24:
				$rowcount = 4;
				$colcount = 6;
				break;
			case 32:
				$rowcount = 4;
				$colcount = 8;
				break;
		}

		for ($rows = 0; $rows < $rowcount; $rows++)
		{
			for ($columns = 0; $columns < $colcount; $columns++)
			{
				$template->setCurrentBlock('column');
				$template->setVariable('CARD_ID', $shuffledvalues[$counter]);
				$template->parseCurrentBlock();
				$counter++;
			}
			$template->touchBlock('row');
		}

		if (strlen($this->object->background))
		{
			$template->setCurrentBlock('background');
			$template->setVariable('BACKGROUND_IMAGE', $this->object->getImagePathWeb() . $this->object->background);
			$template->parseCurrentBlock();
		}

		if ($this->object->show_title)
		{
			$template->setVariable('GAME_TITLE', ilUtil::prepareFormOutput($this->object->getTitle()));
		}
		else
		{
			$template->setVariable('GAME_TITLE', '&nbsp;');
		}
		$template->setVariable('FOUND_TITLE', ilUtil::prepareFormOutput($this->txt('found_pair')));
		$template->setVariable('BUTTON_CONTINUE', ilUtil::prepareFormOutput($this->lng->txt('continue')));
		$template->setVariable('TEXT_MOVES', $this->txt('nr_of_moves'));
		$template->setVariable('FINISH_MESSAGE', $this->txt('memory_finished'));
		$template->setVariable('TEXT_NICKNAME', $this->txt('memory_nickname'));
		$template->setVariable('SAVE_HIGHSCORE', $this->txt('memory_save_highscore'));
		$template->setVariable('SKIP_HIGHSCORE', $this->txt('memory_skip_highscore'));
		$template->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'saveHighScore'));
		$template->setVariable('TIME_START', time());
		$template->setVariable('TEXT_LEVEL', $level);
		$template->setVariable('TEXT_CARDS', $size);
		$template->setVariable('TEXT_TOPIC', $topic);
		if($this->object->fullscreen)
		{
			$template->setVariable('CANCEL_GAME', $this->txt('exit_game'));
			$template->setVariable('CANCEL_URL', $this->ctrl->getLinkTarget($this, 'infoScreen'));
		}
		$this->tpl->setVariable($this->getContentBlockName(), $template->get());

		$this->tpl->setVariable('BODY_ATTRIBUTES', ' id="body"');

		ilObjMatchMemoGUI::initMatchMemo();
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/memory.js");
	}

	public function saveHighScore()
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		$moves = $_POST['movecount'];
		$nickname = $_POST['nickname'];
		$level = $_POST['level'];
		$cards = $_POST['cards'];
		$topic = $_POST['topic'];
		$startingtime = $_POST['start'];
		$endingtime = $_POST['stop'];
		$this->object->saveHighScore($moves, $startingtime, $endingtime, $level, $topic, $cards, $nickname);
		$this->ctrl->setParameter($this, 'finished_level', $level);
		$this->ctrl->redirect($this, 'game');
	}
	
	public function skipHighScore()
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

//		$moves = $_POST['movecount'];
//		$level = $_POST['level'];
//		$topic = $_POST['topic'];
//		$startingtime = $_POST['start'];
//		$cards = $_POST['cards'];
//		$endingtime = $_POST['stop'];
//		$this->object->saveHighScore($moves, $startingtime, $endingtime, $level, $topic, $cards, NULL);
//		$this->ctrl->setParameter($this, 'finished_level', $level);
		$this->ctrl->redirect($this, 'game');
	}

	public function exitgame()
	{
		if (strlen($this->object->back_url))
		{
			ilUtil::redirect($this->object->back_url);
		}
		else
		{
			$this->ctrl->redirect($this, 'infoScreen');
		}
	}

	public function finalScreen()
	{
		global $ilTabs;
		$ilTabs->setTabActive("memory_game");

		$template = $this->plugin->getTemplate('tpl.memory_finalscreen.html');
		$template->setVariable('QUESTION_RESTART', $this->txt('restart_question'));
		$template->setVariable('TEXT_RESTART', $this->txt('new_game'));
		$template->setVariable('TEXT_FINISH', $this->txt('exit_game'));
		$template->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'newgame'));
		$this->tpl->setVariable($this->getContentBlockName(), $template->get());
	}

	public function maintenance()
	{
		global $rbacsystem;
		global $ilTabs;
		$ilTabs->setTabActive("maintenance");

		$this->plugin->includeClass("class.ilMatchMemoMaintenanceTableGUI.php");
		$table_gui = new ilMatchMemoMaintenanceTableGUI($this, 'maintenance', (($rbacsystem->checkAccess('write', $this->ref_id) ? true : false)));
		$data = $this->object->getMaintenanceData();
		$table_gui->populate($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	 * @return bool
	 */
	public function cancelDeleteSelected()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, 'maintenance');
	}

	public function deleteResults()
	{
		global $ilTabs;
		$ilTabs->setTabActive("maintenance");

		if (!is_array($_POST['p_id']) || count($_POST['p_id']) == 0)
		{
			ilUtil::sendInfo($this->txt('msg_no_selection'), true);
			$this->ctrl->redirect($this, 'maintenance');
		}
		global $rbacsystem;
		ilUtil::sendQuestion($this->txt("confirm_delete_selected_data"));
		$this->plugin->includeClass("class.ilMatchMemoMaintenanceTableGUI.php");
		$table_gui = new ilMatchMemoMaintenanceTableGUI($this, 'maintenance', (($rbacsystem->checkAccess('write', $this->ref_id) ? true : false)), true);
		$data = $this->object->getMaintenanceData($_POST['p_id']);
		$table_gui->populate($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	public function deleteAllResults()
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 */
		global $ilTabs;

		$ilTabs->setTabActive("maintenance");

		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'maintenance'));
		$confirmation->setHeaderText($this->txt('confirm_delete_all_data'));
		$confirmation->setConfirm($this->lng->txt('confirm'), 'confirmDeleteAll');
		$confirmation->setCancel($this->lng->txt('cancel'), 'cancelMaintenanceDelete');

		$this->tpl->setContent($confirmation->getHTML());
	}

	public function cancelMaintenanceDelete()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		$this->ctrl->redirect($this, 'maintenance');
	}
	
	public function confirmDeleteAll()
	{
		$this->object->deleteAllData();
		ilUtil::sendInfo($this->txt('msg_all_data_deleted'), true);
		$this->ctrl->redirect($this, 'maintenance');
	}
	
	public function confirmDeleteSelected()
	{
		$this->object->deleteSelectedData($_POST['p_id']);
		ilUtil::sendInfo($this->txt('msg_selected_data_deleted'), true);
		$this->ctrl->redirect($this, 'maintenance');
	}

	/**
	* Returns the name of the current content block (depends on the kiosk mode setting)
	*
	* @return string The name of the content block
	* @access public
	*/
	private function getContentBlockName()
	{
		if ($this->object->fullscreen)
		{
			$this->tpl->setAddFooter(FALSE);
			require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
			iljQueryUtil::initjQuery($this->tpl);
			$this->tpl->addCss($this->plugin->getDirectory() . "/templates/memory.css", "screen");
//			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "memory.css", 'Customizing/global/plugins/Services/Repository/RepositoryObject/MatchMemo'), "screen");
			$this->tpl->setBodyClass('yui-skin-sam fullscreen');
			return "ADM_CONTENT";
		}
		else
		{
			$this->tpl->addCss($this->plugin->getDirectory() . "/templates/memory.css", "screen");
			$this->tpl->setBodyClass('yui-skin-sam');
			return "ADM_CONTENT";
		}
	}
}

?>