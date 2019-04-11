<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator\Database;

use PmgDev\DatabaseReplicator\Config;
use PmgDev\DatabaseReplicator\Source\Hash;

class Prefix
{
	/** @var Config */
	private $config;

	/** @var Hash */
	private $sourceHash;


	public function __construct(Config $config, Hash $sourceHash)
	{
		$this->config = $config;
		$this->sourceHash = $sourceHash;
	}


	public function config(): Config
	{
		$config = clone $this->config;
		$config->database = $this->database();
		return $config;
	}


	public function database(): string
	{
		return $this->prefix() . $this->sourceHash->md5();
	}


	public function prefix(): string
	{
		return "_{$this->config->database}_";
	}

}
