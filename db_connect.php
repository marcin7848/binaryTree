<?php

require_once "./config.php";

class db_connect {
    private $pdo;
    private static $instance;

    public function __construct(){
        try{
          global $host, $user, $pass, $database_name;
          $this->pdo = new PDO('mysql:host='.$host.';dbname='.$database_name.'', $user, $pass);
        }catch(PDOException $e){
          exit('Połączenie nie mogło zostać utworzone: ' . $e->getMessage());
        }

    }

    public function getQuery($query){
         return $stmt = $this->pdo->query($query);
     }

}

$db = new db_connect();
