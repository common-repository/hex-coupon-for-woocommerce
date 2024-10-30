import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Link, useLocation } from 'react-router-dom';
import {
	TbHome,
	TbMenu2,
	TbChevronDown,
	TbDiscount,
	TbBook,
	TbCrown,
	TbHelpSquareRounded,
	TbBox,
	TbCoin,
	TbGiftCard,
	TbFidgetSpinner,
	TbSettingsAutomation,
	TbCoins,
} from "react-icons/tb";
import LogoImg from '../../../img/logo.png';
import { useSidebar } from '../../context/SidebarContext';

const Sidebar = () => {
	const [siteUrl, setSiteUrl] = useState('')
	useEffect(() => {
		setSiteUrl(window.location.href);

	}, []);

	const trimmedUrl = siteUrl.split('wp-admin/')[0]

	const finalUrl = trimmedUrl + 'wp-admin/post-new.php?post_type=shop_coupon';

	const location = useLocation();
	const [activeLink, setActiveLink] = useState('/');

	useEffect(() => {
		setActiveLink(location.pathname);
	}, [location.pathname]);

	const handleLinkClick = (path) => {
		setActiveLink(path);
	};

	// toggle open class add remove
	const toggleOpenClass = (event) => {
		const currentItem = event.currentTarget;
		const siblings = currentItem.parentNode.children;
		for (let siblingItem of siblings) {
			if (siblingItem !== currentItem && siblingItem.classList.contains('has-children') && siblingItem.classList.contains('open')) {
				siblingItem.classList.remove('open');
			}
		}
		currentItem.classList.toggle('open');
	};

	const stopPropagation = (event) => {
		event.stopPropagation();
	};

	const storeCredit = ['/store-credit', '/store-credit/store-credit-settings', '/store-credit/store-credit-logs'];

	const { toggleSidebar, closeSidebar, isSidebarActive } = useSidebar();
	const loyaltyProgram = ['/loyalty-program/loyalty-program-settings', '/loyalty-program/loyalty-program-logs'];

	return (
		<>
			<div className={`sidebarOverlay ${isSidebarActive ? 'active' : ''}`} onClick={closeSidebar}></div>
			<div className="mobileIcon lg:hidden" onClick={toggleSidebar}><TbMenu2 /></div>

			<aside className={`hexpDashboard__left sidebarWrapper ${isSidebarActive ? 'active' : ''}`}>
				<div className="hexpDashboard__left__header">
					<div className="hexpDashboard__left__header__logo logoWrapper">
						<Link to="/"><img src={LogoImg} alt="" /></Link>
					</div>
				</div>
				<div className="hexpDashboard__left__inner">

					<ul className='hexpDashboard__list mt-4'>
						<li className='hexpDashboard__list__item'>
							<Link to="/" className={`hexpDashboard__list__item__link ${activeLink === '/' ? 'active' : ''}`} onClick={() => handleLinkClick('/')}>
								<span className='hexpDashboard__list__item__link__left'><TbHome size={24} />{__("Dashboard", "hex-coupon-for-woocommerce")}</span>
							</Link>
						</li>
						<li className={`hexpDashboard__list__item has-children`} onClick={toggleOpenClass}>
							<span className={`hexpDashboard__list__item__link`}>
								<span className='hexpDashboard__list__item__link__left'><TbDiscount size={24} />{__("Coupon", "hex-coupon-for-wocommerce")}</span>
								<span className="arrowIcon"><TbChevronDown /></span>
							</span>
							<ul className="hexpDashboard__list submenu">
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<a href={finalUrl} className={`hexpDashboard__list__item__link`}>{__("Add New Coupon", "hex-coupon-for-woocommerce")}</a>
								</li>
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<a href={finalUrl + "#general_coupon_data_bogo"} className={`hexpDashboard__list__item__link`}>{__("Bogo Coupon", "hex-coupon-for-woocommerce")}</a>
								</li>
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<a href={finalUrl + "#sharable_url_coupon_tab"} className={`hexpDashboard__list__item__link`}>{__("URL Coupon", "hex-coupon-for-woocommerce")}</a>
								</li>
							</ul>
						</li>
						<li className={`hexpDashboard__list__item has-children ${storeCredit.includes(activeLink) ? 'active open' : ''}`} onClick={toggleOpenClass}>
							<span className={`hexpDashboard__list__item__link`}>
								<span className='hexpDashboard__list__item__link__left'><TbCoin size={24} />{__("Store Credit", "hex-coupon-for-wocommerce")}</span>
								<span className="arrowIcon"><TbChevronDown /></span>
							</span>
							<ul className="hexpDashboard__list submenu">
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<Link to="/store-credit/store-credit-settings" onClick={() => handleLinkClick('/store-credit/store-credit-settings')} className={`hexpDashboard__list__item__link ${activeLink === '/store-credit/store-credit-settings' ? 'active' : ''}`}>
										<span className="hexpDashboard__list__item__link__left">{__("Store Credit Settings", "hex-coupon-for-woocommerce")}</span>
									</Link>
								</li>
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<Link to="/store-credit/store-credit-logs" onClick={() => handleLinkClick('/store-credit/store-credit-logs')} className={`hexpDashboard__list__item__link ${activeLink === '/store-credit/store-credit-logs' ? 'active' : ''}`}>
										<span className="hexpDashboard__list__item__link__left">{__("Store Credit Logs", "hex-coupon-for-woocommerce")}</span>
									</Link>
								</li>
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<Link to="/store-credit/give-new-credit" onClick={() => handleLinkClick('/store-credit/give-new-credit')} className={`hexpDashboard__list__item__link ${activeLink === '/store-credit/give-new-credit' ? 'active' : ''}`}>
										<span className="hexpDashboard__list__item__link__left">{__("Give New Credit", "hex-coupon-for-woocommerce")}</span>
									</Link>
								</li>
							</ul>
						</li>

						<li className={`hexpDashboard__list__item has-children ${loyaltyProgram.includes(activeLink) ? 'active open' : ''}`} onClick={toggleOpenClass}>
							<span className={`hexpDashboard__list__item__link`}>
								<span className='hexpDashboard__list__item__link__left'><TbCoins size={24} />{__("Loyalty Program", "hex-coupon-for-wocommerce")}</span>
								<span className="arrowIcon"><TbChevronDown /></span>
							</span>
							<ul className="hexpDashboard__list submenu">
								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<Link to="/loyalty-program/loyalty-program-settings" onClick={() => handleLinkClick('/loyalty-program/loyalty-program-settings')} className={`hexpDashboard__list__item__link ${activeLink === '/loyalty-program/loyalty-program-settings' ? 'active' : ''}`}>
										<span className="hexpDashboard__list__item__link__left">{__("Loyalty Program Settings", "hex-coupon-for-woocommerce")}</span>
									</Link>
								</li>

								<li className="hexpDashboard__list__item" onClick={stopPropagation}>
									<Link to="/loyalty-program/loyalty-program-logs" onClick={() => handleLinkClick('/loyalty-program/loyalty-program-logs')} className={`hexpDashboard__list__item__link ${activeLink === '/loyalty-program/loyalty-program-logs' ? 'active' : ''}`}>
										<span className="hexpDashboard__list__item__link__left">{__("Loyalty Program Logs", "hex-coupon-for-woocommerce")}</span>
									</Link>
								</li>
							</ul>
						</li>

						<li className='hexpDashboard__list__item'>
							<Link to="/spinwheel" className={`hexpDashboard__list__item__link ${activeLink === '/spinwheel' ? 'active' : ''}`} onClick={() => handleLinkClick('/spinner')}>
								<span className='hexpDashboard__list__item__link__left'><TbFidgetSpinner size={24} />{__("SpinWheel", "hex-coupon-for-woocommerce")}</span>
							</Link>
						</li>
						<li className='hexpDashboard__list__item'>
							<Link to="/gift-card" className={`hexpDashboard__list__item__link ${activeLink === '/gift-card' ? 'active' : ''}`} onClick={() => handleLinkClick('/gift-card')}>
								<span className='hexpDashboard__list__item__link__left'><TbGiftCard size={24} />{__("Gift Card", "hex-coupon-for-woocommerce")}</span>
							</Link>
						</li>
						<li className='hexpDashboard__list__item'>
							<Link to="/automation" className={`hexpDashboard__list__item__link ${activeLink === '/automation' ? 'active' : ''}`} onClick={() => handleLinkClick('/automation')}>
								<span className='hexpDashboard__list__item__link__left'><TbSettingsAutomation size={24} />{__("Automation", "hex-coupon-for-woocommerce")}</span>
							</Link>
						</li>


					</ul>


					<div className="hexcoupon_resources">
						<p className='hexcoupon_resources__title'>{__("Our Resources", "hex-coupon-for-woocommerce")}</p>
						<ul className='hexpDashboard__list'>
							<li className='hexpDashboard__list__item'>
								<a href="https://hexcoupon.com/docs/" target="_blank" className='hexpDashboard__list__item__link'><span className="hexpDashboard__list__item__link__left"><TbBook size={24} />{__("Documentation", "hex-coupon-for-woocommerce")}</span></a>
							</li>
							<li className='hexpDashboard__list__item'>
								<a href="https://hexcoupon.com/pricing/" target="_blank" className='hexpDashboard__list__item__link'><span className="hexpDashboard__list__item__link__left"><TbCrown size={24} />{__("Upgrade to Pro", "hex-coupon-for-woocommerce")}</span></a>
							</li>
							<li className='hexpDashboard__list__item'>
								<a href="https://wordpress.org/support/plugin/hex-coupon-for-woocommerce/" target="_blank" className='hexpDashboard__list__item__link'><span className="hexpDashboard__list__item__link__left"><TbHelpSquareRounded size={24} />{__("Support", "hex-coupon-for-woocommerce")}</span></a>
							</li>
							<li className='hexpDashboard__list__item'>
								<a href="https://profiles.wordpress.org/wphex/#content-plugins" target="_blank" className='hexpDashboard__list__item__link'><span className="hexpDashboard__list__item__link__left"><TbBox size={24} />{__("Our Products", "hex-coupon-for-woocommerce")}</span></a>
							</li>
						</ul>
					</div>
				</div>
			</aside>
		</>
	);
};

export default Sidebar;
