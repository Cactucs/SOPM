<?php
namespace SOPM;

abstract class DatabaseManager
{
	/** @var String */
	protected $databaseName = null;

	/** @var string */
	protected $server = 'mongodb://localhost/27017';

	/** @var array */
	protected $options = array('connect' => TRUE);

	/**
	* Max. time to process finding in database in ms.
	*  -1 for infinitive 
	*
	* @var int 
	*/
	public $findTimeout = 20000; 

	/** @var MongoClient() */
	protected $client;

	/**
	* Database manager constructor.
	*/
	public function __construct()
	{
		$this->client = new \MongoClient($this->server, $this->options);
	}

	/**
	* Returns a collection object.
	*
	* @param  string     Name of collection to return.
	* @return Collection The collection.
	*/
	public function getCollection($collection)
	{
		return new Collection($this->client->selectCollection($this->databaseName, $collection), $this);
	}
}
