<?php

class OddsCompParser {


    const DATA = "_data";

    private $map = [
        1 => "_1x2_odds_hw",
        2 => "_1x2_odds_d",
        3 => "_1x2_odds_aw",
        4 => "_1x2_odds_return",
        5 => self::DATA,
        6 => "_handicap_odds_home",
        7 => "_handicap_odds_odds",
        8 => "_handicap_odds_away",
        9 => null,
        10 => "_over_under_odds_over",
        11 => "_over_under_odds_odds",
        12 => "_over_under_odds_under",
        13 => null,
    ];

    private $value;

    /**
     * OddsCompParser constructor.
     */
    public function __construct() {
    }

    public function parse($content) {

        $document = new DOMDocument();
        @$document->loadHTML($content);
        $xpath = new DOMXPath($document);

        $table = $xpath->query("//*[@class=\"oddstable\"][1]/tr[5]");
        $ibcbet = $table->item(0);

        if (false === strpos($ibcbet->nodeValue, "ibcbet")) {
            throw new Exception('ibcbet node not found');
        }

        if ($ibcbet->childNodes->length !== 14) {
            throw new Exception("unexpected node length");
        }


        for ($i = 1; $i < $ibcbet->childNodes->length; $i++) {
            $node = $ibcbet->childNodes->item($i);

            if ($this->map[$i] === null) {
                continue;
            }

            if (strpos($this->map[$i], "_") === 0) {
                $this->parseCell($i, $node);
            }

            if ($this->map[$i] === self::DATA) {
                $this->parseData($i, $node);
            }
        }
    }

    private function parseCell($i, DOMElement $node) {
        $length = $node->childNodes->length;

        if ($length === 3) {
            $val1 = $node->childNodes->item(0)->nodeValue;
            $val2 = $node->childNodes->item(2)->nodeValue;
            $this->value[$this->map[$i]] = [$val1, $val2];
        } else if ($length === 0) {
            $this->value[$this->map[$i]] = '';
        }
    }

    private function parseData($i, DOMElement $node) {

        $item = $node->childNodes->item(0);
        while (null !== $item && $item->childNodes->length > 0) {
            if ($item->tagName === 'a') {
                $this->value[$this->map[$i]] = $item->getAttribute("href");
                break;
            }
            $item = $item->childNodes->item(0);
        }
    }

    public function result() {
        return $this->value;
    }
}