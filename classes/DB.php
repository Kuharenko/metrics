<?php

require 'classes/Config.php';

class DB
{
    private static $instance = null;
    private $connection;

    private function __clone()
    {
    }

    private function __construct()
    {
        $config = Config::DB();
        $this->connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        if (mysqli_connect_error()) {
            trigger_error("Failed to connect to MySQL: " . mysqli_connect_error(), E_USER_ERROR);
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}