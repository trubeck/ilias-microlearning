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
* Meta Data class (element activity)
*
* @author Markus Heikamp <markus.heikamp@gmx.de>

*/
include_once 'class.ilMDBase.php';

class ilMDActivity extends ilMDBase
{
	// SET/GET
	function setPrevious($a_previous)
	{
		$this->previous = $a_previous;
	}
	function getPrevious()
	{
		return $this->previous;
	}
	function setNext($a_next)
	{
	    $this->next = $a_next;
	}
	function getNext()
	{
		return $this->next;
	}
	function setContains($a_contains)
	{
		$this->contains = $a_contains;
	}
	function getContains()
	{
		return $this->contains;
	}
	function setIsPartOf($a_is_part_of)
	{
	    $this->is_part_of = $a_is_part_of;
	}
	function getIsPartOf()
	{
		return $this->is_part_of;
	}
	function setIncludedMethod($a_included_method)
	{
		$this->included_method = $a_included_method;
	}
	function getIncludedMethod()
	{
		return $this->included_method;
	}
	function setParticipating($a_participating)
	{
	    $this->participating = $a_participating;
	}
	function getParticipating()
	{
		return $this->participating;
	}
	function setResponsible($a_responsible)
	{
	    $this->responsible = $a_responsible;
	}
	function getResponsible()
	{
		return $this->responsible;
	}

	function save()
	{
		global $ilDB;

		$fields = $this->__getFields();
		$fields['meta_activity_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_activity'));
		
		if($this->db->insert('il_meta_activity',$fields))
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
			if($this->db->update('il_meta_activity',
									$this->__getFields(),
									array("meta_activity_id" => array('integer',$this->getMetaId()))))
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
			$query = "DELETE FROM il_meta_activity ".
				"WHERE meta_activity_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			$res = $ilDB->manipulate($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'			=> array('integer',$this->getRBACId()),
					 'obj_id'			=> array('integer',$this->getObjId()),
					 'obj_type'			=> array('text',$this->getObjType()),
					 'parent_type'		=> array('text',$this->getParentType()),
					 'parent_id'		=> array('integer',$this->getParentId()),
					 'previous'			=> array('integer',$this->getPrevious()),
					 'next'				=> array('integer',$this->getNext()),
					 'contains'			=> array('integer',$this->getContains()),
					 'is_part_of'		=> array('integer',$this->getIsPartOf()),
					 'included_method'	=> array('integer',$this->getIncludedMethod()),
					 'participating'	=> array('text',$this->getParticipating()),
					 'responsible'		=> array('text',$this->getResponsible()));
	}

	function read()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_activity ".
				"WHERE meta_activity_id = ".$ilDB->quote($this->getMetaId() ,'integer');

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setParentId($row->parent_id);
				$this->setParentType($row->parent_type);
				$this->setPrevious($row->previous);
				$this->setNext($row->next);
				$this->setContains($row->contains);
				$this->setIsPartOf($row->is_part_of);
				$this->setIncludedMethod($row->included_method);
				$this->setParticipating($row->participating);
				$this->setResponsible($row->responsible);
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
		$writer->xmlStartTag('Activity');
		$writer->xmlElement('Previous',null,$this->getPrevious());
		$writer->xmlElement('Next',null,$this->getNext());
		$writer->xmlElement('Contains',null,$this->getContains());
		$writer->xmlElement('IsPartOf',null,$this->getIsPartOf());
		$writer->xmlElement('IncludedMethod',null,$this->getIncludedMethod());
		$writer->xmlElement('Participating',null,$this->getParticipating());
		$writer->xmlElement('Responsible',null,$this->getResponsible());
		$writer->xmlEndTag('Activity');
	}

	// STATIC
	static function _getIds($a_rbac_id,$a_obj_id,$a_parent_id,$a_parent_type)
	{
		global $ilDB;

		$query = "SELECT meta_activity_id FROM il_meta_activity ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND parent_id = ".$ilDB->quote($a_parent_id ,'integer')." ".
			"AND parent_type = ".$ilDB->quote($a_parent_type ,'text');


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_activity_id;
		}
		return $ids ? $ids : array();
	}

}
?>
