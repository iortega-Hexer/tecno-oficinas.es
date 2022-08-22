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

CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimple = function(step, parent){
    
    var Super = Object.getPrototypeOf(this);
    
    this.updateStateFromHtml = function() {
        var nextState = (this.targetEvent.prop('checked')) ? this.STATE.ACTIVE : this.STATE.INACTIVE;
        
        // sync operation with current state
        this.resetOperations();
        if (nextState === this.STATE.ACTIVE) {
            var op = this.createOperation(this.OPERATION_NAME.ADD);
            this.addOperations(op);
        }
        this.syncHTMLState(nextState);
    };
    
    /**
     *  As input's state changes, sync before rendering operation
     */
    this.getOperations = function() {
        var nextState = (this.targetEvent.prop('checked')) ? this.STATE.ACTIVE : this.STATE.INACTIVE;
        if (nextState !== this.state && nextState === this.STATE.INACTIVE) {
            // 
            // As the current system requires to have every elements up to date
            // and as radio buttons change their state automatically
            // == clic on second choice deselect first one
            // We need to remove ADD operation if it does not match
            // html state
            //
            if (this.operations[0].action === this.OPERATION_NAME.ADD) {
                return [];
            }
        }
        
        return Super.getOperations.call(this);
    };
    

    /**
     * Allows to catch click on already selected input
     * If state is ACTIVE, deselect and trigger onchange event in order to
     * start normal behavior
     * 'this' MUST be binded to the current object (using $.proxy for example)
     * @param {jQuery.Event} event Click event
     */
    this.onClick = function(event) {
        if (this.state === this.STATE.ACTIVE) {
            // prevent double 'on change' for texture
            // manuel trigger on input causes two onchange when deselecting
            event.preventDefault();

            this.targetEvent.prop('checked', false);
            this.targetEvent.trigger('change');  
        }
    };
    
    this.bind = function() {
        Super.bind.call(this);
        this.targetEvent.bind('click', $.proxy(this.onClick, this));
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.targetEvent.unbind('click', $.proxy(this.onClick, this));
    };
    
    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choice_simple', CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimple);
CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimple.prototype = new CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice;
