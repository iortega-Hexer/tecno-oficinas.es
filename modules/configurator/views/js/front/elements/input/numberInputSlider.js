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
 * Handles input with type='number' and data-type=slider
 * @param {type} step
 * @param {type} parent
 */
CONFIGURATOR.ELEMENTS.INPUT.NumberInputSlider = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);
    
    // Avoid problem for updateInternal
    this.initDone = false;
    
    this.init = function(step, parent) {
        Super.init.call(this, step, parent);
        this.initDone = true;
        
        var initValue = parseInt(this.step.params.value, this.numberBase);
        
        this.targetEvent.slider({
            value: initValue,
            min: this.targetEvent.data('min'),
            max: this.targetEvent.data('max'),
            step: this.targetEvent.data('slider-step'),
            slide: $.proxy(this.onSlide, this)
        });
        
        this.changeInformationWithValue(initValue);
    };
    
    this.changeInformationWithValue = function (value) {
        $("#slider_information_"+this.targetEvent.prop('id')+" span").text(value);
    };
    
    this.onSlide = function (event, ui) {
        this.changeInformationWithValue(ui.value);
    };
    
    this.onChange = function(event, ui) {
        this.changeInformationWithValue(ui.value);
        
        // Eviter que si le client clique uniquement sur le slide cela fasse partir la requête
        if(parseInt(this.step.params.value, 10) !== parseInt(ui.value, 10)) {
            this.step.params.value = ui.value;
            this.onInteract();
        }
    };
    
    this.onInteract = function() {
        var currentState = this.state;
        
        // Change value in operation
        this.operations[0].value = this.step.params.value; 

        /**
         * Lance l'envoie AJAX ver le controller
         */
        this.parent.notify(this.getID(), this.STATE.ACTIVE);
    };
    
    this.bind = function() {
        Super.bind.call(this);
        this.targetEvent.slider('option','change', $.proxy(this.onChange, this));
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.targetEvent.slider('option','change', $.proxy(this.onChange, this));
    };
    
    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        this.targetEvent = this.getHTMLElement().find('div[data-type=slider]');
    };       
    
    this.updateInternal = function(data) {
        Super.updateInternal.call(this, data);
        
        // Assure que si en backoffice une valeur est saisie elle remonte sur le front
        if (data.params.value && this.initDone) {
            this.targetEvent.slider('option', 'value', parseInt(data.params.value, 10));
        }
    };
    
    
    if (step) {
        this.init(step, parent);
    }
    
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('slider_input', CONFIGURATOR.ELEMENTS.INPUT.NumberInputSlider);
CONFIGURATOR.ELEMENTS.INPUT.NumberInputSlider.prototype = new CONFIGURATOR.ELEMENTS.INPUT.BaseInput;
