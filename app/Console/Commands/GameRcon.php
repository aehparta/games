<?php

namespace App\Console\Commands;

class GameRcon extends \Illuminate\Console\Command
{
    protected $signature   = 'game:rcon {host} {port} {password} {cmd}';
    protected $description = 'Send raw rcon command to remote server';

    /**
     * Send "raw" command to given server.
     */
    public function handle()
    {
        $cmd  = $this->argument('cmd');
        $rcon = new \App\Game\Rcon($this->argument('host'), $this->argument('port'), $this->argument('password'));
        $r    = $rcon->send($cmd);
        if ($r) {
            $this->info('rcon:' . $cmd . ":\n" . $r . "\n");
        } else {
            $this->info('rcon:' . $cmd . ': no response' . "\n");
        }
    }
}
