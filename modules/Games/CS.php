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
        return [];
    }

    public function getTeams()
    {
        $status = $this->fetchStatus();
        if (isset($status['teams'])) {
            return $status['teams'];
        }
        return [];
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
                    // $this->status['players'][] = Player::findOrCreate($p['name'], $p['score'], $p['bot'], $p);
                }
                cache()->set($this->id . '.status', $this->status, 5);
                return $this->status;
            }
        }

        $this->status = false;
        log_verbose('Failed fetching CS server status');
        return false;
    }

    public static function parseLogs($cmd, $args, $options)
    {
        $path = cfg(['games', $args['game'], 'logs', 'path']);
        if (!is_dir($path)) {
            log_error('Unable to parse cs 1.6 logs, log path is invalid: ' . $path);
            return false;
        }

        /* get time of last record */
        $last = em()->getRepository('Games\Stat')->findBy(array('game' => $args['game']), array('time' => 'DESC'), 1, 0);
        if (!empty($last)) {
            $last = array_pop($last);
            $last = $last->getTime();
        } else {
            $last = new \DateTime('1990-01-01');
        }
        /* save (current time minus few minutes) to be used later */
        $older_than = new \DateTime('-5 minutes');

        $logs = scandir($path, SCANDIR_SORT_ASCENDING);
        foreach ($logs as $log) {
            /* if log file modification time is older than last entry, this is not a log file we want to read */
            if ($log[0] == '.' || $last > date_create('@' . filemtime($path . '/' . $log))) {
                continue;
            }
            log_info('Parsing file ' . $path . '/' . $log . ' as it seems newer than last stat entry in database');
            /* open log file and start parsing */
            $f = fopen($path . '/' . $log, 'r');
            while (($line = fgets($f)) !== false) {
                /* reset variables */
                $matches = [];
                $action  = null;
                $tool    = null;
                $p_src   = ['id' => null, 'name' => null, 'team' => null, 'bot' => true];
                $p_dst   = ['id' => null, 'name' => null, 'team' => null, 'bot' => true];
                /* pattern for someone killed someone */
                $pkilled = '@L ([0-9]+\/[0-9]+\/[0-9]+ - [0-9]+:[0-9]+:[0-9]+): "([^<]+)<[0-9]+><([^>]+)><([A-Z]+)>" killed "([^<]+)<[0-9]+><([^>]+)><([A-Z]+)>" with "([0-9-a-z]+)"@';
                /* suicide */
                $psuicide = '@L ([0-9]+\/[0-9]+\/[0-9]+ - [0-9]+:[0-9]+:[0-9]+): "([^<]+)<[0-9]+><([^>]+)><([A-Z]+)>" committed suicide with "([0-9-a-z]+)"@';
                /* someone planted the bomb */
                $pplanted = '@L ([0-9]+\/[0-9]+\/[0-9]+ - [0-9]+:[0-9]+:[0-9]+): "([^<]+)<[0-9]+><([^>]+)><([A-Z]+)>" triggered "Planted_The_Bomb"@';
                /* someone defused the bomb */
                $pdefused = '@L ([0-9]+\/[0-9]+\/[0-9]+ - [0-9]+:[0-9]+:[0-9]+): "([^<]+)<[0-9]+><([^>]+)><([A-Z]+)>" triggered "Defused_The_Bomb"@';
                /* try to match some of the patterns */
                if (preg_match($pkilled, $line, $matches)) {
                    /* this was a kill */
                    $action = 'kill';
                    $tool   = $matches[8];
                    $p_src  = ['id' => $matches[3], 'name' => $matches[2], 'team' => $matches[4], 'bot' => $matches[3] == 'BOT'];
                    $p_dst  = ['id' => $matches[6], 'name' => $matches[5], 'team' => $matches[7], 'bot' => $matches[6] == 'BOT'];
                } else if (preg_match($psuicide, $line, $matches)) {
                    /* suicide */
                    $action = 'suicide';
                    $tool   = $matches[4];
                    $p_src  = ['id' => $matches[3], 'name' => $matches[2], 'team' => $matches[4], 'bot' => $matches[3] == 'BOT'];
                } else if (preg_match($pplanted, $line, $matches)) {
                    /* this was a plantation */
                    $action = 'bomb planted';
                    $p_src  = ['id' => $matches[3], 'name' => $matches[2], 'team' => $matches[4], 'bot' => $matches[3] == 'BOT'];
                } else if (preg_match($pdefused, $line, $matches)) {
                    /* this was a defusion */
                    $action = 'bomb defused';
                    $p_src  = ['id' => $matches[3], 'name' => $matches[2], 'team' => $matches[4], 'bot' => $matches[3] == 'BOT'];
                } else {
                    /* missed match */
                    continue;
                }
                /* time comes always from the same location */
                $time = new \DateTime(str_replace('-', ' ', $matches[1]));
                /* check that last entry found from database is older than this one */
                if ($last >= $time) {
                    continue;
                }
                /* check that we only parse entries that are little bit in the past so every record is already written
                 * this is because hlds server writes entries in log bit slowly and does not flush output buffer "properly"
                 */
                if ($time >= $older_than) {
                    continue;
                }
                /* get players */
                $p_src = $p_src['id'] !== null ? Player::findOrCreate($p_src['id'], $p_src['name'], $p_src['bot']) : null;
                $p_dst = $p_dst['id'] !== null ? Player::findOrCreate($p_dst['id'], $p_dst['name'], $p_dst['bot']) : null;
                /* new entry in database */
                $stat = new Stat();
                $stat->setGame($args['game']);
                $stat->setTime($time);
                $stat->setAction($action);
                $stat->setPlayerSrc($p_src);
                $stat->setPlayerDst($p_dst);
                $stat->setTool($tool);
                em()->persist($stat);
            }
            fclose($f);
            em()->flush();
        }

        return true;
    }
}
