<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin\Bogo;

use HexCoupon\App\Controllers\BaseController;
use hexcoupon\app\Controllers\WooCommerce\Admin\CouponGeneralTabController;
use HexCoupon\App\Core\Lib\SingleTon;

class HexcouponBogoController extends BaseController
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method registercustom_fee_for_bogo_deal
	 * @return void
	 * @since 1.0.0
	 * Register hook that is needed to validate the coupon according to the starting date.
	 */
	public function register()
	{
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'add_free_items_to_cart' ] );
		add_filter( 'woocommerce_cart_item_price', [ $this, 'replace_price_amount_with_free_text' ], 10, 2 );
		add_action( 'woocommerce_cart_totals_before_order_total', [ $this, 'show_free_items_name_before_total_price' ], 10 );
		add_filter( 'woocommerce_cart_product_subtotal', [ $this, 'alter_product_subtotal_in_cart_for_bogo' ], 10, 4 );
		add_action( 'woocommerce_cart_calculate_fees', [ $this, 'custom_fee_for_bogo_deal' ], 10, 1 );
		add_filter( 'woocommerce_cart_subtotal', [ $this, 'deduct_bogo_discount_amount_from_subtotal' ] , 10, 3 );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method deduct_bogo_discount_amount_from_subtotal
	 * @return string
	 * Deduct bogo discount amount from the cart subtotal
	 */
	public function deduct_bogo_discount_amount_from_subtotal( $cart_subtotal, $compound, $obj )
	{
		$price_to_be_deducted = $this->custom_fee_for_bogo_deal( $obj );

		$final_subtotal_price = wc_price( (float)$obj->get_subtotal() - (float)$price_to_be_deducted );

		return $final_subtotal_price;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method remove_product_in_case_of_any_product_listed_below
	 * @return mixed
	 * Remove product from cart if more than one product is selected in the cart from the list.
	 */
	public function remove_product_in_case_of_any_product_listed_below( $wc_cart, $selected_products_as_free )
	{
		// Get the cart contents
		$cart_contents = $wc_cart->get_cart();

		$product_ids = [];

		foreach ( $cart_contents as $cart_item ) {
			$product_ids[] = $cart_item['product_id'];
		}

		$common_elements  = array_intersect( $product_ids, $selected_products_as_free );

		// removing the first element from the array
		array_shift( $common_elements );

		// removing the products from the cart if more than one product is added to the cart
		$this->remove_cart_product( $common_elements );

		add_action( 'woocommerce_before_cart', [ $this, 'cart_custom_error_message_two' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method custom_fee_for_bogo_deal
	 * @return int
	 * Add discount fee based on bogo deal
	 */
	public function custom_fee_for_bogo_deal( $cart )
	{
		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // Get the id of applied coupon

		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id );

		$free_items_id = ! empty( $all_meta_values['add_specific_product_for_free'] ) ? $all_meta_values['add_specific_product_for_free'] : [];
		$customer_gets_as_free = ! empty( $all_meta_values['customer_gets_as_free'] ) ? $all_meta_values['customer_gets_as_free'] : [];

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

		$total_subtotal = 0;

		// Loop through cart items to calculate total subtotal
		foreach ( $cart->get_cart() as $cart_item ) {
			if ( in_array( $cart_item['product_id'], $free_items_id ) ) {
				$product_title = $this->convert_and_replace_unnecessary_string( $cart_item['product_id'] );
				$product_free_quantity = get_post_meta( $coupon_id, $product_title . '-free_product_quantity', true );
				$product_free_amount = get_post_meta( $coupon_id, $product_title . '-free_amount', true );
				$product_discount_type = get_post_meta( $coupon_id, $product_title . '-hexcoupon_bogo_discount_type', true );
				if ( 'fixed' === $product_discount_type && $cart_item['quantity'] >= $product_free_quantity ) {
					if ( $product_free_amount > $cart_item['data']->get_price() ) {
						$product_free_amount = $cart_item['data']->get_price();
					}
					if ( $product_free_amount <= 0 ) {
						$product_free_amount = 0;
					}

					$total_subtotal += $product_free_quantity * $product_free_amount;
				}
				if ( 'percent' === $product_discount_type && $cart_item['quantity'] >= $product_free_quantity ) {
					if ( $product_free_amount > 100 ) {
						$product_free_amount = 100;
					}
					if ( $product_free_amount <= 0 ) {
						$product_free_amount = 0;
					}
					$total_subtotal += ($product_free_amount / 100) * $cart_item['data']->get_price() * $product_free_quantity;
				}
			}
		}

		$coupon_discount_type = get_post_meta( $coupon_id, 'discount_type', true );

		if ( $cart->get_applied_coupons() && 'buy_x_get_x_bogo' === $coupon_discount_type )
			$cart->add_fee( __( 'Total Bogo Discount', 'hex-coupon-for-woocommerce' ), -$total_subtotal );

		return $total_subtotal;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method alter_product_subtotal_in_cart_for_bogo
	 * @return string
	 * Show product new subtotal in cart according to the Bogo discounts
	 */
	public function alter_product_subtotal_in_cart_for_bogo( $product_subtotal, $product, $quantity, $cart )
	{
		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // Get the id of applied coupon

		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id );

		$customer_gets_as_free = ! empty( $all_meta_values['customer_gets_as_free'] ) ? $all_meta_values['customer_gets_as_free'] : '';

		$main_product_id = ! empty( $all_meta_values['add_specific_product_to_purchase'] ) ? $all_meta_values['add_specific_product_to_purchase'] : [];
		$free_items_id = ! empty( $all_meta_values['add_specific_product_for_free'] ) ? $all_meta_values['add_specific_product_for_free'] : [];

		$main_product_array_to_string = implode( '', $main_product_id );

		$main_product_converted_string = $this->convert_and_replace_unnecessary_string( $main_product_array_to_string );

		$main_product_min_quantity = get_post_meta( $coupon_id, $main_product_converted_string . '-purchased_min_quantity', true );

		if ( 'a_specific_product' === $customer_gets_as_free || 'a_combination_of_products' === $customer_gets_as_free || 'any_products_listed_below' === $customer_gets_as_free || 'same_product_as_free' === $customer_gets_as_free ) {
			if ( in_array( $product->get_id(), $free_items_id ) ) {
				$string_to_be_replaced = [ ' ', '_' ];

				$product_title = get_the_title( $product->get_id() );
				$converted_string = strtolower( str_replace( $string_to_be_replaced, '-', $product_title ) );

				$free_quantity = get_post_meta( $coupon_id, $converted_string . '-free_product_quantity', true );

				$free_amount = get_post_meta( $coupon_id, $converted_string . '-free_amount', true );
				$free_amount = ! empty( $free_amount ) ? $free_amount : 0;
				$hexcoupon_bogo_discount_type = get_post_meta( $coupon_id, $converted_string . '-hexcoupon_bogo_discount_type', true );

				$original_subtotal = floatval($product->get_price() * $quantity );

				$custom_subtotal = $original_subtotal;

				if ( 'fixed' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					if ( $free_amount > $product->get_price() ) {
						$free_amount = $product->get_price();
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}
					$custom_subtotal = $original_subtotal - ( $free_amount * $quantity );
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					if ( $free_amount > 100 ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}
					$custom_subtotal = $original_subtotal * ( ( 100 - $free_amount ) / 100 );
				}

				if ( 'fixed' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) && $quantity >= $main_product_min_quantity ) {
					if ( $free_amount > $product->get_price() ) {
						$free_amount = $product->get_price();
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}
					$custom_subtotal = $original_subtotal - ( $free_amount * $free_quantity );
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) && $quantity >= $main_product_min_quantity ) {
					if ( $free_amount > 100 ) {
						$free_amount = 100 * $free_quantity;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0 * $free_quantity;
					}

					$free_amount = $free_amount * $free_quantity;

					// Calculate the amount to subtract
					$amount_to_substract = ( $free_amount / 100 ) * $product->get_price();

					// Subtract the amount
					$custom_subtotal = $original_subtotal - $amount_to_substract;
				}

				// Format the custom subtotal for display
				$formatted_subtotal = wc_price( $custom_subtotal );

				return $formatted_subtotal;
			}
		}

		// Other than the Bogo free products, prices of all other products will be the same.
		return $product_subtotal;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method remove_cart_product
	 * @return void
	 * remove cart items/products from cart page
	 */
	public function remove_cart_product( $free_item_id )
	{
		foreach ( $free_item_id as $free_item_single ) {
			$free_single_key = WC()->cart->generate_cart_id( $free_item_single );
			WC()->cart->remove_cart_item( $free_single_key );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_product_categories_id_in_cart
	 * @return array
	 * Get product categories id from the cart page.
	 */
	public function get_product_categories_id_in_cart()
	{
		// Get the WooCommerce cart
		$cart = WC()->cart;

		// Initialize an empty array to store category IDs and their occurrences
		$category_occurrences = [];

		// Loop through cart items
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			// Get product ID
			$product_id = $cart_item['product_id'];

			// Get product quantity
			$quantity = $cart_item['quantity'];

			// Get product categories
			$categories = get_the_terms( $product_id, 'product_cat' );

			// Loop through categories
			if ( $categories && ! is_wp_error( $categories ) ) {
				foreach ( $categories as $category ) {
					// Get category ID
					$category_id = $category->term_id;

					// Check if category ID already exists in the array
					if ( array_key_exists( $category_id, $category_occurrences ) ) {
						// Add quantity to existing category occurrence
						$category_occurrences[ $category_id ] += $quantity;
					} else {
						// Add new category occurrence
						$category_occurrences[ $category_id ] = $quantity;
					}
				}
			}
		}

		return $category_occurrences;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method update_quantity_after_updating_cart
	 * @return void
	 * Updating product quantity after clicking on update cart button
	 */
	public function update_quantity_after_updating_cart( $coupon_id, $free_item_id, $main_product_id, $customer_purchases )
	{
		// Initialize the cart object
		$wc_cart = WC()->cart;

		$main_product_ids = $main_product_id;
		$main_product_single_title_lower_case = '';
		$main_product_id = '';

		foreach ( $main_product_ids as $main_single_id ) {
			$main_product_id = $main_single_id;
			$main_product_single_title_lower_case = $this->convert_and_replace_unnecessary_string( $main_single_id );
		}

		// get main purchased product minimum quantity
		$main_product_min_purchased_quantity = get_post_meta( $coupon_id, $main_product_single_title_lower_case . '-purchased_min_quantity', true );
		$main_product_min_purchased_quantity = ! empty( $main_product_min_purchased_quantity ) ? $main_product_min_purchased_quantity : 1;

		$cart_item_quantity = 0;

		foreach ( $wc_cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( $main_product_id  == $cart_item['product_id'] ) {
				$cart_item_quantity = $cart_item['quantity'];
				break;
			}
		}

		if ( 'a_specific_product' === $customer_purchases || 'a_combination_of_products' === $customer_purchases || 'any_products_listed_below' == $customer_purchases ) {
			if ( $cart_item_quantity >= $main_product_min_purchased_quantity ) {
				foreach ( $free_item_id as $free_single_id ) {
					$free_single_product_key = $wc_cart->generate_cart_id( $free_single_id );
					// Find and search if the free product exists in the cart page.
					if ( $wc_cart->find_product_in_cart( $free_single_product_key ) ) {
						$free_product_title_lowercase = $this->convert_and_replace_unnecessary_string( $free_single_id );
						$free_product_quantity = get_post_meta( $coupon_id, $free_product_title_lowercase . '-free_product_quantity', true );
						// Executes the below code, if the cart item quantity is equals to the main product min purchased quantity
						if ( $cart_item_quantity >= $main_product_min_purchased_quantity ) {
							if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
								return;

							if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
								return;

							$customer_gets = $free_product_quantity;
							$wc_cart->set_quantity( $free_single_product_key, $customer_gets );
							break;
						}
					}
				}
			}
			add_action( 'woocommerce_before_cart', [ $this, 'cart_custom_success_message' ] );
		}

		// If customer purchases from 'product_categories'
		if ( 'product_categories' === $customer_purchases ) {
			foreach ( $free_item_id as $free_single_id ) {
				$free_single_product_key = $wc_cart->generate_cart_id( $free_single_id );
				// Find and search if the free product exists in the cart page.
				if ( $wc_cart->find_product_in_cart( $free_single_product_key ) ) {
					$free_product_title_lowercase = $this->convert_and_replace_unnecessary_string( $free_single_id );
					$free_product_quantity = get_post_meta( $coupon_id, $free_product_title_lowercase . '-free_product_quantity', true );

					if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) )
						return;

					if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
						return;

					$customer_gets = $free_product_quantity;
					$wc_cart->set_quantity( $free_single_product_key, $customer_gets );
					break;
				}
			}
			add_action( 'woocommerce_before_cart', [ $this, 'cart_custom_success_message' ] );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method cart_custom_success_message
	 * @return void
	 * @since 1.0.0
	 * Show success message after adding free product in the cart.
	 */
	public function cart_custom_success_message()
	{
		$message = __( 'Free Bogo products added successfully!', 'hex-coupon-for-woocommerce' );

		wc_print_notice( $message );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method cart_custom_error_message
	 * @return void
	 * @since 1.0.0
	 * Show error message if customer does not have enough main item to get the bogo deal.
	 */
	public function cart_custom_error_message()
	{
		$message = __( 'You do not have enough item or enough quantity to avail the Bogo offer.', 'hex-coupon-for-woocommerce' );

		wc_print_notice( $message, 'error' );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method cart_custom_error_message_two
	 * @return void
	 * @since 1.0.0
	 * Show error message if customer tries to add more than one product from the list below.
	 */
	public function cart_custom_error_message_two()
	{
		$message = __( 'You can not add more than one product from the below list.', 'hex-coupon-for-woocommerce' );

		wc_print_notice( $message, 'error' );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method convert_and_replace_unnecessary_string
	 * @param int $post_id
	 * @return string
	 * @since 1.0.0
	 * Replace space ' ', and hyphen '-' from the string with '_' underscore, and convert the uppercase letters to lowercase.
	 */
	public function convert_and_replace_unnecessary_string( $post_id )
	{
		$string = get_the_title( $post_id );
		$string_to_be_replaced = [ ' ', '-' ];
		$replaced_string = strtolower( str_replace( $string_to_be_replaced, '-', $string ) );

		return $replaced_string;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method custom_content_below_coupon_button
	 * @return void
	 * Display the free items below the apply coupon button.
	 */
	public function custom_content_below_coupon_button()
	{
		global $product;

		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // get the id of applied coupon from the cart page

		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id );

		// get all the products ids that has to be purchased
		$selected_products_as_to_be_purchased = $all_meta_values['add_specific_product_to_purchase'];

		// get the product id's of all free items that customer will get
		$selected_products_as_free = $all_meta_values['add_specific_product_for_free'];

		$main_product_min_quantity = 1;

		foreach ( $selected_products_as_to_be_purchased as $single_main_product ) {
			$main_product_id = $single_main_product;
			$main_product_title = $this->convert_and_replace_unnecessary_string( $single_main_product );
			$main_product_min_quantity = get_post_meta( $coupon_id, $main_product_title . '-purchased_min_quantity', true );
		}

		$cart_product_ids = [];

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_product_ids[] = $cart_item['product_id']; // assign all ids of products in the cart in an array
		}

		$matched_product_id = array_intersect( $selected_products_as_to_be_purchased, $cart_product_ids );

		$cart_product_quantity = 1;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( ! empty( $matched_product_id[0] ) && $matched_product_id[0] == $cart_item['product_id'] ) {
				$cart_product_quantity = $cart_item['quantity'];
			}
		}

		// Checking if we are on the cart page
		if ( ( $cart_product_quantity >= $main_product_min_quantity ) && is_cart() ) {
			echo '<div class="hexcoupon_select_free_item">';
			// Add content for the free items
			echo '<h3>' . esc_html__( 'Select any product from below list', 'hex-coupon-for-woocommerce' ) . '</h3>';

			foreach ( $selected_products_as_free as $product_id ) {
				$free_product_title = $this->convert_and_replace_unnecessary_string( $product_id );

				$free_product_quantity = get_post_meta( $coupon_id, $free_product_title . '-free_product_quantity', true );
				$free_product_quantity = ! empty( $free_product_quantity ) ? $free_product_quantity : 1;

				// Output each product
				$product = wc_get_product( $product_id );
				if ( $product ) {
					echo '<div class="custom-product">';
					echo '<a href="' . get_permalink( $product_id ) . '">' . $product->get_image() . '</a>';
					echo '<h3 class="has-text-align-center wp-block-post-title has-medium-font-size"><a href="' . get_permalink ( $product_id ) . '">' . $product->get_name() . '</a></h3>';
					echo '<p class="price has-font-size has-small-font-size has-text-align-center">' . $product->get_price_html() . ' (Qty: '. $free_product_quantity .')</p>';
					echo '<form class="cart" action="" method="post">';
					echo '<input type="hidden" name="quantity" value="' . esc_attr( $free_product_quantity ) . '">';
					echo '<div class="has-text-align-center"><button type="submit" name="add-to-cart" value="' . esc_attr( $product_id ) . '" class="button wp-element-button wp-block-button__link">' . esc_html__( 'Add to Cart', 'hex-coupon-for-woocommerce' ) . '</button></div>';
					echo '</form>';
					echo '</div>';
				}
			}

			echo '</div>';
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method custom_content_below_coupon_button_for_categories
	 * @return void
	 * Display the free items below the apply coupon button.
	 */
	public function custom_content_below_coupon_button_for_categories()
	{
		global $product;

		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // get the id of applied coupon from the cart page

		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id );

		$customer_purchases = $all_meta_values['customer_purchases'];

		// get the product id's of all free items that customer will get
		$selected_products_as_free = $all_meta_values['add_specific_product_for_free'];

		// Checking if we are on the cart page
		if ( 'product_categories' === $customer_purchases && is_cart() ) {
			echo '<div class="hexcoupon_select_free_item">';
			// Add content for the free items
			echo '<h3>' . esc_html__( 'Select any product from below list', 'hex-coupon-for-woocommerce' ) . '</h3>';

			foreach ( $selected_products_as_free as $product_id ) {
				$free_product_title = $this->convert_and_replace_unnecessary_string( $product_id );

				$free_product_quantity = get_post_meta( $coupon_id, $free_product_title . '-free_product_quantity', true );
				$free_product_quantity = ! empty( $free_product_quantity ) ? $free_product_quantity : 1;

				// Output each product
				$product = wc_get_product( $product_id );
				if ( $product ) {
					echo '<div class="custom-product">';
					echo '<a href="' . get_permalink( $product_id ) . '">' . $product->get_image() . '</a>';
					echo '<h3 class="has-text-align-center wp-block-post-title has-medium-font-size"><a href="' . get_permalink ( $product_id ) . '">' . $product->get_name() . '</a></h3>';
					echo '<p class="price has-font-size has-small-font-size has-text-align-center">' . $product->get_price_html() . '</p>';
					echo '<form class="cart" action="" method="post">';
					echo '<input type="hidden" name="quantity" value="' . esc_attr( $free_product_quantity ) . '">';
					echo '<div class="has-text-align-center"><button type="submit" name="add-to-cart" value="' . esc_attr( $product_id ) . '" class="button wp-element-button wp-block-button__link">' . esc_html__( 'Add to Cart', 'hex-coupon-for-woocommerce' ) . '</button></div>';
					echo '</form>';
					echo '</div>';
				}
			}

			echo '</div>';
		}
	}

	/**
	 * @throws \Exception
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_free_items_to_cart
	 * @return array
	 * Add free items to the cart page.
	 */
	public function add_free_items_to_cart()
	{
		$wc_cart = WC()->cart;

		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // get the id of applied coupon from the cart page

		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id );

		$customer_purchases = $all_meta_values['customer_purchases'];

		$selected_products_to_purchase = $all_meta_values['add_specific_product_to_purchase']; // get purchasable selected product
		$selected_products_as_free = $all_meta_values['add_specific_product_for_free']; // get free selected product

		$customer_gets_as_free = $all_meta_values['customer_gets_as_free']; // get meta value of customer gets as free

		$add_categories_to_purchase = ! empty( $all_meta_values['add_categories_to_purchase'] ) ? $all_meta_values['add_categories_to_purchase'] : []; // get the meta-value of coupon purchasable product categories

		// Product IDs
		$main_product_id = ! empty( $selected_products_to_purchase ) ? $selected_products_to_purchase : []; // product ids that has to be existed in the cart to apply BOGO deals
		$free_item_id = ! empty( $selected_products_as_free ) ? $selected_products_as_free : []; // ids of products that customer will get as free

		// Initializing '$cart_product_ids' variable for all cart products ids
		$cart_product_ids = [];

		// Assigning all product ids of cart page into the '$cart_product_ids' variable
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_product_ids[] = $cart_item['product_id']; // assign all ids of products in the cart in an array
		}

		$main_product_in_cart = false; // '$main_product_in_cart' is false if there are no products in the cart that needs to be there to apply BOGO deals.

		$main_product_single_title = '';
		$main_product_single_id = 0;
		$cart_item_quantity = 0;

		// Check if the cart has the exact or any product from the list that the admin has selected to purchase
		if ( 'a_specific_product' === $customer_purchases || 'any_products_listed_below' === $customer_purchases ) {
			foreach ( $main_product_id as $main_product_single ) {
				if ( in_array( $main_product_single, $cart_product_ids ) ) {
					$main_product_single_id = $main_product_single;

					$main_product_single_title = get_the_title( $main_product_single );

					$main_product_in_cart = true; // if the cart has the product it assigns value of '$main_product_in_cart' to 'true'
					break;
				}
			}
		}

		// Check if the cart has all the exact products that the admin has selected to purchase
		if ( 'a_combination_of_products' === $customer_purchases ) {
			foreach ( $main_product_id as $main_product_single ) {
				if ( in_array( $main_product_single, $cart_product_ids ) ) {
					$main_product_in_cart = true; // if the cart does not have the product it assigns value of '$main_product_in_cart' to 'false'
				}
				else {
					$main_product_in_cart = false; // else it becomes true
					break;
				}
			}
		}

		// Define strings that need to be replaced in the title
		$string_to_be_replaced = [ ' ', '-' ];

		$main_product_single_title_lower_case = str_replace( $string_to_be_replaced, '-', strtolower( $main_product_single_title ) );

		// get main purchased product minimum quantity
		$main_product_min_purchased_quantity = get_post_meta( $coupon_id, $main_product_single_title_lower_case.'-purchased_min_quantity', true );
		$main_product_min_purchased_quantity = ! empty( $main_product_min_purchased_quantity ) ? $main_product_min_purchased_quantity : 1;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( $main_product_single_id  == $cart_item['product_id'] ) {
				$cart_item_quantity = $cart_item['quantity'];
				break;
			}
		}

		if ( 'product_categories' === $customer_purchases ) {
			$string_tobe_converted = [ ' ', '-' ];

			$cart_product_cat_occurances = $this->get_product_categories_id_in_cart();

			foreach ( $add_categories_to_purchase as $category_single ) {
				if ( array_key_exists( $category_single, $cart_product_cat_occurances ) ) {
					$category = get_term( $category_single, 'product_cat' );

					if ( $category && ! is_wp_error( $category ) ) {
						$category_name = $category->name;
						$category_converted_name = str_replace( $string_tobe_converted, '-', strtolower( $category_name ) );
						$category_purchased_min_category = get_post_meta( $coupon_id, $category_converted_name . '-purchased_category_min_quantity', true );

						$cart_cat_quantity = $cart_product_cat_occurances[$category_single];

						if ( $cart_cat_quantity >= $category_purchased_min_category ) {
							$main_product_in_cart = true;
						}
					}
				}
			}
		}

		// Add free item to cart if the main product is in the cart
		if ( $main_product_in_cart ) {
			// Add product in the case of customer purchases 'a specific product' and getting 'a specific product' as free
			GetSpecificProductForSpecificProduct::getInstance()->specific_products_against_specific_products( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $string_to_be_replaced, $coupon_id, $main_product_single_id, $cart_product_ids );

			// add product in the case of customer purchases 'a specific product' and getting 'same product as free'
			GetSameProductForSpecificProduct::getInstance()->specific_products_against_same_product( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $coupon_id, $wc_cart, $main_product_id );

			// add product in the case of customer purchases 'a specific product' and getting 'a combination of products'
			GetCombinationOfProductForSpecificProduct::getInstance()->specific_products_against_a_combination_of_products( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $coupon_id, $wc_cart, $main_product_id );

			// Add product in the case of customer purchases 'a specific product' and gets 'any products listed from a list' as free
			GetProductFromListForSpecificProduct::getInstance()->specific_products_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $main_product_min_purchased_quantity, $cart_item_quantity, $free_item_id, $wc_cart, $selected_products_as_free, $coupon_id, $main_product_id );

			// Add product in the case of customer  purchases 'a combination of products' and gets 'a specific product' as free
			GetSpecificProductForCombinationOfProduct::getInstance()->combination_of_product_against_specific_product( $customer_purchases, $customer_gets_as_free, $main_product_id, $coupon_id, $free_item_id, $wc_cart );


			// Add product in the case of customer  purchases 'a combination of products' and gets 'a combination product' as free
			GetCombinationOfProductForCombinationOfProduct::getInstance()->combination_of_product_against_combination_of_product( $customer_purchases, $customer_gets_as_free, $main_product_id, $coupon_id, $free_item_id, $wc_cart );

			// Add product in the case of customer  purchases 'a combination of products' and gets 'any_products_listed_below' as free
			GetProductFromListForCombinationOfProduct::getInstance()->combination_of_product_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $main_product_id, $coupon_id, $free_item_id, $wc_cart, $selected_products_as_free );

			// Add product in the case of customer  purchases 'any_products_listed_below' and gets 'a_specific_product' as free
			GetSpecificProductForAnyListedProduct::getInstance()->any_product_listed_below_against_specific_product( $customer_purchases, $customer_gets_as_free, $wc_cart, $main_product_id, $coupon_id, $free_item_id );


			// Add product in the case of customer  purchases 'any_products_listed_below' and gets 'a_combination_of_products' as free
			GetCombinationOfProductForAnyListedProduct::getInstance()->any_product_listed_below_against_a_combination_of_product( $customer_purchases, $customer_gets_as_free, $wc_cart, $main_product_id, $coupon_id, $free_item_id );


			// Add product in the case of customer  purchases 'any_products_listed_below' and gets 'any_products_listed_below' as free
			GetAnyListedProductForAnyListedProduct::getInstance()->any_product_listed_below_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $wc_cart, $main_product_id, $coupon_id, $free_item_id, $selected_products_as_free );

			// Add product in the case of customer  purchases from 'product_categories' and gets 'a_specific_product' and 'a_combination_of_products' as free
			GetSpecificProductAndCombinationOfProductForProductCategory::getInstance()->product_categories_against_specific_product_and_combination_of_product( $customer_purchases, $customer_gets_as_free, $free_item_id, $wc_cart, $coupon_id );

			// Add product in the case of customer  purchases 'product_categories' and gets 'any_products_listed_below' as free
			GetAnyProductFromListForProductCategory::getInstance()->product_categories_against_any_product_listed_below( $customer_purchases, $customer_gets_as_free, $coupon_id, $free_item_id, $main_product_id, $wc_cart, $selected_products_as_free );
		}
		// Remove all free items from the cart if the main product does not exist in the cart
		else {
			$this->remove_cart_product( $free_item_id );
		}

		return $free_item_id;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method replace_price_amount_with_free_text
	 * @param string $price
	 * @param array $cart_item
	 * @return string
	 * Replace price amount with 'free (BOGO Deal)' text in the price column of product in the cart page.
	 */
	public function replace_price_amount_with_free_text( $price, $cart_item )
	{
		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id(); // Get the id of applied coupon

		$customer_purchases = get_post_meta( $coupon_id, 'customer_purchases', true );
		$customer_gets_as_free = get_post_meta( $coupon_id, 'customer_gets_as_free', true );

		$free_items_id = get_post_meta( $coupon_id, 'add_specific_product_for_free', true );
		$free_items_id = ! empty( $free_items_id ) ? $free_items_id : [];
		$main_product_id = get_post_meta( $coupon_id, 'add_specific_product_to_purchase', true );
		$main_product_id = ! empty( $main_product_id ) ? $main_product_id : [];

		$main_product_array_to_string = implode( '', $main_product_id );

		$main_product_converted_string = $this->convert_and_replace_unnecessary_string( $main_product_array_to_string );

		$product_min_quantity = get_post_meta( $coupon_id, $main_product_converted_string . '-purchased_min_quantity', true );

		$item_price = wc_get_price_excluding_tax( $cart_item['data'] );

		if ( 'a_specific_product' === $customer_gets_as_free || 'a_combination_of_products' === $customer_gets_as_free || 'any_products_listed_below' === $customer_gets_as_free ) {
			if ( in_array( $cart_item['product_id'], $free_items_id ) ) {
				$string_to_be_replaced = [ ' ', '-' ];

				$product_title = get_the_title( $cart_item['product_id'] );
				$converted_string = strtolower( str_replace( $string_to_be_replaced, '-', $product_title ) );

				$free_amount = get_post_meta( $coupon_id, $converted_string . '-free_amount', true );
				$free_amount = ! empty( $free_amount ) ? $free_amount : 0;
				$hexcoupon_bogo_discount_type = get_post_meta( $coupon_id, $converted_string . '-hexcoupon_bogo_discount_type', true );

				$text = __( '(BOGO Deal) <br>', 'hex-coupon-for-woocommerce' );

				$allowed_tag = [
					'br' => []
				];

				if ( 'fixed' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					// Get the free product quantity
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > $item_price ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $cart_item['quantity'] * $free_amount;

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] . 'x) - (' . $product_free_quantity . 'x * ' . $free_amount . ') = (-' . wc_price( $free_amount * $product_free_quantity ) . ')';
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > 100 ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $item_price * ( ( 100 - $free_amount ) / 100 ) * $cart_item['quantity'];

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] .'x) - (' . $product_free_quantity . 'x * ' . $free_amount .'%) = (-' . wc_price( ( $free_amount / 100 ) * ( $cart_item['data']->get_price() * $product_free_quantity ) ) .')';
				}
			}
		}

		if ( 'same_product_as_free' === $customer_gets_as_free ) {
			if ( in_array( $cart_item['product_id'], $free_items_id ) && $cart_item['quantity'] >= $product_min_quantity ) {
				$string_to_be_replaced = [ ' ', '-' ];

				$product_title = get_the_title( $cart_item['product_id'] );
				$converted_string = strtolower( str_replace( $string_to_be_replaced, '-', $product_title ) );

				$free_amount = get_post_meta( $coupon_id, $converted_string . '-free_amount', true );
				$free_amount = ! empty( $free_amount ) ? $free_amount : 0;
				$hexcoupon_bogo_discount_type = get_post_meta( $coupon_id, $converted_string . '-hexcoupon_bogo_discount_type', true );

				$text = __( '(BOGO Deal) <br>', 'hex-coupon-for-woocommerce' );

				$allowed_tag = [
					'br' => []
				];

				if ( 'fixed' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) ) {
					// Get the free product quantity
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > $item_price ) {
						$free_amount = $item_price;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $cart_item['quantity'] * $free_amount;

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] . 'x) - ' . '(' . $product_free_quantity . 'x * ' . $free_amount . ') = (-' . wc_price( $free_amount * $product_free_quantity ) . ')';
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) ) {
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > 100 ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] .'x) - (' . $product_free_quantity . 'x * ' . $free_amount .'%) = (-' . wc_price( ( $free_amount / 100 ) * ( $cart_item['data']->get_price() * $product_free_quantity ) ) .')';
				}
			}
		}

		if ( 'product_categories' === $customer_purchases ) {
			if ( in_array( $cart_item['product_id'], $free_items_id ) ) {
				$string_to_be_replaced = [ ' ', '-' ];

				$product_title = get_the_title( $cart_item['product_id'] );
				$converted_string = strtolower( str_replace( $string_to_be_replaced, '-', $product_title ) );

				$free_amount = get_post_meta( $coupon_id, $converted_string . '-free_amount', true );
				$free_amount = ! empty( $free_amount ) ? $free_amount : 0;
				$hexcoupon_bogo_discount_type = get_post_meta( $coupon_id, $converted_string . '-hexcoupon_bogo_discount_type', true );

				$text = __( '(BOGO Deal) <br>', 'hex-coupon-for-woocommerce' );

				$allowed_tag = [
					'br' => []
				];

				if ( 'fixed' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					// Get the free product quantity
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > $item_price ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $cart_item['quantity'] * $free_amount;

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] . 'x) - (' . $product_free_quantity . 'x * ' . $free_amount . ') = (-' . wc_price( $free_amount * $product_free_quantity ) . ')';
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && array_diff( $main_product_id, $free_items_id ) ) {
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > 100 ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $item_price * ( ( 100 - $free_amount ) / 100 ) * $cart_item['quantity'];

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] .'x) - (' . $product_free_quantity . 'x * ' . $free_amount .'%) = (-' . wc_price( ( $free_amount / 100 ) * ( $cart_item['data']->get_price() * $product_free_quantity ) ) .')';
				}

				if ( 'fixed' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) ) {
					// Get the free product quantity
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > $item_price ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $cart_item['quantity'] * $free_amount;

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] . 'x) - (' . $product_free_quantity . 'x * ' . $free_amount . ') = (-' . wc_price( $free_amount * $product_free_quantity ) . ')';
				}
				if ( 'percent' === $hexcoupon_bogo_discount_type && ! array_diff( $main_product_id, $free_items_id ) ) {
					$product_free_quantity = get_post_meta( $coupon_id,  $converted_string . '-free_product_quantity', true );

					if ( $free_amount > 100 ) {
						$free_amount = 100;
					}
					if ( $free_amount <= 0 ) {
						$free_amount = 0;
					}

					$item_price = $item_price * ( ( 100 - $free_amount ) / 100 ) * $cart_item['quantity'];

					$price = '<span class="free_bogo_deal_text">' . wp_kses( $text, $allowed_tag ) . '</span>' . ' (' . $price . ' * ' . $cart_item['quantity'] .'x) - (' . $product_free_quantity . 'x * ' . $free_amount .'%) = (-' . wc_price( ( $free_amount / 100 ) * ( $cart_item['data']->get_price() * $product_free_quantity ) ) .')';
				}
			}
		}

		return $price;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_free_items_name_before_total_price
	 * @return void
	 * Show BOGO deals free items name in the cart page.
	 */
	public function show_free_items_name_before_total_price()
	{
		$coupon_id = CouponGeneralTabController::getInstance()->coupon_id();
		$all_meta_values = CouponGeneralTabController::getInstance()->get_all_post_meta( $coupon_id ); // get all free items id's

		$free_items_id = ! empty( $all_meta_values['add_specific_product_for_free'] ) ? $all_meta_values['add_specific_product_for_free'] : [];

		// Displays free item names
		$free_items = '';

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( in_array( $cart_item['product_id'], $free_items_id ) ) {
				$free_items .= esc_html( $cart_item['data']->get_name() ) . ', ';
			}
		}

		if ( ! empty( $free_items ) ) {
			$free_items = rtrim( $free_items, ', ' ); // removing ', ' from the end of the right side of the string
			echo '<tr class="free-items-row">';
			echo '<th>' . esc_html__( 'Free/Discounted Items', 'hex-coupon-for-woocommerce' ) . '</th><td class="free-items-name">' . esc_html( $free_items ) . '</td>';
			echo '</tr>';
		}
	}
}
