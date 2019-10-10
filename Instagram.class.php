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
    $username = str_replace('@','',$username);
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
  static function getNthFoto($username,$nth = 0){
    $username = str_replace('@','',$username);
    $cached = self::get('foto-'.$username);
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
      $re = '/GraphImage.+"shortcode":"(.+)"/mU';
      preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);
      if (isset($matches[$nth]) && isset($matches[$nth][1]))$last = $matches[$nth][1];
    }
    if ($last) self::save('foto-'.$username,$matches);
    return $last;
  }
  static function getNthHashtag($hashtag,$nth = 0){
    $hashtag = str_replace('#', '', $hashtag);
    $cached = self::get('#'.$hashtag);
    if ($cached){
      if (isset($cached[$nth][1])){return $cached[$nth][1];}
    }
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
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
        "accept-encoding: gzip, deflate, br",
        "accept-language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,la;q=0.6",
        "cache-control: no-cache",
        "pragma: no-cache",
        "sec-fetch-mode: navigate",
        "sec-fetch-site: same-origin",
        "sec-fetch-user: ?1",
        "upgrade-insecure-requests: 1",
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
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
    if ($last) self::save('#'.$hashtag,$matches,180);
    return $last;
  }
  static function getNthHashtagFoto($hashtag,$nth = 0){
    $hashtag = str_replace('#', '', $hashtag);
    $cached = self::get('#foto-'.$hashtag);
    if ($cached){
      if (isset($cached[$nth][1])){return $cached[$nth][1];}
    }
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
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
        "accept-encoding: gzip, deflate, br",
        "accept-language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7,la;q=0.6",
        "cache-control: no-cache",
        "pragma: no-cache",
        "sec-fetch-mode: navigate",
        "sec-fetch-site: same-origin",
        "sec-fetch-user: ?1",
        "upgrade-insecure-requests: 1",
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
      ),
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    $last = false;
    if (!$err) {
      $re = '/GraphImage.+"shortcode":"(.+)"/mU';
      preg_match_all($re, $response, $matches, PREG_SET_ORDER, 0);
      if (isset($matches[$nth]) && isset($matches[$nth][1]))$last = $matches[$nth][1];
    }
    if ($last) self::save('#foto-'.$hashtag,$matches,180);
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
