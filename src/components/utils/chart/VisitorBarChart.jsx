/* eslint-disable react/prop-types */

import {
    Chart as ChartJS,
    LinearScale,
    CategoryScale,
    BarElement,
    PointElement,
    LineElement,
    Legend,
    Tooltip,
} from 'chart.js';

import { Bar } from 'react-chartjs-2';


ChartJS.register(
    LinearScale,
    CategoryScale,
    BarElement,
    PointElement,
    LineElement,
    Legend,
    Tooltip
);

const BarChart = ({ className }) => {
    //css root color find for chart js
    const mainColorOne = getComputedStyle(document.documentElement).getPropertyValue('--main-color-one').trim();

    const data = {
        type: 'bar',
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [
            {
                label: 'Visitors',
                data: [1, 19, 25, 22, 12, 23, 15],
                fill: true,
                borderColor: mainColorOne,
                backgroundColor: mainColorOne,
                borderWidth: 2,
                barThickness: 12,
                borderRadius: 10,
            },

        ],
    };

    const options = {
        responsive: true,
        interaction: {
            intersect: false,
        },
        stacked: false,
        plugins: {
            legend: true,
            title: {
                display: false,
                text: 'React Bar Chart',
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

    return (
        <div className={`dashboard__chart ${className ?? ''}`}>
            <Bar data={data} options={options} />
        </div>
    )
};

export default BarChart;
