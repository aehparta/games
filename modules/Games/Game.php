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

    public function getHost()
    {
        return $this->rcon->getHost();
    }

    public function getPort()
    {
        return $this->rcon->getPort();
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

    public function getPlayerCount()
    {
        return count($this->getPlayers());
    }

    public function getPlayer($player_id)
    {
        $players = $this->getPlayers();
        foreach ($$players as $player) {
            if ($player->getId() == $player_id) {
                return $player;
            }
        }
        return null;
    }

    public function kickPlayer($player_name)
    {

    }

    public function restart()
    {
        return false;
    }

    public function send($command)
    {
        return $this->rcon->send($command);
    }

    public function getVar($var_id)
    {
        $var = \kernel::getConfigValue('games', $this->id, 'vars', $var_id);
        if (!$var) {
            return null;
        }
        $var['value'] = $this->getVarValue($var_id);
        return $var;
    }

    public function getVarValue($var_id)
    {
        $v = $this->cacheGet($this->id . ':var:' . $var_id);
        if ($v !== null) {
            return $v;
        }
        $r = $this->send($var_id);
        if (!$r) {
            return null;
        }
        preg_match('/"([a-zA-Z0-9-_]+)"[\s]*is[:\s]*"([a-zA-Z0-9-_., ]+)"/', $r, $matches);
        if (count($matches) != 3) {
            return null;
        }
        if ($matches[1] != $var_id) {
            return null;
        }
        $v = trim($matches[2], ' "');
        $this->cacheSet($this->id . ':var:' . $var_id, $v, 30);
        return $v;
    }

    public function setVarValue($var_id, $value)
    {
        $this->send($var_id . ' ' . $value);
        if (\kernel::getConfigValue('games', $this->id, 'vars', $var_id, 'restart') === true) {
            $this->restart();
        }
        $this->cacheSet($this->id . ':var:' . $var_id, null, 0);

    }

    public function getVars()
    {
        $vars = \kernel::getConfigValue('games', $this->id, 'vars');
        if (!$vars) {
            return array();
        }
        foreach ($vars as $key => &$var) {
            $var['id']    = $key;
            $var['value'] = $this->getVarValue($key);
            if (!isset($var['label'])) {
                $var['label'] = $key;
            }
        }
        return array_values($vars);
    }

    public function setTimeout($timeout = null)
    {
        $this->rcon->setTimeout($timeout);
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

    public static function cmdGamesList()
    {
        $games = self::getGames();
        echo "Games:\n";
        foreach ($games as $game) {
            echo ' - ' . str_pad($game->getId(), 8) . ': ' . $game->getLabel() . ' (' . $game->getHost() . ':' . $game->getPort() . ")\n";
        }
        return true;
    }

    public static function cmd($cmd, $args, $options)
    {
        $game = self::getGame($args['game']);
        $game->setTimeout($options['timeout']);
        $r = $game->send($args['command']);
        if ($r) {
            echo $game->getId() . ':' . $game->getLabel() . ':response:' . "\n";
            echo $r . "\n";
        } else {
            echo $game->getId() . ':' . $game->getLabel() . ':response: No response' . "\n";
        }
        return true;
    }
}
