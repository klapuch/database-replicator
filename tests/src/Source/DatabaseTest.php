<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use PmgDev\DatabaseReplicator\Exceptions\ImportFilesFailedException;
use PmgDev\DatabaseReplicator\TestBuilder;
use PmgDev\DatabaseReplicator\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class DatabaseTest extends TestCase
{

	public function testImportFailed()
	{
		$builder = new TestBuilder();
		$files = $builder->createFiles(Utils::platformDir() . '/update');
		$sourceHash = $builder->sourceHash($files);
		$sourceDatabase = $builder->sourceDatabase($sourceHash);
		Assert::exception(static function () use ($sourceDatabase): void {
			$sourceDatabase->build();
		}, ImportFilesFailedException::class);
		$sourceHash->removeActiveFile();
	}


	public function testBuild()
	{
		$builder = new TestBuilder();
		$files = $builder->createFiles(Utils::platformDir() . '/data.sql');
		$sourceHash = $builder->sourceHash($files);
		$sourceDatabase = $builder->sourceDatabase($sourceHash);
		Assert::false($sourceDatabase->build());
		Assert::true($sourceDatabase->build());
		$sourceHash->removeActiveFile();
	}

}

(new DatabaseTest)->run();
