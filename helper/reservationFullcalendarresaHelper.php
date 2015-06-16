<?php
class reservationFullcalendarresaHelper extends reservationFullcalendarresaHelper_Parent
{
    public function timeToSecond($time)
    {
        sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
        if (isset($seconds)) {
            if (isset($minutes)) {
                if (isset($hours)) {
                    $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
                } else {
                    $time_seconds = $minutes * 60 + $seconds;
                }
            } else {
                if (isset($hours)) {
                    $time_seconds = $hours * 3600 + $seconds;
                } else {
                    $time_seconds = $seconds;
                }
            }
        } else {
            if (isset($hours)) {
                if (isset($minutes)) {
                    $time_seconds = $hours * 3600 + $minutes * 60;
                } else {
                    $time_seconds = $hours * 3600;
                }
            } else if (isset($minutes)) {
                $time_seconds = $minutes * 60;
            } else {
                $time_seconds = 0;
            }
        }
        return $time_seconds;
    }
    public function secondToTime($duree)
    {
        // $heures = intval($duree / 3600);
        // $minutes = intval(($duree % 3600) / 60);
        // $secondes = intval((($duree % 3600) % 60));
        // $new_date_time = mktime($heures, $minutes, $secondes, 0, 0, 0);
        $new_date = gmdate("H:i:s", $duree);
        return $new_date;
    }
}
