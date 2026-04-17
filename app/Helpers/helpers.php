<?php

if (! function_exists('format_cooldown')) {
    function format_cooldown(int $seconds): string
    {
        if ($seconds <= 0) {
            return '—';
        }

        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        if ($minutes === 0) {
            return $secs.'с';
        }

        return $secs > 0 ? $minutes.'м '.$secs.'с' : $minutes.'м';
    }
}

if (! function_exists('format_money')) {
    function format_money($money, $decimal = 0, $decimalSeparator = '', $thousandsSeparator = ' '): string
    {
        return number_format($money, $decimal, $decimalSeparator, $thousandsSeparator);
    }
}
