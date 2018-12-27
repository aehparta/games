<?php

namespace Games;

class GamesController
{
    public function index()
    {
        return twig('games.html');
    }

    public function game($game)
    {
        return twig('game.html');
    }
}
