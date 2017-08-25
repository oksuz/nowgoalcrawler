<?php
define('TMP', __DIR__ . '/tmp');

require_once 'conf.php';
require_once "phar://lib.phar";

$container = new ServiceContainer();

$matchTable = $container->getCrawler()->getMatchTable();
$container->getTaskQueue()->createCrawlerTasks($matchTable);
$container->getCrawler()->startCrawling();

