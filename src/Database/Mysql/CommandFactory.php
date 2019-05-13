<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Mysql;

use PmgDev\DatabaseReplicator\Config;

class CommandFactory
{
	/** @var string */
	private $tempDir;

	/** @var string */
	private $mysql;

	/** @var string */
	private $mysqldump;


	public function __construct(string $tempDir = '/tmp', string $mysql = '/usr/bin', string $mysqldump = NULL)
	{
		if ($mysqldump === NULL) {
			$dir = $mysql;
			$mysql = $dir . DIRECTORY_SEPARATOR . 'mysql';
			$mysqldump = $dir . DIRECTORY_SEPARATOR . 'mysqldump';
		}

		$this->tempDir = $tempDir;
		$this->mysql = $mysql;
		$this->mysqldump = $mysqldump;
	}


	public function create(Config $config): Command
	{
		$mysqli = $this->createMysqli($config);
		return new Command($this->createCli($config, $mysqli), $mysqli);
	}


	private function createCli(Config $config, Mysqli $mysqli): Cli
	{
		$cli = new Cli($this->mysql, $this->mysqldump, $config, $mysqli);
		$cli->setTempDir($this->tempDir);
		return $cli;
	}


	private function createMysqli(Config $config): Mysqli
	{
		return new Mysqli($config);
	}

}
