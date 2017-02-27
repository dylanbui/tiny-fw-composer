<?php

// Don't redefine the functions if included multiple times.
//if (!function_exists('GuzzleHttp\Psr7\str')) {
//    require __DIR__ . '/functions.php';
//}

if (!function_exists('redirect')) {
    function redirect($uri = '', $method = 'location', $http_response_code = 302)
    {
        if (!preg_match('#^https?://#i', $uri)) {
            $uri = site_url($uri);
        }

        switch ($method) {
            case 'refresh'  :
                header("Refresh:0;url=" . $uri);
                break;
            default         :
                header("Location: " . $uri, TRUE, $http_response_code);
                break;
        }
        exit;
    }
}

if (!function_exists('current_site_url')) {
    function current_site_url($uri = '')
    {
//        $pageURL = 'http';
        $pageURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
        return $pageURL . site_url($uri);
    }
}

if (!function_exists('site_url')) {
    function site_url($uri = '')
    {
        static $_site_url = null;
        if(is_null($_site_url))
        {
            $tmp = str_replace('public_html/', '', $_SERVER['SCRIPT_NAME']);
            $_site_url = str_replace(basename($tmp), '', $tmp);
        }
        return $_site_url.ltrim($uri, '/');
    }
}

if (!function_exists('site_path')) {
    function site_path($dir = '')
    {
        static $_site_path = null;
        if(is_null($_site_path))
        {
            if (preg_match('#^(.+)/vendor/(.+)$#', __FILE__, $matches)) {
                $_site_path = $matches[1];
            }
        }
        return $_site_path.'/'.ltrim($dir, '/');
    }
}

if (!function_exists('public_html')) {
    function public_html($uri_file = '')
    {
        return site_url($uri_file);
    }
}

if (!function_exists('df')) {
// Check varible existed or not
    function df(&$value, $default = "")
    {
        return empty($value) ? $default : $value;
    }
}

if (!function_exists('h')) {
    function h(&$str)
    {
        return isset($str) ? htmlspecialchars($str) : '';
//      return isset($str) ? nl2br(htmlspecialchars_decode($str)) : '';
        // Chu y : Khi su dung PDO thi no tu dong encode html khi insert, ke ca textarea cung bi thay the \n = <br/>
//      return isset($str) ? nl2br(htmlspecialchars($str)) : '';
//      return empty($str) ? '' : nl2br(htmlspecialchars($str));
    }
}

if (!function_exists('xh')) {
    function xh(&$str)
    {
        //      return isset($str) ? $str : '';
        //      return isset($str) ? nl2br(htmlspecialchars_decode($str)) : '';
        // Chu y : Khi su dung PDO thi no tu dong encode html khi insert, ke ca textarea cung bi thay the \n = <br/>
        //      return isset($str) ? nl2br(htmlspecialchars($str)) : '';
        return empty($str) ? '' : nl2br(htmlspecialchars($str));
    }
}

if (!function_exists('n')) {
    function n(&$str, $decimals = 0)
    {
        return isset($str) ? number_format($str, $decimals, '.', ',') : '';
//         return empty($str) ? '' : nl2br(htmlspecialchars($str));
    }
}

if (!function_exists('html')) {
// Show data html from database
    function html(&$str)
    {
        return empty($str) ? '' : htmlspecialchars_decode($str);
    }
}

if (!function_exists('now_to_mysql')) {
    function now_to_mysql()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('mysql_to_fulldate')) {
    function mysql_to_fulldate($date)
    {
        if (empty($date) || $date == '0000-00-00 00:00:00')
            return '';
        return date("Y-m-d H:i:s", strtotime($date));
    }
}

if (!function_exists('mysql_to_unix_timestamp')) {
// -- Ex : mysql_to_unix_timestamp('2016-11-20 00:00:00'); => Phai dung dinh dang--
    function mysql_to_unix_timestamp($date)
    {
        if (empty($date) || $date == '0000-00-00 00:00:00')
            return '';
        return strtotime($date);
    }
}

if (!function_exists('convert_unix_timestamp_to_datetime')) {
// -- Tutorial : http://php.net/manual/en/class.datetime.php --
    function convert_unix_timestamp_to_datetime($unixtimestamp, $str_format = 'd/m/Y H:i:s')
    {
//    $myDateTime = new \DateTime();
//    $myDateTime->setTimestamp($unixtimestamp);
//    return  $myDateTime->format($str_format);

        // -- Su dung bang ham --
        // date_create() <==> new \DateTime()
        $myDateTime = date_timestamp_set(date_create(), $unixtimestamp);
        return date_format($myDateTime, $str_format);
    }
}

if (!function_exists('convert_string_to_mysql_datetime')) {
    function convert_string_to_mysql_datetime($str_date, $str_format = 'd/m/Y H:i:s')
    {
        // PHP 5.3 and up
//    $myDateTime = \DateTime::createFromFormat($str_format, $str_date);
//    // -- If dont have H:i:s, it auto give a value 12:00:00  --
//    return  $myDateTime->format('Y-m-d H:i:s');
        // -- Ex --
//    $date = \DateTime::createFromFormat('d/m/Y H:i:s', "24/04/2012 20:44:50");
//    echo $date->format('Y-m-d H:i:s');

        // -- Su dung bang ham --
        $myDateTime = date_create_from_format($str_format, $str_date);
        return date_format($myDateTime, 'Y-m-d H:i:s');
    }
}

if (!function_exists('convert_string_to_unix_timestamp')) {
// -- Ex : convert_string_to_unix_timestamp("20/11/2016 00:00:00", "d/m/Y H:i:s"); --
// -- Chu dong truyen vao gia tri format --
    function convert_string_to_unix_timestamp($str_date, $str_format = 'd/m/Y H:i:s')
    {
        // PHP 5.3 and up
//    $myDateTime = \DateTime::createFromFormat($str_format, $str_date);
//    return $myDateTime->getTimestamp();

        // -- Su dung bang ham --
        $myDateTime = date_create_from_format($str_format, $str_date);
        return date_timestamp_get($myDateTime);
    }
}

if (!function_exists('convert_string_datetime_from_format_to_format')) {
    function convert_string_datetime_from_format_to_format($str_date, $from_format = 'd/m/Y H:i:s', $to_format = 'Y-m-d H:i:s')
    {
        // -- Su dung bang ham --
        $myDateTime = date_create_from_format($from_format, $str_date);
        return date_format($myDateTime, $to_format);
    }
}

if (!function_exists('real_escape_string')) {
    function real_escape_string($str)
    {
        return addslashes($str);
    }
}

if (!function_exists('encryption')) {
    /**
     * Create a encryption string
     * @param $string
     * @param $salt
     * @return string
     */
    function encryption($string, $salt = "")
    {
        return sha1($salt . $string);
    }
}

if (!function_exists('token')) {
    /**
     * Create a fairly random 32 character MD5 token
     *
     * @return string
     */
    function token()
    {
        return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
    }
}

if (!function_exists('base64_url_encode')) {
    /**
     * Encode a string so it is safe to pass through the URI
     * @param string $string
     * @return string
     */
    function base64_url_encode($string = NULL)
    {
        return strtr(base64_encode($string), '+/=', '-_~');
    }
}

if (!function_exists('base64_url_decode')) {
    /**
     * Decode a string passed through the URI
     *
     * @param string $string
     * @return string
     */
    function base64_url_decode($string = NULL)
    {
        return base64_decode(strtr($string, '-_~', '+/='));
    }
}

if (!function_exists('create_uniqid')) {
//  http://phpgoogle.blogspot.com/2007/08/four-ways-to-generate-unique-id-by-php.html
//  http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/
    function create_uniqid($random_id_length = 10)
    {
        //generate a random id encrypt it and store it in $rnd_id
        $rnd_id = crypt(uniqid(rand(), 1), 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        //to remove any slashes that might have come
        $rnd_id = strip_tags(stripslashes($rnd_id));

        //Removing any . or / and reversing the string
        $rnd_id = str_replace(".", "", $rnd_id);
        $rnd_id = strrev(str_replace("/", "", $rnd_id));

        //finally I take the first 10 characters from the $rnd_id
        $rnd_id = substr($rnd_id, 0, $random_id_length);

        return $rnd_id;
    }
}

if (!function_exists('is_ajax_request')) {
    //Check to see if it is an ajax request
    function is_ajax_request()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return TRUE;
        }
        return FALSE;
    }
}

if (!function_exists('ip_address')) {
    function ip_address()
    {
        static $ip = FALSE;

        if ($ip) {
            return $ip;
        }
        //Get IP address - if proxy lets get the REAL IP address

        if (!empty($_SERVER['REMOTE_ADDR']) AND !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = '0.0.0.0';
        }

        //Clean the IP and return it
        return $ip = preg_replace('/[^0-9\.]+/', '', $ip);
    }
}

if (!function_exists('is_php')) {
    /**
     * Determines if the current version of PHP is greater then the supplied value
     *
     * Since there are a few places where we conditionally test for PHP > 5
     * we'll set a static variable.
     *
     * @access    public
     * @param    string
     * @return    bool
     */
    function is_php($version = '5.0.0')
    {
        static $_is_php;
        $version = (string)$version;

        if (!isset($_is_php[$version])) {
            $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
        }

        return $_is_php[$version];
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt (but does not authenticate) a message
     *
     * @param string $message - plaintext message
     * @param string $secret_key - encryption key
     * @param string $secret_iv - encryption hash
     * @param boolean $encode - set to TRUE to return a base64-encoded
     * @return string (raw binary)
     */
    function encrypt($message, $secret_key, $secret_iv = 'none', $encode = false)
    {
        $encrypt_method = "AES-256-CBC";
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        // -- encrypt --
        $output = openssl_encrypt($message, $encrypt_method, $key, 0, $iv);

        if ($encode) {
            return base64_encode($output);
        }

        return $output;
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt (but does not verify) a message
     *
     * @param string $message - ciphertext message
     * @param string $secret_key - encryption key
     * @param string $secret_iv - encryption hash
     * @param boolean $encode - set to TRUE to return a base64-encoded
     * @return string (raw binary)
     * @throws Exception
     */
    function decrypt($message, $secret_key, $secret_iv = 'none', $encode = false)
    {
        if ($encode) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $encrypt_method = "AES-256-CBC";
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        // -- decrypt --
        $output = openssl_decrypt($message, $encrypt_method, $key, 0, $iv);

        return $output;
    }
}

// -- Function deprecated from php 7.1 --

//if (!function_exists('encrypt')) {
//    // -- Ma hoa 1 chuoi , can thu vien phpXX-mcrypt --
//    function encrypt($string, $unlocker, $salt = '')
//    {
//        try {
//            $key = hash('SHA256', $salt . $unlocker, true);
//            srand();
//            $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
//            if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) {
//                return false;
//            }
//            $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string . md5($string), MCRYPT_MODE_CBC, $iv));
//            return $iv_base64 . $encrypted;
//        } catch (Exception $e) {
//            return false;
//        }
//    }
//}
//
//if (!function_exists('decrypt')) {
//    // -- Giai ma 1 chuoi , can thu vien phpXX-mcrypt --
//    function decrypt($string, $unlocker, $salt = '')
//    {
//        try {
//            $key = hash('SHA256', $salt . $unlocker, true);
//            $iv = base64_decode(substr($string, 0, 22) . '==');
//            $string = substr($string, 22);
//            $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($string), MCRYPT_MODE_CBC, $iv), "\0\4");
//            $hash = substr($decrypted, -32);
//            $decrypted = substr($decrypted, 0, -32);
//            if (md5($decrypted) != $hash) {
//                return false;
//            }
//            return $decrypted;
//        } catch (Exception $e) {
//            return false;
//        }
//    }
//}

function tinyfw_url()
{
    if (preg_match('#^(.+)/vendor/(.+)$#', __FILE__, $matches)) {

        echo "<pre>";
        print_r($matches);
        echo "</pre>";
        exit();

    }

//    define ('__SITE_URL', str_replace(basename($tmp), '', $tmp));
}

