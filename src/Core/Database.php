<?php

namespace SMVC\Core;

use SMVC\Exceptions\SMVCException;
use SMVC\Helpers\Utils;
use PDO;

class Database {

  // our app
  private $app;

  // holds our DB connection
  private $pdo = null;

  // our current query
  private $query;

  // parameters
  private $parameters = [];

  // db instance
  public $db_instance;

  private $lastInsertId;

  /**
   * Constructor
   * @param array $app - the global app variable
   * @param string $db_instance - the DB to connect to
   */
  public function __construct($app, $db_instance = null) {
    $this->app = $app;

    $db_instances = array_keys((array) $this->app->config->db);
    if(is_null($db_instance) || !in_array($db_instance, $db_instances)) {
      $db_instance = 'contacts';
    }
    $this->db_instance = $db_instance;

    $this->connect();
  }

  public function __destruct() {
    $this->disconnect();
  }

  /**
   * connect to the MySQL database
   * @return void
   */
  private function connect() {

    $db_config = $this->app->config->db->{$this->db_instance};
    try {

      // create dsn
      $dsn = 'mysql:dbname='.$db_config->database;
      $dsn .= ';host='.$db_config->host.':'.$db_config->port;
      $dsn .= ';charset='.$db_config->charset;

      // connect
      $this->pdo = new PDO($dsn, $db_config->username, $db_config->password);
      $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
      $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    } catch (\PDOException $e) {
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>'PDO Exception when trying to connect to the database: '.$e->getMessage()]);
    } catch(\Exception $e) {
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>'Exception when trying to connect to the database: '.$e->getMessage()]);
    }
  }

  public function disconnect() {
    $this->pdo = null;
  }

  public function query($sql, $params = [], $returnLastInsertId = true, $fetchmode = PDO::FETCH_OBJ) {

    // set parameters and trim sql
    $sql = $this->cleanSql($sql);
    $this->parameters = $params;

    // exec
    $this->exec($sql);

    // get query type
    $sql_array = explode(' ', $sql);
    $sql_type = mb_strtolower($sql_array[0]);

    switch($sql_type) {
      case 'show':
      case 'select':
      case 'set':
        $return = $this->query->fetchAll($fetchmode);
        break;
      case 'insert':
        if($returnLastInsertId) {
          $return = $this->lastInsertId;
        } else {
          $return = $this->query->rowCount();
        }
        break;
      case 'update':
      case 'delete':
        $return = $this->query->rowCount();
        break;
      default:
        $return = true;
    }

    //$this->disconnect();
    return $return;
  }

  public function row($sql, $params = [], $fetchmode = PDO::FETCH_OBJ) {

    // set parameters and trim sql
    $sql = $this->cleanSql($sql);
    $this->parameters = $params;

    // exec
    $this->exec($sql);
    return $this->query->fetch($fetchmode);
  }

  private function exec($sql) {

    // connect if not connected
    if(is_null($this->pdo)) {
      $this->connect();
    }

    try {

      $this->pdo->beginTransaction();
      $this->query = $this->pdo->prepare($sql);

      // bind params
      foreach($this->parameters as $param => $value) {
        $type = $this->getParamType($value);
        $this->query->bindValue($param, $value, $type);
      }

      // execute and return last insert id
      $this->query->execute();
      $this->lastInsertId = $this->pdo->lastInsertId();

      // commit
      $this->pdo->commit();

      return true;
    } catch(\PDOException $e) {
      $this->pdo->rollBack();
      $this->app->log->error('PDO Exception when trying to query the database. Message: '.$e->getMessage());
      // throw new SMVCException(__FILE__,__LINE__,'PDO Exception when trying to query the database.');
    } finally {
      //$this->disconnect();
      $this->parameters = [];
    }
  }

  private function getParamType($value) {
    switch(gettype($value)) {
      case 'NULL':
        return PDO::PARAM_NULL;
        break;
      case 'string':
        return PDO::PARAM_STR;
        break;
      case 'boolean':
        return PDO::PARAM_BOOL;
        break;
      case 'integer':
        return PDO::PARAM_INT;
        break;
      default:
        return PDO::PARAM_STR;
    }
  }

  private function cleanSql($sql) {
    // remove new lines and multiple adjacent spaces
    return trim(preg_replace('/\s+/', ' ', trim($sql)));
  }

}
