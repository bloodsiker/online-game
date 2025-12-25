<?php

use App\Models\Article;
use App\Models\Product;
use App\Models\Setting;
use App\Service\SettingsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


if (!function_exists('format_money')) {
    function format_money($money, $decimal = 0, $decimalSeparator = '', $thousandsSeparator = ' '): string
    {
        return number_format($money, $decimal, $decimalSeparator, $thousandsSeparator);
    }
}
