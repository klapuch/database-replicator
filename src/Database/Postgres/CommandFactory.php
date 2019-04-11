<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Postgres;

use PmgDev\DatabaseReplicator;

class CommandFactory
{
	/** @var string */
	private $psql;


	public function __construct(string $psql = '/usr/bin/psql')
	{
		$this->psql = $psql;
	}


	public function create(DatabaseReplicator\Config $config): DatabaseReplicator\Command
	{
		return new Command($this->createPgPhp($config), $this->createPsql($config));
	}


	private function createPgPhp(DatabaseReplicator\Config $config): PgPhp
	{
		return new PgPhp($config);
	}


	private function createPsql(DatabaseReplicator\Config $config): Psql
	{
		return new Psql($config, $this->psql);
	}

}
