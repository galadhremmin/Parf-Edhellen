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
      if ($this->_conn->connect_error) {
        throw new Exception('Failed to connect to '.MYSQL_SERVER.'. Error: '.$mysql->connect_error);
      }

      $this->_conn->set_charset('utf8');
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
