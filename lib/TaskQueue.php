<?php

class TaskQueue {

    /**
     * @var ServiceContainer
     */
    private $service;


    public function __construct(ServiceContainer $service) {
        $this->service = $service;
    }

    public function createCrawlerTasks(MatchTable $matchTable) {

    }


}