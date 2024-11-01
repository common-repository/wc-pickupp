<?php

/**
 * Class load config
 */
class WC_Pickupp_Config
{

    public static function init()
    {
        $region = get_option('pickupp_region');
        // because in save action, the get_option still keeps the old value
        if (isset($_POST['pickupp_region'])) $region = $_POST['pickupp_region'];

        // can define this PICKUPP_ENV in wp-config.php
        $env = defined('PICKUPP_ENV') ? PICKUPP_ENV : 'production';

        define('PICKUPP_SECRET', 'DJF8E34jDSu73iu8j8IdeG38hdiD83HJ');
        include_once __DIR__ . '/' . $region . '/' . $env . '.php';
        include_once __DIR__ . '/' . $region . '/all.php';
    }

}
