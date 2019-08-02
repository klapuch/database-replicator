<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database\Postgres;

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions;
use PmgDev\DatabaseReplicator\Source\Files;

class Psql
{
	/** @var Config */
	private $adminConfig;

	/** @var string */
	private $psql;


	public function __construct(Config $adminConfig, string $psql)
	{
		$this->adminConfig = $adminConfig;
		$this->psql = $psql;
	}


	/**
	 * Run psql
	 * @return string[]
	 * @throws Exceptions\ImportFilesFailedException
	 */
	public function importFiles(Files $files, Config $config): array
	{
		if (count($files) === 0) {
			return [];
		}

		self::envPassword($config);

		exec($this->buildCommand($config, $files), $output, $exitStatus);
		if ($exitStatus !== 0) {
			$files = implode(', ', (array) $files->getIterator());
			throw new Exceptions\ImportFilesFailedException("Files: {$files} -> Import failed:" . PHP_EOL . implode(PHP_EOL, $output));
		}

		return $output;
	}


	private function buildCommand(Config $config, Files $files): string
	{
		$filesCmd = '';
		foreach ($files as $filename) {
			if ($filesCmd !== '') {
				$filesCmd .= ' ';
			}
			$filesCmd .= sprintf('--file="%s"', $filename);
		}

		return vsprintf(
			'"%s" --variable=ON_ERROR_STOP=1 --host=%s --port=%s --username=%s %s %s 2>&1', [
				$this->psql,
				$config->host,
				$config->port,
				$config->username,
				$filesCmd,
				$config->database,
			]
		);
	}


	private static function envPassword(Config $config): void
	{
		putenv('PGPASSWORD=' . $config->password);
	}

}
