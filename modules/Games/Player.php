<?php

namespace Games;

class Player
{
    private $id;
    private $name;
    private $bot;
    private $data;

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isBot()
    {
        return $this->bot;
    }

    public function setBot(bool $is)
    {
        $this->bot = $is;
    }

    public function getValue($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function setValue($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public static function findOrCreate(string $id, string $name, bool $bot = false, array $data = [])
    {
        if (strtoupper($id) == 'BOT') {
            $id  = $name;
            $bot = true;
        }

        $player = em()->getRepository('Games\Player')->findOneBy(['id' => $id, 'bot' => $bot]);
        if (!$player) {
            $player = new self();
            $player->setId($id);
            $player->setName($name);
            $player->setBot($bot);
        }
        $player->setName($name);
        $player->setData($data);
        em()->persist($player);
        em()->flush();

        return $player;
    }

    public static function cmdPlayers($cmd, $args, $options)
    {
        $repo    = em()->getRepository(get_class());
        $players = $repo->findAll();

        foreach ($players as $player) {
            echo str_pad($player->getId(), 24) . ' ' . $player->getName() . "\n";
        }

        return true;
    }
}
