<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use PmgDev\DatabaseReplicator\Exceptions\InvalidStateException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class DatabaseTest extends TestCase
{

	public function testBasic()
	{
		$builder = new TestBuilder();
		$database = $builder->createDatabase();
		$connection = $database->create();
		Assert::same($connection, $database->getConnection());
		$database->drop();

		Assert::exception(static function () use ($builder): void {
			$database = $builder->createDatabase();
			$database->getConnection();
		}, InvalidStateException::class, 'Connection does not exists, you must call create() method first.');
	}


	public function testThrowSkipCreate()
	{
		$builder = new TestBuilder();
		Assert::exception(static function () use ($builder): void {
			$database = $builder->createDatabase();
			$database->getConnection();
		}, InvalidStateException::class, 'Connection does not exists, you must call create() method first.');

		Assert::exception(static function () use ($builder): void {
			$database = $builder->createDatabase();
			$database->drop();
		}, InvalidStateException::class, 'Connection does not exists, you must call create() method first.');

		Assert::exception(static function () use ($builder): void {
			(new DatabaseMock);
		}, InvalidStateException::class, 'Config does not exists, you must call create() method first.');
	}

}

class DatabaseMock extends Database
{

	public function __construct()
	{
		$this->getConfig();
	}


	protected function createConnection(Config $config)
	{
	}


	protected function disconnectConnection($connection): void
	{
	}

}

(new DatabaseTest())->run();
