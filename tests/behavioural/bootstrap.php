<?php

use Nette\Configurator;

require __DIR__ . '/../../vendor/autoload.php';

$configurator = new Configurator;

$configurator->setDebugMode(TRUE);

$configurator->enableDebugger(__DIR__ . '/../../log');
$configurator->setTempDirectory(__DIR__ . '/../../temp');
$configurator->createRobotLoader()->addDirectory(__DIR__ . '/../../app')->addDirectory(__DIR__)->register();
$configurator->addConfig(__DIR__ . '/../../app/config/app.neon');
$configurator->addConfig(__DIR__ . '/test.neon');
$configurator->addParameters(['appDir' => __DIR__ . '/../../app']);

return $configurator->createContainer();
