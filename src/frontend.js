import metadata from './block.json';
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '@wordpress/components';
import { useEffect, useState, useCallback, useRef } from "react";
import axios from "axios";

// Global import
const { registerCheckoutBlock } = wc.blocksCheckout;

const Block = ({ children, checkoutExtensionData }) => {
	if (typeof storeCreditData === 'undefined') {
		return null; // or handle the error appropriately
	}

	const { nonce, cart_total, total_remaining_store_credit, postUrl } = storeCreditData;

	const [storeCredit, setStoreCredit] = useState('0');

	const remainingCredit = parseFloat(total_remaining_store_credit);

	const cartTotal = parseFloat(cart_total);
	const deductedTotal = parseFloat(remainingCredit) > parseFloat(cartTotal) ? cartTotal : remainingCredit;

	const { setExtensionData } = checkoutExtensionData;
	const myRef = useRef(null);

	// Function to handle checkbox change
	useEffect(() => {
		setExtensionData('hex-coupon-for-woocommerce', 'use_store_credit', storeCredit);
	}, [storeCredit, setExtensionData]);

	const onInputChange = useCallback(
		(isChecked) => {
			// Convert isChecked to '1' if true, '0' if false
			const valueToSend = isChecked ? '1' : '0';
			setStoreCredit(valueToSend);
			setExtensionData('hex-coupon-for-woocommerce', 'use_store_credit', valueToSend);
			// Call submitStoreCreditSettings with the checkbox value
			submitStoreCreditSettings(storeCredit, valueToSend);
		},
		[setStoreCredit, setExtensionData, storeCredit]
	);


	useEffect(() => {
		// Ensure that submitStoreCreditSettings is called with the updated text content
		if (myRef.current) {
			submitStoreCreditSettings(myRef.current.textContent, storeCredit);
		}
	}, [myRef.current, storeCredit]);

	const submitStoreCreditSettings = (deductedValue, enableValue) => {
		axios.post(getPostRequestUrl('store_credit_deduction_and_enable_save'), {
			nonce: getNonce(),
			action: 'store_credit_deduction_and_enable_save',
			deductedStoreCredit: deductedValue,
			useStoreCredit: enableValue,
		}, {
			headers: {
				"Content-Type": "multipart/form-data"
			}
		})
			.then((response) => {
				// Handle response if needed
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	// Function to get the request URL
	function getPostRequestUrl(action) {
		return `${postUrl}?action=${action}`;
	}

	// Function to get the nonce
	function getNonce() {
		return nonce;
	}

	return (
		<>
			<div className="wc-block-components">
				<h5>{__("Available Store Credit: ", "hex-coupon-for-woocommerce") + remainingCredit.toFixed(2)}</h5>
				<CheckboxControl className="store_credit_chckbox" label={__("Use Store Credit", "hex-coupon-for-woocommerce")} onChange={onInputChange} name="use_store_credit" style={{marginRight:"5px"}}/>

				{storeCredit === '1' && (
					<span style={{fontWeight:"bold"}}>
                        {storeCredit && __("Store Credit Used: -", "hex-coupon-for-woocommerce")}
						<b ref={myRef}>{storeCredit && deductedTotal.toFixed(2)}</b>
                    </span>
				)}
			</div>
		</>
	);
};

const options = {
	metadata,
	component: Block,
};

registerCheckoutBlock(options);
