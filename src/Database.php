<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

class Database
{
	/** @var ConnectionFactory */
	private $connectionFactory;

	/** @var object|NULL custom connection */
	private $connection;


	public function __construct(ConnectionFactory $connectionFactory)
	{
		$this->connectionFactory = $connectionFactory;
	}


	public function create()/*: object*/
	{
		return $this->connection = $this->connectionFactory->create();
	}


	public function drop(): void
	{
		$this->connectionFactory->drop($this->get());
		$this->connection = NULL;
	}


	public function get()/*: object*/
	{
		if ($this->connection === NULL) {
			throw new Exceptions\InvalidStateException('Connection does not exists, you must call create() method first.');
		}
		return $this->connection;
	}

}
