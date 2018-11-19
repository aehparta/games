<?php

namespace Games;

class CS extends \Games\Game
{
    const LABEL = 'Counter-Strike 1.6';

    private static $status = null;
    private static $challenge;

    public function __construct($host = null, $port = null, $password = null)
    {
        parent::__construct($host, $port, $password);
        /* fetch challenge */
        $r = $this->rcon->send(str_repeat(chr(255), 4) . 'challenge rcon', true, true);
        preg_match('/^\xff\xff\xff\xffchallenge rcon ([0-9]+)/', $r, $matches);
        if (isset($matches[1])) {
            $this->rcon->setPrefix(str_repeat(chr(255), 4) . 'rcon ' . $matches[1] . ' ' . $this->rcon->getPassword() . ' ');
        } else {
            self::$status = false;
        }
        $this->rcon->setResponseTrim('l');
    }

    public function isUp()
    {
        return $this->fetchStatus() !== false;
    }

    public function getMaps()
    {
        $this->setTimeout(1.0);
        $r = $this->send('maps *');
        $this->setTimeout();
        if (!$r) {
            return array();
        }

        $maps = array();
        foreach (explode("\n", $r) as $line) {
            $matches = array();
            preg_match('/^([a-zA-Z0-9-_]+).bsp$/', $line, $matches);
            if (isset($matches[1])) {
                $maps[$matches[1]] = $matches[1];
            }
        }

        $maps = array_keys($maps);
        sort($maps, SORT_NATURAL | SORT_FLAG_CASE);

        return $maps;
    }

    public function getMap()
    {
        $status = $this->fetchStatus();
        if (isset($status['map'])) {
            return $status['map'];
        }
        return null;
    }

    public function setMap(string $map)
    {
        return $this->send('changelevel ' . $map);
    }

    public function getPlayers()
    {
        $status = $this->fetchStatus();
        if (isset($status['players'])) {
            return $status['players'];
        }
        return array();
    }

    private function fetchStatus()
    {
        if (self::$status !== null) {
            return self::$status;
        }

        $r = $this->send('status');
        if (!$r) {
            self::$status = false;
            return false;
        }

        self::$status = array('map' => null, 'players' => array());
        $lines        = explode("\n", $r);

        /* parse current map */
        preg_match('/map[\s]*:[\s]*([^\s]+)/', $lines[3], $map);
        self::$status['map'] = $map[1];

        /* parse players */
        foreach (array_slice($lines, 7) as $p) {
            $p = preg_split('/[\s]+/', trim($p));
            if (count($p) < 10) {
                continue;
            }
            self::$status['players'][] = new Player(trim($p[2], '"'), $p[5]);
        }

        return self::$status;
    }
}
