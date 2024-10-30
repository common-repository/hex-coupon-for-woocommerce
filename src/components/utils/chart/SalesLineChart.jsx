/* eslint-disable react/prop-types */

import { Chart as ChartJS, CategoryScale, Filler,  /*//x axis*/ LinearScale, /*//y axis*/ PointElement, LineElement, Title, Tooltip, Legend, } from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(LineElement, CategoryScale, LinearScale, PointElement, Title, Tooltip, Legend, Filler)

const LineChart = ({ className }) => {
    // Css root color find for chart js
    const mainColorOne = getComputedStyle(document.documentElement).getPropertyValue('--main-color-one').trim();
    // Add Opacity for Diclare Color
    const mainColorOneRgb = (color, opacity) => {
        const hexToRgb = (hex) => hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, (m, r, g, b) => '#' + r + r + g + g + b + b).substring(1).match(/.{2}/g).map(x => parseInt(x, 16));
        const [r, g, b] = hexToRgb(color);
        return `rgba(${r},${g},${b},${opacity})`;
    };

    const data = {
        type: 'line',
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [
            {
                label: 'Sales',
                data: [1, 30, 20, 40, 30, 35, 10],
                fill: true,
                borderColor: mainColorOne,
                backgroundColor: mainColorOneRgb(mainColorOne, 0.1),
                borderWidth: 2,
                pointBorderWidth: 4,
                lineTension: .4,
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
                text: 'React Line Chart',
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
            <Line data={data} options={options} />
        </div>
    )
};

export default LineChart;
