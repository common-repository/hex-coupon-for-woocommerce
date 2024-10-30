import React, { useEffect, useState } from "react";
import 'react-toastify/dist/ReactToastify.css';
import { Skeleton } from "../../Skeleton";
import Switch from "../../utils/switch/Switch";
import { toast, ToastContainer } from "react-toastify";
import axios from "axios";
import { getNonce, getPostRequestUrl } from "../../../utils/helper";
import coinImg from '../../../img/coin.png';
import { __ } from '@wordpress/i18n';
import { TbSettings } from "react-icons/tb";
import { useNavigate } from "react-router-dom";

const LoyaltyProgramSettings = () => {
	const { nonce, ajaxUrl } = loyaltyProgramData;
	const [isLoading, setIsLoading] = useState(true);
	const [switchState, setSwitchState] = useState(false);
	const navigate = useNavigate();

	const handleSwitchChange = (newSwitchState) => {
		setSwitchState(newSwitchState);
	};

	const submitLoyaltyProgramSettings = () => {
		axios
			.post(getPostRequestUrl('loyalty_program_settings_save'), {
				nonce: getNonce(),
				action: 'loyalty_program_settings_save',
				enable: switchState,
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				// handle response if needed
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	}

	const handleButtonClick = () => {
		submitLoyaltyProgramSettings();
		toast.success('Option saved!', {
			position: 'top-center',
			autoClose: 1000,
			hideProgressBar: false,
			closeOnClick: true,
			pauseOnHover: false,
			draggable: true,
			onClose: handleToastClose
		});
	};

	const handleToastClose = () => {
		if (switchState) {
			navigate('/loyalty-program/point-based-loyalty-settings');
		}
	};

	useEffect(() => {
		axios
			.get(ajaxUrl, {
				params: {
					nonce: nonce,
					action: 'loyalty_program_enable_data',
				},
				headers: {
					'Content-Type': 'application/json',
				},
			})
			.then(({ data }) => {
				setSwitchState(data.loyaltyProgramEnable.enable);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce]);


	const handleSettingsClick = () => {
		const path = "/loyalty-program/point-based-loyalty-settings";
		navigate(path);
	};

	return (
		<div className="loyalty-program-settings">
			<h2 className="store_credit_enable_title">{__("Loyalty Program Settings", "hex-coupon-for-woocommerce")}</h2>
			{isLoading ? (
				<Skeleton height={500} radius={10} />
			) : (
				<>
					<div className="loyalty-option">
						<div className="loyalty-icon">
							<img src={coinImg} alt="Point Loyalties Icon" />
						</div>
						<div className="loyalty-details">
							<h3>{__("Point Loyalties","hex-coupon-for-woocommerce")}</h3>
							<p>{__("Customize point loyalties settings","hex-coupon-for-woocommerce")}</p>
						</div>
						<div className="loyalty-toggle">
							<Switch isChecked={switchState} onSwitchChange={handleSwitchChange} />
							<TbSettings
								size={24}
								className="loyalty-settings-icon"
								style={{ display: "inline-block", marginLeft: "5px", cursor: "pointer" }}
								onClick={handleSettingsClick}
							/>
						</div>
					</div>
					<div className="store-credit-enable-notice">
						<p>{__("'Loyalty points' are auto converted to 'Store Credit'. So, enable 'Store Credit' to enjoy this feature","hex-coupon-for-woocommerce")}</p>
					</div>
					<div className="save-button-container">
						<input
							type="submit"
							value={__("Save","hex-coupon-for-woocommerce")}
							className="save-button"
							onClick={handleButtonClick}
						/>
					</div>
					<ToastContainer />
				</>
			)}
		</div>
	);
}

export default LoyaltyProgramSettings;

