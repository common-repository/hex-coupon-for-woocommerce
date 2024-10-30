import React, { useEffect, useState } from "react";
import axios from "axios";
import { Skeleton } from "../../Skeleton";
import Table from "../../utils/table/Table";
import THead from "../../utils/table/THead";
import Th from "../../utils/table/Th";
import TBody from "../../utils/table/TBody";
import { __ } from '@wordpress/i18n';
import HexCardHeaderLeft from "../../HexCardHeader/HexCardHeaderLeft";
import HexCardHeaderTitle from "../../HexCardHeader/HexCardHeaderTitle";
import PieChart from "../../HexCharts/PieChart/PieChart";

const TopLoyaltyPointsEarner = () => {
	const {nonce,ajaxUrl} = hexCuponData;
	const [isLoading, setIsLoading] = useState(true);
	const [topPointsEarners, setTopPointsEarners] = useState([]);
	const [topPointsReasons, setTopPointsReasons] = useState([]);
	const [topStoreCreditSources, setTopStoreCreditSources] = useState([]);
	const [topStoreCreditAmounts, setTopStoreCreditAmounts] = useState([]);

	const labels = [ 'No Data Available',];
	const dataValues = [ 1 ];

	useEffect(() => {
		axios
			.get(ajaxUrl, {
				params: {
					nonce: nonce,
					action: "all_combined_data",
				},
				headers: {
					"Content-Type": "application/json",
				},
			})
			.then(({ data }) => {
				if (data && data.topPointsEarner) {
					setTopPointsEarners(data.topPointsEarner);
					setTopPointsReasons(data.topPointsReasons);
					setTopStoreCreditSources(data.topStoreCreditSources);
					setTopStoreCreditAmounts(data.topStoreCreditAmounts);

				} else {
					console.error("Invalid data format", data);
				}
			})
			.catch((error) => {
				console.error("Error:", error);
			})
			.finally(() => setIsLoading(false));
	}, [nonce, ajaxUrl]);


	return (
		<>
			<div className="loyalty-dashboard-container">
				{isLoading ? (
					<Skeleton height={500} radius={10} />
				) : (
					<>
						<div className="loyalty-dashboard-box">
							<div className="hexDashboard__card mt-4 radius-10">
								<div className="hexDashboard__card__header">
									<div className="hexDashboard__card__header__flex">
										<HexCardHeaderLeft>
											<HexCardHeaderTitle titleHeading={__("Top Loyalty Points Sources","hex-coupon-for-woocommerce")} />
										</HexCardHeaderLeft>
									</div>
								</div>
								<div className="hexDashboard__card__inner mt-4">
									<Table className="border text-left">
										<THead>
											<Th>{__("Sources")}</Th>
											<Th>{__("Points")}</Th>
										</THead>
										<TBody>
											{topPointsReasons.length > 0 ? (
												topPointsReasons.map((log, index) => (
													<tr key={index}>
														<td>
															{log.reason}
														</td>
														<td>{log.points}</td>
													</tr>
												))
											) : (
												<tr style={{ textAlign: "center" }}>
													<td colSpan="8">{__("No data available")}</td>
												</tr>
											)}
										</TBody>
									</Table>
								</div>
							</div>
						</div>
					</>
				)}

				{isLoading ? (
					<Skeleton height={500} radius={10} />
				) : (
					<>
						<div className="loyalty-dashboard-box">
							<div className="hexDashboard__card mt-4 radius-10">
								<div className="hexDashboard__card__header">
									<div className="hexDashboard__card__header__flex">
										<HexCardHeaderLeft>
											<HexCardHeaderTitle titleHeading={__("Top Loyalty Points Earner","hex-coupon-for-woocommerce")} />
										</HexCardHeaderLeft>
									</div>
								</div>
								<div className="hexDashboard__card__inner mt-4">
									<Table className="border text-left">
										<THead>
											<Th>{__("Customer Name")}</Th>
											<Th>{__("Points")}</Th>
										</THead>
										<TBody>
											{topPointsEarners.length > 0 ? (
												topPointsEarners.map((log, index) => (
													<tr key={index}>
														<td>
															{log.user_name}
														</td>
														<td>{log.points}</td>
													</tr>
												))
											) : (
												<tr style={{ textAlign: "center" }}>
													<td colSpan="8">{__("No data available")}</td>
												</tr>
											)}
										</TBody>
									</Table>
								</div>
							</div>
						</div>
					</>
				)}

				{isLoading ? (
					<Skeleton height={500} radius={10} />
				) : (
					<>
						<div className="loyalty-dashboard-box">
							<div className="hexDashboard__card mt-4 radius-10">
								<div className="hexDashboard__card__header">
									<div className="hexDashboard__card__header__flex">
										<HexCardHeaderLeft>
											<HexCardHeaderTitle titleHeading={__("Top Store Credit Sources","hex-coupon-for-woocommerce")} />
										</HexCardHeaderLeft>
									</div>
								</div>
								<div className="pieChart">
									<PieChart
										labels={topStoreCreditSources.length > 0 ? topStoreCreditSources : labels}
										dataValues={topStoreCreditAmounts.length > 0 ? topStoreCreditAmounts : dataValues}
									/>
								</div>
							</div>
						</div>
					</>
				)}
			</div>
		</>
	);
};

export default TopLoyaltyPointsEarner;
