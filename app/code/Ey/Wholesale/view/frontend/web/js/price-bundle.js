/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox'
], function ($, _, mageTemplate, utils) {
    'use strict';

    var globalOptions = {
        optionConfig: null,
        productBundleSelector: 'input.bundle.option, select.bundle.option, textarea.bundle.option',
        qtyFieldSelector: 'input.qty',
        priceBoxSelector: '.price-box',
        optionHandlers: {},
        optionTemplate: '<%- data.label %>' +
        '<% if (data.finalPrice.value) { %>' +
        ' +<%- data.finalPrice.formatted %>' +
        '<% } %>',
        controlContainer: 'dd', // should be eliminated
        isFixedPrice: false,
        priceTemplate: '<span class="price"><%- data.formatted %></span>',
        priceFormat: {
            'decimalSymbol': '.',
            'groupLength': 3,
            'groupSymbol': ',',
            integerRequired: 1,
            pattern: '$%s',
            precision: 2,
            requiredPrecision: 2
        }
    };

    $.widget('mage.priceBundle', {
        options: globalOptions,

        /**
         * @private
         */
        _init: function initPriceBundle() {
            var form = this.element,
                options = $(form).find(this.options.productBundleSelector);

            options.trigger('change');
        },

        /**
         * @private
         */
        _create: function createPriceBundle() {
            var form = this.element,
                options = $(form).find(this.options.productBundleSelector),
                // priceBox = $(form).find(this.options.priceBoxSelector),
                qty = $(form).find(this.options.qtyFieldSelector);

            this._applyQtyFix();
            this._applyOptionNodeFix(options);

            options.on('change', this._onBundleOptionChanged.bind(this));
            qty.on('change', this._onQtyFieldChanged.bind(this));
        },

        /**
         * Apparently changes the "Subtotal" value when you change the qty boxes, I think.
         * @param optionConfig
         * @param changes
         * @private
         */
        _reloadPrice: function reloadPrice(optionConfig, changes) {
            var options = optionConfig.options,
                price_summary = 0,
                price = {},
                selected = [],
                priceFormat = this.options.priceFormat || {},
                priceTemplate = mageTemplate(this.options.priceTemplate);

            _.each(optionConfig.selected, function (val, key) {
                selected[key] = val[0];
            });
            _.each(options, function (obj, key) {
                _.each(obj.selections, function (selection, val_key) {
                    if(key in selected){
                        if(val_key == selected[key]){
                            price_summary += selection.prices.finalPrice.amount * selection.qty;
                        }
                    }
                });
            });
            price.final = price_summary;
            price.amount = price_summary;
            price.formatted = utils.formatPrice(price.final, priceFormat);
            $(this.element).find('[data-price-type="finalPrice"]')
                .html(priceTemplate({data: price}));
        },

        /**
         * Handle change on bundle option inputs
         * SUSPECT
         * @param {jQuery.Event} event
         * @private
         */
        _onBundleOptionChanged: function onBundleOptionChanged(event) {
            var changes,
                bundleOption = $(event.target),
                // priceBox = $(this.element).find(this.options.priceBoxSelector),
                handler = this.options.optionHandlers[bundleOption.data('role')];

            bundleOption.data('optionContainer', bundleOption.closest(this.options.controlContainer));
            // Comment line below for qty 0, qty selected 4 for some reason, and subtotal 0.
            bundleOption.data('qtyField', bundleOption.data('optionContainer').find(this.options.qtyFieldSelector));

            if (handler && handler instanceof Function) {
                changes = handler(bundleOption, window.optionConfig[this.element.data('product-id')], this);
            } else {
                // Comment line below for qty 0, qty selected 4 for some reason, and subtotal 0.
                changes = defaultGetOptionValue(bundleOption, window.optionConfig[this.element.data('product-id')]);
            }
            if (changes) {
                this._reloadPrice(window.optionConfig[this.element.data('product-id')], changes);
            }
            this.updateProductSummary();
        },

        /**
         * Handle change on qty inputs near bundle option
         * @param {jQuery.Event} event
         * @private
         */
        _onQtyFieldChanged: function onQtyFieldChanged(event) {
            var field = $(event.target),
                optionInstance,
                optionConfig;

            if (field.data('optionId') && field.data('optionValueId')) {
                optionInstance = field.data('option');
                optionConfig = window.optionConfig[this.element.data('product-id')]
                    .options[field.data('optionId')]
                    .selections[field.data('optionValueId')];
                optionConfig.qty = field.val();

                if(optionConfig.qty == 0){
                    $('#bundle-option-'+field.data('optionId')).prop("checked", true);
                } else{
                    $('#bundle-option-'+field.data('optionId')+'-'+field.data('optionValueId')).prop("checked", true);
                }

                optionInstance.trigger('change');
            }
        },

        /**
         * Helper to fix backend behavior:
         *  - if default qty large than 1 then backend multiply price in config
         *
         * @private
         */
        _applyQtyFix: function applyQtyFix() {
            var config = window.optionConfig[this.element.data('product-id')];
            if (config.isFixedPrice) {
                _.each(config.options, function (option) {
                    _.each(option.selections, function (item) {
                        if (item.qty && item.qty !== 1) {
                            _.each(item.prices, function (price) {
                                price.amount = price.amount / item.qty;
                            });
                        }
                    });
                });
            }
        },

        /**
         * Helper to fix issue with option nodes:
         *  - you can't place any html in option ->
         *    so you can't style it via CSS
         * @param {jQuery} options
         * @private
         */
        _applyOptionNodeFix: function applyOptionNodeFix(options) {
            var config = this.options,
                format = config.priceFormat,
                template = config.optionTemplate;
            template = mageTemplate(template);
            options.filter('select').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    // optionName = $element.prop('name'),
                    // optionType = $element.prop('type'),
                    optionConfig = config.optionConfig && config.optionConfig.options[optionId].selections;

                $element.find('option').each(function (idx, option) {
                    var $option,
                        optionValue,
                        toTemplate,
                        prices;

                    $option = $(option);
                    optionValue = $option.val();

                    if (!optionValue && optionValue !== 0) {
                        return;
                    }

                    toTemplate = {
                        data: {
                            label: optionConfig[optionValue] && optionConfig[optionValue].name
                        }
                    };
                    prices = optionConfig[optionValue].prices;

                    _.each(prices, function (price, type) {
                        var value = +(price.amount);
                        value += _.reduce(price.adjustments, function (sum, x) {
                            return sum + x;
                        }, 0);
                        toTemplate.data[type] = {
                            value: value,
                            formatted: utils.formatPrice(value, format)
                        };
                    });

                    $option.text(template(toTemplate));
                });
            });
        },

        /**
         * Custom behavior on getting options:
         * now widget able to deep merge accepted configuration with instance options.
         * @param  {Object}  options
         * @return {$.Widget}
         */
        _setOptions: function setOptions(options) {
            $.extend(true, this.options, options);

            this._super(options);

            return this;
        },

        /**
         * Handler to update productSummary box
         * Seems to have something to do with the Qty Selected: number, but Subtotal and individual Qty boxes still
         * update as normal.
         */
        updateProductSummary: function updateProductSummary() {
            this.element.trigger('updateProductSummary', {
                config: window.optionConfig[this.element.data('product-id')]
            });
        }
    });

    return $.mage.priceBundle;

    /**
     * Converts option value to priceBox object
     *
     * @param   {jQuery} element
     * @param   {Object} config
     * @returns {Object|null} - priceBox object with additional prices
     */
    function defaultGetOptionValue(element, config) {
        var changes = {},
            optionHash,
            tempChanges,
            qtyField,
            optionId = utils.findOptionId(element[0]),
            optionValue = element.val() || null,
            optionName = element.prop('name'),
            optionType = element.prop('type'),
            optionConfig = config.options[optionId].selections,
            optionQty = 0,
            canQtyCustomize = false,
            selectedIds = config.selected;

        switch (optionType) {
            case 'radio':

            case 'select-one':

                if (optionType === 'radio' && !element.is(':checked')) {
                    return null;
                }

                qtyField = element.data('qtyField');
                qtyField.data('option', element);

                if (optionValue) {
                    optionQty = optionConfig[optionValue].qty || 0;
                    canQtyCustomize = 1;
                    toggleQtyField(qtyField, optionQty, optionId, optionValue, canQtyCustomize);
                    tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig[optionValue]);
                    tempChanges = applyQty(tempChanges, optionQty);
                } else {
                    tempChanges = {};
                    toggleQtyField(qtyField, '0', optionId, optionValue, false);
                }
                optionHash = 'bundle-option-' + optionName;
                changes[optionHash] = tempChanges;
                selectedIds[optionId] = [optionValue];
                break;

            case 'select-multiple':
                optionValue = _.compact(optionValue);

                _.each(optionConfig, function (row, optionValueCode) {
                    optionHash = 'bundle-option-' + optionName + '##' + optionValueCode;
                    optionQty = row.qty || 0;
                    tempChanges = utils.deepClone(row.prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);
                    tempChanges = applyQty(tempChanges, optionQty);
                    changes[optionHash] = _.contains(optionValue, optionValueCode) ? tempChanges : {};
                });

                selectedIds[optionId] = optionValue || [];
                break;

            case 'checkbox':
                optionHash = 'bundle-option-' + optionName + '##' + optionValue;
                optionQty = optionConfig[optionValue].qty || 0;
                tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);
                tempChanges = applyQty(tempChanges, optionQty);
                changes[optionHash] = element.is(':checked') ? tempChanges : {};

                selectedIds[optionId] = selectedIds[optionId] || [];

                if (!_.contains(selectedIds[optionId], optionValue) && element.is(':checked')) {
                    selectedIds[optionId].push(optionValue);
                } else if (!element.is(':checked')) {
                    selectedIds[optionId] = _.without(selectedIds[optionId], optionValue);
                }
                break;

            case 'hidden':
                qtyField = element.data('qtyField');
                qtyField.data('option', element);

                if (optionValue) {
                    optionQty = optionConfig[optionValue].qty || 0;
                    canQtyCustomize = optionConfig[optionValue].customQty === '1';
                    toggleQtyField(qtyField, optionQty, optionId, optionValue, canQtyCustomize);
                    tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig[optionValue]);
                    tempChanges = applyQty(tempChanges, optionQty);
                } else {
                    tempChanges = {};
                    toggleQtyField(qtyField, '0', optionId, optionValue, false);
                }

                optionHash = 'bundle-option-' + optionName + '##' + optionValue;
                optionQty = optionConfig[optionValue].qty || 0;
                tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);
                tempChanges = applyQty(tempChanges, optionQty);

                optionHash = 'bundle-option-' + optionName;
                changes[optionHash] = tempChanges;
                selectedIds[optionId] = [optionValue];
                break;
        }

        return changes;
    }

    /**
     * Helper to toggle qty field
     * @param {jQuery} element
     * @param {String|Number} value
     * @param {String|Number} optionId
     * @param {String|Number} optionValueId
     * @param {Boolean} canEdit
     */
    function toggleQtyField(element, value, optionId, optionValueId, canEdit) {
        element
            .val(value)
            .data('optionId', optionId)
            .data('optionValueId', optionValueId)
            .attr('disabled', !canEdit);

        if (canEdit) {
            element.removeClass('qty-disabled');
        } else {
            element.addClass('qty-disabled');
        }
    }

    /**
     * Helper to multiply on qty
     *
     * @param   {Object} prices
     * @param   {Number} qty
     * @returns {Object}
     */
    function applyQty(prices, qty) {
        _.each(prices, function (everyPrice) {
            everyPrice.amount *= qty;
            _.each(everyPrice.adjustments, function (el, index) {
                everyPrice.adjustments[index] *= qty;
            });
        });

        return prices;
    }

    /**
     * Helper to limit price with tier price
     *
     * @param {Object} oneItemPrice
     * @param {Number} qty
     * @param {Object} optionConfig
     * @returns {Object}
     */
    function applyTierPrice(oneItemPrice, qty, optionConfig) {
        var tiers = optionConfig.tierPrice,
            magicKey = _.keys(oneItemPrice)[0],
            lowest = false;

        _.each(tiers, function (tier, index) {
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            if (tier.price_qty > qty) {
                return;
            }
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            if (tier.prices[magicKey].amount < oneItemPrice[magicKey].amount) {
                lowest = index;
            }
        });

        if (lowest !== false) {
            oneItemPrice = utils.deepClone(tiers[lowest].prices);
        }

        return oneItemPrice;
    }
});