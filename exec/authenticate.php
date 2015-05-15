<?php
  include_once '../lib/system.php';
  
  $providerId = null;
  if (! isset($_GET['provider']) || ! is_numeric($_GET['provider'])) {
    header('Location: ../authenticate.page');
    exit;
  }
  
  // Load the authentication provider
  $providerId = intval($_GET['provider']);
  $provider = new \data\entities\AuthProvider();
  $provider->load($providerId);
  
  if (! $provider->validate()) {
    header('Location: ../authenticate.page');
    exit;
  }
  
  // Perform the authentication with the idP
  require ROOT.'/lib/hybridauth/Hybrid/Auth.php';
  $auth = new \Hybrid_Auth(ROOT.'/lib/config/config.HybridAuth.php');
  $authProvider = $auth->authenticate($provider->url);

  // Request user profile information from the authentication format
  $profile = $authProvider->getUserProfile();
  
  // Authenticate the profile
  $token       = new \auth\AccessToken($provider->id, $profile->identifier);
  $credentials = \auth\Credentials::authenticate($provider->id, $profile->email, $token, $profile->firstName.' '.$profile->lastName);
  
  // Test authentication status
  if (! $credentials->permitted(new \auth\BasicAccessRequest())) {
    header('Location: ../authenticate.page');
    exit;
  }

  // Record the login attempt.
  $time       = time();
  $remoteAddr = $_SERVER['REMOTE_ADDR'];
  $account    = \auth\Credentials::current()->account();
  
  $stmt = \data\Database::instance()->connection()->prepare(
      'INSERT INTO `auth_logins` (`Date`, `IP`, `AccountID`) VALUES (?, ?, ?)'
  );
  $stmt->bind_param('isi', $time, $remoteAddr, $account->id);
  $stmt->execute();
  $stmt = null;
  
  header('Location: ../authenticate-complete.page');
  