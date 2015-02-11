<?php
  namespace services;

  class DrawTengwarService extends ServiceBase {
    const TENGWAR_FONT_SIZE = 20.0;
    const PADDING = 10;
  
    public function __construct() {
      parent::__construct();
      
      parent::registerMethod('render', 'render');
    }
  
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request is unsupported.');
    }
    
    public function getContentHandler() {
      return new handlers\ImagePNGHandler();
    }
    
    protected static function render(&$input) {
      if (!isset($input['code'])) {
        return null;
      }
      
      // Acquire the ASCII code
      $text = $input['code'];
      
      if (strlen($text) > 128) {
        throw new Exception('ASCII recipient cap exceeded.');
      }
      
      $text = base64_decode($text);
      if ($text == false) {
        throw new Exception('Invalid input string.');
      }
      
      // Acquire the font
      $tengwarFont = ROOT.'fonts/tngan.ttf';
      if (!file_exists($tengwarFont)) {
        throw new Exception('tngan.ttf not found.');
      }
      
      // Acquire the elfdict logo
      $logoFile = ROOT.'img/elfdict-logo.png';
      if (!file_exists($logoFile)) {
        $logoFile = null; // optional - if this one is null, no logo is rendered onto the surface
      }
      
      // calculate width and height based on the data acquired from the TTF rendering engine
      $rect = imagettfbbox(self::TENGWAR_FONT_SIZE, 0, $tengwarFont, $text);
      
      // technique found in the PHP manual: http://www.php.net/manual/en/function.imagettfbbox.php
      $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6])); 
      $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6])); 
      $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7])); 
      $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7])); 
      
      $width  = $maxX - $minX + self::PADDING * 2;
      $height = $maxY - $minY + self::PADDING * 2;
      
      if ($width < 10 || $height < 10) {
        throw new Exception('Invalid input string.');
      }
      
      $img = imagecreatetruecolor($width, $height);
      
      // allocate colours
      $backgroundColor = imagecolorallocate($img, 255, 255, 255);
      $tengwarColor    = imagecolorallocate($img, 0, 0, 0);
      
      // fill the image with the background colour
      imagefill($img, 0, 0, $backgroundColor);
      
      // render the tengwar font onto the canvas surface
      imagettftext($img, self::TENGWAR_FONT_SIZE, 0, self::PADDING, self::PADDING + abs($minY), $tengwarColor, $tengwarFont, $text);
      
      // render the elfdict logo
      if ($logoFile !== null) {
        $data = getimagesize($logoFile);
        
        if ($data !== false) { // returns false on failure
          $logoImg = imagecreatefrompng($logoFile); // returns an array where the first element contains the width and the second element the images' height
          
          $x = $width - $data[0] - 1;
          $y = $height - $data[1] - 1;
          
          imagecopy($img, $logoImg, $x, $y, 0, 0, $data[0], $data[1]); // copies the logo onto the surface
          
          imagedestroy($logoImg);
        }
      }
      
      imagecolordeallocate($img, $backgroundColor);
      imagecolordeallocate($img, $tengwarColor);
            
      return $img;
    }
  }
