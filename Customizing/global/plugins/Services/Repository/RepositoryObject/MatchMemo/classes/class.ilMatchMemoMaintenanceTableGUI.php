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
* @version $Id: class.ilMatchMemoMaintenanceTableGUI.php 21327 2009-08-27 16:54:33Z hschottm $
*
* @ingroup ModulesMatchMemoPool
*/

class ilMatchMemoMaintenanceTableGUI extends ilTable2GUI
{
	protected $writeAccess = false;
	protected $delete = false;
	protected $plugin;

	/**
	 * ilMatchMemoMaintenanceTableGUI constructor.
	 * @param            $a_parent_obj
	 * @param string     $a_parent_cmd
	 * @param bool|false $a_write_access
	 * @param bool|false $delete
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false, $delete = false)
	{
		global $lng, $ilCtrl;

		$this->setId('mm_maint');
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng    = $lng;
		$this->ctrl   = $ilCtrl;
		$this->delete = $delete;
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemo");
	
		$this->setWriteAccess($a_write_access);
		$this->setTitle($this->plugin->txt('participants'));
		$this->setFormName('maintenanceForm');
		$this->setStyle('table', 'fullwidth');
		if(!$delete)
		{
			$this->addColumn('','f','1%');
			$this->addColumn($this->plugin->txt("rank"),'rank', '');
		}
		$this->addColumn($this->plugin->txt("moves"),'moves', '');
		$this->addColumn($this->plugin->txt("memory_nickname"),'nickname', '');
		$this->addColumn($this->plugin->txt("cards"),'cards', '');
		$this->addColumn($this->lng->txt("time"),'time', '');
		$this->addColumn($this->plugin->txt("topic"),'topic', '');
		$this->addColumn($this->lng->txt("level"),'level', '');

		$this->setRowTemplate("tpl.maintenance_row.html", 'Customizing/global/plugins/Services/Repository/RepositoryObject/MatchMemo');

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("rank");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('header');
		$this->enable('select_all');
		if ($delete) $this->disable('sort');
	}

	/**
	 * @param array $data
	 */
	public function populate(array $data)
	{
		if($this->getWriteAccess())
		{
			if(!$this->delete)
			{
				$this->addMultiCommand('deleteResults', $this->lng->txt('delete'));
				if(count($data) > 0)
				{
					$this->addCommandButton('deleteAllResults', $this->lng->txt('delete_all'));
				}
				$this->setPrefix('p_id');
				$this->setSelectAllCheckbox('p_id');
			}
			else
			{
				$this->addCommandButton('confirmDeleteSelected', $this->lng->txt('confirm'));
				$this->addCommandButton('cancelDeleteSelected', $this->lng->txt('cancel'));
			}
		}

		$this->setData($data);
	}

	/**
	 * @param array $data
	 */
	public function fillRow($data)
	{
		global $lng;
		
		if (!$this->delete)
		{
			$this->tpl->setCurrentBlock('check');
			$this->tpl->setVariable('PARTICIPANT_ID', $data['id']);
			$this->tpl->setVariable("RANK", $data["rank"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('hidden');
			$this->tpl->setVariable('PARTICIPANT_ID', $data['id']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("MOVES", ilUtil::prepareFormOutput($data["moves"]));
		$this->tpl->setVariable('LABEL_PARTICIPANT_ID', $data['id']);
		$this->tpl->setVariable("NICKNAME", ilUtil::prepareFormOutput($data["nickname"]));
		$this->tpl->setVariable("CARDS", ilUtil::prepareFormOutput($data["cards"]));
		$this->tpl->setVariable("TIME", ilUtil::prepareFormOutput($data["time"]));
		$this->tpl->setVariable("TOPIC", ilUtil::prepareFormOutput($data["topic"]));
		$level = '';
		switch ($data['level'])
		{
			case 0:
				$level = $this->plugin->txt('level_easy');
				break;
			case 1:
				$level = $this->plugin->txt('level_medium');
				break;
			case 2:
				$level = $this->plugin->txt('level_hard');
				break;
		}
		$this->tpl->setVariable("LEVEL", ilUtil::prepareFormOutput($level));
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