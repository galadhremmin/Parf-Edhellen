<?php
  namespace services;
  
  class ServiceHandler {
    public static function processRequest(&$servicePtr) {
      $service = null;
      $method  = null;
 
      if (!isset($_GET['service'])) {
        throw new \ErrorException('Missing parameter: service.');
      }
      
      $service    = '\\services\\'.ucfirst($_GET['service']).'Service';
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
          throw new \ErrorException('Unsupported, erroneous or malformed HTTP method.');
      }
      
      if ($dataSource === null) {
        throw new \ErrorException('Empty, invalid or corrupt data source.');
      }

      // de-jsonify the data source
      foreach ($dataSource as $key => $value) {
        $tmp = json_decode($value);
        if ($tmp !== null) {
          $dataSource[$key] = $tmp;
        }
      }

      try {
        $serviceRefl = new \ReflectionClass($service);

        if (! $serviceRefl->isSubclassOf('\\services\\ServiceBase')) {
          throw new Exception('');
        }

        if ($method === null) {
          throw new \ErrorException('Unsupported method '.$method.' in '.$service);
        }

        $servicePtr = $serviceRefl->newInstance();
        return $servicePtr->$method($dataSource, $param);
      } catch (ErrorException $e) {
        throw $e;
      } catch (Exception $e) {
        throw new \ErrorException('Unknown service or invocation exception for '.$service.".\n".$e->getMessage());
      }
    }
  }
