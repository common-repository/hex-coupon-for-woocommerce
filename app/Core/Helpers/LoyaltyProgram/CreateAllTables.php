<?php
namespace HexCoupon\App\Core\Helpers\LoyaltyProgram;
use HexCoupon\App\Core\Lib\SingleTon;

class CreateAllTables
{
	use SingleTon;
	private $table_name;

	private $pointsForSignup;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_points_transactions_table
	 * @return void
	 * Creating table called 'hex_points_transactions' table
	 */
	public function create_points_transactions_table()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'hex_loyalty_program_points';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        points int(11) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate ENGINE=InnoDB;";

		$wpdb->query( $sql );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method create_points_log_table
	 * @return void
	 * Creating table called 'hex_loyalty_points_log' table
	 */
	public function create_points_log_table()
	{
		global $wpdb;
		$loyalty_log_table = $wpdb->prefix . 'hex_loyalty_points_log';

		// Check if the table already exists
		if ( $wpdb->get_var("SHOW TABLES LIKE '{$loyalty_log_table}'") !== $loyalty_log_table ) {

			$sql = "CREATE TABLE {$loyalty_log_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            points DOUBLE NOT NULL,
            reason BIGINT UNSIGNED NOT NULL COMMENT '0=SignUp Points, 1=Referral Points, 2=Purchase Points, 3=Review, 4=Comment, 5=Birthday, 6=SocialShare',
            referee_id bigint(20) UNSIGNED NULL,
            converted_credit DOUBLE NOT NULL,
            conversion_rate DOUBLE NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB;";

			$wpdb->query( $sql );
		}
	}

}
