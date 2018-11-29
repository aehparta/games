<?php

namespace Games;

class Player
{
    private $name;
    private $score;
    private $bot;

    public function __construct($name, $score = 0, $bot = false)
    {
        $this->name  = $name;
        $this->score = intval($score);
        $this->bot   = $bot;
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

    public function isBot()
    {
        return $this->bot;
    }
}
