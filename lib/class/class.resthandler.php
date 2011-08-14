<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class RESTHandler {
    public static function processRequest() {
      $service = null;
      $method  = null;
      
      if (!isset($_GET['service'])) {
        throw new ErrorException('Missing parameter: service.');
      }
      
      $service    = ucfirst($_GET['service']).'Service';
      $method     = null;
      $param      = null;
      $dataSource = null;
      
      if (isset($_GET['param']) && strlen($_GET['param']) > 0) {
        $param = $_GET['param'];
      }
      
      if ($param === null) {
      	$method = 'handleRequest';
      } else {
        $method = 'handleParameterizedRequest';
      }
      
      switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
        case 'GET':
          $dataSource = &$_GET;
          break;
        case 'POST':
          $dataSource = &$_POST;
          break;
        default:
          throw new ErrorException('Unsupported, erroneous or malformed HTTP method.');
      }
      
      if ($dataSource === null) {
        throw new ErrorException('Empty, invalid or corrupt data source.');
      }

      try {
        $serviceRefl = new ReflectionClass($service);

        if (!$serviceRefl->isSubclassOf('RESTfulService')) {
          throw new Exception();
        }

        if ($method === null) {
          throw new ErrorException('Unsupported method '.$method.' in '.$service);
        }

        $serviceObj = new $service();
        return $serviceObj->$method($dataSource, $param);
      } catch (ErrorException $e) {
        throw $e;
      } catch (Exception $e) {
        throw new ErrorException('Unknown service or invocation exception for '.$service.".\n".$e->getMessage());
      }
    }
  }
?>