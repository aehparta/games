<?php

namespace Games;

class Cod4 extends \Games\Game
{
    public function getLabel()
    {
        $status = $this->fetchStatus();
        return isset($status['hostname']) ? $status['hostname'] : parent::getLabel();
    }

    public function isUp()
    {
        return $this->fetchStatus() !== false;
    }

    public function getMaps()
    {
        return array(
            'mp_backlot',
            'mp_bloc',
            'mp_bog',
            'mp_broadcast',
            'mp_carentan',
            'mp_cargoship',
            'mp_citystreets',
            'mp_convoy',
            'mp_countdown',
            'mp_crash',
            'mp_crossfire',
            'mp_farm',
            'mp_overgrown',
            'mp_pipeline',
            'mp_shipment',
            'mp_showdown',
            'mp_strike',
            'mp_showdown',
            'mp_killhouse',
            'mp_crash_snow',
        );
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
        return $this->send('map_restart');
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

        $status = array('hostname' => null, 'map' => null, 'players' => array());
        $lines  = explode("\n", $r);
        if (count($lines) < 6) {
            return false;
        }

        /* parse hostname */
        preg_match('/[\s]*hostname[\s]*:[\s]*([^\n]+)/', $lines[0], $hostname);
        $status['hostname'] = trim($hostname[1]);

        /* parse current map */
        preg_match('/map[\s]*:[\s]*([^\n]+)/', $lines[5], $map);
        $status['map'] = $map[1];

        /* parse players */
        foreach (array_slice($lines, 9) as $p) {
            $matches = array();
            preg_match('/[0-9]+[\s]+([0-9]+)[\s]+[0-9]+[\s]+[0-9]+[\s]+[0-9]+[\s]+(.{32})/', trim($p), $matches);
            if (count($matches) != 3) {
                continue;
            }
            $status['players'][] = new Player(trim($matches[2]), intval($matches[1]));
        }

        return $status;
    }

}
