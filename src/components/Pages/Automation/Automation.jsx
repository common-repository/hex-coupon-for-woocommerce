import React, {useEffect, useState} from 'react';
import { Skeleton } from "../../Skeleton";
import comingSoonImg from "../../../img/coming-soon.gif"

const Automation = () => {
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		// Simulating some loading delay (e.g., fetching data)
		const timeout = setTimeout(() => {
			setIsLoading(false);
		}, 200); // Adjust the timeout value as needed

		return () => clearTimeout(timeout);
	}, []); // Empty dependency array to run effect only once on mount

	return (
		<>
			{isLoading ? (
				<Skeleton height={1000} radius={10} />
			) : (
				<>
				</>
			)}
			<div className="coming-soon-wrapper">
				<img src={comingSoonImg} alt="coming-soon"/>
			</div>
		</>
	)
}

export default Automation;
