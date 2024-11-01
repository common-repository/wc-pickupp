<?php
/**
 * WooCommerce Extension Functionality
 *
 * @category  Class
 * @package   WordPress
 * @author    pickupp
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      https://pickupp.io
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

include_once __DIR__ . '/../utils/wc-pickupp-status-map.php';
include_once __DIR__ . '/../services/wc-pickupp-api.php';
include_once __DIR__ . '/../utils/wc-pickupp-tool.php';
include_once 'wc-pickupp-admin-setting.php';
include_once 'wc-pickupp-frontend.php';

/**
 * Class to manage breadcrumbs and vendor's custom fields.
 */
class WooCommerce_Pickupp_Extension_Functionality
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->headers = array();
        $this->api = new WC_Pickupp_Api();
        add_action('admin_notices', array($this, 'pickupp_help_notice'));
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'handle_custom_query_var'), 10, 2);

        add_action('rest_api_init', function () {
            // <wordpress_url>/wp-json/pickupp/v1/order
            register_rest_route( 'pickupp/v1', '/reset', array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'reset' ),
            ));

            // <wordpress_url>/wp-json/pickupp/v1/order
            register_rest_route( 'pickupp/v1', '/link', array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'link' ),
            ));

            // <wordpress_url>/wp-json/pickupp/v1/order
            register_rest_route( 'pickupp/v1', '/order', array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_order' ),
                'permission_callback' => array( $this, 'pickupp_permissions_check' ),
            ));

            // <wordpress_url>/wp-json/pickupp/v1/orders
            register_rest_route( 'pickupp/v1', '/orders', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_orders' ),
                'permission_callback' => array( $this, 'pickupp_permissions_check' ),
            ));
            // <wordpress_url>/wp-json/pickupp/v1/products
            register_rest_route( 'pickupp/v1', '/products', array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_products' ),
                'permission_callback' => array( $this, 'pickupp_permissions_check' ),
            ));
        });

        add_filter('manage_edit-shop_order_columns', array($this, 'add_pickupp_orders_column'), 20);
        add_action('manage_shop_order_posts_custom_column', array($this, 'render_pickupp_orders_column'));
    }

    public function reset()
    {
        $body_json = file_get_contents('php://input');
        $body = json_decode($body_json);
        $token = $body->{'token'};

        if (!$token || $token !== PICKUPP_SECRET) {
            $response = rest_ensure_response('');
            $response->set_status( 403 );
            return json_encode($response);
        }

        delete_option('pickupp_region');
        delete_option('pickupp_api_key');
        delete_option('pickupp_auth_method');
        $response = rest_ensure_response('');
        $response->set_status( 200 );
        return json_encode($response);
    }

    public function link()
    {
        $body_json = file_get_contents('php://input');
        $body = json_decode($body_json);
        $token = $body->{'token'};
        if (!$token) {
            $response = rest_ensure_response(['error_message' => 'The token is empty']);
            $response->set_status( 400 );
            return json_encode($response);
        }
        update_option('pickupp_api_key', $token);

        $response = rest_ensure_response('');
        $response->set_status( 200 );
        return json_encode($response);
    }

    public function pickupp_help_notice()
    {
        $pickupp_api = get_option('pickupp_api_key');
        $pickupp_region = get_option('pickupp_region');

        if (!empty($pickupp_api) && !empty($pickupp_region)) return;

        $class = 'notice notice-warning';
        $message = __('Install Pickupp successfully! Please go to <a href="%s">setting page</a> to continue.', 'wc-pickupp');

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post(sprintf($message, get_admin_url() . 'admin.php?page=wc-settings&tab=advanced&section=pickupp')));
    }

    public function update_order()
    {
        try {
            $body_json = file_get_contents('php://input');
            $body = json_decode($body_json);
            $client_reference_number = $body->{'client_reference_number'};
            $status = $body->{'status'};
            $order_number = $body->{'order_number'};

            // get woocomerce order id and just update the status
            $order = new WC_Order($client_reference_number);
            $oldStatus = get_post_meta($order->get_id(), '_pickupp_order_status', true);

            if (strtoupper($status) === $oldStatus) return;

            if (isset($order) && $order->get_id() != 0) {
                $order->add_order_note('Pickupp order status update for ' . $order_number . ': ' . strtoupper($status));
                update_post_meta($order->get_id(), '_pickupp_order_status', strtoupper($status));
                $wc_status = WC_Pickupp_Status_Map::get_status($status);
                if ($wc_status != '' && $order->status != $wc_status) {
                    $order->update_status($wc_status);
                }
                if (WC_Pickupp_Status_Map::can_send_again($status)) {
                    delete_post_meta($order->get_id(), '_already_sent_with_pickupp');
                }
                if (!empty($order_number) && $status === 'scheduled') {
                    update_post_meta($order->get_id(), '_already_sent_with_pickupp', true);
                    update_post_meta($order->get_id(), '_pickupp_order_number', $order_number);
                }
            }
        } catch (Exception $e) {
            // silent error for now
        } finally {
            $response = rest_ensure_response('');
            $response->set_status( 200 );
            return json_encode($response);
        }
    }

    public function get_orders()
    {
        $params = array(
            'status' => array('wc-processing'),
            'orderby' => 'date',
            'order' => 'DESC',
            'pickupp_status' => true,
            'limit' => 10,
            'paginate' => true,
        );

        if (isset($_REQUEST['created_at_min'])) {
            $params['date_created'] = wp_unslash($_REQUEST['created_at_min']);
        }

        if (isset($_REQUEST['created_at_min']) && isset($_REQUEST['created_at_max'])) {
            $params['date_created'] = wp_unslash($_REQUEST['created_at_min']) . '...' . wp_unslash($_REQUEST['created_at_max']);
        }

        if (isset($_REQUEST['limit'])) {
            $params['limit'] = wp_unslash($_REQUEST['limit']);
        }

        if (isset($_REQUEST['offset'])) {
            $params['offset'] = wp_unslash($_REQUEST['offset']);
        }

        if (isset($_REQUEST['order_ids'])) {
            $params['post__in'] = wp_unslash($_REQUEST['order_ids']);
        }

        $wc_orders = wc_get_orders($params);
        $orders = array();

        $pickup_contact_person = get_option('pickup_contact_person');
        $pickup_contact_phone = get_option('pickup_contact_phone');
        $pickup_address_line_1 = get_option('woocommerce_store_address');
        $pickup_address_line_2 = get_option('woocommerce_store_address_2');
        $pickup_city = get_option('woocommerce_store_city');
        $pickup_postcode = get_option('woocommerce_store_postcode');

        foreach ($wc_orders->orders as $order) {
            $order_id = $order->get_id();
            $order_data = $order->get_data();

            $order_data['pickup_contact_person'] = $pickup_contact_person;
            $order_data['pickup_contact_phone'] = $pickup_contact_phone;
            $order_data['pickup_address'] = [
              'address_1' => $pickup_address_line_1,
              'address_2' => $pickup_address_line_2,
              'city'  => $pickup_city,
              'postcode'  => $pickup_postcode,
            ];
            $order_data['total_weight'] = 0;
            $order_data['total_length'] = 0;
            $order_data['total_width'] = 0;
            $order_data['total_height'] = 0;

            $order_data['shipping']['phone'] = get_post_meta($order_id, '_shipping_phone', true);

            $order_data['items'] = array();
            foreach ( $order->get_items() as $item_id => $item ) {
                $product_id = $item->get_product_id();
                $product_meta_weight = get_post_meta($product_id, '_weight', true);
                $product_meta_length = get_post_meta($product_id, '_length', true);
                $product_meta_width = get_post_meta($product_id, '_width', true);
                $product_meta_height = get_post_meta($product_id, '_height', true);

                $product_weight = wc_get_weight($product_meta_weight ?: 0, 'kg');
                $product_length = wc_get_dimension($product_meta_length ?: 0, 'cm');
                $product_width = wc_get_dimension($product_meta_width ?: 0, 'cm');
                $product_height = wc_get_dimension($product_meta_height ?: 0, 'cm');

                $item_quantity = $item->get_quantity();
                $item_weight = $item_quantity * $product_weight;
                $item_length = $item_quantity * $product_length;

                $order_data['items'][] = array(
                    'name' => $item->get_name(),
                    'quantity' => $item_quantity,
                    'weight' => $item_weight,
                    'length' => $item_length,
                    'width' => $product_width,
                    'height' => $product_height,
                    'sku' => $item->get_product()->get_sku(),
                );

                $order_data['total_weight'] += $item_weight;
                $order_data['total_length'] += $item_length;
                $order_data['total_width'] += $product_width;
                $order_data['total_height'] += $product_height;
            }
            $order_weight = wc_get_weight(get_post_meta($order_id, '_shipping_weight', true), 'kg');
            $order_length = wc_get_dimension(get_post_meta($order_id, '_shipping_length', true), 'cm');
            $order_width = wc_get_dimension(get_post_meta($order_id, '_shipping_width', true), 'cm');
            $order_height = wc_get_dimension(get_post_meta($order_id, '_shipping_height', true), 'cm');

            if ($order_weight) $order_data['total_weight'] = $order_weight;
            if ($order_length) $order_data['total_length'] = $order_length;
            if ($order_width) $order_data['total_width'] = $order_width;
            if ($order_height) $order_data['total_height'] = $order_height;

            $orders[] = $order_data;
        }

        return array(
            'orders' => $orders,
            'total' => $wc_orders->total,
        );
    }

    public function get_products()
    {
        $params = array(
            'limit' => 10,
            'paginate' => true,
        );

        if (isset($_REQUEST['limit'])) {
            $params['limit'] = wp_unslash($_REQUEST['limit']);
        }

        if (isset($_REQUEST['offset'])) {
            $params['offset'] = wp_unslash($_REQUEST['offset']);
        }

        $wc_products = wc_get_products($params);

        $products = array();

        foreach ( $wc_products->products as $product ) {
            $products[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'description' => $product->get_description(),
                'price' => $product->get_price(),
                'weight' => $product->get_weight(),
                'permalink' => $product->get_permalink(),
                'sku' => $product->get_sku(),
                'stock' => $product->get_stock_quantity(),
                'attributes' => $product->get_attributes(),
                'categories' => $product->get_category_ids(),
                'tags' => $product->get_tag_ids(),
                'type' => $product->get_type(),
                'variations' => array_map( function( $variation ) {
                    $variation = wc_get_product( $variation );
                    return array(
                        'id' => $variation->get_id(),
                        'name' => $variation->get_name(),
                        'description' => $variation->get_description(),
                        'price' => $variation->get_price(),
                        'weight' => $variation->get_weight(),
                        'permalink' => $variation->get_permalink(),
                        'sku' => $variation->get_sku(),
                        'stock' => $variation->get_stock_quantity(),
                        'attributes' => $variation->get_attributes(),
                        'categories' => $variation->get_category_ids(),
                        'tags' => $variation->get_tag_ids(),
                    );
                }, $product->get_children()),
            );
        }

        return array(
            'products' => $products,
            'total' => $wc_products->total,
        );
    }


    public function pickupp_permissions_check( WP_REST_Request $request )
    {
        $authorization_header = $_REQUEST['token'];
        if (empty($authorization_header)) {
            $authorization_header = $request->get_header('authorization') ?: '';
        }
        if (empty($authorization_header) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authorization_header = isset($headers['authorization']) ? $headers['authorization'] : '';
        }

        $api_key = get_option('pickupp_api_key');
        $token = str_replace('Basic ', '', $authorization_header);

        return !empty($api_key) && $token == $api_key;
    }

    public function handle_custom_query_var($query, $query_vars)
    {
        if (!empty($query_vars['pickupp_status'])) {
            $status = esc_attr($query_vars['pickupp_status']);
            $query['meta_query'][] = array(
                'key' => '_already_sent_with_pickupp',
                'value' => $status,
                'compare' => $status == 'true' ? '=' : 'NOT EXISTS',
            );
        }

        return $query;
    }

    public function add_pickupp_orders_column($columns)
    {
        $reordered_columns = array();

        // Inserting columns to a specific location
        foreach ($columns as $key => $value) {
            $reordered_columns[$key] = $value;
            if ($key ===  'order_status') {
                // Inserting after "Status" column
                $reordered_columns['pickupp-order'] = __('Pickupp order no.','wc-pickupp');
            }
        }

        return $reordered_columns;
    }

    public function render_pickupp_orders_column($column)
    {
        global $post;

        if ($column === 'pickupp-order') {
            $order_number = get_post_meta($post->ID, '_pickupp_order_number', true);

            if (!empty($order_number)) {
                $output = sprintf('<a href="%1$sorders/%2$s" target="_blank">%2$s</a>', PICKUPP_PORTAL_URL, $order_number);
                echo $output;
            }
        }
    }
}

return new WooCommerce_Pickupp_Extension_Functionality();
