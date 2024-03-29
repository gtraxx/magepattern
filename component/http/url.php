<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2013 Gerits Aurelien contact[at]aurelien-gerits[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]aurelien-gerits[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER

# Do not edit or add to this file if you wish to upgrade Mage Pattern to newer
# versions in the future. If you wish to customize Mage Pattern for your
# needs please refer to http://www.magepattern.com for more information.
#
# -- END LICENSE BLOCK -----------------------------------
/**
 * Created by SC BOX.
 * User: aureliengerits
 * Date: 29/07/12
 * Time: 15:54
 *
 */
class http_url {

    /**
     * Remove host in URL
     *
     * Removes host part in URL
     *
     * @param $url
     * @internal param string $str URL to transform
     * @return    string
     */
    public static function stripHostURL($url)
    {
        return preg_replace('|^[a-z]{3,}://.*?(/.*$)|','$1',$url);
    }

    /**
     *
     * @get the full url of page
     *
     * @param bool $requestUri
     * @return string
     */
    public static function getUrl(bool $requestUri = false): string {
        /*** check for https ***/
        $isHttps = isset($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $source = '://';
        $source .= $_SERVER['HTTP_HOST'];

        /*** return the full address ***/
        if($requestUri) $source .= $_SERVER['REQUEST_URI'];

        //$_SERVER["SERVER_NAME"]
        return $isHttps.$source;
    }

    /**
     * @static
     * @return string
     */
    public static function getFiles(){
        return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    }

    /**
     * Converti une chaine en URL valide
     * @static
     * @param string $str
     * @param array $option
     * @return mixed|string
     * @throws Exception
     * @example:
    http_url::clean(
        '/public/test/truc-machin01/aussi/version-1.0/',
        array('dot'=>'display','ampersand'=>'strict','cspec'=>array('[\/]'),'rspec'=>array(''))
        );
     */
    public static function clean(string $str, $option = ['dot'=>false, 'ampersand'=>'none', 'cspec'=>'', 'rspec'=>'']){
        /**Clean accent*/
        /** Clean non Latin characters */
        if(class_exists('Transliterator')){
            $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
            $str = $transliterator->transliterate($str);
        }else{
            $characters = [
                "¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
                "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
                "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
                "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
                "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
                "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
                "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
                "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y"
            ];
            $str = strtr($str, $characters);
        }

        $str = trim($str);

        if(is_array($option)){
            if(array_key_exists('dot', $option) && $option['dot'] == 'none'){
                $str = str_replace('.','',$str);
            }
            if(array_key_exists('ampersand', $option) && is_string($option['ampersand'])){
                /*
                 * PHP 8
                 * $str = match ($option['ampersand']) {
                    'strict' => str_replace('&', '&amp;', $str), // replace & => $amp (w3c convert)
                    'none' => str_replace('&', '', $str), // replace & => ''
                    default => str_replace('&', (is_string($option['ampersand']) ? $option['ampersand'] : '&amp;'), $str), // replace & => $option['ampersand'] value
                };*/
                switch ($option['ampersand']) {
                    case 'strict':
                        // replace & => $amp (w3c convert)
                        $str = str_replace('&', '&amp;', $str);
                        break;
                    case 'none':
                        // replace & => ''
                        $str = str_replace('&amp;', '', $str);
                        $str = str_replace('&', '', $str);
                        break;
                    default:
                        // replace & => $option['ampersand'] value
                        $str = str_replace('&', (is_string($option['ampersand']) ? $option['ampersand'] : '&amp;'), $str);
                }
            }
        }
        elseif(is_bool($option) && $option != false){
            // replace & => $amp (w3c convert)
            $str = str_replace('&','&amp;',$str);
            $str = str_replace('.','',$str);
        }

        // Convert special characters
        $str = filter_escapeHtml::cleanQuote($str);
        //$cSpec = ['@["’|,+\'\\/[:blank:]\s]+@i', '@[?#!:()\[\]{}\@\X$€%ʹ]+@i'];
        $cSpec = ['@["’|,+\'\\/[:blank:]\s]+@i', '@[?#!:()\[\]{}\@\x$€%ʹ]+@i'];
        $rSpec = ['-', ''];

        if(is_array($option)){
            if(array_key_exists('cspec', $option) && is_array($option['cspec']) && !empty($option['cspec'])) $cSpec = array_merge($cSpec,$option['cspec']);
            if(array_key_exists('rspec', $option) && is_array($option['rspec']) && !empty($option['rspec'])) $rSpec = array_merge($rSpec,$option['rspec']);
        }
        $str = preg_replace($cSpec,$rSpec,$str);

        // Convert following '-' to single '-'
        $str = preg_replace("/[-]+/",'-',$str);
        // Removes the indent if end of string
        $str = rtrim($str,"-");
        // Convert UTF8 encode
        $str = filter_htmlEntities::decode_utf8($str);
        // Convert lower case
        $str = filter_string::strtolower($str);
        return $str;
    }

    /**
     * Short Clean for tag or special url
     * @param $str
     * @return string
     */
    public function shortClean($str){
        /**Clean accent*/
        $Caracs = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
            "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
            "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E",
            "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
            "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N",
            "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
            "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U",
            "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
            "à" => "a", "á" => "a", "â" => "a", "ã" => "a",
            "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
            "è" => "e", "é" => "e", "ê" => "e", "ë" => "e",
            "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
            "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o",
            "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
            "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u",
            "ý" => "y", "ÿ" => "y");
        $str = strtr("$str", $Caracs);
        $str = trim($str);
        /* stripcslashes backslash */
        $str = filter_escapeHtml::cleanQuote($str);
        /*replace blank and special caractère*/
        $cSpec = array("@'@i",'[\?]','[\#]','[\@]','[\,]','[\!]','[\:]','[\(]','[\)]');
        $rSpec = array (" "," "," "," "," "," "," "," "," ");
        /*Removes the indent if end of string*/
        $str = rtrim(preg_replace($cSpec,$rSpec,$str),"");
        /*Convert UTF8 encode*/
        $str = filter_htmlEntities::decode_utf8($str);
        /*Convert lower case*/
        $str = filter_string::strtolower($str);
        return $str;
    }
    /**
     * @return string
     */
    public static function currentUri(){
        return self::getUrl(true);
    }

    /**
     * @return string
     */
    public static function getUri()
    {
        $uri = trim(self::getUrl(true));

        // absolute URL?
        if (0 === strpos($uri, 'http')) {
            return $uri;
        }

        // empty URI
        if (!$uri) {
            return self::currentUri;
        }

        // only an anchor
        if ('#' === $uri[0]) {
            $baseUri = self::currentUri;
            if (false !== $pos = strpos($baseUri, '#')) {
                $baseUri = substr($baseUri, 0, $pos);
            }

            return $baseUri.$uri;
        }

        // only a query string
        if ('?' === $uri[0]) {
            $baseUri = self::currentUri;

            // remove the query string from the current uri
            if (false !== $pos = strpos($baseUri, '?')) {
                $baseUri = substr($baseUri, 0, $pos);
            }

            return $baseUri.$uri;
        }

        // absolute path
        if ('/' === $uri[0]) {
            return preg_replace('#^(.*?//[^/]+)(?:\/.*)?$#', '$1', self::currentUri).$uri;
        }

        // relative path
        return substr(self::currentUri, 0, strrpos(self::currentUri, '/') + 1).$uri;
    }
}