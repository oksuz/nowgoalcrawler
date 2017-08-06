<?php

class Match {

    /**
     * @var String
     */
    private $homeTeam;

    /**
     * @var String
     */
    private $awayTeam;

    /**
     * @var String
     */
    private $datetime;

    /**
     * @var double
     */
    private $timestamp;

    /**
     * @var League
     */
    private $league;

    /**
     * @var int
     */
    private $oddId;

    /**
     * @return mixed
     */
    public function getHomeTeam()
    {
        return $this->homeTeam;
    }

    /**
     * @param mixed $homeTeam
     * @return Match
     */
    public function setHomeTeam($homeTeam)
    {
        $this->homeTeam = $homeTeam;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAwayTeam()
    {
        return $this->awayTeam;
    }

    /**
     * @param mixed $awayTeam
     * @return Match
     */
    public function setAwayTeam($awayTeam)
    {
        $this->awayTeam = $awayTeam;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $datetime
     * @return Match
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeague()
    {
        return $this->league;
    }

    /**
     * @param mixed $league
     * @return Match
     */
    public function setLeague(League $league)
    {
        $this->league = $league;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOddId()
    {
        return $this->oddId;
    }

    /**
     * @param mixed $oddId
     * @return Match
     */
    public function setOddId($oddId)
    {
        $this->oddId = $oddId;
        return $this;
    }

    /**
     * @return float
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param float $timestamp
     * @return Match
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }



}