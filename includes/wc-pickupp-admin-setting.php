<?php

include_once __DIR__ . '/../utils/wc-pickupp-tool.php';

class WC_Pickupp_Admin_Setting
{
    public function __construct()
    {
        $this->api = new WC_Pickupp_Api();
        add_action('woocommerce_settings_pickupp_end', array($this, 'render_extra_settings'));
        add_filter('woocommerce_get_sections_advanced', array($this, 'render_pickupp_setting_tab'));
        add_filter('woocommerce_get_settings_advanced', array($this, 'render_pickupp_all_settings'), 10, 2);
        add_filter('woocommerce_get_settings_general', array($this, 'render_pickupp_general_settings'), 10, 2);
        add_action( 'woocommerce_admin_order_data_after_order_details', array($this, 'render_pickupp_order_detail' ));
        add_action('admin_head', array($this, 'add_custom_files'));
    }

    public function render_extra_settings()
    {
        $pickupp_api = get_option('pickupp_api_key');
        $pickupp_region = get_option('pickupp_region');
        $pickupp_auth_method = get_option('pickupp_auth_method');

        if (empty($pickupp_region)) return;

        $portal_url = PICKUPP_PORTAL_URL;
        $site_url = urlencode(site_url());
        $link_url = "${portal_url}woocommerce?shop=$site_url&authMethod=$pickupp_auth_method";
        $link = "Your shop is not linked yet! <a href=\"$link_url\">Link to Pickupp</a>";

        if ($pickupp_api) {
            $entity_portal = $this->api->get_entity_portal();
            if ( isset( $entity_portal->data ) && $entity_portal->data->value == site_url() ) {
                $link = "Your shop has been linked successfully! <a href=\"${portal_url}woocommerce\" target=\"_blank\">Go to Pickupp!</a>";
            }
        }

        echo '<tr valign="top"><th scope="row" class="titledesc"></th><td class="forminp">';
        echo $link;
        echo '</td></tr>';
    }

    public function render_pickupp_setting_tab($sections)
    {
        $sections['pickupp'] = __('Pickupp', 'wc-pickupp');
        return $sections;
    }

    public function render_pickupp_all_settings($settings, $current_section)
    {
        /**
         * Check the current section is what we want
         **/
        if ($current_section == 'pickupp') {
            $api_key = get_option('pickupp_api_key');

            $pickupp_settings = array();
            // Add Title to the Settings
            $pickupp_settings[] = array(
                'name' => __('Pickupp Settings', 'wc-pickupp'),
                'type' => 'title',
                'desc' => __('The following options are used to configure Pickupp plugin', 'wc-pickupp'),
                'id' => 'pickupp',
            );
            $pickupp_settings[] = array(
                'name' => __('Region', 'wc-pickupp'),
                'desc' => __('Choose the region where you register the account', 'wc-pickupp'),
                'id' => 'pickupp_region',
                'type' => 'select',
                'options' => array(
                    'blank' => __('Select a region', 'wc-pickupp'),
                    'HK' => __('Hong Kong', 'wc-pickupp'),
                    'SG' => __('Singapore', 'wc-pickupp'),
                    'MY' => __('Malaysia(KL)', 'wc-pickupp'),
                    'TW' => __('Taiwan', 'wc-pickupp'),
                ),
            );

            $pickupp_settings[] = array(
                'name' => __('Pickupp Auth Method', 'wc-pickupp'),
                'desc' => __('Please do not edit if unsure. Reach out to your account manager for more information', 'wc-pickupp'),
                'id' => 'pickupp_auth_method',
                'type' => 'select',
                'options' => array(
                    'default' => __('default', 'wc-pickupp'),
                    'query' => __('traditional', 'wc-pickupp'),
                ),
            );

            if ($api_key) {
                $pickupp_settings[] = array(
                    'name' => __('Pickupp API Key', 'wc-pickupp'),
                    'desc' => __('Please do not edit if unsure. Reach out to your account manager for more information', 'wc-pickupp'),
                    'id' => 'pickupp_api_key',
                    'type' => 'text',
                );
            }

            $pickupp_settings[] = array(
              'type' => 'sectionend',
              'id' => 'pickupp',
            );
            return $pickupp_settings;

            /**
             * If not, return the standard settings
             **/
        } else {
            return $settings;
        }
    }

    public function render_pickupp_general_settings($settings)
    {
        /**
         * Check the current section is what we want
         **/
        array_unshift($settings, array(
            'name' => __('Pickup Contact Number', 'wc-pickupp'),
            'desc_tip' => __('This will be contact person phone number ', 'wc-pickupp'),
            'id' => 'pickup_contact_phone',
            'type' => 'text',
            'validate' => array('phone'),
        ));
        array_unshift($settings, array(
            'name' => __('Pickup Contact Person name', 'wc-pickupp'),
            'desc_tip' => __('This will be contact person name ', 'wc-pickupp'),
            'id' => 'pickup_contact_person',
            'type' => 'text',
        ));
        array_unshift($settings, array('type' => 'title', 'id' => 'pickupp'));

        return $settings;
    }

    public function expose_config()
    {
        echo '<script type="text/javascript">';
        echo 'const PICKUPP_SERVICE_START_TIME = ' . json_encode(PICKUPP_SERVICE_START_TIME) . ';';
        echo 'const PICKUPP_SERVICE_END_TIME = ' . json_encode(PICKUPP_SERVICE_END_TIME) . ';';
        echo 'const PICKUPP_CREATE_ORDER_TIME_STEP = ' . json_encode(PICKUPP_CREATE_ORDER_TIME_STEP) . ';';
        echo '</script>';
    }

    public function add_custom_files()
    {
        $this->expose_config();
        echo '<style>';
        include_once __DIR__ . './../public/pickupp.css';
        echo '</style>';
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker@1.11.12/jquery.timepicker.min.css" type="text/css" />';
        echo '<script src="https://cdn.jsdelivr.net/npm/timepicker@1.11.12/jquery.timepicker.min.js"></script>';
        echo '<script type="text/javascript">';
        include_once __DIR__ . './../public/pickupp.js';
        echo '</script>';
    }

    public function render_pickupp_order_detail($order)
    {
        $orderNumber = get_post_meta($order->id, '_pickupp_order_number', true);

        if (empty($orderNumber)) return;

        $orderStatus = get_post_meta($order->id, '_pickupp_order_status', true);
        $link = PICKUPP_PORTAL_URL. 'orders/' . $orderNumber;
        ?>
        <div class="order_data_column">
            <h3><?php echo __( 'Pickupp order no.', 'wc-pickupp'); ?></h3>
            <a target="_blank" href="<?php echo $link ?>"><?php echo $orderNumber ?></a>
            <?php echo $orderStatus ?>
        </div>
        <?php
    }
}

return new WC_Pickupp_Admin_Setting();
