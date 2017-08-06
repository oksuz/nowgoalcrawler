<?php

class JsParser {

    public function parseTodayMatchs($javascript) {
        return $this->parseJs($javascript, '@A\[\d+\]=\[(.*)\]@');
    }

    public function parseLeagues($javascript) {
        return $this->parseJs($javascript, '@B\[\d+\]=\[(.*)\]@');
    }

    public function parseCountries($javascript) {
        return $this->parseJs($javascript, '@C\[\d+\]=\[(.*)\]@');
    }

    private function parseJs($js, $regex) {
        $matches = [];
        $data = [];
        preg_match_all($regex, $js, $matches);
        if (!empty($matches[1])) {
            for ($i = 0, $cnt = count($matches[1]); $i < $cnt; $i++) {
                $curr = $this->convertArray($matches[1][$i]);
                $decoded = json_decode($curr, true);
                $data[($i+1)] = count($decoded) === 1 ? $decoded[0] : $decoded;
            }
        }
        return $data;
    }

    private function convertArray($js) {
        $find = ["'", ",,"];
        $replace = ["\"", ",null,"];
        $converted = str_replace($find, $replace, "[" . $js . "]");

        foreach ($find as $f) {
            if (false !== strpos($converted, $f)) {
                return $this->convertArray($converted);
            }
        }

        return $converted;
    }

}