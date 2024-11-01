<?php

/**
 * Class to handle stuff that you need when your plugin is activated
 */
class WC_Pickupp_Activate
{

    public static function activate()
    {
        $region = get_option('pickupp_region');

        if (!$region) update_option('pickupp_region', 'HK');
    }

}
