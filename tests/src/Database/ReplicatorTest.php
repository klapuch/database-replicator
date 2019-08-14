<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use PmgDev\DatabaseReplicator\Source\Files;
use PmgDev\DatabaseReplicator\TestBuilder;
use PmgDev\DatabaseReplicator\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class ReplicatorTest extends TestCase
{

	public function testBuilder()
	{
		$replicator = (new TestBuilder())->databaseReplicatorLive();
		Assert::type(Replicator::class, $replicator);
	}


	public function testMock()
	{
		$builder = new TestBuilder();
		$sourceFile = $builder->sourceHash();
		$databaseReplicator = $builder->databaseReplicator($sourceFile);
		$cloneDb = $databaseReplicator->copy()->database;
		Assert::contains($sourceFile->md5(), $cloneDb);
		$sourceFile->removeActiveFile();
	}


	public function testNameFailed()
	{
		$builder = new TestBuilder();
		$files = $builder->createFiles(Utils::platformDir() . '/update/01-users.sql');
		$sourceHash = $builder->sourceHash($files);
		$databaseReplicator = $builder->databaseReplicator($sourceHash);
		$cloneDb = $databaseReplicator->copy()->database;
		Assert::contains($sourceHash->md5(), $cloneDb);
		$sourceHash->removeActiveFile();
	}


	public function testCopyFailed()
	{
		$builder = new TestBuilder();
		$files = $builder->createFiles(Utils::platformDir() . '/update/01-users.sql');
		$sourceHash = $builder->sourceHash($files);
		$databaseReplicator = $builder->databaseReplicator($sourceHash);
		$clone1 = $databaseReplicator->copy()->database;
		$databaseReplicator->clearDatabases(FALSE);
		$clone2 = $databaseReplicator->copy()->database;
		Assert::notSame($clone1, $clone2);
	}


	/**
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException
	 */
	public function testImportDynamicFilesFailed()
	{
		$builder = new TestBuilder();
		$sourceHash = $builder->sourceHash($builder->createFiles());
		$databaseReplicator = $builder->databaseReplicator($sourceHash, new Files([Utils::platformDir() . '/broken.sql']));
		$databaseReplicator->copy();
	}


	public function testDropDatabases()
	{
		$builder = new TestBuilder();
		$sourceFile = $builder->sourceHash();
		$databaseReplicator = $builder->databaseReplicator($sourceFile);
		$cloneDb = $databaseReplicator->copy()->database;

		$result = $databaseReplicator->clearDatabases();
		Assert::same(1, count($result['kept']));
		Assert::same(1, count($result['removed']));

		$result = $databaseReplicator->clearDatabases(FALSE);
		Assert::same(0, count($result['kept']));
		Assert::same(2, count($result['removed']));

		$result = $databaseReplicator->clearDatabases([$cloneDb]);
		Assert::same(2, count($result['kept']));
		Assert::same(0, count($result['removed']));

		$sourceFile->removeActiveFile();

		$result = $databaseReplicator->clearDatabases();
		Assert::same(0, count($result['kept']));
		Assert::same(2, count($result['removed']));
	}


	public function testCustomName()
	{
		$builder = new TestBuilder();
		$sourceFile = $builder->sourceHash();
		$databaseReplicator = $builder->databaseReplicator($sourceFile);
		$cloneDb = $databaseReplicator->copy('foo')->database;
		Assert::same('foo', $cloneDb);
		$databaseReplicator->clearDatabases([$cloneDb]);
	}

}

(new ReplicatorTest())->run();
