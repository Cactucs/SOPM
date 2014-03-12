<?php

namespace SOPM;


class Helpers {

	static public function objectify(array $document, $dbManager = null)
	{
		$ret = array();
		foreach ($document as $key => $element) {
			if (is_array($element)) $ret[$key] = self::objectify($element, $dbManager);
			else $ret[$key] = $element;
		}
		if (isset($ret['SOPM_entityName']) && is_string($ret['SOPM_entityName'])) {
			$explodedEntityName = explode('\\', $ret['SOPM_entityName']);
			if($explodedEntityName[0] == 'SOPM') {
				if($explodedEntityName[1] == 'link') {
					return new Link($ret['SOPM_targetId']);
				} else {
					throw new Exception('Undefined special type.');
				}
			} else {
				return new $document['SOPM_entityName']($ret, false, $dbManager);
			}
		} else {
			return new DataArray($ret, $dbManager);
		}
	}
} 