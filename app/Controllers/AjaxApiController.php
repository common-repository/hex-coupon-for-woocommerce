<?php
namespace HexCoupon\App\Controllers;

use HexCoupon\App\Core\Helpers\LoyaltyProgram\LoyaltyPointsQueries;
use HexCoupon\App\Core\Helpers\StoreCredit\StoreCreditQueries;
use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Core\Helpers\StoreCreditHelpers;
use HexCoupon\App\Core\Helpers\GeneralFunctionsHelpers;
use Kathamo\Framework\Lib\Controller;

class AjaxApiController extends Controller
{
	use SingleTon;

	private $base_url = 'hexcoupon/v1/';
	private $is_pro_active;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		$this->is_pro_active = defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE ? true : false;

		if ( ! $this->is_pro_active ){
			add_action( 'wp_ajax_all_combined_data', [ $this, 'all_combined_data' ] );
		}
		add_action( 'wp_ajax_loyalty_program_enable_data', [ $this, 'loyalty_program_enable_data' ] );
		add_action( 'wp_ajax_point_loyalty_program_data', [ $this, 'point_loyalty_program_data' ] );
		add_action( 'wp_ajax_spin_wheel_settings_data', [ $this, 'spin_wheel_settings_data' ] );
		add_action( 'wp_ajax_point_loyalty_program_logs', [ $this, 'point_loyalty_program_logs' ] );
		add_action( 'wp_ajax_show_loyalty_points_in_checkout', [ $this, 'show_loyalty_points_in_checkout' ] );
		add_action( 'wp_ajax_coupon_data', [ $this, 'total_coupon_created_and_redeemed' ] );
		add_action( 'wp_ajax_get_additional_data', [ $this, 'get_additional_data'] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_spin_wheel_settings_data
	 * @return array
	 * Get all the value of spin wheel settings
	 */
	private function get_spin_wheel_settings_data()
	{
		$general = get_option( 'spinWheelGeneral' );
		$popup = get_option( 'spinWheelPopup' );
		$wheel = get_option( 'spinWheelWheel' );
		$wheel_content = get_option( 'spinWheelContent' );
		$wheel_text = get_option( 'spinWheelText' );
		$wheel_coupon = get_option( 'spinWheelCoupon' );

		$spinWheelSettings = [
			'spinWheelGeneral' => $general,
			'spinWheelPopup' => $popup,
			'spinWheelWheel' => $wheel,
			'spinWheelContent' => $wheel_content,
			'spinWheelText' => $wheel_text,
			'spinWheelCoupon' => $wheel_coupon,
		];

		return $spinWheelSettings;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_settings_data
	 * @return void
	 * Sending data of spin wheel settings
	 */
	public function spin_wheel_settings_data()
	{
		$spin_wheel_settings_data = $this->get_spin_wheel_settings_data();

		// Check the nonce and action
		if ( $this->verify_nonce() ) {
			// Nonce is valid, proceed with your code
			wp_send_json( [
				// Your response data here
				'msg' => 'hello',
				'type' => 'success',
				'spinWheelSettingsData' => $spin_wheel_settings_data,
			], 200);
		} else {
			// Nonce verification failed, handle the error
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method point_loyalty_program_logs
	 * @return void
	 * Sending data of loyalty points logs in log page
	 */
	public function point_loyalty_program_logs()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_loyalty_points_log';

		if ( $this->is_pro_active ) {
			$results = $wpdb->get_results(
				"SELECT * FROM $table_name ORDER BY id DESC",
				ARRAY_A
			);
		} else {
			$results = $wpdb->get_results(
				"SELECT * FROM $table_name ORDER BY id DESC LIMIT 15",
				ARRAY_A
			);
		}

		foreach ( $results as &$item ) {
			$user_data = get_userdata( $item['user_id'] );
			if ( $user_data ) {
				$user_email = $user_data->user_email;

				$item['user_email'] = $user_email;
				$first_name = $user_data->first_name;
				$last_name = $user_data->last_name;

				if ( ! $first_name && ! $last_name ) {
					$item['user_name'] = $user_data->display_name;
				} else {
					$item['user_name'] = $first_name . ' ' . $last_name;
				}

			}
		}

		unset( $item );

		if ( $this->verify_nonce() ) {
			// Nonce is valid, proceed with your code
			wp_send_json( [
				// Your response data here
				'msg' => 'hello',
				'type' => 'success',

				// store credit data
				'pointsLoyaltyLogs' => $results
			], 200);
		} else {
			// Nonce verification failed, handle the error
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_loyalty_points_in_checkout
	 * @return void
	 * Sending data of loyalty points in WooCommerce checkout block page
	 */
	public function show_loyalty_points_in_checkout()
	{
		check_ajax_referer( 'custom_nonce', 'security' );

		$points_on_purchase = get_option( 'pointsOnPurchase' );
		$spending_amount = ! empty( $points_on_purchase['spendingAmount'] ) ? $points_on_purchase['spendingAmount']: 0;
		$point_amount = ! empty( $points_on_purchase['pointAmount'] ) ? $points_on_purchase['pointAmount']: 0;

		$all_points = [
			'spendingAmount' => $spending_amount,
			'pointAmount' => $point_amount,
		];

		wp_send_json_success( $all_points );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method loyalty_program_enable_data
	 * @return void
	 * Getting loyalty program enable data
	 */
	public function loyalty_program_enable_data()
	{
		$loyalty_program_enable_settings = $this->loyalty_program_enable_settings();

		// Check the nonce and action
		if ( $this->verify_nonce() ) {
			// Nonce is valid, proceed with your code
			wp_send_json( [
				// Your response data here
				'msg' => 'hello',
				'type' => 'success',
				'loyaltyProgramEnable' => array_map( 'esc_html', $loyalty_program_enable_settings ),
			], 200);
		} else {
			// Nonce verification failed, handle the error
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method point_loyalty_program_data
	 * @return void
	 * Sending point loyalty all settings data
	 */
	public function point_loyalty_program_data()
	{
		$point_loyalty_program_settings = $this->point_loyalty_program_settings();

		// Check the nonce and action
		if ( $this->verify_nonce() ) {
			// Nonce is valid, proceed with your code
			wp_send_json( [
				// Your response data here
				'msg' => 'hello',
				'type' => 'success',
				'pointLoyaltyProgramData' => $point_loyalty_program_settings,
			], 200);
		} else {
			// Nonce verification failed, handle the error
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method all_combined_data
	 * @return void
	 * Get all data in a combined place.
	 */
	public function all_combined_data()
	{
		$total_coupon_created_and_redeemed = $this->total_coupon_created_and_redeemed();

		$get_additional_data = $this->get_additional_data();

		$store_credit_logs = $this->all_refunded_order_data();

		$store_credit_enable = $this->store_credit_enable_data();

		$current_user_data = $this->current_user_data();

		$all_customers_info = StoreCreditHelpers::getInstance()->get_all_customer_info();

		$total_store_credit_amount = StoreCreditHelpers::getInstance()->get_all_data_from_hex_store_credit_table();

		$top_points_earner = LoyaltyPointsQueries::getInstance()->GetTopLoyaltyPointsEarner();

		$top_points_reason = LoyaltyPointsQueries::getInstance()->GetTopReasonsForPoints();

		$top_store_credit_sources = StoreCreditQueries::getInstance()->GetTopStoreCreditSources();
		$store_credit_sources = [];
		$store_credit_amounts = [];
		foreach ( $top_store_credit_sources as $value ) {
			$store_credit_sources[] = $value['sources'];
			$store_credit_amounts[] = $value['credit'];
		}

		// get all the products of WooCommerce product
		$all_products = GeneralFunctionsHelpers::getInstance()->show_all_products();
		// get all the categories of WooCommerce product
		$all_categories = GeneralFunctionsHelpers::getInstance()->show_all_categories();
		// get all the pages of WordPress
		$all_pages = GeneralFunctionsHelpers::getInstance()->show_all_pages();

		// Check the nonce and action
		if ( $this->verify_nonce() ) {
			// Nonce is valid, proceed with your code
			wp_send_json( [
				// Your response data here
				'msg' => 'hello',
				'type' => 'success',
				'created' => $total_coupon_created_and_redeemed[0],
				'redeemedAmount' => $total_coupon_created_and_redeemed[1],
				'active' => $get_additional_data[0],
				'expired' => $get_additional_data[1],
				'redeemed' => $get_additional_data[2],
				'sharableUrlPost' => $get_additional_data[3],
				'bogoCoupon' => $get_additional_data[4],
				'geographicRestriction' => $get_additional_data[5],

				// store credit data
				'storeCreditLogs' => array_map( function( $item ) {
					return array_map( 'esc_html', $item );
				}, $store_credit_logs ),
				'storeCreditEnable' => array_map( 'esc_html', $store_credit_enable ),
				'adminData' => array_map( 'esc_html', $current_user_data ),
				'allCustomersInfo' => array_map( 'esc_html', $all_customers_info ),
				'totalStoreCreditAmount' => array_map( 'esc_html', $total_store_credit_amount ),

				// loyalty points
				'topPointsEarner' => array_map( function( $item ) {
					return array_map( 'esc_html', $item );
				}, $top_points_earner ),
				'topPointsReasons' => array_map( function ( $item ) {
					return array_map( 'esc_html', $item );
				}, $top_points_reason ),

				// store credit
				'topStoreCreditSources' => array_map( 'esc_html', $store_credit_sources),
				'topStoreCreditAmounts' => array_map( 'esc_html', $store_credit_amounts ),

				// all Woocommerce Product
				'allWooCommerceProduct' => $all_products,
				// all WooCommerce categories
				'allWooCommerceCategories' => $all_categories,
				// all WP pages
				'allPages' => $all_pages,
			], 200);
		} else {
			// Nonce verification failed, handle the error
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method all_refunded_order_data
	 * @return array
	 * Get all the data of refunded order.
	 */
	public function all_refunded_order_data()
	{
		$store_credit_logs = StoreCreditHelpers::getInstance()->get_all_refunded_order_data();

		return $store_credit_logs;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method store_credit_enable_data
	 * @return array
	 * Get data about enable disable option of store credit
	 */
	public function store_credit_enable_data()
	{
		$store_credit_enable_data = get_option( 'store_credit_enable_data' );

		return $store_credit_enable_data;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method store_credit_enable_data
	 * @return array
	 * Get data about enable disable option of store credit
	 */
	public function loyalty_program_enable_settings()
	{
		$loyalty_program_enable_data = get_option( 'loyalty_program_enable_settings' );

		return $loyalty_program_enable_data;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method store_credit_enable_data
	 * @return array
	 * Get data about enable disable option of store credit
	 */
	public function point_loyalty_program_settings()
	{
		$points_on_purchase = get_option( 'pointsOnPurchase' );
		$points_for_signup = get_option( 'pointsForSignup' );
		$points_for_referral = get_option( 'pointsForReferral' );
		$points_for_review = get_option( 'pointsForReview' );
		$points_for_comment = get_option( 'pointsForComment' );
		$points_for_birthday = get_option( 'pointsForBirthday' );
		$points_for_social_share = get_option( 'pointsForSocialShare' );
		$conversion_rate = get_option( 'conversionRate' );
		$all_loyalty_labels = get_option( 'allLoyaltyLabels' );

		$point_loyalty_program_settings = [
			'pointsOnPurchase' => $points_on_purchase,
			'pointsForSignup' => $points_for_signup,
			'pointsForReferral' => $points_for_referral,
			'pointsForReview' => $points_for_review,
			'pointsForComment' => $points_for_comment,
			'pointsForBirthday' => $points_for_birthday,
			'pointsForSocialShare' => $points_for_social_share,
			'conversionRate' => $conversion_rate,
			'allLoyaltyLabels' => $all_loyalty_labels,
		];

		return $point_loyalty_program_settings;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method current_user_data
	 * @return array
	 * Get all user data of current admin.
	 */
	public function current_user_data()
	{
		$current_user_data = StoreCreditHelpers::getInstance()->get_current_user_data();

		return $current_user_data;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method total_coupon_created_and_redeemed
	 * @return array
	 * Show all the created and redeemed coupon total count.
	 */
	public function total_coupon_created_and_redeemed()
	{
		global $wpdb;

		// Initialize the values
		$total_redeemed_value = 0;

		$query = "SELECT COUNT(ID) as count
          FROM {$wpdb->prefix}posts
          WHERE post_type = 'shop_coupon'
          AND post_status = 'publish'";
		$result = $wpdb->get_var( $query );

		$total_coupon_created = (int)$result;

		// Query all WooCommerce orders
		$orders = wc_get_orders( [
			'status' => [ 'completed', 'processing' ],
		] );

		// Loop through the orders
		foreach ( $orders as $order ) {
			$discount_amount = (float)$order->get_discount_total();

			// Add the discount to the total redeemed value
			$total_redeemed_value += $discount_amount;
		}

		$final_array = [ $total_coupon_created, $total_redeemed_value ];

		return $final_array;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_additional_data
	 * @return mixed
	 * Show all the active, expired, redeemed, sharable url coupon, bogo coupon, and geographically restricted coupon of all time.
	 */
	public function get_additional_data()
	{
		// Get current date
		$current_date = new \DateTime();

		// Initialize counts
		$active_coupons_count = 0;
		$expired_coupons_count = 0;
		$redeemed_coupons_count = 0;
		$sharable_url_coupons_count = 0;
		$bogo_coupon_count = 0;
		$geographic_restriction_count = 0;

		// Get all coupons
		$coupons = get_posts( [
			'post_type' => 'shop_coupon',
			'numberposts' => -1,
		] );

		// Loop through each coupon
		foreach ( $coupons as $coupon ) {
			// Create WC_Coupon object
			$coupon_obj = new \WC_Coupon( $coupon->ID );

			// Get expiry date
			$expiry_date = $coupon_obj->get_date_expires();

			$redeemed_coupons_count += $coupon_obj->get_usage_count();


			// Compare expiry date with current date
			if ( $expiry_date ) {
				if ( $expiry_date < $current_date ) {
					$expired_coupons_count++;
				} else {
					$active_coupons_count++;
				}
			} else {
				$active_coupons_count++; // Coupons without expiry dates are considered active
			}

			// Check if the coupon is a sharable URL coupon
			$sharable_url_coupon_meta = get_post_meta( $coupon->ID, 'sharable_url_coupon', true );
			if ( isset( $sharable_url_coupon_meta['apply_automatic_coupon_by_url'] ) && $sharable_url_coupon_meta['apply_automatic_coupon_by_url'] === 'yes' ) {
				$sharable_url_coupons_count++;
			}

			$discount_type = get_post_meta( $coupon->ID, 'discount_type', true );

			if ( 'buy_x_get_x_bogo' === $discount_type ) $bogo_coupon_count++;

			$geographic_restriction = get_post_meta( $coupon->ID, 'geographic_restriction', true );

			if ( ! empty( $geographic_restriction ) )  $geographic_restriction_count++;
		}

		$final_array = [ $active_coupons_count, $expired_coupons_count, $redeemed_coupons_count, $sharable_url_coupons_count, $bogo_coupon_count, $geographic_restriction_count ];

		return $final_array;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method verify_nonce
	 * @return bool
	 * Returning true or false after checking nonce.
	 */
	private function verify_nonce()
	{
		return isset( $_GET['nonce'] ) && !empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'],'hexCuponData-react_nonce' ) == 1 ;
	}
}
