<?php
namespace HexCoupon\App\Controllers\WooCommerce\StoreCredit;

use HexCoupon\App\Core\Lib\SingleTon;

class FlushRewriteForStoreCredit
{
	use SingleTon;

	public function register()
	{
		add_action( 'init', [ $this, 'custom_flush_rewrite_rules' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method custom_flush_rewrite_rules
	 * @return void
	 * Flush rewriting so that the store credit page is displayed after making it on and off from the settings page.
	 */
	function custom_flush_rewrite_rules() {
		// Getting the value from your options table
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );
		$enable_store_credit = $store_credit_enable_data['enable'] ?? 0;

		// Checking if the value is set to true
		if ( $enable_store_credit ) {
			// Flush rewrite rules
			flush_rewrite_rules();
		}
	}

}
