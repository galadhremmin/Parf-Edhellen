<?php
  include_once 'lib/system.php';
  
  $result = array('succeeded' => 'true', 'error' => null, 'response' => null);
  try {
    $result['response'] = RESTHandler::processRequest();
  } catch (Exception $e) {
    $result['succeeded'] = false;
    $result['error']     = $e->getMessage();
  }
  
  echo json_encode($result);
?>