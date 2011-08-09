<?php
  include_once '../lib/system.php';
  
  Session::unregister();
  
  header('Location: ../index.php');
?>