import React from 'react';
import HexCouponPromo from '../../HexPromo/HexCouponPromo/HexCouponPromo';
import BarChartOne from '../../HexCharts/BarChart/BarChartOne';
import Quick_Links from "../../Quick Links/Quik_Links";
import TopLoyaltyPointsEarner from "../../HexMain/LoyaltyProgram/TopLoyaltyPointsEarner";

const index = () => {
    return (
        <>
            <Quick_Links />
            <HexCouponPromo />
			<TopLoyaltyPointsEarner />
        </>
    );
};

export default index;
