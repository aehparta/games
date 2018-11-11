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

    public function gameMapAction($name, $map)
    {
        $game = \Games\Game::getGame($name);
        $game->setMap($map);
        throw new \RedirectException(\kernel::historyPop());
    }
}
