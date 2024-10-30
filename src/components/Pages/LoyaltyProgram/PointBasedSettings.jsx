import React, { useEffect, useState } from "react";
import { TbChevronLeft } from "react-icons/tb";
import { useNavigate } from "react-router-dom";
import Switch from "../../utils/switch/Switch";
import { __ } from '@wordpress/i18n';
import { toast, ToastContainer } from "react-toastify";
import axios from "axios";
import { getNonce, getPostRequestUrl } from "../../../utils/helper";
import { Skeleton } from "../../Skeleton";

const PointBasedLoyaltySettings = () => {
	const { nonce, ajaxUrl } = loyaltyProgramData;
	const [isLoading, setIsLoading] = useState(true);
	const navigate = useNavigate();

	const goBack = () => {
		navigate(-1, { state: { refresh: true } });
	};

	const showProMessage = () => {
		toast.info(
			({ closeToast }) => (
				<div>
					Upgrade to <a href="https://hexcoupon.com/pricing/" target="_blank" rel="noopener noreferrer"><b style={{color:"#A760FE"}}>Pro</b></a> to use this feature!
				</div>
			),
			{
				position: 'top-center',
				autoClose: false,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			}
		);
	};

	const handleProFeatureSwitch = (event) => {
		showProMessage();
	};

	const [settings, setSettings] = useState({
		pointsOnPurchase: { enable: false, pointAmount: 100, spendingAmount: 1 },
		pointsForSignup: { enable: false, pointAmount: 100 },
		pointsForReferral: { enable: false, pointAmount: 100 },
		conversionRate: { points: 100, credit: 1 },
		allLoyaltyLabels: { logPageTitle: "Loyalty Points Log", referralLinkLabel: "Share Referral Link", pointsText: "Points earned so far" },
	});

	const handleSwitchChange = (field) => (newSwitchState) => {
		setSettings((prevSettings) => ({
			...prevSettings,
			[field]: {
				...prevSettings[field],
				enable: newSwitchState,
			},
		}));
	};

	const handleInputChange = (field, subField) => (event) => {
		const value = event.target.value;
		setSettings((prevSettings) => ({
			...prevSettings,
			[field]: {
				...prevSettings[field],
				[subField]: value,
			},
		}));
	};

	const handleLogPageTitleChange = (event) => {
		const value = event.target.value;
		setSettings((prevSettings) => ({
			...prevSettings,
			allLoyaltyLabels: {
				...prevSettings.allLoyaltyLabels,
				logPageTitle: value,
			},
		}));
	};

	const handleReferralLinkLabelChange = (event) => {
		const value = event.target.value;
		setSettings((prevSettings) => ({
			...prevSettings,
			allLoyaltyLabels: {
				...prevSettings.allLoyaltyLabels,
				referralLinkLabel: value,
			},
		}));
	};

	const handlePointsTextChange = (event) => {
		const value = event.target.value;
		setSettings((prevSettings) => ({
			...prevSettings,
			allLoyaltyLabels: {
				...prevSettings.allLoyaltyLabels,
				pointsText: value,
			},
		}));
	};

	const submitPointsLoyaltySettings = () => {
		axios
			.post(getPostRequestUrl('points_loyalty_settings_save'), {
				nonce: getNonce(),
				action: 'points_loyalty_settings_save',
				settings: settings,
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				// Handle the successful response here
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	const handleSave = () => {
		submitPointsLoyaltySettings();
		toast.success('Option saved!', {
			position: 'top-center',
			autoClose: 1000,
			hideProgressBar: false,
			closeOnClick: true,
			pauseOnHover: false,
			draggable: true,
		});
	};

	useEffect(() => {
		axios
			.get(ajaxUrl, {
				params: {
					nonce: nonce,
					action: 'point_loyalty_program_data',
				},
				headers: {
					'Content-Type': 'application/json',
				},
			})
			.then(({ data }) => {
				setSettings(data.pointLoyaltyProgramData);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce]);

	return (
		<div className="point-based-loyalty-settings">
			<h1>
				<TbChevronLeft onClick={goBack} className="back-icon" /> {__("Point Loyalty Settings","hex-coupon-for-woocommerce")}
			</h1>

			<div className="settings-section">
				{isLoading ? (
					<Skeleton height={500} radius={10} />
				) : (
					<>
						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Point on Purchase","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={settings.pointsOnPurchase.enable}
										onSwitchChange={handleSwitchChange("pointsOnPurchase")}
									/>
								</div>

								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value={settings.pointsOnPurchase.pointAmount}
											onChange={handleInputChange("pointsOnPurchase", "pointAmount")}
										/>
									</label>
									<label>
										{__("Spending Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value={settings.pointsOnPurchase.spendingAmount}
											onChange={handleInputChange("pointsOnPurchase", "spendingAmount")}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points for Signup","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={settings.pointsForSignup.enable}
										onSwitchChange={handleSwitchChange("pointsForSignup")}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value={settings.pointsForSignup.pointAmount}
											onChange={handleInputChange("pointsForSignup", "pointAmount")}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points for Referral","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={settings.pointsForReferral.enable}
										onSwitchChange={handleSwitchChange("pointsForReferral")}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value={settings.pointsForReferral.pointAmount}
											onChange={handleInputChange("pointsForReferral", "pointAmount")}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points for Review","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={false}
										onClick={handleProFeatureSwitch}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value="0"
											readOnly
											onClick={showProMessage}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points for Comment","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={false}
										onClick={handleProFeatureSwitch}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value="0"
											readOnly
											onClick={showProMessage}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points on Birthday","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={false}
										onClick={handleProFeatureSwitch}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value="0"
											readOnly
											onClick={showProMessage}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="setting-item">
							<div className="setting-header">
								<div className="switch-container">
									<span>{__("Points for Social Share","hex-coupon-for-woocommerce")}</span>
									<span className="switch-enabled">{__("Enabled","hex-coupon-for-woocommerce")}</span>
									<Switch
										isChecked={false}
										onClick={handleProFeatureSwitch}
									/>
								</div>
								<div className="setting-body">
									<label>
										{__("Point Amount","hex-coupon-for-woocommerce")}
										<input
											type="number"
											value="0"
											readOnly
											onClick={showProMessage}
										/>
									</label>
								</div>
							</div>
						</div>

						<div className="conversion-rate">
							<p>{__("Points","hex-coupon-for-woocommerce")}</p>
							<label>
								<input
									type="number"
									value={settings.conversionRate.points}
									onChange={handleInputChange("conversionRate", "points")}
								/>
								<span>{settings.conversionRate.points} POINTS = {settings.conversionRate.credit} {__("S.CREDIT","hex-coupon-for-woocommerce")}</span>
							</label>
							<p>{__("No. of points required to convert in 1 store credit","hex-coupon-for-woocommerce")}</p>
						</div>
						<div className="single__item p-5">
							<label htmlFor="pointsText" className="text-md text-[var(--hex-paragraph-color)]">{__("Points Text:", "hex-coupon-for-woocommerce")}</label>
							<input
								type="text"
								id="pointsText"
								value={settings.allLoyaltyLabels.pointsText}
								onChange={handlePointsTextChange}
								className="py-2.5 pl-4 pr-4 mt-2 h-[auto] w-full !border-transparent !ring-1 !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent"
							/>
						</div>
						<div className="single__item p-5">
							<label htmlFor="referralLinkLabel" className="text-md text-[var(--hex-paragraph-color)]">{__("Referral Link Label:", "hex-coupon-for-woocommerce")}</label>
							<input
								type="text"
								id="referralLinkLabel"
								value={settings.allLoyaltyLabels.referralLinkLabel}
								onChange={handleReferralLinkLabelChange}
								className="py-2.5 pl-4 pr-4 mt-2 h-[auto] w-full !border-transparent !ring-1 !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent"
							/>
						</div>
						<div className="single__item p-5">
							<label htmlFor="logPageTitle" className="text-md text-[var(--hex-paragraph-color)]">{__("Log Page Title:", "hex-coupon-for-woocommerce")}</label>
							<input
								type="text"
								id="logPageTitle"
								value={settings.allLoyaltyLabels.logPageTitle}
								onChange={handleLogPageTitleChange}
								className="py-2.5 pl-4 pr-4 mt-2 h-[auto] w-full !border-transparent !ring-1 !ring-[var(--hex-border-color)] text-md !text-[var(--hex-paragraph-color)] focus:!ring-[var(--hex-main-color-one)] focus:!border-transparent"
							/>
						</div>

						<div className="save-button-area">
							<button className="save-button" onClick={handleSave}>
								{__("Save Changes","hex-coupon-for-woocommerce")}
							</button>
							<ToastContainer />
						</div>
					</>
				)}
			</div>
		</div>
	);
};

export default PointBasedLoyaltySettings;
