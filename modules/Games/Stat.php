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

    public static function cmdStats($cmd, $args, $options)
    {
        $repo    = em()->getRepository(get_class());
        $search  = ['game' => $args['game']];
        $players = [];
        if (isset($args['player'])) {
            $players = [em()->getRepository('Games\Player')->findOneBy(['name' => $args['player']])];
        } else {
            $players = em()->getRepository('Games\Player')->findAll();
        }

        echo str_pad('Name', 24) . str_pad('Kills', 8) . str_pad('Planted', 8) . str_pad('Defused', 8) . str_pad('Suicides', 10) . str_pad('Killed most', 12) . "\n";

        $killed_most = [];

        foreach ($players as $player) {
            if ($player->isBot()) {
                continue;
            }

            $search['player_src'] = $player;
            $stats                = $repo->findBy($search);
            $killed_most          = [];

            $info = ['kill' => 0, 'bomb defused' => 0, 'bomb planted' => 0, 'suicide' => 0];
            foreach ($stats as $stat) {
                $info[$stat->getAction()]++;
                if ($stat->getAction() == 'kill' && !$stat->getPlayerDst()->isBot()) {
                    if (!isset($killed_most[$stat->getPlayerDst()->getName()])) {
                        $killed_most[$stat->getPlayerDst()->getName()] = 0;
                    }
                    $killed_most[$stat->getPlayerDst()->getName()]++;
                }
            }

            $killed_most = array_flip($killed_most);
            ksort($killed_most);
            $killed_most_name  = end($killed_most);
            $killed_most       = array_flip($killed_most);
            $killed_most_times = end($killed_most);

            echo str_pad($player->getName(), 24) . str_pad($info['kill'], 8) . str_pad($info['bomb planted'], 8) . str_pad($info['bomb defused'], 8) . str_pad($info['suicide'], 10) . str_pad($killed_most_name . ' (' . $killed_most_times . ')', 12) . "\n";
            // var_dump($killed_most);
        }

        return true;
    }
}
