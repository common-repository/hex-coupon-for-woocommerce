<?php
namespace HexCoupon\App\Core\Helpers\LoyaltyProgram;
use HexCoupon\App\Core\Lib\SingleTon;

class LoyaltyPointsQueries
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method GetTopLoyaltyPointsEarner
	 * @return array
	 * Retrieving top 5 point earners from the database.
	 */
	public function GetTopLoyaltyPointsEarner()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_loyalty_program_points';
		$all_ids = [];

		$results = $wpdb->get_results(
			'SELECT user_id, SUM(points) as total_points FROM ' . $table_name . ' GROUP BY user_id ORDER BY total_points DESC LIMIT 5'
			,ARRAY_A );

		foreach ( $results as $row ) {
			$user_data = get_userdata( intval( $row['user_id'] ) );
			if ( empty( $user_data->first_name ) && empty( $user_data->last_name ) ) {
				$user_name = $user_data->user_nicename;
			} else {
				$user_name = $user_data->first_name . ' ' . $user_data->last_name;
			}

			$all_ids[] = [
				'user_name' => $user_name,
				'points' => $row['total_points']
			];
		}

		return $all_ids;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method all_combined_data
	 * @return array
	 * Retrieving top reasons with their points
	 */
	public function GetTopReasonsForPoints()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_loyalty_points_log';
		$all_reasons = [];

		if ( defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE ) {
			$query = "SELECT reason, SUM(points) as total_points FROM " . $table_name . " GROUP BY reason ORDER BY total_points DESC";
		} else {
			$query = "SELECT reason, SUM(points) as total_points FROM " . $table_name . " WHERE reason NOT IN ('3', '4', '5', '6') GROUP BY reason ORDER BY total_points DESC";
		}

		$results = $wpdb->get_results(
			$query, ARRAY_A
		);

		foreach ( $results as $row ) {
			switch ( $row['reason'] ) {
				case 0:
					$reason = 'SignUp';
					break;
				case 1:
					$reason = 'Referral';
					break;
				case 2:
					$reason = 'Purchase';
					break;
				case 3:
					$reason = 'Review';
					break;
				case 4:
					$reason = 'Comment';
					break;
				case 5:
					$reason = 'Birthday';
					break;
				case 6:
					$reason = 'Social Share';
					break;
				default:
					$reason = 'SignUp';
			}

			$all_reasons[] = [
				'reason' => $reason,
				'points' => $row['total_points']
			];
		}

		return $all_reasons;
	}
}
