<?php

namespace SOPM;

/**
* A link with not-loaded data from db.
*
*/
class Link
{
	protected $targetId;
	
	/**
	* Link constructor
	*
	* @throws Exception 	If not valid link id.
	*/
	public function __construct($targetId)
	{
		$this->targetId = $targetId;
		if (!$this->isValid())
			throw new Exception("Invalid link.");
	}

	/**
	* Checks if the id is valid.
	*
	* @return bool
	*/
	private function isValid()
	{
		list($collection, $mongoId) = explode('.', $this->targetId);
		try {
			new \MongoId($mongoId);
			return true;
		} catch (\MongoException $e) {
			return false;
		}
	}

	/**
	* Return the target's id.
	*
	* @return string
	*/
	public function getId()
	{
		return $this->targetId;
	}

	/**
	* Returns the name of target collection.
	*
	* @return string
	*/
	public function getCollection()
	{
		return explode('.', $this->targetId)[0];
	}

	/**
	* Returns an array representation of link.
	*
	* @return array
	*/
	public function toArray()
	{
		return array(
			'SOPM_entityName' => 'SOPM\\link',
			'SOPM_targetId' => $this->getId());
	}

    /**
     * Generates an entity
     *
     * @param DatabaseManager $manager
     * @return mixed
     */
    public function getEntity(DatabaseManager $manager)
	{
		return $manager->getCollection($this->getCollection())->findOne(array('_id' => $this->getId()));
	}
}

