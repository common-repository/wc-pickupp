<?php

class WC_Pickupp_Status_Map
{
  // define order status constants
  const WC_PENDING = 'pending'; // this is pending payment
  const WC_PROCESSING = 'processing';
  const WC_COMPLETED = 'completed';
  const WC_CANCELLED = 'cancelled';
  const WC_FAILED = 'failed'; // dont allow sending with pickupp again

  const WC_STATUS_MAP = array(
    'scheduled' => self::WC_PROCESSING,
    'enroute' => self::WC_PROCESSING,
    'unable_to_pickup' => self::WC_PROCESSING,
    'pending_self_collect' => self::WC_PROCESSING,
    'unable_to_deliver' => self::WC_PROCESSING,
    'at_warehouse' => self::WC_PROCESSING,
    'returned' => self::WC_PROCESSING,

    'delivered' => self::WC_COMPLETED,
  );

  public static function get_status($status) {
    $status_lc = strtolower($status);
    if (array_key_exists($status_lc, self::WC_STATUS_MAP)) {
      return self::WC_STATUS_MAP[$status_lc];
    } else {
      return '';
    }
  }

  public static function can_send_again($status) {
    $status = strtolower($status);
    return (
      $status === 'expired' ||
      $status === 'back_to_warehouse' ||
      $status === 'back_to_merchant' ||
      $status === 'merchant_cancelled'
    );
  }

}
