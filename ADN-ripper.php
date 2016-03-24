<?php
  // original writer https://github.com/K-S-V just updated the constants and start to make it work again.
  if ($argc < 2)
      die("Usage: php AnimeDigital.php <encrypted_subtitles>");
  // Read encrypted subtitles
  $file = file_get_contents($argv[1]);
  $file = base64_decode($file);
  // Get decryption key from aes constants
  $constants = "\x39\x31\x63\x31\x63\x66\x37\x33\x32\x63\x35\x34\x33\x33\x63\x62\x34\x38\x63\x65\x37\x65\x62\x61\x65\x63\x34\x65\x63\x65\x38\x61\x36\x61\x32\x38\x31\x64\x31\x39\x66\x61\x32\x39\x33\x35\x66\x35\x64\x30\x31\x39\x66\x30\x63\x31\x34\x33\x35\x62\x35\x36\x30\x66\x30\x32\x31\x61\x35\x36\x34\x65\x31\x34\x62\x62\x37\x36\x30\x63\x32\x63\x31\x65\x31\x33\x65\x36\x35\x38\x30\x37\x63\x38\x38\x38\x38\x65\x36\x64\x33\x62\x62\x38\x62\x33\x36\x34\x63\x36\x37\x35\x38\x63\x39\x65\x35";
  $start     = 46;
  $key       = substr($constants, $start, 32);
  $salt      = substr($file, 8, 8);
  $key       = $key . $salt;
  $hash1     = md5($key, true);
  $hash2     = md5($hash1 . $key, true);
  $iv        = md5($hash2 . $key, true);
  $key       = $hash1 . $hash2;
  // Decrypt subtitles
  $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
  mcrypt_generic_init($td, $key, $iv);
  $file      = substr($file, 16);
  $decrypted = mdecrypt_generic($td, $file);
  mcrypt_generic_deinit($td);
  mcrypt_module_close($td);
  // Detect and remove PKCS#7 padding
  $padded = true;
  $len    = strlen($decrypted);
  $pad    = ord($decrypted[$len - 1]);
  for ($i = 1; $i <= $pad; $i++)
      $padded &= ($pad == ord(substr($decrypted, -$i, 1))) ? true : false;
  if ($padded)
      $decrypted = substr($decrypted, 0, $len - $pad);
  // Save decrypted subtitles
  $file = pathinfo($argv[1], PATHINFO_FILENAME) . ".ass";
  file_put_contents($file, $decrypted);
?>
