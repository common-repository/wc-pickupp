<?php
/**
 * Plugin Name: WC pickupp
 * Version: 2.4.0
 * Plugin URI: /
 * Description: Pickupp Woocommerce integration plugin.
 * Author: pickupp
 * Author URI: https://pickupp.io
 * Requires at least: 4.6.0
 * Tested up to: 5.7.2
 *
 * Text Domain: wc-pickupp
 * Domain Path: /languages
 *
 * @package WordPress
 * @author  pickupp
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WC_Pickupp')) {

    /**
     * Main Class.
     */
    class WC_Pickupp
    {

        /**
         * Plugin version.
         *
         * @var string
         */
        const VERSION = '2.4.0';

        /**
         * Instance of this class.
         *
         * @var object
         */
        protected static $instance = null;

        /**
         * Return an instance of this class.
         *
         * @return object single instance of this class.
         */
        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Constructor
         */
        private function __construct()
        {
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'fallback_notice'));
            } else {
                $this->load_plugin_textdomain();
                $this->load_config();
                $this->includes();
            }
        }

        /**
         * Method to call and run all the things that you need to fire when your plugin is activated.
         *
         */
        public static function activate()
        {
            include_once 'includes/wc-pickupp-activate.php';
            WC_Pickupp_Activate::activate();

        }

        /**
         * Method to call and run all the things that you need to fire when your plugin is deactivated.
         *
         */
        public static function deactivate()
        {
            include_once 'includes/wc-pickupp-deactivate.php';
            WC_Pickupp_Deactivate::deactivate();
        }

        public function load_config() {
            include_once 'config/wc-pickupp-config.php';
            WC_Pickupp_config::init();
        }

        /**
         * Method to includes our dependencies.
         *
         * @var string
         */
        public function includes()
        {
            include_once 'includes/wc-pickupp-extension-functionality.php';
        }

        /**
         * Load the plugin text domain for translation.
         *
         * @access public
         * @return bool
         */
        public function load_plugin_textdomain()
        {
            $locale = apply_filters('wepb_plugin_locale', get_locale(), 'wc-pickupp');
            return true;
        }

        /**
         * Fallback notice.
         *
         * We need some plugins to work, and if any isn't active we'll show you!
         */
        public function fallback_notice()
        {
            echo '<div class="error">';
            echo '<p>' . __('Woocommerce Pickupp: Needs the WooCommerce Plugin activated.', 'wc-pickupp') . '</p>';
            echo '</div>';
        }
    }
}

/**
 * Hook to run when your plugin is activated
 */
register_activation_hook(__FILE__, array('WC_Pickupp', 'activate'));

/**
 * Hook to run when your plugin is deactivated
 */
register_deactivation_hook(__FILE__, array('WC_Pickupp', 'deactivate'));

/**
 * Initialize the plugin.
 */
add_action('plugins_loaded', array('WC_Pickupp', 'get_instance'));
