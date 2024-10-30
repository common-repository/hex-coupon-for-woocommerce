<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\FormHelpers;
use HexCoupon\App\Core\Helpers\RenderHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponSingleUsageRestriction {
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
		$is_pro_active = defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE;

		if ( ! $is_pro_active ) {
			add_action( 'woocommerce_coupon_options_usage_restriction', [ $this, 'coupon_usage_restriction_meta_fields' ], 10, 1 );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_user_role_names
	 * @return array
	 * Retrieve all available role names.
	 */
	private function get_user_role_names()
	{
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		return $wp_roles->get_names();
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_all_products
	 * @return array
	 * Retrieve all available WoCommerce products.
	 */
	public function show_all_products()
	{
		$all_product_titles = []; // initialize an empty array

		$products = get_posts( [
			'post_type' => 'product',
			'numberposts' => -1,
		] );

		foreach ( $products as $product ) {
			$product_obj = wc_get_product( $product->ID );
			$product_price = $product_obj->get_price();
			$all_product_titles[$product->ID] = get_the_title( $product );
		}

		return $all_product_titles; // return all products id
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_all_categories
	 * @return array
	 * Retrieve all available WoCommerce product categories.
	 */
	private function show_all_categories()
	{
		$all_categories = []; // initialize an empty array

		$product_categories = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		] );

		if ( ! empty($product_categories) && ! is_wp_error( $product_categories ) ) {
			foreach ( $product_categories as $category ) {
				$cat_id = $category->term_id;
				$all_categories[ $cat_id ] = $category->name;
			}
		}

		return $all_categories; // return all categories id
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_user_names
	 * @return array
	 * Display name of the users.
	 */
	private function show_user_names()
	{
		// query all users
		$args = [
			'fields' => 'all', // get all fields of each user.
		];
		$user_query = new \WP_User_Query( $args );

		$all_users_name = []; // initialize an empty array

		// Check if there are users found
		if ( ! empty( $user_query->results ) ) {
			// Loop through the users and retrieve their 'first_name', 'last_name', and 'ID'.
			foreach ( $user_query->results as $user ) {
				$all_users_name[ $user->ID ] = $user->first_name . ' ' . $user->last_name . ' (' . $user->user_email . ')';
			}
		}

		return $all_users_name; // return all users id
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method product_cart_condition
	 * @return void
	 * Display meta fields for product cart condition.
	 */
	private function product_cart_condition()
	{
		global $post;

		$usage_restriction = get_post_meta( $post->ID, 'usage_restriction', true );
		$apply_cart_condition_on_products = ! empty( $usage_restriction['apply_cart_condition_for_customer_on_products'] ) ? 'yes' : '';

		woocommerce_wp_checkbox(
			[
				'id' => 'apply_cart_condition_for_customer_on_products',
				'name' => 'usage_restriction[apply_cart_condition_for_customer_on_products]',
				'label' => esc_html__( 'Product Cart Condition', 'hex-coupon-for-woocommerce' ),
				'description' => esc_html__( 'Check this box to to add a cart condition for the customer based on product.', 'hex-coupon-for-woocommerce' ),
				'value' => $apply_cart_condition_on_products,
				'wrapper_class' => 'cart-condition',
			]
		);

		$apply_on_listed_product = ! empty( $usage_restriction['apply_on_listed_product'] ) ? $usage_restriction['apply_on_listed_product'] : '';

		echo '<div class="apply_on_listed_product">';

		woocommerce_wp_radio(
			[
				'id' => 'apply_on_listed_product',
				'name' => 'usage_restriction[apply_on_listed_product]',
				'label' => '',
				'options' => [
					'any_of_the_product' => esc_html__( 'Coupon applies if only customers cart contains any of the product listed below', 'hex-coupon-for-woocommerce' ),
					'all_of_the_product' => esc_html__( 'Coupon applies if only customers cart contains all of the product listed below', 'hex-coupon-for-woocommerce' ),
				],
				'value' => ! empty( $apply_on_listed_product ) ? $apply_on_listed_product : 'any_of_the_product',
			]
		);
		echo '</div>';

		$all_selected_products = ! empty( $usage_restriction['all_selected_products'] ) ? $usage_restriction['all_selected_products']: [];

		echo '<div class="all_selected_products">';

		$output = FormHelpers::Init( [
			'label' => esc_html__( 'Products', 'hex-coupon-for-woocommerce' ),
			'name' => 'usage_restriction[all_selected_products]',
			'id' => 'all_selected_products',
			'value' => $all_selected_products,
			'type' => 'select',
			'options' => $this->show_all_products(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'all_selected_products',
			'placeholder' => esc_html__( 'Search for Products' , 'hex-coupon-for-woocommerce' ),
		] );

		echo '<span class="all_selected_products_tooltip">'.wc_help_tip( esc_html__( 'Products that the coupon will be applied to, or that need to be in the cart in order for the &quot;Fixed cart discount&quot; to be applied.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		$output .= '</div>';

		echo '<div id="selectedValuesContainer">';
		if ( ! empty( $all_selected_products ) ) {
			foreach ( $all_selected_products as $single_product ) {
				echo '<div class="product-item-whole" id="' . esc_attr( $single_product ) . '">';
				echo '<div class="product_title">'.get_the_title( $single_product ).'</div>';
				?>
				<div class="product_min_max_main">
					<div class='product_min product-wrap'>
						<div class="product-wrap-inner">
							<p class="product-wrap-para"><?php echo esc_html__( 'min quantity', 'hex-coupon-for-woocommerce' ); ?></p>
							<input class="product-quantity-input product-cart-condition" placeholder='Enter Qty' type='number' min="1" readonly>
						</div>
						<a href="javascript:void(0)" class='dashicons dashicons-no-alt remove_product' data-value="<?php echo esc_attr( $single_product ); ?>"></a>
					</div>
				</div>
				<?php
				echo '</div>';
			}
		}
		echo '</div>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method category_cart_condition
	 * @return void
	 * Display meta fields for category cart condition.
	 */
	private function category_cart_condition()
	{
		global $post;

		$usage_restriction = get_post_meta( $post->ID, 'usage_restriction', true );
		$apply_cart_condition_on_categories = ! empty( $usage_restriction['apply_cart_condition_for_customer_on_categories'] ) ? 'yes' : '';

		woocommerce_wp_checkbox(
			[
				'id' => 'apply_cart_condition_for_customer_on_categories',
				'name' => 'usage_restriction[apply_cart_condition_for_customer_on_categories]',
				'label' => esc_html__( 'Category Cart Condition', 'hex-coupon-for-woocommerce' ),
				'description' => esc_html__( 'Check this box to to add a cart condition for the customer based on category.', 'hex-coupon-for-woocommerce' ),
				'value' => $apply_cart_condition_on_categories,
				'wrapper_class' => 'category-cart-condition'
			]
		);

		$all_selected_categories = ! empty( $usage_restriction['all_selected_categories'] ) ? $usage_restriction['all_selected_categories']: [];

		echo '<div class="all_selected_categories">';
		$output = FormHelpers::Init( [
			'label' => esc_html__( 'Product Categories', 'hex-coupon-for-woocommerce' ),
			'name' => 'usage_restriction[all_selected_categories]',
			'id' => 'all_selected_categories',
			'value' => $all_selected_categories,
			'type' => 'select',
			'options' => $this->show_all_categories(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'all_selected_categories',
			'placeholder' => esc_html__('Search for category', 'hex-coupon-for-woocommerce' ),
		] );

		echo '<span class="all_selected_categories_tooltip">'.wc_help_tip( esc_html__( 'Categories that the coupon will be applied to, or that need to be in the cart in order for the &quot;Fixed cart discount&quot; to be applied.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		$output .= '</div>';
		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method allowed_or_restricted_customer
	 * @return void
	 * Display meta fields for allowed or restricted customer.
	 */
	private function allowed_or_restricted_customer()
	{
		global $post;

		$usage_restriction = get_post_meta( $post->ID, 'usage_restriction', true );
		$allowed_or_restricted_customer_group = ! empty( $usage_restriction['allowed_or_restricted_customer_group'] ) ? 'yes' : '';

		woocommerce_wp_checkbox(
			[
				'id' => 'allowed_or_restricted_customer_group',
				'name' => 'usage_restriction[allowed_or_restricted_customer_group]',
				'label' => esc_html__( 'Allowed/Restricted customer Group', 'hex-coupon-for-woocommerce' ),
				'description' => esc_html__( 'Check this box to to add groups of Allowed/Restricted customers.', 'hex-coupon-for-woocommerce' ),
				'value' => $allowed_or_restricted_customer_group,
			]
		);

		$allowed_grp_of_customer = ! empty( $usage_restriction['allowed_group_of_customer'] ) ? $usage_restriction['allowed_group_of_customer'] : '';

		echo '<div class="options_group allowed_group_of_customer">';

		woocommerce_wp_radio(
			[
				'id' => 'allowed_group_of_customer',
				'name' => 'usage_restriction[allowed_group_of_customer]',
				'label' => '',
				'wrapper_class' => 'allowed_group_of_customer',
				'options' => [
					'allowed_for_groups' => esc_html__( 'Coupon allowed for below groups', 'hex-coupon-for-woocommerce' ),
					'restricted_for_groups' => esc_html__( 'Coupon restricted for below groups', 'hex-coupon-for-woocommerce' ),
				],
				'value' => ! empty( $allowed_grp_of_customer ) ? $allowed_grp_of_customer : 'allowed_for_groups',
			]
		);

		$selected_customer_group = ! empty( $usage_restriction['selected_customer_group'] ) ? $usage_restriction['selected_customer_group'] : [];

		$output = FormHelpers::Init( [
			'label' => esc_html__( 'Customer Group', 'hex-coupon-for-woocommerce' ),
			'name' => 'usage_restriction[selected_customer_group]',
			'id' => 'selected_customer_group',
			'value' => $selected_customer_group,
			'type' => 'select',
			'options' => $this->get_user_role_names(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'selected_customer_group',
			'placeholder' => esc_html__( 'Search for customer group', 'hex-coupon-for-woocommerce' ),
		] );

		echo '<span class="selected_customer_group_tooltip">'.wc_help_tip( esc_html__( 'Groups that the coupon will be applied to, or that need to be in the cart in order for the &quot;Fixed cart discount&quot; to be applied.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';

		$allowed_or_restricted_individual_customer = ! empty( $usage_restriction['allowed_or_restricted_individual_customer'] ) ? 'yes' : '';

		woocommerce_wp_checkbox(
			[
				'id' => 'allowed_or_restricted_individual_customer',
				'name' => 'usage_restriction[allowed_or_restricted_individual_customer]',
				'label' => '',
				'description' => esc_html__( 'Check this box to to add individual of Allowed/Restricted customers.', 'hex-coupon-for-woocommerce' ),
				'value' => $allowed_or_restricted_individual_customer,
			]
		);

		$allowed_individual_customer = ! empty( $usage_restriction['allowed_individual_customer'] ) ? $usage_restriction['allowed_individual_customer'] : '';

		echo '<div class="options_group allowed_individual_customer">';

		woocommerce_wp_radio(
			[
				'id' => 'allowed_individual_customer',
				'name' => 'usage_restriction[allowed_individual_customer]',
				'wrapper_class' => 'allowed_individual_customer',
				'label' => '',
				'options' => [
					'allowed_for_customers' => esc_html__( 'Coupon allowed for below customers', 'hex-coupon-for-woocommerce' ),
					'restricted_for_customers' => esc_html__( 'Coupon restricted for below customers', 'hex-coupon-for-woocommerce' ),
				],
				'value' => ! empty( $allowed_individual_customer ) ? $allowed_individual_customer : 'allowed_for_customers',
			]
		);

		$selected_individual_customer = ! empty( $usage_restriction['selected_individual_customer'] ) ? $usage_restriction['selected_individual_customer'] : [];

		$output = FormHelpers::Init( [
			'label' => esc_html__( 'Individual Customer', 'hex-coupon-for-woocommerce' ),
			'name' => 'usage_restriction[selected_individual_customer]',
			'id' => 'selected_individual_customer',
			'value' => $selected_individual_customer,
			'type' => 'select',
			'options' => $this->show_user_names(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'selected_individual_customer',
			'placeholder' => esc_html__( 'Search for customers', 'hex-coupon-for-woocommerce' ),
		] );

		echo '<span class="selected_individual_customer_tooltip">'.wc_help_tip( esc_html__( 'Individual customer that the coupon will be applied to, or that need to be in the cart in order for the &quot;Fixed cart discount&quot; to be applied.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method coupon_usage_restriction_meta_fields
	 * @return void
	 * Display all the meta fields in the coupon usage restriction tab.
	 */
	public function coupon_usage_restriction_meta_fields()
	{
		// show all fields of product cart condition
		$this->product_cart_condition();

		// show all fields of categories cart condition
		$this->category_cart_condition();

		// show all fields of allowed or restricted customers
		$this->allowed_or_restricted_customer();
	}
}
