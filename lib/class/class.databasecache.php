<?php

  if (!defined('SYS_ACTIVE')) {
    exit;
  }

  class DatabaseCache extends Caching {
    private $_connection;
    private $_preloadedData;

    public function __construct(Database &$db, $lifetimeMinutes, $tag = null) {
      parent::__construct($lifetimeMinutes, $tag);
      $this->_connection = $db->connection();
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
      
      // Insert (or replace existing) cache item for the generated tag.
      $today = time();

      $stmt = $this->_connection->prepare('REPLACE INTO `cache` (`content`, `timestamp`, `token`) VALUES (?, ?, ?)');
      $stmt->bind_param('sis', $content, $today, $this->_tag);
      $stmt->execute();
      $stmt->close();
    }

    public function hasExpired() {
      if ($this->_preloadedData === null) {
        $this->preload();
      }

      return time() - $this->_preloadedData['timestamp'] > $this->_lifeTime;
    }

    private function preload() {
      $stmt = $this->_connection->prepare('SELECT `content`, `timestamp` FROM `cache` WHERE `token` = ?');

      $stmt->bind_param('s', $this->_tag);
      $stmt->execute();
      
      $stmt->bind_result($content, $timestamp);
      if (!$stmt->fetch()) {
        $content = null;
        $timestamp = 0; 
      }

      $stmt->close();

      $this->_preloadedData = array('data' => $content, 'timestamp' => $timestamp);
    }
  }

