import React, { useEffect, useState } from 'react';
import { Skeleton } from "../../Skeleton";
import Tabs from '../../utils/tab/Tabs';
import { __ } from '@wordpress/i18n';

const SpinWheel = () => {
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		// Simulating some loading delay (e.g., fetching data)
		const timeout = setTimeout(() => {
			setIsLoading(false);
		}, 200); // Adjust the timeout value as needed

		return () => clearTimeout(timeout);
	}, []); // Empty dependency array to run effect only once on mount

	const tabs = [
		{ title: 'General', content: 'Input fields and save button will be rendered here' },
		{ title: 'Pop-up Setting', content: 'Input fields and save button will be rendered here' },
		{ title: 'Wheel Setting', content: <div>Content for tab 3</div> },
		{ title: 'Wheel Content', content: <div>Content for tab 4</div> },
		{ title: 'Text Setting', content: <div>Content for tab 5</div> },
		{ title: 'Coupon Setting', content: <div>Content for tab 6</div> },
	];

	return (
		<>
			{isLoading ? (
				<Skeleton height={1000} radius={10} />
			) : (
				<Tabs tabs={tabs} />
			)}
		</>
	);
}

export default SpinWheel;