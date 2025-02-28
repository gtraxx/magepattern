<?php
namespace Magepattern\Component\Tool;
use Magepattern\Component\Debug\Logger;

class DateTool
{
    const DATE_EU_FORMAT = 'd/m/Y',
        DATE_DEFAULT_FORMAT = 'Y/m/d',
        DATE_TIMESTAMP_FORMAT = 'U',
        DATE_SQL_FORMAT = 'Y-m-d',
        DATETIME_SQL_FORMAT = 'Y-m-d H:i:s';

    /**
     * Retrieves a formated date.
     *
     * @param  string $timestamp  Timestamp
     * @param  string $type       Format type
     * @return string Formatted date
     */
    public static function getDate(string $timestamp, $type = 'rfc1123'): string
    {
        try {
            if(!in_array($type,[])) throw new \Exception('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime.');

            return match(strtolower($type)) {
                'rfc1123' => substr(gmdate('r', $timestamp), 0, -5).'GMT',
                'rfc1036' => gmdate('l, d-M-y H:i:s ', $timestamp).'GMT',
                'asctime' => gmdate('D M j H:i:s', $timestamp)
            };
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return gmdate('Y-M-D H:i:s');
        }
    }

    /**
     * @param int $y
     * @param int $m
     * @param int $d
     * @return bool
     */
	public static function isValid( int $y, int $m, int $d): bool
    {
		return checkdate( $m, $d, $y ) ;
	}

    /**
     * W3C date Format
     * @param string $str
     * @return bool
     */
    public static function isW3CValid(string $str): bool
    {
        $timestamp = strtotime($str);
        if (!$timestamp) return false;
        $month = date( 'm', $timestamp );
        $day   = date( 'd', $timestamp );
        $year  = date( 'Y', $timestamp );
        return checkdate($month, $day, $year);
    }

    /**
     * @deprectaed Use DateTime::format
     * Transforme une date format SQL
     * @param string $d
     * @param string $separator
     * @return string
     */
    #[Deprectaed] public function dateToDbFormat(string $d, string $separator = '/'){
        if(preg_match( "^\d{4}/\d{1,2}/\d{1,2}$", $d)) {
            list($year, $month, $day) = explode($separator, $d);
        }
        elseif(preg_match( "^\d{1,2}/\d{1,2}/\d{4}$", $d)) {
            list($day, $month, $year) = explode($separator, $d);
        }
        if(empty($year) || empty($month) || empty($day)) return false;
        return "$year-$month-$day";
    }

    /**
     * Find the corresponding TimeZone based on the offset
     * @param int $offset, UTC offset in seconds
     * @return bool|\DateTimeZone
     */
    public static function findTimeZone(int $offset): bool|\DateTimeZone
    {
        $abbr = timezone_name_from_abbr('', $offset, 1);

        if($abbr !== false) {
            $tz = new \DateTimeZone($abbr);
            $transition = $tz->getTransitions(time(),strtotime('+1 year'));

            if($transition[1]['isdst']) $abbr = timezone_name_from_abbr('', $offset, 0);
        }

        if($abbr === false) $abbr = timezone_name_from_abbr('', $offset, -1);

        return $abbr !== false ? new \DateTimeZone($abbr) : false;
    }
}