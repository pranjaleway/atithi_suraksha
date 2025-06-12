/**
 *  Logistics Dashboard
 */

'use strict';

(function () {
  let  headingColor, currentTheme, bodyColor;

  // Customer Application Chart
  // --------------------------------------------------------------------
  const customerApplicationChartE1 = document.querySelector('#customerApplicationChart');

const customerApplicationChartConfig = {
  chart: {
    height: 420,
    parentHeightOffset: 0,
    type: 'donut'
  },
  labels: ['Approved', 'Pending', 'Rejected'],
  series: [
    customerStatusData.approved,
    customerStatusData.pending,
    customerStatusData.rejected
  ],
  colors: [
    'rgb(41, 218, 199)', // for approved
    'rgb(115, 103, 240)', // for pending
    'rgb(255, 161, 161)'  // for rejected
  ],
  stroke: { width: 0 },
  dataLabels: {
    enabled: false,
    formatter: function (val) {
      return parseInt(val) + '%';
    }
  },
  legend: {
    show: true,
    position: 'bottom',
    offsetY: 10,
    markers: { width: 8, height: 8, offsetX: -3 },
    itemMargin: { horizontal: 15, vertical: 5 },
    fontSize: '13px',
    fontFamily: 'Inter',
    fontWeight: 400,
    labels: {
      colors: headingColor,
      useSeriesColors: false
    }
  },
  tooltip: {
    theme: 'dark'
  },
  grid: {
    padding: { top: 15 }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '75%',
        labels: {
          show: true,
          value: {
            fontSize: '26px',
            fontFamily: 'Inter',
            color: headingColor,
            fontWeight: 500,
            offsetY: -30,
            formatter: function (val) {
              return parseInt(val) + '%';
            }
          },
          name: {
            offsetY: 20,
            fontFamily: 'Inter'
          },
          total: {
            show: true,
            fontSize: '0.9rem',
            label: 'Total',
            color: bodyColor,
            formatter: function () {
              return (
                customerStatusData.approved +
                customerStatusData.pending +
                customerStatusData.rejected
              );
            }
          }
        }
      }
    }
  },
  responsive: [
    {
      breakpoint: 420,
      options: {
        chart: {
          height: 360
        }
      }
    }
  ]
};

if (customerApplicationChartE1 !== null) {
  const customerApplicationChart = new ApexCharts(customerApplicationChartE1, customerApplicationChartConfig);
  customerApplicationChart.render();
}



// Monthly Customer Chart
// --------------------------------------------------------------------

const monthlyCustomerChart = new ApexCharts(document.querySelector("#monthlyCustomerChart"), {
    chart: {
      type: 'bar',
      height: 350
    },
    series: [
      {
        name: 'Total Customer Application',
        data: monthlyCustomerData.total_received
      },
      {
        name: 'Approved',
        data: monthlyCustomerData.approved
      }
    ],
    colors: ['rgb(130, 106, 249)', 'rgb(210, 176, 255)'],
    xaxis: {
      categories: monthlyCustomerData.labels
    },
    dataLabels: {
      enabled: false
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '10%',
        endingShape: 'rounded'
      }
    },
    legend: {
      position: 'top'
    },
    tooltip: {
      theme: 'light'
    }
  });

  if(monthlyCustomerChart !== null){
    monthlyCustomerChart.render();
  }

})();


