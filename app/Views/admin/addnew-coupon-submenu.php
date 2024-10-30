<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// redirect this page to the WooCommerce 'Add New Coupon' page
wp_redirect( admin_url( esc_url( $redirect_link ) ) );
exit;
