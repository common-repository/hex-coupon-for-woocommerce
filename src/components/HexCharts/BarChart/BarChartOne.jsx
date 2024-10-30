import React, {useEffect, useState} from 'react';
import {
	Chart as ChartJS,
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';

import HexCardHeaderLeft from '../../HexCardHeader/HexCardHeaderLeft';
import HexCardHeaderTitle from '../../HexCardHeader/HexCardHeaderTitle';
import HexCardHeaderRight from '../../HexCardHeader/HexCardHeaderRight';
import SingleSelect from '../../Global/FormComponent/SingleSelect/SingleSelect';
import {getDataForCharJS, getSingleDayList, getWeekList} from "../../../helpers/helpers";
import axios from "axios";
import "../../../scss/skeleton/skeleton.scss";
import {Skeleton} from "../../Skeleton";

ChartJS.register(
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend
);

const BarChartOne = () => {
	const {restApiUrl,nonce,ajaxUrl,translate_array} = hexCuponData;

	const [isLoading,setIsLoading] = useState(true);
	const [couponBarchartData, setCouponBarchartData] = useState({
		todayCouponCreated : 0,
		todayCouponRedeemed : 0,
		todayActiveCoupons : 0,
		todayExpiredCoupons : 0,

		yesterdayCouponCreated : 0,
		yesterdayRedeemedCoupon : 0,
		yesterdayActiveCoupons : 0,
		yesterdayExpiredCoupons : 0,

		weeklyCouponCreated : [],
		weeklyCouponRedeemed : [],
		weeklyActiveCoupon : [],
		weeklyExpiredCoupon : [],
	})

	const [dataSet,setDataset]=useState({})
	const [barChartData, setBarChartData] = useState([]);

	let labels = getWeekList;
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
				.then(({data}) => {
					const initialChartData=	getDataForCharJS(labels, {
						created: data.weeklyCouponCreated,
						redeemed: data.weeklyCouponRedeemed,
						active: data.weeklyActiveCoupon,
						expired: data.weeklyExpiredCoupon,
					})

					setBarChartData(initialChartData)

					setDataset({
						created: data.weeklyCouponCreated,
						redeemed: data.weeklyCouponRedeemed,
						active: data.weeklyActiveCoupon,
						expired: data.weeklyExpiredCoupon,
					})

					setCouponBarchartData({
						todayCouponCreated: data.todayCouponCreated,
						todayCouponRedeemed: data.todayRedeemedCoupon,
						todayActiveCoupons: data.todayActiveCoupons,
						todayExpiredCoupons: data.todayExpiredCoupons,

						yesterdayCouponCreated: data.yesterdayCouponCreated,
						yesterdayRedeemedCoupon: data.yesterdayRedeemedCoupon,
						yesterdayActiveCoupons: data.yesterdayActiveCoupons,
						yesterdayExpiredCoupons: data.yesterdayExpiredCoupons,

						weeklyCouponCreated: data.weeklyCouponCreated,
						weeklyCouponRedeemed: data.weeklyCouponRedeemed,
						weeklyActiveCoupon: data.weeklyActiveCoupon,
						weeklyExpiredCoupon: data.weeklyExpiredCoupon,
					} );
				})

				.catch((error) => {
					console.error('Error:', error);
				})
				.finally(() => {
					setIsLoading(false);
				})

		},
		[isLoading]);

	const {
		todayCouponCreated,
		todayCouponRedeemed,
		todayActiveCoupons,
		todayExpiredCoupons,

		yesterdayCouponCreated,
		yesterdayRedeemedCoupon,
		yesterdayActiveCoupons,
		yesterdayExpiredCoupons,
	} = couponBarchartData;

	const SelectOptions = [
		{ value: 'Week', label: translate_array.thisWeekLabel },
		{ value: 'Yesterday', label: translate_array.yesterdayLabel },
		{ value: 'Today', label: translate_array.todayLabel },
	]

	let dataSetForToday = {
		created: [todayCouponCreated],
		redeemed: [todayCouponRedeemed],
		active: [todayActiveCoupons],
		expired: [todayExpiredCoupons],
	}

	let dataSetForYesterday = {
		created: [yesterdayCouponCreated],
		redeemed: [yesterdayRedeemedCoupon],
		active: [yesterdayActiveCoupons],
		expired: [yesterdayExpiredCoupons],
	}

	const options = {
		indexAxis: 'x',
		elements: {
			bar: {
				borderWidth: 2,
			},
		},
		responsive: true,
		plugins: {
			legend: {
				position: 'top',
			},
			title: {
				display: false,
				text: 'Bar Chart One',
			},
		},
		scales: {
			x: {
				display: true,
				beginAtZero: true,
				grid: {
					drawOnChartArea: false,
				},
			},
			y: {
				display: true,
				beginAtZero: true,
				grid: {
					drawOnChartArea: true,
				},
			},
		},
	};

	function handleChangeSelect(value){
		if (value === 'Week') {
			setBarChartData(getDataForCharJS(getWeekList, dataSet));
		}
		if (value === 'Yesterday') {
			setBarChartData(getDataForCharJS(getSingleDayList, dataSetForYesterday));
		}
		if (value === 'Today') {
			setBarChartData(getDataForCharJS(getSingleDayList, dataSetForToday));
		}
	}

	return (
		<>
			<div className="hexDashboard__card mt-4 radius-10">
				<div className="hexDashboard__card__header">
					<div className="hexDashboard__card__header__flex">
						<HexCardHeaderLeft>
							<HexCardHeaderTitle titleHeading="Coupon Insights" />
						</HexCardHeaderLeft>
						<HexCardHeaderRight>
							<SingleSelect options={SelectOptions} handleChangeSelect={handleChangeSelect} />
						</HexCardHeaderRight>
					</div>
				</div>
				<div className="hexDashboard__card__inner mt-4">
					{isLoading && (
						<Skeleton height={200} />
					)}
					{!isLoading && barChartData && <Bar data={barChartData} options={options}/>}
				</div>
			</div>
		</>
	);
};

export default BarChartOne;
