<?php

class GamesApiController extends \Core\Controller
{
    public function gamesGetAction($game_id = null)
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

    public function gameVarGetAction($game_id, $var)
    {
        $game = \Games\Game::getGame($game_id);
        return $this->render(null, $game->getVar($var));
    }

    public function gameCmdAction($game_id)
    {
        $api  = new \API\API($this);
        $data = $api->parse('game-cmd');
        $game = \Games\Game::getGame($game_id);
        $r    = $game->send($data['cmd']);
        return $this->render(null, $r);
    }

}
