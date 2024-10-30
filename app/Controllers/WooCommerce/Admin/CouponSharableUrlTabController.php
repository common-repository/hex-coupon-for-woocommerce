<?php
namespace hexcoupon\app\Controllers\WooCommerce\Admin;

use HexCoupon\App\Controllers\BaseController;
use hexcoupon\app\Core\Helpers\ValidationHelper;
use HexCoupon\App\Core\Lib\SingleTon;
use Kathamo\Framework\Lib\Http\Request;

class CouponSharableUrlTabController extends BaseController {

	use SingleTon;

	private $error_message = 'An error occured while saving the sharable url tab meta value';

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return void
	 * Add all hooks that are needed for 'Coupon Sharable URL' tab
	 */
	public function register()
	{
		add_action( 'woocommerce_process_shop_coupon_meta', [ $this, 'save_coupon_all_meta_data' ] );
		add_action( 'wp_loaded', [ $this, 'apply_coupon_activation_via_url' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_meta_data
	 * @param string $key
	 * @param string $data_type
	 * @param int $post_id
	 * @return void
	 * Save the coupon sharable url meta-data.
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
				<p><?php echo sprintf( esc_html__( 'Error: %s', 'hex-coupon-for-woocommerce' ), esc_html( $this->error_message ) ); ?></p>
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
	 * @param int $coupon_id post ID of Coupon.
	 * @return void
	 * Save the coupon sharable url custom meta-data when the coupon is updated.
	 */
	public function save_coupon_all_meta_data( $coupon_id )
	{
		$this->save_coupon_meta_data( 'sharable_url_coupon', 'array', $coupon_id );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_coupon_activation_via_url
	 * @return void
	 * Apply coupon automatically after visiting a custom url.
	 */
	public function apply_coupon_activation_via_url()
	{
		if ( isset( $_GET['coupon_code'] ) ) {
			// getting coupon code from the url
			$coupon_code = sanitize_text_field( $_GET['coupon_code'] );

			$coupon_id = wc_get_coupon_id_by_code( $coupon_code );
			$sharable_url_coupon = get_post_meta( $coupon_id, 'sharable_url_coupon', true );
			$redirect_link = ! empty( $sharable_url_coupon['redirect_link'] ) ? $sharable_url_coupon['redirect_link'] : '';
			$custom_local_url = ! empty( $sharable_url_coupon['custom_local_url'] ) ? $sharable_url_coupon['custom_local_url'] : home_url();

			// Check if the given url has the right coupon code
			$sharable_url = ! empty( $sharable_url_coupon['sharable_url'] ) ? $sharable_url_coupon['sharable_url'] : '';
			$coupon_code_search = str_contains( $sharable_url, 'coupon_code=' . $coupon_code );

			$apply_automatic_coupon_by_url = ! empty( $sharable_url_coupon['apply_automatic_coupon_by_url'] ) ? $sharable_url_coupon['apply_automatic_coupon_by_url'] : '';

			if ( $coupon_code_search && ! WC()->cart->has_discount( $coupon_code ) && 'yes' === $apply_automatic_coupon_by_url ) {
				// show user defined success message for url coupon, if set
				 add_filter( 'woocommerce_coupon_message', [ $this, 'custom_success_msg_for_url_coupon' ], 10, 3 );

				if ( 'no_redirect' === $redirect_link ) {
					WC()->cart->apply_coupon( $coupon_code );
					$url = home_url();
					wp_safe_redirect( $url );
				} elseif ( 'redirect_to_custom_local_url' === $redirect_link ) {
					WC()->cart->apply_coupon( $coupon_code );
					wp_safe_redirect( $custom_local_url );
				} else {
					WC()->cart->apply_coupon( $coupon_code );
					wp_safe_redirect( $redirect_link );
				}

				exit();
			}
		}

	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method custom_success_msg_for_url_coupon
	 * Showing custom success message for url coupon
	 */
	public function custom_success_msg_for_url_coupon( $msg, $msg_code, $coupon ) {
		$coupon_code = sanitize_text_field( $_GET['coupon_code'] ); // get coupon code from the url
		$coupon_id = wc_get_coupon_id_by_code( $coupon_code );

		$sharable_url_coupon = get_post_meta( $coupon_id, 'sharable_url_coupon', true );
		// Success message for successful url coupon
		$message_for_coupon_discount_url = ! empty( $sharable_url_coupon['message_for_coupon_discount_url'] ) ? $sharable_url_coupon['message_for_coupon_discount_url'] : '';

		if( $msg === esc_html__( 'Coupon code applied successfully.', 'woocommerce' ) && ! empty( $message_for_coupon_discount_url ) ) {
			$msg = sprintf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $message_for_coupon_discount_url ) );
		}

		return $msg;
	}
}
