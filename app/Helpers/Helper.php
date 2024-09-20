<?php

if (!function_exists('setWireNavigate')) {
    function setWireNavigate($htmlContent)
    {
        return preg_replace('/<a([^>]*)>/', '<a wire:navigate$1>', $htmlContent);
    }
}
