<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException;
use PmgDev\DatabaseReplicator\Source\Files;

interface Command
{

	function drop(string $database): void;


	function copy(string $sourceDb, string $cloneDb, Config $config): void;


	function existsDatabase(string $database): bool;


	/**
	 * @return string[]|iterable
	 */
	function listDatabases(): iterable;


	function create(Config $config): void;


	/**
	 * @throws ImportFilesFailedException
	 */
	function importFiles(Files $filenames, Config $config): void;

}
