<?php
namespace HexCoupon\App\Core\Helpers\LoyaltyProgram;
use HexCoupon\App\Core\Lib\SingleTon;

class DisplayAllNotice
{
	use SingleTon;

	private $wpdb;

	private $table_name;

	private $points_for_signup;

	private $points_on_purchase;

	private $enable_point_loyalties;

	/**
	 * Registering hooks that are needed
	 */
	public function register()
	{
		$this->points_for_signup = get_option( 'pointsForSignup' );
		$points_for_signup_enable = $this->points_for_signup['enable'] ?? '';

		$this->points_on_purchase = get_option( 'pointsOnPurchase' );
		$points_on_purchase_enable = $this->points_on_purchase['enable'] ?? 0;

		$this->enable_point_loyalties = get_option( 'loyalty_program_enable_settings' );
		$this->enable_point_loyalties = $this->enable_point_loyalties['enable'] ?? 0;

		if ( $this->enable_point_loyalties ) {
			if ( $points_for_signup_enable ) {
				// Showing points notice in my account page
				add_action( 'woocommerce_register_form_start', [ $this, 'display_signup_points_notice' ] );
			}
			if ( $points_on_purchase_enable ) {
				// Showing points notice to the checkout page
				add_action( 'woocommerce_before_checkout_form', [ $this, 'show_points_notice_on_checkout' ] );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method display_signup_points_notice
	 * @return void
	 * Creating table called 'hex_store_credit_logs_table'
	 */
	public function display_signup_points_notice() {
		if ( ! is_account_page() ) {
			return;
		}

		$points_for_signup = ! empty( $this->points_for_signup['pointAmount'] ) ? $this->points_for_signup['pointAmount'] : '';

		echo '<div class="woocommerce-info">';
		printf( esc_html__( 'Sign up now and receive %s reward points!', 'hex-coupon-for-woocommerce' ), esc_html( $points_for_signup ) );
		echo '</div>';
	}



	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_points_notice_on_checkout
	 * @return void
	 * Showing notice for points that will be given to customer for order purchase
	 */
	public function show_points_notice_on_checkout()
	{
		// Get the current WooCommerce cart
		$cart = WC()->cart;

		// Ensuring the cart is not empty
		if ( $cart->is_empty() ) {
			return;
		}

		$spending_amount = ! empty( $this->points_on_purchase['spendingAmount'] ) ? $this->points_on_purchase['spendingAmount']: 0;
		$point_amount = ! empty( $this->points_on_purchase['pointAmount'] ) ? $this->points_on_purchase['pointAmount']: 0;

		// Getting the sub-total value of order
		$total_value = $cart->get_subtotal();

		// Calculating the points for full order
		if ( $spending_amount != 0 ) {
			$spending_ratio = $total_value / $spending_amount;
			$total_points = floor( $spending_ratio ) * $point_amount;
		} else {
			$total_points = 0;
		}


		$notice = sprintf( esc_html__( 'You will earn %d points with this order.', 'hex-coupon-for-woocommerce' ), esc_html( $total_points ) );

		// Displaying the points notice
		wc_print_notice( $notice, 'notice' );
	}

}
