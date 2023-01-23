import React from "react";
import { Chart } from "react-google-charts";

export const data = [
  ["Year", "Hoarding Applied", "Approved ", "Rejected"],
  ["2020", 1170, 460, 250],
  ["2021", 660, 1120, 300],
  ["2022", 1030, 540, 350],
];

export const options = {
  chart: {
    title: "Yearly statics",
    subtitle: "Yearly Applied , Approved , Rejected",
  },
};

export function BarGraphComponent() {
  return (
    <Chart
      chartType="Bar"
      width="100%"
      height="400px"
      data={data}
      options={options}
    />
  );
}
