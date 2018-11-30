<?php

namespace Games;

class Player
{
    private $name;
    private $score;
    private $bot;
    private $data;

    public function __construct($name, $score = 0, $bot = false, $data = array())
    {
        $this->name  = $name;
        $this->score = intval($score);
        $this->bot   = $bot;
        $this->data  = array();
        if (is_array($data)) {
            $this->data = $data;
        }
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

    public function getValue($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function setValue($key, $value)
    {
        $this->data[$key] = $value;
    }
}
