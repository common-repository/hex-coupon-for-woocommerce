<?php
namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;

class GeneralFunctionsHelpers {

	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_all_products
	 * @return array
	 * Retrieve all available WoCommerce products.
	 */
	public function show_all_products()
	{
		$all_product_titles = []; // initialize an empty array

		$products = get_posts( [
			'post_type' => 'product',
			'numberposts' => -1,
		] );

		foreach ( $products as $product ) {
			$all_product_titles[$product->ID] = get_the_title( $product );
		}

		return $all_product_titles; // return all products id
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_all_categories
	 * @return array
	 * Retrieve all available WoCommerce product categories.
	 */
	public function show_all_categories()
	{
		$all_categories = []; // initialize an empty array

		$product_categories = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		] );

		if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
			foreach ( $product_categories as $category ) {
				$cat_id = $category->term_id;
				$all_categories[ $cat_id ] = $category->name;
			}
		}

		return $all_categories; // return all categories id
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method show_all_pages
	 * @return array
	 * Retrieve all available Pages of WP.
	 */
	public function show_all_pages()
	{
		// Get the IDs of WooCommerce pages to exclude
		$woo_pages_ids = [
			wc_get_page_id('shop'),      // Shop page ID
			wc_get_page_id('cart'),      // Cart page ID
			wc_get_page_id('checkout'),  // Checkout page ID
			wc_get_page_id('myaccount'), // My Account page ID
		];
	
		// Query to get all published pages and posts excluding WooCommerce pages
		$args = [
			'post_type'      => ['page', 'post'],  // Include both pages and posts
			'posts_per_page' => -1,                // Get all posts/pages
			'post_status'    => 'publish',         // Only published posts/pages
			'post__not_in'   => $woo_pages_ids,    // Exclude WooCommerce pages
		];
	
		$pages_and_posts = get_posts($args);
	
		$pages_and_posts_array = [];
		foreach ($pages_and_posts as $item) {
			$pages_and_posts_array[$item->ID] = $item->post_title;
		}
	
		return $pages_and_posts_array; 
	}

}