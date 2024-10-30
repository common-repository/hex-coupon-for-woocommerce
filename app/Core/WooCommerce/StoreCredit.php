<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

/**
 * will add more
 * @since  1.0.0
 * */
class StoreCredit
{
	use SingleTon;

	private $user;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return void
	 * @since 1.0.0
	 * Add all hooks that are needed.
	 */
	public function register()
	{
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );
		$store_credit_enable_data = $store_credit_enable_data['enable'] ?? 0;

		if ( $store_credit_enable_data ) {
			// Action hook for adding 'Store Credit' menu page in the 'My Account' Page Menu
			add_filter ( 'woocommerce_account_menu_items', [ $this, 'store_credit_in_my_account_page' ], 40 );
			// Action hook for registering permalink endpoint
			add_action( 'init', [ $this, 'store_credit_menu_page_endpoint' ] );
			// Action hook for displaying 'Store Credit' page content
			add_action( 'woocommerce_account_store-credit_endpoint', [ $this, 'store_credit_page_endpoint_content' ] );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_in_my_account_page
	 * @return mixed
	 * @since 1.0.0
	 * Show 'Store Credit' tab in the 'My Account' page
	 */
	public function store_credit_in_my_account_page( $all_menu_links )
	{
		$all_menu_links = array_slice( $all_menu_links, 0, 6, true )
			+ [ 'store-credit' => esc_html__( 'Store Credit', 'hex-coupon-for-woocommerce' ) ]
			+ array_slice( $all_menu_links, 6, NULL, true );

		return $all_menu_links;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_menu_page_endpoint
	 * @return mixed
	 * @since 1.0.0
	 * Register 'store-credit' endpoint
	 */
	public function store_credit_menu_page_endpoint()
	{
		return add_rewrite_endpoint( 'store-credit', EP_PAGES );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_page_endpoint_content
	 * @return void
	 * @since 1.0.0
	 * Show content in the 'store-credit' endpoint
	 */
	public function store_credit_page_endpoint_content()
	{
		$store_credit_logs = StoreCreditPaymentHelpers::getInstance()->show_log_on_user_end();
		$remaining_logs = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();
		?>
		<div class="store-credit-logs-container">
			<p class="store-credit-balance"><?php esc_html_e( 'Remaining Balance:', 'hex-coupon-for-woocommerce' ); ?> <?php echo wc_price( $remaining_logs ); ?> </p>
			<div>
				<span><?php esc_html_e( 'Filter:', 'hex-coupon-for-woocommerce' ); ?></span>
				<select id="store_credit_filter">
					<option value="all"><?php esc_html_e( 'All', 'hex-coupon-for-woocommerce' ); ?></option>
					<option value="in"><?php esc_html_e( 'In', 'hex-coupon-for-woocommerce' ); ?></option>
					<option value="out"><?php esc_html_e( 'Out' ); ?></option>
				</select>
			</div>
		</div>

		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table store-credit-logs-table" id="data-table">
			<thead>
			<tr>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Type', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Amount', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Date', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Time', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Status', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
					<span class="nobr"><?php esc_html_e( 'Actions', 'hex-coupon-for-woocommerce' ); ?></span>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $store_credit_logs ) ) : foreach ( $store_credit_logs as $log ) : $filter = $log->type == 0 ? 'in' : 'out'; ?>
			<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order <?php echo esc_attr( $filter ); ?>">
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
					<?php
					$type = '';

					switch ( $log->label ) {
						case '0':
							$type = 'Refund Credit';
							break;
						case '1':
							$type = 'Gift Credit';
							break;
						default :
							$type = 'None';
					}
					printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $type ) );
					?>
				</td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
					<?php echo wc_price( $log->amount ); ?>
				</td>
				<?php
				$created_at = explode( ' ', $log->created_at );
				$created_date = $created_at[0];
				$created_time = $created_at[1];
				?>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $created_date ) ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $created_time ) ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
					<?php
					$status = $log->type;

					if ( $status == 0 )
						echo '<b style="color: green">' . esc_html__( 'In', 'hex-coupon-for-woocommerce' ) . '</b>';
					if ( $status == 1 )
						echo '<b style="color: darkred">' . esc_html__( 'Out', 'hex-coupon-for-woocommerce' ) .  '</b>';
					?>
				</td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Actions">
					<a href="<?php echo wc_get_page_permalink( 'shop' ); ?>" class="woocommerce-button wp-element-button button view"><?php echo esc_html__( 'Continue Shopping', 'hex-coupon-for-woocommerce' ); ?></a>
				</td>
			</tr>
			<?php endforeach; else : ?>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php esc_html_e( 'No Data', 'hex-coupon-for-woocommerce' ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php esc_html_e( 'No Data', 'hex-coupon-for-woocommerce' ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php esc_html_e( 'No Data', 'hex-coupon-for-woocommerce' ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php esc_html_e( 'No Data', 'hex-coupon-for-woocommerce' ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"><?php esc_html_e( 'No Data', 'hex-coupon-for-woocommerce' ); ?></td>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Actions">
					<a href="<?php echo wc_get_page_permalink( 'shop' ); ?>" class="woocommerce-button wp-element-button button view"><?php echo esc_html__( 'Continue Shopping', 'hex-coupon-for-woocommerce' ); ?></a>
				</td>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
}
