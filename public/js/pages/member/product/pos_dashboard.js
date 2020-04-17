function chartInit(chart, data, currency, assetPath) {
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
            var viewsChart = ec.init(chart, limitless);

            var options = {
                grid: {
                    x: 40,
                    x2: 0,
                    y: 35,
                    y2: 25
                },
                tooltip: {
                    trigger: 'axis',
                    formatter: function (params) {
                        var output = '<b>' + params[0].name + '</b><br/>';

                        for (var i = 0; i < params.length; i++) {
                            if (currency !== undefined) {
                                output += params[i].value > 0 ? currency + params[i].value : currency + 0;
                            } else {
                                output += params[i].value;
                            }

                            if (i !== params.length - 1) {
                                output += '<br/>'
                            }
                        }

                        return output;
                    }
                },
                calculable: false,
                xAxis: [{
                    type: 'category',
                    data: {},
                    color: '#144072'
                }],
                yAxis: [{
                    type: 'value'
                }],
                series: [{
                    name: $(chart).data('name'),
                    type: 'bar',
                    data: {},
                    itemStyle: {
                        normal: {
                            label: {
                                show: !isMobile(),
                                textStyle: {
                                    fontWeight: 500,
                                    fontSize: '12px'
                                },
                                formatter: function(data) {
                                    if (currency && data.value !== '') {
                                        var val = parseFloat(data.value);
                                        return currency + val.toFixed(0);
                                    }

                                    return data.value;
                                }
                            },
                            color: '#144072'
                        }
                    }
                }]
            };

            function buildChart(data)
            {
                var xAxis = Object.keys(data),
                    series = Object.values(data);

                if (xAxis.length && xAxis[0].split(' ').length > 1) {
                    for (var i = 0; i < xAxis.length; i++) {
                        xAxis[i] = xAxis[i].split(' ')[0];
                    }
                }

                // Rebuild chart
                series = series.slice(0, xAxis.length);
                options.xAxis[0].data = xAxis;
                options.series[0].data = series;
                viewsChart.setOption(options, true);
            }

            // Build chart with given data
            buildChart(data);

            // Resize charts on window resize event
            window.onresize = function () {
                setTimeout(function () {

                    $(".chart").each(function(){
                        ec.getInstanceById($(this).attr('_echarts_instance_')).resize();
                    });
                }, 200);
            };
        }
    );
}

function HtmlDecode(s) {
    return $('<div>').html(s).text();
}