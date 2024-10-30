<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\FormHelpers;
use HexCoupon\App\Core\Helpers\RenderHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponPaymentandShipping
{
	use singleton;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return void
	 * Registers all hooks that are needed to create custom tab on 'Coupon Single' page.
	 */
	public function register()
	{
		add_action( 'woocommerce_coupon_data_tabs', [ $this, 'add_custom_coupon_tab' ] );
		add_filter( 'woocommerce_coupon_data_panels', [ $this, 'add_custom_coupon_tab_content' ] );
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method get_all_payment_methods
	 * @return array
	 * Retrieve all payment methods of 'WooCommerce' that are enabled
	 */
	public function get_all_payment_methods()
	{
		// get all available payment method gateways
		$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$payment_options = []; // define an empty array

		// assign all the payment method gateways in the '$payment_options' array
		foreach ( $payment_gateways as $gateway_id => $gateway ) {
			$payment_options[ $gateway_id ] = $gateway->get_title();
		}

		return $payment_options; // return the values
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method get_all_shipping_methods
	 * @return array
	 * Retrieve all shipping methods of 'WooCommerce' that are enabled.
	 */
	public function get_all_shipping_methods()
	{
		// get all shipping zones
		$shipping_methods = \WC_Shipping_Zones::get_zones();

		$shipping_method_names = []; // define an empty array

		// Show the names of the enabled shipping methods only.
		foreach ( $shipping_methods as $shipping_method ) {
			foreach ( $shipping_method['shipping_methods'] as $single_method ) {
				$method_title = $single_method->get_method_title();
				$method_id = $single_method->id;

				if ( 'yes' === $single_method->enabled && in_array( $method_title, [ 'Free shipping', 'Flat rate', 'Local pickup' ] ) ) {
					$shipping_method_names[$method_id] = $single_method->title;
				}
			}
		}

		return $shipping_method_names; // finally return all shipping method names
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method add_custom_coupon_tab
	 * @param array $tabs
	 * @return array
	 * @since 1.0.0
	 * Displays the new tab in the coupon single page called 'Payment & shipping method'.
	 */
	public function add_custom_coupon_tab( $tabs )
	{
		$tabs['custom_coupon_tab'] = [
			'label'    => esc_html__( 'Payment & shipping method', 'hex-coupon-for-woocommerce' ),
			'target'   => 'custom_coupon_tab',
			'class'    => array( 'show_if_coupon_usage_limits' ),
		];
		return $tabs;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_custom_coupon_tab_content
	 * @return void
	 * Displays the content of custom tab 'Payment & shipping method'.
	 */
	public function add_custom_coupon_tab_content()
	{
		$output ='<div id="custom_coupon_tab" class="panel woocommerce_options_panel payment_and_shipping_method">';

		$payment_and_shipping = get_post_meta( get_the_ID(),'payment_and_shipping',true );

		$permitted_payment_methods = ! empty( $payment_and_shipping['permitted_payment_methods'] ) ? $payment_and_shipping['permitted_payment_methods'] : [];

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Allow Payment Methods', 'hex-coupon-for-woocommerce' ),
			'name' => 'payment_and_shipping[permitted_payment_methods]',
			'id' => 'permitted_payment_methods',
			'value' => $permitted_payment_methods,
			'type' => 'select',
			'options' => $this->get_all_payment_methods(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'permitted_payment_methods',
			'placeholder' => esc_html__('Enter Payment Methods')
		] );

		echo '<span class="permitted_payment_methods_tooltip">'.wc_help_tip( esc_html__( 'Select payment methods that you want to allow to the coupon.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		$permitted_shipping_methods = ! empty( $payment_and_shipping['permitted_shipping_methods'] ) ? $payment_and_shipping['permitted_shipping_methods'] : [];

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Allow Shipping Methods', 'hex-coupon-for-woocommerce' ),
			'name' => 'payment_and_shipping[permitted_shipping_methods]',
			'id' => 'permitted_shipping_methods',
			'value' => $permitted_shipping_methods,
			'type' => 'select',
			'options' => $this->get_all_shipping_methods(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'permitted_shipping_methods',
			'placeholder' => esc_html__('Enter Shipping Methods')
		] );

		echo '<span class="permitted_shipping_methods_tooltip">'.wc_help_tip( esc_html__( 'Select shipping methods that you want to allow to the coupon.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		$output .= '</div>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );
	}
}
