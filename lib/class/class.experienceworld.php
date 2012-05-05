<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class ExperienceWorld {
    public $ID;
    public $nick;
    public $admin;
    public $token;
    
    const TOKEN_CRYPT_KEY = '6{VJoo_3FZC>uO*jUCkI?P[QFsj>';
    
    public function __construct() {
      $this->ID    = 0;
      $this->token = null;
      $this->admin = false;
      $this->nick  = null;
    }
    
    public function isValid() {
      return $this->ID > 0;
    }
    
    public function load(&$source) {
      $this->nick  = $source['nick'];

      if (isset($source['world'])) {
        if (!$this->unlockWorld($source)) {
          throw new Exception('Invalid world credentials.');
        }
        $this->generateToken();
      } else if (isset($source['token'])) {
        $this->loadToken($source['token']);
      }
    }
    
    public function recordActivity($message, $messageType) {
      $db = Database::instance()->exclusiveConnection(false);
      
      if ($stmt = $db->prepare('INSERT INTO `experience_message` (`WorldID`, `Date`, `Nick`, `Message`, `Type`)
        VALUES(?, ?, ?, ?, ?)')) {
        $date = time();
        $stmt->bind_param('iisss', $this->ID, $date, $this->nick, $message, $messageType);
        $stmt->execute();
        $stmt->close();
      }
    }
    
    public function getActivity($fromID) {
      $db = Database::instance()->connection();
      $activity = array();
      
      if ($stmt = $db->prepare('SELECT `MessageID`, `Nick`, `Message`, `Type` FROM `experience_message`
        WHERE `MessageID` > ? AND `WorldID` = ? ORDER BY `MessageID` ASC')) {
        $stmt->bind_param('ii', $fromID, $this->ID);
        $stmt->execute();
        $stmt->bind_result($id, $nick, $message, $type);
        
        while ($stmt->fetch()) {
          $activity[] = array(
            'id'      => $id,
            'nick'    => ExperienceUtilities::unescape($nick), 
            'message' => ExperienceUtilities::unescape($message),
            'type'    => $type
          );
        }
        
        $stmt->free_result();
        $stmt->close();
      }
      
      return $activity;
    }
    
    public function clear(array $messageTypes) {
      if (!$this->admin) {
        throw new Exception('Insufficient privileges.');
      }
      
      $activity = array();
      $db = Database::instance()->exclusiveConnection(false);
      
      if ($stmt = $db->prepare('DELETE FROM `experience_message` WHERE `Type` = ? 
        AND `WorldID` = ?')) {
        
        foreach ($messageTypes as $messageType) {
          $stmt->bind_param('si', $messageType, $this->ID);
          $stmt->execute();
        }
        
        $stmt->close();
        
        foreach ($messageTypes as $messageType) {
          if ($messageType === 'for-all') {
            continue;
          }
          
          $this->recordActivity('/clear '.$messageType, ExperienceMessageType::None);
        }
      }
    }
    
    public function removeRecord($recordId) {
      if (!$this->admin) {
        throw new Exception('Insufficient privileges.');
      }
      
      $db = Database::instance()->exclusiveConnection(false);
      
      if ($stmt = $db->prepare('DELETE FROM `experience_message` WHERE `MessageID` = ?
        AND `WorldID` = ?')) {
        
        $stmt->bind_param('ii', $recordId, $this->ID);
        $stmt->execute(); 
        $stmt->close(); 
      }
    }
    
    public function editRecord($recordId, $newContent) {
      if (!$this->admin) {
        throw new Exception('Insufficient privileges.');
      }
      
      $db = Database::instance()->exclusiveConnection(false);
      
      if ($stmt = $db->prepare('UPDATE `experience_message` SET `Message` = ? WHERE `MessageID` = ?
        AND `WorldID` = ?')) {
        
        $stmt->bind_param('sii', $newContent, $recordId, $this->ID);
        $stmt->execute();  
        $stmt->close();
      }
    }
    
    private function generateIV() {
      $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
      $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
      
      return $iv;
    }
    
    private function generateToken() {
      $iv = $this->generateIV();
      
      $classData = serialize(
        array($this->ID, $this->admin)
      ); 
      
      $this->token = base64_encode(
        mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::TOKEN_CRYPT_KEY, $classData, MCRYPT_MODE_ECB, $iv)
      );
    }
    
    private function loadToken($token) {
      $iv = $this->generateIV();
      $data = base64_decode($token);
      
      $classData = @unserialize(
        mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::TOKEN_CRYPT_KEY, $data, MCRYPT_MODE_ECB, $iv)
      );
      
      if ($classData === false) {
        throw new Exception('Invalid world token provided.');
      }
      
      $this->ID    = $classData[0];
      $this->admin = $classData[1];
    }
    
    private function unlockWorld(&$source) {
      $world         = $source['world'];
      $password      = $source['worldPwd'] == '0' ? null : hash('sha256', $source['worldPwd']);
      $adminPassword = $source['adminPwd'] == '0' ? null : hash('sha256', $source['adminPwd']);
      $result        = false;
      
      if (!is_numeric($world)) {
        throw new Exception('Incorrect world ID. Expected number.');
      }
      
      if ($world < 1) {
        $result = $this->createWorld($password, $adminPassword);
      } else {
        $result = $this->loadWorld($world, $password, $adminPassword);
      }
      
      return $result;
    }
    
    private function createWorld($password, $adminPassword) {
      $db = Database::instance()->exclusiveConnection(false);
      
      if ($stmt = $db->prepare('INSERT INTO `experience_world` 
        (`AuthorIP`, `Password`, `AdminPassword`, `CreationDate`) VALUES (?, ?, ?, NOW())')) {
        
        $stmt->bind_param('sss', $_SERVER['REMOTE_ADDR'], $password, $adminPassword);
        $stmt->execute();
        
        $this->ID    = $db->insert_id;
        $this->admin = true;
        
        $stmt->close();
      }
      
      return true;
    }
    
    private function loadWorld($world, $password, $adminPassword) {
      $db = Database::instance()->connection();
      if ($stmt = $db->prepare('SELECT `AdminPassword` FROM `experience_world` 
        WHERE (`Password` IS NULL OR `Password` = ?) AND `WorldID` = ?')) {
        
        $stmt->bind_param('si', $password, $world);
        $stmt->execute();
        $stmt->bind_result($worldAdmin);
        
        if ($stmt->fetch() === null) {
          return false;
        }
        
        $this->ID    = $world;
        $this->admin = $worldAdmin == $adminPassword;
        
        $stmt->free_result();
        $stmt->close();
      }
      
      return true;
    }
  }