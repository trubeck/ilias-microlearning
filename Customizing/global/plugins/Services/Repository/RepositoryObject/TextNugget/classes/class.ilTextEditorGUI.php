<?php


include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilTextEditor.php");

/**
 * Class ilTextEditor GUI class
 * 
 * @ilCtrl_Calls ilTextEditorGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilTextEditorGUI: ilRatingGUI, ilPublicUserProfileGUI, ilPageObjectGUI, ilNoteGUI
 *
 */
class ilTextEditorGUI extends ilPageObjectGUI
{
	/** @var  ilTabsGUI */
	protected $tabs;

	/**
	 * Constructor
	 *
	 * @param int $a_node
	 * @param object $a_access_handler
	 * @param int $a_id
	 * @param int $a_old_nr
	 * @param bool $a_enable_notes
	 * @param bool $a_may_contribute
	 * @return ilBlogPostingGUI
	 */
	function __construct($a_id = 0, $a_old_nr = 0, $a_lang = "")
	{
		global $tpl, $ilTabs;

		$this->tabs = $ilTabs;
		//$this->tabs->clearTargets();
		parent::__construct("txte", $a_id, $a_old_nr, false, $a_lang);	
	}

	/**
	 * Get blog posting
	 *
	 * @returnilBlogPosting
	 */
	function getBlogPosting()
	{
		return $this->getPageObject();
	}

}

?>
