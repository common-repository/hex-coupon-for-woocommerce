<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\FormHelpers;
use HexCoupon\App\Core\Helpers\RenderHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponSingleGeographicRestrictions {

	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @return mixed
	 * Registers all hooks that are needed to create custom tab 'Geographic Restrictions' on 'Coupon Single' page.
	 */
	public function register()
	{
		add_action( 'woocommerce_coupon_data_tabs', [ $this, 'add_geographic_restriction_tab' ] );
		add_filter( 'woocommerce_coupon_data_panels', [ $this, 'add_geographic_restriction_tab_content' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_geographic_restriction_tab
	 * @return string
	 * Displays the new tab in the coupon single page called 'Geographic restrictions'.
	 */
	public function add_geographic_restriction_tab( $tabs )
	{
		$tabs['geographic_restriction_tab'] = array(
			'label'    => esc_html__( 'Geographic restrictions', 'hex-coupon-for-woocommerce' ),
			'target'   => 'geographic_restriction_tab',
			'class'    => array( 'geographic_restriction' ),
		);
		return $tabs;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_geographic_restriction_tab_content
	 * @return void
	 * Displays the content of custom tab 'Geographic restrictions'.
	 */
	public function add_geographic_restriction_tab_content()
	{
		// get 'apply_geographic_restriction' meta field data
		$geographic_restriction = get_post_meta( get_the_ID(), 'geographic_restriction', true );

		echo '<div id="geographic_restriction_tab" class="panel apply_geographic_restriction">';

		$restricted_shipping_zones = ! empty( $geographic_restriction['restricted_shipping_zones'] ) ? $geographic_restriction['restricted_shipping_zones'] : [];

		$output ='<div class="restricted_shipping_zones">';

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Restrict shipping zones', 'hex-coupon-for-woocommerce' ),
			'name' => 'geographic_restriction[restricted_shipping_zones]',
			'id' => 'restricted_shipping_zones',
			'value' => $restricted_shipping_zones,
			'type' => 'select',
			'options' => $this->get_all_shipping_zones(),
			'multiple' => true,
			'select2' => true,
			'class' => 'restricted_shipping_zones',
			'placeholder' => esc_html__( 'Search for shipping zone', 'hex-coupon-for-woocommerce' )
		] );

		echo '<span class="restricted_shipping_zones_tooltip">'.wc_help_tip( esc_html__( 'Select zones that you want to restrict the coupon.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div>';

		$restricted_countries = ! empty( $geographic_restriction['restricted_countries'] ) ? $geographic_restriction['restricted_countries'] : [];

		$output ='<div class="restricted_countries">';

		$output .= FormHelpers::Init( [
			'label' => esc_html__( 'Restrict countries', 'hex-coupon-for-woocommerce' ),
			'name' => 'geographic_restriction[restricted_countries]',
			'id' => 'restricted_countries',
			'value' => $restricted_countries,
			'type' => 'select',
			'options' => $this->get_all_countries_name(), //if the field is select, this param will be here
			'multiple' => true,
			'select2' => true,
			'class' => 'restricted_countries',
			'placeholder' => esc_html__( 'Search for countries', 'hex-coupon-for-woocommerce' )
		] );

		echo '<span class="restricted_countries_tooltip">'.wc_help_tip( esc_html__( 'Select countries that you want to restrict the coupon.', 'hex-coupon-for-woocommerce' ) ).'</span>';

		echo wp_kses( $output, RenderHelpers::getInstance()->Wp_Kses_Allowed_For_Forms() );

		echo '</div></div>';
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_shipping_zones
	 * @return array
	 * Get all the shipping zone name and code.
	 */
	public function get_all_shipping_zones()
	{
		$shipping_zones = []; // define an empty array

		$all_zones = \WC_Shipping_Zones::get_zones(); // get all shipping zones

		foreach ( $all_zones as $zone ) {
			$shipping_zones[ $zone['formatted_zone_location'] ] = $zone['zone_name'];
		}

		return $shipping_zones; // return formatted_zone_location
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method get_all_countries_name
	 * @return array
	 * Get all the countries names of WooCommerce.
	 */
	private function get_all_countries_name()
	{
		$countries_names = []; // define an empty array

		$all_countries = WC()->countries->get_countries(); // get all countries name

		foreach ( $all_countries as $country_code => $country_name ) {
			$countries_names[ $country_code ] = $country_name;
		}

		return $countries_names;
	}
}
