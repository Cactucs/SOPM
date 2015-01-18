# SOPM

SOPM is a simple PHP entity-oriented database library which use MongoDB to save data.

## Simple tutorial

### Entity
The base element of SOPM is an entity. That's an object which is child of `BaseEntity`. Let's have an simple example: 

    class Person extends \SOPM\BaseEntity
    {
        protected $firstName;
        protected $lastName;
        protected $age;
    }
    
### Configuration
Then we need to setup the database manager (connection). It is also done by class

    class Connection extends \SOPM\DatabaseManager
    {
        protected $databaseName = 'SopmTest';
    }
You can also add `server` property to connect to other server (localhost is default). 

### Saving data

That's the configuration and entities. But how to use it? Simply.

First create an entity and set some data using setters.

    $john = new Person;
    $john->setFirstName('John');
    $john->setLastName('Doe');
    $john->setAge(33);
    
The setters (as well as getters) are generated using magic at `BaseEntity`. Do not write your own! 

Before saving we have to create new DatabaseManager. In this case `Connection`:

    $connection = new Connection;
    
Then we have to get a collection where we will save the entity. 

    $collection = $connection->getCollection('people');

And now we can save the entity using collection's `save` method.

    $collection->save($john);

Yes! We've done that! 

### Finding data in collection
Now you know how to save data but how to get them? Let's see!

For this we will use two methods of collection (that's the one where we saved the entity using `save` method). `find` and `findOne` (also `count` - but about that later). These two methods are nearly the same only `findOne` returns only one object not an array as `find`.

They have two arguments. First is an array where you specify the query. Let's look at example. 
We wanna get all Johns from the database we've created before. In other words all entities saved to collection where `firstName` is John.

    $query = array('firstName' => 'John');
    $result = $collection->find($query);

In the result we have an array of all Johns (represented by their entities). If there is no John in collection the result will be null. 

The second argument is options :

  
| Key | Value | Description |
| --- | ----- | ----------- |
| `sort` | array (key = property; value = 1/-1) | sorts: 1 = ascending; 2 = descending |
| `offset` | int | Number of entities to skip at start |
| `limit` | int | Max. number of entities to return. |
    
#### Get data from entity.

So let's print all last names out!

    foreach($result as $john) {
        echo $john->getLastName();
    }
#### Count

As I mentioned before there is also a `count` method. It's similar at the first argument - the query. But it returns only how many entities saved to collection.

It's the same as `count($collection->find($q)`.

### Editing

Editing is as simple as finding and saving. We will get an entity (using something like `find` method) then edit it (with some setters) and then simply save it (using `save` method) Here is an example:

    $jim = $collection->findOne(array('firstName' => 'Jim'));
    $jim->setFirstName('Jack');
    $collection->save($jim);
    
That's it! Simple!

### Links

Links are one of the features if SOPM I really love. Let's say we want to add some attributes of the people. One person can have one or two or even hundreds. After we created collection of all available attributes (in new collection) we will get the attributes we want to add to John.

Get attributes:

    $happy = $connection->getCollection('attributes')->findOne(array('name' => 'happy'));
    $lovely = $connection->getCollection('attributes')->findOne(array('name' => 'lovely'));
Get John:
    
    $john = $connection->getCollection('people')->findOne(array('firstName' => 'John'));
Add attributes to John:

    $john->setAttributes(array($happy, $lovely));
Save:    

    $connection->getCollection('people')->save($john);

Now in the attributes array in collection is saved something magical and when we try to get it it converts it into "normal" entity. Simple but very useful!

### Validation

If you want to validate data you can simply add `validate`-prefixed method.  


``` php
class Person extends \SOPM\BaseEntity
{
    protected $firstName;
    protected function validateFirstName($firstName)
    {
        return (preg_match('/\s/', $firstName) === 0) && (lcfirst($firstName) === strtolower($firstName));
    }

    protected $tags;
    protected function validateTags($tags)
    {
        return is_array($tags);
    }

    protected $age;
    protected function validateAge($age)
    {
        return is_int($age);
    }
}
```
