<?php
namespace Magepattern\Component\Tool;

class MathTool
{
    /**
     * Checks if variable of Numeric
     * @param mixed $str
     * @return bool|int
     */
    public static function isNumeric(string $str): bool|int
    {
        return self::isInt($str) || self::isFloat($str) ? $str : false;
    }

    /**
     * Checks if variable of Float
     *
     * @param mixed $str
     * @return bool|int
     */
    public static function isFloat(string $str): bool|int
    {
        return filter_var($str, FILTER_VALIDATE_FLOAT) ? $str : false;
    }

    /**
     * Checks if variable of Integer
     *
     * @param bool $str
     * @return bool|int
     */
    public static function isInt(string $str): bool|int
    {
        return filter_var($str,FILTER_VALIDATE_INT) ? $str : false;
    }
}