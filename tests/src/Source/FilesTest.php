<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use PmgDev\DatabaseReplicator\Utils;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class FilesTest extends TestCase
{

	public function testBasic()
	{
		$dir = Utils::platformDir();
		$files = new Files();
		$files->addFile($dir . '/travis.neon');
		$files->addFiles([$dir . '/structure.sql', $dir . '/data.sql']);
		$files->addDirectory($dir . '/update');

		$list = [];
		foreach ($files as $file) {
			$list[] = $file;
		}
		Assert::same([
			$dir . '/travis.neon',
			$dir . '/structure.sql',
			$dir . '/data.sql',
			$dir . '/update/01-users.sql',
			$dir . '/update/02-users.sql',
		], $list);
	}

}

(new FilesTest())->run();
