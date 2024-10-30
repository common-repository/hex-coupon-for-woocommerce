<?php
namespace HexCoupon\App\Controllers\Licensing;

use HexCoupon\App\Core\Lib\SingleTon;

class DeactivateLicense
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method deactivate_license
	 * @return void
	 * @since 1.0.0
	 * Deactivating the license from the 'License' menu
	 */
	public function deactivate_license()
	{
		$license = trim( get_option( 'hexcoupon_license_key' ) );

		$api_params = [
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( 'HexCoupon Pro' ), // Name of the product in EDD
			'url'        => home_url()
		];

		$response = wp_remote_post( 'https://wphex.com', array( 'body' => $api_params ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'HTTP request failed: ' . $response->get_error_message() );
			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->success ) {
			delete_option( 'hexcoupon_license_status' );
			set_transient( 'hexcoupon_license_success', esc_html__( 'License deactivated successfully!', 'hex-coupon-for-woocommerce' ), 5 ); // Success message will last for 5 seconds
		}
	}
}
