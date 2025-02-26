<?php
namespace Magepattern\Component\Date;
use Magepattern\Component\Debug\Logger;

class DateInterval extends \DateInterval
{
    /**
     * @param string $interval
     * @param string $type
     * @return \DateInterval|false
     */
    public function getDateInterval(string $interval, string $type = 'string'): \DateInterval|false
    {
        try {
            return match($type) {
                'string' => \DateInterval::createFromDateString($interval),
                'object' => new \DateInterval($interval),
            };
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
        }
        return false;
    }
}