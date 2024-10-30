<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Lib\SingleTon;

class CouponCategory
{
	use singleton;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return string
	 * @since 1.0.0
	 * Registers all hooks that are needed to create 'Coupon' category.
	 */
	public function register()
	{
		add_action( 'init', [ $this, 'add_taxonomy_to_coupon' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method add_taxonomy_to_coupon
	 * @return string
	 * @since 1.0.0
	 * Registers category named 'Coupon Taxonomy'.
	 */
	public function add_taxonomy_to_coupon()
	{
		register_taxonomy(
			'shop_coupon_taxonomy',
			'shop_coupon',
			[
				'label' => esc_html__( 'Coupon Taxonomy' ),
				'rewrite' => [ 'slug' => 'coupon_taxonomy' ],
				'hierarchical' => true,
			],
		);
	}
}
