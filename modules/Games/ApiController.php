<?php

namespace Games;

class ApiController
{
    public function games($games)
    {
        if (is_object($games)) {
            return $this->game($games);
        }
        $data = [];
        foreach ($games as $game) {
            $data[] = $this->game($game);
        }
        return $data;
    }

    public function game($game)
    {
        return [
            'id'       => $game->getId(),
            'label'    => $game->getLabel(),
            'host'     => $game->getHost(),
            'port'     => $game->getPort(),
            'up'       => $game->isUp(),
            'map'      => [
                'current' => $game->getMap(),
                'next'    => $game->getNextMap(),
            ],
            'players'  => $game->getPlayerCount(),
            'teams'    => $game->getTeams(),
            'round'    => $game->getRoundStatus(),
            'actions'  => [
                'kick'   => $game->has('kickPlayer'),
                'kill'   => $game->has('killPlayer'),
                'rename' => $game->has('renamePlayer'),
            ],
            'metadata' => $game->getMetadata(),
        ];
    }

    public function cmd($game)
    {
        $data    = http_request_payload_json();
        if (!isset($data['cmd']) || !is_string($data['cmd'])) {
            http_e418();
        }
        $timeout = null;
        if (isset($data['timeout']) && is_numeric($data['timeout'])) {
            $timeout = $data['timeout'];
        }
        return $game->send($data['cmd'], $timeout);
    }

    public function players($game)
    {
        $data = [];
        foreach ($game->getPlayers() as $player) {
            $data[] = $this->player($player);
        }
        return $data;
    }

    public function vars($game, $var)
    {
        if ($var === null) {
            return $game->getVars();
        }
        return $game->getVar($var);
    }

    public function player($player)
    {
        return [
            'id'     => $player->getId(),
            'name'   => $player->getName(),
            'bot'    => $player->isBot(),
            'score'  => $player->getValue('score'),
            'team'   => $player->getValue('team'),
            'alive'  => $player->getValue('alive'),
            'deaths' => $player->getValue('deaths'),
        ];
    }
}

      // value:
      //   type: string
      //   required: false
      //   method: setVarValue
      //   parameters:
      //     - var-id
