<?php

class League {

    /**
     * @var String
     */
    private $name;

    /**
     * @var String
     */
    private $shortName;

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return String
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param String $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }
}