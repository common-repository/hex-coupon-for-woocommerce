<?php
namespace HexCoupon\App\Core\Helpers\StoreCredit;
use HexCoupon\App\Core\Lib\SingleTon;

class StoreCreditQueries
{
	use SingleTon;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method GetTopStoreCreditSources
	 * @return array
	 * Retrieving all store credit sources
	 */
	public function GetTopStoreCreditSources()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_store_credit_logs';

		$all_sources = [];

		$results = $wpdb->get_results(
			"SELECT label, SUM(amount) as credit_amount FROM " . $table_name . " GROUP BY label ORDER BY credit_amount DESC",
			ARRAY_A
		);

		foreach ( $results as $row ) {
			switch ( $row['label'] ) {
				case 0:
					$reason = 'Refund Credits';
					break;
				case 1:
					$reason = 'Gift Credits';
					break;
				case 2:
					$reason = 'Loyalty Points';
					break;
				default:
					$reason = 'Refund Credits';
			}

			$all_sources[] = [
				'sources' => $reason,
				'credit' => round( $row['credit_amount'], 2 )
			];
		}

		return $all_sources;
	}
}
