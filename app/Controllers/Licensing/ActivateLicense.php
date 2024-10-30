<?php
namespace HexCoupon\App\Controllers\Licensing;

use HexCoupon\App\Core\Lib\SingleTon;

class ActivateLicense
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method activate_license
	 * @return void
	 * @since 1.0.0
	 * Activating the license from the License menu.
	 */
	public function activate_license()
	{
		$license = trim( get_option( 'hexcoupon_license_key' ) );

		$api_params = [
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode('HexCoupon Pro'), // Name of the product in EDD
			'url'        => home_url()
		];

		$response = wp_remote_post( 'https://wphex.com', array( 'body' => $api_params ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'HTTP request failed: ' . $response->get_error_message() );
			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->success ) {
			update_option( 'hexcoupon_license_status', $license_data->license );
			delete_transient( 'hexcoupon_license_error' ); // Clear any previous error
			set_transient( 'hexcoupon_license_success', esc_html__( 'License activated successfully!', 'hex-coupon-for-woocommerce' ), 30 ); // Success message will last for 30 seconds
		} else {
			update_option( 'hexcoupon_license_status', 'invalid' );
			set_transient( 'hexcoupon_license_error', $license_data->error, 5 ); // Error message will last for 5 seconds
		}
	}
}
