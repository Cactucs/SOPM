<?php
namespace SOPM;

/**
 * Class Collection
 * @package SOPM
 */
class Collection
{
	/** @var MongoCollection() */
	private $mongoCollection;

	/** @var DatabaseManager() */
	private $dbManager;

    /**
     * Collection constructor
     *
     * @param \MongoCollection $mongoCollection
     * @param \SOPM\DatabaseManager $dbManager Connection to database.
     * @internal param \SOPM\Collection $MongoCollection which is represented by this obj.
     */
	public function __construct(\MongoCollection $mongoCollection, DatabaseManager $dbManager)
	{
		$this->mongoCollection = $mongoCollection;
		$this->dbManager = $dbManager;
	}

	/**
	* Saves given entity to collection.
	*
	* @throws Exception Not valid entity.
	* @param  BaseEntity     The entity to save
	* @param  array          See mongo docs.
    * @return bool
	*/
	public function save(BaseEntity $entity, array $options = array())
	{
		// if (!$entity->validate())
		// 	throw new Exception("Data not valid.");

		$entity->beforeSave();
		if($entity->getMongoId() == null) {
			$mongoId = new \MongoId;
			$entity->setMongoId($this->mongoCollection->getName().'.'.$mongoId->__toString());
		}

		$this->mongoCollection->save($entity->toArray(true), $options);

		$entity->afterSave();

		return true;
	}

	/**
	* Removes an entity from collection.
	*
	* @param BaseEntity Entity to remove.
	*/
	public function remove(BaseEntity $entity)
	{
		$entity->beforeRemove();
		$this->mongoCollection->remove(['_id' => $entity->getMongoId()]);
	}

    /**
     * The same as self::find() but returns only one result.
     *
     * @param array $query
     * @param array $options
     * @return mixed
     */
    public function findOne($query = array(), $options = array())
	{
		$options['limit'] = 1;

		$results = $this->find($query, $options);
		return $results[0];
	}

	/**
	* Finds all documents matching the query
	*
	* @param array  A query; See mongo docs
	* @param array  Options. (Sort, offset, limit)
	* @return DataArray
	*/
	public function find(array $query = array(), array $options = array()) {
		$documents = $this->mongoCollection->find($query);

		if (isset($options['sort']))
			$documents->sort($options['sort']);

		if (isset($options['offset']))
			$documents->skip($options['offset']);

		if (isset($options['limit']))
			$documents->limit($options['limit']);



		$documents->timeout($this->dbManager->findTimeout);

		return new DataArray($documents, $this->dbManager);
	}

	/**
	* Returns a number of elements suitable with query.
	*
	* @param array
	* @return int
	*/
	public function count($query = array()) {
		return $this->mongoCollection->count($query);
	}


}
