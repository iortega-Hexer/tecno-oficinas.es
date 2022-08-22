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

/**
 * Handles 'display by yes no' option
 * 'Yes' and 'No' inputs throw both notify to parent
 * Indeed, we have to do so because otherwise we wouldn't be able to store
 * previous data and play them back when clicking on 'yes' again.
 * So, we have the following behaviour:
 * - Init: 'No' is checked
 * - Click on 'Yes': 'Yes' is checked and operations are sent (from children)
 *                  in order to give all information
 * - Click on 'No': 'No' is checked and children are hidden, 'resetStep' is sent
 *                  to server
 * - update: When 'yes' is selected, updates are sent to children
 *           When 'no' is selected, updates are not sent to childre in order to
 *           keep previous data
 * @param {type} step
 * @returns {undefined}
 * @param {type} parent
 */
CONFIGURATOR.ELEMENTS.YesNo = function(step, parent) {

    var Super = Object.getPrototypeOf(this);

    /**
     * Query to find 'yes' option (which would display child)
     */
    this.yesQuery = '[id^=yes_radio]';

    /**
     * Query to find 'no' option (which would hide child)
     */
    this.noQuery = '[id^=no_radio]';

    this.yesNoInputQuery =  this.yesQuery + ', '+ this.noQuery;

    /**
     * TRUE: 'yes' is selected
     * FALSE: 'no' is selected
     * Default to FALSE as a yes_no step is not displayed by default
     */
    this.hasSelectedYes = false;

    /**
     * Action when user clicking on 'yes' or 'no' in order to display a step
     * @param {type} event
     * @returns {undefined}
     */
    this.actionDisplayStep = function(event) {
        if (this.targetYes.is(event.target)) {
            if (!this.hasSelectedYes) {
                var op = this.createOperation(this.OPERATION_NAME.ADD_YES_NO);
                op.step = this.step.params.id;
                this.changeState(true, this.STATE.ACTIVE, op);
            }
        } else if (this.targetNo.is(event.target)) {
            if (this.hasSelectedYes) {
                var op = this.createOperation(this.OPERATION_NAME.REMOVE_YES_NO);
                op.step = this.step.params.id;
                this.changeState(false, this.STATE.INACTIVE, op);
            }
        } else {
            console.log("Unknown option yes no !");
        }
    };

    /**
     *
     * @param {type} hasSelected    either 'yes' or 'no' has been selected
     * @param {type} state          'next state', the one send to notify
     * @param {type} op             Operation to set if needed
     * @returns {undefined}
     */
    this.changeState = function(hasSelected, state, op) {
        this.hasSelectedYes = hasSelected;
        this.updateChildVisibility();

        var oldOperations = this.operations;
        if (op) {
            this.resetOperations();
            this.addOperations(op);
        }

        var allowed = this.parent.notify(this.getID(), state);

        // initial state restored if we weren't allowed to update
        if (!allowed) {
            this.operations = oldOperations;
            this.hasSelectedYes = !hasSelected;
            this.updateChildVisibility();
        }
    };

    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        this.targetEvent = this.getHTMLElement().find(this.yesNoInputQuery);
        this.targetYes = this.targetEvent.filter(this.yesQuery);
        this.targetNo = this.targetEvent.filter(this.noQuery);
    };

    this.bind = function() {
        Super.bind.call(this);
        this.targetEvent.bind('click', $.proxy(this.actionDisplayStep, this));
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.targetEvent.unbind('click', $.proxy(this.actionDisplayStep, this));
    };

    this.updateChildVisibility = function() {
        if (this.hasSelectedYes) {
            this.targetYes.prop('checked', true);
            this.substeps.forEach(function(substep){
                substep.show();
            });
        } else {
            this.targetNo.prop('checked', true);
            this.substeps.forEach(function(substep){
                substep.hide();
            });
        }

        // update CSS through uniform
        $.uniform.update(this.targetEvent);
    };

    this.updateState = function() {
        var isInitProcess = this.state === this.STATE.UNKNOWN;
        Super.updateState.call(this);

        if (isInitProcess) {
            this.hasSelectedYes = (this.state === this.STATE.ACTIVE || this.step.params.yes_no_value);
        }

        this.updateChildVisibility();
    };

    this.getOperations = function() {
        var op = [];
        if (this.hasSelectedYes) {
            op = Super.getOperations.call(this);
        } else {
            op = this.operations;
        }

        return op;
    };

    this.update = function(data) {
        // receiving data means we can be visible
        this.show();
        this.step = data;
        this.showErrors();

        if (this.hasSelectedYes) {
            this.mergeAndUpdate(data.substeps);
        }

        this.updateState();
    };

    this.getState = function() {
        return this.STATE.ACTIVE;
    };

    if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('yes_no', CONFIGURATOR.ELEMENTS.YesNo);
CONFIGURATOR.ELEMENTS.YesNo.prototype = new CONFIGURATOR.ELEMENTS.BaseGroupElement;