<?php
  include_once 'lib/system.php';
    
  $result = array('succeeded' => 'true', 'error' => null, 'response' => null);
  
  try {
    $result['response'] = RESTHandler::processRequest();
  } catch (Exception $e) {
    $result['succeeded'] = false;
    $result['error']     = $e->getMessage();
  }

  $json = json_encode($result);
  
  header('Content-Type: application/json; charset=utf-8');
  header('Content-Length: '.strlen($json));
  
  echo $json;
?>