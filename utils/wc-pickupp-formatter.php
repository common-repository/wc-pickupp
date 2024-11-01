<?php

/**
 * Class to format some fields
 */
class WC_Pickupp_Formatter
{

    public static function format_price($price)
    {
        if(PRICE_FORMAT_TO_K && $price >= 1000) {
          return round($price/1000, 5) . 'K';
        }
        return strval(floatval($price));
    }

}
