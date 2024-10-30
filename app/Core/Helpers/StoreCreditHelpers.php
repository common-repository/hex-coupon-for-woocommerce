<?php
namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;

class StoreCreditHelpers {
	use SingleTon;

	private $wpdb;
	private $table_name;

	/**
	 * Constructor to initialize global $wpdb
	 */
	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_name = $wpdb->prefix . 'hex_store_credit_logs';
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_hex_store_credit_logs_table
	 * @return void
	 * Creating table called 'hex_store_credit_logs_table'
	 */
	public function create_hex_store_credit_logs_table()
	{
		// Define SQL statement for table creation
		$sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED,
			store_credit_id BIGINT UNSIGNED NOT NULL,
			loyalty_points_id BIGINT UNSIGNED NOT NULL,
			amount DOUBLE NOT NULL,
			order_id BIGINT UNSIGNED NOT NULL,
			type BIGINT UNSIGNED NOT NULL COMMENT '0=credit,1=debit',
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			admin_id BIGINT UNSIGNED NULL,
			admin_name LONGTEXT NULL,
			note LONGTEXT NULL,
			status BIGINT UNSIGNED NOT NULL COMMENT '0=used, 1=received',
			label BIGINT UNSIGNED NOT NULL COMMENT '0=refund credits, 1=gift credits, 2=loyalty points',
			PRIMARY KEY (id)
			) ENGINE=InnoDB;";

		$this->wpdb->query( $sql );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_hex_notification_table
	 * @return void
	 * Creating table called 'hex_notification_table'
	 */
	public function create_hex_notification_table()
	{
		$table_name = $this->wpdb->prefix . 'hex_notification';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			status BIGINT UNSIGNED NOT NULL COMMENT '0=failed,1=send mail or sms send value',
			user_id BIGINT NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			type BIGINT UNSIGNED NOT NULL COMMENT '0=email,1=sms',
			PRIMARY KEY (id)
		) ENGINE=InnoDB;";

		$this->wpdb->query( $sql );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_hex_store_credit_table
	 * @return void
	 * Creating table called 'hex_store_credit'
	 */
	public function create_hex_store_credit_table()
	{
		$table_name = $this->wpdb->prefix . 'hex_store_credit';

		// Define SQL statement for table creation
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			amount DOUBLE NOT NULL,
			created_at TIMESTAMP NOT NULL,
			user_id BIGINT NOT NULL,
			updated_at TIMESTAMP NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=InnoDB;";

		$this->wpdb->query( $sql );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_current_user_data
	 * @return array
	 * Get the current logged-in user data.
	 */
	public function get_current_user_data()
	{
		$admin_details = [];

		$current_user_data = wp_get_current_user();

		$admin_first_name = $current_user_data->first_name;
		$admin_last_name = $current_user_data->last_name;
		$admin_full_name = $admin_first_name . ' ' . $admin_last_name;

		$admin_details['admin_id'] = $current_user_data->ID;
		$admin_details['admin_name'] = $admin_full_name;

		return $admin_details;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method hex_store_credit_logs_initial_insertion
	 * @return void
	 * Sending store credit data to the 'wp_hex_store_credit_logs' table upon changing the order status to 'refunded' and for sending gift credit
	 */
	public function hex_store_credit_logs_initial_insertion( $order_id, $user_id, $admin_id, $admin_display_name, $amount, $status )
	{
		$store_credit_logs_table_name = $this->wpdb->prefix . 'hex_store_credit_logs';

		$order_id = absint( $order_id );
		$admin_id = absint( $admin_id );
		$admin_display_name = strval( $admin_display_name );
		$amount = floatval( $amount );
		$status = strval( $status );

		// Converting user_id to an array if it's not already
		if ( ! is_array( $user_id ) ) {
			$user_ids = array( $user_id );
		} else {
			$user_ids = $user_id;
		}

		foreach ( $user_ids as $id ) {
			// store credit logs table
			$data = [
				'order_id' => $order_id,
				'user_id' => $id,
				'admin_id' => $admin_id,
				'admin_name' => $admin_display_name,
				'amount' => $amount,
				'status' => $status,
			];
			$data_types = [
				'order_id' => '%d',
				'user_id' => '%d',
				'admin_id' => '%d',
				'admin_name' => '%s',
				'amount' => '%f',
				'status' => '%s',
			];
			$this->wpdb->insert(
				$store_credit_logs_table_name,
				$data,
				$data_types,
			);
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method hex_store_credit_initial_data_insertion
	 * @return void
	 * Insert store credit initial data into the 'wp_hex_store_credit' upon changing the order status to 'refunded'
	 */
	public function hex_store_credit_initial_data_insertion( $amount, $user_id )
	{
		$hex_store_credit_table_name = $this->wpdb->prefix . 'hex_store_credit';
		$amount = floatval( $amount );
		$user_id = absint( $user_id );

		$data = [
			'amount' => $amount,
			'user_id' => $user_id,
		];

		$data_types = [
			'amount' => '%f',
			'user_id' => '%d',
		];

		// Checking if the order_id exists in the table
		$row_exists_for_credit_table = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $hex_store_credit_table_name WHERE user_id = %d", $user_id ) );

		if ( ! $row_exists_for_credit_table ) {
			$this->wpdb->insert(
				$hex_store_credit_table_name,
				$data,
				$data_types,
			);
		} else {
			$this->wpdb->query(
				$this->wpdb->prepare( "UPDATE $hex_store_credit_table_name SET amount = amount + %f, updated_at = CURRENT_TIMESTAMP() WHERE user_id = %d", $amount, $user_id )
			);
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_refunded_order_data
	 * @return array
	 * Get refunded order for store credit logs
	 */
	public function get_all_refunded_order_data()
	{
		// Select all rows from the table
		$results = $this->wpdb->get_results( "SELECT * FROM $this->table_name ORDER BY id DESC", ARRAY_A );

		$store_credit_log = [];

		foreach ( $results as $result ) {
			$user_info = get_userdata( $result['user_id'] );

			$user_nicename = ! empty( $user_info->user_nicename ) ? $user_info->user_nicename : '';

			$user_full_name = ( empty( $user_info->user_nicename ) || empty( $user_info->first_name ) ) ? $user_nicename : $user_info->first_name . ' ' . $user_info->last_name;
			$user_email = ! empty( $user_info->user_email ) ? $user_info->user_email : 'test@test.com';

			$order_id = $result['order_id'];

			$order_edit_page_link = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

			$admin_profile_url = get_author_posts_url( $result['admin_id'] );

			// Create a new entry for each order
			$store_credit_log[] = [
				'user_id' => $result['user_id'],
				'user_name' => $user_full_name,
				'user_email' => $user_email,
				'store_credit_id' => $result['store_credit_id'],
				'amount' => $result['amount'],
				'order_id' => $order_id,
				'type' => $result['type'],
				'created_at' => $result['created_at'],
				'admin_id' => $result['admin_id'],
				'admin_name' => $result['admin_name'],
				'note' => $result['note'],
				'status' => $result['status'],
				'label' => $result['label'],
				'order_edit_page_link' => $order_edit_page_link,
				'admin_profile_url' => $admin_profile_url,
			];
		}

		return $store_credit_log;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method send_value_to_hex_store_credit_logs
	 * @return void
	 * Insert values to 'wp_hex_store_credit_logs' table
	 */
	public function send_value_to_hex_store_credit_logs( $order_id, $status, $admin_id, $admin_name )
	{
		$order_id = absint( $order_id );
		$status = strval( $status );
		$admin_id = absint( $admin_id );
		$admin_name = strval( $admin_name );

		$data = [
			'order_id' => $order_id,
			'status' => $status,
			'admin_id' => $admin_id,
			'admin_name' => $admin_name,
		];

		$data_types = [
			'order_id' => '%d',
			'status' => '%d',
			'admin_id' => '%d',
			'admin_name' => '%s',
		];

		// Checking if the order_id exists in the table
		$row_exists = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE order_id = %d", $order_id ) );
		$amount = 0;
		$user_id = 0;

		if ( $row_exists ) {
			// If order ID exists, then update the row
			$this->wpdb->update(
				$this->table_name,
				$data,
				[ 'order_id' => $order_id ],
				$data_types,
			);

			$amount = $row_exists->amount;
			$user_id = $row_exists->user_id;
		}

		$store_credit_table_name = $this->wpdb->prefix . 'hex_store_credit';

		// Checking if the order_id exists in the table
		$row_exists_for_credit_table = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $store_credit_table_name WHERE user_id = %d", $user_id ) );

		// store credit table
		$data_to_send_in_credit_table = [
			'amount' => $amount,
			'user_id' => $user_id,
		];
		$data_types_for_credit_table = [
			'amount' => '%f',
			'user_id' => '%d',
		];

		if ( ! $row_exists_for_credit_table ) {
			$this->wpdb->insert(
				$store_credit_table_name,
				$data_to_send_in_credit_table,
				$data_types_for_credit_table,
			);
		} else {
			$this->wpdb->query(
				$this->wpdb->prepare( "UPDATE $store_credit_table_name SET amount = amount + %f, updated_at = CURRENT_TIMESTAMP() WHERE user_id = %d", $amount, $user_id )
			);
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method send_confirmation_email_for_store_credit_activation
	 * @return void
	 * Send confirmation email for store credit and save the notification log in database
	 */
	public function send_confirmation_email_for_store_credit_activation( $user_id, $order_id, $amount, $notification_message, $user_email, $formatted_date )
	{
		$table_name = $this->wpdb->prefix . 'hex_notification';
		$admin_email = get_bloginfo( 'admin_email' );
		$site_title = get_bloginfo( 'name' );

		$type = 1;
		$user_data = get_userdata( $user_id );
		$user_firstname = $user_data->user_firstname;
		$user_lastname = $user_data->user_lastname;
		$user_name = $user_firstname . ' ' . $user_lastname;

		$note = esc_html__( "We're writting to let you know that a store credit refund has been processed against your recent return of order #", "hex-coupon-for-woocommerce" ) . $order_id . esc_html__( ". Please follow through the below details", "hex-coupon-for-woocommerce" );
		$to = $user_email;
		$subject = esc_html__( 'Store credit confirmation information', 'hex-coupon-for-woocommerce' );
		$message = EmailTemplatesHelpers::getInstance()->templateMarkup( $note, $user_name, $site_title, $order_id, $amount, $formatted_date, $type );
		$headers = "From: $admin_email\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n"; // Set content type to HTML

		$send_mail = wp_mail( $to, $subject, $message, $headers );


		if ( $send_mail ) {
			$status = 1;
		} else {
			$status = 0;
		}

		$user_id = absint( $user_id );

		// Data to be inserted
		$data = [
			'status' => $status,
			'user_id' => $user_id,
		];

		// Data types that to be inserted
		$data_types = [
			'status' => '%d',
			'user_id' => '%d',
		];

		$this->wpdb->insert(
			$table_name,
			$data,
			$data_types,
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_customer_info
	 * @return array
	 * Get information of all WooCommerce customers
	 */
	public function get_all_customer_info()
	{
		$all_customers_info = [];

		$customers = get_users( [
			'role'    => 'customer',
			'orderby' => 'user_nicename',
			'order'   => 'ASC'
		] );

		foreach ( $customers as $customer ) {
			$all_customers_info[$customer->ID] = $customer->display_name;
		}

		return $all_customers_info;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_data_from_hex_store_credit_table
	 * @return array
	 * Get the store credit amount of individual users
	 */
	public function get_all_data_from_hex_store_credit_table()
	{
		$all_customers_info = [];

		$table_name = $this->wpdb->prefix . 'hex_store_credit';

		$query = "SELECT * FROM $table_name";

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		if ( $results ) {
			foreach ( $results as $result ) {
				$all_customers_info[$result['user_id']] = $result['amount'];
			}
		}

		return $all_customers_info;
	}
}
