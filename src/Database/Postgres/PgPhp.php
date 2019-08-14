<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Postgres;

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions\CommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\ConnectionFailedException;
use PmgDev\DatabaseReplicator\Exceptions\CopyCommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\LostConnectionException;

class PgPhp
{
	/** @var Config */
	private $config;

	/** @var resource */
	private $resource;


	public function __construct(Config $adminConfig)
	{
		$this->config = $adminConfig;
	}


	public function copy(Config $source, string $cloneDb): void
	{
		try {
			$this->sql('CREATE DATABASE %s WITH TEMPLATE %s OWNER %s', $cloneDb, $source->database, $source->username);
		} catch (CommandFailedException $e) {
			throw new CopyCommandFailedException($e->getMessage(), $e->getCode(), $e);
		}
	}


	public function drop(string $database): void
	{
		$this->sql('DROP DATABASE IF EXISTS %s', $database);
	}


	public function existsDatabase(string $database): bool
	{
		$resource = $this->sql("SELECT 1 AS exists FROM pg_database WHERE datname = '%s'", $database);
		$data = pg_fetch_assoc($resource);
		return $data !== FALSE && isset($data['exists']);
	}


	public function listDatabases()
	{
		$resource = $this->sql('SELECT datname FROM pg_database WHERE datistemplate = false');
		$databases = [];
		while ($row = pg_fetch_assoc($resource)) {
			$databases[] = $row['datname'];
		}
		return $databases;
	}


	public function create(Config $config): void
	{
		$this->sql('CREATE DATABASE %s OWNER %s', $config->database, $config->username);
	}


	private function sql(string $command, ...$parameters)
	{
		try {
			return $this->query($command, $parameters);
		} catch (LostConnectionException $e) {
			if (pg_connection_reset($this->resource) === FALSE) {
				throw new ConnectionFailedException('Connection is lost.', 0, $e);
			}
			return $this->query($command, $parameters);
		}
	}


	private function resource()
	{
		if ($this->resource === NULL) {
			$resource = @pg_connect(vsprintf('host=%s port=%s dbname=%s user=%s password=%s', [
				$this->config->host,
				$this->config->port,
				$this->config->database,
				$this->config->username,
				$this->config->password,
			]));
			if ($resource === FALSE) {
				throw new ConnectionFailedException('Check your credentials for database.');
			}
			$this->resource = $resource;
		}
		return $this->resource;
	}


	private function isLostConnection(): bool
	{
		return pg_connection_status($this->resource) === PGSQL_CONNECTION_BAD;
	}


	private function query(string $command, array $parameters)
	{
		$resource = @pg_query($this->resource(), vsprintf($command, $parameters));
		if ($resource === FALSE) {
			if ($this->isLostConnection()) {
				throw new LostConnectionException();
			}
			throw new CommandFailedException(pg_last_error($this->resource));
		}
		return $resource;
	}

}
