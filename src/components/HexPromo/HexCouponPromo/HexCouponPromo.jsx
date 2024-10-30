import React from 'react';
import { useEffect, useState } from 'react';
import Counter from '../../Global/Counter/Counter';
import axios from "axios";
import {Skeleton} from "../../Skeleton";

const HexCouponPromo = () => {

	const [couponData, setCouponData] = useState({
		created: 0,
		active: 0,
		expired: 0,
		redeemed: 0,
		redeemedAmount: 0,
		sharableUrlPost: 0,
		bogoCoupon: 0,
		geographicRestriction: 0,
	});

	const { restApiUrl, nonce, ajaxUrl, translate_array } = hexCuponData;
	const [isLoading, setIsLoading] = useState(true);

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
				setCouponData({
					created: data.created,
					active: data.active,
					expired: data.expired,
					redeemed: data.redeemed,
					redeemedAmount: data.redeemedAmount,
					sharableUrlPost: data.sharableUrlPost,
					bogoCoupon: data.bogoCoupon,
					geographicRestriction: data.geographicRestriction,
				});

				// Handle the response data if needed
			})
			.catch((error) => {
				console.error('Error:', error);
			})
			.finally(() => setIsLoading(false));
	}, [isLoading]);

	const {
		created,
		active,
		expired,
		redeemed,
		redeemedAmount,
		sharableUrlPost,
		bogoCoupon,
		geographicRestriction,
	} = couponData;



	const CounterItem = [
        {
            counterSingle: {start: 0, end: created, duration: 2.5, separator: ","},
            counterPara: translate_array.couponsCreatedLabel,
        },
        {
            counterSingle: {start: 0, end: redeemed, duration: 2.5, separator: ","},
            counterPara: translate_array.couponsRedeemedLabel,
        },
        {
            counterSingle: {start: 0, end: active, duration: 2.5, separator: ","},
            counterPara: translate_array.couponsActiveLabel,
        },
        {
            counterSingle: {start: 0, end: expired, duration: 2.5, separator: ","},
            counterPara: translate_array.couponsExpiredLabel,
        },
		{
			counterSingle: {start: 0, end: redeemedAmount, duration: 2.5, separator: ","},
			leftIcon: '$',
			isAllowedDecimal: true,
			counterPara: translate_array.redeemedCouponValueLabel,
		},
		{
			counterSingle: {start: 0, end: sharableUrlPost, duration: 2.5, separator: ","},
			counterPara: translate_array.sharableUrlCouponsLabel,
		},
		{
			counterSingle: {start: 0, end: bogoCoupon, duration: 2.5, separator: ","},
			counterPara: translate_array.bogoCouponlabel,
		},
		{
			counterSingle: {start: 0, end: geographicRestriction, duration: 2.5, separator: ","},
			counterPara: translate_array.geographicRestrictionLabel,
		},
    ];

    return (
        <>
            <div className="promo__wrapper">
                <div className="hex-grid-container column-xxl-4 column-lg-3 column-sm-2">
                    {CounterItem.map((item, i) => (
                        <div className="grid-item" key={i}>
							{isLoading ? (
								<Skeleton height={100} radius={10} />
							) :
								(
								<Counter
									start={item.counterSingle.start}
									end={item.counterSingle.end}
									duration={item.counterSingle.duration}
									separator={item.counterSingle.separator}
									leftIcon={item.leftIcon}
									counterPara={item.counterPara}
									isAllowedDecimal={item.isAllowedDecimal ?? false}
								/>
							)}

                        </div>
                    ))}
                </div>
            </div>
        </>
    );
};

export default HexCouponPromo;
