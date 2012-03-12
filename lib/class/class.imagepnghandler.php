<?php
  class ImagePNGHandler implements IContentHandler {
    public function handle(array& $content) {
      $data = $content['response'];
    
      header('Content-Type: image/png');
      
      imagepng($data);
      imagedestroy($data);
    }
  }