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
 * Handles input with type='number'
 * @param {type} step
 * @param {type} parent
 */
CONFIGURATOR.ELEMENTS.INPUT.NumberInput = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);

    // empty input means inactive
    this.valueInactive = '';
    
    this.selectorLabelMinValue = ".label_min";
    this.selectorLabelMaxValue = ".label_max";
    this.selectorLabelMinMaxValue = ".label_min_max";
    this.selectorMinValue = ".option_min_value";
    this.selectorMaxValue = ".option_max_value";
    this.selectorOptionInput = ".option_input";
    
    /**
     * Allows to handle multiple clicks on spinners
     */
    this.timeOut;
    
    /**
     * Minimum time before action. If several clicks happen within this
     * interval, the action will be reset (avoid multiple call for update that
     * would result as a bad UX)
     */
    this.delayBeforeSend = 500;
    
    /**
     * decimal base for parseint
     */
    this.numberBase = 10;
    
    this.onClick = function() {
        // clear in case of multiple clics by user
        clearTimeout(this.timeOut);
        
        // compare both value as integer
        // comparaison between the current value and the input's one allows us
        // to determine whether it is a click to enter the input or a click
        // on spinners (buttons that allows to add or substract 1 to the current value)
        var val = this.targetEvent.prop('value');
        var text = parseFloat(val, this.numberBase);
        var contentAsInt = parseFloat(this.currentContent, this.numberBase);
        
        // if val empty means either nothing in input or error
        // both can not happen by clicking on spinners
        if (val === '') {
            return;
        }
        
        var self = this;
        if (text !== contentAsInt) {
            this.timeOut = setTimeout(function() {
                Super.onInteract.call(self);
            }, this.delayBeforeSend);
        }
    };
    
    this.validateData = function(content) {
        if (content === this.valueInactive &&
            this.targetEvent[0].validity) {
            // might be a filter from browser, check if valid
            return this.targetEvent.is(':valid');
        }
        // WARNING: isNaN('') -> false
        var valid = content !== '' && !isNaN(content);

        if (valid) {
            var min = this.step.params.min;
            var max = this.step.params.max;
            var c = parseFloat(content, 10);

            if (min !== '' && !isNaN(min)) {
                valid = valid && min <= c;
            }

            if (max !== '' && !isNaN(max) && max !==0) {
                valid = valid && c <= max;
            }    
        }

        return valid;
    };
    
    this.getBlockMinMaxTarget = function () {
        var self = this;
        
        return self.targetEvent.parents(self.selectorOptionInput);
    };
    
    /**
     * Hide min and max value
     */
    this.hideAllMinMax = function () {
        var self = this;
        self.getBlockMinMaxTarget().find(self.selectorLabelMinValue).hide();
        self.getBlockMinMaxTarget().find(self.selectorLabelMaxValue).hide();
        self.getBlockMinMaxTarget().find(self.selectorLabelMinMaxValue).hide();
    };

    /**
     * Show min and max value
     */
    this.showMinMax = function () {
        var self = this;
        
        // Change text of min/max
        self.getBlockMinMaxTarget().find(self.selectorMaxValue).text(self.step.params.max);
        self.getBlockMinMaxTarget().find(self.selectorMinValue).text(self.step.params.min);
        
        // Active different template depending of case
        if (self.step.params.min > 0 && self.step.params.max > 0) {
            self.getBlockMinMaxTarget().find(self.selectorLabelMinMaxValue).show();
        } else if (self.step.params.min > 0) {
            self.getBlockMinMaxTarget().find(self.selectorLabelMinValue).show();
        } else if (this.step.params.max > 0) {
            self.getBlockMinMaxTarget().find(self.selectorLabelMaxValue).show();
        }
    };
    
    /**
     * Update min and max
     */
    this.updateMinMax = function(){
        var self = this;
        self.hideAllMinMax();
        self.targetEvent.prop("data-min", self.step.params.min);
        self.targetEvent.prop("data-max",self.step.params.max);
        self.showMinMax();
    };
    
    this.update = function(data){
        var self = this;
        self.updateMinMax();
        
        Super.update.call(self, data);
        //Super.onInteract.call(self);  
    };
    
    this.bind = function() {
        Super.bind.call(this);
        this.targetEvent.bind('click', $.proxy(this.onClick, this));

        // FIX: actualisation des valeurs MIN/MAX à la création de l'étape
        // Il y a eu des soucis de rafraichissement des formules dans des étapes avec des conditions
        this.showMinMax();
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.targetEvent.unbind('click', $.proxy(this.onClick, this));
    };
    
    if (step) {
        this.init(step, parent);
    }
    
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('number_input', CONFIGURATOR.ELEMENTS.INPUT.NumberInput);
CONFIGURATOR.ELEMENTS.INPUT.NumberInput.prototype = new CONFIGURATOR.ELEMENTS.INPUT.BaseInput;
