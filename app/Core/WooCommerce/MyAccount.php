<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Lib\SingleTon;

/**
 * will add more
 * @since  1.0.0
 * */

class MyAccount
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
		// Action hook for adding 'All Coupons' menu page in the 'My Account' Page Menu
		add_filter ( 'woocommerce_account_menu_items', [ $this, 'coupon_menu_in_my_account_page' ], 99, 1 );
		// Action hook for registering permalink endpoint
		add_action( 'init', [ $this, 'coupon_menu_page_endpoint' ] );
		// Action hook for displaying 'All Coupons' page content
		add_action( 'woocommerce_account_all-coupons_endpoint', [ $this, 'coupon_page_endpoint_content'] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method coupon_menu_in_my_account_page
	 * @param $all_menu_links
	 * @return array|string[]
	 * @since 1.0.0
	 * Adds a menu called 'All Coupons' in the 'My account' menu page.
	 */
	public function coupon_menu_in_my_account_page( $all_menu_links )
	{
		$all_menu_links = array_slice( $all_menu_links, 0, 5, true )
			+ [ 'all-coupons' => esc_html__( 'All Coupons', 'hex-coupon-for-woocommerce' ) ]
			+ array_slice( $all_menu_links, 5, NULL, true );

		return $all_menu_links;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method coupon_menu_page_endpoint
	 * @return mixed
	 * @since 1.0.0
	 * Add/rewrite the menu endpoint of 'All Coupons' menu page.
	 */
	public function coupon_menu_page_endpoint()
	{
		return add_rewrite_endpoint( 'all-coupons', EP_ROOT | EP_PAGES );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method coupon_page_endpoint_content
	 * @return void
	 * @since 1.0.0
	 * Display the content in the 'All Coupons' menu page, the contents are all available coupon names.
	 */
	public function coupon_page_endpoint_content()
	{
		echo '<header class="woocommerce-Address-title title">';
		echo '<h3>' . esc_html__( 'All Available Coupons', 'hex-coupon-for-woocommerce' ) . '</h3>';
		echo '</header>';

		echo '<h4 class="coupon-section-title">' . esc_html__( 'Active Coupons', 'hex-coupon-for-woocommerce' ) . '</h4>';
		$this->active_coupons();

		echo '<h4 class="coupon-section-title">' . esc_html__( 'Upcoming Coupons', 'hex-coupon-for-woocommerce' ) . '</h4>';
		$this->upcoming_coupons();

		echo '<h4 class="coupon-section-title">' . esc_html__( 'Used Coupons', 'hex-coupon-for-woocommerce' ) . '</h4>';
		$this->used_coupons();
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method cooupon_query
	 * @return array
	 * @since 1.0.0
	 * Getting all the active coupons
	 */
	private function cooupon_query()
	{
		return get_posts( [
			'post_type' => 'shop_coupon',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'orderby' => 'name',
			'order' => 'asc',
		] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method active_coupons
	 * @return void
	 * @since 1.0.0
	 * Displays all available active coupon codes.
	 */
	private function active_coupons()
	{
		global $woocommerce;

		$coupon_posts = $this->cooupon_query();

		if( $coupon_posts ) {
			echo '<div class="user-all-coupon-wrap">';
			foreach ( $coupon_posts as $coupon_post ) {
				$expiry_date = get_post_meta( $coupon_post->ID, 'date_expires', true );
				$discount_type = get_post_meta( $coupon_post->ID, 'discount_type', true );
				$coupon_amount = get_post_meta( $coupon_post->ID, 'coupon_amount', true );
				$usage_restriction = get_post_meta( $coupon_post->ID, 'usage_restriction', true );
				$allowed_group_of_customer = ! empty( $usage_restriction['allowed_group_of_customer'] ) ? $usage_restriction['allowed_group_of_customer'] : '';
				$allowed_individual_customer = ! empty( $usage_restriction['allowed_individual_customer'] ) ? $usage_restriction['allowed_individual_customer'] : '';
				$selected_customer_group = ! empty( $usage_restriction['selected_customer_group'] ) ? $usage_restriction['selected_customer_group'] : [];
				$selected_individual_customer = ! empty( $usage_restriction['selected_individual_customer'] ) ? $usage_restriction['selected_individual_customer'] : [];
				$current_user_role = $this->get_current_user_role();
				$current_user_id = get_current_user_id();
				$geographic_restriction = get_post_meta( $coupon_post->ID, 'geographic_restriction', true );
				$all_zones = ! empty( $geographic_restriction['restricted_shipping_zones'] ) ? $geographic_restriction['restricted_shipping_zones'] : [];
				$all_shipping_zones = CouponSingleGeographicRestrictions::getInstance()->get_all_shipping_zones();

				// Deleting changed shipping zone location
				foreach ( $all_zones as $value ) {
					if ( ! array_key_exists( $value, $all_shipping_zones ) ) {
						$key = array_search( $value, $all_zones );
						unset( $all_zones[$key] );
					}
				}

				$all_zones = implode( ',', $all_zones );

				$all_continents = [
					'Africa' => 'AF',
					'Antarctica' => 'AN',
					'Asia' => 'AS',
					'Europe' => 'EU',
					'North America' => 'NA',
					'Oceania' => 'OC',
					'South America' => 'SA',
				];

				// Initializing WC_Countries class
				$countries = new \WC_Countries();
				// Get all countries and their data
				$all_countries = $countries->get_countries();

				// Getting shipping information of the user
				$shipping_city = $woocommerce->customer->get_shipping_city();
				$shipping_country = $woocommerce->customer->get_shipping_country();
				$get_shipping_country_name = array_key_exists( $shipping_country, $all_countries ) ? $all_countries[$shipping_country] : 'None';

				$shipping_continent_code = $countries->get_continent_code_for_country( $shipping_country );
				$shipping_continent_full_name = array_search( $shipping_continent_code, $all_continents );

				// Getting billing information of the user
				$billing_city = $woocommerce->customer->get_billing_city(); // get the current billing city of the user
				$billing_country = $woocommerce->customer->get_billing_country();
				$get_billing_country_name = array_key_exists( $billing_country, $all_countries ) ? $all_countries[$billing_country] : 'None';

				$billing_continent_code = $countries->get_continent_code_for_country( $billing_country );
				$billing_continent_full_name = array_search( $billing_continent_code, $all_continents );

				if ( $shipping_city || $shipping_country ) {
					if ( ! empty( $all_zones ) && $shipping_city && str_contains( $all_zones, $shipping_city ) ) {
						continue;
					}
					if ( ! empty( $all_zones ) && $shipping_country && $get_shipping_country_name && str_contains( $all_zones, $get_shipping_country_name ) ) {
						continue;
					}
					if ( ! empty( $all_zones ) && $shipping_continent_full_name && $get_shipping_country_name && str_contains( $all_zones, $shipping_continent_full_name ) ) {
						continue;
					}
				} else {
					if ( ! empty( $all_zones ) && ! empty( $billing_city ) && str_contains( $all_zones, $billing_city ) ) {
						continue;
					}
					if ( ! empty( $all_zones ) && $billing_country && $get_billing_country_name && str_contains( $all_zones, $get_billing_country_name ) ) {
						continue;
					}
					if ( ! empty( $all_zones ) && $billing_continent_full_name && $get_billing_country_name && str_contains( $all_zones, $billing_continent_full_name ) ) {
						continue;
					}
				}

				$all_countries = ! empty( $geographic_restriction['restricted_countries'] ) ? $geographic_restriction['restricted_countries'] : [];

				$shipping_country = $woocommerce->customer->get_shipping_country();
				$billing_country = $woocommerce->customer->get_billing_country();

				// Validating coupon based on user country for country wise restriction
				if ( in_array( $shipping_country, $all_countries ) ) {
					continue;
				}
				if ( in_array( $billing_country, $all_countries ) ) {
					continue;
				}

				$restricted_for_groups_logic = 'restricted_for_groups' == $allowed_group_of_customer && in_array( $current_user_role, $selected_customer_group );
				$restricted_for_customers_logic = 'restricted_for_customers' == $allowed_individual_customer && in_array( $current_user_id, $selected_individual_customer );

				if ( $restricted_for_groups_logic || $restricted_for_customers_logic  ) {
					continue;
				}

				$real_expiry_date = ! empty( $expiry_date ) ? date( 'Y-m-d', $expiry_date ) : 'No date set'; // Convert expiry date to a readable format
				$current_date = date( 'Y-m-d' ); // Get current date in the same format

				$coupon_description = get_post_field( 'post_excerpt', $coupon_post->ID );

				// Check if the expiry date has passed
				if ( $real_expiry_date > $current_date ) {
					?>
					<div class="discount-card">
						<div class="discount-info">
							<div class="discount-rate">
								<?php
								if ( $discount_type === 'percent' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>%</span><br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'buy_x_get_x_bogo' ) {
									echo esc_html__( 'Bogo', 'hex-coupon-for-woocommerce' ) . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'fixed_product' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'fixed_cart' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								?>
							</div>
							<div class="discount-details">
								<p><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_description ) ); ?></p>
								<div class="discount-code">
									<span class="icon">🎟️</span>
									<span class="code"><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_post->post_title ) ); ?></span>
								</div>
								<div class="discount-expiry">
									<span class="icon">⏰</span>
									<span class="date"><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $real_expiry_date ) ); ?></span>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
			}
			echo '</div>';
		} else {
			echo '<p style="text-align: center;">' . esc_html__( 'No coupon found', 'hex-coupon-for-woocommerce' ) . '</p>';
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method upcoming_coupons
	 * @return void
	 * @since 1.0.0
	 * Displays all available upcoming coupon codes.
	 */
	private function upcoming_coupons()
	{
		$coupon_posts = $this->cooupon_query();

		if( $coupon_posts ) {
			echo '<div class="user-all-coupon-wrap">';

			foreach ( $coupon_posts as $coupon_post ) {
				$starting_date = get_post_meta( $coupon_post->ID, 'coupon_starting_date', true );
				$discount_type = get_post_meta( $coupon_post->ID, 'discount_type', true );
				$coupon_amount = get_post_meta( $coupon_post->ID, 'coupon_amount', true );

				if ( ! empty( $starting_date ) ) {
					// converting the string value to numeric value
					if ( ! is_numeric( $starting_date ) ) {
						$starting_date = strtotime( $starting_date );
					}

					$real_starting_date = date( 'Y-m-d', $starting_date ); // Converting starting date to a readable format
					$current_date = date( 'Y-m-d' ); // Getting current date in the same format
					$coupon_description = get_post_field( 'post_excerpt', $coupon_post->ID );

					// Check if the starting date has not passed
					if ( $real_starting_date > $current_date ) {
						?>
						<div class="discount-card upcoming">
							<div class="discount-info">
								<div class="discount-rate">
									<?php
									if ( $discount_type === 'percent' ) {
										printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
										echo '<span>%</span><br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
									}
									if ( $discount_type === 'buy_x_get_x_bogo' ) {
										echo esc_html__( 'Bogo', 'hex-coupon-for-woocommerce' ) . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
									}
									if ( $discount_type === 'fixed_product' ) {
										printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
										echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
									}
									if ( $discount_type === 'fixed_cart' ) {
										printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
										echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
									}
									?>
								</div>
								<div class="discount-details">
									<p><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_description ) ); ?></p>
									<div class="discount-code">
										<span class="icon">🎟️</span>
										<span class="code"><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_post->post_title ) ); ?></span>
									</div>
									<div class="discount-expiry">
										<span class="icon">⏰</span>
										<span class="date"><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $real_starting_date ) ); ?></span>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				}
			}
			echo '</div>';
		} else {
			echo '<p style="text-align: center;">' . esc_html__( 'No coupon found', 'hex-coupon-for-woocommerce' ) . '</p>';
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method used_coupons
	 * @return void
	 * @since 1.0.0
	 * Displays all used coupon codes by the users.
	 */
	private function used_coupons()
	{
		$used_coupon_ids = [];

		$args = [
			'post_type'      => 'shop_order',
			'posts_per_page' => -1,
			'post_status'    => 'wc-completed',
		];

		$orders = get_posts( $args );

		foreach ( $orders as $order_post ) {
			$order = wc_get_order( $order_post->ID );
			$used_coupons = $order->get_coupon_codes();

			foreach ( $used_coupons as $coupon_code ) {
				$coupon = new \WC_Coupon( $coupon_code );
				$used_coupon_ids[] = $coupon->get_id();
			}
		}

		// Removing duplicate coupon IDs
		$used_coupon_ids = array_unique( $used_coupon_ids );

		// Step 2: Query the coupon posts using the used coupon IDs
		if ( ! empty( $used_coupon_ids ) ) {
			$coupon_posts = get_posts( [
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'name',
				'order'          => 'asc',
				'post__in'       => $used_coupon_ids, // Filter by used coupon IDs
			] );

			if( $coupon_posts ) {
				echo '<div class="user-all-coupon-wrap">';

				foreach ( $coupon_posts as $coupon_post ) {
					$expiry_date = get_post_meta( $coupon_post->ID, 'date_expires', true );
					$discount_type = get_post_meta( $coupon_post->ID, 'discount_type', true );
					$coupon_amount = get_post_meta( $coupon_post->ID, 'coupon_amount', true );

					$real_expiry_date = ! empty( $expiry_date ) ? date( 'Y-m-d', $expiry_date ) : 'No date set'; // Convert expiry date to a readable format
					$current_date = date( 'Y-m-d' ); // Getting current date in the same format
					$coupon_description = get_post_field( 'post_excerpt', $coupon_post->ID );
					?>
					<div class="discount-card">
						<div class="discount-info">
							<div class="discount-rate">
								<?php
								if ( $discount_type === 'percent' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>%</span><br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'buy_x_get_x_bogo' ) {
									echo esc_html__( 'Bogo', 'hex-coupon-for-woocommerce' ) . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'fixed_product' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								if ( $discount_type === 'fixed_cart' ) {
									printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) );
									echo '<span>' . get_woocommerce_currency_symbol() . '</span>' . '<br>' . esc_html__( 'Discount', 'hex-coupon-for-woocommerce' );
								}
								?>
							</div>
							<div class="discount-details">
								<p><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_description ) ); ?></p>
								<div class="discount-code">
									<span class="icon">🎟️</span>
									<span class="code"><?php printf( esc_html__( '%s ', 'hex-coupon-for-woocommerce' ),  esc_html( $coupon_post->post_title ) ); ?></span>
								</div>
								<div class="discount-expiry">
									<span class="icon">⏰</span>
									<?php
									if ( $real_expiry_date > $current_date ) $real_expiry_date_final = $real_expiry_date;
									elseif ( $real_expiry_date < $current_date ) $real_expiry_date_final = 'Expired';
									?>
									<span class="date"><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $real_expiry_date_final ) ); ?></span>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				echo '</div>';
			} else {
				echo '<p style="text-align: center;">' . esc_html__( 'No coupon found', 'hex-coupon-for-woocommerce' ) . '</p>';
			}
		} else {
			echo '<p style="text-align: center;">' . esc_html__( 'No used coupons found', 'hex-coupon-for-woocommerce' ) . '</p>';
		}

	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_current_user_role
	 * @return void
	 * @since 1.0.0
	 * Getting the current user role.
	 */
	private function get_current_user_role() {
		// Get the current user object
		$current_user = wp_get_current_user();

		// Check if the user has any roles
		if ( ! empty( $current_user->roles ) && is_array( $current_user->roles ) ) {
			// Return the first role of the user (users can have multiple roles)
			return $current_user->roles[0];
		}

		return null;
	}
}
