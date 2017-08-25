<?php
define('TMP', __DIR__ . '/tmp');

require_once 'conf.php';
require_once 'lib/NoSuchMatchInQueueException.php';
require_once 'lib/CrawlerException.php';
require_once 'lib/ServiceContainer.php';
require_once 'lib/League.php';
require_once 'lib/Match.php';
require_once 'lib/MatchTable.php';
require_once 'lib/Crawler.php';
require_once 'lib/TaskQueue.php';
require_once 'lib/JsParser.php';
require_once 'lib/OddsCompParser.php';
require_once 'lib/_3In1OddsParser.php';
require_once 'lib/DbService.php';


$container = new ServiceContainer();

$matchTable = $container->getCrawler()->getMatchTable();
$container->getTaskQueue()->createCrawlerTasks($matchTable);
$container->getCrawler()->startCrawling();

