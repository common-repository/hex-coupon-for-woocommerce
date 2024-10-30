<?php

namespace hexcoupon\app\Controllers\WooCommerce\Admin;

use HexCoupon\App\Controllers\BaseController;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponGeneralTabController extends BaseController
{
	use SingleTon;

	private $error_message = 'An error occured while saving the coupon general tab meta data value';

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return void
	 * @since 1.0.0
	 * Register hook that is needed to validate the coupon according to the starting date.
	 */
	public function register()
	{
		add_action( 'wp_loaded', [ $this, 'get_all_post_meta' ] );
		add_action( 'woocommerce_process_shop_coupon_meta', [ $this, 'save_coupon_general_tab_meta_field_data' ] );
		add_filter( 'woocommerce_coupon_is_valid', [ $this, 'apply_coupon' ], 10, 2 );
		add_action( 'woocommerce_process_shop_coupon_meta', [ $this, 'delete_meta_value' ] );
		add_filter( 'woocommerce_coupon_error', [ $this, 'custom_error_message_for_expiry_date' ], 10, 3 );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method custom_error_message_for_expiry_date
	 * @return void
	 * @since 1.0.0
	 * Altering the default coupon expiry message with the custom one
	 */
	public function custom_error_message_for_expiry_date( $err_message, $err_code, $coupon ) {
		$coupon_id = $coupon->get_id();
		$custom_expiry_message = get_post_meta( $coupon_id, 'message_for_coupon_expiry_date', true );

		if ( 107 === $err_code && ! empty( $custom_expiry_message ) ) {
			$err_message = sprintf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $custom_expiry_message ) );
		}

		return $err_message;
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method coupon_starting_date_invalid_error_message
	 * @param string $err
	 * @param int $err_code
	 * @param object $coupon
	 * @return string
	 * Display custom error message for invalid coupon.
	 */
	public function coupon_starting_date_invalid_error_message( $err, $err_code, $coupon )
	{
		$coupon = new \WC_Coupon( $coupon );

		// Get the ID of the coupon
		$coupon_id = $coupon->get_id();

		$message_for_coupon_starting_date = get_post_meta( $coupon_id, 'message_for_coupon_starting_date', true );

		if ( $err_code === 100 ) {
			if ( ! empty( $message_for_coupon_starting_date ) ) {
				// Change the error message for the INVALID_FILTERED error here
				$err = sprintf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $message_for_coupon_starting_date ) );
			} else {
				$err = esc_html__( 'This coupon has not been started yet. ' );
			}
		}

		return $err;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_post_meta
	 * @return array
	 * Get all coupon meta values
	 */
	public function get_all_post_meta( $coupon )
	{
		$all_meta_data = [];

		$meta_fields_data = [
			'customer_purchases',
			'add_specific_product_to_purchase',
			'add_specific_product_for_free',
			'customer_gets_as_free',
			'add_categories_to_purchase',
			'coupon_starting_date',
			'apply_days_hours_of_week',
			'discount_type',
			'message_for_coupon_starting_date',
		];

		foreach( $meta_fields_data as $meta_value ) {
			$all_meta_data[$meta_value] = get_post_meta( $coupon, $meta_value, true );
		}

		return $all_meta_data;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method coupon_id
	 * @return int
	 * Get the id of the applied coupon from the cart page.
	 */
	public function coupon_id()
	{
		$applied_coupon = WC()->cart->get_applied_coupons(); // get applied coupon from the cart page

		// assigning an empty string
		$coupon_id = '';

		// check if there are applied coupon
		if ( ! empty( $applied_coupon ) ) {
			// Assuming only one coupon is applied; if multiple, you might need to loop through $applied_coupon array
			$coupon_code = reset( $applied_coupon );
			$coupon_id = wc_get_coupon_id_by_code( $coupon_code ); // get the coupon id from the coupon code
		}

		// finally return the coupon code id
		return $coupon_id;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_meta_data
	 * @param string $key
	 * @param string $data_type
	 * @param int $coupon_id
	 * @return mixed
	 * Save the coupon general tab meta-data.
	 */
	private function save_meta_data( $key, $data_type, $coupon_id )
	{
		$validator = $this->validate( [
			$key => $data_type
		] );

		$error = $validator->error();
		if ( $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sprintf( esc_html__( 'Error: %s', 'hex-coupon-for-woocommerce' ), esc_html( $this->error_message ) ); ?></p>
			</div>
			<?php
		}
		$data = $validator->getData();

		update_post_meta( $coupon_id, $key, $data[$key] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_dynamic_meta_data
	 * @param string $day
	 * @param string $key
	 * @param string $data_type
	 * @param int $coupon_id
	 * @return void
	 * Save the coupon dynamic hours meta-data.
	 */
	private function save_dynamic_meta_data( $day, $key, $data_type, $coupon_id )
	{
		if ( isset( $_POST['total_hours_count_' . $day] ) ) {
			$total_hours_count = intval( $_POST['total_hours_count_' . $day] );

			// Loop through the input values and save them as post meta
			for ( $i = 1; $i <= $total_hours_count; $i++ ) {
				$validator = $this->validate( [
					$key.$i => $data_type
				] );
				$error = $validator->error();
				if ( $error ) {
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo sprintf( esc_html__( 'Error: %s', 'hex-coupon-for-woocommerce' ), esc_html( $this->error_message ) ); ?></p>
					</div>
					<?php
				}
				$data = $validator->getData();

				update_post_meta( $coupon_id, $key.$i, $data[$key.$i] );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_on_different_days
	 * @param int $coupon_id
	 * @return void
	 * Save the coupon days and hours option applicability on different days.
	 */
	private function save_coupon_on_different_days( $coupon_id )
	{
		$meta_fields_data = [
			[ 'coupon_apply_on_saturday', 'string' ],
			[ 'coupon_apply_on_sunday', 'string' ],
			[ 'coupon_apply_on_monday', 'string' ],
			[ 'coupon_apply_on_tuesday', 'string' ],
			[ 'coupon_apply_on_wednesday', 'string' ],
			[ 'coupon_apply_on_thursday', 'string' ],
			[ 'coupon_apply_on_friday', 'string' ],
			[ 'discount_type', 'string' ],
			[ 'customer_purchases', 'string' ],
			[ 'add_specific_product_to_purchase', 'string' ],
			[ 'add_categories_to_purchase', 'string' ],
			[ 'customer_gets_as_free', 'string' ],
			[ 'add_specific_product_for_free', 'string' ],
		];

		foreach ( $meta_fields_data as $meta_field_data ) {
			if ( ! empty( $meta_field_data[0] ) && ! empty( $meta_field_data[1] ) ) {
				$this->save_meta_data( $meta_field_data[0], $meta_field_data[1], $coupon_id );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_hex_bogo_data
	 * @param int $coupon_id
	 * @return void
	 * Save the coupon bogo deal meta data.
	 */
	private function save_hex_bogo_data( $coupon_id )
	{
		$all_meta_values = $this->get_all_post_meta( $coupon_id );

		$add_specific_product_to_purchase = $all_meta_values['add_specific_product_to_purchase'];

		$string_to_be_replaced = [ ' ', '-' ];

		if ( ! empty( $add_specific_product_to_purchase ) ) {
			foreach ( $add_specific_product_to_purchase as $value ) {
				$product_title = get_the_title( $value );
				$converted_product_title = str_replace( $string_to_be_replaced, '-', strtolower( $product_title ) ) . '-purchased_min_quantity';
				$this->save_meta_data( $converted_product_title, 'string', $coupon_id );
			}
		}

		$add_categories_to_purchase = $all_meta_values['add_categories_to_purchase'];

		if ( ! empty( $add_categories_to_purchase ) ) {
			foreach ( $add_categories_to_purchase as $value ) {
				$category_name = get_term( $value, 'product_cat' );
				$category_name = $category_name->name;

				$converted_categories_title = strtolower( str_replace( $string_to_be_replaced, '-', $category_name ) ) . '-purchased_category_min_quantity';

				$this->save_meta_data( $converted_categories_title, 'string', $coupon_id );
			}
		}

		$add_specific_product_for_free = $all_meta_values['add_specific_product_for_free'];

		if ( ! empty( $add_specific_product_for_free ) ) {
			foreach ( $add_specific_product_for_free as $value ) {
				$product_title = get_the_title( $value );
				$converted_product_title = str_replace( $string_to_be_replaced, '-', strtolower( $product_title ) ) . '-free_product_quantity';
				$this->save_meta_data( $converted_product_title, 'string', $coupon_id );
			}
		}

		if ( ! empty( $add_specific_product_for_free ) ) {
			foreach ( $add_specific_product_for_free as $value ) {
				$product_title = get_the_title( $value );
				$converted_product_title = str_replace( $string_to_be_replaced, '-', strtolower( $product_title ) ) . '-hexcoupon_bogo_discount_type';
				$this->save_meta_data( $converted_product_title, 'string', $coupon_id );
			}

			foreach ( $add_specific_product_for_free as $value ) {
				$product_title = get_the_title( $value );
				$converted_product_title = str_replace( $string_to_be_replaced, '-', strtolower( $product_title ) ) . '-free_amount';
				$this->save_meta_data( $converted_product_title, 'string', $coupon_id );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_start_expiry_time
	 * @param int $coupon_id
	 * @return void
	 * Save coupon start and expiry time on different days.
	 */
	private function save_coupon_start_expiry_time( $coupon_id )
	{
		$meta_fields_data = [
			[ 'sat_coupon_start_time', 'string' ],
			[ 'sat_coupon_expiry_time', 'string' ],
			[ 'sun_coupon_start_time', 'string' ],
			[ 'sun_coupon_expiry_time', 'string' ],
			[ 'mon_coupon_start_time', 'string' ],
			[ 'mon_coupon_expiry_time', 'string' ],
			[ 'tue_coupon_start_time', 'string' ],
			[ 'tue_coupon_expiry_time', 'string' ],
			[ 'wed_coupon_start_time', 'string' ],
			[ 'wed_coupon_expiry_time', 'string' ],
			[ 'thu_coupon_start_time', 'string' ],
			[ 'thu_coupon_expiry_time', 'string' ],
			[ 'fri_coupon_start_time', 'string' ],
			[ 'fri_coupon_expiry_time', 'string' ],
		];

		foreach ( $meta_fields_data as $meta_field_data ) {
			if ( ! empty( $meta_field_data[0] ) && ! empty( $meta_field_data[1] ) ) {
				$this->save_meta_data( $meta_field_data[0], $meta_field_data[1], $coupon_id );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method save_coupon_dynamic_start_expiry_time
	 * @param $coupon_id
	 * @return void
	 * Apply the coupon dynamic start and expiry hours field value for all days.
	 */
	private function save_coupon_dynamic_start_expiry_time( $coupon_id )
	{
		$meta_fields_data = [
			[ 'saturday', 'sat_coupon_start_time_', 'string' ],
			[ 'saturday', 'sat_coupon_expiry_time_', 'string' ],
			[ 'sunday', 'sun_coupon_start_time_', 'string' ],
			[ 'sunday', 'sun_coupon_expiry_time_', 'string' ],
			[ 'monday', 'mon_coupon_start_time_', 'string' ],
			[ 'monday', 'mon_coupon_expiry_time_', 'string' ],
			[ 'tuesday', 'tue_coupon_start_time_', 'string' ],
			[ 'tuesday', 'tue_coupon_expiry_time_', 'string' ],
			[ 'wednesday', 'wed_coupon_start_time_', 'string' ],
			[ 'wednesday', 'wed_coupon_expiry_time_', 'string' ],
			[ 'thursday', 'thu_coupon_start_time_', 'string' ],
			[ 'thursday', 'thu_coupon_expiry_time_', 'string' ],
			[ 'friday', 'fri_coupon_start_time_', 'string' ],
			[ 'friday', 'fri_coupon_expiry_time_', 'string' ],
		];

		foreach ( $meta_fields_data as $meta_field_data ) {
			if ( ! empty( $meta_field_data[0] ) && ! empty( $meta_field_data[1] ) && ! empty( $meta_field_data[2] ) ) {
				$this->save_dynamic_meta_data( $meta_field_data[0], $meta_field_data[1], $meta_field_data[2], $coupon_id );
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method save_coupon_expiry_date_message
	 * @param $coupon_id
	 * @return void
	 * @since 1.0.0
	 * Save the coupon expiry date message.
	 */
	public function save_coupon_general_tab_meta_field_data( $coupon_id )
	{
		$meta_fields_data = [
			[ 'message_for_coupon_expiry_date', 'string' ],
			[ 'coupon_starting_date', 'string' ],
			[ 'message_for_coupon_starting_date', 'string' ],
			[ 'apply_days_hours_of_week', 'string' ],
		];

		foreach ( $meta_fields_data as $meta_field_data ) {
			if ( ! empty( $meta_field_data[0] ) && ! empty( $meta_field_data[1] ) ) {
				$this->save_meta_data( $meta_field_data[0], $meta_field_data[1], $coupon_id );
			}
		}

		$total_hours_count = [
			[ 'total_hours_count_saturday', 'string' ],
			[ 'total_hours_count_sunday', 'string' ],
			[ 'total_hours_count_monday', 'string' ],
			[ 'total_hours_count_tuesday', 'string' ],
			[ 'total_hours_count_wednesday', 'string' ],
			[ 'total_hours_count_thursday', 'string' ],
			[ 'total_hours_count_friday', 'string' ],
		];

		foreach ( $total_hours_count as $value ) {
			$this->save_meta_data( $value[0], $value[1], $coupon_id );
		}

		// Save the coupon days and hours option applicability on different days
		$this->save_coupon_on_different_days( $coupon_id );

		// Save coupon start and expiry time on different days
		$this->save_coupon_start_expiry_time( $coupon_id );

		// Save coupon dynamic start and expiry time on different days
		$this->save_coupon_dynamic_start_expiry_time( $coupon_id );

		// Save coupon bogo deals data
		$this->save_hex_bogo_data( $coupon_id );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_coupon_starting_date
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply/validate the coupon starting date.
	 */
	private function apply_coupon_starting_date( $valid, $coupon )
	{
		$current_time = time();

		$coupon_starting_date = get_post_meta( $coupon->get_id(), 'coupon_starting_date', true );
		$coupon_converted_starting_date = strtotime( $coupon_starting_date );

		if ( empty( $coupon_starting_date ) || $current_time >= $coupon_converted_starting_date ) {
			return true;
		}
		else {
			// display a custom coupon error message if the coupon is invalid
			add_filter( 'woocommerce_coupon_error', [ $this, 'coupon_starting_date_invalid_error_message' ] , 10, 3 );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_to_single_day
	 * @param bool $valid
	 * @param object $coupon
	 * @param string $full_day
	 * @param string $abbrev
	 * @return bool
	 * Apply/validate the coupon on different days of the week.
	 */
	private function apply_to_single_day( $valid, $coupon )
	{
		global $day;

		// get current date
		$current_day = date('l');
		$changed_day = strtolower($current_day);

		// get current server time
		$current_server_time = current_time( 'timestamp' );

		// get selected name of selected day
		$day = get_post_meta( $coupon->get_id(), 'coupon_apply_on_'.$changed_day, true );

		$day = ! empty( $day ) ? '1' : '';
		// convert the day name
		if ( '1' === $day ) $day = ucfirst( $changed_day );


		$first_three_letters = substr( $changed_day, 0, 3 );

		$coupon_start_time = get_post_meta( $coupon->get_id(), $first_three_letters.'_coupon_start_time', true );
		$coupon_start_time = strtotime( $coupon_start_time );

		$coupon_expiry_time = get_post_meta( $coupon->get_id(), $first_three_letters.'_coupon_expiry_time', true );
		$coupon_expiry_time = strtotime( $coupon_expiry_time );

		if ( ! empty( $day ) && $current_day == $day ) {
			// Validating first data and time field
			if ( $current_server_time >= $coupon_start_time && $current_server_time <= $coupon_expiry_time  ) {
				return true;
			}

			// Validating dynamic date and time field after the first field
			$total_hours_count = get_post_meta( $coupon->get_id(), 'total_hours_count_'. $changed_day, true );

			for ( $i = 1; $i <= $total_hours_count; $i++ ) {
				$additional_start_time = get_post_meta( $coupon->get_id(), $first_three_letters . '_coupon_start_time_' . $i, true );
				$additional_expiry_time = get_post_meta( $coupon->get_id(), $first_three_letters . '_coupon_expiry_time_' . $i, true );

				$additional_start_time =  strtotime( $additional_start_time );
				$additional_expiry_time =  strtotime( $additional_expiry_time );

				if (  $current_server_time >= $additional_start_time && $current_server_time <= $additional_expiry_time ) {
					return true;
				}
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_coupon_on_different_days
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply/validate the coupon on different days of the week.
	 */
	private function apply_coupon_on_different_days( $valid, $coupon )
	{
		if ( ! $this->apply_to_single_day( $valid, $coupon ) ) {
			add_filter( 'woocommerce_coupon_error', [ $this, 'custom_coupon_error_message_for_dynamic_days_and_hours' ] , 10, 2 );

			return false;
		} else {
			return true;
		}
	}

	/**
	 * @package hexcoupon
	 * @author Wphex
	 * @since 1.0.0
	 * @method custom_coupon_error_message_for_dynamic_days_and_hours
	 * @param string $err
	 * @param int $err_code
	 * @return string
	 * Display custom error message for invalid coupon.
	 */
	public function custom_coupon_error_message_for_dynamic_days_and_hours( $err, $err_code )
	{
		if ( $err_code === 100 ) {
			// Change the error message for the INVALID_FILTERED error here
			$err = esc_html__( 'Coupon is not valid at this hour, please come in another time.', 'hex-coupon-for-woocommerce');
		}

		return $err;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_coupon
	 * @param bool $valid
	 * @param object $coupon
	 * @return bool
	 * Apply/validate the coupon starting date.
	 */
	public function apply_coupon( $valid, $coupon )
	{
		// get 'apply_days_hours_of_week' meta value
		$days_hours_of_week = get_post_meta( $coupon->get_id(), 'apply_days_hours_of_week', true );

		// get 'apply_coupon_starting_date' return value
		$apply_coupon_starting_date = $this->apply_coupon_starting_date( $valid, $coupon );

		// get 'apply_coupon_on_different_days' return value
		$apply_coupon_on_different_days = $this->apply_coupon_on_different_days( $valid, $coupon );

		$coupon_apply_on_every_day = [
			'coupon_apply_on_saturday',
			'coupon_apply_on_sunday',
			'coupon_apply_on_monday',
			'coupon_apply_on_tuesday',
			'coupon_apply_on_wednesday',
			'coupon_apply_on_thursday',
			'coupon_apply_on_friday',
		];

		if ( $apply_coupon_starting_date ) {
			if ('yes' === $days_hours_of_week) {
				if ($apply_coupon_on_different_days) {
					return $valid;
				}

				foreach ($coupon_apply_on_every_day as $single_day) {
					$single_day = get_post_meta($coupon->get_id(), $single_day, true);
					if ('1' == $single_day) {
						return false;
					}
				}
			}
			return true;
		}

		return false;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method delete_meta_value
	 * @param int $coupon_id
	 * @return void
	 * Delete meta value.
	 */
	public function delete_meta_value( $coupon_id )
	{
		$days_hours_of_week = get_post_meta( $coupon_id, 'apply_days_hours_of_week', true );

		$coupon_apply_on_day = [
			'coupon_apply_on_saturday',
			'coupon_apply_on_sunday',
			'coupon_apply_on_monday',
			'coupon_apply_on_tuesday',
			'coupon_apply_on_wednesday',
			'coupon_apply_on_thursday',
			'coupon_apply_on_friday',
		];

		$meta_start_key =  [
			'sat_coupon_start_time',
			'sun_coupon_start_time',
			'mon_coupon_start_time',
			'tue_coupon_start_time',
			'wed_coupon_start_time',
			'thu_coupon_start_time',
			'fri_coupon_start_time'
		];

		$meta_expiry_key = [
			'sat_coupon_expiry_time',
			'sun_coupon_expiry_time',
			'mon_coupon_expiry_time',
			'tue_coupon_expiry_time',
			'wed_coupon_expiry_time',
			'thu_coupon_expiry_time',
			'fri_coupon_expiry_time'
		];

		$days = [
			'saturday' => 'sat_coupon',
			'sunday' => 'sun_coupon',
			'monday' => 'mon_coupon',
			'tuesday' => 'tue_coupon',
			'wednesday' => 'wed_coupon',
			'thursday' => 'thu_coupon',
			'friday' => 'fri_coupon'
		];

		$total_hours_count = [
			'total_hours_count_saturday',
			'total_hours_count_sunday',
			'total_hours_count_monday',
			'total_hours_count_tuesday',
			'total_hours_count_wednesday',
			'total_hours_count_thursday',
			'total_hours_count_friday',
		];

		if ( empty( $days_hours_of_week ) ) {
			foreach( $meta_start_key as $value ) {
				delete_post_meta( $coupon_id,$value );
			}

			foreach ( $meta_expiry_key as $value ) {
				delete_post_meta( $coupon_id,$value );
			}

			foreach ( $coupon_apply_on_day as $value ) {
				delete_post_meta( $coupon_id, $value );
			}

			foreach ( $total_hours_count as $value ) {
				delete_post_meta( $coupon_id, $value );
			}
		}

		foreach ( $days as $day => $coupon_prefix ) {
			$coupon_apply_on_day = get_post_meta( $coupon_id, 'coupon_apply_on_' . $day, true );

			if ( empty( $coupon_apply_on_day ) ) {
				delete_post_meta( $coupon_id,  $coupon_prefix . '_start_time' );
				delete_post_meta( $coupon_id,  $coupon_prefix . '_expiry_time' );
			}
		}

		$customer_gets_as_free = get_post_meta( $coupon_id, 'customer_gets_as_free', true );
		$customer_purchases = get_post_meta( $coupon_id, 'customer_purchases', true );

		if ( 'product_categories' === $customer_purchases ) {
			delete_post_meta( $coupon_id, 'add_specific_product_to_purchase' );
		}

		if ( 'a_specific_product' === $customer_purchases || 'a_combination_of_products' === $customer_purchases || 'any_products_listed_below' === $customer_purchases ) {
			delete_post_meta( $coupon_id, 'add_categories_to_purchase' );
		}

		if ( 'same_product_added_to_cart' === $customer_gets_as_free ) {
			delete_post_meta( $coupon_id,'add_specific_product_for_free' );
		}

		$discount_type = get_post_meta( $coupon_id, 'discount_type', true );

		if ( 'percent' === $discount_type || 'fixed_cart' === $discount_type || 'fixed_product' === $discount_type ) {
			$bogo_meta_values = [
				'customer_purchases',
				'add_specific_product_to_purchase',
				'add_categories_to_purchase',
				'customer_gets_as_free',
				'add_specific_product_for_free',
			];

			foreach ( $bogo_meta_values as $single_value ) {
				delete_post_meta( $coupon_id, $single_value );
			}
		}
	}
}
