<?php

if (!function_exists('setWireNavigate')) {
    function setWireNavigate($htmlContent)
    {
        return preg_replace('/<a([^>]*)>/', '<a wire:navigate$1>', $htmlContent);
    }
}

if (!function_exists('setTargetBlank')) {
    function setTargetBlank($htmlContent)
    {
        return preg_replace('/<a([^>]*)>/', '<a target="_blank"$1>', $htmlContent);
    }
}
