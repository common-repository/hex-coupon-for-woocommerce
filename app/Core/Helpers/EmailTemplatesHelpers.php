<?php
namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;

class EmailTemplatesHelpers {
	use SingleTon;

	public function templateMarkup( $note, $user_name, $site_title, $order_id, $amount, $formatted_date, $type ) {
		ob_start();
		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_page_url = get_permalink( $shop_page_id );
		$store_credit_url = get_site_url() . '/my-account/store-credit';
		$email_header_img = plugin_dir_url( __FILE__ ) . '../../../assets/images/Coin.png';
		?>
		<!doctype html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
			<meta http-equiv="X-UA-Compatible" content="ie=edge">
			<title>title</title>
			<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

			<style>
				*{
					font-family: 'Open Sans', sans-serif;
				}
				.mail-container {
					max-width: 650px;
					margin: 0 auto;
					text-align: center;
					background-color: #a760fe0d;
					padding: 40px 0;
				}
				.inner-wrap {
					background-color: #fff;
					margin: 40px;
					padding: 0 0 20px 0;
					text-align: left;
					box-shadow: 0 0 20px 0 rgba(0,0,0,0.01);
				}
				.inner-wrap h2 {
					color: #ffffff;
					background: #A760FE;
					padding: 20px;
				}
				.inner-wrap p {
					font-size: 16px;
					line-height: 26px;
					color: #656565;
					margin: 0;
					padding: 0 20px;
				}
				.logo-wrapper img{
					max-width: 200px;
				}
				.wrap-para {
					text-align: left !important;
				}
				.inner-info .hex_link_one {
					font-size: 14px;
					font-weight: bold;
					text-decoration: none;
					color: #ffffff;
					background: #A760FE;
					border-radius: 4px;
					padding: 6px 20px;
					margin-right: 10px;
				}
				.inner-info .hex_link_two {
					font-size: 14px;
					font-weight: bold;
					text-decoration: none;
					color: #A760FE;
					background: #ffffff;
					border-radius: 4px;
					border: 1px solid #A760FE;
					padding: 6px 20px;
				}
				.inner-info .hex_links {
					padding: 25px;
				}
				.inner-info .info-text {
					padding: 20px 0 0 0;
				}
				.inner-wrap .header_img {
					margin-bottom: 10px;
				}
				.inner-wrap .header_img img {
					width: 100%;
				}

				@media screen and (max-width: 480px) {
					.inner-info .hex_link_one {
						display: block;
						text-align: center;
						max-width: 100px;
						margin-bottom: 10px;
					}
					.inner-info .hex_link_two {
						display: block;
						text-align: center;
						max-width: 100px;
					}
				}

			</style>
		</head>
		<body>

		<div class="mail-container">
			<?php if( 0 === $type ): ?>
			<div class="inner-wrap wrap-para">
				<div class="header_img">
					<img src="<?php echo esc_url( $email_header_img ); ?>" alt="">
				</div>
				<p style="margin-bottom: 10px;"><?php printf( esc_html__( 'Hello %s,', 'hex-coupon-for-woocommerce' ), esc_html( $user_name ) ); ?></p>
				<p><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $note ) ); ?></p>
				<p style="padding: 20px;"><?php esc_html_e( 'Thanks for shopping with ', 'hex-coupon-for-woocommerce' ); ?><b><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $site_title ) ); ?></b></p>
				<p><?php esc_html_e( 'Best Regards,', 'hex-coupon-for-woocommerce' ); ?></p>
				<p><b><?php printf( esc_html__( 'The %s Team', 'hex-coupon-for-woocommerce' ), esc_html( $site_title ) ); ?></b></p>
			</div>

			<?php else : ?>
			<div class="inner-wrap wrap-para">
				<div class="header_img">
					<img src="https://www.bankrate.com/2020/06/02131848/Meir-Jacob-Getty.jpeg?auto=webp&optimize=high" alt="">
				</div>
				<p style="margin-bottom: 10px;"><?php printf( esc_html__( 'Hello %s,', 'hex-coupon-for-woocommerce' ), esc_html( $user_name ) ); ?></p>
				<p><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $note ) ); ?></p>
				<div class="inner-info">
					<div class="info-text">
						<p><b><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $amount ) ); ?></b><?php esc_html_e( ' Store Credit', 'hex-coupon-for-woocommerce' ) ?></p>
						<p><span><?php esc_html_e( 'ID: ', 'hex-coupon-for-woocommerce' ); ?><b><?php printf( esc_html__( '%s . ', 'hex-coupon-for-woocommerce' ), esc_html( $order_id ) ); ?></b></span><span><?php esc_html_e( 'Issued: ', 'hex-coupon-for-woocommerce' ); ?><b><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $formatted_date ) ); ?></b></span></p>
					</div>
					<p class="hex_links"><a class="hex_link_one" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Shop Now', 'hex-coupon-for-woocommerce' ); ?></a><a class="hex_link_two" href="<?php echo esc_url( $store_credit_url ); ?>"><?php esc_html_e( 'View Balance', 'hex-coupon-for-woocommerce' ); ?></a></p>
				</div>
				<p style="padding-bottom: 20px;"><?php printf( esc_html__( 'You can use these credits for any future purchases on %s and you have the flexibility to choose what you want, whenever you want', 'hex-coupon-for-woocommerce' ), esc_html( $site_title ) ); ?></p>
				<p style="padding-bottom: 20px;"><?php esc_html_e( 'Thanks for shopping with ', 'hex-coupon-for-woocommerce' ); ?><b><?php printf( esc_html__( '%s', 'hex-coupon-for-woocommerce' ), esc_html( $site_title ) ); ?></b></p>
				<p><?php esc_html_e( 'Best Regards,', 'hex-coupon-for-woocommerce' ); ?></p>
				<p><b><?php printf( esc_html__( 'The %s Team', 'hex-coupon-for-woocommerc' ), esc_html( $site_title ) ); ?></b></p>
			</div>
			<?php endif; ?>
		</div>

		</body>
		</html>
		<?php
		$html = ob_get_clean();
		return $html;
	}
}
