import React from 'react';
import HexCouponPromo from '../../HexPromo/HexCouponPromo/HexCouponPromo';
import BarChartOne from '../../HexCharts/BarChart/BarChartOne';
import Quick_Links from "../../Quick Links/Quik_Links";
const Dashboard = () => {
    return (
        <>
			<Quick_Links />
			<HexCouponPromo />
            <BarChartOne />
        </>
    );
};

export default Dashboard;
