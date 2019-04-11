<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Exceptions\CommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\CopyCommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException;
use PmgDev\DatabaseReplicator\Source\Files;

class CommandMock implements Command
{
	private const DB_FAILED = '_test_f7922c4363a716c12037129efe80f8f3';

	/** @var Config|NULL */
	private $config;

	/** @var string[] */
	private $databases = [];

	private $badDatabase = '';


	public function drop(string $database): void
	{
		$this->config = NULL;
	}


	public function copy(string $sourceDb, string $cloneDb, Config $config): void
	{
		if ($sourceDb === '_test_07603fbe2b569f7a24933d3147678870' && $this->badDatabase === '') {
			$this->badDatabase = $sourceDb;
			throw new CopyCommandFailedException($sourceDb);
		} else if (isset($this->databases[$sourceDb]) && $this->config === NULL) {
			throw new CopyCommandFailedException($sourceDb);
		}
		$this->badDatabase = '';
		$this->databases[$cloneDb] = $cloneDb;
		$this->databases[$sourceDb] = $sourceDb;
	}


	public function existsDatabase(string $database): bool
	{
		return $this->config !== NULL;
	}


	public function listDatabases(): iterable
	{
		return $this->databases;
	}


	public function create(Config $config): void
	{
		$this->config = $config;
	}


	public function importFiles(Files $filenames, Config $config): void
	{
		$failed = FALSE;
		foreach ($filenames as $filename) {
			if (basename($filename) === 'broken.sql') {
				$failed = $filename;
				break;
			}
		}
		if ($this->config === NULL) {
			throw new \RuntimeException('Bad use this Mock file.');
		}
		if ($this->config->database === self::DB_FAILED) {
			throw new ImportFilesFailedException(self::DB_FAILED);
		} else if ($failed !== FALSE) {
			throw new ImportFilesFailedException($failed);
		}
	}

}
