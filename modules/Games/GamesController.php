<?php

namespace Games;

class GamesController
{
    public function index($path = null)
    {
        return twig('games.html');
    }

    public function game($game_id)
    {
        return $this->render('game.html');
    }
}
