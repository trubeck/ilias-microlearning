<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 */
class ilPalunoObjectPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xpal";

	// must correspond to the plugin subdirectory
	function getPluginName()
	{
		return "PalunoObject";
	}

	protected function uninstallCustom() {
		// TODO: Nothing to do here.
	}
}
?>