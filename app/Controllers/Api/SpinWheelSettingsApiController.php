<?php
namespace HexCoupon\App\Controllers\Api;
if(!defined('ABSPATH')) exit;

use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Traits\NonceVerify;
use Kathamo\Framework\Lib\Controller;

class SpinWheelSettingsApiController extends Controller
{
	use SingleTon, NonceVerify;

	private $allowed_html = [
		'a'      => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'u'      => [],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		'p'      => [],
		'ul'     => [],
		'ol'     => [],
		'li'     => [],
		'h1'     => [],
		'h2'     => [],
		'h3'     => [],
		'h4'     => [],
		'h5'     => [],
		'h6'     => [],
		'img'    => [
			'src'   => [],
			'alt'   => [],
			'width' => [],
			'height'=> [],
		],
		'blockquote' => [],
		'code'   => [],
		'pre'    => [],
		'div'    => [
			'class' => [],
			'id'    => [],
		],
		'span'   => [
			'class' => [],
			'id'    => [],
		],
	];

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_action( 'admin_post_spin_wheel_general_settings_save', [ $this, 'spin_wheel_general_settings_save' ] );
		add_action( 'admin_post_spin_wheel_popup_settings_save', [ $this, 'spin_wheel_popup_settings_save' ] );
		add_action( 'admin_post_spin_wheel_wheel_settings_save', [ $this, 'spin_wheel_wheel_settings_save' ] );
		add_action( 'admin_post_spin_wheel_content_settings_save', [ $this, 'spin_wheel_content_settings_save' ] );
		add_action( 'admin_post_spin_wheel_text_settings_save', [ $this, 'spin_wheel_text_settings_save' ] );
		add_action( 'admin_post_spin_wheel_coupon_settings_save', [ $this, 'spin_wheel_coupon_settings_save' ] );
		add_action( 'wp_ajax_update_spin_count', [ $this, 'update_spin_count' ] );
		add_action( 'wp_ajax_nopriv_update_spin_count', [ $this, 'update_spin_count' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_general_settings_save
	 * @return void
	 * Saving all the settings of spin wheel general settings
	 */
	public function spin_wheel_general_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$spin_wheel_general = [
				'enableSpinWheel' => isset( $dataArray['settings']['enableSpinWheel'] ) ? rest_sanitize_boolean( $dataArray['settings']['enableSpinWheel'] ) : '',
				'spinPerEmail' => isset( $dataArray['settings']['spinPerEmail'] ) ? sanitize_text_field( $dataArray['settings']['spinPerEmail'] ) : '',
				'delayBetweenSpins' => isset( $dataArray['settings']['delayBetweenSpins'] ) ? sanitize_text_field( $dataArray['settings']['delayBetweenSpins'] ) : '',
			];
			update_option( 'spinWheelGeneral', $spin_wheel_general );


			wp_send_json( $_POST );
		} else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403 ); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_popup_settings_save
	 * @return void
	 * Saving all the settings of spin wheel popup settings
	 */
	public function spin_wheel_popup_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$spin_popup_settings = [
				'iconColor' => isset( $dataArray['settings']['iconColor'] ) ? sanitize_text_field( $dataArray['settings']['iconColor'] ) : '',
				'popupInterval' => isset( $dataArray['settings']['popupInterval'] ) ? sanitize_text_field( $dataArray['settings']['popupInterval'] ) : '',
				'showOnlyHomepage' => isset( $dataArray['settings']['showOnlyHomepage'] ) ? rest_sanitize_boolean( $dataArray['settings']['showOnlyHomepage'] ) : '',
				'showOnlyBlogPage' => isset( $dataArray['settings']['showOnlyBlogPage'] ) ? rest_sanitize_boolean( $dataArray['settings']['showOnlyBlogPage'] ) : '',
				'showOnlyShopPage' => isset( $dataArray['settings']['showOnlyShopPage'] ) ? rest_sanitize_boolean( $dataArray['settings']['showOnlyShopPage'] ) : '',
				'selectedPages' => isset( $dataArray['settings']['selectedPages'] ) ? $dataArray['settings']['selectedPages'] : '',
			];
			update_option( 'spinWheelPopup', $spin_popup_settings );

			wp_send_json( $_POST );
		} else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403 ); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_wheel_settings_save
	 * @return void
	 * Saving all the settings of spin wheel wheel settings
	 */
	public function spin_wheel_wheel_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$spin_wheel_wheel_settings = [
				'titleText' => isset( $dataArray['settings']['titleText'] ) ? sanitize_text_field( $dataArray['settings']['titleText'] ) : '',
				'titleColor' => isset( $dataArray['settings']['titleColor'] ) ? sanitize_text_field( $dataArray['settings']['titleColor'] ) : '',
				'textColor' => isset( $dataArray['settings']['textColor'] ) ? sanitize_text_field( $dataArray['settings']['textColor'] ) : '',
				'wheelDescription' => isset( $dataArray['settings']['wheelDescription'] ) ? wp_kses( $dataArray['settings']['wheelDescription'], $this->allowed_html ) : '',
				'wheelDescriptionColor' => isset( $dataArray['settings']['wheelDescriptionColor'] ) ? sanitize_text_field( $dataArray['settings']['wheelDescriptionColor'] ) : '',
				'buttonText' => isset( $dataArray['settings']['buttonText'] ) ? sanitize_text_field( $dataArray['settings']['buttonText'] ) : '',
				'buttonColor' => isset( $dataArray['settings']['buttonColor']) ? sanitize_text_field( $dataArray['settings']['buttonColor'] ) : '',
				'buttonBGColor' => isset( $dataArray['settings']['buttonBGColor']) ? sanitize_text_field( $dataArray['settings']['buttonBGColor'] ) : '',
				'enableYourName' => isset( $dataArray['settings']['enableYourName'] ) ? rest_sanitize_boolean( $dataArray['settings']['enableYourName'] ) : '',
				'enableEmailAddress' => isset( $dataArray['settings']['enableEmailAddress'] ) ? sanitize_text_field( $dataArray['settings']['enableEmailAddress'] ) : '',
				'gdprMessage' => isset( $dataArray['settings']['gdprMessage'] ) ? wp_kses( $dataArray['settings']['gdprMessage'], $this->allowed_html ) : '',
			];

			update_option( 'spinWheelWheel', $spin_wheel_wheel_settings );

			wp_send_json( $_POST );
		} else {
			wp_send_json([
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_content_settings_save
	 * @return void
	 * Saving all the settings of spin wheel content settings
	 */
	public function spin_wheel_content_settings_save() 
	{
		if ( $this->verify_nonce( 'POST' ) ) {
			$formData = json_encode( $_POST );
			$dataArray = json_decode( $formData, true );
	
			$spin_wheel_content_settings = [];
	
			// Loop over each setting and build the array
			foreach ( $dataArray['settings'] as $index => $setting ) {
				$contentKey = "content" . ($index + 1); // Corrected index usage
				$spin_wheel_content_settings[$contentKey] = [
					'couponType'  => isset( $setting['couponType'] ) ? sanitize_text_field( $setting['couponType'] ) : '',
					'label'       => isset( $setting['label'] ) ? sanitize_text_field( $setting['label'] ) : '',
					'value'       => isset( $setting['value'] ) ? sanitize_text_field( $setting['value'] ) : '',
					'color'       => isset( $setting['color'] ) ? sanitize_text_field( $setting['color'] ) : '',
				];
			}
	
			// Save the settings to the options table
			update_option( 'spinWheelContent', $spin_wheel_content_settings );
	
			wp_send_json_success( $spin_wheel_content_settings );
		} else {
			wp_send_json_error( ['error' => 'Nonce verification failed'], 403 );
		}
	}	

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_text_settings_save
	 * @return void
	 * Saving all the settings of spin wheel text settings
	 */
	public function spin_wheel_text_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$spin_wheel_text_settings = [
				'emailSubject' => isset( $dataArray['settings']['emailSubject'] ) ? sanitize_text_field( $dataArray['settings']['emailSubject'] ) : '',
				'emailContent' => isset( $dataArray['settings']['emailContent'] ) ? wp_kses( $dataArray['settings']['emailContent'], $this->allowed_html ) : '',
				'frontendMessageIfWin' => isset( $dataArray['settings']['frontendMessageIfWin'] ) ? sanitize_text_field( $dataArray['settings']['frontendMessageIfWin'] ) : '',
				'frontendMessageIfLost' => isset( $dataArray['settings']['frontendMessageIfLost'] ) ? sanitize_text_field( $dataArray['settings']['frontendMessageIfLost'] ) : '',
			];
			update_option( 'spinWheelText', $spin_wheel_text_settings );

			wp_send_json( $_POST );
		} else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403 ); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_coupon_settings_save
	 * @return void
	 * Saving all the settings of spin wheel coupon settings
	 */
	public function spin_wheel_coupon_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$spin_wheel_coupon_settings = [
				'spinAllowFreeShipping' => isset( $dataArray['settings']['spinAllowFreeShipping'] ) ? rest_sanitize_boolean( $dataArray['settings']['spinAllowFreeShipping'] ) : '',
				'spinMinimumSpend' => isset( $dataArray['settings']['spinMinimumSpend'] ) ? sanitize_text_field( $dataArray['settings']['spinMinimumSpend'] ) : '',
				'spinMaximumSpend' => isset( $dataArray['settings']['spinMaximumSpend'] ) ? sanitize_text_field( $dataArray['settings']['spinMaximumSpend'] ) : '',
				'spinIndividualSpendOnly' => isset( $dataArray['settings']['spinIndividualSpendOnly'] ) ? rest_sanitize_boolean( $dataArray['settings']['spinIndividualSpendOnly'] ) : '',
				'spinExcludeSaleItem' => isset( $dataArray['settings']['spinExcludeSaleItem'] ) ? rest_sanitize_boolean( $dataArray['settings']['spinExcludeSaleItem'] ) : '',

				'spinIncludeProducts' => isset( $dataArray['settings']['spinIncludeProducts'] ) ? $dataArray['settings']['spinIncludeProducts'] : '',
				'spinExcludeProducts' => isset( $dataArray['settings']['spinExcludeProducts'] ) ? $dataArray['settings']['spinExcludeProducts'] : '',
				'spinIncludeCategories' => isset( $dataArray['settings']['spinIncludeCategories'] ) ? $dataArray['settings']['spinIncludeCategories'] : '',
				'spinExcludeCategories' => isset( $dataArray['settings']['spinExcludeCategories'] ) ? $dataArray['settings']['spinExcludeCategories'] : '',
				'spinUsageLimitPerCoupon' => isset( $dataArray['settings']['spinUsageLimitPerCoupon'] ) ? sanitize_text_field( $dataArray['settings']['spinUsageLimitPerCoupon'] ) : '',
				'spinLimitUsageToXItems' => isset( $dataArray['settings']['spinLimitUsageToXItems'] ) ? sanitize_text_field( $dataArray['settings']['spinLimitUsageToXItems'] ) : '',
				'spinUsageLimitPerUser' => isset( $dataArray['settings']['spinUsageLimitPerUser'] ) ? sanitize_text_field( $dataArray['settings']['spinUsageLimitPerUser'] ) : '',
			];
			update_option( 'spinWheelCoupon', $spin_wheel_coupon_settings );			

			wp_send_json( $_POST );
		} else {
			wp_send_json([
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	public function get_last_created_coupon_by_email( $user_email ) {
		// Ensure WooCommerce functions are available
		if ( !function_exists( 'wc_get_orders' ) ) {
			return null;
		}
	
		// Query the coupons
		$args = array(
			'posts_per_page' => 1, // Get the last coupon only
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'customer_email',
					'value' => $user_email,
					'compare' => 'LIKE'
				),
			),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
	
		$coupons = get_posts($args);
	
		if (!empty($coupons)) {
			return $coupons[0]; // Return the latest coupon
		}
	
		return null; // Return null if no coupon is found
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method update_spin_count
	 * @return void
	 * update the spin count
	 */
	public function update_spin_count() 
	{
		// Initialize $user_email variable
		$user_email = '';

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email = $current_user->user_email;	
		} else {
			// Retrieve the selected offer sent from JavaScript
			$user_email = sanitize_text_field( $_POST['userEmail'] );
			$user_name = sanitize_text_field( $_POST['userName'] );
			
			if ( get_user_by( 'email', $_POST['userEmail'] ) ) {
				wp_send_json_error( array( 'message' => 'This email already exists.' ) ); // Return a specific error message
				return;
			} else {
				$this->create_customer_user( $user_name, $user_email );
			}
		}

		// Get the current user ID, handle logged-in and non-logged-in users
		$user_id = get_current_user_id();

		// For non-logged-in users, we can get user ID after creation
		if ( !$user_id && !empty( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		// Get the current spin count from user meta
		$spin_count = get_user_meta( $user_id, 'user_spin_count', true );

		// If there is no spin count yet, initialize it to 0
		if ( $spin_count === '' ) {
			$spin_count = 0;
		}

		// Increment the spin count
		$spin_count++;

		// Update the spin count in user meta
		update_user_meta( $user_id, 'user_spin_count', $spin_count );
		// Update term and condition value
		update_user_meta( $user_id, 'spin_wheel_accepted_term_condition', true );

		$coupon_type = sanitize_text_field( $_POST['couponType'] );
		$value = sanitize_text_field( $_POST['couponValue'] );

		if ( $coupon_type == 'PERCENTAGE DISCOUNT' ) {
			$discount_type = 'percent';
		} elseif ( $coupon_type == 'FIXED PRODUCT DISCOUNT' ) {
			$discount_type = 'fixed_product';
		} elseif ( $coupon_type == 'FIXED CART DISCOUNT' ) {
			$discount_type = 'fixed_cart';
		} else {
			return;
		}

		// Create coupon for the email provided
		$this->create_woocommerce_coupon( $value, $discount_type, $user_email );

		$emailText = 'You have win a coupon via spin wheel';

		$code = $this->get_last_created_coupon_by_email( $user_email );

		// Set the email parameters
		$to = $user_email;
		$subject = 'Coupon creation via spin wheel';
		$message = $this->email_template( $emailText, $discount_type, $value, $code->post_title );
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		// Send the email
		wp_mail( $to, $subject, $message, $headers );

		// Return the updated spin count as a JSON response
		wp_send_json_success( $spin_count );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method email_template
	 * @return void
	 * Email template for sending email
	 */
	public function email_template( $emailText, $coupon_type, $value, $code ) {
		ob_start();
		?>
		<!doctype html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="ie=edge">
			<title>Congratulations Email</title>
			<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
	
			<style>
				* {
					font-family: 'Open Sans', sans-serif;
				}
				body {
					background-color: #e3ebf2;
					padding: 40px 0;
				}
				.mail-container {
					max-width: 650px;
					margin: 0 auto;
					text-align: center;
					background-color: #ffffff;
					box-shadow: 0 0 20px rgba(0,0,0,0.1);
				}
				.inner-wrap {
					text-align: left;
					padding: 0 40px 40px 40px;
				}
				.inner-wrap h2 {
					color: #ffffff;
					background-color: #5f85a4;
					padding: 20px;
					margin: 0;
					font-size: 24px;
					text-align: center;
				}
				.inner-wrap p {
					font-size: 16px;
					line-height: 26px;
					color: #333333;
					margin: 20px 0;
				}
				.coupon-code {
					color: #4e7db2;
					font-size: 18px;
					font-weight: bold;
				}
			</style>
		</head>
		<body>
		<div class="mail-container">
			<div class="inner-wrap">
				<h2><?php esc_html_e( 'Congratulations', 'hex-coupon-for-woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Dear Customer,', 'hex-coupon-for-woocommerce' ); ?></p>
				<p><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $emailText ) ); ?></p>
				<p><?php esc_html_e( 'Thank you!', 'hex-coupon-for-woocommerce' ); ?></p>
				<p><strong><?php esc_html_e( 'Coupon Type:', 'hex-coupon-for-woocommerce' ); ?></strong> <span class="coupon-code"><?php echo $coupon_type; ?></span></p>
				<p><strong><?php esc_html_e( 'Coupon Code:', 'hex-coupon-for-woocommerce' ); ?></strong> <span class="coupon-code"><?php echo $code; ?></span></p>
				<p><strong><?php esc_html_e( 'Coupon Value:', 'hex-coupon-for-woocommerce' ); ?></strong> <span class="coupon-code"><?php echo $value; ?></span></p>
				<p><?php esc_html_e( 'Yours sincerely,', 'hex-coupon-for-woocommerce' ); ?></p>
				<p><strong><?php esc_html_e( 'The', 'hex-coupon-for-woocommerce' ); ?> <?php echo get_bloginfo( 'title' ); ?> <?php esc_html_e( 'Team', 'hex-coupon-for-woocommerce' ); ?></strong></p>
			</div>
		</div>
		</body>
		</html>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_woocommerce_coupon
	 * @return void
	 * Create new user
	 */
	public function create_woocommerce_coupon( $value, $discount_type, $user_email ) 
	{
		// If the email isn't provided, fall back to the current user's email
		if ( empty( $user_email ) ) {
			$user_data = get_userdata( get_current_user_id() );
			$user_email = $user_data->user_email;
		}

		$spin_wheel_coupon = get_option( 'spinWheelCoupon' );
		$minimum_spend = $spin_wheel_coupon['spinMinimumSpend'];
		$maximum_spend = $spin_wheel_coupon['spinMaximumSpend'];
		$individual_use_only = $spin_wheel_coupon['spinIndividualSpendOnly'];
		$exclude_sale_item = $spin_wheel_coupon['spinExcludeSaleItem'];
		$exclude_products = $spin_wheel_coupon['spinExcludeProducts'];
		$exclude_categories = $spin_wheel_coupon['spinExcludeCategories'];
		$usage_limit_per_coupon = $spin_wheel_coupon['spinUsageLimitPerCoupon'];
		$limit_usage_to_xitems = $spin_wheel_coupon['spinLimitUsageToXItems'];
		$usage_limit_per_user = $spin_wheel_coupon['spinUsageLimitPerUser'];

		// Define the coupon details
		$code = $this->get_last_created_coupon_by_email( $user_email );
		$last_code = $code->post_title;
		$get_code_number_part = explode( 'l', $last_code );
		$final_code = 'SpinWheel' . get_current_user_id() . $get_code_number_part[1] + 1;
		$discount_amount = $value; // The discount amount
		
		// Check if a coupon with the same code already exists
		if ( ! wc_get_coupon_id_by_code( $final_code ) ) {
			// Create a new coupon
			$coupon = new \WC_Coupon();
			$coupon->set_code( $final_code );
			$coupon->set_amount( $discount_amount );
			$coupon->set_discount_type( $discount_type );
			$coupon->set_description( 'You got this discount from spin wheel' );
			$coupon->set_individual_use( $individual_use_only ); // Prevents other coupons from being used with this coupon
			$coupon->set_exclude_sale_items( $exclude_sale_item );
			$coupon->set_excluded_product_ids( $exclude_products );
			$coupon->set_excluded_product_categories( $exclude_categories );
			$coupon->set_usage_limit( $usage_limit_per_coupon ); // The number of times the coupon can be used
			$coupon->set_usage_limit_per_user( $usage_limit_per_user ); // The number of times the coupon can be used per customer
			$coupon->set_limit_usage_to_x_items( $limit_usage_to_xitems );
			$coupon->set_minimum_amount( $minimum_spend ); // Minimum spend required to use the coupon
			$coupon->set_maximum_amount( $maximum_spend ); // Maximum spend required to use the coupon
			$coupon->set_email_restrictions( array($user_email) ); // Restrict to specific emails

			// Save the coupon
			$coupon_id = $coupon->save();

			if ( ! $coupon_id ) {
				error_log( 'Coupon creation failed: Coupon Id: ' . $coupon_id );
			}
		} else {
			error_log( 'Coupon code already exists' );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_general_settings_save
	 * @return void
	 * Create a new user after spin wheel with provided email
	 */
	public function create_customer_user( $user_name, $user_email ) {
		$email = $user_email;
		$name = $user_name;
		$role = 'customer';

		// Set the username as the name
		$username = sanitize_user( $name, true );
	
		// Check if the username or email already exists
		if ( username_exists( $username ) || email_exists( $email ) ) {
			return 'Username or email already exists.';
		}

		$password = wp_generate_password( 12, true, true );
	
		// Create the user
		$user_id = wp_create_user( $username, $password, $email, $role );

		// Set the email parameters
		$to = $email;
		$subject = 'New WordPress WooCommerce customer account creation.';
		$message = 'A new customer account has been created for you. Your login credentials are <p>Email: ' . $email . '</p><p>Password: ' . $password . '</p>';
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		// Send the email
		wp_mail( $to, $subject, $message, $headers );
	
		if ( is_wp_error( $user_id ) ) {
			return 'User creation failed: ' . $user_id->get_error_message();
		}
	}	
	
}
