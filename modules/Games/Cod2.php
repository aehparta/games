<?php

namespace Games;

class Cod2 extends \Games\Game
{
    public function getLabel()
    {
        $this->setTimeout(0.5);
        $hostname = $this->getVarValue('sv_hostname');
        $this->setTimeout();
        return $hostname ? $hostname : parent::getLabel();
    }

    public function isUp()
    {
        return $this->fetchStatus() !== false;
    }

    public function getMaps()
    {
        return array(
            'mp_breakout',
            'mp_brecourt',
            'mp_burgundy',
            'mp_carentan',
            'mp_dawnville',
            'mp_decoy',
            'mp_downtown',
            'mp_farmhouse',
            'mp_leningrad',
            'mp_matmata',
            'mp_railyard',
            'mp_toujane',
            'mp_trainstation',
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

        /* parse current map */
        preg_match('/map[\s]*:[\s]*([^\n]+)/', $lines[0], $map);
        $status['map'] = $map[1];

        /* parse players */
        foreach (array_slice($lines, 3) as $p) {
            $p                   = preg_split('/[\s]+/', trim($p));
            $status['players'][] = new Player($p[4]);
        }

        return $status;
    }

}
