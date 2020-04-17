function chartInit(chart, chartData, assetPath) {
    // Set paths
    require.config({
        paths: {
            echarts: assetPath
        }
    });

    // Configuration
    require(
        ['echarts', 'echarts/theme/limitless', 'echarts/chart/bar'],

        // Charts setup
        function (ec, limitless) {
            var viewsChart = ec.init(document.getElementById('views_chart'), limitless);

            var options = {
                grid: {
                    x: 40,
                    x2: 40,
                    y: 35,
                    y2: 25
                },
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        var tooltipName = params[0].name;

                        // If tooltip name is number => name is day of month, so we add month name to the tooltip
                        if (Number.isInteger(tooltipName)) {
                            tooltipName = moment().format('MMMM') + ' ' + tooltipName;
                        }

                        var output = '<b>' + tooltipName + '</b><br/>';

                        for (var i = 0; i < params.length; i++) {
                            var value = params[0].seriesName === 'Revenue' ? '$' + parseFloat(params[i].value).toFixed(2) : params[i].value;

                            if (Number.isInteger(parseInt(params[i].seriesName))) {
                                params[i].seriesName = 'Views';
                            }

                            output += params[i].seriesName + ': ' + value;

                            if (i !== params.length - 1) { // Append a <br/> tag if not last in loop
                                output += '<br/>'
                            }
                        }

                        return output;
                    }
                },
                calculable: false,
                xAxis: [{
                    type: 'category',
                    data: {}
                }],
                yAxis: [{
                    type: 'value'
                }],
                series: [{
                    name: 'Page views',
                    type: 'bar',
                    data: {},
                    itemStyle: {
                        normal: {
                            label: {
                                show: true,
                                textStyle: {
                                    fontWeight: 500
                                },
                                formatter: function (val) {
                                    if (val.data === 0) {
                                        return '';
                                    }

                                    // Format value to show the price for the Revenue chart
                                    if (chart === 'revenue') {
                                        return '$' + parseFloat(val.data).toFixed(2);
                                    }

                                    return val.data;
                                }
                            }
                        }
                    }
                }]
            };

            function buildChart(item, period, time)
            {
                // For completed chart we show just completed step
                var data = (chart === 'completed' ? chartData['Completed'] : chartData[item]),
                    xAxis = [],
                    series = [],
                    monthsNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    now = moment(),
                    year = now.format('Y'),
                    $timeNavigation = $('#timeFilters').empty(),
                    i;

                if (!time) time = period === 'day' || period === 'month' ? now.format('MMM') : year;

                // Get xAxis and series data from given data array or create default xAxis with empty values
                switch (period) {
                    // Count views for the each day of the current month without total value
                    case 'day':
                        xAxis = new Array(now.daysInMonth()).join().split(',').map(function(item, index){ return ++index;});
                        series = data && data[year] && data[year][time] ? Object.values(data[year][time]) : new Array(now.daysInMonth()).fill(0);
                        series = series.slice(0, xAxis.length);

                        break;
                    // Count views for the each month of the current year
                    case 'month':
                        xAxis = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                        for (i = 0; i < xAxis.length; i++) {
                            value = (data && data[year] && data[year][xAxis[i]] ? data[year][xAxis[i]].total : 0);
                            series.push(value);
                        }

                        break;
                    // Count views for the each month of the selected year
                    case 'year':
                        // If data is empty, default chart data is previous month of current year and zeros
                        if (!data) {
                            for (i = 0; i < monthsNames.length; i++) {
                                if (i + 1 <= moment().format('M')) xAxis.push(monthsNames[i]);
                            }

                            series = new Array(xAxis.length).fill(0);
                            break;
                        }

                        // If data for selected year is empty, set year to last existed year with a data
                        if (!data[time]) time = Object.keys(data)[Object.keys(data).length - 1].toString();

                        xAxis = Object.keys(data[time]);

                        for (i = 0; i < xAxis.length; i++) {
                            series.push(data[time][xAxis[i]].total);
                        }

                        // Add additional navigation by years to the 4th level of chart nav
                        var years = Object.keys(data);

                        for (i = 0; i < years.length; i++) {
                            var navButton = '<li data-type="time" data-period="year"' +
                                (time === years[i] ? 'class="active"' : '') + '>' +
                                '<a data-value="' + years[i] + '" ' + 'class="chart-period-btn">' + years[i] + '</a></li>';

                            $timeNavigation.append(navButton);
                        }

                        break;
                    case 'last12':
                        var currentMonth = moment().format('M') - 1;

                        // Create months array, where last month is current and previous month is last 12 months
                        for (i = 0; i < 13; i++) {
                            var month = monthsNames[currentMonth],
                                byYear = now.format('M') < (12 - i) ? year - 1 : year,
                                value = (data && data[byYear] && data[byYear][month] ? data[byYear][month].total : 0);

                            xAxis.push(month);
                            series.push(value);

                            currentMonth++;

                            if (currentMonth > 11) currentMonth = 0;
                        }

                        break;
                }

                // Rebuild chart
                options.xAxis[0].data = xAxis;
                options.series[0].data = series;
                if (chart && chart[0]) options.series[0].name = chart[0].toUpperCase() + chart.substring(1);
                viewsChart.setOption(options, true);
            }

            /**
             * Re-build chart after each click on chart navigation buttons
             */
            $('body').on('click', '.chart-period-btn', function () {
                // Remove active class from navs of same class and highlight as active current nav
                $('li[data-type="' + $(this).parent().data('type') + '"]').removeClass('active');
                $(this).parent().addClass('active');

                // Get item, period, time values for historical chart
                var item = $('li[data-type="item"][class="active"]').find('a').data('value'),
                    period = $('li[data-type="period"][class="active"]').find('a').data('value'),
                    $time = $('li[data-type="time"][class="active"]'),
                    time = $time.data('period') === period ? $time.find('a').data('value').toString() : null;

                // Build chart with given data
                buildChart(item, period, time);
            });

            // Trigger click on button to build chart right after load
            $('li[data-type="period"][class="active"]').find('a').trigger('click');

            // Resize charts on window resize event
            window.onresize = function () {
                setTimeout(function () {
                    viewsChart.resize();
                }, 200);
            };
        }
    );
}

