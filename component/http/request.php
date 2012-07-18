<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 17/06/12
 * Time: 22:30
 *
 */
class http_request{
    /**
     * Checks if variable of POST type exists
     *
     * @param bool $str
     * @return bool
     */
    public static function isPost($str){
        if(function_exists('filter_has_var')){
            return filter_has_var(INPUT_POST, $str) ? true : false;
        }else{
            return isset($_POST[$str]) ? true : false;
        }
    }
    /**
     * Checks if variable of GET type exists
     *
     * @param bool $str
     * @return bool
     */
    public static function isGet($str){
        if(function_exists('filter_has_var')){
            return filter_has_var(INPUT_GET, $str) ? true : false;
        }else{
            return isset($_GET[$str]) ? true : false;
        }
    }
    /**
     * Checks if variable of REQUEST type exists
     *
     * @param bool $str
     * @return bool
     */
    public static function isRequest($str){
        if(function_exists('filter_has_var')){
            return filter_has_var(INPUT_REQUEST, $str) ? true : false;
        }else{
            return isset($_REQUEST[$str]) ? true : false;
        }
    }
    /**
     * Checks if variable of SESSION type exists
     *
     * @param bool $str
     * @return bool
     */
    public static function isSession($str){
        return isset($_SESSION[$str]) ? true : false;
    }
    /**
     * Checks if variable of SERVER type exists
     *
     * @param bool $str
     * @return bool
     */
    public static function isServer($str){
        if(function_exists('filter_has_var')){
            return filter_has_var(INPUT_SERVER, $str) ? true : false;
        }else{
            return isset($_SERVER[$str]) ? true : false;
        }
    }
}