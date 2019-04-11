<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\Utils\FileSystem;
use PmgDev\DatabaseReplicator\Source;
use PmgDev\DatabaseReplicator\Source\Files;

abstract class Builder
{
	/** @var string */
	private $sourceFile;

	/** @var Config */
	private $config;

	/** @var Command */
	private $command;

	/** @var string */
	private $tempDir = '/tmp';


	public function __construct(string $sourceFile, Config $config, Command $command)
	{
		$this->sourceFile = $sourceFile;
		$this->config = $config;
		$this->command = $command;
	}


	final public function setTempDir(string $tempDir): void
	{
		FileSystem::createDir($tempDir);
		$this->tempDir = $tempDir;
	}


	final public function createDatabase(): Database
	{
		return new Database($this->createConnectionFactory());
	}


	abstract protected function createConnectionFactory(): ConnectionFactory;


	final protected function createDatabaseReplicator(
		?Files $files = NULL,
		?Files $dynamicFiles = NULL
	): Database\Replicator
	{
		$sourceHash = $this->createSourceHash($files);
		$databasePrefix = $this->createDatabasePrefix($sourceHash);
		$sourceDatabase = $this->createSourceDatabase($databasePrefix, $sourceHash);
		return new Database\Replicator($this->command, $databasePrefix, $sourceDatabase, $dynamicFiles);
	}


	final protected function createSourceHash(?Files $files = NULL): Source\Hash
	{
		if ($files === NULL) {
			$files = $this->createSourceFiles();
		}
		return new Source\Hash($this->config->database, $this->tempDir, $files);
	}


	final protected function createSourceFiles()
	{
		return new Files([$this->sourceFile]);
	}


	final protected function createDatabasePrefix(Source\Hash $sourceHash): Database\Prefix
	{
		return new Database\Prefix($this->getConfig(), $sourceHash);
	}


	final protected function getConfig(): Config
	{
		return clone $this->config;
	}


	final protected function createSourceDatabase(
		Database\Prefix $prefix,
		Source\Hash $sourceHash
	): Source\Database
	{
		return new Source\Database($prefix, $sourceHash, $this->command);
	}


	final protected function getCommand(): Command
	{
		return $this->command;
	}

}
