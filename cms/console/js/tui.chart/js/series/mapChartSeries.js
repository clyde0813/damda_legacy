/**
 * @fileoverview Map chart series component.
 * @author NHN Ent.
 *         FE Development Lab <dl_javascript@nhnent.com>
 */

'use strict';

var Series = require('./series');
var dom = require('../helpers/domHandler');
var predicate = require('../helpers/predicate');
var renderUtil = require('../helpers/renderUtil');

var MapChartSeries = tui.util.defineClass(Series, /** @lends MapChartSeries.prototype */ {
    /**
     * Map chart series component.
     * @constructs MapChartSeries
     * @extends Series
     * @param {object} params parameters
     *      @param {object} params.options series options
     *      @param {object} params.theme series theme
     *      @param {MapChartDataProcessor} params.dataProcessor data processor for map chart
     */
    init: function(params) {
        /**
         * Base position.
         * @type {{left: number, top: number}}
         */
        this.basePosition = {
            left: 0,
            top: 0
        };

        /**
         * Zoom magnification.
         * @type {number}
         */
        this.zoomMagn = 1;

        /**
         * Map ratio.
         * @type {number}
         */
        this.mapRatio = 1;

        /**
         * Graph dimension.
         * @type {{}}
         */
        this.graphDimension = {};

        /**
         * Limit position.
         * @type {{}}
         */
        this.limitPosition = {};

        /**
         * Map model.
         * @type {MapChartMapModel}
         */
        this.mapModel = null;

        /**
         * Previous mouse position.
         * @type {?{left: number, top: number}}
         */
        this.prevPosition = null;


        /**
         * Previous moved index.
         * @type {?number}
         */
        this.prevMovedIndex = null;

        /**
         * Whether drag or not.
         * @type {boolean}
         */
        this.isDrag = false;

        /**
         * Start position.
         * @type {?{left: number, top: number}}
         */
        this.startPosition = null;

        Series.call(this, params);
    },

    /**
     * Set map ratio.
     * @private
     */
    _setMapRatio: function() {
        var seriesDimension = this.boundsMaker.getDimension('series'),
            mapDimension = this.mapModel.getMapDimension(),
            widthRatio = seriesDimension.width / mapDimension.width,
            heightRatio = seriesDimension.height / mapDimension.height;

        this.mapRatio = Math.min(widthRatio, heightRatio);
    },

    /**
     * Set graph dimension.
     * @private
     */
    _setGraphDimension: function() {
        var seriesDimension = this.boundsMaker.getDimension('series');

        this.graphDimension = {
            width: seriesDimension.width * this.zoomMagn,
            height: seriesDimension.height * this.zoomMagn
        };
    },

    /**
     * Render series component.
     * @param {object} data data for rendering
     * @returns {HTMLElement} series element
     */
    render: function(data) {
        var container;

        this.mapModel = data.mapModel;
        this._setMapRatio();

        container = Series.prototype.render.call(this, data);

        return container;
    },

    /**
     * Set limit position to move map.
     * @private
     */
    _setLimitPositionToMoveMap: function() {
        var seriesDimension = this.boundsMaker.getDimension('series'),
            graphDimension = this.graphDimension;

        this.limitPosition = {
            left: seriesDimension.width - graphDimension.width,
            top: seriesDimension.height - graphDimension.height
        };
    },

    /**
     * Render raphael graph.
     * @param {{width: number, height: number}} dimension dimension
     * @private
     * @override
     */
    _renderGraph: function() {
        if (!this.graphContainer) {
            this.graphContainer = dom.create('DIV', 'tui-chart-series-graph-area');
            this.seriesContainer.appendChild(this.graphContainer);
        }

        this._setGraphDimension();
        renderUtil.renderDimension(this.graphContainer, this.graphDimension);

        this._setLimitPositionToMoveMap();

        this.graphRenderer.render(this.graphContainer, {
            colorSpectrum: this.data.colorSpectrum,
            mapModel: this.mapModel,
            dimension: this.graphDimension,
            theme: this.theme
        });
    },

    /**
     * Render series label.
     * @param {HTMLElement} seriesLabelContainer series label area element
     * @private
     */
    _renderSeriesLabel: function(seriesLabelContainer) {
        var self = this,
            htmls = tui.util.map(this.mapModel.getLabelData(this.zoomMagn * this.mapRatio), function(datum, index) {
                var label = datum.name || datum.code,
                    left = datum.labelPosition.left - (renderUtil.getRenderedLabelWidth(label, self.theme.label) / 2),
                    top = datum.labelPosition.top - (renderUtil.getRenderedLabelHeight(label, self.theme.label) / 2);

                return self._makeSeriesLabelHtml({
                    left: left,
                    top: top
                }, datum.name, index);
            });
        seriesLabelContainer.innerHTML = htmls.join('');
    },

    /**
     * Render series area.
     * @param {HTMLElement} seriesContainer series area element
     * @param {object} data data for rendering
     * @param {function} funcRenderGraph function for graph rendering
     * @private
     */
    _renderSeriesArea: function(seriesContainer, data, funcRenderGraph) {
        Series.prototype._renderSeriesArea.call(this, seriesContainer, data, funcRenderGraph);

        if (predicate.isShowLabel(this.options) && !this.seriesLabelContainer) {
            this.graphContainer.appendChild(this.seriesLabelContainer);
        }
    },

    /**
     * Adjust map position.
     * @param {{left: number, top: number}} targetPosition target position
     * @returns {{left: number, top: number}} adjusted position
     * @private
     */
    _adjustMapPosition: function(targetPosition) {
        return {
            left: Math.max(Math.min(targetPosition.left, 0), this.limitPosition.left),
            top: Math.max(Math.min(targetPosition.top, 0), this.limitPosition.top)
        };
    },

    /**
     * Update base position for zoom.
     * @param {{width: number, height: number}} prevDimension previous dimension
     * @param {{left: number, top: number}} prevLimitPosition previous limit position
     * @param {number} changedRatio changed ratio
     * @private
     */
    _updateBasePositionForZoom: function(prevDimension, prevLimitPosition, changedRatio) {
        var prevBasePosition = this.basePosition,
            prevLeft = prevBasePosition.left - (prevLimitPosition.left / 2),
            prevTop = prevBasePosition.top - (prevLimitPosition.top / 2),
            newBasePosition = {
                left: (prevLeft * changedRatio) + (this.limitPosition.left / 2),
                top: (prevTop * changedRatio) + (this.limitPosition.top / 2)
            };

        this.basePosition = this._adjustMapPosition(newBasePosition);
    },

    /**
     * Zoom.
     * @param {number} changedRatio changed ratio
     * @private
     */
    _zoom: function(changedRatio) {
        var prevDimension = this.graphDimension,
            prevLimitPosition = this.limitPosition;

        this._setGraphDimension();
        renderUtil.renderDimension(this.graphContainer, this.graphDimension);
        this.graphRenderer.setSize(this.graphDimension);

        this._setLimitPositionToMoveMap();
        this._updateBasePositionForZoom(prevDimension, prevLimitPosition, changedRatio);
        renderUtil.renderPosition(this.graphContainer, this.basePosition);

        if (this.seriesLabelContainer) {
            this._renderSeriesLabel(this.seriesLabelContainer);
        }
    },

    /**
     * Update positions to resize.
     * @param {number} prevMapRatio previous ratio
     * @private
     */
    _updatePositionsToResize: function(prevMapRatio) {
        var changedRatio = this.mapRatio / prevMapRatio;

        this.basePosition.left *= changedRatio;
        this.basePosition.top *= changedRatio;

        this.limitPosition.left *= changedRatio;
        this.limitPosition.top *= changedRatio;
    },

    /**
     * Resize graph.
     * @private
     */
    _resizeGraph: function() {
        var prevRatio = this.mapRatio;

        this._setMapRatio();

        this._setGraphDimension();
        renderUtil.renderDimension(this.graphContainer, this.graphDimension);
        this.graphRenderer.setSize(this.graphDimension);

        this._updatePositionsForResizing(prevRatio);
        renderUtil.renderPosition(this.graphContainer, this.basePosition);

        if (this.seriesLabelContainer) {
            this._renderSeriesLabel(this.seriesLabelContainer);
        }
    },

    /**
     * On click series.
     * @param {{left: number, top: number}} position - mouse position
     */
    onClickSeries: function(position) {
        var foundIndex = this._executeGraphRenderer(position, 'findSectorIndex');

        if (!tui.util.isNull(foundIndex)) {
            this.userEvent.fire('selectSeries', {
                chartType: this.chartType,
                index: foundIndex,
                code: this.mapModel.getDatum(foundIndex).code
            });
        }
    },

    /**
     * Whether changed or not.
     * @param {?{left: number, top: number}} prevPosition previous position
     * @param {{left: number, top: number}} position position
     * @returns {boolean} result boolean
     * @private
     */
    _isChangedPosition: function(prevPosition, position) {
        return !prevPosition || prevPosition.left !== position.left || prevPosition.top !== position.top;
    },

    /**
     * Show wedge.
     * @param {number} index map data index
     * @private
     */
    _showWedge: function(index) {
        var datum = this.mapModel.getDatum(index);

        if (!tui.util.isUndefined(datum.ratio)) {
            this.fire('showWedge', datum.ratio);
        }
    },

    /**
     * Show tooltip
     * @param {number} index map data index
     * @param {{left: number, top: number}} mousePosition mouse position
     * @private
     */
    _showTooltip: function(index, mousePosition) {
        this.fire('showTooltip', {
            chartType: this.chartType,
            indexes: {
                index: index
            },
            mousePosition: mousePosition
        });
    },

    /**
     * Get series container bound.
     * @returns {{left: number, top: number}} container bound
     * @private
     */
    _getContainerBound: function() {
        if (!this.containerBound) {
            this.containerBound = this.seriesContainer.getBoundingClientRect();
        }

        return this.containerBound;
    },

    /**
     * On move series.
     * @param {{left: number, top: number}} position position
     */
    onMoveSeries: function(position) {
        var foundIndex = this._executeGraphRenderer(position, 'findSectorIndex'),
            containerBound;

        if (!tui.util.isNull(foundIndex)) {
            if (this.prevMovedIndex !== foundIndex) {
                if (!tui.util.isNull(this.prevMovedIndex)) {
                    this.graphRenderer.restoreColor(this.prevMovedIndex);
                    this.fire('hideWedge');
                    this.fire('hideTooltip');
                }

                this.graphRenderer.changeColor(foundIndex);
            }

            if (this._isChangedPosition(this.prevPosition, position)) {
                containerBound = this._getContainerBound();
                this._showTooltip(foundIndex, {
                    left: position.left - containerBound.left,
                    top: position.top - containerBound.top
                });
                this.prevMovedIndex = foundIndex;
            }

            this._showWedge(foundIndex);
        } else if (!tui.util.isNull(this.prevMovedIndex)) {
            this.graphRenderer.restoreColor(this.prevMovedIndex);
            this.fire('hideWedge');
            this.fire('hideTooltip');
            this.prevMovedIndex = null;
        }
        this.prevPosition = position;
    },

    /**
     * On drag start series.
     * @param {{left: number, top: number}} position position
     */
    onDragStartSeries: function(position) {
        this.startPosition = {
            left: position.left,
            top: position.top
        };
    },

    /**
     * Move position.
     * @param {{left: number, top: number}} startPosition start position
     * @param {{left: number, top: number}} endPosition end position
     * @private
     */
    _movePosition: function(startPosition, endPosition) {
        var movementPosition = this._adjustMapPosition({
            left: this.basePosition.left + (endPosition.left - startPosition.left),
            top: this.basePosition.top + (endPosition.top - startPosition.top)
        });

        renderUtil.renderPosition(this.graphContainer, movementPosition);

        this.basePosition = movementPosition;
    },

    /**
     * On drag series.
     * @param {{left: number, top: number}} position position
     */
    onDragSeries: function(position) {
        this._movePosition(this.startPosition, position);

        this.startPosition = position;

        if (!this.isDrag) {
            this.isDrag = true;
            this.fire('hideTooltip');
        }
    },

    /**
     * On drag end series.
     */
    onDragEndSeries: function() {
        this.isDrag = false;
    },

    /**
     * Move position for zoom.
     * @param {{left: number, top: number}} position mouse position
     * @param {number} changedRatio changed ratio
     * @private
     */
    _movePositionForZoom: function(position, changedRatio) {
        var seriesDimension = this.boundsMaker.getDimension('series'),
            containerBound = this._getContainerBound(),
            startPosition = {
                left: (seriesDimension.width / 2) + containerBound.left,
                top: (seriesDimension.height / 2) + containerBound.top
            },
            movementPosition = {
                left: position.left - startPosition.left,
                top: position.top - startPosition.top
            },
            endPosition;

        changedRatio = changedRatio > 1 ? -(changedRatio / 2) : changedRatio;

        endPosition = {
            left: startPosition.left + (movementPosition.left * changedRatio),
            top: startPosition.top + (movementPosition.top * changedRatio)
        };

        this._movePosition(startPosition, endPosition);
    },

    /**
     * On zoom.
     * @param {number} newMagn new zoom magnification
     * @param {?{left: number, top: number}} position mouse position
     */
    onZoom: function(newMagn, position) {
        var changedRatio = newMagn / this.zoomMagn;

        this.zoomMagn = newMagn;

        this._zoom(changedRatio);

        if (position) {
            this._movePositionForZoom(position, changedRatio);
        }

        this.userEvent.fire('zoom', newMagn);
    }
});

tui.util.CustomEvents.mixin(MapChartSeries);

module.exports = MapChartSeries;
