<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Postgres;

use PmgDev\DatabaseReplicator;
use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Source;

class Command extends DatabaseReplicator\Database\Command
{
	/** @var PgPhp */
	private $pgPhp;

	/** @var Psql */
	private $psql;


	public function __construct(PgPhp $pgPhp, Psql $psql)
	{
		$this->pgPhp = $pgPhp;
		$this->psql = $psql;
	}


	public function drop(string $database): void
	{
		$this->pgPhp->drop($database);
	}


	public function copy(Config $source, string $cloneDb): void
	{
		$this->pgPhp->copy($source, $cloneDb);
	}


	public function existsDatabase(string $database): bool
	{
		return $this->pgPhp->existsDatabase($database);
	}


	public function listDatabases(): iterable
	{
		return $this->pgPhp->listDatabases();
	}


	public function create(Config $config): void
	{
		$this->pgPhp->create($config);
	}


	protected function commandImport(Source\Files $filenames, Config $config): void
	{
		$this->psql->importFiles($filenames, $config);
	}

}
