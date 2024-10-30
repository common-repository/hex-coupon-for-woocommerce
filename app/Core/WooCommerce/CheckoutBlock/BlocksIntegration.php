<?php

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

define( 'ORDD_BLOCK_VERSION', '1.0.0' );

class Blocks_Integration implements IntegrationInterface {

    /**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'store-credit';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'checkout-block-frontend' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'store-credit-block-editor' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array();
	}

	/**
	 * Register scripts block editor.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {
		// Getting payment system data from pro version
		$store_credit_payment_system_data = get_option( 'store_credit_payment_system_data' );

		$script_path = '/build/index.js';
		$script_path_pro = '/build-pro/index.js';

		if ( ! empty( $store_credit_payment_system_data['storeCreditPayment'] ) && $store_credit_payment_system_data['storeCreditPayment'] === 'full_payment' ) {
			$script_url = plugins_url( 'hex-coupon-for-woocommerce-pro' . $script_path_pro );
		} else {
			$script_url = plugins_url( 'hex-coupon-for-woocommerce' . $script_path );
		}

		$script_asset_path = plugins_url( 'hex-coupon-for-woocommerce/build/index.asset.php' );
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => hexcoupon_get_config(),
			);

		wp_register_script(
			'store-credit-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Register scripts for frontend block.
	 *
	 * @return void
	 */
	public function register_block_frontend_scripts() {
		$script_path		= '/build/checkout-block-frontend.js';
		$script_pro_path	= '/build-pro/checkout-block-frontend.js';

		// Getting payment system data from pro version
		$store_credit_payment_system_data = get_option( 'store_credit_payment_system_data' );

		if ( ! empty( $store_credit_payment_system_data['storeCreditPayment'] ) && $store_credit_payment_system_data['storeCreditPayment'] === 'full_payment' ) {
			$script_url        = plugins_url( '/hex-coupon-for-woocommerce-pro' . $script_pro_path );
			$script_asset_path = WP_PLUGIN_DIR . '/hex-coupon-for-woocommerce-pro/build-pro/checkout-block-frontend.asset.php';
		} else {
			$script_url        = plugins_url( '/hex-coupon-for-woocommerce' . $script_path );
			$script_asset_path = WP_PLUGIN_DIR . '/hex-coupon-for-woocommerce/build/checkout-block-frontend.asset.php';
		}

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => hexcoupon_get_config(),
			);

		wp_register_script(
			'checkout-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return ORDD_BLOCK_VERSION;
	}

}
