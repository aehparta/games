<?php

namespace App\Console\Commands;

class GameCmd extends \Illuminate\Console\Command
{
    protected $signature   = 'game:cmd {id} {cmd}';
    protected $description = 'Send command to specific game';

    /**
     * Send "raw" command to given server.
     */
    public function handle()
    {
        $game = \App\Game\Game::getGame($this->argument('id'));
        $r    = $game->send($this->argument('cmd'));
        if ($r) {
            echo $game->getId() . ':' . $game->getLabel() . ':' . $this->argument('cmd') . ":\n" . $r . "\n";
        } else {
            echo $game->getId() . ':' . $game->getLabel() . ':' . $this->argument('cmd') . ':noresponse' . "\n";
        }
    }
}
