<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Lib\Singleton;

class AddCustomLinksInAllPluginsPage
{
	use Singleton;

	/**
	 * @return void
	 * @author WpHex
	 * @method register
	 * @package hexcoupon
	 * @since 1.0.0
	 * Registers all hooks that are needed to create 'Coupon' category.
	 */
	public function register()
	{
		add_filter( 'plugin_row_meta', [ $this, 'hexcoupon_plugin_row_meta' ], 10, 2 );
	}

	/**
	 * Add custom link besides 'View Details' link in the all plugin page
	 *
	 * @return array
	 */
	public function hexcoupon_plugin_row_meta( $links, $file )
	{
		$support_link = 'https://wordpress.org/support/plugin/hex-coupon-for-woocommerce/';
		$documentation_link = 'https://hexcoupon.com/docs/';
		$upgrade = 'https://hexcoupon.com/pricing/';


		$is_pro_active = defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE ? true : false;

		if ( 'hex-coupon-for-woocommerce/hex-coupon-for-woocommerce.php' == $file ) {
			if ( ! $is_pro_active ) {
				$row_meta = [
					'hexcoupon-support' => '<a rel="noopener" href="' . esc_url( $support_link ) . '" style="color: #23c507;font-weight: bold;" aria-label="' . esc_attr( esc_html__('Support', 'hex-coupon-for-woocommerce' ) ) . '" target="_blank">' . esc_html__( 'Support', 'hex-coupon-for-woocommerce' ) . '</a>',
					'hexcoupon-documentation' => '<a rel="noopener" href="' . esc_url( $documentation_link ) . '" style="color: #23c507;font-weight: bold;" aria-label="' . esc_attr( esc_html__('Documentation', 'hex-coupon-for-woocommerce' ) ) . '" target="_blank">' . esc_html__( 'Documentation', 'hex-coupon-for-woocommerce' ) . '</a>',
					'hexcoupon-upgrade' => '<a rel="noopener" href="' . esc_url( $upgrade ) . '" style="color: #984BF6;font-weight: bold;" aria-label="' . esc_attr( esc_html__('Upgrade to Pro', 'hex-coupon-for-woocommerce' ) ) . '" target="_blank">' . esc_html__( 'Upgrade to Pro', 'hex-coupon-for-woocommerce' ) . '</a>',
				];
			} if( $is_pro_active ) {
				$row_meta = [
					'hexcoupon-support' => '<a rel="noopener" href="' . esc_url( $support_link ) . '" style="color: #23c507;font-weight: bold;" aria-label="' . esc_attr( esc_html__('Support', 'hex-coupon-for-woocommerce' ) ) . '" target="_blank">' . esc_html__( 'Support', 'hex-coupon-for-woocommerce' ) . '</a>',
					'hexcoupon-documentation' => '<a rel="noopener" href="' . esc_url( $documentation_link ) . '" style="color: #23c507;font-weight: bold;" aria-label="' . esc_attr( esc_html__('Documentation', 'hex-coupon-for-woocommerce' ) ) . '" target="_blank">' . esc_html__( 'Documentation', 'hex-coupon-for-woocommerce' ) . '</a>',
				];
			}

			return array_merge($links, $row_meta);
		}
		return (array)$links;
	}
}
