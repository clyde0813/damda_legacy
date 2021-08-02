'use strict';

var DEFAULT_COLOR = '#000000',
    DEFAULT_BACKGROUND = '',
    EMPTY = '',
    DEFAULT_AXIS = {
        tickColor: DEFAULT_COLOR,
        title: {
            fontSize: 12,
            fontFamily: EMPTY,
            color: DEFAULT_COLOR
        },
        label: {
            fontSize: 12,
            fontFamily: EMPTY,
            color: DEFAULT_COLOR
        }
    };

var defaultTheme = {
    chart: {
        background: DEFAULT_BACKGROUND,
        fontFamily: 'Verdana'
    },
    title: {
        fontSize: 18,
        fontFamily: EMPTY,
        color: DEFAULT_COLOR
    },
    yAxis: DEFAULT_AXIS,
    xAxis: DEFAULT_AXIS,
    plot: {
        lineColor: '#dddddd',
        background: '#ffffff'
    },
    series: {
        label: {
            fontSize: 11,
            fontFamily: EMPTY,
            color: DEFAULT_COLOR
        },
        colors: ['#008ae5','#00e571','#9e43f2','#f24391','#ffb833','#fe3737','#00e2e5','#ff5533','#945D00','#2536b7','#4BA600','#00aede','#b881ec','#ff79b5','#9db8d6','#41b7ab'],
        singleColors: [],
        borderColor: EMPTY,
        selectionColor: EMPTY,
        startColor: '#F4F4F4',
        endColor: '#345391',
        overColor: '#F0C952'
    },
    legend: {
        label: {
            fontSize: 12,
            fontFamily: EMPTY,
            color: DEFAULT_COLOR
        }
    },
    tooltip: {}
};

module.exports = defaultTheme;
