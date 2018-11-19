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

}
