<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use PmgDev\DatabaseReplicator;
use PmgDev\DatabaseReplicator\Exceptions;
use PmgDev\DatabaseReplicator\Source;

abstract class Command implements DatabaseReplicator\Command
{

	public function importFiles(Source\Files $files, DatabaseReplicator\Config $config): void
	{
		try {
			$this->commandImport($files, $config);
		} catch (Exceptions\ImportFilesFailedException $e) {
			$this->drop($config->database);
			throw $e;
		}
	}


	abstract protected function commandImport(Source\Files $files, DatabaseReplicator\Config $config): void;

}
