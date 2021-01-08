<?php namespace App\Infrastructure\Utils;

  class CaptchaUtils
  {
    public static function captchaOriginal(): string
    {
  // Generate captcha code
  $random_num    = md5(random_bytes(64));
  $captcha_code  = substr($random_num, 0, 6);

  // Assign captcha in session
  $_SESSION['CAPTCHA_CODE'] = $captcha_code;

  // Create captcha image
  $layer = imagecreatetruecolor(168, 37);
  $captcha_bg = imagecolorallocate($layer, 247, 174, 71);
  imagefill($layer, 0, 0, $captcha_bg);
  $captcha_text_color = imagecolorallocate($layer, 0, 0, 0);
  imagestring($layer, 5, 55, 10, $captcha_code, $captcha_text_color);
  header("Content-type: image/jpeg");
  imagejpeg($layer);
    }
  
    public static function captcha(): \stdClass
    {
    $result = \json_decode('{}');
    
    $val1 = random_int ( 10 , 20 );
    $val2 = random_int ( 1 , 9 );
    $calc = $val1 + $val2;


    $result->{'question'} = $val1 . ' + ' . $val2 . ' = ?';
    $result->{'answer'} = '' . $calc;
    return $result;
    }
  }
?>