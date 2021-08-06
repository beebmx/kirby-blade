<?php

use Illuminate\Contracts\Support\Htmlable;

if (!function_exists('b')) {
    function b($value, $doubleEncode = true)
    {
        return _e($value, $doubleEncode);
    }
}

if (!function_exists('_e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param \Illuminate\Contracts\Support\Htmlable|string $value
     * @param bool $doubleEncode
     * @return string
     */
    function _e($value, $doubleEncode = true)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}
