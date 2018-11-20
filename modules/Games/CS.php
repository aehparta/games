<?php

namespace Games;

class CS extends \Games\Game
{
    private static $status = null;
    private static $challenge;

    public function __construct($id)
    {
        parent::__construct($id);
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

    public function getLabel()
    {
        $hostname = $this->getVarValue('hostname');
        return $hostname ? $hostname : parent::getLabel();
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
            $matches = array();
            preg_match('/[#0-9\s]+"([^"]+)"[\s]+[0-9]+[\s]+[^\s]+[\s]+([0-9]+)/', trim($p), $matches);
            if (count($matches) != 3) {
                continue;
            }
            self::$status['players'][] = new Player($matches[1], $matches[2]);
        }

        return self::$status;
    }
}
