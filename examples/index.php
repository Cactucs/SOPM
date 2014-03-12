<?php
date_default_timezone_set("Europe/Prague");
error_reporting(-1);
require_once('../src/loader.php');
require_once('loader.php');


// Create connection
$conn = new Connection();

$tags = $conn->getCollection('tags');

// create new entity
$tagA = new Tag();
$tagA->setName('good');
$tagA->setGood(true);
$tags->save($tagA);

$tagB = new Tag();
$tagB->setName('smart');
$tagB->setGood(true);
$tags->save($tagB);



// Create new entity
$person = new Person();

// Set some info
$person->setFirstName('John');
$person->setAge(44);

$tags = array($tagA, $tagB);

// Add entity to parent entity
$person->setTags($tags);

// Get a collection to save
$people = $conn->getCollection('people');

// Save entity to collection
$people->save($person);

// Get one entity from database
$onePerson = $people->findOne();

// Get and print some info 
echo $onePerson->getFirstName() . ' ' . $onePerson->getLastName() . ' is ' . $onePerson->getAge() . ' years old.' . PHP_EOL;

// Remove person 
// $people->remove($onePerson);

// Count people in database
echo $people->count() . PHP_EOL;

// How many Bills we have in db?
echo $people->count(array('firstName' => 'Bill')) . PHP_EOL;

// Get person with smallest age
$youngest = $people->findOne(array(), array('sort' => array('age' => 1)));

// Prints the age
echo $youngest->getAge() . PHP_EOL;

// Get person with biggest age
$oldest = $people->findOne(array(), array('sort' => array('age' => -1)));

// Prints the age
echo $oldest->getAge() . PHP_EOL;

// Get tags names
$tags = $oldest->getTags();

// Count tags
echo count($tags) . PHP_EOL;

// Print all tags
foreach ($tags as $tag) {
	echo $tag->getName() . PHP_EOL;
}

// How many lazy Bills we have?
echo $people->count(array('firstName' => 'Bill', 'tags' => array('name' => 'lazy'))) . PHP_EOL;

// And what is the first last name in alphabet of lazy Bill
// echo $people->findOne(array('firstName' => 'Bill', 'tags' => array('name' => 'lazy')), array('sort' => array('lastName' => 1)))->getLastName() . PHP_EOL;
