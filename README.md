# Engine
A simple Doctrine-based library for handling nodes and edges in a transactional database. Originally built to provide repository-like ease for [Halligan](https://github.com/stratedge/halligan), a web-based fire department management framework.

## Installation

### Composer

Engine is not presently registered with Packagist, so it must be registered as a VCS repository.

```
{
	"require": {
		"stratedge/engine": "@dev"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/stratedge/engine"
		}
	]
}
```

## Usage

```php
<?php
	
//Create Doctrine Connection
$config = new Configuration();

$connectionParams = [
	'url' => 'mysql://username:password@host:port/db_name?charset=utf8'
];

$connection = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

//--- BEGIN SETUP ---//

//Setup nodes with database connection
$user = new Your\Namespace\Entities\Nodes\User($connection);
$post = new Your\Namespace\Entities\Nodes\Post($connection);

//Setup node repositories with reference to the correct node
Your\Namespace\Repositories\Nodes\User::register($user);
Your\Namespace\Repositories\Nodes\Post::register($post);

//Setup edges with database connection
$posted_by = new Your\Namespace\Entities\Edges\PostedBy($connection);

//Setup edge repositories with reference to the correct edge and related nodes
Your\Namespace\Repositories\Edges\PostedBy::register($posted_by, $post, $user);

//--- END SETUP ---//

//Create a new user node
$new_user = Your\Namespace\Repositories\Nodes\User::create([
	'first_name' => 'John',
	'last_name' => 'Smith'
]);

//Create a new post node
$new_post = Your\Namespace\Repositories\Nodes\Post::create([
	'post_body' => 'Lorem ipsum dolor sit amet'
]);

//Associate the user to the post
$new_posted_by = Your\Namespace\Repositories\Edges\PostedBy::create($new_post, $new_user);

//Find all posts by user
$posts = Your\Namespace\Repositories\Edges\PostedBy::findOppositeNodes($new_user);

//Find user that posted post
$the_user = Your\Namespace\Repositories\Edges\PostedBy::findOppositeNode($new_post);

//Load a particular post
$specific_post = Your\Namespace\Repositories\Nodes\Post::find(12);

//Load all users by a property value or several property values
$users_with_name = Your\Namespace\Repositories\Nodes\User::findBy([
	'first_name' => 'John'
]);

//Load the first user found by property value or several property values
$user_with_name = Your\Namespace\Repositories\Nodes\User::findOneBy([
	'first_name' => 'John'
]);
```

## Concepts

### Nodes

A node represents a single row in a database as an object in code. A user or a post would be considered a node. The columns of the node's table correspond to properties in the object.

### Edges

An edge is the relationship between 2 nodes. A relationship between a user and a post may be expressed as a hash table with the name posted_by as in code as a PostedBy edge object. Edges can have properties like nodes.

### Repositories

Repositories are the go-to place for creating or finding nodes and edges. As static classes, their underlying implementations, such as which nodes and edges the repository represnets, must be registered for each repository, to make unit testing code much easier and logic visually clear.

### Built on Doctrine

Engine depends on the tried-and-true Doctrine DBAL layer.

## Roadmap

* Add caching support