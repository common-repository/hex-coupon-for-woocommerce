import React, {useEffect, useState} from 'react';
import { TbFilePlus,TbGift,TbLink,TbMapPinCancel,TbTruckDelivery } from "react-icons/tb";
import { __ } from '@wordpress/i18n';

const Quick_Links = () => {
	const [siteUrl, setSiteUrl] = useState('')
	useEffect(() => {
		setSiteUrl(window.location.href);

	}, []);

	const trimmedUrl = siteUrl.split('wp-admin/')[0]

	const finalUrl = trimmedUrl+'wp-admin/post-new.php?post_type=shop_coupon';

	return (
		<div className="hexcoupon_quick_links">
			<p>{__("Quick Links:","hex-coupon-for-woocommerce")}</p>
			<a href={finalUrl}><TbFilePlus size={24} />{__("Add New Coupon","hex-coupon-for-woocommerce")}</a>
			<a href={finalUrl+"#general_coupon_data_bogo"}><TbGift size={24}/>{__("BOGO Coupon","hex-coupon-for-woocommerce")}</a>
			<a href={finalUrl+"#sharable_url_coupon_tab"} onClick="goToCouponTab('sharable_url_coupon_tab'); return false;"><TbLink size={24} />{__("URL Coupon","hex-coupon-for-woocommerce")}</a>
			<a href={finalUrl+"#geographic_restriction_tab"}><TbMapPinCancel size={24} />{__("Geographic Restriction","hex-coupon-for-woocommerce")}</a>
			<a href={finalUrl+"#custom_coupon_tab"}><TbTruckDelivery size={24} />{__("Payment and Shipping","hex-coupon-for-woocommerce")}</a>
		</div>
	)
}
export default Quick_Links;


