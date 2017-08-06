<?php
define('TMP', __DIR__ . '/tmp');

require_once 'conf.php';
require_once 'lib/League.php';
require_once 'lib/Match.php';
require_once 'lib/ServiceContainer.php';
require_once 'lib/MatchTable.php';
require_once 'lib/Curl.php';
require_once 'lib/TaskQueue.php';
require_once 'lib/JsParser.php';


$container = new ServiceContainer();
$matchTable = $container->getCurl()->getMatchTable();
$container->getTaskQueue()->createCrawlerTasks($matchTable);

$container->getCurl()->startCrawling();

