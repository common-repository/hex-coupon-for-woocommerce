<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="wrap hexcoupon-license-page">
	<div class="hexcoupon-license-container">
		<div class="license-heading">
			<h2><?php esc_html_e( 'HexCoupon License Activation', 'hex-coupon-for-woocommerce' ); ?></h2>
			<p class="license-heading-para"><?php esc_html_e( 'You need to activate the purchased key to use Pro Plugin features', 'hex-coupon-for-woocommerce' ); ?></p>
		</div>
		<?php
		$error = get_transient( 'hexcoupon_license_error' );
		if ( $error ) {
			echo '<div class="notice-error"><p>' . esc_html__( 'Invalid License: ', 'hex-coupon-for-woocommerce' ) . esc_html( $error ) . '</p></div>';
			delete_transient('hexcoupon_license_error'); // Clear the transient immediately after displaying the message
		}

		$success = get_transient('hexcoupon_license_success');
		if ( $success ) {
			echo '<div class="notice-success"><p>' . esc_html( $success ) . '</p></div>';
			delete_transient( 'hexcoupon_license_success' ); // Clear the transient immediately after displaying the message
		}
		?>
		<form method="post" action="">
			<input type="hidden" name="hexcoupon_license_action" value="save_license">
			<?php wp_nonce_field( 'hexcoupon_nonce', 'hexcoupon_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'License Key', 'hex-coupon-for-woocommerce' ); ?></th>
					<td>
						<input id="hexcoupon_license_key" name="hexcoupon_license_key" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'hexcoupon_license_key' ) ); ?>" />
						<?php $hexcoupon_license_status = get_option( 'hexcoupon_license_status' ); if ( $hexcoupon_license_status == 'valid' ) : ?>
						<span class="license-active"><?php esc_html_e( 'Active', 'hex-coupon-for-woocommerce' ); ?></span>
						<?php elseif ( $hexcoupon_license_status == 'expired' ) : ?>
						<span class="license-inactive"><?php esc_html_e( 'Expired', 'hex-coupon-for-woocommerce' ); ?></span>
						<?php elseif ( $hexcoupon_license_status == 'key_mismatch' ) : ?>
						<span class="license-inactive"><?php esc_html_e( 'Key Mismatched', 'hex-coupon-for-woocommerce' ); ?></span>
						<?php else : ?>
						<span class="license-inactive"><?php esc_html_e( 'Inactive', 'hex-coupon-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button( esc_html__( 'Save License', 'hexcoupon' ) ); ?>
		</form>
		<form method="post" action="">
			<?php wp_nonce_field( 'hexcoupon_nonce', 'hexcoupon_nonce' ); ?>
			<?php if ( get_option( 'hexcoupon_license_status' ) == 'valid' ) { ?>
				<input type="hidden" name="hexcoupon_license_action" value="deactivate_license">
				<input type="submit" class="button-secondary" name="hexcoupon_deactivate" value="<?php esc_html_e( 'Deactivate License', 'hex-coupon-for-woocommerce' ); ?>"/>
			<?php } else { ?>
				<input type="hidden" name="hexcoupon_license_action" value="activate_license">
				<input type="submit" class="button-secondary" name="hexcoupon_activate" value="<?php esc_html_e( 'Activate License', 'hex-coupon-for-woocommerce' ); ?>"/>
			<?php } ?>
		</form>
	</div>
</div>
