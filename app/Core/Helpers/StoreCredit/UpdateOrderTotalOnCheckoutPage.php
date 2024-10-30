<?php
namespace HexCoupon\App\Core\Helpers\StoreCredit;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class UpdateOrderTotalOnCheckoutPage {

	use SingleTon;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		// Inline script to update order total based on checkbox status in the legacy checkout page
		add_action( 'wp_footer', [ $this, 'update_order_total_based_on_checkbox' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method update_order_total_based_on_checkbox
	 * @return void
	 * @since 1.0.0
	 * Update order total value based on checkbox in the checkout page
	 */
	public function update_order_total_based_on_checkbox()
	{
		$total_available_store_credit = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();
		$total_available_store_credit = $total_available_store_credit ?? 0;
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				// Store the original total amount when the page loads
				var originalTotalAmount = parseFloat($('.order-total .woocommerce-Price-amount').text().replace(/[^\d.-]/g, ''));

				// Event delegation for checkbox change
				$(document).on('change', 'input[name="store_credit_checkbox"]', function() {
					var isChecked = $(this).prop('checked');

					// Adjust the order total based on the checkbox status and available store credit
					var totalAmount = isChecked ? (originalTotalAmount - <?php echo $total_available_store_credit; ?>) : originalTotalAmount;
					totalAmount = totalAmount < 0 ? 0 : totalAmount;

					$('.order-total .woocommerce-Price-amount').text(formatPrice(totalAmount));
				});

				function formatPrice(price) {
					return '$' + parseFloat(price).toFixed(2);
				}
			});
		</script>
		<?php
	}

}
