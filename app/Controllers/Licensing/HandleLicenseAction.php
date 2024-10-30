<?php
namespace HexCoupon\App\Controllers\Licensing;

use HexCoupon\App\Core\Lib\SingleTon;

class HandleLicenseAction
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method handle_license_action
	 * @return void
	 * @since 1.0.0
	 * Adding the mechanism for license saving, activating and deactivating from the 'License' menu
	 */
	public function handle_license_action()
	{
		if ( isset( $_POST['hexcoupon_license_action'] ) && check_admin_referer( 'hexcoupon_nonce', 'hexcoupon_nonce' ) ) {
			$action = sanitize_text_field( $_POST['hexcoupon_license_action'] );

			switch ( $action ) {
				case 'save_license':
					if ( isset( $_POST['hexcoupon_license_key'] ) ) {
						$new_license = sanitize_text_field( $_POST['hexcoupon_license_key'] );
						$old_license = get_option( 'hexcoupon_license_key' );

						update_option( 'hexcoupon_license_key', $new_license );

						if ( $old_license && $old_license != $new_license ) {
							delete_option( 'hexcoupon_license_status' );
						}
					} else {
						error_log( 'License key not set in the POST request.' );
					}
					break;

				case 'activate_license':
					ActivateLicense::getInstance()->activate_license();
					break;

				case 'deactivate_license':
					DeactivateLicense::getInstance()->deactivate_license();
					break;
			}
		}
	}
}
