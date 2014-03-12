<?php
date_default_timezone_set("Europe/Prague");
error_reporting(-1);
require_once('../src/loader.php');
require_once('loader.php');


// Create connection
$conn = new Connection();

$people = $conn->getCollection('people');

$person = $people->findOne();

$person->setFirstName('Bill');
$people->save($person);

