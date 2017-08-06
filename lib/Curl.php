<?php

class Curl {

    /**
     * @var ServiceContainer
     */
    private $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function startCrawling() {

    }

    public function getMatchTable() {
        $ch = $this->initCurl();
        curl_setopt($ch, CURLOPT_URL, "http://www.nowgoal.net/data/bf_en2.js?" . time() * 1000);
        $js = curl_exec($ch);
        curl_close($ch);

        $parser = $this->container->getJsParser();

        $todayMatchs = $parser->parseTodayMatchs($js);
        $countries = $parser->parseCountries($js);
        $leagues = $parser->parseLeagues($js);

        return MatchTable::create($todayMatchs, $leagues, $countries);
    }


    private function initCurl() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, TMP . "/cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIEJAR, TMP . "/cookie.txt");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36");
        curl_setopt($ch, CURLOPT_REFERER, "https://www.gooogle.com");
        return $ch;
    }

}
