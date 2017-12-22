<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/**
* Meta Data class (element responsible)
*
* @author Markus Heikamp
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDResponsible extends ilMDBase
{
	// SET/GET
	function setResponsible($a_responsible)
	{
		global $ilLog; 	
		include_once "Services/Logging/classes/class.ilLog.php";
		//$ilLog->write("hallo1 ".$a_responsible);
		$this->responsible_role = $a_responsible;
		//$ilLog->write("hallo2 ".$this->responsible_role);
	}
	function getResponsible()
	{
		return $this->responsible_role;
	}

	function save()
	{
		global $ilDB;
		
		$fields = $this->__getFields();
		$fields['meta_responsible_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_responsible'));
		
		if($this->db->insert('il_meta_responsible',$fields))
		{
			$this->setMetaId($next_id);
			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			if($this->db->update('il_meta_responsible',
									$this->__getFields(),
									array("meta_responsible_id" => array('integer',$this->getMetaId()))))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_responsible ".
				"WHERE meta_responsible_id = ".$ilDB->quote($this->getMetaId(),'integer');
			$res = $ilDB->manipulate($query);
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> array('integer',$this->getRBACId()),
					 'obj_id'	=> array('integer',$this->getObjId()),
					 'obj_type'	=> array('text',$this->getObjType()),
					 'parent_type' => array('text',$this->getParentType()),
					 'parent_id' => array('integer',$this->getParentId()),
					 'responsible_role'	=> array('text',$this->getResponsible()));
	}

	function read()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_responsible ".
				"WHERE meta_responsible_id = ".$ilDB->quote($this->getMetaId() ,'integer');

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setParentId($row->parent_id);
				$this->setParentType($row->parent_type);
				$this->setResponsible($row->responsible_role);
			}
		}
		return true;
	}
				
	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlElement('Responsible',null,$this->getResponsible());
	}


	// STATIC
	public static function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type)
	{
		global $ilDB;

		$query = "SELECT meta_responsible_id FROM il_meta_responsible ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND parent_id = ".$ilDB->quote($a_parent_id ,'integer')." ".
			"AND parent_type = ".$ilDB->quote($a_parent_type ,'text')." ".
			"ORDER BY meta_responsible_id ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_responsible_id;
		}
		return $ids ? $ids : array();
	}
}
?>