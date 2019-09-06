<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

interface DatabaseConnection
{

	/**
	 * @return object connection
	 */
	function create()/*: object php 7.2*/;


	function drop(): void;


	function getConnection()/*: object php 7.2*/;

}
