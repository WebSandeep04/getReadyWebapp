<?php

if (!function_exists('frontend_setting')) {
    /**
     * Get frontend setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function frontend_setting($key, $default = null)
    {
        try {
            return \App\Models\FrontendSetting::getValue($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
} 