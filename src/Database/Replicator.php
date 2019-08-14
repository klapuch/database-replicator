<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use PmgDev\DatabaseReplicator\Command;
use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions;
use PmgDev\DatabaseReplicator\Source;

class Replicator
{
	/** @var Command */
	private $command;

	/** @var Prefix */
	private $prefix;

	/** @var Source\Database */
	private $sourceDatabase;

	/** @var Source\Files */
	private $files;


	public function __construct(
		Command $command,
		Prefix $prefix,
		Source\Database $sourceDatabase,
		?Source\Files $files = NULL
	)
	{
		$this->command = $command;
		$this->prefix = $prefix;
		$this->sourceDatabase = $sourceDatabase;
		$this->files = $files ?? new Source\Files();
	}


	public function copy(): Config
	{
		$name = $this->databaseName();

		try {
			$config = $this->copyDatabase($name);
		} catch (Exceptions\CopyCommandFailedException $e) {
			$this->sourceDatabase->build();
			$config = $this->copyDatabase($name);
		}

		$config->database = $name;
		try {
			$this->command->importFiles($this->files, $config);
		} catch (Exceptions\ImportFilesFailedException $e) {
			$this->command->drop($name);
			throw $e;
		}

		return $config;
	}


	public function drop(string $database): void
	{
		$this->command->drop($database);
	}


	/**
	 * @param bool|string[] $keepDatabases
	 * @return string[][]
	 */
	public function clearDatabases($keepDatabases = TRUE): array
	{
		$listDatabases = $this->generateListDatabases($keepDatabases);
		$kept = $removed = [];
		$dbPrefix = $this->prefix->prefix();
		foreach ($this->command->listDatabases() as $database) {
			if (preg_match('~^' . $dbPrefix . '[a-z0-9]{32}.*$~', $database)) {
				if (in_array($database, $listDatabases, TRUE)) {
					$kept[] = $database;
				} else {
					$this->drop($database);
					$removed[] = $database;
				}
			}
		}
		return ['removed' => $removed, 'kept' => $kept];
	}


	/**
	 * @param bool|string[] $keepDatabases
	 * @return string[]
	 */
	private function generateListDatabases($keepDatabases): array
	{
		try {
			if ($keepDatabases === TRUE) {
				$listDatabases = [$this->prefix->database()];
			} else if (is_array($keepDatabases)) {
				$keepDatabases[] = $this->prefix->database();
				$listDatabases = $keepDatabases;
			} else {
				$listDatabases = [];
			}
		} catch (Exceptions\ActiveFileNotFoundException $e) {
			$listDatabases = [];
		}
		return $listDatabases;
	}


	private function generateName(): string
	{
		static $i = 0;
		$name = $this->prefix->database() . '_' . date('His') . '_' . getmypid() . '_' . $i;
		++$i;
		return $name;
	}


	private function databaseName(): string
	{
		try {
			$name = $this->generateName();
		} catch (Exceptions\ActiveFileNotFoundException $e) {
			$this->sourceDatabase->build();
			$name = $this->generateName();
		}
		return $name;
	}


	private function copyDatabase(string $name): Config
	{
		$config = $this->prefix->config();
		$this->command->copy($config, $name);
		return $config;
	}

}
