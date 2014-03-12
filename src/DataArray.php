<?php

namespace SOPM;

/**
 * Class DataArray
 * @package SOPM
 */
class DataArray implements \ArrayAccess, \Iterator, \Countable {

	/**
	 * @var array
	 */
	private $array = array();

	/**
	 * @var DatabaseManager
	 */
	private $dbManager;

	private $mongoCursor;

	/**
	 * @param $array
	 * @param DatabaseManager $dbManager
	 */
	public function __construct($array, DatabaseManager $dbManager)
	{
		if ($array instanceof \MongoCursor)
			$this->mongoCursor = $array;
		else
			$this->array = $array;
		$this->dbManager = $dbManager;
	}
	
	// ARRAY ACCESS
	/**
	 * @param mixed $name
	 * @return bool
	 */
	public function offsetExists($name)
	{
		if ($this->mongoCursor instanceof \MongoCursor )
			return ($this->mongoCursor->count() - 1) > $name;
		else
			return isset($this->array[$name]);
	}

	/**
	 * @param mixed $name
	 * @return mixed|DataArray
	 */
	public function offsetGet($name)
	{
		$this->generateArray($name);

		if(!isset($this->array[$name]))
			return null;

		$val = $this->array[$name];
		if (is_array($val)) {
			return Helpers::objectify($val, $this->dbManager);
		} else if ($val instanceof Link)
			return $val->getEntity($this->dbManager);
		else 
			return $val;
	}

	/**
	 * @param mixed $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function offsetSet($name, $value)
	{
		$this->generateArray($name);

		return $this->array[$name] = $value;
	}

	/**
	 * @param mixed $name
	 */
	public function offsetUnset($name)
	{
		$this->generateArray($name);

		unset($this->array[$name]);
	}

	// ITERATOR
	/**
	 * @var int
	 */
	private $iteratorOffset = 0;

	/**
	 *
	 */
	public function rewind()
	{
		$this->iteratorOffset = 0;
	}

	/**
	 * @return mixed
	 */
	public function key()
	{
		$this->generateArray($this->iteratorOffset);
		return array_keys($this->array)[$this->iteratorOffset];
	}

	/**
	 * @return mixed|DataArray
	 */
	public function current()
	{
		$this->generateArray($this->iteratorOffset);
		return $this->offsetGet($this->key());
	}

	/**
	 *
	 */
	public function next()
	{
		$this->iteratorOffset++;
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		$this->generateArray($this->iteratorOffset);
		return isset(array_keys($this->array)[$this->iteratorOffset]);
	}

	// COUNTABLE
	/**
	 * @return int
	 */
	public function count()
	{
		if ($this->mongoCursor instanceof \MongoCursor )
			return $this->mongoCursor->count() - 1;
		else
			return count($this->array);
	}

	private $loaded = 0;
	private function generateArray($loadTo)
	{
		if ($this->mongoCursor instanceof \MongoCursor) {
			$ret = array();
			if ($this->loaded === 0)
				$this->mongoCursor->rewind();
			while($this->mongoCursor->valid() && (($loadTo +1) > $this->loaded || $loadTo === -1)) {
				$ret[] = $this->mongoCursor->current();
				$this->loaded++;
				$this->mongoCursor->next();
			}
			$this->array = array_merge($this->array, $ret);
		}
		return $this->array;
	}

	public function toArray($databaseFriendly = false, $tryGenerateLink = false)
	{
		$array = $this->generateArray(-1);
		if ($tryGenerateLink && isset($array['_id']) && $array['_id'] != null)
			return array(
				'SOPM_entityName' => 'SOPM\\link',
				'SOPM_targetId' => $array['_id']);

		$arr = array();
		foreach ($array as $keyA => $property) {
			if (is_object($property) && method_exists($property, 'toArray')) {
				$value = $property->toArray(true, true);
			} elseif (is_array($property)) {
				$value = array();
				foreach ($property as $key => $element) {
					if (method_exists($element, 'toArray'))
						$value[$key] = $element->toArray(true, true);
					else
						$value[$key] = $element;
				}
			} else {
				$value = $property;
			}
			$arr[$keyA] = $value;
		}
		return $arr;
	}
}

