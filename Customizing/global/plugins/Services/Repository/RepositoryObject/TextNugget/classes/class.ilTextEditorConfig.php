<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Text Editor page configuration 
 *
 */
class ilTextEditorConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		$this->setEnablePCType("Map", true);
		$this->setEnableInternalLinks((bool)$_GET["ref_id"]); // #15668
		$this->setPreventHTMLUnmasking(false);
		$this->setEnableActivation(true);
		
		$blga_set = new ilSetting("txta");
		$this->setPreventHTMLUnmasking(!(bool)$blga_set->get("mask", false));
	}
	
}

?>
