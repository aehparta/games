<?php

namespace Games;

class Player
{
    private $name;
    private $score;

    public function __construct($name, $score = 0)
    {
        $this->name  = $name;
        $this->score = intval($score);
    }

    public function getId()
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getScore()
    {
        return $this->score;
    }
}
