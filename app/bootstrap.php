<?php

use Nette\Configurator;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Configurator;

$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()->addDirectory(__DIR__)->register();
$configurator->addConfig(__DIR__ . '/config/app.neon');
$configurator->addConfig(__DIR__ . '/config/console.neon');
$configurator->addConfig(__DIR__ . '/config/local.neon');

return $configurator->createContainer();


