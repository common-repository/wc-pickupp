<?php

class WC_Pickupp_Frontend
{
    public function __construct()
    {
        add_filter('woocommerce_shipping_fields', array($this, 'render_additional_shipping_fields'));

    }
    public function render_additional_shipping_fields($fields)
    {
        $fields['shipping_phone'] = array(
            'label' => __('Recipient Phone', 'wc-pickupp'),
            'required' => true,
            'clear' => true,
            'validate' => array('phone'),
        );
        return $fields;
    }
}

return new WC_Pickupp_Frontend();
