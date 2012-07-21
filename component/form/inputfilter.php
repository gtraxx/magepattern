<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 21/07/12
 * Time: 14:37
 *
 */
class form_inputfilter{
    /**
     * Constante for URL format
     * @var void
     */
    const REGEX_URL_FORMAT = '~^(https?|ftps?)://   # protocol
      (([a-z0-9-]+\.)+[a-z]{2,6}              		# a domain name
          |                                   		#  or
        \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}    		# a IP address
      )
      (:[0-9]+)?                              		# a port (optional)
      (/?|/\S+)                               		# a /, nothing or a / with something
    $~ix';
    /**
     * function isEmpty
     *
     * @param string $val
     * @return false
     */
    public static function isEmpty($val,$zero = true){
        $val = trim($val);
        if($zero){
            $value = empty($val) && $val !== 0;
        }else{
            $value =  empty($val);
        }
    }

    /**
     * function isURL
     * is Valide URL
     *
     * @param bool $url
     * @throws Exception
     * @return bool
     */
    public static function isURL($url){
        /*filter_var($url, FILTER_VALIDATE_URL);//FILTER_FLAG_SCHEME_REQUIRED
          return $url;*/
        //String
        $clean = (string) $url;
        if($url != ''){
            if (!preg_match(self::REGEX_URL_FORMAT, $clean)){
                //Generate exception
                throw new Exception('Invalid URL: '.$url);
            }
        }
        return $clean;
    }
    /**
     * function isMail
     *
     * @param bool $mail
     * @return bool
     */
    public static function isMail($mail){
        return filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : false;
    }
    /**
     * Checks if variable of Numeric
     *
     * @param bool $str
     * @return bool
     */
    public static function isNumeric($str){
        return (integer) ctype_digit($str) ? $str : false;
    }
    /**
     * Checks if variable of Float
     *
     * @param bool $str
     * @return bool
     */
    public static function isFloat($str){
        return filter_var($str, FILTER_VALIDATE_FLOAT) ? $str : false;
    }
    /**
     * Checks if variable of Integer
     *
     * @param bool $str
     * @return bool
     */
    public static function isInt($str){
        return filter_var($str,FILTER_VALIDATE_INT) ? $str : false;
    }
    /**
     * Checks if variable of String
     *
     * @param bool $str
     * @return bool
     */
    public static function isAlpha($str){
        return (string) ctype_alpha($str) ? $str : false;
    }
    /**
     * Checks if variable of alphanumeric
     *
     * @param bool $str
     * @return bool
     */
    public static function isAlphaNumeric($str){
        return (string) ctype_alnum($str) ? $str : false;
    }

    /**
     * Function pour vérifier la longueur minimal d'un texte
     *
     * @param $str
     * @param integer $size
     * @internal param string $getPost
     * @return vars
     */
    public static function isMinString($str, $size){
        $small = strlen($str) < $size;
        return $small;
    }

    /**
     * Function pour vérifier la longueur maximal d'un texte
     *
     * @param $str
     * @param integer $size
     * @internal param string $getPost
     * @return vars
     */
    public static function isMaxString($str, $size){
        $largest = strlen($str) > $size;
        return $largest;
    }
}
?>