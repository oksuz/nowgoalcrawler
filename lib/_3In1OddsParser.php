<?php

class _3In1OddsParser
{

    /**
     * @var DOMXPath
     */
    private $xpath;
    private $_1x2Odds = [];
    private $handicapOdds = [];
    private $overUnderOdds = [];

    private $tableMap = [
        0 => "time",
        1 => "score",
        2 => "home",
        3 => "draw",
        4 => "away",
        5 => "update_time",
        6 => "status"
    ];

    public function parse($source) {
        $document = new DOMDocument();
        @$document->loadHTML($source);
        $this->xpath = new DOMXPath($document);

        $this->parse1x2Odds();
        $this->parseHandicapOdds();
        $this->parseOverUnderOdds();
    }

    private function parse1x2Odds() {
        $this->parseTable($this->xpath->query("//*[@id=\"div_h\"]/table")->item(0), $this->_1x2Odds);
    }

    private function parseHandicapOdds() {
        $this->parseTable($this->xpath->query("//*[@id=\"div_l\"]/table")->item(0), $this->handicapOdds);
    }

    private function parseOverUnderOdds() {
        $this->parseTable($this->xpath->query("//*[@id=\"div_d\"]/table")->item(0), $this->overUnderOdds);
    }

    private function parseTable(DOMElement $table, &$collection) {
        for ($i = 2, $l = $table->childNodes->length; $i < $l; $i++) {
            $this->parseRow($table->childNodes->item($i)->childNodes, $collection);
        }
    }

    private function parseRow(DOMNodeList $nodeList, &$collection) {
        $result = [];
        for ($i = 0, $td = 0; $i < $nodeList->length; $i++) {
            $node = $nodeList->item($i);
            if (!empty($node->tagName) && $node->tagName === 'td') {
                if (5 === $td) {
                    $timestamp = $this->parseTime(trim($node->nodeValue));
                    $result['update_timestamp'] = $timestamp;
                    $result[$this->tableMap[$td]] = date("Y-m-d H:i:s", $timestamp);
                } else {
                    $result[$this->tableMap[$td]] = trim($node->nodeValue);
                }
                $td++;
            }
        }
        array_push($collection, $result);
    }

    /**
     * @return array
     */
    public function get1x2Odds() {
        return $this->_1x2Odds;
    }

    /**
     * @return array
     */
    public function getHandicapOdds() {
        return $this->handicapOdds;
    }

    /**
     * @return array
     */
    public function getOverUnderOdds() {
        return $this->overUnderOdds;
    }

    private function parseTime($nodeValue) {
        $result = [];
        preg_match("/\((.*?)\)/", $nodeValue, $result);
        $result = explode(",", $result[1]);
        $dateString = sprintf("%d-%02d-%02d %02d:%02d:%02d", $result[0], explode("-", $result[1])[0], $result[2], $result[3], $result[4], $result[5]);
        $date = new DateTime($dateString, new DateTimeZone("UTC"));
        return $date->getTimestamp();
    }


}