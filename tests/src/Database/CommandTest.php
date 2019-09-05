<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use Nette\Neon\Neon;
use PmgDev\DatabaseReplicator\Command;
use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException;
use PmgDev\DatabaseReplicator\Source\Files;
use PmgDev\DatabaseReplicator\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class CommandTest extends TestCase
{

	/**
	 * @dataProvider ../../temp/platforms.php
	 */
	public function testCreateDrop(string $platform)
	{
		$data = self::createData($platform);
		$config = self::createConfig($data);
		$command = self::createCommand($platform, $data['command'], clone $config);

		$config->database = '_database_producer_test';
		$cloneDb = $config->database . '2';

		// only for debug
		// $command->drop($config->database);
		// $command->drop($cloneDb);

		Assert::false($command->existsDatabase($config->database));
		Assert::false($command->existsDatabase($cloneDb));
		$command->create($config);
		$dir = Utils::platformDir($platform);
		$command->importFiles(new Files([$dir . '/structure.sql', $dir . '/data.sql']), $config);

		$command->copy($config, $cloneDb);

		$command->importFiles(new Files(), $config);

		$i = 0;
		foreach ($command->listDatabases() as $database) {
			if (in_array($database, [$cloneDb, $config->database])) {
				Assert::true($command->existsDatabase($database));
				$command->drop($database);
				Assert::false($command->existsDatabase($database));
				++$i;
			}
		}
		Assert::same(2, $i);
	}


	private static function createData(string $platform): array
	{
		$neon = Utils::DATA_DIR . '/database.neon';
		if (getenv('USER') === 'travis') {
			$neon = Utils::platformDir($platform) . '/travis.neon';
			$data = Neon::decode((string) file_get_contents($neon));
		} elseif (!is_file($neon)) {
			throw new \RuntimeException(sprintf('File does not exist "%s". Let\'s see "%s"', $neon, Utils::DATA_DIR . '/database.example.neon'));
		} else {
			$data = Neon::decode((string) file_get_contents($neon))[$platform];
		}

		return $data;
	}


	/**
	 * @dataProvider ../../temp/platforms.php
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\ConnectionFailedException
	 */
	public function testThrowFailedConnection(string $platform)
	{
		$data = self::createData($platform);
		$config = self::createConfig($data);
		$config->database = $config->database . '_test';
		$command = self::createCommand($platform, $data['command'], $config);
		$command->create($config);
	}


	/**
	 * @dataProvider ../../temp/platforms.php
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\CopyCommandFailedException
	 */
	public function testThrowFailedCopy(string $platform)
	{
		$data = self::createData($platform);
		$config = self::createConfig($data);
		$command = self::createCommand($platform, $data['command'], $config);
		$sourceConfig = clone $config;
		$sourceConfig->database = '_xxx_';
		$command->copy($sourceConfig, '_yyy_');
	}


	/**
	 * @dataProvider ../../temp/platforms.php
	 */
	public function testImportFailed(string $platform)
	{
		$data = self::createData($platform);
		$config = self::createConfig($data);
		$command = self::createCommand($platform, $data['command'], $config);
		$sourceConfig = clone $config;

		$copyDb = '_yyy_';
		$copyConfig = clone $config;
		$copyConfig->database = $copyDb;

		$command->drop($copyDb);;
		$command->copy($sourceConfig, $copyDb);
		Assert::true($command->existsDatabase($copyDb));

		Assert::exception(function() use ($platform, $command, $copyConfig) {
			$dir = Utils::platformDir($platform);
			$command->importFiles(new Files([$dir . '/broken.sql']), $copyConfig);
		}, ImportFilesFailedException::class);

		Assert::false($command->existsDatabase($copyDb));
	}


	private static function createConfig(array $data)
	{
		return new Config($data['database'], $data['username'], $data['password'], $data['host'], $data['port']);
	}


	private static function createCommand(string $platform, string $command, Config $config): Command
	{
		if ($platform === Utils::PGSQL) {
			return (new Postgres\CommandFactory($command))->create($config);
		}
		throw new \RuntimeException('Command factory not found.');
	}

}

(new CommandTest())->run();
