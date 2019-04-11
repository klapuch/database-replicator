<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use PmgDev\DatabaseReplicator\TestBuilder;
use PmgDev\DatabaseReplicator\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class HashTest extends TestCase
{

	public function testCheckMd5()
	{
		$builder = new TestBuilder();
		$files = $builder->createFiles();
		$sourceHash = $builder->sourceHash($files);
		$sourceHash->begin();
		Assert::same('e2171c90f6243b03ce3ccfa85b7a1df5', $sourceHash->md5());
		$sourceHash->commit();

		// load from file
		$sourceHash = $builder->sourceHash();
		$sourceHash->setExpiration(10);
		Assert::same('e2171c90f6243b03ce3ccfa85b7a1df5', $sourceHash->md5());
		$sourceHash->removeActiveFile();
	}


	/**
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\ActiveFileNotFoundException
	 */
	public function testThrowActiveFileNotFound()
	{
		$builder = new TestBuilder();
		$sourceHash = $builder->sourceHash();
		$sourceHash->md5();
	}


	public function testThrowActiveFileNotFoundEmptySource()
	{
		$builder = new TestBuilder();
		$sourceHash = $builder->sourceHash();
		$sourceHash->setExpiration(10);
		$sourceHash->begin();
		Assert::same('e2171c90f6243b03ce3ccfa85b7a1df5', $sourceHash->md5());
		$sourceHash->commit();

		file_put_contents(Utils::TEMP_DIR . '/' . $builder->config()->database, '');

		$sourceHash = $builder->sourceHash();
		Assert::exception(static function () use ($sourceHash): void {
			$sourceHash->md5();
		}, \PmgDev\DatabaseReplicator\Exceptions\ActiveFileNotFoundException::class);
		$sourceHash->removeActiveFile();
	}


	/**
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\FileNotFoundException
	 */
	public function testThrowExceptionSourceFileNotFound()
	{
		new Hash('test', '/tmp', new Files(['/uknown/file']));
	}


	/**
	 * @throws \PmgDev\DatabaseReplicator\Exceptions\InvalidStateException
	 */
	public function testThrowExceptionCallCommitBeforeBegin()
	{
		$builder = new TestBuilder();
		$sourceHash = $builder->sourceHash();
		$sourceHash->commit();
	}

}

(new HashTest())->run();
