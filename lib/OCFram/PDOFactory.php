<?php
namespace OCFram;

class PDOFactory
{
  public static function getMysqlConnexion()
  {
    $db = new \PDO(
        'mysql:host=localhost;dbname='.DEFAULT_DB_NAME,
        DEFAULT_DB_USERNAME,
        DEFAULT_DB_PASSWD);

    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
    return $db;
  }
}