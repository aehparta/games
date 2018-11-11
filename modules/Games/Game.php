<?php

namespace Games;

class Game extends \Core\Module
{
    const LABEL = null;

    private $rcon;

    public function __construct($host = null, $port = null, $password = null)
    {
        parent::__construct();
        $host       = $host ? $host : $this->getModuleValue('host');
        $port       = $port ? $port : $this->getModuleValue('port');
        $password   = $password ? $password : $this->getModuleValue('password');
        $this->rcon = new \Games\Rcon($host, $port, $password);
    }

    public function isUp()
    {
        return false;
    }

    public function getName()
    {
        $parts = explode('\\', get_class($this));
        return $parts[1];
    }

    public function getLabel()
    {
        $label = $this->getModuleValue('label');
        if ($label) {
            return $label;
        }
        if (static::LABEL !== null) {
            return static::LABEL;
        }
        return $this->getName();
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

    public static function getGames()
    {
        $game_names = \kernel::getConfigValue('modules', get_class(), 'games');
        if (!is_array($game_names)) {
            $game_names = array();
        }
        $games = array();
        foreach ($game_names as $game_name) {
            $class = '\\Games\\' . $game_name;
            if (class_exists($class)) {
                $games[] = new $class();
            }
        }
        return $games;
    }

    public static function getGame($name)
    {
        foreach (self::getGames() as $game) {
            if ($game->getName() === $name) {
                return $game;
            }
        }
        return null;
    }

    public function send($command, $read = true)
    {
        return $this->rcon->send($command, $read);
    }

    public static function cmdGamesList()
    {
        $games = self::getGames();
        echo "Games:\n";
        foreach ($games as $game) {
            echo ' - ' . $game->getName() . ': ' . $game->getLabel() . "\n";
        }
        return true;
    }

    public static function cmd($cmd, $args, $options)
    {
        $class = '\\Games\\' . $args['game'];
        if (!class_exists($class)) {
            \kernel::log(LOG_ERR, 'class for game ' . $args['game'] . ' does not exists');
            return false;
        }
        $game = new $class($options['host'], $options['port'], $options['password']);
        $r    = $game->send($args['command']);
        if ($r) {
            echo $args['game'] . ':' . $args['command'] . ":\n" . $r . "\n";
        } else {
            echo $args['game'] . ':' . $args['command'] . ':noresponse' . "\n";
        }
        return true;
    }
}
