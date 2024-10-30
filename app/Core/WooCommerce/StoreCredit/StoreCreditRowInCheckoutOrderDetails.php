<?php
namespace HexCoupon\App\Core\WooCommerce\StoreCredit;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class StoreCreditRowInCheckoutOrderDetails {

	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		// Add store credit balance to the order details in the checkout page
		add_action( 'woocommerce_review_order_before_shipping', [ $this, 'add_store_credit_row_to_checkout' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_store_credit_row_to_checkout
	 * @return void
	 * Add store credit row to the checkout order details section
	 */
	public function add_store_credit_row_to_checkout()
	{
		global $woocommerce;
		$total = $woocommerce->cart->total;
		$total_available_store_credit = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();
		$total_available_store_credit = $total_available_store_credit ?? 0.0;

		$deducted_credit = 0;

		if ( $total_available_store_credit > $total ) {
			$deducted_credit = $total;
		}
		if ( $total_available_store_credit < $total ) {
			$deducted_credit = number_format( $total_available_store_credit, 2 );
		}
		?>
		<tr class="store-credit">
			<th><?php esc_html_e( 'Store Credit Used', 'hex-coupon-for-woocommerce' ); ?></th>
			<td><?php echo '-' . esc_html( $deducted_credit ); ?></td>
		</tr>

		<script>
			jQuery(document).ready(function($){
				let StoreCreditCheckbox = $("#store_credit_checkbox");
				$(StoreCreditCheckbox).change(function(){
					if ($(this).is(':checked')){
						$("tr.store-credit").show();
					}else{
						$("tr.store-credit").hide();
					}
				});
				$(StoreCreditCheckbox).trigger('change');
			});
		</script>
		<?php
	}

}
