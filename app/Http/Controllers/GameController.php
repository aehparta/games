<?php

namespace App\Http\Controllers;

class GameController extends Controller
{
    public function games()
    {
        return \App\Game\Game::getGames();
    }
}
