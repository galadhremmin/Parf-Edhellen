<?php
  include_once 'lib/system.php';
    
  $result = array('succeeded' => 'true', 'error' => null, 'response' => null);
  $servicePtr = null;
  
  try {
    $result['response'] = services\ServiceHandler::processRequest($servicePtr);
  } catch (Exception $e) {
    $result['succeeded'] = false;
    $result['error']     = $e->getMessage();
  }

  $handler = new services\handlers\JSONHandler();


  if ($servicePtr !== null) {
    $handler = $servicePtr->getContentHandler();
  }
  
  if ($handler == null) {
    throw new Exception('The service was found and the request was successfully handled, but an output content handler is missing.');
  }

  $handler->handle($result);