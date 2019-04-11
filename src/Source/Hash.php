<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Source;

use Nette\Utils\FileSystem;
use PmgDev\DatabaseReplicator\Exceptions;

class Hash
{
	/** @var string */
	private $name;

	/** @var string */
	private $tempDir = '';

	/** @var string */
	private $md5 = '';

	/** @var int */
	private $expiration = 1800; // [s] default 30 min

	/** @var Files */
	private $files;


	public function __construct(
		string $name,
		string $tempDir,
		Files $files
	)
	{
		Filesystem::createDir($tempDir);
		$this->name = $name;
		$this->tempDir = $tempDir;
		$this->files = $files;
	}


	public function getFiles(): Files
	{
		return $this->files;
	}


	public function setExpiration(int $expiration): void
	{
		$this->expiration = max($expiration, 0);
	}


	public function begin(): void
	{
		$this->removeActiveFile();
		$this->md5 = $this->md5Dry();
	}


	public function removeActiveFile(): void
	{
		@unlink($this->activeFile());
		$this->md5 = '';
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function commit(): void
	{
		if ($this->md5 === '') {
			throw new Exceptions\InvalidStateException('Let\'s start by method begin().');
		}
		file_put_contents($this->activeFile(), $this->md5);
	}


	public function md5(): string
	{
		if ($this->md5 === '') {
			$filePath = $this->activeFile();
			if (!is_file($this->activeFile()) || (time() - filemtime($filePath) >= $this->expiration)) {
				throw new Exceptions\ActiveFileNotFoundException($this->activeFile());
			}
			$this->md5 = (string) file_get_contents($this->activeFile());

			if ($this->md5 === '') {
				throw new Exceptions\ActiveFileNotFoundException($this->activeFile());
			}
		}
		return $this->md5;
	}


	private function activeFile(): string
	{
		return $this->tempDir . DIRECTORY_SEPARATOR . $this->name;
	}


	private function md5Dry(): string
	{
		return md5($this->getName() . $this->md5Files());
	}


	private function md5Files(): string
	{
		$token = '';
		foreach ($this->files as $filepath) {
			$token .= '.' . md5_file($filepath);
		}
		return $token;
	}

}
