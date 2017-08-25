<?php

class TaskQueue {

    /**
     * @var ServiceContainer
     */
    private $container;


    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function createCrawlerTasks(MatchTable $matchTable) {
        $db = $this->container->getDb();
        $id = $db->getQId();

        /**
         * @var Match $match
         */
        foreach ($matchTable->getMatchs() as $match) {
            $db->createMatch($id, $match);
        }
    }

}