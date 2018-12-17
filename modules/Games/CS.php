<?php

namespace Games;

class CS extends \Games\Game
{
    private $status = null;
    private $challenge;

    public function __construct($id)
    {
        parent::__construct($id);
        /* fetch challenge */
        $r = $this->rcon->send(str_repeat(chr(255), 4) . 'challenge rcon', true, true);
        preg_match('/^\xff\xff\xff\xffchallenge rcon ([0-9]+)/', $r, $matches);
        if (isset($matches[1])) {
            $this->rcon->setPrefix(str_repeat(chr(255), 4) . 'rcon ' . $matches[1] . ' ' . $this->rcon->getPassword() . ' ');
        } else {
            $this->status = false;
        }
        $this->rcon->setResponseTrim('l');
    }

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
        $maps = cache()->get($this->id . '.maps');
        if ($maps) {
            return $maps;
        }

        $this->setTimeout(2.0);
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
        cache()->set($this->id . '.maps', $maps);

        return $maps;
    }

    public function getMap()
    {
        $status = $this->fetchStatus();
        if (isset($status['map']['current'])) {
            return $status['map']['current'];
        }
        return null;
    }

    public function setMap(string $map)
    {
        $this->send('changelevel ' . $map);
        cache()->set($this->id . '.status', null, 0);
    }

    public function getNextMap()
    {
        $status = $this->fetchStatus();
        if (isset($status['map']['next'])) {
            return $status['map']['next'];
        }
        return null;
    }

    public function getPlayers()
    {
        $status = $this->fetchStatus();
        if (isset($status['players'])) {
            return $status['players'];
        }
        return null;
    }

    public function getTeams()
    {
        $status = $this->fetchStatus();
        if (isset($status['teams'])) {
            return $status['teams'];
        }
        return null;
    }

    public function getRoundStatus()
    {
        $status = $this->fetchStatus();
        if (isset($status['round'])) {
            return array('round' => $status['round'], 'time' => $status['roundtime']);
        }
        return null;
    }

    public function kickPlayer($player_id)
    {
        $this->send('kick "' . $player_id . '"');
        cache()->set($this->id . '.status', null, 0);
    }

    public function killPlayer($player_id)
    {
        $this->send('amx_slay "' . $player_id . '"');
        cache()->set($this->id . '.status', null, 0);
    }

    private function fetchStatus()
    {
        if ($this->status !== null) {
            return $this->status;
        }

        $status = cache()->get($this->id . '.status');
        if ($status) {
            log_verbose('Status from cache');
            $this->status = $status;
            return $this->status;
        }

        $this->setTimeout(0.2);
        $r = $this->send('amx_status');
        $this->setTimeout();
        if ($r) {
            $r = json_decode($r, true);
            if (isset($r['players']) && isset($r['map']) && isset($r['hostname'])) {
                $this->status = array(
                    'hostname'  => $r['hostname'],
                    'map'       => array('current' => $r['map'], 'next' => $r['map_next']),
                    'players'   => array(),
                    'teams'     => array(
                        'TERRORIST' => array('label' => 'Terrorists', 'active' => true, 'score' => $r['score']['TERRORIST']),
                        'CT'        => array('label' => 'Counter-Terrorists', 'active' => true, 'score' => $r['score']['CT']),
                    ),
                    'roundtime' => $r['roundtime'],
                    'round'     => $r['round'],
                );
                foreach ($r['players'] as $p) {
                    $this->status['players'][] = new Player($p['name'], $p['score'], $p['bot'], $p);
                }
                cache()->set($this->id . '.status', $this->status, 5);
                return $this->status;
            }
        }

        $this->status = false;
        log_verbose('Failed fetching CS server status');
        return false;
    }
}
