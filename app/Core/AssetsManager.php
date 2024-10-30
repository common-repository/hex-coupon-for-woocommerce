<?php
namespace HexCoupon\App\Core;

use HexCoupon\App\Core\Helpers\StoreCreditPaymentHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class AssetsManager
{
	use SingleTon;
	private $version = '';
	private $configs = [];

	private $is_pro_active;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return void
	 * Registering all the hooks that are needed
	 */
	public function register()
	{
		$this->configs = hexcoupon_get_config();

		$this->is_pro_active = defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE ? true : false;

		$this->before_register_assets();

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'public_scripts' ] );
		// enqueuing scripts for block pattern
		add_action( 'enqueue_block_assets', [ $this, 'block_scripts' ] );
		// loading js files for translation
		add_action( 'admin_enqueue_scripts', [ $this, 'load_translation_for_admin_side' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'load_translation_for_public_side' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'spin_wheel_inline_css' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method before_register_assets
	 * @return void
	 *
	 */
	private function before_register_assets()
	{
		if ( $this->configs['dev_mode'] ) {
			return $this->version = time();
		}

		$this->version = $this->configs['plugin_version'];
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method admin_scripts
	 * @return void
	 * Enqueuing all the scripts for back-end
	 */
	public function admin_scripts()
	{
		$folder_prefix = hexcoupon_get_config('dev_mode') ? '/dev' : '/dist';
		$js_file_extension = hexcoupon_get_config('dev_mode') ? '.js' : '.min.js';
		$css_file_extension = hexcoupon_get_config('dev_mode') ? '.css' : '.min.css';

		if ( ( str_contains( $_SERVER['REQUEST_URI'], 'post-new.php' ) && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'shop_coupon') ||
			( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) ) {

			wp_enqueue_script(
				hexcoupon_prefix( 'admin' ),
				hexcoupon_asset_url( $folder_prefix . "/admin/js/admin" . $js_file_extension ),
				['jquery', 'select2', 'wp-i18n'],
				$this->version,
				true
			);

			wp_enqueue_script(
				hexcoupon_prefix( 'flatpickr' ),
				hexcoupon_asset_url( $folder_prefix . "/admin/js/flatpickr.min.js" ),
				[ 'jquery'],
				$this->version,
				true
			);

			wp_enqueue_script(
				hexcoupon_prefix( 'toast' ),
				hexcoupon_asset_url( $folder_prefix . "/admin/js/toast.min.js" ),
				[ 'jquery'],
				$this->version,
				true
			);

			wp_enqueue_style(
				hexcoupon_prefix( 'admin' ),
				hexcoupon_asset_url( $folder_prefix. "/admin/css/admin" .$css_file_extension ),
				[],
				$this->version,
				'all'
			);

			wp_enqueue_style(
				hexcoupon_prefix( 'flatpickr' ),
				hexcoupon_asset_url( $folder_prefix . "/admin/css/flatpickr.min.css" ),
				[],
				$this->version,
				'all'
			);

			wp_enqueue_style(
				hexcoupon_prefix( 'toast' ),
				hexcoupon_asset_url( $folder_prefix . "/admin/css/toast.min.css" ),
				[],
				$this->version,
				'all'
			);

		}

		wp_enqueue_script(
			hexcoupon_prefix( 'all-coupon-page' ),
			hexcoupon_asset_url( $folder_prefix . "/admin/js/all-coupon-page" . $js_file_extension ),
			['jquery', 'select2', 'wp-i18n'],
			$this->version,
			true
		);

		wp_enqueue_style(
			hexcoupon_prefix( 'hexcoupon-admin-notice' ),
			hexcoupon_asset_url( $folder_prefix . "/admin/css/hex-dashboard-notice" . $css_file_extension ),
			[],
			$this->version,
			'all'
		);

		wp_enqueue_style(
			hexcoupon_prefix( 'all-coupon-page' ),
			hexcoupon_asset_url( $folder_prefix . "/admin/css/all-coupon-page" . $css_file_extension ),
			[],
			$this->version,
			'all'
		);

		//load react js and css only on the hexcoupon plugin page
		if ( ! $this->is_pro_active ) {
			$screen = get_current_screen();

			if ( $screen->base === "toplevel_page_hexcoupon-page" ){
				wp_enqueue_script(
					hexcoupon_prefix( 'main' ),
					hexcoupon_url( "dist/assets/index.js" ),
					['jquery','wp-element','wp-i18n'],
					$this->version,
					true
				);

				wp_enqueue_style(
					hexcoupon_prefix( 'main' ),
					hexcoupon_url( "dist/assets/index.css" ),
					[],
					$this->version,
					"all"
				);
			}
		}

		$coupon_dashboard_label_text = [
			'couponsCreatedLabel' => esc_html__( 'Coupons Created', 'hex-coupon-for-woocommerce' ),
			'couponsRedeemedLabel' => esc_html__( 'Coupons Redeemed', 'hex-coupon-for-woocommerce' ),
			'couponsActiveLabel' => esc_html__( 'Coupons Active', 'hex-coupon-for-woocommerce' ),
			'couponsExpiredLabel' => esc_html__( 'Coupons Expired', 'hex-coupon-for-woocommerce' ),
			'redeemedCouponValueLabel' => esc_html__( 'Redeemed Coupon Amount', 'hex-coupon-for-woocommerce' ),
			'sharableUrlCouponsLabel' => esc_html__( 'Sharable Url Coupons', 'hex-coupon-for-woocommerce' ),
			'bogoCouponlabel' => esc_html__( 'Bogo Coupons', 'hex-coupon-for-woocommerce' ),
			'geographicRestrictionLabel' => esc_html__( 'Geographically Restricted', 'hex-coupon-for-woocommerce' ),
			'couponInsightsLabel' => esc_html__( 'Coupon Insights', 'hex-coupon-for-woocommerce' ),
			'thisWeekLabel' => esc_html__( 'This Week', 'hex-coupon-for-woocommerce' ),
			'yesterdayLabel' => esc_html__( 'Yesterday', 'hex-coupon-for-woocommerce' ),
			'todayLabel' => esc_html__( 'Today', 'hex-coupon-for-woocommerce' ),
		];

		wp_localize_script( hexcoupon_prefix( 'main' ), 'hexCuponData', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postUrl' => admin_url( 'admin-post.php' ),
			'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
			'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
			'translate_array' => [
				'couponsCreatedLabel' => $coupon_dashboard_label_text['couponsCreatedLabel'],
				'couponsRedeemedLabel' => $coupon_dashboard_label_text['couponsRedeemedLabel'],
				'couponsActiveLabel' => $coupon_dashboard_label_text['couponsActiveLabel'],
				'couponsExpiredLabel' => $coupon_dashboard_label_text['couponsExpiredLabel'],
				'redeemedCouponValueLabel' => $coupon_dashboard_label_text['redeemedCouponValueLabel'],
				'sharableUrlCouponsLabel' => $coupon_dashboard_label_text['sharableUrlCouponsLabel'],
				'bogoCouponlabel' => $coupon_dashboard_label_text['bogoCouponlabel'],
				'geographicRestrictionLabel' => $coupon_dashboard_label_text['geographicRestrictionLabel'],
				'couponInsightsLabel' => $coupon_dashboard_label_text['couponInsightsLabel'],
				'thisWeekLabel' => $coupon_dashboard_label_text['thisWeekLabel'],
				'yesterdayLabel' => $coupon_dashboard_label_text['yesterdayLabel'],
				'todayLabel' => $coupon_dashboard_label_text['todayLabel'],
			]
		] );

		wp_localize_script( hexcoupon_prefix( 'main' ), 'loyaltyProgramData', [
			'check' => 'hello',
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postUrl' => admin_url( 'admin-post.php' ),
			'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
			'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
		] );

		wp_localize_script( hexcoupon_prefix( 'main' ), 'spinWheelSettingsData', [
			'check' => 'hello',
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postUrl' => admin_url( 'admin-post.php' ),
			'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
			'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
		] );

		wp_localize_script( hexcoupon_prefix( 'main' ), 'loyaltyProgramLogs', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postUrl' => admin_url( 'admin-post.php' ),
			'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
			'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
		] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method block_scripts
	 * @return void
	 * Enqueuing all the scripts for block front-end
	 */
	public function block_scripts()
	{
		$point_loyalties = get_option( 'loyalty_program_enable_settings' );
		$enable_point_loyalties = $point_loyalties['enable'] ?? 0;

		$points_on_purchase = get_option( 'pointsOnPurchase' );
		$points_on_purchase_enable = $points_on_purchase['enable'] ?? 0;

		$store_credit_enable_data = get_option( 'store_credit_enable_data' );
		$store_credit_enable_data = $store_credit_enable_data['enable'] ?? 0;

		if ( class_exists( 'WooCommerce' ) && is_checkout() ) {
			// Admin back-end
			if ( ! $this->is_pro_active && is_admin() ) {
				wp_enqueue_script(
					hexcoupon_prefix( 'checkout-main' ),
					hexcoupon_url( 'build/index.js' ),
					[ 'jquery','wp-element' ],
					$this->version,
					true
				);
			}

			// user front-end
			if ( $enable_point_loyalties && $points_on_purchase_enable ) {
				wp_enqueue_script(
					'checkout-block-notices',
					plugins_url('hex-coupon-for-woocommerce/assets/dev/public/js/checkout-block-notices.js' ),
					[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wc-blocks-checkout', 'wp-data' ],
					plugins_url('hex-coupon-for-woocommerce/assets/dev/public/js/checkout-block-notices.js' ),
					true
				);

				$user_id = get_current_user_id();
				wp_localize_script( 'checkout-block-notices', 'pointsForCheckoutBlock', [
					'ajax_url' => admin_url( 'admin-ajax.php '),
					'nonce' => wp_create_nonce( 'custom_nonce' ),
					'user_id' => $user_id,
				] );
			}

			if ( ! $this->is_pro_active && $store_credit_enable_data ) {
				// enqueuing file for 'WooCommerce Checkout' page
				wp_enqueue_script(
					hexcoupon_prefix( 'checkout-block' ),
					hexcoupon_url( "build/index.js" ),
					['jquery','wp-element'],
					$this->version,
					true
				);
			}

			if ( ! $this->is_pro_active && $store_credit_enable_data ) {
				// enqueuing file for 'WooCommerce Checkout' page
				wp_enqueue_script(
					hexcoupon_prefix( 'checkout-frontend' ),
					hexcoupon_url( "build/checkout-block-frontend.js" ),
					['jquery','wp-element'],
					$this->version,
					true
				);
			}
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method public_scripts
	 * @return void
	 * Enqueuing all the scripts for front-end
	 */
	public function public_scripts()
	{
		$folder_prefix = hexcoupon_get_config('dev_mode') ? '/dev' : '/dist';
		$js_file_extension = hexcoupon_get_config('dev_mode') ? '.js' : '.min.js';
		$css_file_extension = hexcoupon_get_config('dev_mode') ? '.css' : '.min.css';

		wp_enqueue_script(
			hexcoupon_prefix( 'public' ),
			hexcoupon_asset_url( $folder_prefix . "/public/js/public" . $js_file_extension ),
			['jquery', 'wp-i18n'],
			$this->version,
			true
		);
		
		// Get the current user ID
		$user_id = get_current_user_id();

		// Get the current spin count from user meta
		$spin_count = get_user_meta( $user_id, 'user_spin_count', true );

		$spin_wheel_general = get_option( 'spinWheelGeneral' );
		$enable_spin_wheel = ! empty( $spin_wheel_general['enableSpinWheel'] ) ? $spin_wheel_general['enableSpinWheel'] : 0;
		$spin_per_email = ! empty( $spin_wheel_general['spinPerEmail'] ) ? $spin_wheel_general['spinPerEmail'] : 1;
		$delay_between_spins = ! empty( $spin_wheel_general['delayBetweenSpins'] ) ? $spin_wheel_general['delayBetweenSpins']: 0;

		$spin_wheel_popup = get_option( 'spinWheelPopup' );
		$popup_interval = ! empty( $spin_wheel_popup['popupInterval'] ) ? $spin_wheel_popup['popupInterval'] : 0;

		$spin_wheel_text = get_option( 'spinWheelText' );
		$frontend_message_if_win = ! empty( $spin_wheel_text['frontendMessageIfWin'] ) ? $spin_wheel_text['frontendMessageIfWin'] : 'You have won!';
		$frontend_message_if_loss = ! empty( $spin_wheel_text['frontendMessageIfLost'] ) ? $spin_wheel_text['frontendMessageIfLost'] : 'You have lost!';
		$email_subject = ! empty( $spin_wheel_text['emailSubject'] ) ? $spin_wheel_text['emailSubject'] : 'Spin Wheel Notification.';
		$email_content = ! empty( $spin_wheel_text['emailContent'] ) ? $spin_wheel_text['emailContent'] : 'Hello Dear, You have won a coupon via spin wheel. Check your my account to check it.';

		function is_blog () {
            return ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag()) && 'post' == get_post_type();
        }

		$show_on_homepage = ! empty( $spin_wheel_popup['showOnlyHomepage'] ) ? $spin_wheel_popup['showOnlyHomepage'] : 0;
        $show_on_blogpage = ! empty( $spin_wheel_popup['showOnlyBlogPage'] ) ? $spin_wheel_popup['showOnlyBlogPage'] : 0;
        $show_on_shoppage = ! empty( $spin_wheel_popup['showOnlyShopPage'] ) ? $spin_wheel_popup['showOnlyShopPage'] : 0;
		$selected_pages = ! empty( $spin_wheel_popup['selectedPages'] ) ? $spin_wheel_popup['selectedPages'] : [];

        if ( 
				$enable_spin_wheel && is_home() && $show_on_homepage == 1 && $spin_count < $spin_per_email || 
				$enable_spin_wheel && is_blog() && $show_on_blogpage == 1 && $spin_count < $spin_per_email || 
				$enable_spin_wheel && is_shop() && $show_on_shoppage == 1 && $spin_count < $spin_per_email ||
				$enable_spin_wheel && is_single( $selected_pages ) || is_page( $selected_pages ) && $spin_count < $spin_per_email
			) :

		wp_enqueue_script(
			hexcoupon_prefix( 'spin' ),
			hexcoupon_asset_url( $folder_prefix . "/public/js/spin" . $js_file_extension ),
			['jquery', 'wp-i18n'],
			$this->version,
			true
		);

		wp_localize_script( hexcoupon_prefix( 'spin' ), 'spinToWinData', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'spinPerEmail' => $spin_per_email,
			'delayBetweenSpin' => $delay_between_spins,
			'popupIntervalTime' => $popup_interval,
			'frontendMessageIfWin' => $frontend_message_if_win,
			'frontendMessageIfLoss' => $frontend_message_if_loss,
			'emailSubject' => $email_subject,
			'emailContent' => $email_content,
		] );

		endif;

		wp_enqueue_script(
			hexcoupon_prefix( 'bootstrap' ),
			hexcoupon_asset_url( $folder_prefix . "/public/js/bootstrap.bundle" . $js_file_extension ),
			['jquery'],
			$this->version,
			true
		);
		

		wp_enqueue_style(
			hexcoupon_prefix( 'public' ),
			hexcoupon_asset_url( $folder_prefix . "/public/css/public" . $css_file_extension ),
			[],
			$this->version,
			'all'
		);

		wp_enqueue_style(
			hexcoupon_prefix( 'bootstrap' ),
			hexcoupon_asset_url( $folder_prefix . "/public/css/bootstrap" . $css_file_extension ),
			[],
			$this->version,
			'all'
		);

		wp_enqueue_style(
			hexcoupon_prefix( 'spin' ),
			hexcoupon_asset_url( $folder_prefix . "/public/css/spin" . $css_file_extension ),
			[],
			$this->version,
			'all'
		);

		$total_remaining_store_credit = StoreCreditPaymentHelpers::getInstance()->show_total_remaining_amount();

		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;
			$total_price = $woocommerce->cart->total;

			wp_localize_script( hexcoupon_prefix( 'checkout-block' ), 'storeCreditData', [
				'total_remaining_store_credit' => $total_remaining_store_credit,
				'cart_total' => $total_price,
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'postUrl' => admin_url( 'admin-post.php' ),
				'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
				'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
			] );

			wp_localize_script( hexcoupon_prefix( 'checkout-frontend' ), 'hexCuponData', [
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'postUrl' => admin_url( 'admin-post.php' ),
				'restApiUrl' => get_site_url().'/wp-json/hexcoupon/v1/',
				'nonce' => wp_create_nonce('hexCuponData-react_nonce'),
			] );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method spin_wheel_inline_css
	 * @return void
	 * Set CSS value dynamically from php file to css file
	 */
	public function spin_wheel_inline_css()
	{
		$spin_wheel_popup = get_option( 'spinWheelPopup' );
		$spinToWin_wraper_bg = ! empty( $spin_wheel_popup['iconColor'] ) ? $spin_wheel_popup['iconColor'] : '#B71C1C';

		$spin_wheel_wheel = get_option( 'spinWheelWheel' );
		$textPart_h3 = ! empty( $spin_wheel_wheel['titleColor'] ) ? $spin_wheel_wheel['titleColor'] : '#D8CFCF';
		$textPart_p = ! empty( $spin_wheel_wheel['textColor'] ) ? $spin_wheel_wheel['textColor'] : '#D8CFCF';
		$description_color = ! empty( $spin_wheel_wheel['wheelDescriptionColor'] ) ? $spin_wheel_wheel['wheelDescriptionColor'] : '#ffffff';
		$button_color = ! empty( $spin_wheel_wheel['buttonColor'] ) ? $spin_wheel_wheel['buttonColor'] : '#ffffff';
		$button_bg = ! empty( $spin_wheel_wheel['buttonBGColor'] ) ? $spin_wheel_wheel['buttonBGColor'] : '#3636ad';
		
		$spin_wheel_content = get_option( 'spinWheelContent' );
		$content1_color = ! empty( $spin_wheel_content['content1']['color'] ) ? $spin_wheel_content['content1']['color'] : '#E53935';
		$content2_color = ! empty( $spin_wheel_content['content2']['color'] ) ? $spin_wheel_content['content2']['color'] : '#FFCDD2';
		$content3_color = ! empty( $spin_wheel_content['content3']['color'] ) ? $spin_wheel_content['content3']['color'] : '#B71C1C';
		$content4_color = ! empty( $spin_wheel_content['content4']['color'] ) ? $spin_wheel_content['content4']['color'] : '#E57373';

		// Create the CSS string
		$custom_css = "
			.spinToWin, .spinToWin .spinToWin-wraper {
				background-color: {$spinToWin_wraper_bg};
			}
			.text-part h3 {
				color: {$textPart_h3};
			}
			.text-part .wheelDescription {
				color: {$description_color};
			}
			.text-part p {
				color: {$textPart_p};
			}				
			.spinToWin .text-part button.try-your-luck {
				color: {$button_color};
			}
			.text-part .try-your-luck {
				background: {$button_bg} !important;
			}
			.spinToWin .slice:nth-child(1), 
			.spinToWin .slice:nth-child(3), 
			.spinToWin .slice:nth-child(5), 
			.spinToWin .slice:nth-child(7), 
			.spinToWin .slice:nth-child(9), 
			.spinToWin .slice:nth-child(11) {
				--bg: {$content1_color};
			}
			.spinToWin .slice:nth-child(2),
			.spinToWin .slice:nth-child(8),
			.spinToWin .slice:nth-child(12) {
				--bg: {$content2_color};
			}
			.spinToWin .slice:nth-child(4),
			.spinToWin .slice:nth-child(10) {
				--bg: {$content3_color};
			}
			.spinToWin  .slice:nth-child(6) {
				--bg: {$content4_color};
			}
		";
	
		// Add the inline style
		wp_add_inline_style( hexcoupon_prefix( 'spin' ), $custom_css);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method load_translation_for_admin_side
	 * @return void
	 *
	 */
	public function load_translation_for_admin_side()
	{
		wp_set_script_translations( hexcoupon_prefix( 'admin' ), 'hex-coupon-for-woocommerce', hexcoupon_url( "languages" ) );
		wp_set_script_translations( hexcoupon_prefix( 'all-coupon-page' ), 'hex-coupon-for-woocommerce', hexcoupon_url( "languages" ) );
		wp_set_script_translations( hexcoupon_prefix( 'main' ), 'hex-coupon-for-woocommerce', hexcoupon_url( "languages" ) );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method load_translation_for_public_side
	 * @return void
	 *
	 */
	public function load_translation_for_public_side()
	{
		wp_set_script_translations( hexcoupon_prefix( 'public' ), 'hex-coupon-for-woocommerce', hexcoupon_url( "languages" ) );
	}
}
