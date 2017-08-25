<?php

class ServiceContainer {

    /**
     * @return JsParser
     */
    public function getJsParser() {
        return new JsParser();
    }

    /**
     * @return Crawler
     */
    public function getCrawler() {
        return new Crawler($this);
    }

    /**
     * @return TaskQueue
     */
    public function getTaskQueue() {
        return new TaskQueue($this);
    }

    /**
     * @return DbService
     */
    public function getDb() {
        return new DbService(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    /**
     * @return OddsCompParser
     */
    public function getOddsCompParser() {
        return new OddsCompParser();
    }

    public function get3in1oddsParser() {
        return new _3In1OddsParser();
    }

}