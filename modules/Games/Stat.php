<?php

namespace Games;

class Stat
{
    private $id;
    private $game;
    private $time;
    private $action;
    private $player_src;
    private $player_dst;
    private $tool;

    public function getId()
    {
        return $this->id;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function setGame(string $game)
    {
        $this->game = $game;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime(\DateTime $time)
    {
        $this->time = $time;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getPlayerSrc()
    {
        return $this->player_src;
    }

    public function setPlayerSrc($p)
    {
        $this->player_src = $p;
    }

    public function getPlayerDst()
    {
        return $this->player_dst;
    }

    public function setPlayerDst($p)
    {
        $this->player_dst = $p;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setTool($tool)
    {
        $this->tool = $tool;
    }
}
