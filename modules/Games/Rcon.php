<?php

/**
 * Copyright (c) 2013 Daniele Pantaleone
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @version     1.0
 * @author      Daniele Pantaleone
 * @copyright   Daniele Pantaleone, 3 August, 2013
 */

namespace Games;

class Rcon
{
    private $host;
    private $port;
    private $password;
    private $timeout = 0.1;
    private $prefix;

    private $response_trim = 'print';

    private $socket = null;
    private $errno;
    private $errstr;

    /**
     * Construct an Rcon object.
     *
     * @param string $host     The ip/domain where to send RCON commands
     * @param int    $port     The port on which the server accepts RCON commands
     * @param string $password The server RCON password
     * @param float  $timeout  An optional UDP socket timeout
     */
    public function __construct($host, $port, $password)
    {
        if (!$host || !$port) {
            throw new \Exception('trying to connect without specified host or port');
        }
        $this->host     = $host;
        $this->port     = $port;
        $this->password = $password;
        $this->prefix   = str_repeat(chr(255), 4) . 'rcon ' . $this->password . ' ';
    }

    /**
     * Initialize the UDP socket where to send RCON commands.
     *
     * @author Daniele Pantaleone
     *
     * @throws RconException If the socket fails in being created
     */
    private function connect()
    {
        if ($this->socket) {
            return true;
        }

        /* create the UDP socket where to send data */
        $this->socket = @fsockopen("udp://{$this->host}",
            $this->port,
            $this->errno,
            $this->errstr,
            $this->timeout);
        if (!$this->socket) {
            log_notice('could not connect to host ' . $this->host . ' on port ' . $this->port);
            return false;
        }
        return true;
    }

    /**
     * Close a previously initialized UDP socket.
     * Will do nothing if the socket is already closed.
     *
     * @author Daniele Pantaleone
     */
    private function disconnect()
    {
        if (!is_null($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Read a server response from the UDP socket.
     * Will return NULL if the server response is not valid.
     *
     * @author Daniele Pantaleone
     *
     * @throws Exception If the UDP socket has not been correctly initialized
     * @return The       server response as a string if it's valid otherwise NULL
     */
    public function read($raw = false)
    {
        if (is_null($this->socket)) {
            log_err('could not read response: UDP socket is NULL');
            return false;
        }

        stream_set_timeout($this->socket, 0, $this->timeout * 1e6);

        $r = '';
        while ($buffer = fread($this->socket, 65536)) {
            $r .= $buffer;
        }

        if ($raw) {
            return $r;
        }

        $r = str_replace("\xff\xff\xff\xff" . $this->response_trim, '', $r);
        
        $response = '';
        for ($i = 0; $i < strlen($r); $i++) {
            $c = $r[$i];
            if (ctype_space($c) || ctype_print($c)) {
                $response .= $c;
            }
        }

        $response = trim($response);
        if (empty($response)) {
            return null;
        }

        return $response;
    }

    /**
     * Write a command con the UDP socket.
     *
     * @author Daniele Pantaleone
     *
     * @param  string    $command The command to be executed
     * @throws Exception If the UDP socket has not been initialized
     */
    private function write($command, $no_prefix = false)
    {
        if (is_null($this->socket)) {
            log_err('could not send command ' . $command . ': UDP socket is NULL');
            return false;
        }
        $data = ($no_prefix ? '' : $this->prefix) . $command . "\n";
        return fwrite($this->socket, $data);
    }

    /**
     * Send an RCON command.
     */
    public function send($command, $no_prefix = false, $read_raw = false)
    {
        if (!$this->connect()) {
            return false;
        }
        if (!$this->write($command, $no_prefix)) {
            return false;
        }

        $res = $this->read($read_raw);
        $this->disconnect();

        return $res;
    }

    /**
     * Send "raw" command to given server.
     */
    public static function cmd($cmd, $args, $options)
    {
        $rcon = new self($args['host'], $args['port'], $args['password']);
        $r    = $rcon->send($args['command']);
        if ($r) {
            log_info('rcon:' . $args['command'] . ":\n" . $r . "\n");
        } else {
            log_info('rcon:' . $args['command'] . ': no response' . "\n");
        }
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get current password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set custom prefix before command.
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Set timeout.
     */
    public function setTimeout($timeout)
    {
        if (!$timeout) {
            $this->timeout = 0.1;
        } else {
            $this->timeout = $timeout;
        }
    }

    /**
     * Remove given string after receiving data from start of data.
     */
    public function setResponseTrim($str)
    {
        $this->response_trim = $str;
    }
}
