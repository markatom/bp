<?php

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

if ($_GET['token'] !== 'gak52GDpsEvPyy7v') {
	exit;
}

$container = require __DIR__ . '/../app/bootstrap.php';
$em        = $container->getByType(EntityManager::class);
$loader    = new Loader;
$executor  = new ORMExecutor($em, new ORMPurger($em));

$loader->loadFromDirectory(__DIR__ . '/../tests/fixtures');
$executor->execute($loader->getFixtures());
