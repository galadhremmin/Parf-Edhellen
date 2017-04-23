<?php
  include_once '../lib/system.php';
  
  require ROOT.'/lib/hybridauth/Hybrid/Auth.php';
  $auth = new \Hybrid_Auth(ROOT.'/lib/config/config.HybridAuth.php');

  $auth->logoutAllProviders();
  session_destroy();
  session_regenerate_id(true);
  
  header('Location: ../index.php');
  
