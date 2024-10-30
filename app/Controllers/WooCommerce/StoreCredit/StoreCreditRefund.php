<?php
namespace HexCoupon\App\Controllers\WooCommerce\StoreCredit;

use HexCoupon\App\Core\Helpers\StoreCreditHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class StoreCreditRefund
{
	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_action( 'woocommerce_order_status_changed', [ $this, 'refunded_order_data' ], 10, 4 );
		add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'refunded_order_data_notice' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method refunded_order_data
	 * @return void
	 * Creating a log for store credit after giving full or partial refund to the customer
	 */
	public function refunded_order_data( $order_id, $old_status, $new_status, $order )
	{
		// Getting 'store_credit_enable_data' data from the option table
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );

		// Checking if the new status is 'refunded'
		if ( 'refunded' === $new_status && $store_credit_enable_data['enable'] ) {
			$user_id = $order->get_user_id(); // getting the user id from the order object
			$amount = $order->get_total();
			// Get the timestamp when the order status changed to 'refunded'
			$status_change_date = current_time( 'timestamp' );
			// Convert timestamp to a human-readable date format
			$formatted_date = date_i18n( get_option( 'date_format' ), $status_change_date );

			$user_data = get_userdata( $user_id );
			$user_email = $user_data->user_email;
			$notification_message = esc_html__( 'You got store credit ', 'hex-coupon-for-woocommerce' ) . wc_price( $amount ) . esc_html__( 'as refund', 'hex-coupon-for-woocommerce' );
			$status = 1;

			// Get the admin ID who performed the refund
			$admin_id = get_current_user_id();
			$admin_data = get_userdata( $admin_id );
			$admin_name = $admin_data ? $admin_data->display_name : 'Unknown';

			// Invoking data insertion functions
			StoreCreditHelpers::getInstance()->hex_store_credit_logs_initial_insertion( $order_id, $user_id, $admin_id, $admin_name, $amount, $status );
			StoreCreditHelpers::getInstance()->hex_store_credit_initial_data_insertion( $amount, $user_id );
			// Sending notification via email
			StoreCreditHelpers::getInstance()->send_confirmation_email_for_store_credit_activation( $user_id, $order_id, $amount, $notification_message, $user_email, $formatted_date );
		}
		// Checking if all the new status has the partial refunded amount
		if ( ( 'completed' === $new_status || 'processing' === $new_status || 'failed' === $new_status || 'cancelled' === $new_status ) && $store_credit_enable_data['enable'] && $order->get_total_refunded() ) {
			$user_id = $order->get_user_id(); // getting the user id from the order object
			$amount = $order->get_total_refunded();
			// Get the timestamp when the order status changed to 'refunded'
			$status_change_date = current_time( 'timestamp' );
			// Convert timestamp to a human-readable date format
			$formatted_date = date_i18n( get_option( 'date_format' ), $status_change_date );

			$status = 1;
			$notification_message = esc_html__( 'You got store credit ', 'hex-coupon-for-woocommerce' ) . wc_price( $amount ) . esc_html__( 'as refund', 'hex-coupon-for-woocommerce' );

			$user_data = get_userdata( $user_id );
			$user_email = $user_data->user_email;

			// Get the admin ID who performed the refund
			$admin_id = get_current_user_id();
			$admin_data = get_userdata( $admin_id );
			$admin_name = $admin_data ? $admin_data->display_name : 'Unknown';

			// Invoking data insertion functions
			StoreCreditHelpers::getInstance()->hex_store_credit_logs_initial_insertion( $order_id, $user_id, $admin_id, $admin_name, $amount, $status );
			StoreCreditHelpers::getInstance()->hex_store_credit_initial_data_insertion( $amount, $user_id );
			// Sending notification via email
			StoreCreditHelpers::getInstance()->send_confirmation_email_for_store_credit_activation( $user_id, $order_id, $amount, $notification_message, $user_email, $formatted_date );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method refunded_order_data_notice
	 * @return void
	 * Showing an admin notice after a log has been created after giving store credit for the refund
	 */
	public function refunded_order_data_notice( $order )
	{
		// Getting 'store_credit_enable_data' data from the option table
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );

		$admin_url = admin_url( 'admin.php?page=hexcoupon-page#/store-credit/store-credit-logs' );

		// Checking if the order status is 'refunded' and store credit is enabled
		if ( in_array( $order->get_status(), array( 'refunded', 'processing', 'failed', 'completed' ) ) && $order->get_total_refunded() > 0 && $store_credit_enable_data['enable'] ) {
			$refund_amount = $order->get_total_refunded();
			if ( $refund_amount ) {
				?>
				<div class="notice notice-info is-dismissible updated">
					<p><?php esc_html_e( 'The refund amount has been converted to store credit of:  ', 'hex-coupon-for-woocommerce' );?><b><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $refund_amount ) ); ?></b></p>
					<p><a class="button-primary button-large" href="<?php echo esc_url( $admin_url );?>"><?php echo esc_html__( 'Check Store Credit Log' );?></a></p>
				</div>
				<?php
			}
		}
	}
}
