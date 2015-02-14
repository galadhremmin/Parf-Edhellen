<?php
  namespace controllers;
  
  class AuthenticateController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Authenticate', $engine, false);
    }
    
    public function load() {    
      $error = null;
      $model = $this->getModel();
      
      try {
        // Initialize the OpenID authentication class
        $provider = new \auth\LightOpenID('elfdict.com');
        
        // Upon class initialization, it's acquiring a variety
        // of modes. Use these do determine subsequent behaviour.
        if (!$provider->mode) {
          // User is not logged in & has requrest to authenticate
          if (isset($_GET['authenticate'])) {
            // Make sure that the requested provider is correctly formatted.
            if (!isset($_POST['provider']) || !is_numeric($_POST['provider'])) {
              throw new ErrorException('Missing provider.');
            }
            
            $providerID = $_POST['provider'];
            if ($model === null) {
              throw new ErrorException('No providers available.');
            }
            
            $providers = $model->getProviders();
            if (!isset($providers[$providerID])) {
              throw new ErrorException('Unrecognised provider.');
            }
          
            // Assign the provider URL as the discovery URL.
            $provider->identity = $providers[$providerID]->URL;
            
            // relocate to the identity provider
            header('Location: ' . $provider->authUrl());
          }
        } else if ($provider->mode === 'cancel') {
          // user cancelled authentication
          $error = 'User has canceled authentication!';
        } else {
          // user is authenticated
          if (Session::register($provider)) {
            // authentication success!
            header('Location: profile.page');
          } else {
            $error = 'Unfortunately, authentication seems to have failed.';
          }
        }
      } catch(ErrorException $e) {
        $error = $e->getMessage();
      }

      if ($model !== null) {
        $this->_engine->assign('providers', $model->getProviders());
      }
      
      $this->_engine->assign('errorMessage', $error);
    }
  }
