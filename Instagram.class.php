<?php
class Instagram{
  private static $cache_dir = './tmp/cache';
  static function get($id){
    $file_name = self::getFileName($id);
    if (!is_file($file_name) || !is_readable($file_name)) {
      return false;
    }
    $lines    = file($file_name);
    $lifetime = array_shift($lines);
    $lifetime = (int) trim($lifetime);
    if ($lifetime !== 0 && $lifetime < time()) {
      @unlink($file_name);
      return false;
    }
    $serialized = join('', $lines);
    $data       = json_decode($serialized,true);
    return $data;
  }
  static function delete($id){
    $file_name = self::getFileName($id);
    return unlink($file_name);
  }
  static function save($id, $data, $lifetime = 43200 /*12 hours*/){
    $dir = self::getDirectory($id);
    if (!is_dir($dir)) {
      if (!mkdir($dir, 0755, true)) {
        return false;
      }
    }
    $file_name  = self::getFileName($id);
    $lifetime   = time() + $lifetime;
    $serialized = json_encode($data);
    $result     = file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);
    if ($result === false) {
      return false;
    }
    return true;
  }
  static function getDirectory($id){
    $hash = sha1($id, false);
    $dirs = array(
      self::getCacheDirectory(),
      substr($hash, 0, 2),
      substr($hash, 2, 2)
    );
    return join(DIRECTORY_SEPARATOR, $dirs);
  }
  static function getCacheDirectory(){
    return self::$cache_dir;
  }
  static function getFileName($id){
    $directory = self::getDirectory($id);
    $hash      = sha1($id, false);
    $file      = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
    return $file;
  }
  static function getLast($username){
    return self::getNth($username,0);
  }
  static function getNth($username,$nth = 0){
    $cached = self::get($username);
    if ($cached){
      if (isset($cached[$nth][1])){return $cached[$nth][1];}
    }
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.instagram.com/".$username."/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    $last = false;
    if (!$err) {
      $re = '/"shortcode":"(.+)"/mU';
      preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);
      if (isset($matches[$nth]) && isset($matches[$nth][1]))$last = $matches[$nth][1];
    }
    if ($last) self::save($username,$matches);
    return $last;
  }
  static function getNthHashtag($hashtag,$nth = 0){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.instagram.com/explore/tags/".$hashtag."/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    $last = false;
    if (!$err) {
      $re = '/"shortcode":"(.+)"/mU';
      preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);
      if (isset($matches[$nth]) && isset($matches[$nth][1]))$last = $matches[$nth][1];
    }
    return $last;
  }
  static function getPicture($id){
    $cached = self::get('picture~'.$id);
    if ($cached){
      return $cached[0][1];
    }
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.instagram.com/p/".$id."/embed/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_POSTFIELDS => "",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    $last = false;
    if (!$err) {
      $re = '/img class="EmbeddedMediaImage" src="(.+)"/mU';
      preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);
      if (isset($matches[0]) && isset($matches[0][1]))$last = $matches[0][1];
    }
    if ($last) self::save('picture~'.$id,$matches);
    return $last;
  }
}
