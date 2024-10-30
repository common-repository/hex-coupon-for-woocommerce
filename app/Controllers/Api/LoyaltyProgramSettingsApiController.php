<?php
namespace HexCoupon\App\Controllers\Api;

use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Traits\NonceVerify;
use Kathamo\Framework\Lib\Controller;

class LoyaltyProgramSettingsApiController extends Controller
{

	use SingleTon, NonceVerify;

	/**
	 * Register hooks callback
	 *
	 * @return void
	 */
	public function register()
	{
		add_action( 'admin_post_loyalty_program_settings_save', [ $this, 'loyalty_program_settings_save' ] );
		add_action( 'admin_post_points_loyalty_settings_save', [ $this, 'points_loyalty_settings_save' ] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method loyalty_program_settings_save
	 * @return void
	 * Saving loyalty program enable/disable option in the option table
	 */
	public function loyalty_program_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$loyalty_program_enable_settings = [
				'enable' => rest_sanitize_boolean( $dataArray['enable'] ),
			];

			update_option( 'loyalty_program_enable_settings', $loyalty_program_enable_settings ); // saving the value in the option table

			wp_send_json( $_POST );
		} else {
			wp_send_json( [
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method points_loyalty_settings_save
	 * @return void
	 * Saving all the settings of points loyalty settings page
	 */
	public function points_loyalty_settings_save()
	{
		if ( $this->verify_nonce('POST') ) {
			$formData = json_encode( $_POST );

			$dataArray = json_decode( $formData, true );

			$points_on_purchase = [
				'enable' => isset($dataArray['settings']['pointsOnPurchase']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsOnPurchase']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsOnPurchase']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsOnPurchase']['pointAmount']) : '',
				'spendingAmount' => isset($dataArray['settings']['pointsOnPurchase']['spendingAmount']) ? sanitize_text_field($dataArray['settings']['pointsOnPurchase']['spendingAmount']) : '',
			];
			update_option( 'pointsOnPurchase', $points_on_purchase );

			$points_for_signup = [
				'enable' => isset($dataArray['settings']['pointsForSignup']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForSignup']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForSignup']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForSignup']['pointAmount']) : '',
			];
			update_option( 'pointsForSignup', $points_for_signup );

			$points_for_referral = [
				'enable' => isset($dataArray['settings']['pointsForReferral']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForReferral']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForReferral']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForReferral']['pointAmount']) : '',
			];
			update_option( 'pointsForReferral', $points_for_referral );

			$points_for_review = [
				'enable' => isset($dataArray['settings']['pointsForReview']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForReview']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForReview']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForReview']['pointAmount']) : '',
			];
			update_option( 'pointsForReview', $points_for_review );

			$points_for_comment = [
				'enable' => isset($dataArray['settings']['pointsForComment']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForComment']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForComment']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForComment']['pointAmount']) : '',
			];
			update_option( 'pointsForComment', $points_for_comment );

			$points_for_birthday = [
				'enable' => isset($dataArray['settings']['pointsForBirthday']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForBirthday']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForBirthday']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForBirthday']['pointAmount']) : '',
			];
			update_option( 'pointsForBirthday', $points_for_birthday );

			$points_for_social_share = [
				'enable' => isset($dataArray['settings']['pointsForSocialShare']['enable']) ? rest_sanitize_boolean($dataArray['settings']['pointsForSocialShare']['enable']) : '',
				'pointAmount' => isset($dataArray['settings']['pointsForSocialShare']['pointAmount']) ? sanitize_text_field($dataArray['settings']['pointsForSocialShare']['pointAmount']) : '',
			];
			update_option( 'pointsForSocialShare', $points_for_social_share );

			$conversion_rate = [
				'credit' => isset($dataArray['settings']['conversionRate']['credit']) ? sanitize_text_field($dataArray['settings']['conversionRate']['credit']) : '',
				'points' => isset($dataArray['settings']['conversionRate']['points']) ? sanitize_text_field($dataArray['settings']['conversionRate']['points']) : '',
			];
			update_option( 'conversionRate', $conversion_rate );

			$all_labels = [
				'logPageTitle' => isset( $dataArray['settings']['allLoyaltyLabels']['logPageTitle'] ) ? sanitize_text_field( $dataArray['settings']['allLoyaltyLabels']['logPageTitle'] ) : '',
				'referralLinkLabel' => isset( $dataArray['settings']['allLoyaltyLabels']['referralLinkLabel'] ) ? sanitize_text_field( $dataArray['settings']['allLoyaltyLabels']['referralLinkLabel']) : '',
				'pointsText' => isset( $dataArray['settings']['allLoyaltyLabels']['pointsText'] ) ? sanitize_text_field( $dataArray['settings']['allLoyaltyLabels']['pointsText']) : '',
			];
			update_option('allLoyaltyLabels', $all_labels );

			wp_send_json( $_POST );
		} else {
			wp_send_json([
				'error' => 'Nonce verification failed',
			], 403); // 403 Forbidden status code
		}
	}
}
