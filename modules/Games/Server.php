<?php

namespace Games;

class Server
{
    private $socket;

    public function __construct($address, $port)
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($this->socket === false) {
            throw new \Exception('unable to open socket, reason: ' . socket_strerror());
        }
        if (socket_bind($this->socket, $address, $port) === false) {
            throw new \Exception('unable to bind socket to ' . $address . ':' . $port . ', reason: ' . socket_strerror($this->socket));
        }
    }

    public function read()
    {
        return socket_read($this->socket, 1024);
    }

    public static function serve($cmd, $args, $options)
    {
        $server = new self($args['address'], $args['port']);
        while (($data = $server->read()) !== false) {
            if ($data === null) {
                continue;
            }
            echo $data;
        }
    }
}
