<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Mysql;

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions\ConnectionFailedException;

class Mysqli
{
	/** @var Config */
	private $config;

	/** @var \mysqli */
	private $mysqli;


	public function __construct(Config $config)
	{
		$this->config = $config;
	}


	public function drop(string $database): void
	{
		$this->query('DROP DATABASE IF EXISTS %s', $database);
	}


	public function existsDatabase(string $database): bool
	{
		$result = $this->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s'", $database);
		$exists = $result->fetch_row() !== NULL;
		$result->free();
		return $exists;
	}


	/**
	 * @return iterable|string[]
	 */
	public function listDatabases(): iterable
	{
		$result = $this->query('SHOW DATABASES');

		static $ignoreDb = [
			'information_schema',
			'mysql',
			'performance_schema',
			'sys',
		];

		$databases = [];
		while ($row = $result->fetch_assoc()) {
			if (in_array($row['Database'], $ignoreDb, TRUE)) {
				continue;
			}
			$databases[] = $row['Database'];
		}

		$result->free();
		return $databases;
	}


	public function create(Config $config): void
	{
		$this->query('CREATE DATABASE %s', $config->database);
	}


	private function query(string $sql, string ...$arguments)
	{
		return $this->resource()->query(vsprintf($sql, $arguments));
	}


	private function resource()
	{
		if ($this->mysqli === NULL) {
			$config = $this->config;
			$mysqli = @new \mysqli($config->host, $config->username, $config->password, $config->database, $config->port);
			if ($mysqli->connect_errno !== 0) {
				throw new ConnectionFailedException($mysqli->connect_error, $mysqli->connect_errno);
			}
			$this->mysqli = $mysqli;
		}
		return $this->mysqli;
	}

}
