(function($) {
	"use strict";
	$(document).ready(function(){
		// destructuring internationalization functions for making text translatable
		const { __ } = wp.i18n;

		let isProActive = false;

		if (typeof pro_data !== 'undefined' && pro_data.is_pro_active) {
			isProActive = pro_data.is_pro_active;
		}

		/*
       ==================================================
           Restricting users from selecting the same item
       ==================================================
       */
		$(document).on( 'click', '.submitbox #publish',function (e) {
			// Get selected values from both dropdowns
			var purchasedProductValue = $('#add_specific_product_to_purchase').val();
			var freeProductValue = $('#add_specific_product_for_free').val();

			// Check if there are any common selected values
			var commonValues = $(purchasedProductValue).filter(freeProductValue);

			var purchasedProductCheckedVal = $('input[name="customer_purchases"]:checked').val();
			var freeProductCheckedVal = $('input[name="customer_gets_as_free"]:checked').val();

			// If common values exist, show an alert
			if (purchasedProductCheckedVal == 'a_specific_product' || purchasedProductCheckedVal == 'a_combination_of_products' || purchasedProductCheckedVal == 'any_products_listed_below' && freeProductCheckedVal == 'a_specific_product' || freeProductCheckedVal == 'a_combination_of_products' || freeProductCheckedVal == 'any_products_listed_below' ) {
				let couponDiscountType = $('select[name="discount_type"]');
				let couponDiscountTypeVal = couponDiscountType.val();

				// Validate purchased and free product select2 field is empty
				if(couponDiscountTypeVal === "buy_x_get_x_bogo"){
					if(purchasedProductValue == "" || freeProductValue == ""){
						e.preventDefault();
						alert(__("You have to select at least a product for the purchase and free item","hex-coupon-for-woocommerce"));
					}
				}

				if (commonValues.length > 0 ) {
					e.preventDefault();
					alert(__('Can not select same item on both purchased and free product, chose specific and same product to do so!','hex-coupon-for-woocommerce'));
					$("#add_specific_product_for_free").focus();
				}
			}
			if (purchasedProductCheckedVal == 'a_specific_product' && freeProductCheckedVal == 'same_product_as_free') {
				if (!(commonValues.length > 0) ) {
					e.preventDefault();
					alert(__('You have select same product for both purchased and free options!','hex-coupon-for-woocommerce'));
					$("#add_specific_product_for_free").focus();
				}
			}
		});

		/*
       ========================================
           Mixed Code
       ========================================
       */
		// Code for restricting admin notice for being displayed
		$('#custom-admin-notice').on('click', function() {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'dismiss_custom_admin_notice'
				}
			});
		});

		// Dashboard quick links mechanism
		const hash = window.location.hash;

		if (hash === '#sharable_url_coupon_tab') {
			const tabElement = document.querySelector('.sharable_url_coupon_tab_tab');
			if (tabElement) {
				$('li.sharable_url_coupon_tab_tab a').trigger('click');
			}
		}

		if (hash === "#geographic_restriction_tab") {
			const tabElement = document.querySelector('.geographic_restriction_tab_tab');
			if (tabElement) {
				$('li.geographic_restriction_tab_tab a').trigger('click');
			}
		}

		if (hash === "#custom_coupon_tab") {
			const tabElement = document.querySelector('.custom_coupon_tab_tab');
			if (tabElement) {
				$('li.custom_coupon_tab_tab a').trigger('click');
			}
		}

		if (hash === "#general_coupon_data_bogo") {
			const tabElement = document.querySelector('.general_tab');
			if (tabElement) {
				$('#coupon_type').val('buy_x_get_x_bogo');
			}
		}

		if (hash === "#reset_usage") {
			const tabElement = document.querySelector('.usage_limit_tab');
			if (tabElement) {
				$('li.usage_limit_tab a').trigger('click');
				$('input#reset_usage_limit').trigger('click');
			}
		}

		/*
       ========================================
           General Tab
       ========================================
       */
		$(".hex__select2").select2({
			placeholder: function() {
				return $(this).data("placeholder");
			}
		});

		// Remove discounts type default select input field
		$(".discount_type_field").remove();

		const couponTypeField = $(".coupon_type_field");
		const couponAmountField = $(".coupon_amount_field");

		// Place coupon type select input field before the coupon amount input field
		couponTypeField.insertBefore(couponAmountField);

		// Place customer purchases radio buttons before the coupon type select input

		const customerPurchasesDiv = $(".customer_purchases");

		const addSpecificProductToPurchaseClass = $(".add_specific_product_to_purchase");

		const addSpecificProductToPurchaseId = $("#add_specific_product_to_purchase");

		customerPurchasesDiv.insertAfter(couponTypeField);

		const customerPurchasesField = $(".customer_purchases_field");

		// Place add a specific product to purchase select2 field after the customer purchases radio buttons
		addSpecificProductToPurchaseClass.insertAfter(customerPurchasesField);
		addSpecificProductToPurchaseId.insertAfter(".add_specific_product_to_purchase label");

		// Place the tooltip of add a specific product to purchase select2 field
		$(".add_specific_product_to_purchase_tooltip").insertAfter(".add_specific_product_to_purchase span.select2-container");

		const addCategoriesToPurchase = $(".add_categories_to_purchase");

		addCategoriesToPurchase.insertAfter(customerPurchasesField);
		$("select.add_categories_to_purchase").insertAfter(".add_categories_to_purchase label");
		$(".add_categories_to_purchase_tooltip").insertAfter(".add_categories_to_purchase span.select2-container");

		const customerGetsAsFreeClass = $(".customer_gets_as_free");

		// Place the customer gets as free after the customer purchases
		customerGetsAsFreeClass.insertAfter(customerPurchasesDiv);

		const addSpecificProductForFreeClass = $(".add_specific_product_for_free");
		const addSpecificProductForFreeID = $("#add_specific_product_for_free");

		addSpecificProductForFreeClass.insertAfter(".customer_gets_as_free_field");
		addSpecificProductForFreeID.insertAfter(".add_specific_product_for_free label");

		$(".add_specific_product_for_free_tooltip").insertAfter(".add_specific_product_for_free span.select2-container");

		const bogoDealCheckboxes = $(".bogo_deal_checkboxes");
		// Place the bogo deal checkboxes after the customer gets as free div
		bogoDealCheckboxes.insertAfter(customerGetsAsFreeClass);

		// Remove all other fields if BOGO is selected
		const discountTypeField = $("select[name^='discount_type']");

		const freeShippingField = $(".free_shipping_field");
		const expiryDateField = $(".expiry_date_field");
		const messageForCouponExpiryDateField = $(".message_for_coupon_expiry_date_field");
		const couponStartingDateField = $(".coupon_starting_date_field");
		const messageForCouponStartingDateField = $(".message_for_coupon_starting_date_field");

		discountTypeField.on("change",function (){
			if("buy_x_get_x_bogo" === this.value){
				customerPurchasesDiv.show()
				customerGetsAsFreeClass.show()
				bogoDealCheckboxes.show()

				couponAmountField.hide();
				freeShippingField.hide();
				expiryDateField.hide();
				messageForCouponExpiryDateField.hide();
				couponStartingDateField.hide();
				messageForCouponStartingDateField.hide();
				$("#selected_free_products").show();
			}
			else {
				customerPurchasesDiv.hide()
				customerGetsAsFreeClass.hide()
				bogoDealCheckboxes.hide()

				couponAmountField.show();
				freeShippingField.show();
				expiryDateField.show();
				messageForCouponExpiryDateField.show();
				couponStartingDateField.show();
				messageForCouponStartingDateField.show();
				$("#selected_free_products").hide();
			}
		});

		// perform on page load
		discountTypeField.trigger("change");

		// Don't allow more than one product selection on selecting a specific product from the customer purchases field
		const customerPurchases = $("input[name='customer_purchases']");
		const customerPurchasesChecked = $("input[name='customer_purchases']:checked");
		const customerGetsChecked = $("input[name='customer_gets_as_free']:checked");

		if(customerPurchasesChecked.val() === "a_specific_product"){
			$("input[name='customer_gets_as_free'][value='same_product_as_free']").parent().show();
		}else{
			$("input[name='customer_gets_as_free'][value='same_product_as_free']").parent().hide();
		}

		if(customerPurchasesChecked.val() === "product_categories"){
			addCategoriesToPurchase.show();
			addSpecificProductToPurchaseClass.hide();
		}else{
			addCategoriesToPurchase.hide();
			addSpecificProductToPurchaseClass.show();
		}

		// Show or hide product selection and category selection input fields if product categories type is selected
		customerPurchases.on("change",function(){
			if("product_categories" === $(this).val()){
				addCategoriesToPurchase.show();
				addSpecificProductToPurchaseClass.hide();
			}else {
				addCategoriesToPurchase.hide();
				addSpecificProductToPurchaseClass.show();
			}

			if("a_specific_product" === $(this).val()){
				addSpecificProductToPurchaseId.select2({
					maximumSelectionLength: 1, // Set maximum selection to 1
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
				$("input[name='customer_gets_as_free'][value='same_product_as_free']").parent().show();
			}else {
				addSpecificProductToPurchaseId.select2({
					maximumSelectionLength: 0, // Set maximum selection to unlimited
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
				$("input[name='customer_gets_as_free'][value='same_product_as_free']").parent().hide();
			}

			// Check if the radio button is checked and its value is 'a_specific_product'
			if ($(this).is(':checked') && $(this).val() === 'a_specific_product') {
				// Remove all 'select2-selection__choice' elements except the first one inside '.add_specific_product_to_purchase'
				$('.add_specific_product_to_purchase .select2-selection__choice').slice(1).remove();

				var selectedOption = $('select[name="add_specific_product_to_purchase"] option:selected:first');

				// Remove the selected attribute from all options except the first selected one
				$('#add_specific_product_to_purchase option:selected:not(:first)').removeAttr('selected');

				// Reset the selected option back to the first one
				selectedOption.prop('selected', true);

				$("#selected_purchased_products .product-item-whole").slice(1).remove();
			}
		});

		if(customerPurchasesChecked.val() === "a_specific_product"){
			addSpecificProductToPurchaseId.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 1 // Set maximum selection to 1
			});
		}else{
			addSpecificProductToPurchaseId.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 0 // Set maximum selection to unlimited
			});
		}

		// Don't allow more than one product selection on selecting a specific product from the customer gets as free field
		const customerGetsAsFree = $("input[name='customer_gets_as_free']");

		// Control number of result selection if a specific product type is selected
		customerGetsAsFree.on("change",function(){
			if("a_specific_product" === $(this).val()){
				addSpecificProductForFreeID.select2({
					maximumSelectionLength: 1, // Set maximum selection to 1
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
			}
			if("a_combination_of_products" === $(this).val()){
				addSpecificProductForFreeID.select2({
					maximumSelectionLength: 0, // Set maximum selection to unlimited
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
			}
			if("any_products_listed_below" === $(this).val()){
				addSpecificProductForFreeID.select2({
					maximumSelectionLength: 0, // Set maximum selection to unlimited
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
			}
			if("same_product_as_free" === $(this).val()){
				addSpecificProductForFreeID.select2({
					maximumSelectionLength: 1, // Set maximum selection to unlimited
					templateSelection: function (data, container) {
						// Add a 'value' attribute to the generated <li> elements
						$(container).attr('value', data.id);
						return data.text;
					}
				});
			}
		});

		if(customerGetsChecked.val() === "a_specific_product"){
			addSpecificProductForFreeID.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 1, // Set maximum selection to 1
			});
		}
		if(customerGetsChecked.val() === "a_combination_of_products"){
			addSpecificProductForFreeID.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 0, // Set maximum selection to 1
			});
		}
		if(customerGetsChecked.val() === "any_products_listed_below"){
			addSpecificProductForFreeID.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 0, // Set maximum selection to 1
			});
			// addSpecificProductForFreeClass.show();
		}
		if(customerGetsChecked.val() === "same_product_as_free"){
			addSpecificProductForFreeID.select2({
				templateSelection: function (data, container) {
					// Add a 'value' attribute to the generated <li> elements
					$(container).attr('value', data.id);
					return data.text;
				},
				maximumSelectionLength: 1, // Set maximum selection to 1
			});
		}

		// Remove all li of select2 button except the first one on selecting the 'a_specific_product' radio button
		$('input[name="customer_gets_as_free"]').on('change', function() {
			// Check if the radio button is checked and its value is 'a_specific_product'
			if (($(this).is(':checked') && $(this).val() === 'a_specific_product') || $(this).is(':checked') && $(this).val() === 'same_product_as_free') {
				// Remove all 'select2-selection__choice' elements except the first one inside '.add_specific_product_to_purchase'
				$('.customer_gets_as_free .select2-selection__choice').slice(1).remove();

				const selectedFreeOption = $('select[name="add_specific_product_to_purchase"] option:selected:first');

				// Remove the selected attribute from all options except the first selected one
				$('#add_specific_product_for_free option:selected:not(:first)').removeAttr('selected');

				// Reset the selected option back to the first one
				selectedFreeOption.prop('selected', true);

				$("#selected_free_products .product-item-whole").slice(1).remove();
			}
		});

		/*
       ========================================
           Usage Restriction
       ========================================
       */
		const cartConditionCB = $(".cart-condition").prop("outerHTML");
		$(".cart-condition").remove();
		$("select[name^='product_ids']").parent().after(cartConditionCB);

		$("select[name^='product_ids']").parent().remove();


		const cartConditionRadio = $(".apply_on_listed_product").prop("outerHTML");
		$(".apply_on_listed_product").remove();
		$("select[name^='exclude_product_ids']").parent().before(cartConditionRadio);

		$(".all_selected_products").insertAfter(".apply_on_listed_product");

		$(".all_selected_products_tooltip").insertAfter(".all_selected_products span.select2-container");

		const cartConditionCategory = $(".category-cart-condition").prop("outerHTML");
		$(".category-cart-condition").remove();
		$("select[name^='exclude_product_categories']").parent().before(cartConditionCategory);

		$("select[name^='product_categories']").parent().remove();

		$(".all_selected_categories").insertAfter(".category-cart-condition");

		$(".all_selected_categories_tooltip").insertAfter(".all_selected_categories span.select2-container");
		$(".selected_customer_group_tooltip").insertAfter(".selected_customer_group");
		$(".selected_individual_customer_tooltip").insertAfter(".selected_individual_customer");

		// place the 'selectedValuesContainer' container after the all_selected_products option grp input field
		$("#selectedValuesContainer").insertAfter(".all_selected_products .options_group");

		//** Show premium feature text clicking on min max input field **//

		// Select the input element
		let $productQuantityInput = $(".product-cart-condition");

		if(! isProActive){
			$(document).on('click','.product-cart-condition', function (){
				var message = "Upgrade to Pro enable this field!";
				showNotice(message, 'info');
			});
			$productQuantityInput.attr('readonly', 'readonly');

		} else{
			$productQuantityInput.attr('readonly');
		}

		// Remove product from select2 after clicking the cross icon
		$(document).on("click",".remove_product", function (){
			// get value from remove product element
			let value = $(this).attr("data-value");

			// now find this value inside select box element and option
			$(`select#all_selected_products option[value=${value}]`).removeAttr("selected");
			$(`select#all_selected_products`).trigger('change');

			// call this function for handlingAllProductSection task
			removeProductItemCard($(this));
		});

		// Remove product from below select2 option field after removing item from select2
		$(document).on("select2:unselect","#all_selected_products",function (e){
			var unselectedId = e.params.data.id;

			$('.product-item-whole#' + unselectedId).remove();
		});

		$(document).on("select2:select","#all_selected_products", function (e){
			let selectedProduct = $(e.params.data.element);

			handleSelectProductChange(selectedProduct);
		});

		function removeProductItemCard(element){
			element.closest(".product-item-whole").remove();
		}

		function handleSelectProductChange(product){

			const title = product.attr("title");
			const value = product.attr("value");

			// Adding the product when selecting
			let productItem =  `
				<div class="product-item-whole" id="${value}">
					<div class="product_title">${title}</div>
						<div class="product_min_max_main">
							<div class="product_min product-wrap">
								<div class="product_min product-wrap">

									<div class="product-wrap-inner">
										<p class="product-wrap-para">${__("min quantity", "hex-coupon-for-woocommerce")}</p>
										<input name="${convertTitleToName(title)}_min_quantity" class="product-quantity-input product-cart-condition" placeholder="Enter Qty" type="number" min="1">
									</div>
									<a href="javascript:void(0)" class="dashicons dashicons-no-alt remove_product" data-value="${value}" data-title="${title}"></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			`;

			$("#selectedValuesContainer").append(productItem);

			// Set readonly attribute for the new input if Pro is not active
			if (!isProActive) {
				$('.product-item-whole#' + value + ' .product-quantity-input').attr('readonly', 'readonly');
			}

		}

		function convertTitleToName(title){
			return title.replaceAll(" ","-").replaceAll("-","_").toLowerCase();
		}

		/*
       ========================================
           Usage Limits
       ========================================
       */

		// On page load
		const resetOptionValue = $("#reset_option_value").val(); // Get the value of 'reset_option_value' input

		// Find all p elements within the specified div
		const paragraphs = $(".reset_limit").find("p");

		// Loop through each p element and compare data-reset-value attribute
		paragraphs.each(function() {
			var dataResetValue = $(this).attr("data-reset-value");
			if (dataResetValue === resetOptionValue) {
				$(this).addClass("usage_limit_p_background");
			}
		});

		const resetLimitP = $(".reset_limit p");

		// On clicking the p element
		resetLimitP.click(function (){
			resetLimitP.removeClass("usage_limit_p_background");

			$(this).addClass("usage_limit_p_background");

			// Get the reset value from the data attribute
			const resetValue = $(this).data('reset-value');
			// Update the hidden input field value
			$('#reset_option_value').val(resetValue);
		});

		/*
       ========================================
           Geographic Restriction
       ========================================
       */

		$(".restricted_shipping_zones_tooltip").insertAfter(".restricted_shipping_zones span.select2-container");

		$(".restricted_countries_tooltip").insertAfter(".restricted_countries span.select2-container");

		/*
       ========================================
           Payment & Shipping method
       ========================================
       */
		// Place help tooltip after the select payment method input field
		$(".permitted_payment_methods_tooltip").insertAfter(".payment_and_shipping_method .options_group:first-child span.select2-container");

		// Place help tooltip after the select shipping method input field
		$(".permitted_shipping_methods_tooltip").insertAfter(".payment_and_shipping_method .options_group:last-child span.select2-container");

		/*
       ========================================
           Sharable URL Coupon
       ========================================
       */
		const redirectLinkVal = $('#redirect_link');

		// Show or hide the corresponding field based on the selected value on page loads
		if (redirectLinkVal.val() === 'redirect_to_custom_local_url') {
			$('.custom_local_url_field').show();
		} else {
			$('.custom_local_url_field').hide();
		}

		// Show or hide the corresponding field based on the selected value on change
		redirectLinkVal.change(function() {
			if ($(this).val() === 'redirect_to_custom_local_url') {
				$('.custom_local_url_field').show();
			} else {
				$('.custom_local_url_field').hide();
			}
		});


		// Add readonly property to the sharable_url input field
		$('#sharable_url').prop('readonly', true);

		// Copy url on clicking the copy url button
		$(".copy-sharable-url").click(function() {
			var text = $(".output-url-text").text(); // Get the text from the <p> element
			var tempInput = $('<input>'); // Create a temporary input element
			$("body").append(tempInput); // Append it to the body

			tempInput.val(text).select(); // Set its value and select the text
			document.execCommand("copy"); // Copy the selected text

			tempInput.remove(); // Remove the temporary input element
			const alertText = __( "URL copied to clipboard:", "hex-coupon-for-woocommerce" );
			alert(alertText + text); // Show an alert
		});

		/*
       ========================================
           Bogo in General Tab
       ========================================
       */

		// Function to show toast message
		function showNotice(message,type) {
			toastr.options = {
				"closeButton": true,
				"debug": false,
				"newestOnTop": false,
				"progressBar": true,
				"positionClass": "toast-top-center",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			}
			switch(type) {
				case 'info':
					toastr.info(message);
					break;
				case 'success':
					toastr.success(message);
					break;
				case 'warning':
					toastr.warning(message);
					break;
				case 'error':
					toastr.error(message);
					break;
				default:
					toastr.info(message);
			}
		}

		if(!isProActive){
			var any_products_listed_below = $('input[type="radio"][name="customer_purchases"][value="any_products_listed_below"]');
			var product_categories = $('input[type="radio"][name="customer_purchases"][value="product_categories"]');

			any_products_listed_below.prop('disabled', true);
			product_categories.prop('disabled', true);

			var message = __( 'To use this feature.', 'hex-coupon-for-woocommerce' ) + '<a href="https://hexcoupon.com/pricing" target="_blank">' + __('Upgrade to Pro','hex-coupon-for-woocommerce') + '</a>';
			//
			any_products_listed_below.parent().on('click', function() {
				showNotice(message, 'info');
			});

			product_categories.parent().on('click', function() {
				showNotice(message, 'info');
			});
		}


		// Show an alert box to the user if the Bogo purchased and free fields are not properly entered
		$(document).on('click', '.submitbox #publish', function (e) {
			let couponDiscountType = $('select[name="discount_type"]');
			let couponDiscountTypeVal = couponDiscountType.val();

			if("buy_x_get_x_bogo" != couponDiscountTypeVal){
				let inputValue = $('input[name="coupon_amount"]').val();
				if(inputValue === '' || inputValue <= 0){
					e.preventDefault();
					alert(__("You did not entered any amount in the coupon amount field", "hex-coupon-for-woocommerce"));
					$('input[name="coupon_amount"]').focus();
				}
			}

			function validateInput(bogoInputFieldClass) {
				let firstInvalidInput = null;

				$(bogoInputFieldClass).each(function () {
					var inputValue = $(this).val();

					// Check if the input is empty or not a number
					if ($.trim(inputValue) === '' || isNaN(inputValue) || parseInt(inputValue) <= 0) {
						// Prevent the default behavior of the WP post publish button
						e.preventDefault();

						// Display an alert for invalid input
						alert(__('Enter a valid number in the Bogo fields, equivalent or greater than 1', 'hex-coupon-for-woocommerce'));

						// Set the first invalid input field
						if (!firstInvalidInput) {
							firstInvalidInput = this;
						}
						// Focus on the first invalid number input field
						if (firstInvalidInput) {
							$(firstInvalidInput).focus();
						}
						invalidFields.push(this);
					}
				});
			}

			// Validate the specified input fields
			validateInput('.purchase');
			validateInput('.minimum');
			validateInput('.amount');

			// show alert if user leave a field empty
			let $checkbox = $("#apply_days_hours_of_week");

			if ($checkbox.prop("checked")) {
				let $timeInputs = $(".flatpickr-input").filter(function() {
					return $(this).css('display') !== 'none';
				});

				let $emptyInput = $timeInputs.filter(function() {
					return $(this).val() === "";
				}).first();

				if ($emptyInput.length > 0) {
					alert(__("Do not leave date and time empty.","hex-coupon-for-woocommerce"));
					$emptyInput.focus();
					e.preventDefault(); // Preventing form submission
				}
			}
		});

		// Place the div with an id of 'selected_purchased_products' after the 'add_specific_product_to_purchase options_group' div
		$("#selected_purchased_products").insertAfter(".add_specific_product_to_purchase .options_group");
		// Place the div with an id of 'selected_purchased_categories' aftr the 'add_categories_to_purchase options_group' div
		$("#selected_purchased_categories").insertAfter(".add_categories_to_purchase .options_group");

		$("#add_specific_product_to_purchase").on("select2:select",function (e){
			var selectedOption = $(e.params.data.element);

			// Get the title attribute value
			var titleAttribute = selectedOption.attr('title');

			var valueAttribute = selectedOption.attr('value');

			var convertedTitleName = convertTitleToName(titleAttribute);

			// Create a new product item
			var newPurchasedProductItem = $('<div class="product-item-whole" id="'+valueAttribute+'">' +
				'<div class="product_title">'+titleAttribute+'</div>' +
				'<div class="product_min_max_main">' +
				'<div class="product_min product-wrap">' +
				'<div class="product-wrap-inner">' +
				'<p class="product-wrap-para">Quantity</p>' +
				'<input class="product-quantity-input purchase" placeholder="Quantity" type="number" value="1" min="0" max="100" name="'+convertedTitleName+'-purchased_min_quantity">' +
				'</div>' +
				'<a href="javascript:void(0)" class="dashicons dashicons-no-alt remove_purchased_product" data-title="'+titleAttribute+'" data-value="'+valueAttribute+'"></a>' +
				'</div>' +
				'</div>' +
				'</div>');

			// Append the new product item to the selected_purchased_products div
			$('#selected_purchased_products').append(newPurchasedProductItem);
		});

		// Add 'add_specific_product_to_purchase' class to 'select2-selection__choice__remove' on page load
		$('.add_specific_product_to_purchase .select2-selection__choice__remove').addClass('add_specific_product_to_purchase');

		// Add 'add_specific_product_to_purchase' class to 'select2-selection__choice__remove' on select2 change event
		$("select#add_specific_product_to_purchase").change(function(){
			$(".select2-selection__choice__remove").addClass('add_specific_product_to_purchase');
		})

		// Add 'add_specific_product_for_free' class to 'select2-selection__choice__remove' on page load
		$('.add_specific_product_for_free .select2-selection__choice__remove').addClass('add_specific_product_for_free');

		// Add 'add_specific_product_for_free' class to 'select2-selection__choice__remove' on select2 change event
		$("select#add_specific_product_for_free").change(function(){
			$(".add_specific_product_for_free .select2-selection__choice__remove").addClass('add_specific_product_for_free');
		})

		$(document).on('click', 'span.add_specific_product_to_purchase',function(){
			var liValue = $(this).closest('li').attr('value');

			// Find the product-item-whole div with a matching a tag
			var matchingDiv = $('#selected_purchased_products .product-item-whole a[data-value="' + liValue + '"]').closest('.product-item-whole');

			// Remove the product-item-whole div
			matchingDiv.remove();
		});

		$(document).on('click', 'span.add_specific_product_for_free',function(){
			var liValue = $(this).closest('li').attr('value');

			// Find the product-item-whole div with a matching a tag
			var matchingDiv = $('#selected_free_products .product-item-whole a[data-value="' + liValue + '"]').closest('.product-item-whole');

			// Remove the product-item-whole div
			matchingDiv.remove();
		});

		$('#add_specific_product_to_purchase').on('select2:unselect', function(e) {
			// Get the ID of the unselected item
			var unselectedId = e.params.data.id;

			// Find and remove the div with the class 'product-item-whole' that has the ID of the unselected item
			$('.product-item-whole#' + unselectedId).remove();
		});



		$('#add_specific_product_for_free').on('select2:unselect', function(e) {
			// Get the ID of the unselected item
			var unselectedId = e.params.data.id;

			// Find and remove the div with the class 'product-item-whole' that has the ID of the unselected item
			$('.product-item-whole#' + unselectedId).remove();
		});

		$('#add_categories_to_purchase').on('select2:unselect', function(e) {
			// Get the ID of the unselected item
			var unselectedId = e.params.data.id;

			// Find and remove the div with the class 'product-item-whole' that has the ID of the unselected item
			$('.product-item-whole#' + unselectedId).remove();
		});

		// product purchase
		$(document).on("click",".remove_purchased_product", function (){
			let purchasedValue = $(this).attr("data-value");

			$('.add_specific_product_to_purchase li[value*="'+purchasedValue+'"]').remove();
			$('#add_specific_product_to_purchase option[value="'+purchasedValue+'"]').removeAttr("selected");

			// call this function for handlingAllProductSection task
			removeProductItemCard($(this));
		});

		// Purchased category
		$("#add_categories_to_purchase").select2({
			templateSelection: function (data, container) {
				// Add a 'value' attribute to the generated <li> elements
				$(container).attr('value', data.id);
				return data.text;
			}
		});

		$("#add_categories_to_purchase").on("select2:select",function (e){
			var selectedOption = $(e.params.data.element);

			var valueAttribute = selectedOption.attr('value');
			// Get the title attribute
			var CatTitleAttribute = selectedOption.attr('title');

			var convertedCatTitleName = convertTitleToName(CatTitleAttribute);

			// Create a new product item
			var newPurchasedCatProductItem = $('<div class="product-item-whole" id="'+valueAttribute+'">' +
				'<div class="product_title">'+CatTitleAttribute+'</div>' +
				'<div class="product_min_max_main">' +
				'<div class="product_min product-wrap">' +
				'<div class="product-wrap-inner">' +
				'<p class="product-wrap-para">Quantity</p>' +
				'<input class="product-quantity-input" placeholder="Quantity" type="number" value="1" name="'+convertedCatTitleName+'-purchased_category_min_quantity" min="0" max="100">' +
				'</div>' +
				'<a href="javascript:void(0)" class="dashicons dashicons-no-alt remove_purchased_category" data-title="'+CatTitleAttribute+'" data-value="'+valueAttribute+'"></a>' +
				'</div>' +
				'</div>' +
				'</div>');

			// Append the new product item to the selected_purchased_products div
			$('#selected_purchased_categories').append(newPurchasedCatProductItem);
		});

		// product category purchase
		$(document).on("click",".remove_purchased_category", function (){
			let purchasedCatValue = $(this).attr("data-value");

			$('li[value*="'+purchasedCatValue+'"]').remove();
			$('#add_categories_to_purchase option[value="'+purchasedCatValue+'"]').removeAttr("selected");

			// call this function for handlingAllProductSection task
			removeProductItemCard($(this));
		});

		// Free Products
		$("#add_specific_product_for_free").on("select2:select",function (e){
			var selectedOption = $(e.params.data.element);

			let freeTitleAttribute = selectedOption.attr('title');
			var freeValueAttribute = selectedOption.attr('value');

			var convertedFreeTitleName = convertTitleToName(freeTitleAttribute);

			var newPurchasedFreeProductItem = `
				  <div class="product-item-whole" id="${freeValueAttribute}">
					<div class="product_title">${freeTitleAttribute}</div>
					<div class="product_min_max_main">
					  <div class="product_min product-wrap">
						<div class="product-wrap-inner">
						  <p class="product-wrap-para">Quantity</p>
						  <input class="product-quantity-input minimum" placeholder="Quantity" type="number" value="1" name="${convertedFreeTitleName}-free_product_quantity" min="0" max="100">
						</div>
					  </div>
					  <div class="product_min product-wrap">
						<div class="product-wrap-inner">
						  <p class="product-wrap-para">Discount Type</p>
						  <select name="${convertedFreeTitleName}-hexcoupon_bogo_discount_type" id="hexcoupon_bogo_discount_type">
							<option value="percent">Percent (%)</option>
							<option value="fixed">Fixed</option>
						  </select>
						</div>
						<div class="product-wrap-inner">
						  <p class="product-wrap-para">Amount</p>
						  <input class="product-quantity-input amount" placeholder="Amount" type="number" value="0" name="${convertedFreeTitleName}-free_amount" min="0" max="100">
						</div>
						<a href="javascript:void(0)" class="dashicons dashicons-no-alt remove_free_product" data-title="${freeTitleAttribute}" data-value="${freeValueAttribute}"></a>
					  </div>
					</div>
				  </div>
				`;

			// Append the new product item to the selected_purchased_products div
			$('#selected_free_products').append(newPurchasedFreeProductItem);
		});

		$(document).on("click",".remove_free_product", function (){
			let purchasedFreeValue = $(this).attr("data-value");

			$('.add_specific_product_for_free li[value*="'+purchasedFreeValue+'"]').remove();
			$('#add_specific_product_for_free option[value="'+purchasedFreeValue+'"]').removeAttr("selected");

			// call this function for handlingAllProductSection task
			removeProductItemCard($(this));
		});

		/*
       ========================================
           	Days & Hours in General Tab
       ========================================
       */
		// Make this area blur to show Upgrade Pro notice
		let $daysAndHoursTab = $("#days_and_hours_tab");
		let $upgradeNotice = $("#upgrade_notice");

		if(!isProActive){
			$daysAndHoursTab.hover(
				function() {
					$daysAndHoursTab.addClass("blur");
					$upgradeNotice.css("display", "block");
				},
				function() {
					$daysAndHoursTab.removeClass("blur");
					$upgradeNotice.css("display", "none");
				}
			);
		} else {
			$upgradeNotice.css("display","none");
		}

		// Other tasks
		const applyDaysHoursOfWeek = $("#apply_days_hours_of_week");
		const totalHoursCountSaturday = $("#total_hours_count_saturday");
		const totalHoursCountSunday = $("#total_hours_count_sunday");
		const totalHoursCountMonday = $("#total_hours_count_monday");
		const totalHoursCountTuesday = $("#total_hours_count_tuesday");
		const totalHoursCountWednesday = $("#total_hours_count_wednesday");
		const totalHoursCountThursday = $("#total_hours_count_thursday");
		const totalHoursCountFriday = $("#total_hours_count_friday");

		// Show hide days and hours on page load
		if(! applyDaysHoursOfWeek.is(":checked")) {
			totalHoursCountSaturday.val('0');
			totalHoursCountSunday.val('0');
			totalHoursCountMonday.val('0');
			totalHoursCountTuesday.val('0');
			totalHoursCountWednesday.val('0');
			totalHoursCountThursday.val('0');
			totalHoursCountFriday.val('0');
		}

		// Show hide products cart condition on page load
		if ($("#apply_cart_condition_for_customer_on_products").is(":checked")) {
			$(".apply_on_listed_product").show();
			$(".all_selected_products").show();
		} else {
			$(".apply_on_listed_product").hide();
			$(".all_selected_products").hide();
		}

		// Show hide categories cart condition on page load
		if ($("#apply_cart_condition_for_customer_on_categories").is(":checked")) {
			$(".all_selected_categories").show();
		} else {
			$(".all_selected_categories").hide();
		}

		// Show hide group of customer fields on page load
		if ($("#allowed_or_restricted_customer_group").is(":checked")) {
			$(".allowed_group_of_customer").show();
		} else {
			$(".allowed_group_of_customer").hide();
		}

		// Show hide individual of customer fields on page load
		if ($("#allowed_or_restricted_individual_customer").is(":checked")) {
			$(".allowed_individual_customer").show();
		} else {
			$(".allowed_individual_customer").hide();
		}

		// Show hide days and hours and apply days & hour on the basis of clicking the discount_type select input
		// Get the currently selected option's value
		var selectedValue = $(discountTypeField).val();

		// Show hide fields in cart product condition on the basis of clicking the checkbox
		$("#apply_cart_condition_for_customer_on_products").on("change", function () {
			if ($(this).is(":checked")) {
				$(".apply_on_listed_product").show();
				$(".all_selected_products").show();
			} else {
				$(".apply_on_listed_product").hide();
				$(".all_selected_products").hide();
			}
		});

		// Show hide fields in cart category condition on the basis of clicking the checkbox
		$("#apply_cart_condition_for_customer_on_categories").on("change", function () {
			if ($(this).is(":checked")) {
				$(".all_selected_categories").show();
			} else {
				$(".all_selected_categories").hide();
			}
		});

		// Show hide all fields of allowed group of customer on the basis of clicking the checkbox
		$("#allowed_or_restricted_customer_group").on("change", function () {
			if ($(this).is(":checked")) {
				$(".allowed_group_of_customer").show();
			} else {
				$(".allowed_group_of_customer").hide();
			}
		});

		// Show hide all fields of allowed individual of customer on the basis of clicking the checkbox
		$("#allowed_or_restricted_individual_customer").on("change", function () {
			if ($(this).is(":checked")) {
				$(".allowed_individual_customer").show();
			} else {
				$(".allowed_individual_customer").hide();
			}
		});

		// Function for enabling flatpickr
		function flatPicker(day) {
			$(".time-picker-"+day).flatpickr({
				enableTime: true,
				noCalendar: true,
				dateFormat: "H:i",
			});
		}

		// Function to show hide different fields
		function showHideDayFields(dayFullName,dayShortName) {
			if($("#coupon_apply_on_"+dayFullName).is(":checked")) {
				$(".time-picker-"+dayFullName).show();
				$("#"+dayShortName+"_add_more_hours").show();
				$(".cross_hour_"+dayFullName).show();
				$(".input_separator_"+dayFullName).show();
				$("#"+dayShortName+"_deactivated_text").hide();
			} else {
				$(".time-picker-"+dayFullName).hide();
				$("#"+dayShortName+"_add_more_hours").hide();
				$(".cross_hour_"+dayFullName).hide();
				$(".input_separator_"+dayFullName).hide();
				$("#"+dayShortName+"_deactivated_text").show();
				$("#total_hours_count_"+dayFullName).val('0');
				$(".add_more_hours_"+dayShortName+"_pro_text").hide();
				$("p."+dayFullName+" .appededItem.first-input").remove();
			}
		}

		/*
	   ========================================
		   Days and Hours fields for Saturday
	   ========================================
	   */

		// Enable flatpicker on saturday default input fields
		flatPicker('saturday');

		// Show hide saturday fields
		showHideDayFields('saturday','sat');

		if (!isProActive){
			// Show hide saturday hours on the basis of clicking the saturday checkbox
			$('#coupon_apply_on_saturday').prop('disabled', true);
		}

		$("#coupon_apply_on_saturday").on("change", function () {
			showHideDayFields('saturday', 'sat');
		});

		// Function to add input field dynamically for saturday
		function addSaturdayInputField(){
			let totalHoursCountSaturdayVal = totalHoursCountSaturday.val();

			totalHoursCountSaturdayVal++;

			let appendedElementSaturday = "<span class='appededItem first-input'><input type='text' class='time-picker-saturday' name='sat_coupon_start_time_" + totalHoursCountSaturdayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_saturday'>-</span><input type='text' class='time-picker-saturday coupon_expiry_time' name='sat_coupon_expiry_time_" + totalHoursCountSaturdayVal + "' value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_saturday cross-hour'></a></span>";

			$(".saturday").append(appendedElementSaturday);
			totalHoursCountSaturday.val(totalHoursCountSaturdayVal);
			flatPicker('saturday');
		}

		// Add input field dynamically for saturday when the button is clicked
		$(document).on("click", "#sat_add_more_hours", addSaturdayInputField);

		// Remove each input item of saturday after clicking the cross icon.
		$(document).on("click", ".cross_hour_saturday", function () {
			let totalHoursCountSaturdayVal = totalHoursCountSaturday.val();
			$(this).closest("span").remove();

			if (totalHoursCountSaturdayVal > 0) {
				totalHoursCountSaturdayVal--;
			}

			totalHoursCountSaturday.val(totalHoursCountSaturdayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Sunday
	   ========================================
	   */
		// Enable flatpicker on sunday default input fields
		flatPicker('sunday');

		// Show hide sunday fields
		showHideDayFields('sunday','sun');

		// Show hide sunday hours on the basis of clicking the sunday checkbox
		$("#coupon_apply_on_sunday").on("change", function () {
			showHideDayFields('sunday', 'sun');
		});

		// Function to add input field dynamically for sunday
		function addSundayInputField(){
			let totalHoursCountSundayVal = totalHoursCountSunday.val();

			totalHoursCountSundayVal++;

			let appendedElementSunday = "<span class='appededItem first-input'><input type='text' class='time-picker-sunday' name='sun_coupon_start_time_" + totalHoursCountSundayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_sunday'>-</span><input type='text' class='time-picker-sunday coupon_expiry_time' name='sun_coupon_expiry_time_" + totalHoursCountSundayVal + "' value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_sunday cross-hour'></a></span>";

			$(".sunday").append(appendedElementSunday);
			totalHoursCountSunday.val(totalHoursCountSundayVal);
			flatPicker('sunday');
		}

		// Add input field dynamically for sunday when the button is clicked
		$(document).on("click", "#sun_add_more_hours", addSundayInputField);

		// Remove each input item of sunday after clicking the cross icon.
		$(document).on("click", ".cross_hour_sunday", function () {
			let totalHoursCountSundayVal = totalHoursCountSunday.val();
			$(this).closest("span").remove();

			if (totalHoursCountSundayVal > 0) {
				totalHoursCountSundayVal--;
			}

			totalHoursCountSunday.val(totalHoursCountSundayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Monday
	   ========================================
	   */

		// Enable flatpicker on monday default input fields
		flatPicker('monday');

		// Show hide monday fields
		showHideDayFields('monday','mon');

		// Show hide monday hours on the basis of clicking the monday checkbox
		$("#coupon_apply_on_monday").on("change", function () {
			showHideDayFields('monday', 'mon');
		});

		// Function to add input field dynamically for monday
		function addMondayInputField(){
			let totalHoursCountMondayVal = totalHoursCountMonday.val();

			totalHoursCountMondayVal++;

			let appendedElementMonday = "<span class='appededItem first-input'><input type='text' class='time-picker-monday' name='mon_coupon_start_time_" + totalHoursCountMondayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_monday'>-</span><input type='text' class='time-picker-monday coupon_expiry_time' name='mon_coupon_expiry_time_" + totalHoursCountMondayVal + "' value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_monday cross-hour'></a></span>";

			$(".monday").append(appendedElementMonday);
			totalHoursCountMonday.val(totalHoursCountMondayVal);
			flatPicker('monday');
		}

		// Add input field dynamically for monday when the button is clicked
		$(document).on("click", "#mon_add_more_hours", addMondayInputField);

		// Remove each input item of monday after clicking the cross icon.
		$(document).on("click", ".cross_hour_monday", function () {
			let totalHoursCountMondayVal = totalHoursCountMonday.val();
			$(this).closest("span").remove();

			if (totalHoursCountMondayVal > 0) {
				totalHoursCountMondayVal--;
			}

			totalHoursCountMonday.val(totalHoursCountMondayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Tuesday
	   ========================================
	   */

		// Enable flatpicker on tuesday default input fields
		flatPicker('tuesday');

		// Show hide tuesday fields
		showHideDayFields('tuesday','tue');

		// Show hide tuesday hours on the basis of clicking the tuesday checkbox
		$("#coupon_apply_on_tuesday").on("change", function () {
			showHideDayFields('tuesday', 'tue');
		});

		// Function to add input field dynamically for tuesday
		function addTuesdayInputField(){
			let totalHoursCountTuesdayVal = totalHoursCountTuesday.val();

			totalHoursCountTuesdayVal++;

			let appendedElementTuesday = "<span class='appededItem first-input'><input type='text' class='time-picker-tuesday' name='tue_coupon_start_time_" + totalHoursCountTuesdayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_tuesday'>-</span><input type='text' class='time-picker-tuesday coupon_expiry_time' name='tue_coupon_expiry_time_" + totalHoursCountTuesdayVal + "' value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_tuesday cross-hour'></a></span>";

			$(".tuesday").append(appendedElementTuesday);
			totalHoursCountTuesday.val(totalHoursCountTuesdayVal);
			flatPicker('tuesday');
		}

		// Add input field dynamically for tuesday when the button is clicked
		$(document).on("click", "#tue_add_more_hours", addTuesdayInputField);

		// Remove each input item of tuesday after clicking the cross icon.
		$(document).on("click", ".cross_hour_tuesday", function () {
			let totalHoursCountTuesdayVal = totalHoursCountTuesday.val();
			$(this).closest("span").remove();

			if (totalHoursCountTuesdayVal > 0) {
				totalHoursCountTuesdayVal--;
			}

			totalHoursCountTuesday.val(totalHoursCountTuesdayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Wednesday
	   ========================================
	   */

		// Enable flatpicker on wednesday default input fields
		flatPicker('wednesday');

		// Show hide wednesday fields
		showHideDayFields('wednesday','wed');

		// Show hide wednesday hours on the basis of clicking the wednesday checkbox
		$("#coupon_apply_on_wednesday").on("change", function () {
			showHideDayFields('wednesday', 'wed');
		});

		// Function to add input field dynamically for wednesday
		function addWednesdayInputField(){
			let totalHoursCountWednesdayVal = totalHoursCountWednesday.val();

			totalHoursCountWednesdayVal++;

			let appendedElementWednesday = "<span class='appededItem first-input'><input type='text' class='time-picker-wednesday' name='wed_coupon_start_time_" + totalHoursCountWednesdayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_wednesday'>-</span><input type='text' class='time-picker-wednesday coupon_expiry_time' name='wed_coupon_expiry_time_" + totalHoursCountWednesdayVal + "'  value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_wednesday cross-hour'></a></span>";

			$(".wednesday").append(appendedElementWednesday);
			totalHoursCountWednesday.val(totalHoursCountWednesdayVal);
			flatPicker('wednesday');
		}

		// Add input field dynamically for wednesday when the button is clicked
		$(document).on("click", "#wed_add_more_hours", addWednesdayInputField);

		// Remove each input item of wednesday after clicking the cross icon.
		$(document).on("click", ".cross_hour_wednesday", function () {
			let totalHoursCountWednesdayVal = totalHoursCountWednesday.val();
			$(this).closest("span").remove();

			if (totalHoursCountWednesdayVal > 0) {
				totalHoursCountWednesdayVal--;
			}

			totalHoursCountWednesday.val(totalHoursCountWednesdayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Thursday
	   ========================================
	   */

		// Enable flatpicker on thursday default input fields
		flatPicker('thursday');

		// Show hide thursday fields
		showHideDayFields('thursday','thu');

		// Show hide thursday hours on the basis of clicking the thursday checkbox
		$("#coupon_apply_on_thursday").on("change", function () {
			showHideDayFields('thursday', 'thu');
		});

		// Function to add input field dynamically for thursday
		function addThursdayInputField(){
			let totalHoursCountThursdayVal = totalHoursCountThursday.val();

			totalHoursCountThursdayVal++;

			let appendedElementThursday = "<span class='appededItem first-input'><input type='text' class='time-picker-thursday' name='thu_coupon_start_time_" + totalHoursCountThursdayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_thursday'>-</span><input type='text' class='time-picker-thursday coupon_expiry_time' name='thu_coupon_expiry_time_" + totalHoursCountThursdayVal + "'  value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_thursday cross-hour'></a></span>";

			$(".thursday").append(appendedElementThursday);
			totalHoursCountThursday.val(totalHoursCountThursdayVal);
			flatPicker('thursday');
		}

		// Add input field dynamically for thursday when the button is clicked
		$(document).on("click", "#thu_add_more_hours", addThursdayInputField);

		// Remove each input item of thursday after clicking the cross icon.
		$(document).on("click", ".cross_hour_thursday", function () {
			let totalHoursCountThursdayVal = totalHoursCountThursday.val();
			$(this).closest("span").remove();

			if (totalHoursCountThursdayVal > 0) {
				totalHoursCountThursdayVal--;
			}

			totalHoursCountThursday.val(totalHoursCountThursdayVal);
		});

		/*
	   ========================================
		   Days and Hours fields for Friday
	   ========================================
	   */

		// Enable flatpicker on friday default input fields
		flatPicker('friday');

		// Show hide friday fields
		showHideDayFields('friday','fri');

		// Show hide friday hours on the basis of clicking the friday checkbox
		$("#coupon_apply_on_friday").on("change", function () {
			showHideDayFields('friday', 'fri');
		});

		// Function to add input field dynamically for friday
		function addFridayInputField(){
			let totalHoursCountFridayVal = totalHoursCountFriday.val();

			totalHoursCountFridayVal++;

			let appendedElementFriday = "<span class='appededItem first-input'><input type='text' class='time-picker-friday' name='fri_coupon_start_time_" + totalHoursCountFridayVal + "' id='coupon_start_time' value='' placeholder='HH:MM'><span class='input_separator_friday'>-</span><input type='text' class='time-picker-friday coupon_expiry_time' name='fri_coupon_expiry_time_" + totalHoursCountFridayVal + "'  value='' placeholder='HH:MM'><a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_friday cross-hour'></a></span>";

			$(".friday").append(appendedElementFriday);
			totalHoursCountFriday.val(totalHoursCountFridayVal);
			flatPicker('friday');
		}

		// Add input field dynamically for friday when the button is clicked
		$(document).on("click", "#fri_add_more_hours", addFridayInputField);

		// Remove each input item of thursday after clicking the cross icon.
		$(document).on("click", ".cross_hour_friday", function () {
			let totalHoursCountFridayVal = totalHoursCountFriday.val();
			$(this).closest("span").remove();

			if (totalHoursCountFridayVal > 0) {
				totalHoursCountFridayVal--;
			}

			totalHoursCountFriday.val(totalHoursCountFridayVal);
		});

		$('.toggle-input').on('change', function() {
			// Set the value of the current checkbox to 'yes' if checked, or '' if unchecked
			$(this).val(this.checked ? 'yes' : '');
		});

	});
})(jQuery);
