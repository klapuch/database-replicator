<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\Utils\Random;
use Tester\Assert;

class ConnectionMock extends Database
{

	protected function createConnection(Config $config)
	{
		$std = new \stdClass();
		$std->name = Random::generate(10);
		return $std;
	}


	protected function disconnectConnection($connection): void
	{
		Assert::same(\stdClass::class, get_class($connection));
	}

}
