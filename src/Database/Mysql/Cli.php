<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Mysql;

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions\CommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\CopyCommandFailedException;
use PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException;
use PmgDev\DatabaseReplicator\Source;

class Cli
{

	/** @var string */
	private $mysql;

	/** @var string */
	private $mysqldump;

	/** @var Config */
	private $config;

	/** @var Mysqli */
	private $mysqli;

	/** @var string */
	private $tempDir = '/tmp';


	public function __construct(string $mysql, string $mysqldump, Config $config, Mysqli $mysqli)
	{
		$this->mysql = $mysql;
		$this->mysqldump = $mysqldump;
		$this->config = $config;
		$this->mysqli = $mysqli;
	}


	public function setTempDir(string $tempDir): void
	{
		$this->tempDir = $tempDir;
	}


	public function copy(Config $source, string $cloneDb): void
	{
		$newConfig = clone $source;
		$newConfig->database = $cloneDb;

		$file = sprintf('%s/%s.sql', $this->tempDir, $source->database);
		$this->dumpDatabase($file, $source);
		$this->importDatabase($file, $newConfig);
	}


	public function importFiles(Source\Files $files, Config $config): void
	{
		foreach ($files as $filepath) {
			try {
				$this->exec(sprintf('%s < "%s"', self::cli($this->mysql, $config, '-r'), $filepath), $config);
			} catch (CommandFailedException $e) {
				throw new ImportFilesFailedException($filepath, $e->getCode(), $e);
			}
		}
	}


	private function exec(string $command, Config $config): array
	{
		self::envPassword($config);
		exec($command, $output, $exitStatus);
		if ($exitStatus !== 0) {
			$errMsg = "Command: {$command} -> Import failed:";
			throw new CommandFailedException($errMsg . PHP_EOL . implode(PHP_EOL, $output));
		}

		return $output;
	}


	private static function cli(string $command, Config $config, string $parameters = ''): string
	{
		if ($parameters !== '') {
			$parameters .= ' ';
		}
		return vsprintf('"%s" --host=%s --port=%s --user=%s %s%s', [
			$command,
			$config->host,
			$config->port,
			$config->username,
			$parameters,
			$config->database,
		]);
	}


	private static function envPassword(Config $config): void
	{
		putenv('MYSQL_PWD=' . $config->password);
	}


	private function dumpDatabase(string $file, Config $config): void
	{
		if (is_file($file)) {
			return;
		}
		$filename = basename($file, '.sql');
		$logFile = $this->tempDir . "/{$filename}-dump-error.log";
		$cli = sprintf('%s > "%s"',
			self::cli($this->mysqldump, $config, sprintf('--log-error="%s" --routines --triggers --events --single-transaction --compress --skip-add-drop-table --skip-comments --skip-add-locks', $logFile)),
			$file
		);

		try {
			$this->exec($cli, $config);
		} catch (CommandFailedException $e) {
			is_file($file) && unlink($file);
			throw new CopyCommandFailedException($e->getMessage(), $e->getCode(), $e);
		}
	}


	private function importDatabase(string $file, Config $config)
	{
		$this->mysqli->create($config);

		$cli = sprintf('%s < "%s"',
			self::cli($this->mysql, $config, '--raw --silent'),
			$file
		);

		try {
			$this->exec($cli, $config);
		} catch (CommandFailedException $e) {
			$this->mysqli->drop($config->database);
			throw new CopyCommandFailedException($e->getMessage(), $e->getCode(), $e);
		}
	}

}
