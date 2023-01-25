import React from "react";
import { Chart } from "react-google-charts";

export const data = [
    ["Task", " Per Day"],
    ["Apply", 11],
    ["Renew", 8],
    // CSS-style declaration
];

export const options = {
    title: "My Daily Activities",
    pieHole: 0.4,
    is3D: true,
};

export function PieChartComponent() {
    return (
        <Chart
            chartType="PieChart"
            width="100%"
            height="500px"
            opacity="50%"
            data={data}
            options={options}
        />
    );
}
