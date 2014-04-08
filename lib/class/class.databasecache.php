<?php

  if (!defined('SYS_ACTIVE')) {
    exit;
  }

  class DatabaseCache extends Caching {
    private $_connection;
    private $_preloadedData;

    public function __construct(Database &$db, $lifetimeMinutes, $tag = null) {
      parent::__construct($lifetimeMinutes, $tag);
      $this->_connection = & $db->connection();
      $this->_preloadedData = null;
    } 

    public function load() {
      if ($this->_preloadedData === null) {
        $this->preload();
      }

      return $this->_preloadedData['data'];
    }

    public function save($content) {
      
      // this is no longer up to date, so deallocate it
      $this->_preloadedData = null;
      

      // insert a new row with the token as the identifier
      $stmt = $this->_connection->prepare('REPLACE INTO `cache` (`content`, `timestamp`, `token`) VALUES (?, ?, ?)');
      $stmt->bind_param('sis', $content, time(), parent::$_tag);
      $stmt->execute();
      $stmt->close();
    }

    public function hasExpired() {
      if ($this->_preloadedData === null) {
        $this->preload();
      }

      return time() - $this->_preloadedData['timestamp'] > parent::$_lifeTime;
    }

    private function preload() {
      $stmt = $this->_connection->prepare('SELECT `content`, `timestamp` FROM `cache` WHERE `token` = ?');

      $stmt->bind_param('s', parent::$_tag);
      $stmt->execute();
      
      $content = null;
      $timestamp = 0;

      $stmt->bind_result($content, $timestamp);
      $stmt->fetch();
      $stmt->close();

      $this->_preloadedData = array('data' => $content, 'timestamp' => $timestamp);
    }
  }

