<?php
  namespace controllers;
  
  class IndexController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Index', $engine);
    }
    
    public function load() {
      if ($this->handleHashbang())  {
        return;
      }

      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('reviews', $model->getReviews());
        $this->_engine->assign('sentence', $model->getSentence());
        $this->_engine->assign('loggedIn', $model->getIsLoggedIn());
      }
    }

    /**
     * Handles hashbangs (#!) as per Google's recommendations described here:
     * https://developers.google.com/webmasters/ajax-crawling/docs/getting-started
     *
     * @return bool whether a hashbang was found and processed.
     */
    private function handleHashbang() {
      $fragmentKey = '_escaped_fragment_';
      if (!isset($_GET[$fragmentKey])) {
        return false;
      }

      $fragment = $_GET[$fragmentKey];
      if (empty($fragment)) {
        return false;
      }

      $pieces = explode('=', $fragment);
      $numberOfPieces = count($pieces);

      // Iterate through each key value pair and look for 'w' which is our word key.
      // Move forward in steps of two, because the second element is always believed to be the value.
      for ($i = 0; $i < $numberOfPieces; $i += 2) {
        if ($pieces[$i] === 'w' && $i + 1 < $numberOfPieces) {
          $term = urldecode($pieces[$i + 1]);
          header('Location: translate.php?term='.urlencode($term).'&ajax=false');
          return true;
        }
      }

      return false;
    }
  }
