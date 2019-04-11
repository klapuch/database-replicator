<?php declare(strict_types=1);

namespace PmgDev\DatabaseReplicator;

use Tester;
use Tracy;

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');
Tester\Environment::setup();

Tracy\Debugger::enable(false, Utils::TEMP_DIR);

Utils::saveForProvider();
