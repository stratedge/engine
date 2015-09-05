# Engine
A simple Doctrine-based library for handling nodes and edges in a transactional database. Originally built to provide repository-like ease for [Halligan](https://github.com/stratedge/halligan), a web-based fire department management framework.

## Installation

### Composer

Engine is not presently registered with Packagist, so it must be registered as a VCS repository.

```
{
	"require": {
		"stratedge/engine": "dev-master"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/stratedge/engine.git"
		}
	]
}
```

## Usage

```php
<?php

//--- BEGIN SETUP ---//

//Create Doctrine Connection
$config = new Configuration();

$connectionParams = [
	'url' => 'mysql://username:password@host:port/db_name?charset=utf8'
];

$connection = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

//Setup the database adapter
$doctrine = new Stratedge\Engine\Adapters\Doctrine($connection);
Stratedge\Engine\Database::register($doctrine);

//--- END SETUP ---//

//Create a new user node
$new_user = Your\Namespace\User::create([
	'first_name' => 'John',
	'last_name' => 'Smith'
]);

//Create a new post node
$new_post = Your\Namespace\Post::create([
	'post_body' => 'Lorem ipsum dolor sit amet'
]);

//Associate the user to the post
$new_posted_by = Your\Namespace\PostUser::create($new_post, $new_user);

//Find all posts by user
$posts = Your\Namespace\PostUser::findOppositeNodes($new_user);

//Find user that posted post
$the_user = Your\Namespace\PostedBy::findOppositeNode($new_post);

//Load a particular post
$specific_post = Your\Namespace\Post::find(12);

//Load all users by a property value or several property values
$users_with_name = Your\Namespace\User::findBy([
	'first_name' => 'John'
]);

//Load the first user found by property value or several property values
$user_with_name = Your\Namespace\User::findOneBy([
	'first_name' => 'John'
]);
```

## Concepts

### Nodes

A node represents a single row in a database as an object in code. A user or a post would be considered a node. The columns of the node's table correspond to properties in the object.

### Edges

An edge is the relationship between 2 nodes. A relationship between a user and a post may be expressed as a hash table with the name posted_by, and in code as a PostedBy edge object. Edges can have properties like nodes.

### Built on Doctrine (If You Want)

Engine works on the tried-and-true Doctrine DBAL layer out of the box, but as the library employs database adapters that implement a common interface, feel free to hook in whatever you'd like!

## Roadmap

* Add caching support