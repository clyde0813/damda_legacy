/**
 * @fileoverview Predicate.
 * @author NHN Ent.
 *         FE Development Lab <dl_javascript@nhnent.com>
 */

'use strict';

var chartConst = require('../const');

/**
 * predicate.
 * @module predicate
 */
var predicate = {
    /**
     * Whether bar chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isBarChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_BAR;
    },

    /**
     * Whether column chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isColumnChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_COLUMN;
    },

    /**
     * Whether bar type chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isBarTypeChart: function(chartType) {
        return predicate.isBarChart(chartType) || predicate.isColumnChart(chartType);
    },

    /**
     * Whether combo chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isComboChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_COMBO;
    },

    /**
     * Whether pie and donut combo chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @param {Array.<string>} subChartTypes - types of chart
     * @returns {boolean}
     */
    isPieDonutComboChart: function(chartType, subChartTypes) {
        var isAllPieType = tui.util.all(subChartTypes, function(subChartType) {
            return predicate.isPieTypeChart(subChartType);
        });

        return predicate.isComboChart(chartType) && isAllPieType;
    },

    /**
     * Whether line chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isLineChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_LINE;
    },

    /**
     * Whether area chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isAreaChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_AREA;
    },

    /**
     * Whether line and area combo chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @param {Array.<string>} subChartTypes - types of chart
     * @returns {boolean}
     */
    isLineAreaComboChart: function(chartType, subChartTypes) {
        var isAllLineType = tui.util.all(subChartTypes || [], function(subChartType) {
            return predicate.isLineChart(subChartType) || predicate.isAreaChart(subChartType);
        });

        return predicate.isComboChart(chartType) && isAllLineType;
    },

    /**
     * Whether line type chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @param {Array.<string>} [subChartTypes] - types of chart
     * @returns {boolean}
     */
    isLineTypeChart: function(chartType, subChartTypes) {
        return predicate.isLineChart(chartType) || predicate.isAreaChart(chartType)
            || predicate.isLineAreaComboChart(chartType, subChartTypes);
    },

    /**
     * Whether bubble chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isBubbleChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_BUBBLE;
    },

    /**
     * Whether scatter chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isScatterChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_SCATTER;
    },

    /**
     * Whether heatmap chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isHeatmapChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_HEATMAP;
    },

    /**
     * Whether treemap chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isTreemapChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_TREEMAP;
    },

    /**
     * Whether box type chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isBoxTypeChart: function(chartType) {
        return predicate.isHeatmapChart(chartType) || predicate.isTreemapChart(chartType);
    },

    /**
     * Whether pie chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isPieChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_PIE;
    },

    /**
     * Whether donut chart or not.
     * @memberOf module:predicate
     * @param {string} chartType -chart type
     * @returns {boolean}
     */
    isDonutChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_DONUT;
    },

    /**
     * Whether pie type chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isPieTypeChart: function(chartType) {
        return predicate.isPieChart(chartType) || predicate.isDonutChart(chartType);
    },

    /**
     * Whether map chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isMapChart: function(chartType) {
        return chartType === chartConst.CHART_TYPE_MAP;
    },

    /**
     * Whether coordinate type chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isCoordinateTypeChart: function(chartType) {
        return predicate.isBubbleChart(chartType) || predicate.isScatterChart(chartType);
    },

    /**
     * Whether allow rendering for minus point in area of series.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    allowMinusPointRender: function(chartType) {
        return predicate.isLineTypeChart(chartType) || predicate.isCoordinateTypeChart(chartType) ||
            predicate.isBoxTypeChart(chartType);
    },

    /**
     * Whether mouse position chart or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isMousePositionChart: function(chartType) {
        return predicate.isPieTypeChart(chartType) || predicate.isMapChart(chartType)
            || predicate.isCoordinateTypeChart(chartType);
    },

    /**
     * Whether align of label is outer or not.
     * @memberOf module:predicate
     * @param {string} align - align of legend
     * @returns {boolean}
     */
    isLabelAlignOuter: function(align) {
        return align === chartConst.LABEL_ALIGN_OUTER;
    },

    /**
     * Whether show label or not.
     * @param {{showLabel: ?boolean, showLegend: ?boolean}} options - options
     * @returns {boolean}
     */
    isShowLabel: function(options) {
        return options.showLabel || options.showLegend;
    },

    /**
     * Whether show outer label or not.
     * @param {{showLabel: ?boolean, showLegend: ?boolean, labelAlign: string}} options - options
     * @returns {*|boolean}
     */
    isShowOuterLabel: function(options) {
        return predicate.isShowLabel(options) && predicate.isLabelAlignOuter(options.labelAlign);
    },

    /**
     * Whether align of legend is left or not.
     * @memberOf module:predicate
     * @param {string} align - align of legend
     * @returns {boolean}
     */
    isLegendAlignLeft: function(align) {
        return align === chartConst.LEGEND_ALIGN_LEFT;
    },

    /**
     * Whether align of legend is top or not.
     * @memberOf module:predicate
     * @param {string} align - align of legend
     * @returns {boolean}
     */
    isLegendAlignTop: function(align) {
        return align === chartConst.LEGEND_ALIGN_TOP;
    },

    /**
     * Whether align of legend is bottom or not.
     * @memberOf module:predicate
     * @param {string} align - align of legend
     * @returns {boolean}
     */
    isLegendAlignBottom: function(align) {
        return align === chartConst.LEGEND_ALIGN_BOTTOM;
    },

    /**
     * Whether horizontal legend align or not.
     * @memberOf module:predicate
     * @param {string} align - align of legend
     * @returns {boolean}
     */
    isHorizontalLegend: function(align) {
        return predicate.isLegendAlignTop(align) || predicate.isLegendAlignBottom(align);
    },

    /**
     * Whether has width for vertical type legend or not.
     * @param {{align: string, visible: boolean}} legendOption - option for legend component
     * @returns {boolean}
     */
    hasVerticalLegendWidth: function(legendOption) {
        legendOption = legendOption || {};

        return !predicate.isHorizontalLegend(legendOption.align) && legendOption.visible;
    },

    /**
     * Whether allowed stackType option or not.
     * @memberOf module:predicate
     * @param {string} chartType - type of chart
     * @returns {boolean}
     */
    isAllowedStackOption: function(chartType) {
        return predicate.isBarChart(chartType) || predicate.isColumnChart(chartType)
            || predicate.isAreaChart(chartType);
    },

    /**
     * Whether normal stack type or not.
     * @memberOf module:predicate
     * @param {boolean} stackType - stackType option
     * @returns {boolean}
     */
    isNormalStack: function(stackType) {
        return stackType === chartConst.NORMAL_STACK_TYPE;
    },

    /**
     * Whether percent stack type or not.
     * @memberOf module:predicate
     * @param {boolean} stackType - stackType option
     * @returns {boolean}
     */
    isPercentStack: function(stackType) {
        return stackType === chartConst.PERCENT_STACK_TYPE;
    },

    /**
     * Whether valid stackType option or not.
     * @memberOf module:predicate
     * @param {boolean} stackType - stackType option
     * @returns {boolean}
     */
    isValidStackOption: function(stackType) {
        return stackType && (predicate.isNormalStack(stackType) || predicate.isPercentStack(stackType));
    },

    /**
     * Whether allow range data or not.
     * @memberOf module:predicate
     * @param {string} chartType - chart type
     * @returns {boolean}
     */
    isAllowRangeData: function(chartType) {
        return predicate.isBarTypeChart(chartType) || predicate.isAreaChart(chartType);
    },

    /**
     * Whether align of yAxis is center or not.
     * @memberOf module:predicate
     * @param {boolean} hasRightYAxis - whether has right yAxis.
     * @param {string} alignOption - align option of yAxis.
     * @returns {boolean} whether - align center or not.
     */
    isYAxisAlignCenter: function(hasRightYAxis, alignOption) {
        return !hasRightYAxis && (alignOption === chartConst.YAXIS_ALIGN_CENTER);
    },

    /**
     * Whether minus limit or not.
     * @memberOf module:predicate
     * @param {{min: number, max: number}} limit - limit
     * @returns {boolean}
     */
    isMinusLimit: function(limit) {
        return limit.min <= 0 && limit.max <= 0;
    },

    /**
     * Whether auto tick interval or not.
     * @param {string} [tickInterval] - tick interval option
     * @returns {boolean}
     */
    isAutoTickInterval: function(tickInterval) {
        return tickInterval === chartConst.TICK_INTERVAL_AUTO;
    },

    /**
     * Whether valid label interval or not.
     * @param {number} [labelInterval] - label interval option
     * @param {string} [tickInterval] - tick interval option
     * @returns {*|boolean}
     */
    isValidLabelInterval: function(labelInterval, tickInterval) {
        return labelInterval && labelInterval > 1 && !tickInterval;
    }
};

module.exports = predicate;
