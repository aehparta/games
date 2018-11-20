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
}
