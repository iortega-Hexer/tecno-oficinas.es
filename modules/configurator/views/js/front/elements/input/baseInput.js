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
CONFIGURATOR.ELEMENTS.INPUT = CONFIGURATOR.ELEMENTS.INPUT || {};

/**
 * Abstract class that handles input element
 * @param {type} step
 * @param {type} parent
 * @returns {undefined}
 */
CONFIGURATOR.ELEMENTS.INPUT.BaseInput = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);
    
    /**
     * Value to which the input must be equal if INACTIVE
     */
    this.valueInactive = 'NOT DEFINED';

    /**
     * DOM Element inside HTMLElement where an hypothetic price is displayed
     */
    this.HTMLReducPriceLabel = null;
    
    this.init = function(step, parent) {
        this.pristine = false;
        this.initDOMLinks(step, parent);

        Super.init.call(this, step, parent);
        
        this.updateInternal(step);
        this.syncHTMLState();
    };

    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        
        // seems that JQuery uniform is messing with some home made trigger, so we have to be
        // specifically listening to the input and not the all line (result as a double clic)
        this.targetEvent = this.getHTMLElement().find('input');
        
        this.HTMLPriceLabel = this.getHTMLElement().find('.label.label-default');
        this.HTMLReducPriceLabel = this.getHTMLElement().find('.label.label-danger.reduc');
    };       
       
    /**
     * Links state to an operation
     * @param {type} state
     * @param {string} text Input's content if any
     * @returns {CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice.createOperation.ope}
     */
    this.createOperationFromState = function(state, text) {
        var op = Super.createOperationFromState.call(this, state, text);
        op.value = text || op.value;
        
        return op;
    };
    
    /**
     * Methods called when the targetEvent is left (blur event)
     * 'this' MUST be binded to the current object (using $.proxy for example)
     */
    this.onInteract = function() {
        var currentState = this.state;
        var text = this.targetEvent.val();

        this.hasError = !this.validateData(text);

        var nextState;
        var op;
        // true if inactive or if value does not match input's requirements
        // indeed, in HTML5 browser, val() returns '' when input does not
        // match requirement. For example, an input type=number with 'John'
        // in it will have '' as val()
        if (text === this.valueInactive) {
            if (currentState === this.STATE.ACTIVE) {
                // remove value
                nextState = this.STATE.INACTIVE;
                op = this.createOperationFromState(nextState);
            }
        } else {
            if (text !== this.currentContent) {
                nextState = this.STATE.ACTIVE;
                op = this.createOperationFromState(nextState, text);   
            }
        }
        
        this.syncHTMLState();
        
        if (this.hasError) {
            return;
        }
        
        var oldOperations = this.operations; 
        if (op) {
            this.resetOperations();
            this.addOperations(op);
        } else {
            // means we do nothing, quit
            return;            
        }    
        
        var allowed = this.parent.notify(this.getID(), nextState);
        this.state = allowed ? nextState : currentState;
        this.currentContent = allowed ? text : this.currentContent;
        
        // initial state restored if we weren't allowed to update
        if (!allowed) {
            // restore old operation
            // if not allowed means element does not have a good value
            this.operations = oldOperations;
            this.hasError = true;
        } else {
            this.pristine = true;
        }
        
        this.syncHTMLState();
    };
    
    this.validateData = function(inputContent) {
        // override by subclasses
        return true;
    };
    
    /**
     * Allows to set the specific value to input
     * Goes through updateInternal process
     * @param {Boolean | Number} value  new value for input
     */
    this.setValue = function(value) {
        var d = {};
        d.params = {};
        d.params.value = value;
        this.updateInternal(d);
    };
    
    this.updateInternal = function(data) {
        
        this.resetOperations();
        var op;
        
        // equals false if no value
        if (data.params.value) {
            this.currentContent = data.params.value;
            this.state = this.STATE.ACTIVE;
            this.pristine = true;
            op = this.createOperationFromState(this.state, this.currentContent);
        } else {
            this.currentContent = this.valueInactive;
            this.state = this.STATE.INACTIVE;

            if (this.pristine) {
                op = this.createOperationFromState(this.state, this.currentContent);   
            }
        }
        
        if (op) {
            this.addOperations(op);            
        }
        
        this.targetEvent.prop('value', this.currentContent);
    
        // after updateInternal, we always have a correct value
        this.hasError = false;
        this.syncHTMLState();

        if(data.params.display_reduc != null) {
            //this.getHTMLReducPriceLabel().html(data.params.display_reduc);
        }
    };
    
    /**
     * Syncs HTML display according 'hasError'
     */
    this.syncHTMLState = function() {
        var elt = this.getHTMLElement();
        
        //
        // display errors no matter which state we are in
        // display 'success' only on active state 
        if (this.hasError) {
            elt.removeClass('form-ok');
            elt.addClass('form-error');
        } else {
            elt.removeClass('form-error');            
            if (this.STATE.ACTIVE === this.state) {
                elt.addClass('form-ok');
            } else {
                elt.removeClass('form-ok');
            }
        }
    };
    
    this.bind = function() {
        this.targetEvent.bind('blur', $.proxy(this.onInteract, this));
    };

    this.unbind = function() {
        this.targetEvent.unbind('blur', $.proxy(this.onInteract, this));
    };

    if (step) {
        this.init(step, parent);
    }

    this.getHTMLReducPriceLabel = function() {
        return this.HTMLReducPriceLabel;
    };
};

CONFIGURATOR.ELEMENTS.INPUT.BaseInput.prototype = new CONFIGURATOR.ELEMENTS.BaseSimpleElement;
