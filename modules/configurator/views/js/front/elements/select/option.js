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
CONFIGURATOR.ELEMENTS.SELECT = CONFIGURATOR.ELEMENTS.SELECT || {};

/**
 * Handles HTML OPTION element found in select
 * @param {type} step
 * @param {type} parent
 * @returns {undefined}
 */
CONFIGURATOR.ELEMENTS.SELECT.Option = function(step, parent) {
 
    var Super = Object.getPrototypeOf(this);
        
    this.init = function(step, parent) {
        Super.init.call(this, step, parent);
        this.updateInternal(step);
    };
    
    
    this.updateInternal = function(step) {
        // create option element according to new info and add it
        // to select
        var select = this.parent.getHTMLElement().find('select');
        var elt = this.createElement();
        select.append(elt);
        this.HTMLElement = elt;
        
        // updating state according to 'selected' attribute
        this.state = (step.params.selected) ? this.STATE.ACTIVE: this.STATE.INACTIVE;
    };
    
    /**
     * 
     * @returns JQuery 'option' element
     */
    this.createElement = function() {
        var value = this.step.params.name;
        
        // adding amount if necessary
        value += (this.step.params.display_amount) ? ' (' + this.step.params.display_amount + ')': '';
        
        var params = {id: 'option_' + this.parent.getID() + '_' + this.getID(),
                        text: value,
                        value: this.getPosition()
                    };
                    
        if (this.step.params.selected) {
            params.selected = 'selected';
        }
                    
        var option = $("<option>", params);
        
        return option;
    };
    
    this.update = function(data) {
        this.step = data;
        //this.updateInternal(data);
        Super.update.call(this, data);
    };

    this.createOperationFromState = function(state) {
        var op;
        if (state === this.STATE.ACTIVE) {
            op = Super.createOperationFromState.call(this, this.STATE.ACTIVE);
            if (this.getPosition() === -1) {
                op.action = this.OPERATION_NAME.REMOVE;
            }
        }

        return op;
    };
    
    /**
     * Change current state of element. It synchronise both JS and html
     * to the new provided state
     * @param {type} newState
     * @returns {undefined}
     */
    this.goToState = function(newState) {
        this.resetOperations();
        this.state = newState;
        
        if (newState === this.STATE.ACTIVE) {
            this.HTMLElement.attr('selected', 'selected');
        } else {
            this.HTMLElement.attr('selected', false);
        }
        
        var op = this.createOperationFromState(newState);

        if (op) {
            this.addOperations(op);            
        }
    };
    
    if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('select_option', CONFIGURATOR.ELEMENTS.SELECT.Option);
CONFIGURATOR.ELEMENTS.SELECT.Option.prototype = new CONFIGURATOR.ELEMENTS.BaseSimpleElement;
