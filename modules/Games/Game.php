<?php

namespace Games;

class Game extends \Core\Module
{
    protected $id;
    protected $rcon;

    public function __construct($id)
    {
        parent::__construct();
        $this->id   = $id;
        $host       = \kernel::getConfigValue('games', $id, 'host');
        $port       = \kernel::getConfigValue('games', $id, 'port');
        $password   = \kernel::getConfigValue('games', $id, 'password');
        $this->rcon = new \Games\Rcon($host, $port, $password);
    }

    public function isUp()
    {
        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
    {
        $label = \kernel::getConfigValue('games', $this->id, 'label');
        if ($label) {
            return $label;
        }
        return $this->getId();
    }

    public function getMaps()
    {
        return array();
    }

    public function getMap()
    {
        return null;
    }

    public function setMap(string $map)
    {
        return false;
    }

    public function getPlayers()
    {
        return array();
    }

    public function restart()
    {
        return false;
    }

    public static function getGames()
    {
        $games_cfg = \kernel::getConfigValue('games');
        if (!is_array($games_cfg)) {
            return array();
        }
        $games = array();
        foreach ($games_cfg as $id => $cfg) {
            if (class_exists($cfg['class'])) {
                $games[] = new $cfg['class']($id);
            }
        }
        return $games;
    }

    public static function getGame($id)
    {
        foreach (self::getGames() as $game) {
            if ($game->getId() === $id) {
                return $game;
            }
        }
        return null;
    }

    public function send($command)
    {
        return $this->rcon->send($command);
    }

    public function getVar($var)
    {
        $r = $this->send($var);
        if (!$r) {
            return null;
        }
        preg_match('/"([a-zA-Z0-9-_]+)"[\s]*is[:\s]*"([a-zA-Z0-9-_.,]+)"/', $r, $matches);
        if (count($matches) != 3) {
            return null;
        }
        if ($matches[1] != $var) {
            return null;
        }
        $v = trim($matches[2], ' "');
        return $v;
    }

    public function setVar($var, $value)
    {
        $this->send($var . ' ' . $value);
        if (\kernel::getConfigValue('games', $this->id, 'vars', $var, 'restart') === true) {
            $this->restart();
        }
    }

    public function setTimeout($timeout = null)
    {
        $this->rcon->setTimeout($timeout);
    }

    public static function cmdGamesList()
    {
        $games = self::getGames();
        echo "Games:\n";
        foreach ($games as $game) {
            echo ' - ' . str_pad($game->getId(), 8) . ': ' . $game->getLabel() . "\n";
        }
        return true;
    }
}
