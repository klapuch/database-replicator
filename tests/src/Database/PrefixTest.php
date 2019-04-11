<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use PmgDev\DatabaseReplicator\TestBuilder;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class PrefixTest extends TestCase
{

	public function testBasic()
	{
		$builder = new TestBuilder();
		$sourceFile = $builder->sourceHash();
		$sourceFile->begin();
		$databasePrefix = $builder->databasePrefix($sourceFile);
		Assert::same([
			'database' => '_test_e2171c90f6243b03ce3ccfa85b7a1df5',
			'username' => 'test',
			'password' => '',
			'host' => 'localhost',
			'port' => 5432,
		], (array) $databasePrefix->config());
		$sourceFile->removeActiveFile();
	}

}

(new PrefixTest())->run();
