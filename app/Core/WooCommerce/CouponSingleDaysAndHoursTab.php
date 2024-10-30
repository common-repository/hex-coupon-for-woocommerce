<?php
namespace HexCoupon\App\Core\WooCommerce;

use HexCoupon\App\Core\Helpers\FormHelpers;
use HexCoupon\App\Core\Helpers\RenderHelpers;
use HexCoupon\App\Core\Lib\SingleTon;

class CouponSingleDaysAndHoursTab
{
	use singleton;

	/**
	 * @return void
	 * Registers all hooks that are needed to create custom tab on 'Coupon Single' page.
	 * @author WpHex
	 * @since 1.0.0
	 * @method register
	 * @package hexcoupon
	 */
	public function register()
	{
		add_action( 'woocommerce_coupon_data_tabs', [ $this, 'add_custom_coupon_tab' ] );
		add_filter( 'woocommerce_coupon_data_panels', [ $this, 'add_custom_coupon_tab_content'] );
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @method add_custom_coupon_tab
	 * @param array $tabs
	 * @return array
	 * @since 1.0.0
	 * Displays the new tab in the coupon single page called 'Specific Days and Hours'.
	 */
	public function add_custom_coupon_tab( $tabs )
	{
		$tabs['days_and_hours_tab'] = [
			'label'    => esc_html__( 'Specific Days and Hours', 'hex-coupon-for-woocommerce' ),
			'target'   => 'days_and_hours_tab',
			'class'    => array( 'show_if_coupon_usage_limits' ),
		];

		return $tabs;
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_custom_coupon_tab_content
	 * @return void
	 * Displays the content of custom tab 'Specific Days and Hours'.
	 */
	public function add_custom_coupon_tab_content()
	{
		echo '<div id="days_and_hours_tab" class="panel woocommerce_options_panel days_and_hours_tab">';

		echo '<div id="upgrade_notice">';
		echo '<p>' . esc_html__( 'To access this feature ', 'hex-coupon-for-woocommerce' ) . '<a href="' . esc_url( 'https://hexcoupon.com/pricing' ) . '">' . esc_html__( 'Upgrade to Pro', 'hex-coupon-for-woocommerce' ) . '</a></p>';
		echo '</div>';

		// Add apply days and hours of week checkbox
		$this->apply_days_hours_of_week_checkbox();

		// Add apply on saturday fields
		$this->add_coupon_apply_on_saturday_fields();

		// Add apply on sunday fields
		$this->add_coupon_apply_on_sunday_fields();

		// Add apply on monday fields
		$this->add_coupon_apply_on_monday_fields();

		// Add apply on tuesday fields
		$this->add_coupon_apply_on_tuesday_fields();

		// Add apply on wednesday fields
		$this->add_coupon_apply_on_wednesday_fields();

		// Add apply on thursday fields
		$this->add_coupon_apply_on_thursday_fields();

		// Add apply on friday fields
		$this->add_coupon_apply_on_friday_fields();

		echo '</div>';
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method apply_days_hours_of_week_checkbox
	 * @return void
	 * Add apply days and hours of week checkbox field
	 */
	private function apply_days_hours_of_week_checkbox()
	{
		global $post;

		$checked = get_post_meta( $post->ID, 'apply_days_hours_of_week', true );
		$checked = ! empty( $checked ) ? 'yes' : '';

		woocommerce_wp_checkbox(
			[
				'id' => 'apply_days_hours_of_week',
				'label' => esc_html__( 'Valid for days/hours', 'hex-coupon-for-woocommerce' ),
				'description' => esc_html__( 'Check this box to make coupon valid for specific days and hours of the week.', 'hex-coupon-for-woocommerce' ),
				'value' => $checked,

			]
		);
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_saturday_fields
	 * @return void
	 * Add coupon apply on saturday all fields.
	 */
	private function add_coupon_apply_on_saturday_fields()
	{
		global $post;

		$sat_coupon_start_time = get_post_meta( $post->ID, 'sat_coupon_start_time', true );
		$sat_coupon_expiry_time = get_post_meta( $post->ID, 'sat_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Saturday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                            <span>
                                <label id="coupon_apply_on_saturday_label" for="coupon_apply_on_saturday" class="switch">
                                    <input type="checkbox" name="coupon_apply_on_saturday" id="coupon_apply_on_saturday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_saturday', true ), 1 ); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </span>
						</p>
					</div>

					<div class="time-hours-start-expiry">
						<p class="form-field saturday">
                            <span class="first-input">
                                <input type="text" class="time-picker-saturday coupon_start_time" name="sat_coupon_start_time" value="<?php echo esc_attr( $sat_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_saturday">-</span>
                                <input type="text" class="time-picker-saturday coupon_start_time" name="sat_coupon_expiry_time" value="<?php echo esc_attr( $sat_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                                <input type="hidden" id="total_hours_count_saturday" name="total_hours_count_saturday" value="<?php $total_hours_count_saturday =  get_post_meta( $post->ID, 'total_hours_count_saturday', true ) ; echo esc_attr( $total_hours_count_saturday ); ?>">
                            </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_saturday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'sat_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'sat_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-saturday coupon_start_time' name='sat_coupon_start_time_".$i."' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_saturday'>-</span><input type='text' class='time-picker-saturday coupon_expiry_time' name='sat_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_saturday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-sat">
							<span class="add_more_hours_sat_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="sat_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>

					<span id="sat_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Saturday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
			<span class="time-hours-border-bottom"></span>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_sunday_fields
	 * @return void
	 * Add coupon apply on sunday all fields.
	 */
	private function add_coupon_apply_on_sunday_fields()
	{
		global $post;

		$sun_coupon_start_time = get_post_meta( $post->ID, 'sun_coupon_start_time', true );
		$sun_coupon_expiry_time = get_post_meta( $post->ID, 'sun_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Sunday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                           <label id="coupon_apply_on_sunday_label" for="coupon_apply_on_sunday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_sunday" id="coupon_apply_on_sunday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_sunday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field sunday">
                            <span class="first-input">
                                <input type="text" class="time-picker-sunday coupon_start_time" name="sun_coupon_start_time" value="<?php echo esc_attr( $sun_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_sunday">-</span>
                                <input type="text" class="time-picker-sunday coupon_start_time" name="sun_coupon_expiry_time" value="<?php echo esc_attr( $sun_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                                <input type="hidden" id="total_hours_count_sunday" name="total_hours_count_sunday" value="<?php $total_hours_count_sunday = intval( get_post_meta( $post->ID, 'total_hours_count_sunday', true ) ); echo esc_attr( $total_hours_count_sunday); ?>">
                            </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_sunday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'sun_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'sun_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-sunday coupon_start_time' name='sun_coupon_start_time_".$i."' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_sunday'>-</span><input type='text' class='time-picker-sunday coupon_start_time' name='sun_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_sunday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-sun">
							<span class="add_more_hours_sun_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="sun_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="sun_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Sunday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_monday_fields
	 * @return void
	 * Add coupon apply on monday all fields.
	 */
	private function add_coupon_apply_on_monday_fields()
	{
		global $post;

		$mon_coupon_start_time = get_post_meta( $post->ID, 'mon_coupon_start_time', true );
		$mon_coupon_expiry_time = get_post_meta( $post->ID, 'mon_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Monday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                           <label id="coupon_apply_on_monday_label" for="coupon_apply_on_monday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_monday" id="coupon_apply_on_monday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_monday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field monday">
                            <span class="first-input">
                                <input type="text" class="time-picker-monday coupon_start_time" name="mon_coupon_start_time" value="<?php echo esc_attr( $mon_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_monday">-</span>
                                <input type="text" class="time-picker-monday coupon_start_time" name="mon_coupon_expiry_time" value="<?php echo esc_attr( $mon_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                                <input type="hidden" id="total_hours_count_monday" name="total_hours_count_monday" value="<?php $total_hours_count_monday = intval( get_post_meta( $post->ID, 'total_hours_count_monday', true ) ); echo esc_attr( $total_hours_count_monday ); ?>">
                            </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_monday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'mon_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'mon_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-monday' name='mon_coupon_start_time_".$i."' id='coupon_start_time' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_monday'>-</span><input type='text' class='time-picker-monday coupon_expiry_time' name='mon_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_monday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-mon">
							<span class="add_more_hours_mon_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="mon_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="mon_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Monday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_tuesday_fields
	 * @return void
	 * Add coupon apply on tuesday all fields.
	 */
	private function add_coupon_apply_on_tuesday_fields()
	{
		global $post;

		$tue_coupon_start_time = get_post_meta( $post->ID, 'tue_coupon_start_time', true );
		$tue_coupon_expiry_time = get_post_meta( $post->ID, 'tue_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Tuesday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                           <label id="coupon_apply_on_tuesday_label" for="coupon_apply_on_tuesday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_tuesday" id="coupon_apply_on_tuesday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_tuesday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field tuesday">
                           <span class="first-input">
                                <input type="text" class="time-picker-tuesday coupon_start_time" name="tue_coupon_start_time" value="<?php echo esc_attr( $tue_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_tuesday">-</span>
                                <input type="text" class="time-picker-tuesday coupon_start_time" name="tue_coupon_expiry_time" value="<?php echo esc_attr( $tue_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                                <input type="hidden" id="total_hours_count_tuesday" name="total_hours_count_tuesday" value="<?php $total_hours_count_tuesday = intval( get_post_meta( $post->ID, 'total_hours_count_tuesday', true ) ); echo esc_attr( $total_hours_count_tuesday ); ?>">
                           </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_tuesday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'tue_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'tue_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-tuesday' name='tue_coupon_start_time_".$i."' id='coupon_start_time' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_tuesday'>-</span><input type='text' class='time-picker-tuesday coupon_expiry_time' name='tue_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_tuesday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-tue">
							<span class="add_more_hours_tue_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="tue_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="tue_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Tuesday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_wednesday_fields
	 * @return void
	 * Add coupon apply on wednesday all fields.
	 */
	private function add_coupon_apply_on_wednesday_fields()
	{
		global $post;

		$wed_coupon_start_time = get_post_meta( $post->ID, 'wed_coupon_start_time', true );
		$wed_coupon_expiry_time = get_post_meta( $post->ID, 'wed_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Wednesday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                           <label id="coupon_apply_on_wednesday_label" for="coupon_apply_on_wednesday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_wednesday" id="coupon_apply_on_wednesday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_wednesday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field wednesday">
                        <span class="first-input">
                            <input type="text" class="time-picker-wednesday coupon_start_time" name="wed_coupon_start_time" value="<?php echo esc_attr( $wed_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_wednesday">-</span>
                            <input type="text" class="time-picker-wednesday coupon_start_time" name="wed_coupon_expiry_time" value="<?php echo esc_attr( $wed_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                            <input type="hidden" id="total_hours_count_wednesday" name="total_hours_count_wednesday" value="<?php $total_hours_count_wednesday = intval( get_post_meta( $post->ID, 'total_hours_count_wednesday', true ) ); echo esc_attr( $total_hours_count_wednesday ); ?>">
                        </span>

							<?php
							for ($i = 1; $i <= $total_hours_count_wednesday; $i++) {
								$start_time = get_post_meta( $post->ID, 'wed_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'wed_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-wednesday' name='wed_coupon_start_time_".$i."' id='coupon_start_time' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_wednesday'>-</span><input type='text' class='time-picker-wednesday coupon_expiry_time' name='wed_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_wednesday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-wed">
							<span class="add_more_hours_wed_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="wed_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="wed_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Wednesday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_thursday_fields
	 * @return void
	 * Add coupon apply on thursday all fields.
	 */
	private function add_coupon_apply_on_thursday_fields()
	{
		global $post;

		$thu_coupon_start_time = get_post_meta( $post->ID, 'thu_coupon_start_time', true );
		$thu_coupon_expiry_time = get_post_meta( $post->ID, 'thu_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Thursday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                            <label id="coupon_apply_on_thursday_label" for="coupon_apply_on_thursday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_thursday" id="coupon_apply_on_thursday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_thursday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field thursday">
                        <span class="first-input">
                                <input type="text" class="time-picker-thursday coupon_start_time" name="thu_coupon_start_time" value="<?php echo esc_attr( $thu_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_thursday">-</span>
                                <input type="text" class="time-picker-thursday coupon_start_time" name="thu_coupon_expiry_time" value="<?php echo esc_attr( $thu_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                                <input type="hidden" id="total_hours_count_thursday" name="total_hours_count_thursday" value="<?php $total_hours_count_thursday = intval( get_post_meta( $post->ID, 'total_hours_count_thursday', true ) ); echo esc_attr( $total_hours_count_thursday ); ?>">
                        </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_thursday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'thu_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'thu_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                      <input type='text' class='time-picker-thursday' name='thu_coupon_start_time_".$i."' id='coupon_start_time' value='".$start_time."' placeholder='HH:MM'>
                                                      <span class='input_separator_thursday'>-</span><input type='text' class='time-picker-thursday coupon_expiry_time' name='thu_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                      <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_thursday cross-hour'></a>
                                                    </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-thu">
							<span class="add_more_hours_thu_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="thu_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="thu_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Thursday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @package hexcoupon
	 * @author WpHex
	 * @since 1.0.0
	 * @method add_coupon_apply_on_friday_fields
	 * @return void
	 * Add coupon apply on friday all fields.
	 */
	private function add_coupon_apply_on_friday_fields()
	{
		global $post;

		$fri_coupon_start_time = get_post_meta( $post->ID, 'fri_coupon_start_time', true );
		$fri_coupon_expiry_time = get_post_meta( $post->ID, 'fri_coupon_expiry_time', true );
		?>
		<div class="options_group day_time_hours_block">
			<div class="options_group__saturdayWrap">
				<div class="options_group__saturdayWrap__inner">
					<div class="time-hours-label-checkbox">
						<p class="form-field form-day-field">
                            <span class="day-title">
                                <?php esc_html_e( 'Friday', 'hex-coupon-for-woocommerce' ); ?>
                            </span>
						</p>
						<p class="form-field">
                        <span>
                            <label id="coupon_apply_on_friday_label" for="coupon_apply_on_friday" class="switch">
                                <input type="checkbox" name="coupon_apply_on_friday" id="coupon_apply_on_friday" value="1" <?php checked( get_post_meta( $post->ID, 'coupon_apply_on_friday', true ), 1 ); ?>>
                                <span class="slider round"></span>
                            </label>
                        </span>
						</p>
					</div>
					<div class="time-hours-start-expiry">
						<p class="form-field friday">
                        <span class="first-input">
                            <input type="text" class="time-picker-friday coupon_start_time" name="fri_coupon_start_time" value="<?php echo esc_attr( $fri_coupon_start_time ); ?>" placeholder="HH:MM" /><span class="input_separator_friday">-</span>
                            <input type="text" class="time-picker-friday coupon_start_time" name="fri_coupon_expiry_time" value="<?php echo esc_attr( $fri_coupon_expiry_time ); ?>" placeholder="HH:MM" />

                            <input type="hidden" id="total_hours_count_friday" name="total_hours_count_friday" value="<?php $total_hours_count_friday = intval( get_post_meta( $post->ID, 'total_hours_count_friday', true ) ); echo esc_attr( $total_hours_count_friday ); ?>">
                        </span>

							<?php
							for ( $i = 1; $i <= $total_hours_count_friday; $i++ ) {
								$start_time = get_post_meta( $post->ID, 'fri_coupon_start_time_' . $i, true );
								$expiry_time = get_post_meta( $post->ID, 'fri_coupon_expiry_time_' . $i, true );
								$appendedElement = "<span class='appededItem first-input'>
                                                  <input type='text' class='time-picker-friday' name='fri_coupon_start_time_".$i."' id='coupon_start_time' value='".$start_time."' placeholder='HH:MM'>
                                                  <span class='input_separator_friday'>-</span><input type='text' class='time-picker-friday coupon_expiry_time' name='fri_coupon_expiry_time_".$i."' value='".$expiry_time."' placeholder='HH:MM'>
                                                  <a href='javascript:void(0)' class='dashicons dashicons-no-alt cross_hour_friday cross-hour'></a>
                                                </span>";
								echo $appendedElement;
							}
							?>
						</p>
						<p class="form-field add-more-hours-fri">
							<span class="add_more_hours_fri_pro_text"><?php echo esc_html__( 'To add more hours switch to Pro version', 'hex-coupon-for-woocommerce' ); ?></span>
							<a id="fri_add_more_hours" href="javascript:void(0)"><?php echo esc_html__( 'Add More Hours', 'hex-coupon-for-woocommerce' );?></a>
						</p>
					</div>
					<span id="fri_deactivated_text"><?php echo esc_html__( 'Coupon deactivated for Friday', 'hex-coupon-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
}
