<?php
namespace HexCoupon\App\Controllers;

use HexCoupon\App\Core\Lib\SingleTon;
use Kathamo\Framework\Lib\Controller;


class RedirectUserToPluginDashboard extends Controller
{
	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_action( 'activated_plugin', [ $this, 'redirect_to_hexcoupon_dashboard_after_plugin_activation' ] );
	}

	/**
	 * Redirecting users to the dashboard of HexCoupon after activating the plugin
	 *
	 * @return void
	 */
	public function redirect_to_hexcoupon_dashboard_after_plugin_activation( $plugin ) {
		if ( $plugin == 'hex-coupon-for-woocommerce/hex-coupon-for-woocommerce.php' ) {
			// Check if WooCommerce is active and then redirect to HexCoupon menu page
			if ( class_exists( 'WooCommerce' ) ) {
				// Redirect to the specified page after activation
				wp_safe_redirect( admin_url( 'admin.php?page=hexcoupon-page' ) );
				exit;
			}
		}
	}
}
