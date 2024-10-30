<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Configurations for the plugin
 *
 * @package hexcoupon
 */
return array(
	'plugin_prefix'		=> 'hexcoupon',
	'plugin_slug'		=> 'hexcoupon',
	'namespace_root'	=> 'HexCoupon',
	'plugin_version'	=> '1.2.4',
	'plugin_name'		=> 'HexCoupon',
	'dev_mode'			=> false,
	'root_dir'			=> dirname(__DIR__),
	'middlewares'		=> [
		'auth'	=> HexCoupon\App\Controllers\Middleware\Auth::class,
	],
);
