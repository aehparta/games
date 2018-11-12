<?php

class GamesController extends \Core\Controller
{
    public function indexAction()
    {
        $params = array();
        $params['games'] = \Games\Game::getGames();
        return $this->render('index.html', $params);
    }

    public function gameAction($name)
    {
        $params = array();
        $params['game'] = \Games\Game::getGame($name);
        return $this->render('game.html', $params);
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

    public function gameCmdAction($name)
    {
        $cmd = $this->input('cmd');
        if (empty($cmd)) {
            throw new \Exception400('missing cmd');
        }
        $game = \Games\Game::getGame($name);
        $r = $game->send($cmd);
        return $this->render(null, $r);
    }
}
