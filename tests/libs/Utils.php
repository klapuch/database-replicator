<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\Utils\Finder;

class Utils
{
	public const PGSQL = 'pgsql';

	public const TEMP_DIR = __DIR__ . '/../temp';
	public const DATA_DIR = __DIR__ . '/../data';


	public static function platformDir(string $platform = self::PGSQL): string
	{
		return self::DATA_DIR . sprintf('/%s', $platform);
	}


	public static function saveForProvider(): void
	{
		$file = self::TEMP_DIR . '/platforms.php';
		if (is_file($file)) {
			return;
		}
		$content = '<?php return [';

		foreach (Finder::findDirectories('*')->in(self::DATA_DIR) as $dir) {
			/** @var \SplFileInfo $dir */
			$platform = $dir->getFilename();
			$content .= "'$platform' => ['platform' => '$platform'],";
		}
		$content .= '];';
		file_put_contents($file, $content);
	}

}
