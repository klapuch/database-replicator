<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Mysql;

use PmgDev\DatabaseReplicator;
use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Source;

class Command extends DatabaseReplicator\Database\Command
{

	/** @var Cli */
	private $mysql;

	/** @var Mysqli */
	private $mysqli;


	public function __construct(Cli $mysql, Mysqli $mysqli)
	{
		$this->mysql = $mysql;
		$this->mysqli = $mysqli;
	}


	public function drop(string $database): void
	{
		$this->mysqli->drop($database);
	}


	public function copy(Config $config, string $cloneDb): void
	{
		$this->mysql->copy($config, $cloneDb);
	}


	public function existsDatabase(string $database): bool
	{
		return $this->mysqli->existsDatabase($database);
	}


	public function listDatabases(): iterable
	{
		return $this->mysqli->listDatabases();
	}


	public function create(Config $config): void
	{
		$this->mysqli->create($config);
	}


	protected function commandImport(Source\Files $files, DatabaseReplicator\Config $config): void
	{
		$this->mysql->importFiles($files, $config);
	}

}
