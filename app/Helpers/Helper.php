<?php

use Illuminate\Support\Str;

if (! function_exists('setWireNavigate')) {
    function setWireNavigate($htmlContent)
    {
        return preg_replace('/<a([^>]*)>/', '<a wire:navigate.hover$1>', $htmlContent);
    }
}

if (! function_exists('setTargetBlank')) {
    function setTargetBlank($htmlContent)
    {
        return preg_replace('/<a([^>]*)>/', '<a target="_blank"$1>', $htmlContent);
    }
}

if (! function_exists('limitString')) {
    function limitString($string, $limit = 100, $end = '...')
    {
        return Str::limit(strip_tags($string), $limit, $end);
    }
}
