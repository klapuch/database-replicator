<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use PmgDev\DatabaseReplicator\Command;
use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Database as PDDatabase;
use PmgDev\DatabaseReplicator\Exceptions;

class Database
{
	/** @var PDDatabase\Prefix */
	private $prefix;

	/** @var Hash */
	private $sourceHash;

	/** @var Command */
	private $command;


	public function __construct(
		PDDatabase\Prefix $prefix,
		Hash $sourceHash,
		Command $command
	)
	{
		$this->prefix = $prefix;
		$this->sourceHash = $sourceHash;
		$this->command = $command;
	}


	public function build(): bool
	{
		$this->sourceHash->begin();
		$config = $this->prefix->config();
		if ($this->command->existsDatabase($config->database)) {
			$this->sourceHash->commit();
			return TRUE;
		}
		$this->command->create($config);
		$this->importSourceFiles($config);
		$this->sourceHash->commit();
		return FALSE;
	}


	private function importSourceFiles(Config $config): void
	{
		try {
			$this->command->importFiles($this->sourceHash->getFiles(), $config);
		} catch (Exceptions\ImportFilesFailedException $e) {
			$this->command->drop($config->database);
			throw $e;
		}
	}

}
