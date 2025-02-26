<?php
namespace Magepattern\Component\Tool;
use Magepattern\Component\Debug\Logger,
    Magepattern\Component\HTTP\Url;

class FormTool
{
    /**
     * Combine function trim and escapeHTML for input
     *
     * @param string $str
     * @return string
     */
    public static function simpleClean(string $str): string
    {
        return trim(HTMLTool::escapeHTML($str));
    }

    /**
     * Combine function trim and Extreme escapeHTML for input
     *
     * @param string $str
     * @return string
     */
    public static function extremeClean(string $str): string
    {
        return trim(HTMLTool::escapeExtremeHTML($str));
    }

    /**
     * Combine function trim and strip_tag for input
     *
     * @param string $str
     * @return string
     */
    public static function tagClean(string $str): string
    {
        return trim(EscapeTool::clean($str));
    }

    /**
     * Conbine function trim and rplMagixString
     *
     * @param string $str
     * @return string
     */
    public static function rewriteUrl(string $str): string
    {
        return trim(Url::clean($str));
    }

    /**
     * Conbine function trim and Clean Quote
     *
     * @param string $str
     * @return string
     */
    public static function cleanQuote(string $str): string
    {
        return trim(EscapeTool::cleanQuote($str));
    }

    /**
     * Combine function trim and escapeHTML and downTextCase for input
     *
     * @param string $str
     * @return string
     */
    public static function cleanStrtolower(string $str): string
    {
        return trim(HTMLTool::escapeHTML(StringTool::strtolower($str)));
    }

    /**
     * Combine function trimText and cleanTruncate for input
     * @param string $str
     * @param int $lg_max
     * @param string $delimiter
     * @return string
     */
    public static function truncateClean(string $str,int $lg_max,string $delimiter): string
    {
        return trim(StringTool::truncate($str,$lg_max,$delimiter));
    }

    /**
     * Combine function trimText and isPostAlphaNumeric for input
     * @param string $str
     *
     * @return string
     */
    public static function alphaNumeric(string $str): string
    {
        return trim(StringTool::isAlphaNumeric($str));
    }

    /**
     * Combine function trimText and isPostNumeric for input
     * @param string $str
     *
     * @return string
     */
    public static function numeric(string $str): string
    {
        return trim(MathTool::isNumeric($str));
    }

    /**
     * Special function for clean array
     *
     * @param array $array
     * @return array
     */
    public static function arrayClean(array $array): array
    {
        foreach($array as $key => &$val) {
            if (!is_array($val)) {
                if(empty($val)) {
                    $val = null;
                }
                else {
                    $val = str_contains('content', $key) ? self::cleanQuote($val) : self::simpleClean($val);
                }
            }
            else {
                $val = self::arrayClean($val);
            }
        }
        return $array;
    }

    /**
     * Special function for extreme clean array
     *
     * @param array $array
     * @return array
     */
    public static function arrayExtremeClean(array $array): array
    {
        foreach($array as $key => $val) {
            if (!is_array($val)) {
                $array[$key] = self::extremeClean($val);
            }
            else{
                $array[$key] = self::arrayClean($val);
            }
        }
        return $array;
    }

    /**
     * @param string $str
     * @param string $type
     * @param string $flag
     * @return string
     */
    public static function sanitize(string $str, string $type, string $flag = ''): string
    {
        try {
            if(!in_array($type,[])) throw new \Exception('Type of string not passed to sanitizer',E_WARNING);

            $filter = match($type) {
                'mail' => FILTER_SANITIZE_EMAIL,
                'url' => FILTER_SANITIZE_URL,
                'numeric' => FILTER_SANITIZE_NUMBER_INT,
                'float' => FILTER_SANITIZE_NUMBER_FLOAT
            };
            $option = 0;
            if($type === 'float' && $flag !== ''){
                $option = match($flag) {
                    'fraction' => FILTER_FLAG_ALLOW_FRACTION,
                    'thousand' => FILTER_FLAG_ALLOW_THOUSAND,
                    'scientific' => FILTER_FLAG_ALLOW_SCIENTIFIC
                };
            }
            return filter_var($str, $filter, $option);
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return $str;
        }
    }
}