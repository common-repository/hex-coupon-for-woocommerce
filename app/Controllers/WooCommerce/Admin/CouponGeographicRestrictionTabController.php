<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin;

use HexCoupon\App\Controllers\BaseController;
use hexcoupon\app\Core\Helpers\ValidationHelper;
use HexCoupon\App\Core\Lib\SingleTon;
use Kathamo\Framework\Lib\Http\Request;
use HexCoupon\App\Core\WooCommerce\CouponSingleGeographicRestrictions;

class CouponGeographicRestrictionTabController extends BaseController
{
	use SingleTon;

	private $error_message = 'An error occured while saving the geographic restriction tab meta value';

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return mixed
	 * @since 1.0.0
	 * Add all hooks that are needed for 'Geographic restriction' tab
	 */
	public function register()
	{
		add_action( 'wp_loaded', [ $this, 'get_all_post_meta' ] );
		add_action( 'woocommerce_process_shop_coupon_meta', [ $this, 'save_coupon_all_meta_data' ] );
		add_filter( 'woocommerce_coupon_is_valid', [ $this, 'apply_coupon_meta_data' ], 10, 2 );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_post_meta
	 * @param int $coupon
	 * @return array
	 * Get all coupon meta values
	 */
	public function get_all_post_meta( $coupon )
	{
		$all_meta_data = get_post_meta( $coupon, 'geographic_restriction', true );

		return $all_meta_data;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_meta_data
	 * @param string $key
	 * @param string $data_type
	 * @param int $post_id
	 * @return void
	 * Save the coupon geographic restriction all meta-data.
	 */
	private function save_coupon_meta_data( $key, $data_type, $post_id )
	{
		$validator = $this->validate( [
			$key => $data_type
		] );

		$error = $validator->error();
		if ( $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sprintf( esc_html__( 'Error: %s', 'hex-coupon-for-woocommerce' ), esc_html( $this->error_message ) ); ?></p>
			</div>
			<?php
		}
		$data = $validator->getData();

		update_post_meta( $post_id, $key, $data[$key] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_all_meta_data
	 * @param int $coupon_id.
	 * @return void
	 * Save the coupon geographic restriction meta-field data.
	 */
	public function save_coupon_all_meta_data( $coupon_id )
	{
		// Assign all meta fields key and their data type
		$this->save_coupon_meta_data( 'geographic_restriction', 'array', $coupon_id );
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method restrict_selected_shipping_zones_to_coupon
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon to selected shipping zones only.
	 */
	private function restrict_selected_shipping_zones_to_coupon( $valid, $coupon )
	{
		global $woocommerce;

		$all_meta_data = $this->get_all_post_meta( $coupon->get_id() ); // get the meta values

		$all_zones = ! empty( $all_meta_data['restricted_shipping_zones'] ) ? $all_meta_data['restricted_shipping_zones'] : [];

		$all_shipping_zones = CouponSingleGeographicRestrictions::getInstance()->get_all_shipping_zones();

		// Deleting changed shipping zone location
		foreach ( $all_zones as $value ) {
			if ( ! array_key_exists( $value, $all_shipping_zones ) ) {
				$key = array_search( $value, $all_zones );
				unset( $all_zones[$key] );
			}
		}

		$all_zones = implode( ',', $all_zones );

		$all_continents = [
			'Africa' => 'AF',
			'Antarctica' => 'AN',
			'Asia' => 'AS',
			'Europe' => 'EU',
			'North America' => 'NA',
			'Oceania' => 'OC',
			'South America' => 'SA',
		];

		// Initializing WC_Countries class
		$countries = new \WC_Countries();

		// Get all countries and their data
		$all_countries = $countries->get_countries();

		// Getting shipping information of the user
		$shipping_city = $woocommerce->customer->get_shipping_city();
		$shipping_country = $woocommerce->customer->get_shipping_country();
		$get_shipping_country_name = array_key_exists( $shipping_country, $all_countries ) ? $all_countries[$shipping_country] : 'None';

		$shipping_continent_code = $countries->get_continent_code_for_country( $shipping_country );
		$shipping_continent_full_name = array_search( $shipping_continent_code, $all_continents );

		// Getting billing information of the user
		$billing_city = $woocommerce->customer->get_billing_city(); // get the current billing city of the user
		$billing_country = $woocommerce->customer->get_billing_country();
		$get_billing_country_name = array_key_exists( $billing_country, $all_countries ) ? $all_countries[$billing_country] : 'None';

		$billing_continent_code = $countries->get_continent_code_for_country( $billing_country );
		$billing_continent_full_name = array_search( $billing_continent_code, $all_continents );

		// Validating user based on their shipping or billing address for zone wise restriction
		if ( empty( $all_zones ) ) {
			return true;
		}

		if ( $shipping_city || $shipping_country ) {
			if ( ! empty( $all_zones ) && $shipping_city && str_contains( $all_zones, $shipping_city ) ) {
				return false;
			}
			if ( ! empty( $all_zones ) && $shipping_country && $get_shipping_country_name && str_contains( $all_zones, $get_shipping_country_name ) ) {
				return false;
			}
			if ( ! empty( $all_zones ) && $shipping_continent_full_name && $get_shipping_country_name && str_contains( $all_zones, $shipping_continent_full_name ) ) {
				return false;
			}
		} else {
			if ( ! empty( $all_zones ) && ! empty( $billing_city ) && str_contains( $all_zones, $billing_city ) ) {
				return false;
			}
			if ( ! empty( $all_zones ) && $billing_country && $get_billing_country_name && str_contains( $all_zones, $get_billing_country_name ) ) {
				return false;
			}
			if ( ! empty( $all_zones ) && $billing_continent_full_name && $get_billing_country_name && str_contains( $all_zones, $billing_continent_full_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method restrict_selected_shipping_countries
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon to selected countries only.
	 */
	private function restrict_selected_shipping_countries( $valid, $coupon )
	{
		global $woocommerce;

		$all_meta_data = $this->get_all_post_meta( $coupon->get_id() ); // get all meta values

		$all_countries = ! empty( $all_meta_data['restricted_countries'] ) ? $all_meta_data['restricted_countries'] : [];

		$shipping_country = $woocommerce->customer->get_shipping_country();
		$billing_country = $woocommerce->customer->get_billing_country();

		// Validating coupon based on user country for country wise restriction
		if ( empty( $all_countries ) ) {
			return true;
		}
		if ( in_array( $shipping_country, $all_countries ) ) {
			return false;
		}
		if ( in_array( $billing_country, $all_countries ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method apply_coupon_meta_data
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon based on all criteria.
	 */
	public function apply_coupon_meta_data( $valid, $coupon )
	{
		$payment_shipping_method = PaymentAndShippingTabController::getInstance()->apply_coupon_meta_data( $valid, $coupon );

		$restricted_shipping_zones = $this->restrict_selected_shipping_zones_to_coupon( $valid, $coupon );

		$restrict_shipping_countries = $this->restrict_selected_shipping_countries( $valid, $coupon );

		if ( ! is_null( $restricted_shipping_zones )  ) {
			if ( ! $restricted_shipping_zones ) {
				// display a custom coupon error message if the coupon is invalid
				add_filter( 'woocommerce_coupon_error', [ $this, 'custom_coupon_error_message_for_shipping_zones' ] , 10, 2 );

				return false;
			}
		}

		if ( ! is_null( $restrict_shipping_countries ) ) {
			if ( ! $restrict_shipping_countries ) {
				// display a custom coupon error message if the coupon is invalid
				add_filter( 'woocommerce_coupon_error', [ $this, 'custom_coupon_error_message_for_shipping_countries' ] , 10, 2 );

				return false;
			}
		}

		if ( is_null( $restricted_shipping_zones ) || is_null( $restrict_shipping_countries ) ) {
			return $valid;
		}

		if ( ! $payment_shipping_method ) {
			// display a custom coupon error message if the coupon is invalid
			add_filter( 'woocommerce_coupon_error', [ $this, 'custom_coupon_error_message_for_payment_and_shipping_method' ] , 10, 2 );

			return false;
		}

		return $valid;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method custom_coupon_error_message_for_shipping_zones
	 * @param string $err
	 * @param int $err_code
	 * @return string
	 * Display custom error message for invalid coupon.
	 */
	public function custom_coupon_error_message_for_shipping_zones( $err, $err_code )
	{
		if ( $err_code === 100 ) {
			// Change the error message for the INVALID_FILTERED error here
			$err = esc_html__( 'Invalid coupon, your shipping zone does not support this coupon.', 'hex-coupon-for-woocommerce');
		}

		return $err;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method custom_coupon_error_message_for_shipping_countries
	 * @param string $err
	 * @param int $err_code
	 * @return string
	 * Display custom error message for invalid coupon.
	 */
	public function custom_coupon_error_message_for_shipping_countries( $err, $err_code )
	{
		if ( $err_code === 100 ) {
			// Change the error message for the INVALID_FILTERED error here
			$err = esc_html__( 'Invalid coupon. Your country does not support this coupon.', 'hex-coupon-for-woocommerce');
		}

		return $err;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method custom_coupon_error_message_for_payment_and_shipping_method
	 * @param string $err
	 * @param int $err_code
	 * @return string
	 * Display custom error message for invalid coupon.
	 */
	public function custom_coupon_error_message_for_payment_and_shipping_method( $err, $err_code ) {
		if ( $err_code === 100 ) {
			// Change the error message for the INVALID_FILTERED error here
			$err = esc_html__( 'Invalid coupon. Your payment or shipping method does not support this coupon.', 'hex-coupon-for-woocommerce');
		}

		return $err;
	}
}
