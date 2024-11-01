<?php

/**
 * Class to validate before sending request
 */
class WC_Pickupp_Validator
{

    public static function validate_order_form($order)
    {
        // pickup contact person
        if (!$order['pickup_contact_person']) {
            return 'Missing pickup contact person, please set it in woocommerce -> setting -> general';
        }

        // pickup contact phone
        if (!$order['pickup_contact_phone']) {
            return 'Missing pickup contact phone, please set it in woocommerce -> setting -> general';
        }

        // pickup address line 2
        if (!$order['pickup_address_line_1']) {
            return 'Missing pickup address line 1.';
        }

        // pickup address line 2
        if (!$order['pickup_address_line_2']) {
            $order['pickup_address_line_2'] = '-';
        }

        // dropoff contact person
        // check if the string only contains spaces
        if (ctype_space($order['dropoff_contact_person'])) {
            return 'Missing dropoff contact person';
        }

        // dropoff address line 1
        if (!$order['dropoff_address_line_1']) {
            return 'Missing dropoff address line 1.';
        }

        // dropoff address line 2
        if (!$order['dropoff_address_line_2']) {
            $order['dropoff_address_line_2'] = '-';
        }

        // dropoff contact phone
        if (!$order['dropoff_contact_phone']) {
            return 'Missing dropoff contact phone.';
        }

        // weight
        if (!$order['weight']) {
            return 'Missing weight.';
        }

        // weight
        if (!$order['length']) {
            return 'Missing length.';
        }

        // height
        if (!$order['height']) {
            return 'Missing height.';
        }

        // width
        if (!$order['width']) {
            return 'Missing width.';
        }

        // item nmae
        if (!$order['item_name']) {
            return 'Missing item name.';
        }
        return null;

    }

}
