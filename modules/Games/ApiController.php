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

    private function game($game)
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
}

// id:
//   method: getId
// label:
//   method: getLabel
// host:
//   method: getHost
// port:
//   method: getPort
// up:
//   method: isUp
// map:
//   current:
//     method: getMap
//   next:
//     method: getNextMap
// players:
//   method: getPlayerCount
// teams:
//   method: getTeams
// round:
//   method: getRoundStatus
// actions:
//   kick:
//     method: has
//     parameters:
//       - kickPlayer
//   kill:
//     method: has
//     parameters:
//       - killPlayer
//   rename:
//     method: has
//     parameters:
//       - renamePlayer
// metadata:
//   method: getMetadata
