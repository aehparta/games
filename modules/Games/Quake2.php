<?php

namespace Games;

class Quake2 extends \Games\Game
{
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
        $r = $this->send('dir maps/*.bsp');
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
        return $this->send('gamemap ' . $map);
    }

    public function getPlayers()
    {
        $status = $this->fetchStatus();
        if (isset($status['players'])) {
            return $status['players'];
        }
        return array();
    }

    public function restart()
    {
        return $this->setMap($this->getMap());
    }

    private function fetchStatus()
    {
        static $status = null;
        if ($status !== null) {
            return $status;
        }
        $r = $this->send('status');
        if (!$r) {
            $status = false;
            return false;
        }

        $status = array('map' => null, 'players' => array());
        $lines  = explode("\n", $r);
        if (count($lines) < 1) {
            $status = false;
            return false;
        }

        /* parse current map */
        preg_match('/map[\s]*:[\s]*([^\n]+)/', $lines[0], $map);
        if (count($map) < 2) {
            $status = false;
            return false;
        }
        $status['map'] = $map[1];

        /* parse players */
        foreach (array_slice($lines, 3) as $p) {
            $p                   = preg_split('/[\s]+/', trim($p));
            $status['players'][] = new Player($p[3], $p[1]);
        }

        return $status;
    }
}
