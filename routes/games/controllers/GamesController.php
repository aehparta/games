<?php

class GamesController extends \Core\Controller
{
    public function indexAction($path = null)
    {
        return $this->render('index.html');
    }

    public function gameAction($game_id)
    {
        return $this->render('game.html');
    }

    public function gameMapGetAction($name)
    {
        $game = \Games\Game::getGame($name);
        return $this->render(null, $game->getMap());
    }

    public function gameMapSetAction($name, $map)
    {
        $game = \Games\Game::getGame($name);
        $game->setMap($map);
        return $this->render(null, $map);
    }

    public function gameVarAction($name, $var)
    {
        $value = $this->input('value');
        if ($value === null) {
            throw new \Exception400('missing value');
        }
        $game = \Games\Game::getGame($name);
        $r    = $game->setVar($var, $value);
        return $this->render(null, $r);
    }
}
