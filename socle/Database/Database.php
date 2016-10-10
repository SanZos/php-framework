<?php
/**
* Classe de gestion de singelton a la base de données
* @version 0.1
* @package Database\Database
* @author Antonin DUBOIS
*/
namespace Database;

use Config\Config;

/**
* Class Database
* @see Config\Config
*/
class Database {
    /** @var \PDO Instance de la classe PDO */
    private $PDOInstance = null;
    
    /** @var Database Instance de la classe Database */
    private static $instance = null;
    
    /**
    * Constructeur
    *
    * @see \PDO::__construct()
    */
    public function __construct() {
        $mysql = Config::getMysqlConfig();
        if(isset($mysql->socket) && !empty($mysql->socket)){
            $host = 'unix_socket='.$mysql->socket;
        } else if(isset($mysql->host) && !empty($mysql->host)){
            $host = 'host='.$mysql->host;
        }
        try {
            $this->PDOInstance = new \PDO('mysql:dbname='.$mysql->name.';'.$host, $mysql->user, $mysql->passwd, array( \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
    * Crée et retourne l'objet Database
    *
    * @access public
    * @static
    * @param void
    * @return Database $instance
    */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            try {
                self::$instance = new Database();
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return self::$instance;
    }
    
    /**
    * Prépare une requête SQL avec PDO
    *
    * @param string $query La requête SQL
    * @return \PDOStatement Retourne l'objet \PDOStatement
    */
    public function prepare($query) {
        return $this->PDOInstance->prepare($query);
    }
    
    /**
    * Renvoie le dernier index inséré
    *
    * @return int Index de la dernière insertion
    */
    public function lastInsertId() {
        return $this->PDOInstance->lastInsertId();
    }

    public static function dateFormat(&$date){
        $date = \DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    }
}