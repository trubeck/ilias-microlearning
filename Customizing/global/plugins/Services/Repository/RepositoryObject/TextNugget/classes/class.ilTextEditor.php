<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Text Editor object
 * 
 *
 */
class ilTextEditor extends ilPageObject
{
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "txte";
	}
	
	/**
	 * Create new text block
	 */
	function create()
	{
		parent::create();			
	}

}
?>