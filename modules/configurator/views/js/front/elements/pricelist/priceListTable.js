/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author DMConcept <support@dmconcept.fr>
 *  @copyright 2015 DMConcept
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

var CONFIGURATOR = CONFIGURATOR || {};
CONFIGURATOR.ELEMENTS = CONFIGURATOR.ELEMENTS || {};
CONFIGURATOR.ELEMENTS.PRICELIST = CONFIGURATOR.ELEMENTS.PRICELIST || {};

/**
 * Handles price list displayed as table
 * @param {type} step
 * @param {type} parent
 * @returns {undefined}
 */
CONFIGURATOR.ELEMENTS.PRICELIST.PriceListTable = function(step, parent) {

    var Super = Object.getPrototypeOf(this);

    /**
     * Targets are all cells in table
     */
    this.cellQuery = 'table tbody td.table-cell';

    /**
     * Keeps track of the selected cell
     */
    this.selectedCell;

    this.init = function(step, parent) {
        Super.init.call(this, step, parent);
        this.updateInternalState(step.substeps);
    };

    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);

        this.targetEvent = this.getHTMLElement().find(this.cellQuery);
    };

    this.clickOnCell = function(event) {
        var target = $(event.target);

        // clicked
        if (target.html().trim() === '') {
            return;
        }

        var oldSelected = this.selectedCell;
        var nextState;
        var currentState = this.state;
        var ope;
        if (target.hasClass('selected')) {
            nextState = this.STATE.INACTIVE;
            ope = this.createOperation(this.OPERATION_NAME.RESET_STEP);
            ope.step = this.getID();
        } else {
            nextState = this.STATE.ACTIVE;
            ope = this.createOperation(this.OPERATION_NAME.ADD_PRICE_LIST);
            ope.step = this.getID();

            ope.optionDim1 = target.attr('data-option-1');
            ope.optionDim2 = target.attr('data-option-2');

            ope.valueDim1 = target.attr('data-value-1');
            ope.valueDim2 = target.attr('data-value-2');

            this.selectedCell = target;
        }

        if (ope) {
            this.resetOperations();
            this.addOperations(ope);
        }
        this.syncHTMLState(nextState);
        var allowed = Super.notify.call(this, this.getID(), nextState);
        this.state = (allowed) ? nextState : currentState;

        if (!allowed) {
            this.selectedCell = oldSelected;
            this.syncHTMLState();
        }
    };

    this.syncHTMLState = function(state) {
        if (state) {
            this.state = state;
        }

        switch (this.state) {
            case this.STATE.ACTIVE:
                this.targetEvent.removeClass('selected');
                this.selectedCell.addClass('selected');
                break;
            case this.STATE.INACTIVE:
                this.targetEvent.removeClass('selected');
                break;
        }
        
    };

    this.updateInternalState = function(substeps) {
        var value1 = substeps[0].params.value;
        var value2 = substeps[1].params.value;

        this.selectedCell = this.targetEvent.filter('[data-value-1="'+ value1 +'"]');
        this.selectedCell = this.selectedCell.filter('[data-value-2="'+ value2 +'"]');
        if (this.selectedCell.length) {
            this.state = this.STATE.ACTIVE;
        } else {
            this.state = this.STATE.INACTIVE;
        }

        this.syncHTMLState();
    };

    this.mergeAndUpdate = function() {
        // do nothing in order to avoid console.log because
        // we cannot init children
    };

    this.update = function(data) {
        this.step = data;
        this.show();
        this.showErrors();
        this.updateInternalState(data.substeps);
    };

    this.bind = function() {
        this.targetEvent.bind('click', $.proxy(this.clickOnCell, this));
    };

    this.unbind = function() {
        this.targetEvent.unbind('click', $.proxy(this.clickOnCell, this));
    };

   if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('pricelist_table', CONFIGURATOR.ELEMENTS.PRICELIST.PriceListTable);
CONFIGURATOR.ELEMENTS.PRICELIST.PriceListTable.prototype = new CONFIGURATOR.ELEMENTS.PRICELIST.BasePricelist;
