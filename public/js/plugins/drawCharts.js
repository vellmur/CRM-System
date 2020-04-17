var barOptions = {
    width: '100%',
    left: 0,
    chartArea: {
        top: 10,
        bottom: 10
    },
    bar: {
        groupWidth: "70%"
    },
    legend: {
        position: "none"
    },

    seriesType: 'bars',
    hAxis: {
        textStyle: {
            fontName: "'Open Sans', sans-serif",
            fontSize: '12'
        }
    },
    vAxis: {
        textStyle: {
            fontName: "'Open Sans', sans-serif",
            fontSize: '12'
        }
    }
};

var pieOptions = {
    chartArea: {
        top: 20,
        bottom: 20
    },
    'legend':'bottom'
};

var maxHeight = 0;

function drawChart(position, stats, chartType)
{
    var data = new google.visualization.arrayToDataTable(stats);
    var height = data.getNumberOfRows() * 25 + 30;

    var graph = {
        'data': data
    };

    switch (chartType) {
        case 'horizontal':
            var chart = new google.visualization.BarChart(position);
            var options = barOptions;
            graph.labelsWidth = getWidthOfLabels(stats);

            break;
        case 'vertical':
            chart = new google.visualization.ColumnChart(position);
            options = barOptions;
            break;
        case 'pie':
            chart = new google.visualization.PieChart(position);
            options = pieOptions;
            break;
    }

    maxHeight = height > maxHeight ? height : maxHeight;

    graph.chart = chart;
    graph.options = options;

    return graph;
}
$.fn.textWidth = function(){
    var html_org = $(this).html();
    var html_calc = '<span>' + html_org + '</span>';
    $(this).html(html_calc);
    var width = $(this).find('span:first').width();
    $(this).html(html_org);
    return width;
};

/**
 * Left vAxis labels must be displayed with no wrap and in full text length.
 * Function helps to find needed width for vAxis with labels by longest label length.
 *
 * @param stats
 */
function getWidthOfLabels(stats)
{
    var labelsWidth = null;
    var longestLabel = '';

    // Find longest label (text in vAxis)
    for (var i = 0; i < stats.length; i++) {
        if (stats[i][0].length > longestLabel.length) {
            longestLabel = stats[i][0];
        }
    }

    // If longest label found
    if (longestLabel !== '') {
        var $body = $('body');

        // Create/get test div and append longest text inside, than find the width of the div with text
        var $testDiv = $body.find('#testWidth');

        if (!$testDiv.length) {
            $body.append('<div id="testWidth" style="position: absolute;visibility: hidden;height: auto;width: auto;white-space: nowrap;font-size: 12px !important;"></div>');
            $testDiv = $body.find('#testWidth');
        }

        $testDiv.append(longestLabel);
        labelsWidth = $testDiv.width() + 25;

        $testDiv.text('');
    }

    return labelsWidth;
}

/**
 * Get height of biggest chart
 *
 * @returns {number}
 */
function getMaxHeight()
{
    return maxHeight > 200 ? maxHeight : 200;
}

