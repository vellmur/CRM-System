<?php

namespace App\Service;

class TimezoneService
{
    /**
     * Return time (hour) in timezone or in default timezone - 'Europe/Paris'.
     *
     * @param $timezone
     * @return string
     */
    public function getTimezoneTime($timezone)
    {
        // Get current date and hour for timezone, or by default timezone - 'UTC'
        if (!$timezone) $timezone = 'UTC';
        $now = new \DateTime('', new \DateTimeZone($timezone));

        return $now->format('H');
    }

    /**
     *  Return true if local timezone == $workTime, or default timezone (Europe/Paris) == $defaultTime
     *
     * @param $timezone
     * @param $localRunTime
     * @param $serverRunTime
     * @return bool
     */
    public function timeMatch($timezone, $localRunTime, $serverRunTime)
    {
        $currentTime = $this->getTimezoneTime($timezone);

        return ($timezone && $currentTime == $localRunTime) || (!$timezone && $currentTime == $serverRunTime);
    }
}