<?php

require_once("./Services/Export/classes/class.ilXmlImporter.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilObjPalunoObject.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/PalunoObject/classes/class.ilPalunoObjectPlugin.php");

/**
 * Class ilPalunoObjectImporter
 *
 * @author Markus Heikamp
 */
class ilPalunoObjectImporter extends ilXmlImporter {

	/**
	 * Import xml representation
	 *
	 * @param    string        entity
	 * @param    string        target release
	 * @param    string        id
	 * @return    string        xml string
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
		$xml = simplexml_load_string($a_xml);
		$pl = new ilPalunoObjectPlugin();
		$entity = new ilObjPalunoObject();
		$entity->setTitle((string) $xml->title." ".$pl->txt("copy"));
		$entity->setDescription((string) $xml->description);
		$entity->setOnline((string) $xml->online);
		$entity->setImportId($a_id);
		$entity->create();
		$new_id = $entity->getId();
		$a_mapping->addMapping("Plugins/PalunoObject", "xpal", $a_id, $new_id);
		return $new_id;
	}
}