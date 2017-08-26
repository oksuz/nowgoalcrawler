<?php

class Crawler {

    /**
     * @var ServiceContainer
     */
    private $container;

    public function __construct(ServiceContainer $container) {
        $this->container = $container;
    }

    public function startCrawling() {
        for (;;) {
            $ch = null;
            $match = null;
            try {
                $ch = $this->initCurl();
                $match = $this->container->getDb()->getMatchFromQueue();
                $url = sprintf('http://data.nowgoal.net/oddscomp/%s.html', $match->getOddId());

                echo sprintf("go to url %s for odss" . PHP_EOL, $url);

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_REFERER, "https//www.nowgoal.net");
                $response = curl_exec($ch);

                if (curl_errno($ch) !== 0) {
                    throw new CrawlerException(curl_error($ch));
                }

                $oddsCompParser = $this->container->getOddsCompParser();
                $oddsCompParser->parse($response);
                $this->container->getDb()->saveOddsComp($match, $oddsCompParser->result());

                $_3in1oddPath = trim($oddsCompParser->result()[OddsCompParser::DATA]);
                $_3in1oddsUrl = sprintf("http://data.nowgoal.net%s", $_3in1oddPath);


                if (!empty($_3in1oddPath)) {
                    echo sprintf("go to url %s for 3in1odds " . PHP_EOL, $_3in1oddsUrl);
                    curl_setopt($ch, CURLOPT_URL, $_3in1oddsUrl);
                    $response = curl_exec($ch);
                    if (curl_errno($ch) !== 0) {
                        throw new CrawlerException(curl_error($ch));
                    }
                    $_3in1oddsParser = $this->container->get3in1oddsParser();
                    $_3in1oddsParser->parse($response);

                    $this->container->getDb()->save1x2Odds($_3in1oddsParser->get1x2Odds(), $match);
                    $this->container->getDb()->saveHandicapOdds($_3in1oddsParser->getHandicapOdds(), $match);
                    $this->container->getDb()->saveOverUnderOdds($_3in1oddsParser->getOverUnderOdds(), $match);
                }
            } catch (NoSuchMatchInQueueException $e) {
                echo "The end :) " . PHP_EOL;
                exit(0);
            } catch (CrawlerException $e) {
                echo "crawler exception: " . $e->getMessage() . PHP_EOL;
            } catch (Exception $e) {
                throw $e;
            } finally {
                if ($match instanceof Match) {
                    $this->container->getDb()->unlockMatch($match);
                }

                if (is_resource($ch)) {
                    curl_close($ch);
                }
            }
        }
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
