/*
 Template Name: Stexo - Responsive Bootstrap 4 Admin Dashboard
 Author: Themesdesign
 Website: www.themesdesign.in
 File: Chart js 
 */

!function ($) {
    "use strict";

    var ChartJs = function () {};

    ChartJs.prototype.respChart = function (selector, type, data, options) {
        // get selector by context
        var ctx = selector.get(0).getContext("2d");
        // pointing parent container to make chart js inherit its width
        var container = $(selector).parent();

        // enable resizing matter
        $(window).resize(generateChart);

        // this function produce the responsive Chart JS
        function generateChart() {
            // make chart width fit with its container
            var ww = selector.attr('width', $(container).width());
            switch (type) {
                case 'Line':
                    new Chart(ctx, {type: 'line', data: data, options: options});
                    break;
                case 'Doughnut':
                    new Chart(ctx, {type: 'doughnut', data: data, options: options});
                    break;
                case 'Pie':
                    new Chart(ctx, {type: 'pie', data: data, options: options});
                    break;
                case 'Bar':
                    new Chart(ctx, {type: 'bar', data: data, options: options});
                    break;
                case 'Radar':
                    new Chart(ctx, {type: 'radar', data: data, options: options});
                    break;
                case 'PolarArea':
                    new Chart(ctx, {data: data, type: 'polarArea', options: options});
                    break;
            }
            // Initiate new chart or Redraw

        }
        ;
        // run function - render chart at first load
        generateChart();
    },
    //init
    ChartJs.prototype.init = function (data) {
        //creating lineChart

        var lineChart = {
            labels: data['list'],
            datasets: [
                {
                    label: "月消毒实际工作量",
                    fill: true,
                    lineTension: 0.5,
                    backgroundColor: "rgba(48, 65, 155, 0.2)",
                    borderColor: "#30419b",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "#30419b",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "#30419b",
                    pointHoverBorderColor: "#30419b",
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data: data['cleancount']
                },
                {
                    label: "月消毒计划工作量",
                    fill: true,
                    lineTension: 0.5,
                    backgroundColor: "rgba(240, 244, 247, 0.2)",
                    borderColor: "#f0f4f7",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "#f0f4f7",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "#f0f4f7",
                    pointHoverBorderColor: "#eef0f2",
                    pointHoverBorderWidth: 2,
                    pointRadius: 1,
                    pointHitRadius: 10,
                    data:  data['plancount']
                }
            ]
        };
        //   });

        var lineOpts = {
            scales: {
                yAxes: [{
                        ticks: {
                            max: 100,
                            min: 20,
                            stepSize: 10
                        }
                    }]
            }
        };


        this.respChart($("#lineChart"), 'Line', lineChart, lineOpts);
    },
   
    $.ChartJs = new ChartJs, $.ChartJs.Constructor = ChartJs()

}(window.jQuery),
//initializing
        function ($) {
            "use strict";
              
           $.get('reportcenterapi', function (data, status) {
                //console.log(data);
                 $.ChartJs.init(data);
            });
 
        }(window.jQuery);
