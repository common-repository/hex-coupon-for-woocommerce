<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Lib\SingleTon;

class CouponShortcode
{
	use singleton;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return void
	 * @since 1.0.0
	 * Registers all hooks that are needed to create 'Coupon' shortcode functionality.
	 */
	public function register()
	{
		add_filter( 'manage_edit-shop_coupon_columns', [ $this, 'custom_coupon_list_table_columns' ] );
		add_action( 'manage_shop_coupon_posts_custom_column', [ $this, 'custom_coupon_list_table_column_values' ], 10, 2 );
		add_action( 'wp_loaded', [ $this, 'check_woocommerce_installed_after_wp_loaded' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method check_woocommerce_installed_after_wp_loaded
	 * @return void
	 * @since 1.0.0
	 * Run this shortcode after 'WooCommerce is loaded and active'
	 */
	public function check_woocommerce_installed_after_wp_loaded()
	{
		if ( class_exists( 'WooCommerce' ) ) {
			add_shortcode('hexcoupon', [ $this, 'display_coupon_info_shortcode' ] );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method display_coupon_info_shortcode
	 * @param $atts
	 * @return string
	 * @since 1.0.0
	 * Creates a shortcode for the coupon.
	 */
	public function display_coupon_info_shortcode( $atts )
	{
		// Shortcode attributes (if provided) or default values.
		$atts = shortcode_atts( [
			'code' => '', // Coupon code to display information for.
		], $atts );

		// Check if the 'code' attribute is provided.
		if ( empty( $atts['code'] ) ) {
			return esc_html__( 'Provide a coupon code.', 'hex-coupon-for-woocommerce' );
		}

		// Get the coupon object using the provided coupon code.
		$coupon = new \WC_Coupon( $atts['code'] );

		// Get coupon information.
		$coupon_code = $coupon->get_code();
		$coupon_description = $coupon->get_description();
		$coupon_discount_type = $coupon->get_discount_type();
		$coupon_expiry_date = $coupon->get_date_expires() ? $coupon->get_date_expires()->date('F j, Y') : esc_html__('No expiry date', 'hex-coupon-for-woocommerce');

		$discount_type = '';

		switch ( $coupon_discount_type ) {
			case 'percent' :
				$discount_type = '%';
				$coupon_amount = $coupon->get_amount();
				break;
			case 'fixed_cart' :
			case 'fixed_product' :
				$discount_type = get_woocommerce_currency_symbol();
				$coupon_amount = $coupon->get_amount();
				break;
			case 'buy_x_get_x_bogo':
				$discount_type = '';
				$coupon_amount = esc_html__( 'BOGO', 'hex-coupon-for-woocommerce' );
				break;
		}

		// Build the HTML output for the coupon information.
		$output = '<div class="discount-card">';
		$output .= '<div class="discount-info">';
		$output .= '<div class="discount-rate">' . sprintf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_amount ) ) . '<span>' . $discount_type . '</span> <br> ' . esc_html__( 'DISCOUNT', 'hex-coupon-for-woocommerce' ) . '</div>';
		$output .= '<div class="discount-details">';
		$output .= '<p>' . sprintf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $coupon_description ) ) . '</p>';
		$output .= '<div class="discount-code">';
		$output .= '<span class="icon">🎟️</span> <span class="code">' . esc_html( strtoupper( $coupon_code ) ) . '</span>';
		$output .= '</div>';
		$output .= '<div class="discount-expiry">';
		$output .= '<span class="icon">⏰</span> <span class="date">' . esc_html( $coupon_expiry_date ) . '</span>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method display_coupon_info_shortcode
	 * @param $coupon_code
	 * @return string
	 * @since 1.0.0
	 * Creates a shortcode for the coupon.
	 */
	public function generate_coupon_shortcode( $coupon_code )
	{
		return '[hexcoupon code="' . esc_attr( $coupon_code ) . '"]';
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method custom_coupon_list_table_columns
	 * @param $columns
	 * @return mixed
	 * @since 1.0.0
	 * Creates a shortcode for the coupon.
	 */
	public function custom_coupon_list_table_columns( $columns )
	{
		$columns['coupon_shortcode'] = esc_html__( 'Shortcode', 'hex-coupon-for-woocommerce' );
		return $columns;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method custom_coupon_list_table_column_values
	 * @param $column
	 * @param $coupon_id
	 * @return void
	 * @since 1.0.0
	 * Creates a shortcode for the coupon.
	 */
	public function custom_coupon_list_table_column_values( $column, $coupon_id )
	{
		if ( 'coupon_shortcode' === $column ) {
			$shortcode = $this->generate_coupon_shortcode( $coupon_id );
			?>
			<input type="text" readonly="readonly" class="shortcode_column" value="<?php echo esc_attr( $shortcode ); ?>" />
			<?php
		}
	}
}
