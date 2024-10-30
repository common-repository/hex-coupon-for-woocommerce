<?php

namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;
use function Automattic\Jetpack\Extensions\Business_Hours\render;

class QrCodeGeneratorHelpers
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method qr_code_generator_for_url
	 * @return void
	 * Creating a QR code for coupons
	 */
	public function qr_code_generator_for_url( $coupon_id )
	{
		// Getting the coupon code title
		$coupon_code = get_the_title( $coupon_id );

		// URL for qr code
		$sharable_url = sanitize_url( get_site_url() . '/' . '?coupon_code=' . $coupon_code );

		// Creating the QRCode object with the given URL
		$qr_generator = new \QRCode( $sharable_url );

		// Generating the QR code as a PNG image
		$image = $qr_generator->render_image();

		// Define the directory path to save the image within your plugin directory
		$directory = plugin_dir_path( __FILE__ ) . '../../../assets/images/';

		// Generate a unique filename for the image
		$filePath = $directory . 'qr_code_' . $coupon_id . '.png';

		// Save the PNG image to a file
		imagepng( $image, $filePath );

		// Free up memory
		imagedestroy( $image );
	}

}
