wp.domReady(() => {
	const { createElement, render, useState, useEffect, useRef } = wp.element;
	const { Notice } = wp.components;

	const CustomNotice = () => {
		const [points, setPoints] = useState(0);
		const [divider, setDivider] = useState(0);
		const [multiplier, setMultiplier] = useState(0);
		const pointsRef = useRef(null);

		useEffect(() => {
			const fetchMultiplier = async () => {
				try {
					const response = await jQuery.ajax({
						url: pointsForCheckoutBlock.ajax_url,
						method: 'POST',
						data: {
							action: 'show_loyalty_points_in_checkout',
							security: pointsForCheckoutBlock.nonce
						}
					});

					if (response.success) {
						setDivider(response.data.spendingAmount);
						setMultiplier(response.data.pointAmount);
					}
				} catch (error) {
					console.error('Error fetching multiplier:', error);
				}
			};

			const calculatePoints = () => {
				const totalElement = document.querySelector('.wc-block-components-totals-item__value');
				if (totalElement && divider && multiplier) {
					const totalPrice = parseFloat(totalElement.innerText.replace(/[^\d.-]/g, ''));
					const calculatedPoints = Math.floor(totalPrice / divider) * multiplier;
					setPoints(calculatedPoints);
				} else {
					setPoints(0);
				}
			};

			fetchMultiplier();
			calculatePoints();
		}, [divider, multiplier]);

		return createElement(
			Notice,
			{
				status: 'info',
				isDismissible: false,
			},
			`You will earn `,
			createElement('span', { className: 'points-value', ref: pointsRef }, points),
			` points with this order.`
		);
	};

	const checkoutForm = document.querySelector('.wc-block-checkout');
	if (checkoutForm) {
		const noticeWrapper = document.createElement('div');
		noticeWrapper.classList.add('custom-checkout-notice-wrapper');
		checkoutForm.prepend(noticeWrapper);
		render(createElement(CustomNotice), noticeWrapper);
	}
});
