(function($) {
	"use strict";
	$(document).ready(function(){
		// destructuring internationalization functions for making text translatable
		const { __ } = wp.i18n;

		$(".product_addition_notice span.dashicons-dismiss").on("click",function(){
			$(".product_addition_notice").hide();
		});
		$('#store_credit_filter').on('change', function() {
			// Get the selected option value
			var filterValue = $(this).val();
			// Get all table rows
			var rows = $('#data-table tbody tr');
			// Loop through each row and toggle its visibility based on the filter value
			rows.each(function() {
				var row = $(this);
				if (filterValue === 'all') {
					row.css('display', 'table-row');
				} else if (filterValue === 'in' && row.hasClass('in')) {
					row.css('display', 'table-row');
				} else if (filterValue === 'out' && row.hasClass('out')) {
					row.css('display', 'table-row');
				} else {
					row.css('display', 'none');
				}
			});
		});
		// copy referral link after clicking the copy button
		$('.copy-referral-link').on('click',function(){
			// Get the input field
			var referralLink = $('#referral-link');
			// Select the input field text
			referralLink.select();
			referralLink[0].setSelectionRange(0, 99999); // For mobile devices
			// Try to use the Clipboard API
			if (navigator.clipboard) {
				navigator.clipboard.writeText(referralLink.val()).then(function() {
					// Alert the copied text
					alert(__('Referral link copied to clipboard!','hex-coupon-for-woocommerce'));
				}, function(err) {
					// If something goes wrong
					alert(__('Failed to copy the referral link: ','hex-coupon-for-woocommerce') + err);
				});
			} else {
				// Fallback for browsers that don't support navigator.clipboard
				try {
					document.execCommand('copy');
					alert(__('Referral link copied to clipboard!','hex-coupon-for-woocommerce'));
				} catch (err) {
					alert(__('Failed to copy the referral link: ','hex-coupon-for-woocommerce') + err);
				}
			}
		});
	});
})(jQuery);
