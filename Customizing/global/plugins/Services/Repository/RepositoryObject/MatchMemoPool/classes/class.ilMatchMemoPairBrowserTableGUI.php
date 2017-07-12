<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id: class.ilMatchMemoPairBrowserTableGUI.php 22272 2009-11-03 09:15:47Z hschottm $
*
* @ingroup ModulesMatchMemoPool
*/

class ilMatchMemoPairBrowserTableGUI extends ilTable2GUI
{
	protected $editable = true;
	protected $writeAccess = false;
	protected $totalPoints = 0;
	protected $browsercolumns = array();
	protected $plugin;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemoPool");
	
		$this->setWriteAccess($a_write_access);
		$this->setTitle($this->plugin->txt('pair_browser'));
		$this->setFormName('pairbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("title"),'title', '');
		$this->addColumn('','edit', '');
		$this->addColumn($this->lng->txt("description"),'description', '');
		$this->addColumn($this->lng->txt("author"),'author', '');
		$this->addColumn($this->lng->txt("create_date"),'created', '');
		$this->addColumn($this->lng->txt("last_update"),'updated', '');

		$this->setPrefix('p_id');
		$this->setSelectAllCheckbox('p_id');
		
		if ($this->getWriteAccess())
		{
			$this->addMultiCommand('copy', $this->lng->txt('copy'));
			$this->addMultiCommand('move', $this->lng->txt('move'));
//			$this->addMultiCommand('exportQuestion', $this->lng->txt('export'));
			$this->addMultiCommand('deletePairs', $this->lng->txt('delete'));
			if($a_parent_obj->object->clipboardContainsValidItems())
			{
				$this->addCommandButton('paste', $this->lng->txt('paste'));
			}
			$this->addCommandButton('addPair', $this->plugin->txt('pair_add'));
			$this->addCommandButton('importPairs', $this->lng->txt('import'));
		}


		$this->setRowTemplate("tpl.pairbrowser_row.html", 'Customizing/global/plugins/Services/Repository/RepositoryObject/MatchMemoPool');

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('select_all');
	}
	
	function fillHeader()
	{
		foreach ($this->column as $key => $column)
		{
			if (strcmp($column['text'], $this->lng->txt("points")) == 0)
			{
				$this->column[$key]['text'] = $this->lng->txt("points") . "&nbsp;(" . $this->totalPoints . ")";
			}
		}
		parent::fillHeader();
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		if ($this->getEditable())
		{
			$this->tpl->setCurrentBlock("edit_link");
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->ctrl->setParameterByClass('ilobjmatchmemopoolgui', 'pid', $data['pair_id']);
			$this->tpl->setVariable("LINK_EDIT", $this->ctrl->getLinkTargetByClass("ilobjmatchmemopoolgui", "editPair"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("DESCRIPTION", (strlen($data["description"])) ? $data["description"] : "&nbsp;");
		$this->tpl->setVariable("AUTHOR", ilUtil::prepareFormOutput($data["author"]));
		$this->tpl->setVariable("CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
		$this->tpl->setVariable("UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"],IL_CAL_UNIX)));

		$this->tpl->setVariable('PAIR_ID', $data["pair_id"]);
		$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($data["title"]));
	}
	
	public function setEditable($value)
	{
		$this->editable = $value;
	}
	
	public function getEditable()
	{
		return $this->editable;
	}

	public function setWriteAccess($value)
	{
		$this->writeAccess = $value;
	}
	
	public function getWriteAccess()
	{
		return $this->writeAccess;
	}
}
?>