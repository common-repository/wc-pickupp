<?php
/**
 * Class to handle api calls
 */
class WC_Pickupp_Api
{

    public function __construct()
    {
        $this->api_key = get_option('pickupp_api_key');
        $this->tz = new DateTimeZone(wc_timezone_string());
    }

    public function get_entity_portal()
    {
      return $this->make_request('/merchant/entity/portal?type=woocommerce', 'GET', '');
    }

    public function make_request($path, $method, $data)
    {
        $response = NULL;
        if ( $method === 'POST' ) {
          $response = wp_safe_remote_post( PICKUPP_SERVER_URL . $path, array(
            'timeout' => 20,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
              'Content-Type' => 'application/x-www-form-urlencoded',
              'Authorization' => 'Basic ' . $this->api_key,
            ),
            'body' => $data,
          ));
        } else if ( $method === 'GET' ) {
          $response = wp_safe_remote_get( PICKUPP_SERVER_URL . $path, array(
            'timeout' => 20,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
              'Authorization' => 'Basic ' . $this->api_key,
            ),
          ));
        }

        if ( is_wp_error( $response ) ) {
            $errRes = new stdClass();
            $errRes->meta->error_type = "CUSTOM";
            $errRes->meta->error_message = $response->get_error_message();
            return $errRes;
        }
        $result = $response['body'];
        if (!$result) {
          $errMsg = error_get_last()["message"];
          $errRes = new stdClass();
          $errRes->meta->error_type = "CUSTOM";
          $errRes->meta->error_message = $errMsg;
          return $errRes;
        }
        $resJSON = json_decode($result);
        // if $result is invalid JSON string, resJSON is NULL but try catch cannot catch this
        if (!$resJSON) {
          $errRes = new stdClass();
          $errRes->meta->error_type = "INVALID_JSON_RESPONSE";
          $errRes->meta->error_message = "JSON result from api is invalid: " . $result;
          return $errRes;
        }
        return $resJSON;
    }
}
