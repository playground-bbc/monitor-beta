<?php
namespace app\helpers;

use yii;
use Jenssegers\Date\Date;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * DateHelper wrapper for time function.
 *
 */
class DateHelper
{
	/**
     * [add plus a date depending in number]
     * @param  [string] $date   [date]
     * @return [string] $number [ej +1 day]
     */
    public static function add($date, $number)
    {
    	$date_formateer = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');
		$date_obj = new Date($date_formateer);
		$date_change = $date_obj->add($number);
		$date = (array) $date_change;
		return explode(" ",$date['date'])[0];
    }

    /**
     * [add plus hours to date depending in number]
     * @param  [string] $date   [date unix]
     * @param  [string] $number   [int 1]
     * @return [string] $number []
     */
    public static function addHours($date, $number)
    {
        //$date_format = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');
        $date_future = Date::parse($date)->addHours($number);
        return $date_future->getTimestamp();
    }

    /**
     * [sub plus a date depending in number]
     * @param  [string] $date   [date]
     * @return [string] $number [ej +1 day]
     */
    public static function sub($date, $number)
    {
        $date_formateer = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');
        $date_obj = new Date($date_formateer);
        $date_change = $date_obj->sub($number);
        $date = (array) $date_change;
        return explode(" ",$date['date'])[0];
    }

    /**
     * [diffInDays get diffInDays between two date]
     * @param  [string] $date_1   [date ej unix date]
     * @return [string] $date_21  [date ej "Sat Aug 24 14:29:51 +0000 2019"]
     */
    public static function diffInDays($date_1,$date_2){
    	
    	$date_format_1 = Yii::$app->formatter->asDatetime($date_1,'yyyy-MM-dd');
		$date_format_2 = Yii::$app->formatter->asDatetime($date_2,'yyyy-MM-dd');
    	$diff = Date::parse($date_format_1)->floatDiffInDays($date_format_2);
    	
    	return round($diff);
    }

     /**
     * [diffInDays get diffInMonths between two date]
     * @param  [string] $date_1   [date ej unix date]
     * @return [string] $date_21  [date ej "Sat Aug 24 14:29:51 +0000 2019"]
     */
    public static function diffInMonths($date_1,$date_2){
        
        $date_format_1 = Yii::$app->formatter->asDatetime($date_1,'yyyy-MM-dd');
        $date_format_2 = Yii::$app->formatter->asDatetime($date_2,'yyyy-MM-dd');
        $diff = Date::parse($date_format_1)->floatDiffInMonths($date_format_2);
        return round($diff, 0, PHP_ROUND_HALF_DOWN);
    }
    /**
     * [diffForHumans get difference between two date formatter as human read_only]
     * @param  [string / int] $date_1 [unix date format]
     * @param  [string / int] $date_2 [unix date format]
     * @return [string]               [1 day before and two hours]
     */
    public static function diffForHumans($date_1,$date_2){
        $date_1 = new Date((int)$date_1); // date-searched
        $date_2 = new Date((int)$date_2); // end date
        $diff = Date::parse($date_1)->diffForHumans($date_2);
        return $diff;
    }

    public static function periodDates($start_date,$end_date)
    {
        date_default_timezone_set('UTC');
        $start_date = Yii::$app->formatter->asDatetime($start_date,'yyyy-MM-dd');
        $end_date = Yii::$app->formatter->asDatetime($end_date,'yyyy-MM-dd');
        // By default daysUntil will use a 1-day interval:
        //$period = Date::parse($start_date)->range($end_date,1, 'days');
        $period = Date::parse($start_date)->toPeriod($end_date, '1 days');
        // iterate by days
        $days = [];
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }

        return $days;
    }

    /**
     * [asTimestamp get time in unix date]
     * @param  [type] $date [11 julio de 2019 or 11/07/2019 yes is my birthday]
     * @return [string]       [21101054511210 yes is not unix is example]
     */
    public static function asTimestamp($date){
        $date = new \DateTime($date, new \DateTimeZone('America/Santiago'));
        return $date->getTimestamp();
    }

    public static function asDatetime($date){
        return date('d/m/Y',$date);
    }
    /**
     * [isToday take a date and well his function name isToday rigth]
     * @param  [type]  $date [description]
     * @return boolean       [description]
     */
    public static function isToday($date){
        $date = new Date($date);
        return $date->isToday();
    }
    /**
     * [getToday get today date as unix with timezone santiago]
     * @return [type] [description]
     */
    public static function getToday()
    {
        date_default_timezone_set('America/Santiago');

        $now = new \DateTime();
        return $now->getTimestamp();
    }
     /**
     * [getTodayDate get today date but only  date + 00:00:00.000000]
     * @return [int] [today date format 00:00:00.000000 on timespan]
     */
    public static function getTodayDate($timespan = true)
    {
        $today_date = Date::today();
        
        return ($timespan) ? $today_date->getTimestamp() : $today_date;

    }

    public static function daysUntil($from_date,$to_date)
    {
        $period = Date::parse($from_date)->daysUntil($to_date);
        $days = [];
        foreach ($period as $key => $date) {
            $days[] = $date->format('U');
        }
        return $days;
    }

    /**
     * [isBetweenDate chekcs if date is between two date ]
     * @param  [type]  $date [date to look up]
     * @param  [type]  $from [start date in unix]
     * @param  [type]  $to   [end date in unix]
     * @return boolean       [true if or not if is not]
     */
    public static function isBetweenDate($date,$from,$to){
        // set default timezone
        date_default_timezone_set('America/Santiago');

        $date = date('Y-m-d', strtotime(str_replace("/", "-", $date)));

        $stratDate = date('Y-m-d', $from);
        $endDate = date('Y-m-d', $to);

        if (($date >= $stratDate) && ($date <= $endDate)){
            return true;
        }else{
            return false;  
        }

    }
}