<?php

class GamesApiController extends \Core\Controller
{
    public function listAction($game_id = null)
    {
        if ($game_id !== null) {
            $game = \Games\Game::getGame($game_id);
        } else {
            $game = \Games\Game::getGames();
        }
        $api  = new \API\API($this);
        $data = $api->parse('game', $game);
        return $this->render(null, $data);
    }

    public function varAction($game_id, $var_id = null)
    {
        $game = \Games\Game::getGame($game_id);
        if ($var_id === null) {
            return $this->render(null, $game->getVars());
        }
        $api = new \API\API($this);
        $api->setParameter('var-id', $var_id);
        $data = $api->parse('game-var', $game);
        return $this->render(null, $game->getVar($var_id));
    }

    public function cmdAction($game_id)
    {
        $api  = new \API\API($this);
        $data = $api->parse('game-cmd');
        $game = \Games\Game::getGame($game_id);
        $r    = $game->send($data['cmd']);
        return $this->render(null, $r);
    }

    public function mapsAction($game_id)
    {
        $game = \Games\Game::getGame($game_id);
        return $this->render(null, $game->getMaps());
    }

    public function mapAction($game_id, $map_id)
    {
        $game = \Games\Game::getGame($game_id);
        $game->setMap($map_id);
        return $this->render(null, $game->getMap());
    }

    public function playersAction($game_id)
    {
        $game    = \Games\Game::getGame($game_id);
        $players = $game->getPlayers();
        $api     = new \API\API($this);
        $data    = $api->parse('game-players', $players);
        return $this->render(null, $data);
    }

    public function playerDeleteAction($game_id, $player_id)
    {
        $game = \Games\Game::getGame($game_id);
        $game->kickPlayer($player_id);
        return $this->render(null, null);
    }
}
