<?php
namespace HexCoupon\App\Controllers\Api;

use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Traits\NonceVerify;
use Kathamo\Framework\Lib\Controller;

class StoreCreditSettingsApiController extends Controller
{

	use SingleTon, NonceVerify;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_action( 'admin_post_store_credit_settings_save', [ $this, 'store_credit_settings_save' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method store_credit_settings_save
	 * @return void
	 * Saving store credit enable/disable option in the option table
	 */
	public function store_credit_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$store_credit_enable_settings = [
				'enable' => rest_sanitize_boolean( $dataArray['enable'] ),
			];

			// Apply filter hook to modify the data array for pro version
			$store_credit_enable_settings = apply_filters( 'store_credit_settings_data', $store_credit_enable_settings, $dataArray );

			update_option( 'store_credit_enable_data', $store_credit_enable_settings ); // saving the value in the option table

			wp_send_json( $_POST );
		} else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

}
