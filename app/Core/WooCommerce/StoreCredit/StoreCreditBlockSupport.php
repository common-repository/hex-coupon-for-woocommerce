<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;

if ( ! defined( 'ABSPATH' ) ) exit;
final class StoreCreditBlockSupport extends AbstractPaymentMethodType {

	protected $name = 'hex_store_credit';

	public function initialize() {
		// get payment gateway settings
		$this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
	}

	public function is_active() {
		return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-store-credit-blocks-integration',
			plugin_dir_url( __FILE__ ) . '../../../../build/index.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
			),
			null,
			true
		);

		return array( 'wc-store-credit-blocks-integration' );
	}

	public function get_payment_method_data() {
		return array(
			'title'        => $this->get_setting( 'title' ),
			'description'  => $this->get_setting( 'description' ),
			'total_available_credit' => StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount(),
		);
	}

}
