<?php
namespace HexCoupon\App\Core\Helpers\LoyaltyProgram;
use HexCoupon\App\Core\Lib\SingleTon;

class LoyaltyProgramHelpers
{
	use SingleTon;

	private $wpdb;
	private $table_name;
	private $store_credit_table;
	private $store_credit_logs_table;
	private $loyalty_points_log_table;
	private $pointsForSignup;
	private $points_on_purchase;
	private $conversion_rate;
	private $loyalty_program_enable_settings;

	/**
	 * Registering hooks that are needed
	 */
	public function register()
	{
		global $wpdb;

		$this->wpdb = $wpdb;
		$this->table_name = $wpdb->prefix . 'hex_loyalty_program_points';
		$this->store_credit_table = $wpdb->prefix . 'hex_store_credit';
		$this->store_credit_logs_table = $wpdb->prefix . 'hex_store_credit_logs';

		$this->loyalty_program_enable_settings = get_option( 'loyalty_program_enable_settings' );
		$enable_loyalty_program = ! empty( $this->loyalty_program_enable_settings ) ? $this->loyalty_program_enable_settings : 0;

		$this->pointsForSignup = get_option( 'pointsForSignup' );
		$enable_points_for_signup = ! empty( $this->pointsForSignup['enable'] ) ? intval( $this->pointsForSignup['enable'] ) : 0;

		$this->loyalty_points_log_table = $wpdb->prefix . 'hex_loyalty_points_log';

		$this->points_on_purchase = get_option( 'pointsOnPurchase' );
		$this->conversion_rate = get_option( 'conversionRate' );

		if ( $enable_loyalty_program && $enable_points_for_signup ) {
			add_action( 'user_register', [ $this, 'give_points_on_signup' ] );
		}

		add_action( 'init', [ $this, 'start_session' ] );
		add_action( 'template_redirect', [ $this, 'handle_referral' ] );
		add_action( 'user_register', [ $this, 'update_referrer_points' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'give_points_after_order_purchase_in_block' ], 10, 1 );
		add_action( 'woocommerce_order_status_changed', [ $this, 'give_referral_points_after_order_completed' ], 10, 4 );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method give_referral_points_after_order_completed
	 * @return void
	 * Giving referral points to the customer after changing the order status to 'completed'
	 */
	public function give_referral_points_after_order_completed( $order_id, $old_status, $new_status, $order )
	{
		$order = wc_get_order( $order_id );
		$user_id = $order->get_user_id();

		if ( 'completed' === $new_status ) {
			/**
			 * Giving points for referral after referee makes his/her first purchase
			 */
			$this->give_referral_after_purchase( $user_id );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method start_session
	 * @return void
	 * Giving points to the customer after signup
	 */
	public function start_session()
	{
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method give_points_on_signup
	 * @return void
	 * Giving points to the customer after signup
	 */
	public function give_points_on_signup( $user_id )
	{
		$wpdb = $this->wpdb;

		$table_name = $this->table_name;
		$store_credit_table = $this->store_credit_table;
		$loyalty_points_log_table = $this->loyalty_points_log_table;

		// Retrieve the signup points from the options
		$points_for_signup = get_option( 'pointsForSignup' );
		$points_for_signup = ! empty( $points_for_signup['pointAmount'] ) ? intval( $points_for_signup['pointAmount'] ) : 0;

		// Get the total points for the user, defaulting to 0 if no entry exists
		$current_points = $wpdb->get_var( $wpdb->prepare(
			"SELECT points FROM $table_name WHERE user_id = %d",
			$user_id
		) );

		// If no entry exists, default the current points to 0
		$current_points = $current_points !== null ? intval( $current_points ) : 0;

		// Calculate the new points balance
		$new_points_balance = $current_points + $points_for_signup;

		// Prepare the data for insertion or update
		$data = [
			'user_id' => $user_id,
			'points'  => $new_points_balance,
		];

		// Specify the data types for the insert function
		$data_types = [	'%d', '%d' ];

		// Insert or update the user's points balance in the database
		$wpdb->replace(
			$table_name,
			$data,
			$data_types
		);

		// ** Mechanism to send converting points to store credit and sending it to the database 'store_credit_table' ** //
		// Getting current store credit amount
		$points_to_be_converted = ! empty( $this->conversion_rate['points'] ) ? $this->conversion_rate['points'] : 0;

		// Calculate the new credit balance
		$new_credit_balance = round( $points_for_signup / $points_to_be_converted, 2 );

		// Prepare the data for insertion or update
		$store_credit_data = [
			'user_id' => $user_id,
			'amount'  => $new_credit_balance,
		];

		// Insert the user's points balance into the database
		$wpdb->insert(
			$store_credit_table,
			$store_credit_data,
			[ '%d', '%f' ]
		);

		// ** Mechanism to send logs in the 'hex_loyalty_points_log' table ** //
		$loyalty_points_log_data = [
			'user_id' => intval( $user_id ),
			'points'  => floatval( $points_for_signup ),
			'reason'  => boolval( 0 ),
			'converted_credit'  => floatval( $new_credit_balance ),
			'conversion_rate'  => floatval( $points_to_be_converted ),
		];

		$wpdb->insert(
			$loyalty_points_log_table,
			$loyalty_points_log_data,
			[ '%d', '%f', '%d', '%f', '%f' ],
		);

		$loyalty_points_primary_key = $wpdb->insert_id;

		// ** Mechanism to send logs for loyalty points in the store credit log table ** //
		$this->send_logs_to_the_store_credit_log_table( $user_id, $new_credit_balance, $loyalty_points_primary_key );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method start_session
	 * @return void
	 * Store the referrer ID in a session variable
	 */
	public function handle_referral()
	{
		if ( isset( $_GET['ref'] ) ) {
			$_SESSION['referrer_id'] = intval( $_GET['ref'] );
			wp_redirect( home_url( '/my-account' ) );
			exit;
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method update_referrer_points
	 * @return void
	 * Updating user meta after signup for referral points
	 */
	public function update_referrer_points( $user_id )
	{
		$enable_loyalty_program = ! empty( $this->loyalty_program_enable_settings['enable'] ) ? $this->loyalty_program_enable_settings['enable'] : 0;

		// Retrieve the signup points from the options
		$points_for_referral = get_option( 'pointsForReferral' );
		$enable_referral = ! empty( $points_for_referral['enable'] ) ? $points_for_referral['enable'] : 0;
		$points_for_referral = ! empty( $points_for_referral['pointAmount'] ) ? intval( $points_for_referral['pointAmount'] ) : 0;

		if ( $enable_loyalty_program && $enable_referral && isset( $_SESSION['referrer_id'] ) ) {
			$referrer_id = intval( $_SESSION['referrer_id'] );
			if ( $referrer_id ) {
				$user_meta_data = [
					'give_referral_point' => true,
					'referrer_id' => $referrer_id,
					'referral_points' => $points_for_referral,
				];

				foreach ( $user_meta_data as $meta_key => $meta_value ) {
					update_user_meta( $user_id, $meta_key, $meta_value );
				}

				// Clear the referrer ID from the session
				unset( $_SESSION['referrer_id'] );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method give_referral_after_purchase
	 * @return void
	 * Giving referral points to the customer after changing the order purchase
	 */
	public function give_referral_after_purchase( $user_id )
	{
		$give_referral_point = get_user_meta( $user_id, 'give_referral_point', true );

		if ( $give_referral_point ) {
			$points_for_referral = get_user_meta( $user_id, 'referral_points', true );
			$referrer_id = get_user_meta( $user_id, 'referrer_id', true );

			$wpdb = $this->wpdb;

			$table_name = $this->table_name;
			$store_credit_table = $this->store_credit_table;
			$loyalty_points_log_table = $this->loyalty_points_log_table;

			// Get the total points for the user, defaulting to 0 if no entry exists
			$current_points = $wpdb->get_var( $wpdb->prepare(
				"SELECT points FROM $table_name WHERE user_id = %d",
				$referrer_id
			) );

			// If no entry exists, default the current points to 0
			$current_points = $current_points !== null ? intval( $current_points ) : 0;

			// Calculate the new points balance
			$new_points_balance = $current_points + $points_for_referral;

			// Prepare the data for insertion or update
			$data = [
				'user_id' => $referrer_id,
				'points'  => $new_points_balance,
			];

			// Check if the user ID already exists in the table
			$existing_entry = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE user_id = %d",
				$referrer_id
			) );

			// If the user ID doesn't exist, insert a new row
			if ( ! $existing_entry ) {
				// Insert the user's points balance into the database
				$wpdb->insert(
					$table_name,
					$data,
					['%d', '%d']
				);
			} else {
				// Update the user's points balance in the database
				$wpdb->update(
					$table_name,
					['points' => $new_points_balance],
					['user_id' => $referrer_id],
					['%d'],
					['%d']
				);
			}

			//** Mechanism to send converting points to store credit and sending it to the database **
			// Getting current store credit amount
			$points_to_be_converted = ! empty( $this->conversion_rate['points'] ) ? $this->conversion_rate['points'] : 0;

			$current_credit = $wpdb->get_var( $wpdb->prepare(
				"SELECT amount FROM $store_credit_table WHERE user_id = %d",
				$referrer_id
			) );

			// If no entry exists, default the current credit to 0
			$current_credit = $current_credit !== null ? floatval( $current_credit ) : 0;

			// Calculate the new credit balance
			$new_credit_balance = round( $current_credit + ( $points_for_referral / $points_to_be_converted ), 2 );

			// Check if the user ID already exists in the table
			$existing_store_credit_entry = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $store_credit_table WHERE user_id = %d",
				$referrer_id
			) );

			// Prepare the data for insertion or update
			$store_credit_data = [
				'user_id' => $referrer_id,
				'amount'  => $new_credit_balance,
			];

			// If the user ID doesn't exist, insert a new row
			if ( ! $existing_store_credit_entry ) {
				// Insert the user's points balance into the database
				$wpdb->insert(
					$store_credit_table,
					$store_credit_data,
					['%d', '%f']
				);
			} else {
				// Update the user's points balance in the database
				$wpdb->update(
					$store_credit_table,
					['amount' => $new_credit_balance],
					['user_id' => $referrer_id],
					['%f'],
					['%d']
				);
			}

			// ** Mechanism to send loyalty points logs to the 'hex_loyalty_points_table' **
			$converted_credit = round( $points_for_referral / $points_to_be_converted, 2 );

			$loyalty_points_log_data = [
				'user_id' => intval( $referrer_id ),
				'points' => floatval( $points_for_referral ),
				'reason' => boolval( 1 ),
				'referee_id' => intval( $user_id ),
				'converted_credit' => floatval( $converted_credit ),
				'conversion_rate' => floatval( $points_to_be_converted ),
			];

			$wpdb->insert(
				$loyalty_points_log_table,
				$loyalty_points_log_data,
				[ '%d', '%f', '%d', '%f', '%f' ],
			);

			$loyalty_points_primary_key = $wpdb->insert_id;

			// ** Mechanism to send logs for loyalty points in the 'hex_store_credit_logs' table ** //
			$this->send_logs_to_the_store_credit_log_table( $referrer_id, $converted_credit, $loyalty_points_primary_key );

			update_user_meta( $user_id, 'give_referral_point', 0 );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method give_points_after_order_purchase_in_block
	 * @return void
	 * Giving points after successful checkout in block checkout page
	 */
	public function give_points_after_order_purchase_in_block( $order_id )
	{
		// Using a static variable to ensure the code runs only once
		static $has_run = false;

		// Check if the function has already been run
		if ( $has_run ) {
			return;
		}

		// Mark the function as run
		$has_run = true;

		// Get the order object
		$order = wc_get_order( $order_id );

		if ( $order ) {
			// Get the order subtotal
			$order_subtotal = $order->get_subtotal();
			$user_id = $order->get_user_id();
			$pointAmount = ! empty( $this->points_on_purchase['pointAmount'] ) ? $this->points_on_purchase['pointAmount'] : 0;
			$spendingAmount = ! empty( $this->points_on_purchase['spendingAmount'] ) ? $this->points_on_purchase['spendingAmount'] : 0;

			if ( $spendingAmount != 0 ) {
				$spending_ratio = $order_subtotal / $spendingAmount;
				$points = floor( $spending_ratio ) * $pointAmount;
			} else {
				$points = 0;
			}

			$wpdb = $this->wpdb;

			$table_name = $this->table_name;
			$store_credit_table = $this->store_credit_table;
			$loyalty_points_log_table = $this->loyalty_points_log_table;

			// Get the total points for the user, defaulting to 0 if no entry exists
			$current_points = $wpdb->get_var( $wpdb->prepare(
				"SELECT points FROM $table_name WHERE user_id = %d",
				$user_id
			) );

			// If no entry exists, default the current points to 0
			$current_points = $current_points !== null ? intval( $current_points ) : 0;

			// Calculate the new points balance
			$new_points_balance = $current_points + $points;

			// Prepare the data for insertion or update
			$data = [
				'user_id' => $user_id,
				'points'  => $new_points_balance,
			];

			// Check if the user ID already exists in the table
			$existing_entry = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_name WHERE user_id = %d",
				$user_id
			) );

			// If the user ID doesn't exist, insert a new row
			if ( ! $existing_entry ) {
				// Insert the user's points balance into the database
				$wpdb->insert(
					$table_name,
					$data,
					['%d', '%d']
				);
			} else {
				// Update the user's points balance in the database
				$wpdb->update(
					$table_name,
					['points' => $new_points_balance],
					['user_id' => $user_id],
					['%d'],
					['%d']
				);
			}

			// ** Getting current store credit amount for inserting credit to the 'store credit table' **
			$points_to_be_converted = ! empty( $this->conversion_rate['points'] ) ? $this->conversion_rate['points'] : 0;

			$current_credit = $wpdb->get_var( $wpdb->prepare(
				"SELECT amount FROM $store_credit_table WHERE user_id = %d",
				$user_id
			) );

			// If no entry exists, default the current credit to 0
			$current_credit = $current_credit !== null ? floatval( $current_credit ) : 0;

			// Calculate the new credit balance
			$new_credit_balance = round( $current_credit + ( $points / $points_to_be_converted ), 2 );

			// Check if the user ID already exists in the table
			$existing_store_credit_entry = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $store_credit_table WHERE user_id = %d",
				$user_id
			) );

			// Prepare the data for insertion or update
			$store_credit_data = [
				'user_id' => $user_id,
				'amount'  => $new_credit_balance,
			];

			// If the user ID doesn't exist, insert a new row
			if ( ! $existing_store_credit_entry ) {
				// Insert the user's points balance into the database
				$wpdb->insert(
					$store_credit_table,
					$store_credit_data,
					['%d', '%f']
				);
			} else {
				// Update the user's credit balance in the database
				$wpdb->update(
					$store_credit_table,
					['amount' => $new_credit_balance],
					['user_id' => $user_id],
					['%f'],
					['%d']
				);
			}

			// ** Mechanism to send logs in the loyalty_points_log table after order checkout ** //
			$credit_for_purchase = $points / $points_to_be_converted;

			$loyalty_points_log_data = [
				'user_id' => intval( $user_id ),
				'points'  => round(  $points ),
				'reason'  => strval( 2 ),
				'converted_credit'  => round( $credit_for_purchase, 2 ),
				'conversion_rate'  => floatval( $points_to_be_converted ),
			];

			$wpdb->insert(
				$loyalty_points_log_table,
				$loyalty_points_log_data,
				[ '%d', '%f', '%d', '%f', '%f' ],
			);

			$loyalty_points_primary_key = $wpdb->insert_id;

			// ** Mechanism to send logs for loyalty points in the 'hex_store_credit_logs' table ** //
			$this->send_logs_to_the_store_credit_log_table( $user_id, $credit_for_purchase, $loyalty_points_primary_key );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method send_logs_to_the_store_credit_log_table
	 * @return void
	 * Sending logs to the 'hex_store_credit_logs' table for user getting points
	 */
	public function send_logs_to_the_store_credit_log_table( $user_id, $credit, $loyalty_points_primary_key )
	{
		$data = [
			'user_id' => $user_id,
			'amount' => round( $credit, 2 ),
			'type' => 0,
			'status' => 1,
			'label' => 2,
			'loyalty_points_id' => $loyalty_points_primary_key,
		];

		$data_types = [
			'user_id' => '%d',
			'amount' => '%f',
			'type' => '%d',
			'status' => '%d',
			'label' => '%d',
			'loyalty_points_id' => '%d'
		];

		$this->wpdb->insert( $this->store_credit_logs_table, $data, $data_types );
	}

}
