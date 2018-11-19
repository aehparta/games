<?php

namespace App\Console\Commands;

class GameList extends \Illuminate\Console\Command
{
    protected $signature   = 'game:list';
    protected $description = 'List all configured games';

    /**
     * Send "raw" command to given server.
     */
    public function handle()
    {
        $games = \App\Game\Game::getGames();
        $this->info('Games:');
        foreach ($games as $game) {
            $this->info('  ' . str_pad($game->getId(), 8) . ': ' . $game->getLabel());
        }
    }
}
