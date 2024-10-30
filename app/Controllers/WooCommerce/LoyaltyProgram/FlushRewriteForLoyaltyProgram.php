<?php
namespace HexCoupon\App\Controllers\WooCommerce\LoyaltyProgram;

use HexCoupon\App\Core\Lib\SingleTon;

class FlushRewriteForLoyaltyProgram
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
	 * Flush rewriting so that the loyalty program page is displayed after making it on and off from the settings page.
	 */
	function custom_flush_rewrite_rules() {
		// Getting the value from your options table
		$loyalty_program_enable_settings = get_option( 'loyalty_program_enable_settings' );
		$loyalty_program_enable = $loyalty_program_enable_settings['enable'] ?? 0;

		// Checking if the value is set to true
		if ( $loyalty_program_enable ) {
			// Flush rewrite rules
			flush_rewrite_rules();
		}
	}

}
