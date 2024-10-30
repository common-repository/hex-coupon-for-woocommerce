<?php

namespace HexCoupon\App\Core;

use HexCoupon\App\Core\Lib\SingleTon;

class AdminNoticeManager
{
	use SingleTon;

	private $woocommerce_plugin_url = 'woocommerce/woocommerce.php';

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return mixed
	 * @since 1.0.0
	 * Add all the necessary hooks that are needed.
	 */
	public function register()
	{
		// Hook for displaying a notice to activate and install the 'WooCommerce' plugin
		add_action( 'admin_notices', [ $this, 'show_active_and_installation_notice_for_woocommerce' ] );
		// Hook for displaying a notice for checking the 'WordPress' version.
		add_action('admin_notices', [ $this, 'wordpress_version_notice' ] );
		// Hook for displaying a notice for checking the 'WooCommerce' version.
		add_action('admin_notices', [ $this, 'woocommerce_version_notice' ] );
		// Hook for displaying a notice for checking the 'PHP' version.
		add_action('admin_notices', [ $this, 'php_version_notice' ] );
		// Show getting started notice after user activates the plugin
		add_action('admin_notices', [ $this, 'getting_started_notice' ] );
		// Hook to add AJAX action
		add_action('wp_ajax_dismiss_custom_admin_notice', [ $this, 'dismiss_custom_admin_notice' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_wp_version
	 * @return string
	 * @since 1.0.0
	 * Get WordPress version.
	 */
	private function get_wp_version()
	{
		$current_wp_version = get_bloginfo( 'version' );
		return $current_wp_version;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_current_php_version
	 * @return string
	 * @since 1.0.0
	 * Get current PHP version.
	 */
	private function get_current_php_version()
	{
		$php_version = phpversion();
		return $php_version;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_plugin_wp_version
	 * @return string
	 * @since 1.0.0
	 * Get WordPress version from plugin meta-data.
	 */
	private function get_plugin_wp_version()
	{
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin_data = get_plugin_data( plugin_dir_path(__FILE__) . '../../hex-coupon-for-woocommerce.php' );
		$plugin_version = $plugin_data['RequiresWP'];

		return $plugin_version;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_plugin_wc_version
	 * @return string
	 * @since 1.0.0
	 * Get WooCommerce version from plugin meta-data.
	 */
	private function get_plugin_wc_version()
	{
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin_data = get_plugin_data( plugin_dir_path(__FILE__) . '../../hex-coupon-for-woocommerce.php' );
		$plugin_version = ! empty( $plugin_data['WC requires at least'] ) ? $plugin_data['WC requires at least'] : '';

		return $plugin_version;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_plugin_php_version
	 * @return string
	 * @since 1.0.0
	 * Get PHP version from plugin meta data.
	 */
	private function get_plugin_php_version()
	{
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin_data = get_plugin_data( plugin_dir_path(__FILE__) . '../../hex-coupon-for-woocommerce.php' );
		$plugin_version = $plugin_data['RequiresPHP'];
		return $plugin_version;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method show_active_and_installation_notice_for_woocommerce
	 * @since 1.0.0
	 * Display the 'WooCommerce' installation notice after 'Hexcoupon' plugin activation.
	 */
	public function show_active_and_installation_notice_for_woocommerce()
	{
		$all_plugins_list = get_plugins();

		$install_notice_message = $this->get_woocommerce_install_notice_message();
		$active_notice_message = $this->get_woocommerce_active_notice_message();

		$allowed_html_tags = [
			'a' => [
				'href' => [],
			],
			'p' => [],
			'b' => []
		];

		if( ! array_key_exists( 'woocommerce/woocommerce.php', $all_plugins_list ) ) {
			$image_folder_link = plugin_dir_url( __FILE__ ).'../../assets/images/';
			?>
			<div class="notice notice-error is-dismissible hexcoupon-admin-notice">
				<div class="hexcoupon-notice-icon">
					<img src="<?php echo esc_url( $image_folder_link ); ?>hexcoupon-notice-icon.png" alt="Icon">
				</div>
				<div class="hexcoupon-notice-text">
					<?php echo wp_kses( $install_notice_message, $allowed_html_tags ); ?>
				</div>
			</div>


			<?php
		}
		elseif ( ! class_exists( 'WooCommerce' ) ) {
			$image_folder_link = plugin_dir_url( __FILE__ ).'../../assets/images/';
			?>
			<div class="notice notice-error is-dismissible hexcoupon-admin-notice">
				<div class="hexcoupon-notice-icon">
					<img src="<?php echo esc_url( $image_folder_link ); ?>hexcoupon-notice-icon.png" alt="Icon">
				</div>
				<div class="hexcoupon-notice-text">
					<?php echo wp_kses( $active_notice_message, $allowed_html_tags ); ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_woocommerce_active_notice_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for WooCommerce activation notice for the users.
	 * */
	private function get_woocommerce_active_notice_message()
	{
		$activate_url = wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $this->woocommerce_plugin_url ) ), 'activate-plugin_' . $this->woocommerce_plugin_url );
		return sprintf( __( '<p><b>WooCommerce</b> plugin is not active! Activate the WooCommerce plugin to use <b>HexCoupon</b> features.
		</p><a href="%s">Activate WooCommerce</a>','hex-coupon-for-woocommerce' ), esc_url( $activate_url ) );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_woocommerce_install_notice_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for WooCommerce installation notice for the users.
	 * */
	private function get_woocommerce_install_notice_message()
	{
		$install_url = wp_nonce_url( admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
		return sprintf( __( '<p><b>WooCommerce</b> plugin is not installed! Install the WooCommerce plugin to use <b>HexCoupon</b> features.
		</p><a href="%s">Install WooCommerce</a>','hex-coupon-for-woocommerce' ), esc_url( $install_url ) );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_woocommerce_install_notice_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for WooCommerce installation notice for the users.
	 * */
	private function show_getting_started_message()
	{
		$getting_started_url = 'https://hexcoupon.com/get-to-know-how-the-coupon-works/';

		return sprintf( __( '<p>Welcome to <b>HexCoupon</b> - Solution for smarter store marketing. Get advanced features for your <b>WooCommerce</b> store with our free plugin.
		</p><a href="%s">Want to learn how to set up?</a>','hex-coupon-for-woocommerce' ), esc_url( $getting_started_url ) );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_wordpress_version_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for WordPress version notice for the users.
	 * */
	private function get_wordpress_version_message()
	{
		$wp_version = $this->get_plugin_wp_version();

		return sprintf(
			esc_html__( 'This plugin requires at least WordPress version of %s', 'hex-coupon-for-woocommerce' ),
			esc_html( $wp_version )
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_woocommerce_version_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for WooCommerce version notice for the users.
	 * */
	private function get_woocommerce_version_message()
	{
		$plugin_version = $this->get_plugin_wc_version();

		return sprintf(
			esc_html__( 'This plugin requires at least WooCommerce version of %s', 'hex-coupon-for-woocommerce' ),
			esc_html( $plugin_version )
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method get_php_version_message
	 * @return string
	 * @since 1.0.0
	 * Renders a message for PHP version notice for the users.
	 * */
	private function get_php_version_message()
	{
		$php_version = $this->get_plugin_php_version();

		return sprintf(
			esc_html__( 'This plugin requires at least PHP version of %s', 'hex-coupon-for-woocommerce' ),
			esc_html( $php_version )
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method wordpress_version_notice
	 * @since 1.0.0
	 * Renders admin notice for WordPress version checking.
	 * */
	public function wordpress_version_notice()
	{
		$current_wp_version = $this->get_wp_version();
		$plugin_wp_version = $this->get_plugin_wp_version();

		if ( $current_wp_version < $plugin_wp_version ) {
			$wp_version_notice_message = $this->get_wordpress_version_message();
		}
		if ( ! empty( $wp_version_notice_message ) ) {
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php printf( esc_html__( 'Notice: %s', 'hex-coupon-for-woocommerce' ), esc_html( $wp_version_notice_message ) ); ?>
			</p>
		</div>
		<?php
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method woocommerce_version_notice
	 * @since 1.0.0
	 * Renders admin notice for WooCommerce version checking.
	 * */
	public function woocommerce_version_notice()
	{
		$plugin_wc_version = $this->get_plugin_wc_version();

		if ( defined('WC_VERSION') && class_exists( 'WooCommerce' ) && version_compare( WC_VERSION, $plugin_wc_version, '<' ) ) {
			$wc_version_notice_message = $this->get_woocommerce_version_message();
		}
		if ( ! empty( $wc_version_notice_message ) ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php printf( esc_html__( 'Notice: %s', 'hex-coupon-for-woocommerce' ), esc_html( $wc_version_notice_message ) ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method php_version_notice
	 * @since 1.0.0
	 * Renders admin notice for PHP version checking.
	 * */
	public function php_version_notice()
	{
		$plugin_php_version = $this->get_plugin_php_version();
		$current_php_version = $this->get_current_php_version();

		if ( $current_php_version < $plugin_php_version ) {
			$php_version_notice_message = $this->get_php_version_message();
		}
		if ( ! empty( $php_version_notice_message ) ) {
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php printf( esc_html__( 'Notice: %s', 'hex-coupon-for-woocommerce' ), esc_html( $php_version_notice_message ) ); ?>
			</p>
		</div>
		<?php
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method getting_started_notice
	 * @since 1.0.0
	 * Show getting started notice.
	 * */
	public function getting_started_notice()
	{
		$getting_started_notice = $this->show_getting_started_message();

		$allowed_html_tags = [
			'a' => [
				'href' => [],
			],
			'p' => [],
			'b' => [

			]
		];
		function is_coupon_create_page() {
				$protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
				$host = $_SERVER['HTTP_HOST'];
				$uri = $_SERVER['REQUEST_URI'];

				return $protocol . '://' . $host . $uri;
		}

		$coupon_sinlge_url = admin_url().'post-new.php?post_type=shop_coupon';

		$dismissed = get_user_meta(get_current_user_id(), 'custom_notice_dismissed', true);

		if ( is_coupon_create_page() === $coupon_sinlge_url && ! $dismissed ) {
			$image_folder_link = plugin_dir_url( __FILE__ ).'../../assets/images/';
			?>
			<div class="notice notice-info is-dismissible hexcoupon-admin-notice" id="custom-admin-notice">
				<div class="hexcoupon-notice-icon">
					<img src="<?php echo esc_url( $image_folder_link ); ?>hexcoupon-notice-icon.png" alt="Icon">
				</div>
				<div class="hexcoupon-notice-text">
					<?php echo wp_kses( $getting_started_notice, $allowed_html_tags ); ?>
				</div>
			</div>
			<?php
		}
	}

	// AJAX handler to dismiss the custom notice of getting started
	public function dismiss_custom_admin_notice() {
		update_user_meta( get_current_user_id(), 'custom_notice_dismissed', true );
	}


}
