<?php

namespace Games;

class CS extends \Games\Game
{
    const LABEL = 'Counter-Strike 1.6';

    public function isUp()
    {
        return $this->fetchStatus() !== false;
    }

    private function fetchStatus()
    {
        static $status = null;
        if ($status !== null) {
            return $status;
        }
        return false;
    }
}
