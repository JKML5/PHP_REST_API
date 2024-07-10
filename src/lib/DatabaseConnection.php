<?php

class DatabaseConnection
{
  public ?mysqli $connection = null;

  public function getConnection(): mysqli
  {
    if ($this->connection === null) {
      $dbConfig = require 'config/config.php';
      $host = $dbConfig['database']['default']['host'];
      $database = $dbConfig['database']['default']['database'];
      $username = $dbConfig['database']['default']['username'];
      $password = $dbConfig['database']['default']['password'];

      $this->connection = new mysqli($host, $username, $password, $database);

      if ($this->connection->connect_error)
        throw new Exception('Connection failed: ' . $this->connection->connect_error);

      $this->connection->set_charset("utf8");

      return $this->connection;
    }
  }
}
