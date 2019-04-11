<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

class Config
{
	/** @var string */
	public $database;

	/** @var string */
	public $username;

	/** @var string */
	public $password;

	/** @var string */
	public $host;

	/** @var int */
	public $port;


	public function __construct(string $database, string $username, string $password, string $host, int $port)
	{
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
	}

}
