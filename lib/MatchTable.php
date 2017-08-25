<?php

class MatchTable {

    private $matchs;

    public function __construct($matchs) {
        $this->matchs = $matchs;
    }

    public static function create(array $matchs, array $league, array $countries) {
        $result = [];
        $timezone = new DateTimeZone('UTC');
        foreach ($matchs as $match) {
            $dateSplit = explode(",", $match[6]);
            $dateString = sprintf("%d-%02d-%02d %02d:%02d:%02d", $dateSplit[0], intval($dateSplit[1])+1, $dateSplit[2], $dateSplit[3], $dateSplit[4], $dateSplit[5]);


            $date = new DateTime($dateString, $timezone);
            $l = new League();
            $l->setName($league[$match[1]][2]);
            $l->setShortName($league[$match[1]][1]);

            $m = new Match();
            $m->setOddId($match[0])
                ->setHomeTeam($match[4])
                ->setAwayTeam($match[5])
                ->setDatetime(date('Y-m-d H:i:s', $date->getTimestamp()))
                ->setTimestamp($date->getTimestamp())
                ->setLeague($l);

            $result[] = $m;
        }

        return new MatchTable($result);
    }

    /**
     * @return array of Match
     */
    public function getMatchs()
    {
        return $this->matchs;
    }


}