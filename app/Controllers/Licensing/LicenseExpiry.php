<?php
namespace HexCoupon\App\Controllers\Licensing;

use HexCoupon\App\Core\Lib\SingleTon;

class LicenseExpiry
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return void
	 * @since 1.0.0
	 * Registering the necessary hooks that are needed
	 */
	public function register()
	{
		add_action( 'admin_init', [ $this, 'check_license_expiry_on_init' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method check_license_expiry_on_init
	 * @return void
	 * @since 1.0.0
	 * Checking license expiry to validate the license key
	 */
	public function check_license_expiry_on_init()
	{
		$hexcoupon_license_key = get_option( 'hexcoupon_license_key' );

		// Your EDD site URL
		$edd_site_url = 'https://wphex.com';
		// License key to check
		$license_key = $hexcoupon_license_key;
		// Item ID
		$item_id = 2810;
		// URL of the site
		$site_url = home_url();

		// Build the URL for the API request
		$api_url = add_query_arg( [
			'edd_action' => 'check_license',
			'item_id' => $item_id,
			'license' => $license_key,
			'url' => $site_url
		], $edd_site_url );

		// Make the API request
		$response = wp_remote_get( $api_url );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			error_log( 'License check failed: Could not connect to the server' . $response->get_error_message() );
			return;
		}

		// Parse the response
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Check if the license is expired
		if ( $license_data && isset( $license_data->license ) && $license_data->license === 'expired' ) {
			update_option(  'hexcoupon_license_status', $license_data->license );
			// Handle expired license
			add_action( 'admin_notices', function() {
				$message = sprintf(
					__( 'Your HexCoupon Pro license has expired. Please %1$srenew%2$s to continue getting the update.', 'hex-coupon-for-woocommerce' ),
					'<a href="https://hexcoupon.com/pricing" target="_blank">',
					'</a>'
				);

				echo '<div class="notice notice-error"><p>' . wp_kses_post( $message ) . '</p></div>';
			} );
		} elseif ( $license_data && isset( $license_data->license ) && $license_data->license === 'key_mismatch' ) {
			update_option(  'hexcoupon_license_status', $license_data->license );
		}
	}

}
