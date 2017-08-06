<?php

class ServiceContainer {

    /**
     * @return JsParser
     */
    public function getJsParser() {
        return new JsParser();
    }

    /**
     * @return Curl
     */
    public function getCurl() {
        return new Curl($this);
    }

    public function getTaskQueue() {
        return new TaskQueue($this);
    }
}