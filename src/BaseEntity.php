<?php
namespace SOPM;

abstract class BaseEntity
{


	/** 
	* Mongo's unique id. Accessible via getMongoId() method.
	*
	* @var MongoId | null
	*/
	private $_id = null;

	/**
	* Reflection of $this (Entity).
	*
	* @var ReflectionClass
	*/
	private $reflection;

	/**
	* Database manager object used to get links. 
	*
	* @var DatabaseManager
	*/
	private $dbManager = null;

	/**
	* Constructor of entity
	*
	* @param array           Data to be save in entity.
	* @param bool            Indicates if entity was ever saved to db.
	* @param DatabaseManager The connection to db.
	*
	*/
	public function __construct($attributes = array(), $new = true, DatabaseManager $dbManager = null)
	{
		foreach ($attributes as $key => $value) {
			$this->$key = $value;
		}

		$this->reflection = new \ReflectionClass($this);
		$this->dbManager = $dbManager;

		if ($new)
			$this->afterNew();
	}

	public function __call($method, array $arguments)
	{
		// Is this a get or a set
		$prefix = strtolower(substr($method, 0, 3));

		if ($prefix != 'get' && $prefix != 'set' && $prefix != 'add')
			return $this;

		// What is the get/set class attribute
		$property = lcfirst(substr($method, 3));


		if (empty($property))
			throw new Exception("Calling empty ".$prefix.'ter');


		if ($property == 'mongoId') {
			switch ($prefix) {
				case 'get':
					return $this->_id;
					break;
				
				case 'set':
					$this->_id = $arguments[0];
					return $this;
					break;
				
				default:
					throw new Exception('Undefined operation (\'' . $prefix . '\') with mongoId');
					break;
			}
		}

		if(!$this->offsetExists($property))
			throw new Exception(ucfirst($prefix)."ting property that do not exists: $property");


		switch ($prefix) {
			case 'get':
				if ($this->$property instanceof Link)
					return $this->$property->getEntity($this->dbManager);
				else if (is_array($this->$property))
					return new DataArray($this->$property, $this->dbManager);
				else
					return $this->$property;
					break;
			
			case 'set':
				if (!array_key_exists(0, $arguments))
					throw new Exception('Calling set with no arguments.');

				// Validation
				$valid = true;
				$method = 'validate'.ucfirst($property);
				if(method_exists($this, $method))
					$valid = $this->$method($arguments[0]);
				if ($valid) {
					$this->$property = $arguments[0];
					return $this;
				} else { 
					throw new Exception('Trying to set invalid data (\''.$arguments[0].'\') to property '.$property);
				}	
				break;
			
			case 'add':
				if (!array_key_exists(0, $arguments))
					throw new Exception('Calling add with no arguments.');
				
				if (!is_array($this->$property))
					throw new Exception('Adding to no-array property.');

				$prop = $this->$property;
				$prop[] = $arguments[0];
				$this->$property = $prop;
				return $this;
				break;
		}
		throw new Exception(ucfirst($prefix)."ting property that do not exists: $property");
	}


	/** Callback called after creating new. */
	public function afterNew() {}
	/** Callback called before saving to db. */
	public function beforeSave() {}
	/** Callback called after saving to db. */
	public function afterSave() {}
	/** Callback called before removing from db. */
	public function beforeRemove() {}

	/**
	* Generates array from entity's data.
	*
	* @param  bool Include entity name and _id?
	* @param  bool Try to generate data only for link?
	* @return array
	*/
	public function toArray($databaseFriendly = false, $tryGenerateLink = false)
	{
		if ($tryGenerateLink && $this->_id != null) 
			return array(
				'SOPM_entityName' => 'SOPM\\link',
				'SOPM_targetId' => $this->getMongoId());

		$arr = array();
		$props = $this->reflection->getProperties();
		foreach ($props as $property) {
			if ($property->class != __CLASS__ && !$property->isStatic()) {
				$propName = $property->name;
				if (is_object($this->$propName)) {
					$value = $this->$propName->toArray(true, true);
				} elseif (is_array($this->$propName)) {
					$value = array();
					foreach ($this->$propName as $key => $element) {
                        if (method_exists($element, 'toArray'))
						    $value[$key] = $element->toArray(true, true);
                        else
                            $value[$key] = $element;
					}
				} else {
					$value = $this->$propName;
				}
				$arr[$property->name] = $value;
			}
		}
		if ($databaseFriendly) {
			$arr['SOPM_entityName'] = get_called_class();
			$arr['_id'] = $this->_id;
		}
		return $arr;
	}

	/**
	* Checks if the property of entity exists.
	*
	* @param  string Name of property to check.
	* @return bool
	*/
	private function offsetExists($name) {
		try {
			return $this->reflection->getProperty($name)->class != __CLASS__;
		} catch (\ReflectionException $e) {
			return false;
		}
	}
}