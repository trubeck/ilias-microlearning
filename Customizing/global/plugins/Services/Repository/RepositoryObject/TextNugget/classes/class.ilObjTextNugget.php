<?php

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once("./Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TextNugget/classes/class.ilObjTextNuggetGUI.php");

/**
 */
class ilObjTextNugget extends ilObjectPlugin implements ilLPStatusPluginInterface
{

	public $referenceIdFromExam = 0;

	/**
	 * Constructor
	 *
	 * @access        public
	 * @param int $a_ref_id
	 */
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}

	/**
	 * Get type.
	 */
	final function initType()
	{
		$this->setType(ilTextNuggetPlugin::ID);
	}

	/**
	* Get Nugget name by object ID.
	*/
	function getNuggetNameByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_data WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$entry = $data["title"];

		return $entry;
	}

	/**
	* Set Ref Id from Exam.
	*/
	function setRefIdFromExam($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_reference WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$this->referenceIdFromExam = $data["ref_id"];
	}

	/**
	* Get Ref Id from Exam.
	*/
	function getRefIdFromExam()
	{
		return $this->referenceIdFromExam;
	}

	/**
	* Get Ref Id from Exam by object ID.
	*/
	function getRefIdFromExamByObjId($obj_id)
	{
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM object_reference WHERE obj_id = ".$ilDB->quote($obj_id, "integer"));
		$data = $ilDB->fetchAssoc($result);
		$refIdFromNugget = $data["ref_id"];

		return $refIdFromNugget;
	}

	/**
	 * Create object
	 */
	function doCreate()
	{
		global $ilDB;

		$ilDB->manipulate("INSERT INTO rep_robj_xtxt_data ".
			"(id, is_online, option_one) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote("default 1", "text").
			")");

		$this->createMetaData();
	}

	/**
	 * Read data from db
	 */
	function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM rep_robj_xtxt_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
		);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setOnline($rec["is_online"]);
		}
	}

	/**
	 * Update data
	 */
	function doUpdate()
	{
		global $ilDB;

		$ilDB->manipulate($up = "UPDATE rep_robj_xtxt_data SET ".
			" is_online = ".$ilDB->quote($this->isOnline(), "integer")."".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
		);

		$this->updateMetaData();
	}

	/**
	 * Delete data from db
	 */
	function doDelete()
	{
		global $ilDB;

		$this->deleteMetaData();

		$ilDB->manipulate("DELETE FROM rep_robj_xtxt_data WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
		);
	}

	/**
	 * Set online
	 *
	 * @param        boolean                online
	 */
	function setOnline($a_val)
	{
		$this->online = $a_val;
	}

	/**
	 * Get online
	 *
	 * @return        boolean                online
	 */
	function isOnline()
	{
		return $this->online;
	}

	/**
	 * Get all user ids with LP status completed
	 *
	 * @return array
	 */
	public function getLPCompleted() {
		return array();
	}

	/**
	 * Get all user ids with LP status not attempted
	 *
	 * @return array
	 */
	public function getLPNotAttempted() {
		return array();
	}

	/**
	 * Get all user ids with LP status failed
	 *
	 * @return array
	 */
	public function getLPFailed() {
		return array(6);
	}

	/**
	 * Get all user ids with LP status in progress
	 *
	 * @return array
	 */
	public function getLPInProgress() {
		return array();
	}

	/**
	 * Get current status for given user
	 *
	 * @param int $a_user_id
	 * @return int
	 */
	public function getLPStatusForUser($a_user_id) {
		global $ilUser;
		if($ilUser->getId() == $a_user_id)
			return $_SESSION[ilObjTextNuggetGUI::LP_SESSION_ID];
		else
			return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}
?>