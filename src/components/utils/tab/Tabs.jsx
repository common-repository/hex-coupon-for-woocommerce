import React, { useEffect, useState } from 'react';
import '../../../scss/components/_tabs.scss';
import Switch from "../../utils/switch/Switch";

import { __ } from '@wordpress/i18n';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';
import axios from "axios";
import { getNonce, getPostRequestUrl } from "../../../utils/helper";
import { toast, ToastContainer } from "react-toastify";
import { Skeleton } from "../../Skeleton";
import Select from 'react-select';

const { nonce, ajaxUrl } = loyaltyProgramData;

const Tabs = ({ tabs }) => {
	
	const [allProducts, setAllProducts] = useState([]);
	const [allCategories, setAllCategories] = useState([]);
	const [allPages, setAllPages] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [selectedUsers, setSelectedUsers] = useState([]);

    const [selectedExcludeProducts, setSelectedExcludeProducts] = useState([]);
    const [selectedExcludeCategories, setSelectedExcludeCategories] = useState([]);
	const [selectedPages,setSelectedPages] = useState([]);

	const handleUserSelect = (selectedOptions) => {
		const selectedIds = selectedOptions.map(option => option.value);
		setSelectedUsers(selectedIds);
		// setSelectedCustomersCount(selectedOptions.length);
	}

	useEffect(() => {
		// Fetch all combined data (products, categories) and pages
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
				// Format WooCommerce Products
				const formattedProducts = Object.entries(data.allWooCommerceProduct).map(([id, name]) => ({
					value: id, // The key (product ID) as the value
					label: name, // The value (product name) as the label
				}));
				setAllProducts(formattedProducts);
	
				// Format WooCommerce Categories
				const formattedCategories = Object.entries(data.allWooCommerceCategories).map(([id, name]) => ({
					value: id, // The key (category ID) as the value
					label: name, // The value (category name) as the label
				}));
				setAllCategories(formattedCategories);

				const formattedPages = Object.entries(data.allPages).map(([id, title]) => ({
					value: id,
					label: title,
				}));
				setAllPages(formattedPages);
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce]);
	
	const [formData, setFormData] = useState(null);
	const [activeTab, setActiveTab] = useState(0);

	useEffect(() => {
		axios
			.get(ajaxUrl, {
				params: {
					nonce: nonce,
					action: 'spin_wheel_settings_data',
				},
				headers: {
					'Content-Type': 'application/json',
				},
			})
			.then(({ data }) => {
				// For Excluded Categories
				const spinExcludeCategoriesIds = data.spinWheelSettingsData.spinWheelCoupon.spinExcludeCategories || [];
				const excludedCategories = spinExcludeCategoriesIds.map(id =>
					allCategories.find(category => category.value === id)
				).filter(category => category); // Filter out undefined values
				setSelectedExcludeCategories(excludedCategories);
	
				// For Excluded Products
				const spinExcludeProductsIds = data.spinWheelSettingsData.spinWheelCoupon.spinExcludeProducts || [];
				const excludedProducts = spinExcludeProductsIds.map(id =>
					allProducts.find(product => product.value === id)
				).filter(product => product); // Filter out undefined values
				setSelectedExcludeProducts(excludedProducts);
	
				// Fetch selected pages IDs from the API
				const selectedPagesId = data.spinWheelSettingsData.spinWheelPopup.selectedPages || [];
				// Map selectedPages IDs to the corresponding pages from allPages
				const mappedSelectedPages = selectedPagesId.map(pageId => {
					return allPages.find(page => page.value === pageId);
				}).filter(page => page); // Remove any undefined entries
				setSelectedPages(mappedSelectedPages);
	
				// Initialize formData once spinWheelData is available
				let spinWheelContentArray = Object.keys(data.spinWheelSettingsData?.spinWheelContent || {}).map(key => {
					const item = data.spinWheelSettingsData.spinWheelContent[key];
					return {
						id: parseInt(key.replace('content', '')) || key, // Extract numeric ID or keep key as fallback
						...item,
					};
				});
	
				if (spinWheelContentArray.length === 0) {
					spinWheelContentArray = [
						{ id: 1, couponType: 'Non', label: 'Not Lucky', value: 0, color: '#ffe0b2' },
						{ id: 2, couponType: 'Percentage discount', label: '{coupon_amount} OFF', value: 5, color: '#ec65100' },
						{ id: 3, couponType: 'Non', label: 'Not Lucky', value: 0, color: '#ffb74d' },
						{ id: 4, couponType: 'Fixed product discount', label: '{coupon_amount} OFF', value: 10, color: '#ff8c00' },
					];
				}				
	
				setFormData({
					tab1: {
						field1: data.spinWheelSettingsData.spinWheelGeneral.enableSpinWheel,
						field2: data.spinWheelSettingsData.spinWheelGeneral.spinPerEmail,
						field3: data.spinWheelSettingsData.spinWheelGeneral.delayBetweenSpins,
					},
					tab2: {
						field1: data.spinWheelSettingsData.spinWheelPopup.iconColor,
						field3: data.spinWheelSettingsData.spinWheelPopup.popupInterval,
						field4: data.spinWheelSettingsData.spinWheelPopup.showOnlyHomepage,  
						field5: data.spinWheelSettingsData.spinWheelPopup.showOnlyBlogPage,  
						field6: data.spinWheelSettingsData.spinWheelPopup.showOnlyShopPage,   
						field7: data.spinWheelSettingsData.spinWheelPopup.selectedPages,
					},
					tab3: {
						titleText: data.spinWheelSettingsData.spinWheelWheel.titleText,
						titleColor: data.spinWheelSettingsData.spinWheelWheel.titleColor,
						textColor: data.spinWheelSettingsData.spinWheelWheel.textColor,
						wheelDescription: data.spinWheelSettingsData.spinWheelWheel.wheelDescription,
						wheelDescriptionColor: data.spinWheelSettingsData.spinWheelWheel.wheelDescriptionColor,
						buttonText: data.spinWheelSettingsData.spinWheelWheel.buttonText,
						buttonColor: data.spinWheelSettingsData.spinWheelWheel.buttonColor,
						buttonBGColor: data.spinWheelSettingsData.spinWheelWheel.buttonBGColor,
						enableYourName: data.spinWheelSettingsData.spinWheelWheel.enableYourName,
						yourName: data.spinWheelSettingsData.spinWheelWheel.yourName,						
						enableEmailAddress: data.spinWheelSettingsData.spinWheelWheel.enableEmailAddress,
						emailAddress: data.spinWheelSettingsData.spinWheelWheel.emailAddress,
						gdprMessage: data.spinWheelSettingsData.spinWheelWheel.gdprMessage,
					},
					tab4: {
						settings: spinWheelContentArray, // Use the array directly
					},
					tab5: {
						field1: data.spinWheelSettingsData.spinWheelText.emailSubject || '',
						field2: data.spinWheelSettingsData.spinWheelText.emailContent || '',
						field3: data.spinWheelSettingsData.spinWheelText.frontendMessageIfWin || '',
						field4: data.spinWheelSettingsData.spinWheelText.frontendMessageIfLost || '',
					},
					tab6: {
						field1: data.spinWheelSettingsData.spinWheelCoupon.spinAllowFreeShipping,
						field2: data.spinWheelSettingsData.spinWheelCoupon.spinMinimumSpend,
						field3: data.spinWheelSettingsData.spinWheelCoupon.spinMaximumSpend,
						field4: data.spinWheelSettingsData.spinWheelCoupon.spinIndividualSpendOnly,
						field5: data.spinWheelSettingsData.spinWheelCoupon.spinExcludeSaleItem,
						field7: data.spinWheelSettingsData.spinWheelCoupon.spinExcludeProducts,
						field9: data.spinWheelSettingsData.spinWheelCoupon.spinExcludeCategories,
						field10: data.spinWheelSettingsData.spinWheelCoupon.spinUsageLimitPerCoupon,
						field11: data.spinWheelSettingsData.spinWheelCoupon.spinLimitUsageToXItems,
						field12: data.spinWheelSettingsData.spinWheelCoupon.spinUsageLimitPerUser,
					}					
				});
				setSettings(spinWheelContentArray);
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	}, [allCategories, allProducts, allPages, nonce]);	

	const [settings, setSettings] = useState([
        { id: 1, couponType: 'Non', label: 'Not Lucky', value: 0, color: '#ffe0b2' },
        { id: 2, couponType: 'Percentage discount', label: '{coupon_amount} OFF', value: 5, color: '#ec65100' },
        { id: 3, couponType: 'Non', label: 'Not Lucky', value: 0, color: '#ffb74d' },
        { id: 4, couponType: 'Fixed product discount', label: '{coupon_amount} OFF', value: 10, color: '#ff8c00' },
    ]);

	const handleInputChange = (id, field, value) => {
		setFormData(prevFormData => ({
			...prevFormData,
			tab4: {
				...prevFormData.tab4,
				settings: prevFormData.tab4.settings.map(setting =>
					setting.id === id ? { ...setting, [field]: value } : setting
				)
			}
		}));
	
		setSettings(prevSettings =>
			prevSettings.map(setting =>
				setting.id === id ? { ...setting, [field]: value } : setting
			)
		);
	};

	const handleFormChange = (e, tab) => {
		const { name, type, checked, value } = e.target;
		const newValue = type === 'checkbox' ? checked : value;
		
		setFormData(prevFormData => ({
			...prevFormData,
			[tab]: { 
				...prevFormData[tab], 
				[name]: newValue 
			}
		}));
	};
	

	const handleSave = (tab) => {
		// Implement save logic here
		if (tab === 'tab1') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinWheelGeneral();
		} else if (tab === 'tab2') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinWheelPopup();
		} else if (tab === 'tab3') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinWheelWheel();
		} else if (tab === 'tab4') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinWheelContent();
		} else if (tab === 'tab5') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinWheelText();
		} else if (tab === 'tab6') {
			toast.info('Saving...', {
				position: 'top-center',
				autoClose: 1000,
				hideProgressBar: false,
				closeOnClick: true,
				pauseOnHover: false,
				draggable: true,
			});
			submitSpinCoupon();
		}
	};

	const submitSpinWheelGeneral = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_general_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_general_settings_save',
				settings: {
					enableSpinWheel: formData.tab1.field1, // Enable spin wheel
					spinPerEmail: formData.tab1.field2, // Spin per email field
					delayBetweenSpins: formData.tab1.field3, // Delay between spins field
				},
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				// Handle the successful response here
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	const submitSpinWheelPopup = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_popup_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_popup_settings_save',
				settings: {
					iconColor: formData.tab2.field1,
					popupInterval: formData.tab2.field3,
					showOnlyHomepage: formData.tab2.field4,
					showOnlyBlogPage: formData.tab2.field5,
					showOnlyShopPage: formData.tab2.field6,
					selectedPages: selectedPages.map(page => page.value)
				},
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	const submitSpinWheelWheel = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_wheel_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_wheel_settings_save',
				settings: {
					titleText: formData.tab3.titleText,
					titleColor: formData.tab3.titleColor,
					textColor: formData.tab3.textColor, // Text color from color picker
					wheelDescription: formData.tab3.wheelDescription, // Wheel description from ReactQuill
					wheelDescriptionColor: formData.tab3.wheelDescriptionColor, // wheel description color
					buttonText: formData.tab3.buttonText, // Button text
					buttonColor: formData.tab3.buttonColor, // Button color from color picker
					buttonBGColor: formData.tab3.buttonBGColor, 
					enableYourName: formData.tab3.enableYourName, // Enable Your Name switch
					enableEmailAddress: formData.tab3.enableEmailAddress, // Enable Email Address switch
					gdprMessage: formData.tab3.gdprMessage, // GDPR message from ReactQuill
				},
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				// Handle the successful response here
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	const submitSpinWheelContent = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_content_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_content_settings_save',
				settings: settings, // Submit the updated settings
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};	

	const submitSpinWheelText = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_text_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_text_settings_save',
				settings: {
					emailSubject: formData.tab5.field1,
					emailContent: formData.tab5.field2,
					frontendMessageIfWin: formData.tab5.field3,
					frontendMessageIfLost: formData.tab5.field4,
				},
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				// Handle the successful response here
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};

	const submitSpinCoupon = () => {
		axios
			.post(getPostRequestUrl('spin_wheel_coupon_settings_save'), {
				nonce: getNonce(),
				action: 'spin_wheel_coupon_settings_save',
				settings: {
					spinAllowFreeShipping: formData.tab6.field1,
					spinMinimumSpend: formData.tab6.field2,
					spinMaximumSpend: formData.tab6.field3,
					spinIndividualSpendOnly: formData.tab6.field4,
					spinExcludeSaleItem: formData.tab6.field5,
                    spinExcludeProducts: selectedExcludeProducts.map(product => product.value),
                    spinExcludeCategories: selectedExcludeCategories.map(category => category.value),
					spinUsageLimitPerCoupon: formData.tab6.field10,
					spinLimitUsageToXItems: formData.tab6.field11,
					spinUsageLimitPerUser: formData.tab6.field12,
				},
			}, {
				headers: {
					"Content-Type": "multipart/form-data"
				}
			})
			.then((response) => {
				toast.success('Option saved!', {
					position: 'top-center',
					autoClose: 1000,
					hideProgressBar: false,
					closeOnClick: true,
					pauseOnHover: false,
					draggable: true,
				});
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	};	

	const renderTabContent = (index) => {
		if (!formData) {
			return <div><Skeleton height={500} radius={10} /></div>; // Add a loading state
		}

		switch (index) {
			case 0:
				return (
					<div className="tab-content-item general active">
						<div className="general-item">
							<label>{__("Enable/Disable Spin", "hex-coupon-for-woocommerce-pro")}</label>
							<Switch
								isChecked={formData.tab1.field1}
								onSwitchChange={(isChecked) =>
									setFormData({
										...formData,
										tab1: { ...formData.tab1, field1: isChecked },
									})
								}
							/>
						</div>
						<div className="general-item">
							<label>{__("Spin per email(if user spin count exceeds he won't see the spin wheel again)", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="number"
								name="field2"
								value={formData.tab1.field2}
								onChange={(e) => handleFormChange(e, 'tab1')}
							/>
						</div>
						<div className="general-item">
							<label>{__("Delay between each spin(in seconds)", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="number"
								name="field3"
								value={formData.tab1.field3}
								onChange={(e) => handleFormChange(e, 'tab1')}
							/>
						</div>
						<div className="general-item">
							<label></label>
							<button className="save" type="button" onClick={() => handleSave('tab1')}>{__("Save","hex-coupon-for-woocommerce")}</button>
						</div>
					</div>
				);
			case 1:
				return (
					<div className="tab-content-item popup-settings active">
						<div className="popup-settings">
							<div className="color-picker-container">
								<label>{__("Pop-Up Background Color", "hex-coupon-for-woocommerce-pro")}</label>
								<input
									type="color"
									className="colorPicker"
									value={formData.tab2.field1} // This holds the color value
									onChange={(e) => handleFormChange(e, 'tab2')}
									name="field1" // Corrected name attribute
								/>
							</div>
						</div>
						<div className="popup-settings">
							<label>{__("If customers close and not spin, show popup again after(in seconds)", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="number"
								name="field3" // Corrected name attribute
								value={formData.tab2.field3} // This holds the popup interval value
								onChange={(e) => handleFormChange(e, 'tab2')}
							/>
						</div>
						<div className="popup-settings">
							<label>{__("Show on Homepage", "hex-coupon-for-woocommerce-pro")}</label>
							<Switch
								isChecked={formData?.tab2?.field4}  // Use optional chaining to avoid errors
								onSwitchChange={(isChecked) => setFormData({
									...formData,
									tab2: {
										...formData.tab2,
										field4: isChecked
									}
								})}
							/>
						</div>
						<div className="popup-settings">
							<label>{__("Show on Blog page", "hex-coupon-for-woocommerce-pro")}</label>
							<Switch
								isChecked={formData.tab2.field5}
								onSwitchChange={(isChecked) => setFormData({ ...formData, tab2: { ...formData.tab2, field5: isChecked } })}
							/>
						</div>
						<div className="popup-settings">
							<label>{__("Show on Shop page", "hex-coupon-for-woocommerce-pro")}</label>
							<Switch
								isChecked={formData.tab2.field6}
								onSwitchChange={(isChecked) => setFormData({ ...formData, tab2: { ...formData.tab2, field6: isChecked } })}
							/>
						</div>

						<div className="popup-settings">
							<label>{__("Select Specific Pages", "hex-coupon-for-woocommerce-pro")}</label>
							
							<Select
								
								closeMenuOnSelect={false}
								isMulti
								options={allPages}
								className="mt-2 selectedPages"
								value={selectedPages}
								onChange={setSelectedPages}
							/>

							
						

						</div>

						

						<div className="popup-settings">
							<label></label>
							<button className="save" type="button" onClick={() => handleSave('tab2')}>{__("Save","hex-coupon-for-woocommerce-pro")}</button>
						</div>
					</div>
				);
			case 2:
				return (
					<div className="tab-content-item wheel-content active">
						
						<div className="wheel-settings">
							<label>{__("Title Text", "hex-coupon-for-woocommerce")}</label>
							<input
								type="text"
								name="titleText"
								value={formData.tab3.titleText}
								onChange={(e) => handleFormChange(e, 'tab3')}
							/>
						</div>

						<div className="wheel-settings">
							<label>{__("Title Text Color", "hex-coupon-for-woocommerce")}</label>
							<input
								type="color"
								className="colorPicker"
								value={formData.tab3.titleColor}
								onChange={(e) => handleFormChange(e, 'tab3')}
								name="titleColor"
							/>
						</div>
						
						<div className="wheel-settings wysiwyg-container">
							<label>{__("Wheel Description", "hex-coupon-for-woocommerce-pro")}</label>
							<ReactQuill
								value={formData.tab3.wheelDescription}
								onChange={(content) => setFormData({ ...formData, tab3: { ...formData.tab3, wheelDescription: content } })}
							/>
						</div>

						<div className="wheel-settings">
							<label>{__("Description Color", "hex-coupon-for-woocommerce")}</label>
							<input
								type="color"
								className="colorPicker"
								value={formData.tab3.wheelDescriptionColor}
								onChange={(e) => handleFormChange(e, 'tab3')}
								name="wheelDescriptionColor"
							/>
						</div>

						<div className="wheel-settings">
							<label>{__("Button Text", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="text"
								name="buttonText"
								value={formData.tab3.buttonText}
								onChange={(e) => handleFormChange(e, 'tab3')}
							/>
						</div>

						<div className="wheel-settings">
							<label>{__("Button Text Color", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="color"
								className="colorPicker"
								value={formData.tab3.buttonColor}
								onChange={(e) => handleFormChange(e, 'tab3')}
								name="buttonColor"
							/>
						</div>

						<div className="wheel-settings">
							<label>{__("Button Background Color", "hex-coupon-for-woocommerce-pro")}</label>
							<input
								type="color"
								className="colorPicker"
								value={formData.tab3.buttonBGColor}
								onChange={(e) => handleFormChange(e, 'tab3')}
								name="buttonBGColor"
							/>
						</div>
						
						<div className="wheel-settings">
							<label>{__("Your Name", "hex-coupon-for-woocommerce-pro")}</label>
							<div className="name">
								<Switch
									isChecked={formData.tab3.enableYourName}
									onSwitchChange={(isChecked) => setFormData({ ...formData, tab3: { ...formData.tab3, enableYourName: isChecked } })}
								/>
							</div>
						</div>

						<div className="wheel-settings">
							<label>{__("Email Address", "hex-coupon-for-woocommerce-pro")}</label>
							<div className="email">
								<Switch
									isChecked={formData.tab3.enableEmailAddress}
									onSwitchChange={(isChecked) => setFormData({ ...formData, tab3: { ...formData.tab3, enableEmailAddress: isChecked } })}
								/>
							</div>
						</div>
						
						<div className="wheel-settings wysiwyg-container">
							<label>{__("GDPR Checkbox Text", "hex-coupon-for-woocommerce-pro")}</label>
							<ReactQuill
								value={formData.tab3.gdprMessage}
								onChange={(content) => setFormData({ ...formData, tab3: { ...formData.tab3, gdprMessage: content } })}
							/>
						</div>

						<div className="wheel-settings wysiwyg-container">
							<label></label>
							<button className="save" type="button" onClick={() => handleSave('tab3')}>
								{__("Save", "hex-coupon-for-woocommerce-pro")}
							</button>
						</div>
					</div>
				);
			case 3:
				return (
					<div className="wheel-content">
						<table>
							<thead>
								<tr>
									<th>{__("Index", "hex-coupon-for-woocommerce-pro")}</th>
									<th>{__("Coupon Type", "hex-coupon-for-woocommerce-pro")}</th>
									<th>{__("Label", "hex-coupon-for-woocommerce-pro")}</th>
									<th>{__("Value", "hex-coupon-for-woocommerce-pro")}</th>
									<th>{__("Color", "hex-coupon-for-woocommerce-pro")}</th>
								</tr>
							</thead>
							<tbody>
								{formData.tab4.settings.map((setting, index) => (
									<tr key={index}>
										<td>{index + 1}</td>
										<td>
											<select
												value={setting.couponType}
												onChange={(e) => handleInputChange(setting.id, 'couponType', e.target.value)}
											>
												<option value="Non">{__("Non", "hex-coupon-for-woocommerce-pro")}</option>
												<option value="Percentage discount">{__("Percentage discount", "hex-coupon-for-woocommerce-pro")}</option>
												<option value="Fixed product discount">{__("Fixed product discount", "hex-coupon-for-woocommerce-pro")}</option>
												<option value="Fixed cart discount">{__("Fixed cart discount", "hex-coupon-for-woocommerce-pro")}</option>
											</select>
										</td>
										<td>
											<input
												type="text"
												value={setting.label}
												onChange={(e) => handleInputChange(setting.id, 'label', e.target.value)}
											/>
										</td>
										<td>
											<input
												type="number"
												value={setting.value}
												min={0}
												onChange={(e) => handleInputChange(setting.id, 'value', e.target.value)}
											/>
										</td>
										<td>
											<input
												type="color"
												value={setting.color}
												onChange={(e) => handleInputChange(setting.id, 'color', e.target.value)}
											/>
										</td>
									</tr>
								))}
							</tbody>
						</table>
						<button className="save" type="button" onClick={submitSpinWheelContent}>
							{__("Save", "hex-coupon-for-woocommerce-pro")}
						</button>
					</div>
				);
			case 4:
				return (
					<div className="tab-content-item text-setting active">
						<div className="text-setting-item">
							<label>{__("Email Subject", "hex-coupon-for-woocommerce")}</label>
							<input
								type="text"
								placeholder="Enter email subject here"
								name="field1"  // This should match the key in the formData object
								value={formData.tab5.field1}
								onChange={(e) => handleFormChange(e, 'tab5')}
							/>
						</div>

						<div className="text-setting-item wysiwyg-container">
							
								<label>{__("Email content", "hex-coupon-for-woocommerce-pro")}</label>
								<ReactQuill
									value={formData.tab5.field2}
									onChange={(content) => setFormData({ ...formData, tab5: { ...formData.tab5, field2: content } })}
								/>
							
						</div>

						<div className="text-setting-item">
							<label>{__("Frontend Message if win", "hex-coupon-for-woocommerce-pro")}</label>								
							<textarea
								value={formData.tab5.field3}
								onChange={(e) => setFormData({ ...formData, tab5: { ...formData.tab5, field3: e.target.value } })}
								rows="5"
								cols="50"
							/>
						</div>

						<div className="text-setting-item">
							<label>{__("Frontend message if lost", "hex-coupon-for-woocommerce-pro")}</label>
							<textarea
								value={formData.tab5.field4}
								onChange={(e) => setFormData({ ...formData, tab5: { ...formData.tab5, field4: e.target.value } })}
								rows="5"
								cols="50"
							/>
						</div>
						<div className="text-setting-item">
							<label></label>
							<button className="save" type="button" onClick={() => handleSave('tab5')}>
								{__("Save", "hex-coupon-for-woocommerce-pro")}
							</button>
						</div>
					</div>
				);
			case 5:
				return (
					<div className="tab-content-item active">
					<div className="coupon-setting">
						<form onSubmit={(e) => { e.preventDefault(); handleSave('tab6'); }}>
							
							{/* Allow Free Shipping */}
							<div className="form-group">
								<label>
									<input
										type="checkbox"
										name="field1"
										checked={formData.tab6.field1}
										onChange={(e) => handleFormChange(e, 'tab6')}
									/>
									Allow free shipping
								</label>
								<p>
									Check this box if the coupon grants free shipping. A{' '}
									<a href="#">free shipping method</a> must be enabled in your
									shipping zone and be set to require a "valid free shipping
									coupon".
								</p>
							</div>

							{/* Minimum Spend */}
							<div className="form-group">
								<label>Minimum spend</label>
								<input
									type="number"
									name="field2"
									value={formData.tab6.field2}
									onChange={(e) => handleFormChange(e, 'tab6')}
									placeholder="No minimum"
								/>
								<p>The minimum spend to use the coupon.</p>
							</div>

							{/* Maximum Spend */}
							<div className="form-group">
								<label>Maximum spend</label>
								<input
									type="number"
									name="field3"
									value={formData.tab6.field3}
									onChange={(e) => handleFormChange(e, 'tab6')}
									placeholder="No maximum"
								/>
								<p>The maximum spend to use the coupon.</p>
							</div>

							{/* Individual Use Only */}
							<div className="form-group">
								<label>
									<input
										type="checkbox"
										name="field4"
										checked={formData.tab6.field4}
										onChange={(e) => handleFormChange(e, 'tab6')}
									/>
									Individual use only
								</label>
								<p>
									Check this box if the coupon cannot be used in conjunction
									with other coupons.
								</p>
							</div>

							{/* Exclude Sale Items */}
							<div className="form-group">
								<label>
									<input
										type="checkbox"
										name="field5"
										checked={formData.tab6.field5}
										onChange={(e) => handleFormChange(e, 'tab6')}
									/>
									Exclude sale items
								</label>
								<p>
									Check this box if the coupon should not apply to items on
									sale. Per-item coupons will only work if the item is not on
									sale. Per-cart coupons will only work if there are items in
									the cart that are not on sale.
								</p>
							</div>

							{/* Exclude Products */}
							<div className="form-group">
								<label>Exclude Products</label>
								<Select
									closeMenuOnSelect={false}
									isMulti
									options={allProducts}
									className="mt-2"
									value={selectedExcludeProducts}
									onChange={setSelectedExcludeProducts}
								/>
								<p>
									Products that the coupon will not be applied to, so that
									cannot be in the cart in order for the "Fixed cart discount"
									to be applied.
								</p>
							</div>

							{/* Exclude Categories */}
							<div className="form-group">
								<label>Exclude Categories</label>
								<Select
									closeMenuOnSelect={false}
									isMulti
									options={allCategories}
									className="mt-2"
									value={selectedExcludeCategories}
									onChange={setSelectedExcludeCategories}
								/>
								<p>
									Product categories that the coupon will not be applied to, or
									that cannot be in the cart in order for the "Fixed cart
									discount" to be applied.
								</p>
							</div>

							{/* Usage Limit Per Coupon */}
							<div className="form-group">
								<label>Usage limit per coupon</label>
								<input
									type="number"
									name="field10"
									value={formData.tab6.field10}
									onChange={(e) => handleFormChange(e, 'tab6')}
									min="1"
								/>
								<p>How many times this coupon can be used before it is void.</p>
							</div>

							{/* Limit Usage to X Items */}
							<div className="form-group">
								<label>Limit usage to X items</label>
								<input
									type="number"
									name="field11"
									value={formData.tab6.field11}
									onChange={(e) => handleFormChange(e, 'tab6')}
									min="1"
								/>
								<p>
									The maximum number of individual items this coupon can apply
									to when using product discount.
								</p>
							</div>

							{/* Usage Limit Per User */}
							<div className="form-group">
								<label>Usage limit per user</label>
								<input
									type="number"
									name="field12"
									value={formData.tab6.field12}
									onChange={(e) => handleFormChange(e, 'tab6')}
									min="1"
								/>
								<p>How many times this coupon can be used by an individual user.</p>
							</div>

							<button type="submit" className="save-button">
								Save
							</button>
						</form>
					</div>
				</div>
				);
			default:
				return <div className="tab-content-item active">{tabs[index].content}</div>;
		}
	};

	return (
		<div className="tabs">
			<ul className="tab-list">
				{tabs.map((tab, index) => (
					<li
						key={index}
						className={`tab-list-item ${activeTab === index ? 'active' : ''}`}
						onClick={() => setActiveTab(index)}
					>
						{tab.title}
					</li>
				))}
			</ul>
			<div className="tab-content">
				{tabs.map((tab, index) => (
					<div
						key={index}
						className={`tab-content-item ${activeTab === index ? 'active' : ''}`}
					>
						{renderTabContent(index)}
					</div>
				))}
			</div>
			<ToastContainer />
		</div>
	);
};

export default Tabs;