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
	function setCompetencyLevel($a_competency_level)
	{
		return $this->competency_level = $a_competency_level;
	}
	function getCompetencyLevel()
	{
		return $this->competency_level;
	}
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
	
	function getPossibleSubelements()
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_activity");
		$data_act = $ilDB->fetchAssoc($result);
		$result = $ilDB->query("SELECT * FROM il_meta_method");
		$data_met = $ilDB->fetchAssoc($result);
		$result = $ilDB->query("SELECT * FROM il_meta_result_type");
		$data_res = $ilDB->fetchAssoc($result);
		include_once "Services/Logging/classes/class.ilLog.php";

		if($data_act == null)
		{
			$subs['Activity'] = 'meta_activity';
		}
		if($data_met == null)
		{
			$subs['Technique/Method'] = 'meta_method';
		}
		if($data_res == null)
		{
			$subs['Result Type'] = 'meta_result_type';
		}
		
		return $subs;
	}

	function &getActivityIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDActivity.php';

		return ilMDActivity::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),'meta_situation_model');
	}

	function &getActivity($a_activity_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDActivity.php';
		
		if(!$a_activity_id)
		{
			return false;
		}
		$act = new ilMDActivity();
		$act->setMetaId($a_activity_id);

		return $act;
	}

	function &addActivity()
	{
		include_once 'Services/MetaData/classes/class.ilMDActivity.php';

		$act = new ilMDActivity($this->getRBACId(),$this->getObjId(),$this->getObjType());
		$act->setParentId($this->getMetaId());
		$act->setParentType('meta_situation_model');

		return $act;
	}

	function &getMethodIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDMethod.php';

		return ilMDMethod::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),'meta_situation_model');
	}

	function &getMethod($a_method_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDMethod.php';
		
		if(!$a_method_id)
		{
			return false;
		}
		$met = new ilMDMethod();
		$met->setMetaId($a_method_id);

		return $met;
	}

	function &addMethod()
	{
		include_once 'Services/MetaData/classes/class.ilMDMethod.php';

		$met = new ilMDMethod($this->getRBACId(),$this->getObjId(),$this->getObjType());
		$met->setParentId($this->getMetaId());
		$met->setParentType('meta_situation_model');

		return $met;
	}

	function &getResultTypeIds()
	{
		include_once 'Services/MetaData/classes/class.ilMDResultType.php';

		return ilMDResultType::_getIds($this->getRBACId(),$this->getObjId(),$this->getMetaId(),'meta_situation_model');
	}

	function &getResultType($a_result_type_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDResultType.php';
		
		if(!$a_result_type_id)
		{
			return false;
		}
		$res = new ilMDResultType();
		$res->setMetaId($a_result_type_id);

		return $res;
	}

	function &addResultType()
	{
		include_once 'Services/MetaData/classes/class.ilMDResultType.php';

		$res = new ilMDResultType($this->getRBACId(),$this->getObjId(),$this->getObjType());
		$res->setParentId($this->getMetaId());
		$res->setParentType('meta_situation_model');

		return $res;
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
			/**
	
			$objIdOldPreviousNugget = $this->getObjIdOfOldPreviousNugget($this->getPrevious());
			//check if chosen objId is set in another nugget as previous
			if($objIdOldPreviousNugget != null)
			{
				//set field 'previous' of old previous nugget to 0
				$query = "UPDATE il_meta_situation_model SET previous=0 WHERE obj_id = ".$ilDB->quote($objIdOldPreviousNugget ,'integer');
				$res = $ilDB->manipulate($query);
			}

			$objIdOldNextNugget = $this->getObjIdOfOldNextNugget($this->getNext());
			//check if chosen objId is set in another nugget as next
			if($objIdOldNextNugget != null)
			{
				//set field 'previous' of old previous nugget to 0
				$query = "UPDATE il_meta_situation_model SET next=0 WHERE obj_id = ".$ilDB->quote($objIdOldNextNugget ,'integer');
				$res = $ilDB->manipulate($query);
			}
			*/
			if($this->db->update('il_meta_situation_model',
									$this->__getFields(),
									array("meta_situation_model_id" => array('integer',$this->getMetaId()))))
			{
			
			//$this->resetPossibleNextConnection();
			//$this->updateEntryOfPreviousNugget($this->getPrevious());
			//$this->resetPossiblePreviousConnection();
			//$this->updateEntryOfNextNugget($this->getNext());
				
			return true;
			}
		}
		return false;
	}

	function resetPossibleNextConnection()
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE next = ".$ilDB->quote($this->getObjId(), "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["obj_id"];
		$query = "UPDATE il_meta_situation_model SET next=0 WHERE obj_id = ".$ilDB->quote($entry ,'integer');
		$res = $ilDB->manipulate($query);
	}

	function updateEntryOfPreviousNugget($obj_id)
	{
		global $ilDB;

		$obj_type = $this->getNuggetTypeByObjId($obj_id);
		$previousprevious = $this->getPreviousNuggetOfPreviousNuggetByObjId($obj_id);

		//fields of previous nugget to be stored in database
		$array = array('rbac_id'	=> array('integer',$obj_id),
					'obj_id'	=> array('integer',$obj_id),
					'obj_type'	=> array('text',$obj_type),
					'previous'	=> array('integer',$previousprevious),
					'next'		=> array('integer',$this->getObjId()));
		
		//if previous is set
		if($this->getPrevious() != 0)
		{
			if(!$this->getMetaIdByObjId($obj_id))
			{
				//insert new entry if no metaId was found
				$array['meta_situation_model_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_situation_model'));
				$this->db->insert('il_meta_situation_model',$array);
			}

			//update entry
			$this->db->update('il_meta_situation_model',
						$array,
						array("meta_situation_model_id" => array('integer',$this->getMetaIdByObjId($obj_id))));
		}
		else 
		{
			$this->resetPossibleNextConnection();
		}
	}

	/**
	* Get ObjId of Old Previous Nugget.
	*/
	function getObjIdOfOldPreviousNugget($previous)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE previous = ".$ilDB->quote($previous, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["obj_id"];

		return $entry;
	}

	function resetPossiblePreviousConnection()
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE previous = ".$ilDB->quote($this->getObjId(), "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["obj_id"];
		$query = "UPDATE il_meta_situation_model SET previous=0 WHERE obj_id = ".$ilDB->quote($entry ,'integer');
		$res = $ilDB->manipulate($query);
	}

	function updateEntryOfNextNugget($obj_id)
	{
		global $ilDB;

		$obj_type = $this->getNuggetTypeByObjId($obj_id);
		$nextnext = $this->getNextNuggetOfNextNuggetByObjId($obj_id);

		//fields of next nugget to be stored in database
		$array = array('rbac_id'	=> array('integer',$obj_id),
						'obj_id'	=> array('integer',$obj_id),
						'obj_type'	=> array('text',$obj_type),
						'previous'	=> array('integer',$this->getObjId()),
						'next'		=> array('integer',$nextnext));
		
		//if next is set
		if($this->getNext() != 0)
		{
			if(!$this->getMetaIdByObjId($obj_id))
			{
				//insert new entry if no metaId was found
				$array['meta_situation_model_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_situation_model'));
				$this->db->insert('il_meta_situation_model',$array);
			}

			//update entry
			$this->db->update('il_meta_situation_model',
						$array,
						array("meta_situation_model_id" => array('integer',$this->getMetaIdByObjId($obj_id))));
		}
		else 
		{
			//set field 'previous' of old next nugget to 0
			$this->resetPossiblePreviousConnection();
		}
	}

	/**
	* Get ObjId of Old Next Nugget.
	*/
	function getObjIdOfOldNextNugget($next)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE next = ".$ilDB->quote($next, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["obj_id"];

		return $entry;
	}

	/**
	* Get Nugget type by object ID.
	*/
	function getNuggetTypeByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_data WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["type"];

		return $entry;
	}

	/**
	* Get Previous Nugget of Previous Nugget.
	*/
	function getPreviousNuggetOfPreviousNuggetByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["previous"];

		return $entry;
	}

	/**
	* Get Next Nugget of Next Nugget.
	*/
	function getNextNuggetOfNextNuggetByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["next"];

		return $entry;
	}

	/**
	* Get Previous Nugget of Previous Nugget.
	*/
	function getMetaIdByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_meta_situation_model WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["meta_situation_model_id"];

		return $entry;
	}

	function delete()
	{
		global $ilDB;
		
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_situation_model ".
				"WHERE meta_situation_model_id = ".$ilDB->quote($this->getMetaId() ,'integer');
			$res = $ilDB->manipulate($query);
			
			foreach($this->getActivityIds() as $id)
			{
				$act = $this->getActivity($id);
				$act->delete();
			}
		
			foreach($this->getMethodIds() as $id)
			{
				$met = $this->getMethod($id);
				$met->delete();
			}

			foreach($this->getResultTypeIds() as $id)
			{
				$res = $this->getResultType($id);
				$res->delete();
			}
			return true;
		}
		return false;
	}
	
	function __getFields()
	{
		return array('rbac_id'			=> array('integer',$this->getRBACId()),
					 'obj_id'			=> array('integer',$this->getObjId()),
					 'obj_type'			=> array('text',$this->getObjType()),
					 'competency_level'	=> array('integer',$this->getCompetencyLevel()),
					 'previous'			=> array('integer',$this->getPrevious()),
					 'next'				=> array('integer',$this->getNext()));
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
				$this->setCompetencyLevel($row->competency_level);
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
		$writer->xmlElement('CompetencyLevel',null,$this->getCompetencyLevel());
		$writer->xmlElement('Previous',null,$this->getPrevious());
		$writer->xmlElement('Next',null,$this->getNext());
		$writer->xmlEndTag('SituationModel');
	}

	// STATIC
	static function _getId($a_rbac_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT meta_situation_model_id FROM il_meta_situation_model ".
			"WHERE rbac_id = ".$ilDB->quote($a_rbac_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->meta_situation_model_id;
		}
		return false;
	}

}
?>