/* eslint-disable react/prop-types */

import '../../../assets/sass/component/chart_style.scss'

import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend);


const PieChart = ({ className }) => {
    //css root color find for chart js
    const mainColorOne = getComputedStyle(document.documentElement).getPropertyValue('--main-color-one').trim();
    const mainColorTwo = getComputedStyle(document.documentElement).getPropertyValue('--main-color-two').trim();
    const mainColorThree = getComputedStyle(document.documentElement).getPropertyValue('--main-color-three').trim();
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim();

    const data = {
        labels: ['Marketing', 'Returns', 'Net Profit', 'Taxes', 'Transaction', 'Experience'],
        datasets: [
            {
                label: 'Sales',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: [
                    mainColorOne,
                    mainColorTwo,
                    mainColorThree,
                    successColor,
                    mainColorOne,
                    mainColorTwo,
                ],
                borderColor: [
                    mainColorOne,
                    mainColorTwo,
                    mainColorThree,
                    successColor,
                    mainColorOne,
                    mainColorTwo,
                ],
                borderWidth: 1,
            },
        ],
    };
    const options = {
        tooltips: {
            enabled: true,
            mode: 'index',
            position: 'nearest',
            intersect: false,
            custom: function (tooltip) {
                tooltip.options.zIndex = 1000;
            }
        },
        plugins: {
            legend: false,
            title: {
                display: false,
                text: 'React Bar Chart',
            },
        },
    }

    return (
        <div className={`dashboard__chart ${className ?? ''}`}>
            <Pie data={data} options={options} />
        </div>
    );
};

export default PieChart;