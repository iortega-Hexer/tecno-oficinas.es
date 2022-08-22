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
 * Base element that representes a HTML element that can be manipulate by
 * user. baseElement is the root class of all elements. 
 * @param {type} step
 * @param {Object} parent     Object from the same hierarchie as baseElement
 *                              Should not be null as it is the only way for the object
 *                              to communicate
 * @returns {Object}          A new object
 */
CONFIGURATOR.ELEMENTS.BaseElement = function(step, parent) {

    /**
     * Available states for an elements.
     * The meaning behind state can change depending on the element's nature.
     */
    this.STATE = {
        ACTIVE: "ACTIVE",           // Element has left its default state
        INACTIVE: "INACTIVE",       // Element is in its default state
        UNKNOWN: "UNKNOWN",         // Unknown status, mostly used for debug purpose
        VISIBLE: "VISIBLE",         // Element is visible (user can see it)
        INVISIBLE: "INVISIBLE"      // Element is not visible (user cannot see it)
    };
    
    this.state = this.STATE.UNKNOWN;

    /**
     * Only between 'VISIBLE' and 'INVISIBLE'
     */
    this.visibleState = this.STATE.UNKNOWN;
    
    /**
     * Operations available on server's side
     * Will be used to fill the operation's action attribute
     */
    this.OPERATION_NAME = {
        ADD: 'add',
        ADD_PRICE_LIST: 'addPriceList',
        REMOVE: 'remove',
        RESET_STEP: 'resetStep',
        UPDATE_WANTED_QTY: 'updateWantedQty',
        ADD_YES_NO: 'addYesNo',
        REMOVE_YES_NO: 'removeYesNo'
    };

    /**
     * Will contain all operations that the element wants to send to the server
     */
    this.operations = [];

    /**
     * Root for this query is getHTMLElement
     * Then we look for this element in order to add errors
     */
    this.queryError = '.error-step';
    this.queryInfo = '.info-step';
    this.queryInfoText = '.info_text';

    /**
     * Give informations about an error on this step
     */
    this.class_step_error = 'error-on-this-step';
    this.class_step_info = 'info-on-this-step';
    
    /**
     * Used for tabs when this.show is loaded
     */
    this.configurator_tabs_id = '#configurator-tabs';
    this.step_group = 'step_group';
    
    /**
    * Concats arguments and separates them with '_'. With the
    * resulting ID, makes a Jquery research and returns the results
    * Example: 
    *      - getStep() -> return
    *      - getStep('arg1') -> $('#arg1')
    *      - getStep('arg1', 'arg2') -> $('#arg1_arg2')
    * @returns JQuery result's
    */
    this.getHTML = function() {
        if (arguments.length === 0) return;
        var query = '#';
        for (var i = 0; i < arguments.length-1; i++) {
            query += arguments[i] + '_';
        }
        query += arguments[arguments.length-1];
        return $(query);
    };

    /**
     * Returns the HTMLElement to which the current object is bound to
     * @returns JQuery results's
     */
    this.getHTMLElement = function() {
        return this.HTMLElement;
    };

    /**
     * Returns Element's ID.
     * @returns {number} Element's ID
     */
    this.getID = function() {
        return this.step.params.id;
    };
    
    /**
     * Returns Element's position.
     * The position corresponds to its apparition's order in HTML
     * @returns {number} Element's position
     */
    this.getPosition = function() {
        return this.step.params.position;
    };
    
    /**
     * Current state of element as defined in this.STATE object
     * @returns {String} Current state of element
     */
    this.getState = function() {
        return this.state;
    };

    /**
     * Current state of element as defined in this.STATE object
     * Correspons to its visiblity
     * @returns {String} Current state of element
     */
    this.getVisibleState = function() {
        return this.visibleState;
    };
    
    /**
     * Returns TRUE if element is both visible and active
     * FALSE otherwise
     * @param {type} element  Element to test, must be subclasses of baseElement
     * @returns {Boolean}
     */
    this.isVisibleAndActive = function(element) {
        return element.getState() === this.STATE.ACTIVE &&
               element.getVisibleState() === this.STATE.VISIBLE;
    };
    
    /**
     * Hides only the current element, do not call on children if any
     */
    this.hide = function() {
        this.visibleState = this.STATE.INVISIBLE;
        var elt = this.getHTMLElement();
        if (elt) {
            elt.attr('data-display', false);
            elt.hide();
        } else {
            console.log("WARNING: HTMLElement not found for " + this.step.params.element_type);
        }
    };
    
    /**
     * Hides the current HTML Element. If the current element has children,
     * hides them as well. An hidden element will stay hidden
     */
    this.hideAll = function() {
        this.hide();
    };
    
    /**
     * Hides only the current element, do not call on children if any
     */
    this.show = function() {
        this.visibleState = this.STATE.VISIBLE;
        var elt = this.getHTMLElement();
        
        var currentSelected = $(this.configurator_tabs_id+' li[data-selected=true]').data('block');
        
        if (elt) {
            elt.attr('data-display', true);
            if(elt.hasClass(this.step_group) && (elt.hasClass(currentSelected) || $(this.configurator_tabs_id + ' li').length === 0)){
                elt.show();
                elt.closest('.configurator-card').show();
            } else if(elt.hasClass(this.step_group) && !elt.hasClass(currentSelected)) {
                return;
            }
            
            elt.show();
        } else {
            console.log("WARNING: HTMLElement not found for " + this.step.params.element_type);
        }

        this.showInfos();
        this.showInfoText();
    };
    
    /**
     * Shows the current HTML Element. If the current element has children,
     * shows them as well.
     */
    this.showAll = function() {
        this.show();
    };
    
    /**
     * Current object binds itself to its HTML equivalent,
     * Asks children to do so as well.
     */
    this.bind = function() {
        //safeguards
        console.log("Bind method has not been implemented yet !");
    };

    /**
     * Current object unbinds itself to its HTML equivalent,
     * Asks children to do so as well.
     */
    this.unbind = function() {
        //safeguards
        console.log("Unbind method has not been implemented yet !");
    };
    
    /**
     * Returns element's operations
     * @return {array} operations
     */
    this.getOperations = function() {
        return this.operations;
    };
    
    /**
     * Given the operation's name, create if necessary the corresponding 
     * operation but do not add it to the operation's list
     * @param {OPERATION_NAME} operationName
     * @returns {Object} the corresponding operation if any
     */
    this.createOperation = function(operationName) {
        return {
            action: operationName,
            value: '',
            dimension: 1,
            step: 0,
            option: 0,
            dropzone_positions: []
        };
    };
    
    /**
     * Add the provided 'op' at the end of the operation's list
     * @param {Object} op     Operation to add
     */
    this.addOperations = function(op) {
        this.operations.push(op);
    };
    
    /**
     * Removes operations previously stored
     */
    this.resetOperations = function() {
        this.operations = [];
    };

    /**
     * Updates hierarchy. Given data parameter, set up the state of both
     * Javascript objects and HTML elements according to the new data. If
     * the current element has children, it should ask them to update themself
     * as well by providing data that are for them
     * @param {type} data same structure as 'step' parameter in init method
     */
    this.update = function(data) {
        this.step = data;
        
        // receiving data means we can be visible
        this.show();
        this.showErrors();
        this.showInfos();
        this.showInfoText();
    };

    /**
     * After reset, display all errors contained in this.step.errors
     */
    this.showErrors = function() {
        this.resetErrors();
        var self = this;

        this.step.errors.forEach(function(error) {
            // if error is 'string', means that the error can be found in
            // ERRORS.dispatch object
            if (typeof error === 'string') {
                error = CONFIGURATOR.ERRORS.dispatch.getAssociatedObject(error);
                if (typeof error === 'string') {
                    console.log("Couldn't find associated error");
                    console.log(error);
                    return;
                }
            }
            self.appendError(error);
        });
    };

    /**
     * Empty all errors previously displayed by appendError method
     */
    this.resetErrors = function() {
        this.getHTMLElement().find(this.queryError).empty();
        this.getHTMLElement().removeClass(this.class_step_error);
    };

    /**
     * Error contains type, title and message
     * For now we are only handling message
     * @param {Object} error
     * @returns {undefined}
     */
    this.appendError = function(error) {
        var err = $('<p></p>', {html: '<span class="configurator_step_error"></span>'+error.message});
        this.getHTMLElement().find(this.queryError).append(err);
        this.getHTMLElement().addClass(this.class_step_error);
    };

    this.showInfos = function() {
        this.resetInfos();
        var self = this;

        if (typeof this.step.infos !== 'undefined') {
            this.step.infos.forEach(function (info) {
                self.appendInfo(info);
            });
        }
    };

    this.resetInfos = function() {
        var htmlElement = this.getHTMLElement();
        if (typeof htmlElement !== 'undefined') {
            this.getHTMLElement().find(this.queryInfo).empty();
            this.getHTMLElement().removeClass(this.class_step_info);
        }
    };

    this.appendInfo = function(info) {
        var htmlElement = this.getHTMLElement();
        if (typeof htmlElement !== 'undefined') {
            var inf = $('<p></p>', {html: '<span class="configurator_step_info"></span>'+info});
            this.getHTMLElement().find(this.queryInfo).append(inf);
            this.getHTMLElement().addClass(this.class_step_info);
        }
    };
    
    this.showInfoText = function() {
        this.resetInfoText();
        if (typeof this.step.infosText !== 'undefined') {
            this.appendInfoText(this.step.infosText);
        } else if (typeof configuratorInfoText !== 'undefined' && configuratorInfoText[this.step.params.id]) {
            this.appendInfoText(configuratorInfoText[this.step.params.id]);
        }
    };

    this.resetInfoText = function() {
        var htmlElement = this.getHTMLElement();
        if (typeof htmlElement !== 'undefined') {
            this.getHTMLElement().find(this.queryInfoText).empty();
        }
    };

    this.appendInfoText = function(infoText) {
        var htmlElement = this.getHTMLElement();
        if (typeof htmlElement !== 'undefined') {
            this.getHTMLElement().find(this.queryInfoText).append(infoText);
        }
    };

    /**
     * Ask for update of a designated module 'data.element_type' with 'data'
     * Default behavior is to follow up the update.
     * WARNING : If this method is not overrided by at least the root element,
     *          it will make an infinite loop
     * @param {array} data       List of actions to perform on modules
     *                           value is data to send to the module trought 'handle' method
     * @returns {undefined}
     */
    this.updateModules = function(data) {
        this.parent.updateModules(data);
    };

    /**
     * Send actions contained in params.actions on if must be
     */
    this.sendActions = function() {
        // send data only if visible and active
        if (this.isVisibleAndActive(this)) {
            this.updateModules(this.step.actions);
        }
    };

    /**
     * 
     * @param {Object} step contains data from server
     * @param {Object} parent   Element's parent, all element must have a parent
     *                          of some sorts, as it is the only way to follow up
     *                          events and actions
     */
    this.init = function(step, parent){
        this.step = step;
        this.parent = parent;
        
        //
        // Fix operation's explosion
        // this.operations = [] is shared by all subclasses otherwise
        //
        this.resetOperations();
        this.show();
    };
    
    /**
     * Init variables that are the result of a JQuery select
     * HTMLElement should be init after a call to this method
     * @param step 
     * @param parent
     */
    this.initDOMLinks = function(step, parent) {
        var selector = '[data-position="'+ step.params.position +'"]';
        var root = parent.getHTMLElement();
        this.HTMLElement = root.filter(selector).add(root.find(selector));
    };

    // init only when step is provided, avoid unecessary initialisation
    // when object's hierarchy is made (prototype update)
    if (step) {
        this.init(step, parent);  
    }
};