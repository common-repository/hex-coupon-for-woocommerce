<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin;

use HexCoupon\App\Controllers\BaseController;
use hexcoupon\app\Core\Helpers\ValidationHelper;
use HexCoupon\App\Core\Lib\SingleTon;
use Kathamo\Framework\Lib\Http\Request;
class PaymentAndShippingTabController extends BaseController
{
	use SingleTon;

	private $error_message = 'An error occured when saving the payment and shipping value';

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return mixed
	 * Add all hooks that are needed to save the coupon meta-data and apply it on products
	 */
	public function register()
	{
		add_action( 'woocommerce_process_shop_coupon_meta', [ $this, 'save_coupon_all_meta_data' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_meta_data
	 * @param string $key key of coupon meta-data
	 * @param string $data_type
	 * @param int $post_id post ID of coupon
	 * @return void
	 * Save the coupon payment & shipping method meta-data.
	 */
	private function save_coupon_meta_data( $key, $data_type, $post_id )
	{
		$validator = $this->validate( [
			$key => $data_type
		] );

		$error = $validator->error();
		if ( $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sprintf(
					/* translators: %s: Just an error message to display */
					esc_html__( 'Error: %s', 'hex-coupon-for-woocommerce' ), esc_html( $this->error_message )
					); ?></p>
			</div>
			<?php
		}
		$data = $validator->getData();

		update_post_meta( $post_id, $key, $data[$key] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_all_meta_data
	 * @param int $post_id post id of coupon.
	 * @return void
	 * Save the coupon user roles custom meta-data when the coupon is updated.
	 */
	public function save_coupon_all_meta_data( $post_id )
	{
		// Save coupon permitted payment method and shipping method meta field data
		$this->save_coupon_meta_data( 'payment_and_shipping', 'array', $post_id );
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method apply_selected_payments_method_to_coupon
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon to user selected payment methods only.
	 */
	private function apply_selected_payments_method_to_coupon( $valid, $coupon, $payment_and_shipping )
	{
		// get saved selected permitted payment methods meta data
		$selected_permitted_payment_methods = ! empty( $payment_and_shipping['permitted_payment_methods'] ) ? $payment_and_shipping['permitted_payment_methods'] : [];

		// get current payment method of customer
		$current_payment_method = WC()->session->get( 'chosen_payment_method' );

		// check if is it empty
		if ( empty( $selected_permitted_payment_methods ) ) {
			return true;
		}

		// check if the current payment method matches with the selected payment methods
		if ( in_array( $current_payment_method, $selected_permitted_payment_methods ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method apply_selected_shipping_methods_to_coupon
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon to user selected shipping methods only.
	 */
	private function apply_selected_shipping_methods_to_coupon( $valid, $coupon, $payment_and_shipping )
	{
		// get permitted shipping methods meta field data
		$selected_shipping_methods = ! empty( $payment_and_shipping['permitted_shipping_methods'] ) ? $payment_and_shipping['permitted_shipping_methods'] : [];

		// check if is it empty
		if ( empty( $selected_shipping_methods ) ) {
			return true;
		}

		// get current chosen shipping method of customer
		$chosen_shipping_methods = WC()->session->get('chosen_shipping_methods' );

		// check current chosen shipping method matches with the selected permitted shipping method
		if ( ! empty( $chosen_shipping_methods ) ) {
			foreach ( $chosen_shipping_methods as $chosen_shipping_method ) {
				$exploded_string = explode( ':', $chosen_shipping_method );
				$chosen_shipping_method = $exploded_string[0];
				if ( in_array( $chosen_shipping_method, $selected_shipping_methods ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method apply_coupon_meta_data
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply coupon based on all criteria.
	 */
	public function apply_coupon_meta_data( $valid, $coupon )
	{
		$payment_and_shipping = get_post_meta( $coupon->get_id(), 'payment_and_shipping', true );

		$selectedPaymentMethod = $this->apply_selected_payments_method_to_coupon( $valid, $coupon, $payment_and_shipping );

		$selectedShippingMethods = $this->apply_selected_shipping_methods_to_coupon( $valid, $coupon, $payment_and_shipping );

		if ( $selectedPaymentMethod && $selectedShippingMethods ) {
			return true;
		}

		if ( ! $selectedPaymentMethod ) {
			return false;
		}
		if ( ! $selectedShippingMethods ) {
			return false;
		}
	}
}
