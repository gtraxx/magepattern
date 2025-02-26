<?php
namespace Magepattern\Component\Tool;

class StringTool
{
    /**
     * Search for one or more substring in a string
     * @param string $haystack
     * @param array $needles
     * @param bool $contains Define the way of searching, true must contain the substring, false must not contain the substring
     * @return bool
     */
    public static function str_search(string $haystack, array $needles, bool $contains = true): bool
    {
        if(empty($needles)) return !$contains;
        foreach ($needles as $needle) {
            if(is_string($needle) && str_contains($haystack,$needle)) return $contains;
        }
        return !$contains;
    }

    /**
     * Constante for URL format
     * @var void
     */
    const REGEX_URL_FORMAT = '~^(https?|ftps?):(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?(/?|/\S+)$~ix';
    /**
     * ~^(https?|ftps?)://                      # protocol
     * (([a-z0-9-]+\.)+[a-z]{2,6}              	# a domain name
     *     |                                   	#  or
     *   \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}    	# a IP address
     * )
     * (:[0-9]+)?                              	# a port (optional)
     * (/?|/\S+)                               	# a /, nothing or a / with something
     * $~ix
     */

    /**
     * @param string $url
     * @return bool
     */
    public static function isURL(string $url): bool
    {
        return (bool)preg_match(self::REGEX_URL_FORMAT, $url);
    }

    /**
     * function isMail
     * @param string $mail
     * @return bool
     */
    public static function isMail(string $mail): bool
    {
        return (bool)filter_var($mail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Checks if variable of String
     * @param string $str
     * @return bool
     */
    public static function isAlpha(string $str){
        return ctype_alpha($str);
    }

    /**
     * Checks if variable of alphanumeric
     * @param string $str
     * @return bool
     */
    public static function isAlphaNumeric(string $str){
        return ctype_alnum($str);
    }

    /**
     * Function pour vérifier la longueur minimal d'un texte
     * @param string $str
     * @param int $size
     * @internal param string $getPost
     * @return bool
     */
    public static function isMinString(string $str, int $size): bool
    {
        return strlen($str) < $size;
    }

    /**
     * Function pour vérifier la longueur maximal d'un texte
     * @param string $str
     * @param int $size
     * @internal param string $getPost
     * @return bool
     */
    public static function isMaxString(string $str, int $size): bool
    {
        return strlen($str) > $size;
    }
    
    /**
     * Join function for get Alpha string
     *
     * @see filter_escapeHtml::trim
     * @see filter_escapeHtml::isAlpha
     * @see filter_escapeHtml::isMaxString
     *
     * @param string $str
     * @param int $lg_max
     * @return string
     */
    public static function isAlphaMax(string $str,int $lg_max): string
    {
        return self::isAlpha(trim($str)) . self::isMaxString($str,$lg_max);
    }

    /**
     * Join function for get Alpha Numéric string
     *
     * @see filter_escapeHtml::trim
     * @see filter_escapeHtml::isAlphaNumeric
     * @see filter_escapeHtml::isMaxString
     *
     * @param string $str
     * @param int $lg_max
     * @return string
     */
    public static function isAlphaNumericMax(string $str,int $lg_max): string
    {
        return self::isAlphaNumeric(trim($str)) . self::isMaxString($str,$lg_max);
    }

    /**
     * Join function for get Intéger
     *
     * @see filter_escapeHtml::trim
     * @see filter_escapeHtml::isNumeric
     * @see filter_escapeHtml::isMaxString
     *
     * @param string $str
     * @param int $lg_max
     * @return string
     */
    public static function isNumericClean(string $str,int $lg_max): string
    {
        return MathTool::isNumeric(trim($str)) . self::isMaxString($str,$lg_max);
    }

    /**
     * Renvoi une chaine en majuscule en tenant compte de l'encodage
     *
     * @param string $str
     * @return string
     */
    public static function strtoupper(string $str): string
    {
        if (function_exists('mb_strtoupper') && function_exists('mb_detect_encoding')) {
            if (mb_detect_encoding($str,"utf-8") == "utf-8") {
                $str = mb_strtoupper($str,'utf-8');
            }
            elseif (mb_detect_encoding($str, "ISO-8859-1")) {
                $str = mb_strtoupper($str, "ISO-8859-1");
            }
            else {
                $str = strtoupper($str);
            }
        }
        else {
            $str = strtoupper($str);
        }
        return $str;
    }

    /**
     * Renvoi une chaine en minuscule en tenant compte de l'encodage
     *
     * @param string $str
     * @return string
     */
    public static function strtolower(string $str): string
    {
        if (function_exists('mb_strtolower') && function_exists('mb_detect_encoding')) {
            if (mb_detect_encoding($str,"UTF-8") == "UTF-8") {
                $str = mb_strtolower($str,'UTF-8');
            }
            elseif(mb_detect_encoding($str, "ISO-8859-1")) {
                $str = mb_strtolower($str,'ISO-8859-1');
            }
            else {
                $str = strtolower($str);
            }
        }
        else {
            $str = strtolower($str);
        }
        return $str;
    }
    
    /**
     * Convert first letters string in Uppercase
     *
     * @param string $str
     * @return string
     */
    public static function ucFirst(string $str): string
    {
        return self::strtoupper(substr($str,0,1)).substr($str,1);
    }
    
    /**
     * truncate string with clean delimiter
     * Tronque une chaîne de caractères sans couper au milieu d'un mot
     * @param string $str
     * @param int $lg_max (length max)
     * @param string $delimiter (delimiter ...)
     * @return string
     */
    public static function truncate(string $str, int $lg_max, string $delimiter)
    {
        if(self::isMaxString($str,$lg_max)){
            $str = substr($str, 0, $lg_max);
            $last_space = strrpos($str, " ");
            $str = substr($str, 0, $last_space).$delimiter;
        }
        return $str;
    }
}