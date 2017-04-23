<?php
  namespace services\handlers;

  class JSONHandler implements IContentHandler {
    public function handle(array& $content) {  
      $json = json_encode($content);
      
      header('Content-Type: application/json; charset=utf-8');
      
      echo $json;
    }
  }
