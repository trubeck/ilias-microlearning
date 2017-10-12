<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 */
class ilTextNuggetPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xtxt";

	// must correspond to the plugin subdirectory
	function getPluginName()
	{
		return "TextNugget";
	}

	protected function uninstallCustom() {
		// TODO: Nothing to do here.
	}
}
?>