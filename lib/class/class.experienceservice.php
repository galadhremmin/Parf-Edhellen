<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class ExperienceService extends RESTfulService {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getExperience');
      parent::registerMethod('receiveCommand', 'receiveCommand');
    }
  
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getExperience($id) {
      return null;
    }
    
    protected static function receiveCommand(&$input) {
      if (!isset($input['command']) || !isset($input['nick'])) {
        throw new Exception('Command or nick is missing.');
      }
      
      $command = 'invoke'.ucfirst($input['command']);
      
      if (preg_match('/[^a-zA-Z0-9]/', $command)) {
        throw new Exception('Invalid command format "' . $command . '".');
      }
      
      if (!method_exists(__CLASS__, $command)) {
        throw new Exception('Unrecognised command.');
      }
      
      if (isset($input['args'])) {
         $input['args'] = str_replace(
            array('<', '>'), 
            array('&lt;', '&gt;'), 
            $input['args']
         );
      } else {
         $input['args'] = null;
      }
      
      $world = new ExperienceWorld();
      $world->load($input);
      
      return self::$command(
        $world, 
        $input['args']
      );
    }
    
    protected static function invokeLword(&$world, $args) {
      $world->recordActivity($world->nick.' connected.', ExperienceMessageType::Information);
    
      return new ExperienceResponse(
        $world->nick,
        $world, 
        ExperienceMessageType::None
      );
    }
    
    protected static function invokeSay(&$world, $args) {
      if ($args === null || count($args) < 1) {
        throw new Exception('Missing message.');
      }
      
      self::coerce($world);
      
      $message = implode(' ', $args);      
      $world->recordActivity($message, ExperienceMessageType::Message);
      
      return new ExperienceResponse(
        null, null, ExperienceMessageType::None
      );
    }
    
    protected static function invokeUpdate(&$world, $args) {
      self::coerce($world);
    
      $activity = $world->getActivity(
        $args == null || count($args) < 1 ? 0 : $args[0]
      );
      $response = array();
      
      foreach ($activity as $item) {
        $response[] = new ExperienceMessage(
          $item['nick'],
          $item['message'], 
          $item['type'],
          $item['id']
        );
      }
      
      return $response;
    }
    
    protected static function invokeImage(&$world, $args) {
      self::assignScenery('image', $world, $args);
    }
    
    protected static function invokeThumb(&$world, $args) {
      self::assignScenery('thumb', $world, $args);
    }
    
    protected static function invokeScenery(&$world, $args) {
       return self::invokeImage($world, $args);
    }
    
    protected static function invokeRoom(&$world, $args) {
       return self::invokeThumb($world, $args);
    }
    
    protected static function invokeRoll(&$world, $args) {
      self::coerce($world);
      
      $sides = 6;
      if ($args !== null && count($args) > 0 && is_numeric($args[0]) && $args[0] > 2) {
        $sides = $args[0];
      }
      
      if ($sides > 1000) {
        $sides = 1000;
      }
      
      srand(time());
      $result = rand(1, $sides);
      
      $world->recordActivity($world->nick . ' rolled a d' . $sides .': '.$result, ExperienceMessageType::Information);
      
      return new ExperienceResponse(
        null, $cmd, ExperienceMessageType::None
      );
    }
    
    protected static function invokeClear(&$world, $messageTypes) {
      self::coerce($world);
    
      if (!$world->admin) {
        throw new Exception('Insufficient privileges. Only a world master might clear the chronicles for everyone.');
      }
      
      $world->clear($messageTypes);
      
      return new ExperienceResponse(
        null, null, ExperienceMessageType::None
      ); 
    }
    
    protected static function invokeInformation(&$world, $args) {
      if (count($args) < 2) {
         throw new Exception('Insufficient parameters.');
      }
      
      $message = implode(' ', $args);
      $world->recordActivity($message, ExperienceMessageType::Information);
      
      return new ExperienceResponse(
        null, null, ExperienceMessageType::None
      ); 
    }
    
    protected static function invokeEdit(&$world, $args) {
      self::coerce($world);
    
      if (!$world->admin) {
        throw new Exception('Insufficient privileges. Only a world master might revise existing messages.');
      }
      
      if ($args == null || count($args) < 1 || !is_numeric($args[0])) {
        throw new Exception('A valid message ID is missing.');
      }
      
      $messageId = (int) $args[0];
      $message   = null;
      
      if (count($args) > 1) {
        $message = implode(' ', array_slice($args, 1));
      }
      
      $notification = null;
      if ($message == null) {
        $world->removeRecord($messageId);
        $notification = 'Record removed.';
      } else {
        $world->editRecord($messageId, $message);
        $notification = 'Record changed.';
      }
      
      return new ExperienceResponse(
        null, $notification, ExperienceMessageType::Information
      );
    } 
        
    private static function coerce(&$world) {
      if (!$world->isValid()) {
        throw new Exception('Invalid world connection.');
      }
    }
    
    private static function assignScenery($context, &$world, $args) {
      if ($args === null || count($args) < 1) {
        throw new Exception('Missing image URL.');
      }
      
      self::coerce($world);
      
      if (!$world->admin) {
        throw new Exception('Insufficient privileges. Only a world master might change scenery.');
      }
      
      $cmd = '/'. $context .' '.implode('%20', $args);
      $world->recordActivity($cmd, ExperienceMessageType::None);
      
      return new ExperienceResponse(
        null, $cmd, ExperienceMessageType::None
      );
    }
  }
