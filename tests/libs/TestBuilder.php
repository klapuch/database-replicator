<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Source\Files;

class TestBuilder extends Builder
{

	public function __construct()
	{
		$config = self::createConfigDummy();
		$this->setTempDir(Utils::TEMP_DIR);
		parent::__construct(Utils::platformDir() . '/structure.sql', $config, new CommandMock());
	}


	public function databaseReplicatorLive(): Database\Replicator
	{
		return $this->createDatabaseReplicator();
	}


	public function databaseReplicator(Source\Hash $sourceHash, Files $files = NULL): Database\Replicator
	{
		$databasePrefix = $this->createDatabasePrefix($sourceHash);
		$sourceDatabase = $this->createSourceDatabase($databasePrefix, $sourceHash);
		return new Database\Replicator($this->getCommand(), $databasePrefix, $sourceDatabase, $files);
	}


	public function config(): Config
	{
		return $this->getConfig();
	}


	public function createCliMock(): Command
	{
		return $this->getCommand();
	}


	public function sourceDatabase(Source\Hash $sourceHash): Source\Database
	{
		$databasePrefix = $this->createDatabasePrefix($sourceHash);
		return $this->createSourceDatabase($databasePrefix, $sourceHash);
	}


	public function createDatabase(): DatabaseConnection
	{
		return new ConnectionMock($this->createDatabaseReplicator());
	}


	public function sourceHash(?Files $files = NULL): Source\Hash
	{
		$sourceHash = $this->createSourceHash($files);
		$sourceHash->setExpiration(0);
		return $sourceHash;
	}


	public function createFiles(string $path = ''): Files
	{
		$files = $this->createSourceFiles();
		if (is_dir($path)) {
			$files->addDirectory($path);
		} else if (is_file($path)) {
			$files->addFile($path);
		} else if ($path !== '') {
			throw new \InvalidArgumentException('File or directory does not exists. ' . $path);
		}

		return $files;
	}


	public function databasePrefix(Source\Hash $sourceHash)
	{
		return $this->createDatabasePrefix($sourceHash);
	}


	public static function createConfigDummy(): Config
	{
		return new Config('test', 'test', '', 'localhost', 5432);
	}

}
