<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use Nette\Utils\Finder;
use PmgDev\DatabaseReplicator\Exceptions\FileNotFoundException;

final class Files implements \IteratorAggregate, \Countable
{
	/** @var string[] */
	private $files = [];


	public function __construct(array $files = [])
	{
		$this->addFiles($files);
	}


	public function addDirectory(string $directory, string $mask = '*.sql'): void
	{
		$this->addFiles(self::scanDirectory($directory, $mask));
	}


	public function addFiles(iterable $files): void
	{
		foreach ($files as $file) {
			$this->addFile($file);
		}
	}


	public function addFile(string $file): void
	{
		if (!is_file($file)) {
			throw new FileNotFoundException($file);
		}
		$this->files[] = $file;
	}


	public function getIterator(): \Iterator
	{
		return new \ArrayIterator($this->files);
	}


	public function count()
	{
		return count($this->files);
	}


	/**
	 * @return \SplFileInfo[]
	 */
	private static function scanDirectory(string $directory, string $mask): array
	{
		$finder = Finder::findFiles($mask)->in($directory);
		$files = [];
		foreach ($finder as $file) {
			/** @var \SplFileInfo $file */
			$files[] = $file->getPathname();
		}
		sort($files);
		return $files;
	}

}
