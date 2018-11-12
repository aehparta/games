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
        foreach (explode("\n", $r) as $map) {
            if (substr($map, -4) == '.bsp') {
                $map = substr($map, 0, -4);
            }
            $maps[$map] = $map;
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
        return $this->send('map ' . $map);
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
        preg_match('/map[\s]*:[\s]*([^\s]+)/', $lines[2], $map);
        self::$status['map'] = $map[1];

        /* parse players */
        // foreach (array_slice($lines, 6) as $p) {
        //     $p                   = preg_split('/[\s]+/', trim($p));
        //     $status['players'][] = new Player($p[5]);
        // }

        return self::$status;
    }
}
