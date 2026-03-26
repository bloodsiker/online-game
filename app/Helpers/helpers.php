<?php


if (!function_exists('format_money')) {
    function format_money($money, $decimal = 0, $decimalSeparator = '', $thousandsSeparator = ' '): string
    {
        return number_format($money, $decimal, $decimalSeparator, $thousandsSeparator);
    }
}
