<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Database\Replicator;
use PmgDev\DatabaseReplicator\Exceptions\InvalidStateException;

abstract class Database implements DatabaseConnection
{
	/** @var Replicator */
	private $replicator;

	/** @var Config|NULL */
	private $config;

	/** @var object|NULL */
	private $connection;


	public function __construct(Replicator $replicator)
	{
		$this->replicator = $replicator;
	}


	public function create()/*: object php 7.2+*/
	{
		$this->config = $this->createDatabase();
		$this->connection = $this->createConnection($this->config);
		return $this->connection;
	}


	public function drop(): void
	{
		$this->disconnectConnection($this->getConnection());
		$this->dropDatabase($this->getConfig());
		$this->connection = NULL;
		$this->config = NULL;
	}


	public function getConnection()/*: object php 7.2+*/
	{
		if ($this->connection === NULL) {
			throw new InvalidStateException('Connection does not exists, you must call create() method first.');
		}
		return $this->connection;
	}


	protected function dropDatabase(Config $config): void
	{
		$this->replicator->drop($config->database);
	}


	protected function createDatabase(): Config
	{
		return $this->replicator->copy();
	}


	final protected function getConfig(): Config
	{
		if ($this->config === NULL) {
			throw new InvalidStateException('Config does not exists, you must call create() method first.');
		}
		return $this->config;
	}


	abstract protected function createConnection(Config $config)/*: object php 7.2+*/;


	abstract protected function disconnectConnection(/*: object php 7.2+*/ $connection): void;

}
