<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\FormHelpers;
use HexCoupon\App\Core\Helpers\RenderHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponSingleGeneralTab
{
	use singleton;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return void
	 * Registers all hooks that are needed.
	 */
	public function register()
	{
		add_action( 'woocommerce_coupon_options', [ $this, 'add_coupon_extra_fields' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_categories
	 * @return array
	 * Show all the categories of the product.
	 */
	private function show_categories()
	{
		$all_product_categories = [];

		$product_categories = get_categories(
			[
				'taxonomy' => 'product_cat',
				'orderby' => 'name',
			]
		);

		foreach ( $product_categories as $category ) {
			$all_product_categories[$category->term_id] = $category->name;
		}

		return $all_product_categories;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method expiry_date_message_field
	 * @return void
	 * Add coupon expiry date message textarea field.
	 */
	private function expiry_date_message_field()
	{
		global $post;

		$discount_type = get_post_meta( $post->ID, 'discount_type', true );
		$discount_type = ! empty( $discount_type ) ? $discount_type : '';

		// Adding coupon type select input field
		woocommerce_wp_select( [
			'class' => 'select short',
			'label' => esc_html__( 'Coupon type', 'hex-coupon-for-woocommerce' ),
			'id' => 'coupon_type',
			'name' => 'discount_type',
			'options' => [
				'percent' => 'Percentage discount',
				'fixed_cart' => 'Fixed cart discount',
				'fixed_product' => 'Fixed product discount',
				'buy_x_get_x_bogo' => 'Buy X Get X Product (BOGO)',
			],
			'value' => $discount_type,
		] );

		$customer_purchases = get_post_meta( $post->ID, 'customer_purchases', true );
		$customer_purchases = ! empty( $customer_purchases ) ? $customer_purchases : '';

		// Adding customer purchases radio buttons field
		echo '<div class="options_group customer_purchases">';

		woocommerce_wp_radio(
			[
				'id' => 'customer_purchases',
				'label' => esc_html__( 'Customer purchases', 'hex-coupon-for-woocommerce' ),
				'options' => [
					'a_specific_product' => esc_html__( 'A specific product', 'hex-coupon-for-woocommerce' ),
					'a_combination_of_products' => esc_html__( 'A combination of products', 'hex-coupon-for-woocommerce' ),
					'any_products_listed_below' => esc_html__( 'Any products listed below', 'hex-coupon-for-woocommerce' ),
					'product_categories' => esc_html__( 'Any product from categories', 'hex-coupon-for-woocommerce' ),
				],
				'value' => ! empty( $customer_purchases ) ? $customer_purchases : 'a_specific_product',
			]
		);

		echo '</div>';

		// Adding a select2 field to add specific product
		$add_specific_product_to_purchase = get_post_meta( get_the_ID(),'add_specific_product_to_purchase',true );

		$output ='<div class="add_specific_product_to_purchase">';

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Add product/products', 'hex-coupon-for-woocommerce' ),
			'name' => 'add_specific_product_to_purchase',
			'value' => $add_specific_product_to_purchase,
			'type' => 'select',
			'options' => CouponSingleUsageRestriction::getInstance()->show_all_products(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'add_specific_product_to_purchase',
			'id' => 'add_specific_product_to_purchase',
			'placeholder' => __( 'Search for specific product', 'hex-coupon-for-woocommerce' )
		] );

		echo '<span class="add_specific_product_to_purchase_tooltip">'.wc_help_tip( esc_html__( 'Add the product that customer buys.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo '<div id="selected_purchased_products">';
		if ( ! empty( $add_specific_product_to_purchase ) ) {
			foreach ( $add_specific_product_to_purchase as $value ) {
				$purchased_product_title = get_the_title( $value );

				$converted_purchased_product_title = strtolower( str_replace( ' ', '-', $purchased_product_title ) );

				$purchased_min_quantity = get_post_meta( $post->ID, $converted_purchased_product_title . '-purchased_min_quantity', true );

				echo '<div class="product-item-whole">';
				echo '<div class="product_title">'.$purchased_product_title.'</div>';
				?>
				<div class="product_min_max_main">
					<div class='product_min product-wrap'>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'Quantity', 'hex-coupon-for-woocommerce' ); ?></p>
							<input class="product-quantity-input purchase" placeholder='Quantity' type='number' value="<?php echo esc_attr( $purchased_min_quantity ); ?>" name="<?php echo esc_attr( $converted_purchased_product_title );?>-purchased_min_quantity" min="0" max="100">
						</div>
						<a href="javascript:void(0)" class='dashicons dashicons-no-alt remove_purchased_product' data-title="<?php echo esc_attr( $purchased_product_title ); ?>" data-value="<?php echo esc_attr( $value ); ?>"></a>
					</div>
				</div>
				<?php
				echo '</div>';
			}
		}
		echo '</div>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';


		// Adding a select2 field to add categories
		$add_categories_to_purchase = get_post_meta( get_the_ID(),'add_categories_to_purchase',true );

		$output ='<div class="add_categories_to_purchase">';

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Add categories', 'hex-coupon-for-woocommerce' ),
			'name' => 'add_categories_to_purchase',
			'value' => $add_categories_to_purchase,
			'type' => 'select',
			'options' => $this->show_categories(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'add_categories_to_purchase',
			'id' => 'add_categories_to_purchase',
			'placeholder' => __( 'Search for categories', 'hex-coupon-for-woocommerce' )
		] );

		echo '<span class="add_categories_to_purchase_tooltip">'.wc_help_tip( esc_html__( 'Add categories that customer need to buy from.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo '<div id="selected_purchased_categories">';

		if ( ! empty( $add_categories_to_purchase ) ) {
			foreach ( $add_categories_to_purchase as $value ) {
				$purchased_product_category_title = get_the_category_by_ID( $value );

				$converted_purchased_product_category_title = strtolower( str_replace( ' ', '-', $purchased_product_category_title ) );

				$category_purchased_min_quantity = get_post_meta( $post->ID, $converted_purchased_product_category_title . '-purchased_category_min_quantity', true );

				echo '<div class="product-item-whole">';
				echo '<div class="product_title">'.$purchased_product_category_title.'</div>';
				?>
				<div class="product_min_max_main">
					<div class='product_min product-wrap'>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'Quantity', 'hex-coupon-for-woocommerce' ); ?></p>
							<input class="product-quantity-input" placeholder='Quantity' type='number' value="<?php echo esc_attr( $category_purchased_min_quantity ); ?>" name="<?php echo esc_attr( $converted_purchased_product_category_title );?>-purchased_category_min_quantity" min="0" max="100">
						</div>
						<a href="javascript:void(0)" class='dashicons dashicons-no-alt remove_purchased_category' data-value="<?php echo esc_attr( $value ); ?>" data-title="<?php echo esc_attr( $purchased_product_category_title ); ?>"></a>
					</div>
				</div>
				<?php
				echo '</div>';
			}
		}

		echo '</div>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';


		// Adding customer gets as free radio buttons
		$customer_gets_as_free = get_post_meta( $post->ID, 'customer_gets_as_free', true );
		$customer_gets_as_free = ! empty( $customer_gets_as_free ) ? $customer_gets_as_free : '';

		echo '<div class="options_group customer_gets_as_free">';

		woocommerce_wp_radio(
			[
				'id' => 'customer_gets_as_free',
				'label' => esc_html__( 'Customer gets', 'hex-coupon-for-woocommerce' ),
				'options' => [
					'a_specific_product' => esc_html__( 'A specific product', 'hex-coupon-for-woocommerce' ),
					'a_combination_of_products' => esc_html__( 'A combination of products', 'hex-coupon-for-woocommerce' ),
					'any_products_listed_below' => esc_html__( 'Any products listed below', 'hex-coupon-for-woocommerce' ),
					'same_product_as_free' => esc_html__( 'Same product as free', 'hex-coupon-for-woocommerce' ),
				],
				'value' => ! empty( $customer_gets_as_free ) ? $customer_gets_as_free : 'a_specific_product',
			]
		);

		echo '</div>';

		// Adding a select2 field to add a specific product
		$add_specific_product_for_free = get_post_meta( get_the_ID(),'add_specific_product_for_free',true );

		$output ='<div class="add_specific_product_for_free">';

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Add product/products', 'hex-coupon-for-woocommerce' ),
			'name' => 'add_specific_product_for_free',
			'value' => $add_specific_product_for_free,
			'type' => 'select',
			'options' => CouponSingleUsageRestriction::getInstance()->show_all_products(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'add_specific_product_for_free',
			'id' => 'add_specific_product_for_free',
			'placeholder' => __( 'Search for specific product', 'hex-coupon-for-woocommerce' )
		] );

		echo '<span class="add_specific_product_for_free_tooltip">'.wc_help_tip( esc_html__( 'Add the product that customer will get for free.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo '<div id="selected_free_products">';
		if( ! empty( $add_specific_product_for_free ) ) {
			foreach ( $add_specific_product_for_free as $value ) {
				$free_product_title = get_the_title( $value );

				$converted_free_product_title = strtolower( str_replace( ' ', '-', $free_product_title ) );

				$free_product_quantity = get_post_meta( $post->ID, $converted_free_product_title . '-free_product_quantity', true );

				$free_product_amount = get_post_meta( $post->ID, $converted_free_product_title . '-free_amount', true );

				echo '<div class="product-item-whole">';
				echo '<div class="product_title">'.$free_product_title.'</div>';
				?>
				<div class="product_min_max_main">
					<div class='product_min product-wrap'>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'Quantity', 'hex-coupon-for-woocommerce' ); ?></p>
							<input class="product-quantity-input minimum" placeholder='Quantity' type='number' value="<?php echo esc_attr( $free_product_quantity ); ?>" name="<?php echo esc_attr( $converted_free_product_title );?>-free_product_quantity" min="0" max="100">
						</div>
					</div>
					<div class='product_min product-wrap'>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'Discount Type', 'hex-coupon-for-woocommerce' ); ?></p>
							<?php
							$saved_discount_type = get_post_meta($post->ID, $converted_free_product_title . '-hexcoupon_bogo_discount_type', true);

							// Default value if not set
							$saved_discount_type = $saved_discount_type ? $saved_discount_type : 'percent';
							?>
							<select name="<?php echo esc_attr( $converted_free_product_title );?>-hexcoupon_bogo_discount_type" id="hexcoupon_bogo_discount_type">
								<option value="percent" <?php if ( 'percent' === $saved_discount_type ) echo esc_attr( 'selected' );?>>Percent (%)</option>
								<option value="fixed" <?php if ( 'fixed' === $saved_discount_type ) echo esc_attr( 'selected' );?>>Fixed</option>
							</select>
						</div>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'Amount', 'hex-coupon-for-woocommerce' ); ?></p>
							<input class="product-quantity-input amount" placeholder='Amount' type='number' value="<?php echo esc_attr( $free_product_amount ); ?>" name="<?php echo esc_attr( $converted_free_product_title );?>-free_amount" min="0" max="100">
						</div>
						<a href="javascript:void(0)" class='dashicons dashicons-no-alt remove_free_product' data-title="<?php echo esc_attr( $free_product_title ); ?>" data-value="<?php echo esc_attr( $value ); ?>"></a>
					</div>
				</div>
				<?php
				echo '</div>';
			}
		}

		echo '</div>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';

		woocommerce_wp_textarea_input(
			[
				'id' => 'message_for_coupon_expiry_date',
				'label' => '',
				'desc_tip' => true,
				'description' => esc_html__( 'Set a message for customers about the coupon expiry date.', 'hex-coupon-for-woocommerce' ),
				'placeholder' => esc_html__( 'Message for customer e.g. This coupon has been expired.', 'hex-coupon-for-woocommerce' ),
				'value' => get_post_meta( $post->ID, 'message_for_coupon_expiry_date', true ),
			]
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method starting_date_field
	 * @return void
	 * Add coupon starting date input field.
	 */
	private function starting_date_field()
	{
		global $post;

		woocommerce_wp_text_input(
			[
				'id' => 'coupon_starting_date',
				'label' => esc_html__( 'Coupon starting date', 'hex-coupon-for-woocommerce' ),
				'desc_tip' => true,
				'description' => esc_html__( 'Set the coupon starting date.', 'hex-coupon-for-woocommerce' ),
				'type' => 'text',
				'value' => get_post_meta( $post->ID, 'coupon_starting_date', true ),
				'class' => 'date-picker',
				'placeholder' => esc_html( 'YYYY-MM-DD' ),
				'custom_attributes' => [
					'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
				],
			]
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method starting_date_message_filed
	 * @return void
	 * Add coupon starting date input field.
	 */
	private function starting_date_message_filed()
	{
		global $post;

		woocommerce_wp_textarea_input(
			[
				'id' => 'message_for_coupon_starting_date',
				'label' => '',
				'desc_tip' => true,
				'description' => esc_html__( 'Set a message for customers about the coupon starting date.', 'hex-coupon-for-woocommerce' ),
				'placeholder' => esc_html__( 'Message for customer e.g. This coupon has not been started yet.', 'hex-coupon-for-woocommerce' ),
				'value' => get_post_meta( $post->ID, 'message_for_coupon_starting_date', true ),
			]
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_extra_fields
	 * @return void
	 * Add coupon expiry date message textarea field.
	 */
	public function add_coupon_extra_fields()
	{
		// Textarea message field for coupon expiry date
		$this->expiry_date_message_field();

		// Coupon starting date input field
		$this->starting_date_field();

		// Textarea message field for coupon starting date
		$this->starting_date_message_filed();
	}
}
