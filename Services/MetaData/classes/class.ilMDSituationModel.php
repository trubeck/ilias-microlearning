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
* Meta Data class (element situation model)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDSituationModel extends ilMDBase
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
	function &getDescriptionLanguage()
	{
		return $this->description_language;
	}
	function getDescriptionLanguageCode()
	{
		if(is_object($this->description_language))
		{
			return $this->description_language->getLanguageCode();
		}
		return false;
	}
	
	function save()
	{
		global $ilDB; 
		
		$fields = $this->__getFields();
		$fields['meta_situation_model_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_situation_model'));
		
		if($this->db->insert('il_meta_situation_model',$fields))
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
			if($this->db->update('il_meta_situation_model',
									$this->__getFields(),
									array("meta_situation_model_id" => array('integer',$this->getMetaId()))))
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
			$query = "DELETE FROM il_meta_situation_model ".
				"WHERE meta_situation_model_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			$res = $ilDB->manipulate($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> array('integer',$this->getRBACId()),
					 'obj_id'	=> array('integer',$this->getObjId()),
					 'obj_type'	=> array('text',$this->getObjType()),
					 'previous'	=> array('integer',$this->getPrevious()),
					 'next'		=> array('integer',$this->getNext()));
	}

	function read()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_situation_model ".
				"WHERE meta_situation_model_id = ".$ilDB->quote($this->getMetaId() ,'integer');

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$this->setRBACId($row->rbac_id);
				$this->setObjId($row->obj_id);
				$this->setObjType($row->obj_type);
				$this->setPrevious($row->previous);
				$this->setNext($row->next);
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
		$writer->xmlStartTag('SituationModel');
		$writer->xmlElement('Previous',null,$this->getPrevious());
		$writer->xmlElement('Next',null,$this->getNext());
		$writer->xmlEndTag('SituationModel');
	}

				

	// STATIC
	static function _getIds($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_situation_model_id FROM il_meta_situation_model ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_situation_model_id;
		}
		return $ids ? $ids : array();
	}
}
?>