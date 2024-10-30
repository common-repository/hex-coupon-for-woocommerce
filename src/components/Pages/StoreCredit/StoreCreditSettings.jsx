import React, { useEffect, useState } from "react";
import Switch from "../../utils/switch/Switch";
import axios from "axios";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { getNonce, getPostRequestUrl } from "../../../utils/helper";
import { Skeleton } from "../../Skeleton";
import loyaltyCoinImg from "../../../img/loyalty-icon.png";
import { __ } from '@wordpress/i18n';

const StoreCreditSettings = () => {
	const { nonce, ajaxUrl } = hexCuponData;
	const [isLoading, setIsLoading] = useState(true);
	const [switchState, setSwitchState] = useState(false);

	const handleSwitchChange = (newSwitchState) => {
		setSwitchState(newSwitchState);
	};

	const submitStoreCreditSettings = () => {
		axios
			.post(getPostRequestUrl('store_credit_settings_save'), {
				nonce: getNonce(),
				action: 'store_credit_settings_save',
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
		submitStoreCreditSettings();
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
					action: 'all_combined_data',
				},
				headers: {
					'Content-Type': 'application/json',
				},
			})
			.then(({ data }) => {
				setSwitchState(data.storeCreditEnable.enable);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce]);

	return (
		<div className="store-credit-settings">
			<h2 className="store_credit_enable_title">{__("Store Credit Settings", "hex-coupon-for-woocommerce")}</h2>
			{isLoading ? (
				<Skeleton height={500} radius={10} />
			) : (
				<>
					<div className="store-credit-option">
						<div className="store-credit-icon">
							<img src={loyaltyCoinImg} alt="Point Loyalties Icon" />
						</div>
						<div className="store-credit-details">
							<h3>{__("Store Credit", "hex-coupon-for-woocommerce")}</h3>
							<p>{__("Enable on refund","hex-coupon-for-woocommerce")}</p>
						</div>
						<div className="store-credit-toggle">
							<Switch isChecked={switchState} onSwitchChange={handleSwitchChange} />
						</div>
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

export default StoreCreditSettings;

