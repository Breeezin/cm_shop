<?php
class SecurityEPG
{

  public static function encrypt ($input, $key)
  {
	$data = openssl_encrypt($input, 'aes-256-ecb', $key, OPENSSL_RAW_DATA);
    $data = base64_encode ($data);
    return $data;
  }

  public static function decrypt ($sStr, $sKey)
  {
    $decrypted = openssl_decrypt(base64_decode ($sStr), 'aes-256-ecb', $sKey, OPENSSL_RAW_DATA );
    $dec_s = strlen ($decrypted);
    $padding = ord ($decrypted[$dec_s - 1]);
    $decrypted = substr ($decrypted, 0, -$padding);
    return $decrypted;
  }

}

?>
