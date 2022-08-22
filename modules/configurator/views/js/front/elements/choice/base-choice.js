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
CONFIGURATOR.ELEMENTS.CHOICE = CONFIGURATOR.ELEMENTS.CHOICE || {};

CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);
    
    /**
     * DOM Element inside HTMLElement to which we bind 'onclick' listener
     */
    this.targetEvent;
    
    /**
     * DOM Element inside HTMLElement where an hypothetic price is displayed
     */
    this.HTMLPriceLabel;
	this.HTMLReducPriceLabel;
    
    this.init = function(step, parent) {
        
        this.initDOMLinks(step, parent);
        
        Super.init.call(this, step, parent);
        
        this.updateInternal(step);
        
    };
    
    //
    // TODO
    // For now, looking for an ID attribute
    // Later we will have to handle steps in steps in steps
    // so we should always look for a specific element inside the parent
    //
    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        
        // seems that JQuery uniform is messing with some home made trigger, so we have to be
        // specifically listening to the input and not the all line (result as a double clic)
        this.targetEvent = this.getHTMLElement().find('input[type=radio], input[type=checkbox]');

        this.HTMLPriceLabel = this.getHTMLElement().find('.label.label-default');  
		this.HTMLReducPriceLabel = this.getHTMLElement().find('.label.label-danger.reduc');  
    };    
    
    this.updateInternal = function(data) {
        this.step = data;
        
        this.resetOperations();
        // selected field from params defines the Element's initial state
        var initState = this.STATE.INACTIVE;
        if (this.step.params.selected) {
            initState = this.STATE.ACTIVE;
            // When the initial state is 'selected', we have to prepare the
            // corresponding operation in order to be able to give this option back
            // at the next getOperations() procedure
            var op = this.createOperationFromState(initState);
            this.addOperations(op); 
        }
        this.syncHTMLState(initState);
        
        this.getHTMLPriceLabel().html(data.params.display_amount);
		this.getHTMLReducPriceLabel().html(data.params.display_reduc);
    };
    
    /**
     * Update the HTML state of the current element through this.targetEvent.
     * Call JQuery's uniform plugin in order to update its display. If a state
     * is provided, set it as the new state before update. Only work for
     * INACTIVE and ACTIVE state
     * 
     * @param {type} newState Coming from this.STATE. When provided, is set as 
     *                          the new state
     */
    this.syncHTMLState = function(newState) {
        if (newState) {
            this.state = newState;
        }
        
        var htmlState;
        
        switch (this.state) {
            case this.STATE.INACTIVE:
                htmlState = false;
                this.getHTMLElement().removeClass('selected');
                break;
            case this.STATE.ACTIVE:
                htmlState = true;
                this.getHTMLElement().addClass('selected');
                break;
            default:
                console.log("Do not know how to update HTML for state: " + this.state);
                htmlState = false;
        }
        
        
        this.targetEvent.prop('checked', htmlState);
        // update CSS through uniform
        $.uniform.update(this.targetEvent);
    };
        
    /**
     * On change action binded to HTMLElement during bind/unbind
     * 'this' MUST be binded to the current object (using $.proxy for example)
     */
    this.onChange = function() {
        var currentState = this.state;
        var nextState = (this.state === this.STATE.ACTIVE) ? this.STATE.INACTIVE : this.STATE.ACTIVE;
        
        
        var oldOperations = this.operations; 
        var op = this.createOperationFromState(nextState);
        if (op) {
            this.resetOperations();
            this.addOperations(op);
        }
        
        var allowed = this.parent.notify(this.getID(), nextState);
        
        nextState = allowed ? nextState : currentState;
        this.syncHTMLState(nextState);
        
        // initial state restored if we weren't allowed to update
        if (!allowed) {
            this.operations = oldOperations;
        }
    };
    
    this.bind = function() {
        this.targetEvent.bind('change', $.proxy(this.onChange, this));
    };

    this.unbind = function() {
        this.targetEvent.unbind('change', $.proxy(this.onChange, this));
    };
        
    /**
     * 
     * @return HTML Element where price must be displayed
     */
    this.getHTMLPriceLabel = function() {
        return this.HTMLPriceLabel;
    };
	this.getHTMLReducPriceLabel = function() {
        return this.HTMLReducPriceLabel;
    };
    
    if (step) {
        this.init(step, parent);  
    }
};

CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice.prototype = new CONFIGURATOR.ELEMENTS.BaseSimpleElement;
