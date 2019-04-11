<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

interface ConnectionFactory
{

	/**
	 * @return object connection
	 */
	function create()/*: object php 7.2*/;


	function drop(/*object*/ $connection): void;

}
