/**
 * @fileoverview Axis Data Maker
 * @author NHN Ent.
 *         FE Development Lab <dl_javascript@nhnent.com>
 */

'use strict';

var chartConst = require('../const');
var predicate = require('../helpers/predicate');

/**
 * Axis data maker.
 * @module axisDataMaker
 */
var axisDataMaker = {
    /**
     * Makes labels by labelInterval option.
     * @param {Array.<string>} labels - labels
     * @param {number} labelInterval - label interval option
     * @param {number} [addedDataCount] - added data count
     * @returns {Array.<string>} labels
     * @private
     */
    _makeLabelsByIntervalOption: function(labels, labelInterval, addedDataCount) {
        addedDataCount = addedDataCount || 0;
        labels = tui.util.map(labels, function(label, index) {
            if (((index + addedDataCount) % labelInterval) !== 0) {
                label = chartConst.EMPTY_AXIS_LABEL;
            }

            return label;
        });

        return labels;
    },

    /**
     * Make data about label axis.
     * @memberOf module:axisDataMaker
     * @param {object} params parameters
     *      @param {Array.<string>} params.labels - chart labels
     *      @param {boolean} params.isVertical - whether vertical or not
     *      @param {boolean} params.aligned - whether align or not
     *      @param {?boolean} params.addedDataCount - added data count
     * @returns {{
     *      labels: Array.<string>,
     *      tickCount: number,
     *      validTickCount: number,
     *      isLabelAxis: boolean,
     *      isVertical: boolean
     * }} axis data
     */
    makeLabelAxisData: function(params) {
        var tickCount = params.labels.length;
        var options = params.options || {};
        var labels = params.labels;

        if (predicate.isValidLabelInterval(options.labelInterval, options.tickInterval)
                && params.labels.length > options.labelInterval) {
            labels = this._makeLabelsByIntervalOption(params.labels, options.labelInterval, params.addedDataCount);
        }

        if (!params.aligned) {
            tickCount += 1;
        }

        return {
            labels: labels,
            tickCount: tickCount,
            validTickCount: 0,
            isLabelAxis: true,
            options: options,
            isVertical: !!params.isVertical,
            isPositionRight: !!params.isPositionRight,
            aligned: !!params.aligned
        };
    },

    /**
     * Make data about value axis.
     * @memberOf module:axisDataMaker
     * @param {object} params parameters
     *      @param {AxisScaleMaker} params.axisScaleMaker chart values
     *      @param {boolean} params.isVertical whether vertical or not
     * @returns {{
     *      labels: Array.<string>,
     *      tickCount: number,
     *      validTickCount: number,
     *      isLabelAxis: boolean,
     *      limit: {min: number, max: number},
     *      isVertical: boolean
     * }} axis data
     */
    makeValueAxisData: function(params) {
        var axisScaleMaker = params.axisScaleMaker,
            rangeValues = axisScaleMaker.getFormattedScaleValues(),
            tickCount = rangeValues.length;

        return {
            labels: rangeValues,
            tickCount: tickCount,
            validTickCount: tickCount,
            limit: axisScaleMaker.getLimit(),
            step: axisScaleMaker.getStep(),
            options: params.options,
            isVertical: !!params.isVertical,
            isPositionRight: !!params.isPositionRight,
            aligned: !!params.aligned
        };
    },

    /**
     * Make adjusting tick interval information.
     * @param {number} beforeBlockCount - before block count
     * @param {number} seriesWidth - width of series area
     * @param {number} blockSize - block size
     * @returns {null | {blockCount: number, beforeRemainBlockCount: number, interval: number}}
     * @private
     */
    _makeAdjustingIntervalInfo: function(beforeBlockCount, seriesWidth, blockSize) {
        var newBlockCount = parseInt(seriesWidth / blockSize, 10);
        // interval : ????????? ????????? block(tick??? tick ????????? ??????) ????????? ???????????? ?????? block ???
        var interval = parseInt(beforeBlockCount / newBlockCount, 10);
        var intervalInfo = null;
        var remainCount;

        if (interval > 1) {
            // remainCount : ?????? block??? ??? ????????? block?????? ????????? ?????? ?????? block ???
            // | | | | | | | | | | | |  - ?????? block
            // |     |     |     |      - ?????? ????????? block
            //                   |*|*|  - ?????? ?????? block ???
            remainCount = beforeBlockCount - (interval * newBlockCount);

            if (remainCount >= interval) {
                newBlockCount += parseInt(remainCount / interval, 0);
                remainCount = remainCount % interval;
            }

            intervalInfo = {
                blockCount: newBlockCount,
                beforeRemainBlockCount: remainCount,
                interval: interval
            };
        }

        return intervalInfo;
    },

    /**
     * Make candidate for adjusting tick interval.
     * @param {number} beforeBlockCount - before block count
     * @param {number} seriesWidth - width of series area
     * @returns {Array.<{newBlockCount: number, remainBlockCount: number, interval: number}>}
     * @private
     */
    _makeCandidatesForAdjustingInterval: function(beforeBlockCount, seriesWidth) {
        var self = this;
        var blockSizeRange = tui.util.range(90, 121, 5); // [90, 95, 100, 105, 110, 115, 120]
        var candidates = tui.util.map(blockSizeRange, function(blockSize) {
            return self._makeAdjustingIntervalInfo(beforeBlockCount, seriesWidth, blockSize);
        });

        return tui.util.filter(candidates, function(info) {
            return !!info;
        });
    },

    /**
     * Calculate adjusting interval information for auto tick interval option.
     * @param {number} curBlockCount - current block count
     * @param {number} seriesWidth - series width
     * @returns {{newBlockCount: number, remainBlockCount: number, interval: number}}
     * @private
     */
    _calculateAdjustingIntervalInfo: function(curBlockCount, seriesWidth) {
        var candidates = this._makeCandidatesForAdjustingInterval(curBlockCount, seriesWidth);
        var intervalInfo = null;

        if (candidates.length) {
            intervalInfo = tui.util.min(candidates, function(candidate) {
                return candidate.blockCount;
            });
        }

        return intervalInfo;
    },

    /**
     * Make filtered labels by interval.
     * @param {Array.<string>} labels - labels
     * @param {number} startIndex - start index
     * @param {numbrer} interval - interval
     * @returns {Array.<string>}
     * @private
     */
    _makeFilteredLabelsByInterval: function(labels, startIndex, interval) {
        return tui.util.filter(labels.slice(startIndex), function(label, index) {
            return index % interval === 0;
        });
    },

    /**
     * Update label type axisData for auto tick interval option.
     * @param {object} axisData - axisData
     * @param {number} seriesWidth - series width
     * @param {number} [addedDataCount] - added data count
     */
    updateLabelAxisDataForAutoTickInterval: function(axisData, seriesWidth, addedDataCount) {
        var beforeBlockCount = axisData.tickCount - 1;
        var intervalInfo = this._calculateAdjustingIntervalInfo(beforeBlockCount, seriesWidth);
        var adjustingBlockCount, interval, beforeRemainBlockCount, startIndex;

        if (!intervalInfo) {
            return;
        }

        adjustingBlockCount = intervalInfo.blockCount;
        interval = intervalInfo.interval;
        beforeRemainBlockCount = intervalInfo.beforeRemainBlockCount;
        axisData.eventTickCount = axisData.tickCount;

        // startIndex??? ?????? block?????? ??? ???????????? ?????? ????????? tick ?????? ??? ???????????? ?????????
        // |     |     |     |*|*|*|    - * ????????? ?????? ?????? block ???
        // |*|*|O    |     |     |*|    - ?????? ????????? tick??? ?????? ?????? (O ????????? startIndex = 2)
        // |*|O    |     |     |*|*|    - tick??? ?????? ?????? ?????? ?????? : O ????????? startIndex = 1)
        startIndex = Math.round(beforeRemainBlockCount / 2) - (addedDataCount % interval);

        // startIndex??? 0?????? ?????? ?????? interval?????? ????????????
        if (startIndex < 0) {
            startIndex += interval;
        }

        axisData.labels = this._makeFilteredLabelsByInterval(axisData.labels, startIndex, interval);

        tui.util.extend(axisData, {
            startIndex: startIndex,
            tickCount: adjustingBlockCount + 1,
            positionRatio: (startIndex / beforeBlockCount),
            sizeRatio: 1 - (beforeRemainBlockCount / beforeBlockCount),
            lineWidth: seriesWidth + chartConst.OVERLAPPING_WIDTH,
            interval: interval
        });
    },

    /**
     * Update label type axisData for stacking dynamic data.
     * @param {object} axisData - axis data
     * @param {object} prevUpdatedData - previous updated axisData
     * @param {number} firstTickCount - calculated first tick count
     */
    updateLabelAxisDataForStackingDynamicData: function(axisData, prevUpdatedData, firstTickCount) {
        var interval = prevUpdatedData.interval;
        var startIndex = prevUpdatedData.startIndex;
        var beforeBlockCount = axisData.tickCount - 1;
        var newBlockCount = beforeBlockCount / interval;
        var firstBlockCount = firstTickCount ? firstTickCount - 1 : 0;
        var beforeRemainBlockCount;

        // ?????? ????????? block??? ?????? ????????? ????????? block ?????? ????????? ?????? ???????????? interval ????????? ????????? ??????
        if (firstBlockCount && ((firstBlockCount * 2) <= newBlockCount)) {
            interval *= 2;
        }

        axisData.labels = this._makeFilteredLabelsByInterval(axisData.labels, startIndex, interval);
        newBlockCount = axisData.labels.length - 1;
        beforeRemainBlockCount = beforeBlockCount - (interval * newBlockCount);

        tui.util.extend(axisData, {
            startIndex: startIndex,
            eventTickCount: axisData.tickCount,
            tickCount: axisData.labels.length,
            positionRatio: startIndex / beforeBlockCount,
            sizeRatio: 1 - (beforeRemainBlockCount / beforeBlockCount),
            lineWidth: prevUpdatedData.lineWidth,
            interval: interval
        });
    }
};

module.exports = axisDataMaker;
