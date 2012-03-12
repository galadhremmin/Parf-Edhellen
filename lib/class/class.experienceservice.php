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
      
      $world = new ExperienceWorld();
      $world->load($input);
      
      return self::$command(
        $world, 
        isset($input['args']) ? $input['args'] : null
      );
    }
    
    protected static function invokeLword(&$data, $args) {
      return new ExperienceResponse(
        $data->nick,
        $data, 
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
    
    private static function coerce(&$world) {
      if (!$world->isValid()) {
        throw new Exception('Invalid world connection.');
      }
    }
  }
