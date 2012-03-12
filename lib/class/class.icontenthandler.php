<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  interface IContentHandler {
    function handle(array& $content);
  }