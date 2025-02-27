<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Tool\EscapeTool,
    Magepattern\Component\Tool\StringTool,
    Magepattern\Component\Debug\Logger;

class Url
{
    /**
     * @var array $chararcters
     */
    private static array $chararcters = ["¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
        "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
        "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
        "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
        "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
        "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
        "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
        "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y"];

    /**
     * Remove host in URL
     * Removes host part in URL
     * @param string $url
     * @internal param string $str URL to transform
     * @return string
     */
    public static function stripHostURL(string $url): string
    {
        return preg_replace('|^[a-z]{3,}://.*?(/.*$)|','$1',$url);
    }

    /**
     * @get the full url of page
     * @param bool $requestUri
     * @return string
     */
    public static function getUrl(bool $requestUri = false): string
    {
        /*** check for https ***/
        $protocol = isset($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $source = '://'.$_SERVER['HTTP_HOST'];
        if($requestUri){
            /*** return the full address ***/
            $source .= $_SERVER['REQUEST_URI'];
        }
        return $protocol.$source;
    }

    /**
     * @return string
     */
    public static function getFiles(): string
    {
        return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    }

    /**
     * Converti une chaine en URL valide
     * @static
     * @param string $str
     * @param array $option
     * @return string
     * @example:
    URL::clean(
    '/public/test/truc-machin01/aussi/version-1.0/',
    array('dot'=>'display','ampersand'=>'strict','cspec'=>array('[\/]'),'rspec'=>array(''))
    );
     */
    public static function clean(string $str, array $option = []) : string
    {
        $default = ['dot' => 'none', 'ampersand' => 'none', 'cspec' => [], 'rspec' => []];
        $option = array_merge($default, $option);

        // Validation des options
        /*if (!is_array($option)) {
            //echo "Validation failed: options is not an array\n";
            return ''; // Ou lancez une exception
        }*/
        if (isset($option['dot']) && !is_bool($option['dot']) && !is_string($option['dot'])) {
            //echo "Validation failed: dot is not a boolean or string\n";
            return ''; // Ou lancez une exception
        }
        if (isset($option['ampersand']) && !is_string($option['ampersand'])) {
            //echo "Validation failed: ampersand is not a string\n";
            return ''; // Ou lancez une exception
        }
        if (isset($option['cspec']) && !is_array($option['cspec'])) {
            //echo "Validation failed: cspec is not an array\n";
            return ''; // Ou lancez une exception
        }
        if (isset($option['rspec']) && !is_array($option['rspec'])) {
            //echo "Validation failed: rspec is not an array\n";
            return ''; // Ou lancez une exception
        }
        //echo "Après Options: ";
        //var_dump($str);
        // Gestion des accents et des caractères non latins
        if (class_exists('Transliterator')) {
            try {
                $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
                $str = $transliterator->transliterate($str);
            } catch (\IntlException $e) {
                // Gestion spécifique des exceptions IntlException
                $str = strtr($str, self::$chararcters);
                Logger::getInstance()->log("Transliterator (IntlException): " . $e->getMessage(), 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            } catch (\Exception $e) {
                // Gestion des autres exceptions
                $str = strtr($str, self::$chararcters);
                Logger::getInstance()->log("Transliterator (Exception): " . $e->getMessage(), 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            }
        } else {
            $str = strtr($str, self::$chararcters);
        }

        $str = trim($str);

        if(!empty($option)){
            if(array_key_exists('dot', $option) && $option['dot'] == 'none') {
                $str = str_replace('.','',$str);
            }
            if (array_key_exists('ampersand', $option) && is_string($option['ampersand'])) {
                $str = match ($option['ampersand']) {
                    'strict' => str_replace('&', '&amp;', $str), //htmlspecialchars($str, ENT_QUOTES | ENT_HTML5),// replace & => $amp (w3c convert)
                    'none' => str_replace('&', '', $str), // replace & => ''
                    default => str_replace('&', $option['ampersand'], $str) // replace & => $option['ampersand'] value
                };
            }
        }
        else {
            // replace & => $amp (w3c convert)
            $str = str_replace('&','&amp;',$str);
            $str = str_replace('.','',$str);
        }

        // Convert special characters
        $str = EscapeTool::cleanQuote($str);

        $cSpec = ['@["’|,+\'\\/[:blank:]\s]+@i', '@[?#!:()\\[\\]{}\@$%]+@i'];//['@["’|,+\'\\/[:blank:]\s]+@i', '@[?#!:()\\[\\]{}\@.$%]+@i']
        $rSpec = ['-', ''];

        if (is_array($option)) {
            if (array_key_exists('cspec', $option) && is_array($option['cspec']) && !empty($option['cspec'])) $cSpec = array_merge($cSpec, $option['cspec']);
            if (array_key_exists('rspec', $option) && is_array($option['rspec']) && !empty($option['rspec'])) $rSpec = array_merge($rSpec, $option['rspec']);
        }

        if (preg_replace($cSpec, $rSpec, $str) === NULL) {
            // Gestion de l'erreur
            Logger::getInstance()->log("preg_replace error: " . preg_last_error(), 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            $str = ""; // Ou une autre valeur par défaut
        }
        $str = preg_replace($cSpec, $rSpec, $str);
        $str = preg_replace("/[-]+/", '-', $str);
        $str = rtrim($str, "-");
        $str = EscapeTool::decode_utf8($str);

        return StringTool::strtolower($str);
    }

    /**
     * Short Clean for tag or special url
     * @param string $str
     * @return string
     */
    public static function shortClean(string $str): string
    {
        $str = strtr("$str", self::$chararcters);
        $str = trim($str);
        /* stripcslashes backslash */
        $str = EscapeTool::cleanQuote($str);
        /* replace blank and special caractère */
        $cSpec = ["@'@i",'[\?]','[\#]','[\@]','[\,]','[\!]','[\:]','[\(]','[\)]'];
        $rSpec = [" "," "," "," "," "," "," "," "," "];
        /* Removes the indent if end of string */
        $str = rtrim(preg_replace($cSpec,$rSpec,$str),"");
        /* Convert UTF8 encode */
        $str = EscapeTool::decode_utf8($str);
        /* Convert lower case */
        return StringTool::strtolower($str);
    }

    /**
     * @deprecated Use getUrl(true) instead
     * @return string
     */
    #[Deprecated] public static function currentUri(): string
    {
        return self::getUrl(true);
    }

    /**
     * @return string
     */
    public static function getUri(): string
    {
        $baseUri = self::getUrl(true);
        $uri = trim($baseUri);

        // absolute URL?
        if (str_starts_with($uri, 'http')) return $uri;

        // empty URI
        if (!$uri) return $baseUri;

        // only an anchor
        if ('#' === $uri[0] || '?' === $uri[0]) {
            // remove the query string from the current uri
            if (false !== $pos = strpos($baseUri, $uri[0])) $baseUri = substr($baseUri, 0, $pos);
            return $baseUri.$uri;
        }

        // absolute path
        if ('/' === $uri[0]) return preg_replace('#^(.*?//[^/]+)(?:/.*)?$#', '$1', $baseUri).$uri;

        // relative path
        return substr($baseUri, 0, strrpos($baseUri, '/') + 1).$uri;
    }
}