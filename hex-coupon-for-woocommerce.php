<?php
/**
 * @package hexcoupon
 *
 * Plugin Name: HexCoupon: Ultimate WooCommerce Toolkit
 * Plugin URI: https://wordpress.org/plugins/hex-coupon-for-woocommerce
 * Description: Extend coupon functionality in your Woocommerce store.
 * Version: 1.2.4
 * Author: WpHex
 * Requires at least: 5.4
 * Tested up to: 6.6.1
 * Requires PHP: 7.1
 * WC requires at least: 6.0
 * WC tested up to: 9.2.3
 * Author URI: https://wphex.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hex-coupon-for-woocommerce
 * Domain Path: /languages
 */

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use HexCoupon\App\Core\Core;
use HexCoupon\App\Core\Helpers\StoreCredit\StoreCreditBlockSupport;
use HexCoupon\App\Controllers\Api\SpinWheelSettingsApiController;

if ( ! defined( 'ABSPATH' ) ) die();

define( 'HEXCOUPON_FILE', __FILE__ );

require_once __DIR__ .'/qrcode/qrcode.php';

require_once __DIR__ . '/configs/bootstrap.php';

if ( file_exists( HEXCOUPON_DIR_PATH . '/vendor/autoload.php' ) ) {
	require_once HEXCOUPON_DIR_PATH . '/vendor/autoload.php';
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_hex_coupon_for_woocommerce() {

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client( 'c0ee1555-4851-4d71-8b6d-75b1872dd3d2', 'HexCoupon &#8211; Advance Coupons For WooCommerce(Free)', __FILE__ );

	// Active insights
	$client->insights()->init();

}

appsero_init_tracker_hex_coupon_for_woocommerce();

/**
 * Plugin compatibility declaration with WooCommerce HPOS - High Performance Order Storage
 *
 * @return void
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( FeaturesUtil::class ) ) {
		FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_filter( 'woocommerce_coupon_discount_types', 'display_bogo_discount_in_couopon_type_column',10, 1 );

/**
 * Display 'Bogo Discount' text in the 'Coupon type' column in all coupon page
 *
 * @return void
 */
function display_bogo_discount_in_couopon_type_column( $discount_types ) {
	$discount_types[ 'buy_x_get_x_bogo' ] = esc_html__( 'Bogo Discount', 'hex-coupon-for-woocommerce' );

	return $discount_types;
}

/**
 * Block Support for store credit
 */
// enabling store credit block support based on whether it is enabled or not
$store_credit_enable_data = get_option( 'store_credit_enable_data' );
$store_credit_enable_data = $store_credit_enable_data['enable'] ?? 0;

if ( $store_credit_enable_data ) {
	add_action( 'woocommerce_blocks_loaded', 'store_credit_block_support' );
	add_filter ( 'woocommerce_blocks_loaded', 'checkout_block_for_store_credit' );
}

function store_credit_block_support()
{
	// here we're including our "gateway block support class"
	require_once __DIR__ . '/app/Core/Helpers/StoreCredit/StoreCreditBlockSupport.php';

	// registering the PHP class we have just included
	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new StoreCreditBlockSupport );
		}
	);
}

// Store Credit block checkout
function checkout_block_for_store_credit()
{
	require_once 'app/Core/WooCommerce/CheckoutBlock/BlocksIntegration.php';

	add_action(
		'woocommerce_blocks_checkout_block_registration',
		function( $integration_registry ) {
			$integration_registry->register( new Blocks_Integration() );
		}
	);

	if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => 'hex-coupon-for-woocommerce',
				'data_callback'   => 'cb_data_callback',
				'schema_callback' => 'cb_schema_callback',
				'schema_type'     => ARRAY_A,
			)
		);
	}
}

/**
 * Callback function to register endpoint data for blocks.
 *
 * @return array
 */
function cb_data_callback() {
	return array(
		'use_store_credit' => '',
	);
}

/**
 * Callback function to register schema for data.
 *
 * @return array
 */
function cb_schema_callback() {
	return array(
		'use_store_credit'  => array(
			'description' => __( 'Use Store Credit', 'hex-coupon-for-woocommerce' ),
			'type'        => array( 'true', 'false' ),
			'readonly'    => true,
		),
	);
}

Core::getInstance();
