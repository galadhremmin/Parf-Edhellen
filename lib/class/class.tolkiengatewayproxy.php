<?php
  class TolkienGatewayProxy {
    const ETYM_PATTERN = '/==\\s*etymology\\s*==/i';
    const ETYM_TOKEN   = '==etymology==';
  
    public function acquire($tag) {
      $html = self::cache($tag);
      
      if ($html != null) {
        return $html;
      }
      
      $ch = curl_init();
      
      curl_setopt($ch, CURLOPT_URL, "http://tolkiengateway.net/w/index.php?title=" .
        urlencode($tag)."&action=edit");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.79 Safari/535.11');
      
      // grab URL and pass it to the browser
      $html = curl_exec($ch);

      // close cURL resource, and free up system resources
      curl_close($ch);
      
      $html     = str_replace('&lt;', '<', $html); // undo their escapes
      $startPos = stripos($html, '<textarea');
      $endPos   = stripos($html, '</textarea');
      
      if ($startPos === false || $endPos === false) {
        return null;
      }
      
      self::removeReferences($html);
      
      $html = strip_tags(preg_replace(self::ETYM_PATTERN, self::ETYM_TOKEN, substr($html, $startPos, $endPos - $startPos)));
      $startPos = stripos($html, self::ETYM_TOKEN);
      
      if ($startPos === false) {
        return null;
      }
            
      $startPos += strlen(self::ETYM_TOKEN);      
      $endPos = strpos($html, '==', $startPos);
      
      if ($endPos === false) { 
        $endPos = strlen($html); // assume that the search has reached the EOF 
      }
      
      $html = trim(substr($html, $startPos, $endPos - $startPos));
      
      self::parseWiki($html);
      self::cache($tag, $html);
      
      return $html;
    }
    
    public static function removeReferences(&$h) {
      $h = preg_replace('/<ref([\\sa-z0-9=\\-"\']+)?>[^<]+<\\/ref>|\\{\\{[^\\}]+\\}\\}/i', '', $h);
    }
    
    private static function parseWiki(&$h) {
      $m = null;
      preg_match_all('/\\[\\[(([^\\|\\]]+)\\|)?([^\\]]+)\\]\\]/', $h, $m);
      
      if ($m != null && count($m) > 0) {
        $keys   = array("'''", "''");
        $values = array("", "_");
        
        for ($i = 0; $i < count($m[0]); ++$i) {
          $keys[] = $m[0][$i];
          $values[] = $m[3][$i];
        } 
      }
      
      $h = str_replace($keys, $values, $h);
    }
    
    private static function cache($key, $value = null) {
      if (strlen($key) < 1) {
        return null;
      }
    
      $normalizedString = StringWizard::normalize($key);
      
      if (preg_match('/[^a-z0-9\\-]/i', $normalizedString)) {
        return null;
      }
      
      $path = ROOT.'cache/tolkien-gateway/'.$normalizedString;
      $fp   = null;
      $html = null;
      
      if ($value !== null) {
        $fp = fopen($path, 'w');
        if (flock($fp, LOCK_EX)) {
          fwrite($fp, $value);
          flock($fp, LOCK_UN);
        }
        
        $html = $value;
      } else if (file_exists($path) && time() - filemtime($path) < 3600) {
        $fp   = fopen($path, 'r');
        $html = '';
        
        if (flock($fp, LOCK_SH)) {
          while (!feof($fp)) {
            $html .= fread($fp, 4096);
          }
          flock($fp, LOCK_UN);
        }
      }
      
      if ($fp != null) {
        fclose($fp);
      }
      
      return $html;
    }
  }
  