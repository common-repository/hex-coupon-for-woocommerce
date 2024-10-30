<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'HEXCOUPON_DIR_PATH', plugin_dir_path( HEXCOUPON_FILE ) );
define( 'HEXCOUPON_PLUGIN_URL', plugins_url( '/', HEXCOUPON_FILE ) );


if ( ! function_exists( 'hexcoupon_get_config' ) ) {
	/**
	 * get configs.
	 *
	 * @param string $name - plugin name.
	 *
	 * @return string
	 */
	function hexcoupon_get_config($name = '')
	{
		$configs = require HEXCOUPON_DIR_PATH . '/configs/config.php';
		if ( $name ) {
			return isset($configs[$name]) ? $configs[$name] : false;
		}
		return $configs;
	}
}

if ( ! function_exists( 'hexcoupon_prefix' ) ) {
	/**
	 * Add prefix for the given string.
	 *
	 * @param string $name - plugin name.
	 *
	 * @return string
	 */
	function hexcoupon_prefix($name)
	{
		return hexcoupon_get_config('plugin_slug') . "-" . $name;
	}
}

if ( ! function_exists( 'hexcoupon_url' ) ) {
	/**
	 * Add prefix for the given string.
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	function hexcoupon_url($path)
	{
		return HEXCOUPON_PLUGIN_URL . $path;
	}
}

if ( ! function_exists( 'hexcoupon_asset_url' ) ) {
	/**
	 * Add prefix for the given string.
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	function hexcoupon_asset_url($path)
	{
		return hexcoupon_url( "assets" . $path );
	}
}

if ( ! function_exists( 'hexcoupon_wp_ajax' ) ) {
	/**
	 * Wrapper function for wp_ajax_* and wp_ajax_nopriv_*
	 *
	 * @param  string $action - action name
	 * @param string $callback - callback method name
	 * @param bool   $public - is this a public ajax action
	 *
	 * @return mixed
	 */
	function hexcoupon_wp_ajax($action, $callback, $public = false)
	{
		add_action( 'wp_ajax_' . $action, $callback );
		if ( $public ) {
			add_action( 'wp_ajax_nopriv_' . $action, $callback );
		}
	}
}

if ( ! function_exists( 'hexcoupon_render_template' ) ) {
	/**
	 * Require a Template file.
	 *
	 * @param  string $file_path
	 * @param array  $data
	 *
	 * @return mixed
	 *
	 * @throws \Exception - if file not found throw exception
	 * @throws \Exception - if data is not array throw exception
	 */
	function hexcoupon_render_template($file_path, $data = array())
	{
		$file = HEXCOUPON_DIR_PATH . "app" . $file_path;
		if ( ! file_exists( $file ) ) {
			throw new \Exception( "File not found" );
		}
		if ( ! is_array( $data ) ) {
			throw new \Exception( "Expected array as data" );
		}
		extract( $data, EXTR_PREFIX_SAME, hexcoupon_get_config('plugin_prefix') );	// @phpcs:ignore

		return require_once $file;
	}
}

if ( ! function_exists( 'hexcoupon_render_view_template' ) ) {
	/**
	 * Require a View template file.
	 *
	 * @param  string $file_path
	 * @param array  $data
	 *
	 * @return mixed
	 */
	function hexcoupon_render_view_template($file_path, $data = array())
	{
		return hexcoupon_render_template( "/Views" . $file_path, $data );
	}
}
