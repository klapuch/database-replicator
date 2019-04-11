# Database Replicator

[![Build Status](https://travis-ci.org/pmgdev/database-replicator.svg?branch=master)](https://travis-ci.org/pmgdev/database-replicator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/pmgdev/database-replicator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pmgdev/database-replicator/?branch=master)
[![Downloads this Month](https://img.shields.io/packagist/dm/pmgdev/database-replicator.svg)](https://packagist.org/packages/pmgdev/database-replicator)
[![Latest stable](https://img.shields.io/packagist/v/pmgdev/database-replicator.svg)](https://packagist.org/packages/pmgdev/database-replicator)
[![Coverage Status](https://coveralls.io/repos/github/pmgdev/database-replicator/badge.svg?branch=master)](https://coveralls.io/github/pmgdev/database-replicator?branch=master)

This is simple library whose prepare database for each test.

### Support databases

- for this moment only postgres
- here is interface [Command](src/Command.php) for implement other databases
- for add new database let's create pull request

### Extensions
- [Nette framework](https://github.com/pmgdev/database-replicator-nette)

### Install by composer

```bash
composer require --dev pmgdev/database-replicator
```

## How to use

Everybody can have different database layout. Here is prepared interface [ConnectionFactory](src/ConnectionFactory.php), where we must create instance of our database layout like (PDO, Doctrine, our implementation etc.) We need one connection with the permission to create and to drop database. And secondary connection for standard to use.

1) we implement [ConnectionFactory](src/ConnectionFactory.php)

    - we need create dependency on [Database\Replicator](src/Database/Replicator.php)
        - method **copy()** for create new database
        - method **drop()** for remove used database

2) here is prepared [Builder](src/Builder.php), we can extend it and implement method **createConnectionFactory**, where return our implementation from point 1)

#### Example

ConnectionFactory
```php
<?php

use PmgDev\DatabaseReplicator;

class MyConnectionFactory implements PmgDev\DatabaseReplicator\ConnectionFactory 
{
	/** @var DatabaseReplicator\Database\Replicator */
	private $databaseReplicator;


	public function __construct(DatabaseReplicator\Database\Replicator $databaseReplicator)
	{
		$this->databaseReplicator = $databaseReplicator;  
	}

	public function create(): object
	{
		$config = $this->databaseReplicator->copy();
		return MyConnection($config);
	}

	public function drop($connection): void
	{
		$db = $connection->getDatabase();
		$connection->diconnect();
		$this->databaseReplicator->drop($db);
	}

}
```

Builder
```php
<?php

use PmgDev\DatabaseReplicator\Source;

class MyBuilder extends PmgDev\DatabaseReplicator\Builder 
{

	protected function createConnectionFactory(): PmgDev\DatabaseReplicator\ConnectionFactory 
	{
		// these instances of Files are optional
		
		// if you have additional files for complete source of database
		$files = $this->createSourceFiles();
		$files->addFile('data.sql');
		$files->addDirectory('/sql/path');
		
		// import dynamic data, like a current_timestamp, to every copy of original database
		$dynamicFiles = new Source\Files;
		$dynamicFiles->addFile('dynamic.data.sql');
		
		return MyConnectionFactory($this->createDatabaseReplicator($files, $dynamicFiles));
	}

}
```

#### Start using
```php
<?php

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Database;
use PmgDev\DatabaseReplicator\Database\Postgres;

$adminConfig = new Config('posgres', 'posgres', '***', 'localhost', 5432); // permission for create and drop databases
$command = (new Postgres\CommandFactory())->create($adminConfig); // prepared Command for postgres, this we can replace by other Command

$sourceFile = '/path/to/your/structure.sql'; // here is sql how look like database
$config = new Config('test', 'joe', '***', 'localhost', 5432); // standard config

/** @var Database $database */
$database = (new MyBuilder($sourceFile, $config, $command))->createDatabase();

$connection = $database->create(); // startup

var_dump($connection); // our connection instance of MyConnection
// make test

$database->drop(); // remove database
```
