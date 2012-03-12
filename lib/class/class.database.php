<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Database {
    private $_conn;
    private static $_inst;
    
    public static function instance() {
      if (self::$_inst === null) {
        self::$_inst = new Database();
      }
      
      return self::$_inst;
    }
  
    private function __construct() {
      $this->_conn = new mysqli(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_USE_DATABASE);
    }
    
    public function __destruct() {
      if ($this->_conn != null) {
        $this->_conn->close();
      }
    }
    
    public function connection() {
      return $this->_conn;
    }
    
    public function exclusiveConnection($requireLogin = true) {
      if ($requireLogin && !Session::isValid()) {
        throw new ErrorException('Inadequate permissions.');
      }
      
      return $this->connection();
    }
  }
?>