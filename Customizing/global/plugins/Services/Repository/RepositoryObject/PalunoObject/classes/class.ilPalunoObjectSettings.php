<?php

class ilPalunoObjectSettings
{
	private static $instance = null;
	private $defaultAccess = "users";
	private $purposeSuffixes = array();
	private $mimeTypes = array();

	/**
	 * singleton contructor
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	$this->initStorage();
		$this->read();	
	}
	
	/**
	 * get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilPalunoObjectSettings();
	}

	/**
	 * set filetypes for purposes
	 *
	 * @access public
	 * 
	 */
	public function setPurposeSuffixes($purpose_filetypes)
	{
	 	$this->purposeSuffixes = $purpose_filetypes;
	}

	/**
	 * get filetypes for purposes
	 *
	 * @access public
	 * 
	 */
	public function getPurposeSuffixes()
	{
	 	return $this->purposeSuffixes;
	}

	public function getDefaultAccess() {
	    return $this->defaultAccess;
	}
	
	public function setDefaultAccess($value) {
	    $this->defaultAccess = $value == "users" ? "users" : "public";
	}
	
	/**
	 * @return array of mimetypes
	 */
	public function getMimeTypes() {
		return $this->mimeTypes;
	}
	
	/**
	 * @param unknown_type $mimeTypes
	 */
	public function setMimeTypes(array $mimeTypes) {
		$this->mimeTypes = $mimeTypes;
	}

	
	/**
	 * save 
	 *
	 * @access public
	 */
	public function save()
	{
		foreach ($this->purposeSuffixes as $purpose => $filetypes) {
			$this->storage->set($purpose . "_types", implode(",",$filetypes));
		}
		$this->storage->set("defaultaccess",$this->defaultAccess);
		$this->storage->set("mimetypes", implode(",", $this->getMimeTypes()));
	}

	/**
	 * Read settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
		foreach ($this->purposeSuffixes as $purpose => $filetypes) {
			if ($this->storage->get($purpose."_types") != false) {
				$this->purposeSuffixes[$purpose] = explode(",",$this->storage->get($purpose."_types"));
			}
		}	
		$this->setDefaultAccess($this->storage->get("defaultaccess"));
		if ($this->storage->get("mimetypes"))
			$this->setMimeTypes(explode(",", $this->storage->get("mimetypes")));	
	}
	
	/**
	 * Init storage class (ilSetting)
	 * @access private
	 * 
	 */
	private function initStorage()
	{
	 	include_once('./Services/Administration/classes/class.ilSetting.php');
	 	$this->storage = new ilSetting('xpal');
	 	include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilObjPalunoObject.php');
	 	$this->purposeSuffixes = array_flip(ilObjPalunoObject::$purposes);
	 	       
	 	$this->purposeSuffixes["Standard"] = array("mp3","flv","mp4","m4v","mov","wmv","gif","png", "jpg", "jpeg");
        $this->purposeSuffixes["AudioPortable"] = array("mp3");
        $this->purposeSuffixes["VideoPortable"] = array("mp4","m4v","mov");
        $this->setDefaultAccess("users");
		include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");		        
        $mimeTypes = array_unique(array_values(ilMimeTypeUtil::getExt2MimeMap()));
        sort($mimeTypes);
        $this->setMimeTypes($mimeTypes);
	}
}
?>