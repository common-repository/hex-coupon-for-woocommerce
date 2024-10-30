<?php
namespace HexCoupon\App\Core\WooCommerce\LoyaltyProgram;

use HexCoupon\App\Core\Lib\SingleTon;

class LoyaltyProgram
{
	use SingleTon;

	private $user;

	private $points_converstion_rate;
	private $loyalty_program_settings;
	private $points_on_social_share;

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method register
	 * @return void
	 * @since 1.0.0
	 * Add all hooks that are needed.
	 */
	public function register()
	{
		$this->points_converstion_rate = get_option( 'conversionRate' );
		$this->loyalty_program_settings = get_option( 'loyalty_program_enable_settings' );
		$loyalty_program_enable = $this->loyalty_program_settings['enable'] ?? 0;

		$this->points_on_social_share = get_option( 'pointsForSocialShare' );

		// Making it on/off based on Loyalty Program on/off settings data
		if ( $loyalty_program_enable ) {
			// Action hook for adding 'Loyalty Points' menu page in the 'My Account' Page Menu
			add_filter ( 'woocommerce_account_menu_items', [ $this, 'loyalty_points_in_my_account_page' ], 40 );
			// Action hook for registering permalink endpoint
			add_action( 'init', [ $this, 'register_endpoints' ] );

			// Action hook for displaying 'Loyalty Points' page content
			add_action( 'woocommerce_account_loyalty-points_endpoint', [ $this, 'loyalty_points_page_endpoint_content' ] );
			add_action( 'woocommerce_account_loyalty-points-logs_endpoint', [ $this, 'loyalty_points_logs_page_endpoint_content' ] );
		}
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_in_my_account_page
	 * @return mixed
	 * @since 1.0.0
	 * Show 'Loyalty Points' tab in the 'My Account' page
	 */
	public function loyalty_points_in_my_account_page( $all_menu_links )
	{
		$all_menu_links = array_slice( $all_menu_links, 0, 7, true )
			+ [ 'loyalty-points' => esc_html__( 'Loyalty Points', 'hex-coupon-for-woocommerce' ) ]
			+ array_slice( $all_menu_links, 6, NULL, true );

		return $all_menu_links;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_menu_page_endpoint
	 * @return void
	 * @since 1.0.0
	 * Register 'loyalty-points' endpoint
	 */
	public function register_endpoints()
	{
		add_rewrite_endpoint( 'loyalty-points', EP_PAGES );
		add_rewrite_endpoint( 'loyalty-points-logs', EP_PAGES );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method store_credit_page_endpoint_content
	 * @return void
	 * @since 1.0.0
	 * Show content in the 'loyalty points' endpoint
	 */
	public function loyalty_points_page_endpoint_content()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_loyalty_program_points';

		$user_id = get_current_user_id();


		$current_points = $wpdb->get_var( $wpdb->prepare(
			"SELECT points FROM $table_name WHERE user_id = %d",
			$user_id
		) );

		// If no entry exists, default the current points to 0
		$current_points = $current_points !== null ? intval( $current_points ) : 0;

		// Creating referral link for each user
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$site_url = get_site_url();
			$referral_link = $site_url . "?ref=" . $user_id;
		}
		$logs_page_url = get_site_url() . '/my-account/loyalty-points-logs';

		$conversion_rate = $this->points_converstion_rate['points'] ?? 0;
		$allowed_html = [
			'b' => [
				'class'
			]
		];

		$all_loyalty_labels = get_option( 'allLoyaltyLabels' );
		$points_text = $all_loyalty_labels['pointsText'] ?? 'Points earned so far';
		$referral_link_label = $all_loyalty_labels['referralLinkLabel'] ?? 'Referral Link';
		?>
		<div class="referral-top-bar">
			<div class="current-points">
				<?php printf( esc_html__( '%s: %s', 'hex-coupon-for-woocommerce' ), esc_html( $points_text ), esc_html( $current_points ) ); ?>
			</div>
			<div class="points-log-link">
				<a href="<?php echo esc_url( $logs_page_url ); ?>">
					<?php esc_html_e( 'View Log', 'hex-coupon-for-woocommerce' ); ?>
				</a>
			</div>
		</div>

		<div class="referral-container">
			<h2><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $referral_link_label ) )?></h2>
			<div class="referral-box">
				<input type="text" id="referral-link" value="<?php echo esc_url( $referral_link ); ?>" readonly>
				<button class="copy-referral-link"><?php esc_html_e( 'Copy', 'hex-coupon-for-woocommerce' ); ?></button>
			</div>
			<div class="conversion-rate">
				<?php echo wp_kses( "Your points are converted to store credit. The conversion rate is <b>'{$conversion_rate}'</b> points per store credit.", $allowed_html ); ?>
			</div>
		</div>

		<?php
		$is_pro_active = defined( 'IS_PRO_ACTIVE' ) && IS_PRO_ACTIVE ? true : false;;

		$enable_points_on_social_share = ! empty( $this->points_on_social_share['enable'] ) ? $this->points_on_social_share['enable'] : 0;
		if ( $is_pro_active && $enable_points_on_social_share ) :
		?>
		<div class="referral-container">
			<h2><?php esc_html_e( 'Social Share:', 'hex-coupon-for-woocommerce' ); ?> </h2>
			<?php
			$facebook_svg = plugins_url( '/hex-coupon-for-woocommerce/assets/images/Facebook.svg' );
			$x_svg = plugins_url( '/hex-coupon-for-woocommerce/assets/images/X.svg' );
			$linkedin_svg = plugins_url( '/hex-coupon-for-woocommerce/assets/images/Linkedin.svg' );

			echo '<div class="social-share-buttons">';
			// Facebook Share Button
			echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $referral_link ) . '" target="_blank">';
			echo '<img width="40" height="40" src="' . esc_url( $facebook_svg ) . '" alt="Share on Facebook" />';
			echo '<i class="fa-brands fa-facebook-f"></i>';
			echo '</a>';
			// Twitter Share Button
			echo '<a href="https://twitter.com/intent/tweet?url=' . urlencode( $referral_link ) . '" target="_blank">';
			echo '<img width="40" height="40" src="' . esc_url( $x_svg ) . '" alt="Share on Twitter" />';
			echo '<i class="lab la-twitter"></i>';
			echo '</a>';
			// LinkedIn Share Button
			echo '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $referral_link ) . '" target="_blank">';
			echo '<img width="40" height="40" src="' . esc_url( $linkedin_svg ) . '" alt="Share on LinkedIn" />';
			echo '<i class="lab la-linkedin"></i>';
			echo '</a>';
			echo '</div>';
			?>
		</div>
		<?php endif; ?>

		<div class="referral-container two">
			<h3 class="notice notice-info">
				<?php esc_html_e( 'You will only get points for referral when the referral user makes a successful purchase', 'hex-coupon-for-woocommerce' ); ?>
			</h3>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method loyalty_points_logs_page_endpoint_content
	 * @return void
	 * @since 1.0.0
	 * Show content in the 'loyalty points logs' endpoint
	 */
	public function loyalty_points_logs_page_endpoint_content()
	{
		$user_id = get_current_user_id();

		global $wpdb;
		$table_name = $wpdb->prefix . 'hex_loyalty_points_log';

		$items_per_page = 10;
		$page = isset( $_GET['log'] ) ? abs( (int) $_GET['log'] ) : 1;
		$offset = ( $page * $items_per_page ) - $items_per_page;

		$query = "SELECT * FROM {$table_name} WHERE user_id = {$user_id}";

		$total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
		$total = $wpdb->get_var( $total_query );

		$results = $wpdb->get_results( $query.' ORDER BY id DESC LIMIT '. $offset.', '. $items_per_page, ARRAY_A );

		$all_loyalty_labels = get_option( 'allLoyaltyLabels' );
		$log_page_title = $all_loyalty_labels['logPageTitle'] ?? 'Loyalty Points Log';
	?>
	<div class="loyalty-points-log">
		<h2>
			<?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $log_page_title ) ); ?>
		</h2>
		<table class="loyalty-points-log-table">
			<thead>
			<tr>
				<th><?php echo esc_html__( 'Points', 'hex-coupon-for-woocommerce' ); ?></th>
				<th><?php echo esc_html__( 'Reason', 'hex-coupon-for-woocommerce' ); ?></th>
				<th><?php echo esc_html__( 'Referee ID', 'hex-coupon-for-woocommerce' ); ?></th>
				<th><?php echo esc_html__( 'Converted Credit', 'hex-coupon-for-woocommerce' ); ?></th>
				<th><?php echo esc_html__( 'Conversion Rate', 'hex-coupon-for-woocommerce' ); ?></th>
				<th><?php echo esc_html__( 'Date', 'hex-coupon-for-woocommerce' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( ! $results ) : ?>
				<tr>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
					<td><?php esc_html_e( 'No data yet', 'hex-coupon-for-woocommerce' ); ?></td>
				</tr>
			<?php endif; ?>
			<?php
			if ( $results ) : foreach( $results as $item ) :
				switch ( $item['reason'] ) {
					case 0 :
						$reason = 'Signup';
						break;
					case 1 :
						$reason = 'Referral';
						break;
					case 2 :
						$reason = 'Purchase';
						break;
					case 3 :
						$reason = 'Review';
						break;
					case 4 :
						$reason = 'Comment';
						break;
					case 5 :
						$reason = 'Birthday';
						break;
					case 6 :
						$reason = 'Social Share';
						break;
					default :
						$reason = 'Signup';
				}

			// Assigning 'Null' if there is null value
			$referee_id = $item['referee_id'] ?? 'NA';
			?>
			<tr>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $item['points'] ) ); ?></td>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $reason ) ); ?></td>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $referee_id ) ); ?></td>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $item['converted_credit'] ) ); ?></td>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $item['conversion_rate'] ) ); ?></td>
				<td><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $item['created_at'] ) ); ?></td>
			</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
		<p><b><?php esc_html_e( 'All points you get are converted to store credit. Use store credit to make purchase on our store.', 'hex-coupon-for-woocommerce' );?></b></p>
	</div>
	<?php
		echo paginate_links( [
			'base' => add_query_arg( 'log', '%#%' ),
			'format' => '',
			'prev_text' => __( '« Prev' ),
			'next_text' => __( 'Next »' ),
			'total' => ceil($total / $items_per_page),
			'current' => $page
		] );
	}
}
