<?php
namespace Magepattern\Component\Date;
use Magepattern\Component\Debug\Logger;

class DateTime extends \DateTime
{
    /**
     * @param string $format
     * @return string
     */
    public function toInterval($format = 'YMD'): string
    {
        $interval = 'P';
        $format = explode('',$format);
        foreach ($format as $char) {
            $interval .= $this->format($char) ?? '';
        }
        return $interval;
    }

    /**
     * @param string $time
     * @return false|string
     */
    public function diffStatus(string $time): false|string
    {
        try {
            $interval = $this->diff(new DateTime($time));
            return  $interval > 0 ? 'current' : ($interval == 0 ? 'lastday' : 'expired');
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }


    /**
     * @deprecated Use new MpDate
     * création de l'objet datetime
     * @param string $time
     * @param null|\DateTimeZone $timezone
     * @return \DateTime
     */
    #[Deprecated] private function create($time = "now", null|\DateTimeZone $timezone = NULL)
    {
        return parent($time, $timezone);
    }

    /**
     * @deprecated Use new MpDate
     * Instance la classe DateTime
     * @param  $time
     * @return \DateTime
     * @throws Exception
     */
    #[Deprecated] private function _datetime($time){
        try {
            $datetime = self::create($time);
            if($datetime instanceof DateTime)
                return $datetime;
            else
                throw new \Exception('not instantiate the class: DateTime');
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
    }

    /**
     * @deprecated Use date_create_from_format
     * @param string $format
     * @param string $time
     * @return string
     */
    #[Deprecated] public function dateDefine($format='Y-m-d',$time=null)
    {
        try {
            return $this->_datetime($time)->format($format);
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
    }

    /**
     * @deprecated Use date_create_from_format
     * @access public
     * Retourne la date au format européen avec slash (2000/01/01)
     * @param null $time
     * @return string
     * @throws Exception
     * @internal param \timestamp $date
     * @example
     * $datecreate = new date_dateformat();
     * echo $datecreate->dateToEuropeanFormat('01-01-2000');
     */
    #[Deprecated] public function dateToEuropeanFormat($time=null){
        return $this->dateDefine('d/m/Y',$time);
    }

    /**
     * @deprecated Use date_create_from_format
     * @access public
     * Retourne la date au format classique avec slash (2000/01/01)
     * @param null $time
     * @return string
     * @internal param \timestamp $date
     * @example
     * $datecreate = new date_dateformat();
     * echo $datecreate->dateToDefaultFormat('2000-01-01');
     */
    #[Deprecated] public function dateToDefaultFormat($time=null){
        return $this->dateDefine('Y/m/d',$time);
    }

    /**
     * @deprecated Use date_create_from_format or DateTime::format instead
     * @access public
     * Retourne la date au format W3C
     * $datecreate = new date_dateformat();
     * echo $datecreate->dateW3C('2005-08-15');
     * 2005-08-15T15:52:01+00:00
     * @param null $time
     * @return string
     * @throws Exception
     */
    #[Deprecated] public function dateW3C($time=null){
        return $this->dateDefine(DATE_W3C,$time);
    }

    /**
     * @deprecated Use date_create_from_format or DateTime::format instead
     * @access public
     * Retourne le timestamp au format unix
     * @param null $time
     * @return int|string
     * @throws Exception
     */
    #[Deprecated] public function getTimestamp($time=null){
        return $this->dateDefine("U",$time);
    }

    /**
     * @deprecated Use date_create_from_format or DateTime::format instead
     * @access public
     * Retourne la date au format SQL
     * @param null $time
     * @return string
     * @throws Exception
     */
    #[Deprecated] public function SQLDate($time=null){
        return $this->dateDefine("Y-m-d",$time);
    }

    /**
     * @deprecated Use date_create_from_format or DateTime::format instead
     * @access public
     * Retourne la date et l'heure au format SQL
     * @param null $time
     * @return string
     * @throws Exception
     */
    #[Deprecated] public function SQLDateTime($time=null){
        return $this->dateDefine("Y-m-d H:i:s",$time);
    }

    /**
     * @deprecated
     * @access public
     * Retourne la différence entre deux dates
     * @param $time1
     * @param $time2
     * @param string $return_f
     * @return mixed
     * @throws Exception
     * @internal param string $dateTime
     * @throws Exception
     */
    #[Deprecated] public function dateDiff($time1,$time2,$return_f = '%R%a'){
        $datetime1 = $this->_datetime($time1);
        $datetime2 = $this->_datetime($time2);
        $interval = $datetime1->diff($datetime2, false);
        return $interval->format($return_f);
    }

    /**
     * @deprecated
     * @access public
     * Retourne la date Modifiée
     * @param string $modify
     * @param string $format
     * @param null $time
     * @throws Exception
     * @return \DateTime|string DateTime
     * @example :
    $dateformat = new date_dateformat();
    $dateformat->ovrModify('+1 day',"Y-m-d H:i:s");
     */
    #[Deprecated] public function ovrModify($modify,$format='Y-m-d',$time=null)
    {
        if($modify != null){
            $datetime = $this->_datetime($time);
            $datetime->modify($modify);
            return $datetime->format($format);
        }else{
            throw new Exception('dateformat params modify is null');
        }
    }

    /**
     * Ajoute une durée à un objet DateTime
     * @param array|\DateInterval $interval_spec
     * @param string $format
     * @param null $time
     * @throws Exception
     * @return \DateTime|string DateTime
     * @example :
    Utilisation avec object dateTime
    $dateformat->ovrAdd(
    array('interval'=>'P10D','type'=>'object'),
    'Y-m-d H:i:s',
    '2009-10-13'
    );
    Utilisation avec chaine pour la durée
    $dateformat->ovrAdd(
    array('interval'=>'+1 day','type'=>'string'),
    'Y-m-d H:i:s',
    '2009-10-13'
    );
     */
    #[Deprecated] public function ovrAdd(string $interval, string $type, $format='Y-m-d', $time=null){
            $datetime = $this->_datetime($time);
            $dateInterval = new DateInterval();
            $datetime->add($dateInterval->getDateInterval($interval, $type));
            self::add($time);
            DateTime::add($time);
            $DT = new DateTime();
            $DT->add($time);

            return $datetime->format($format);
    }

    /**
     * Soustrait la durée spécifiée par l'objet DateInterval de l'objet DateTime.
     * Substract
     *
     * @param string $interval
     * @param string $type
     * @param string $format
     * @param null $time
     * @throws Exception
     * @return \DateTime
    Utilisation avec object dateTime
    $dateformat->ovrSub(
    array('interval'=>'P10D','type'=>'object'),
    'Y-m-d H:i:s',
    '2009-10-13'
    );
     *
    Utilisation avec chaine pour la durée
    $dateformat->ovrSub(
    array('interval'=>'+1 day','type'=>'string'),
    'Y-m-d H:i:s',
    '2009-10-13'
    );
     */
    #[Deprecated] public function ovrSub(string $interval, string $type = 'string', $format='Y-m-d', $time = null)
    {
        $datetime = $this->_datetime($time);
        $dateInterval = new DateInterval();
        $datetime->sub($dateInterval->getDateInterval($interval, $type));
        return $datetime->format($format);
    }

    /**
     * Configure une date ISO
     * @param $year
     * @param $month
     * @param int $day
     * @param string $format
     * @return mixed
     * @throws Exception
     * @example :
     * $dateformat->isoDate(2008, 2);
     * $dateformat->isoDate(2008, 2, 7);
     */
    #[Deprecated] public function isoDate($year, $week, $dayOfWeek=1,$format='Y-m-d'){
        $datetime = $this->_datetime(null);
        $datetime->setISODate($year, $week, $dayOfWeek);
        return $datetime->format($format);
    }

    /**
     * Assigne la date courante de l'objet à une nouvelle date.
     * @param $year
     * @param $month
     * @param $day
     * @param string $format
     * @return mixed
     * @throws Exception
     * @example :
     * $dateformat->assignDate(2008, 2, 1);
     */
    #[Deprecated] public function assignDate($year, $month, $day,$format='Y-m-d'){
        $datetime = $this->_datetime(null);
        $datetime->setDate($year, $month, $day);
        return $datetime->format($format);
    }
}