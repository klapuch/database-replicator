<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Nette\Utils\Random;
use Tester\Assert;

class ConnectionFactoryMock implements ConnectionFactory
{

	public function create()/*: object*/
	{
		$std = new \stdClass();
		$std->name = Random::generate(10);
		return $std;
	}


	public function drop(/*object*/ $connection): void
	{
		Assert::same(\stdClass::class, get_class($connection));
	}

}
