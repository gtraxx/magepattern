<?php
namespace Magepattern\Component\HTTP;

class Request
{
    /**
     * @param string $method
     * @return bool
     */
    public static function isMethod(string $method):bool
    {
        return $_SERVER['REQUEST_METHOD'] === $method;
    }

    /**
     * @param string $key
     * @param string $glob
     * @return bool
     */
    private static function issetGlobalKey(string $key,string $glob):bool
    {
        $input_type = match($glob) {
            'get' => INPUT_GET,
            'post' => INPUT_POST,
            'server' => INPUT_SERVER,
            'cookie' => INPUT_COOKIE,
            default => null
        };
        if(function_exists('filter_has_var') && $input_type !== null) return filter_has_var($input_type,$key);

        return match($glob) {
            'get' => isset($_GET[$key]),
            'post' => isset($_POST[$key]),
            'request' => isset($_REQUEST[$key]),
            'session' => isset($_SESSION[$key]),
            'server' => isset($_SERVER[$key]),
            'cookie' => isset($_COOKIE[$key])
        };
    }

    /**
     * Checks if variable of GET type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isGet(string $key): bool
    {
        return self::issetGlobalKey($key,'get');
    }

    /**
     * Checks if variable of POST type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isPost(string $key): bool
    {
        return self::issetGlobalKey($key,'post');
    }

    /**
     * Checks if variable of REQUEST type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isRequest(string $key): bool
    {
        return self::issetGlobalKey($key,'request');
    }

    /**
     * Checks if variable of SESSION type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isSession(string $key): bool
    {
        return self::issetGlobalKey($key,'session');
    }

    /**
     * Checks if variable of SERVER type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isServer(string $key): bool
    {
        return self::issetGlobalKey($key,'server');
    }

    /**
     * Checks if variable of COOKIE type exists
     *
     * @param string $key
     * @return bool
     */
    public static function isCookie(string $key): bool
    {
        return self::issetGlobalKey($key,'cookie');
    }
}