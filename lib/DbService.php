<?php

class DbService {


    /**
     * @var PDO
     */
    private $link;

    const FIVE_MIN_IN_SEC = 300;

    public function __construct($host, $user, $password, $db, array $options = array()) {
        $dsn = sprintf("mysql:host=%s;dbname=%s", $host, $db);
        $defaults = $this->getDefaultOptions();
        $options = $options + $defaults;
        $this->link = new PDO($dsn, $user, $password, $options);
    }

    private function getDefaultOptions() {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
    }

    public function getQId() {
        $stmt = $this->link->prepare("SELECT id, created_at FROM ". DB_PREFIX ."q_log ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result === false || (time() - strtotime($result["created_at"])) > self::FIVE_MIN_IN_SEC) {
            $this->link->query("INSERT INTO ". DB_PREFIX ."q_log(created_at) VALUES(NOW())");
            return intval($this->link->lastInsertId());
        }


        return intval($result["id"]);
    }


    public function createMatch($qId, Match $m) {
        $query = "INSERT INTO ". DB_PREFIX ."matchs(q_id, odd_id, home_team, away_team, match_time, time_stamp, league_short_name, league, created_at) 
          VALUES (:q_id, :odd_id, :home_team, :away_team, :match_time, :time_stamp, :league_short_name, :league, :created_at)";

        $stmt = $this->link->prepare($query);
        $stmt->bindValue("q_id", $qId);
        $stmt->bindValue("odd_id", $m->getOddId());
        $stmt->bindValue("home_team", $m->getHomeTeam());
        $stmt->bindValue("away_team", $m->getAwayTeam());
        $stmt->bindValue("match_time", $m->getDatetime());
        $stmt->bindValue("time_stamp", $m->getTimestamp());
        $stmt->bindValue("league_short_name", $m->getLeague()->getShortName());
        $stmt->bindValue("league", $m->getLeague()->getName());
        $stmt->bindValue("created_at", date('Y-m-d H:i:s'));

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo sprintf('An exception occurred while saving match %s, e: %s \r\n', $m->getOddId(), $e->getMessage());
            }
        }

    }

    /**
     * @return Match
     * @throws Exception
     */
    public function getMatchFromQueue() {
        $this->link->query("SET @update_id := 0;");
        $this->link->query("SET @id = (SELECT id FROM ". DB_PREFIX ."matchs WHERE is_locked = 0 AND is_active = 1 AND match_time > DATE_ADD(NOW(), INTERVAL -2 HOUR) AND (last_crawled_at IS NULL OR last_crawled_at < DATE_ADD(NOW(), INTERVAL -10 MINUTE)) ORDER BY match_time ASC LIMIT 1)");
        $this->link->query("UPDATE ". DB_PREFIX ."matchs SET is_locked = 1, id = (SELECT @update_id := @id) WHERE id = @id");
        $id = $this->link->query("SELECT @update_id")->fetch();

        if (empty($id["@update_id"])) {
            throw new NoSuchMatchInQueueException("There are no match found for crawl");
        }

        echo sprintf("lock and get match id: %s " . PHP_EOL, $id["@update_id"]);

        $stmt = $this->link->prepare("SELECT * FROM ". DB_PREFIX ."matchs WHERE id = :id");
        $stmt->execute(["id" => $id["@update_id"]]);
        $match = $stmt->fetch();


        $m = new Match();
        $league = new League();
        $league->setShortName($match["league_short_name"]);
        $league->setName($league);

        $m->setTimestamp($match["time_stamp"])
            ->setDatetime($match["match_time"])
            ->setAwayTeam($match["away_team"])
            ->setHomeTeam($match["home_team"])
            ->setOddId($match["odd_id"])
            ->setLeague($league)
            ->setId($match["id"]);

        return $m;
    }

    public function unlockMatch(Match $m) {
        $stmt = $this->link->prepare("UPDATE ". DB_PREFIX ."matchs SET is_locked = 0, last_crawled_at = :last_crawled_at WHERE odd_id = :odd_id");
        $stmt->execute([
            "odd_id" => $m->getOddId(),
            "last_crawled_at" => date('Y-m-d H:i:s')
        ]);
        echo sprintf("unlock match by oddId: %s " . PHP_EOL, $m->getOddId());
    }

    public function saveOddsComp(Match $match, $result)
    {
        $stmt = $this->link->prepare("INSERT INTO 
            ". DB_PREFIX ."odds_comp(match_id, _1x2_odds_hw, _1x2_odds_d, _1x2_odds_aw, _data, _handicap_odds_home, _handicap_odds_odds, _handicap_odds_away, _over_under_odds_over, _over_under_odds_odds, _over_under_odds_under)
            VALUES(:match_id, :_1x2_odds_hw, :_1x2_odds_d, :_1x2_odds_aw, :_data, :_handicap_odds_home, :_handicap_odds_odds, :_handicap_odds_away, :_over_under_odds_over, :_over_under_odds_odds, :_over_under_odds_under) 
            ON DUPLICATE KEY UPDATE
            _1x2_odds_hw = :_1x2_odds_hw, 
            _1x2_odds_d = :_1x2_odds_d, 
            _1x2_odds_aw = :_1x2_odds_aw,
            _data = :_data, 
            _handicap_odds_home = :_handicap_odds_home,
            _handicap_odds_odds = :_handicap_odds_odds,
            _handicap_odds_away = :_handicap_odds_away,
            _over_under_odds_over = :_over_under_odds_over, 
            _over_under_odds_odds = :_over_under_odds_odds, 
            _over_under_odds_under = :_over_under_odds_under");

        $stmt->bindValue("match_id", $match->getId());
        foreach ($result as $field => $value) {
            if (is_array($value)) {
                $stmt->bindValue($field, json_encode($value));
            } else {
                $stmt->bindValue($field, $value);
            }
        }

        $stmt->execute();
    }

    public function save1x2Odds(array $_1x2Odds, Match $match) {
        $this->saveOdds("_1x2_odds", $_1x2Odds, $match);
    }

    public function saveHandicapOdds(array $handicapOdds, Match $match) {
        $this->saveOdds("handicap_odds", $handicapOdds, $match);
    }

    public function saveOverUnderOdds(array $overUnderOdds, $match) {
        $this->saveOdds("over_under_odds", $overUnderOdds, $match);
    }

    private function saveOdds($table, array $odds, Match $m) {
        $statements = [];
        $stmt = $this->link->prepare(sprintf("DELETE FROM %s WHERE match_id = :match_id", DB_PREFIX . $table));
        $stmt->bindValue("match_id", $m->getId());
        array_push($statements, $stmt);

        foreach ($odds as $odd) {
            $stmt = $this->link->prepare(sprintf("INSERT INTO %s(match_id, time, score, home, draw, away, update_timestamp, update_time, status) VALUES(:match_id, :time, :score, :home, :draw, :away, :update_timestamp, :update_time, :status)", DB_PREFIX . $table));
            foreach ($odd as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue("match_id", $m->getId());
            array_push($statements, $stmt);
        }

        foreach ($statements as $s) {
            $s->execute();
        }
    }
}