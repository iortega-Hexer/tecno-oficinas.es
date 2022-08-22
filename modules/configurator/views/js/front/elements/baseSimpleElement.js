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
 * Abstract class that contains methods used by all simple element
 * Simple element means here that it does not contain any substeps
 * @param {type} step
 * @param {type} parent
 */
CONFIGURATOR.ELEMENTS.BaseSimpleElement = function(step, parent) {

    var Super = Object.getPrototypeOf(this);
    
    /**
     * Links state to an operation
     * @param {type} state
     * @returns {CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice.createOperation.ope}
     */
    this.createOperationFromState = function(state) {
        var op;
        switch (state) {
            case this.STATE.ACTIVE:
                op = this.createOperation(this.OPERATION_NAME.ADD);
                break;
            case this.STATE.INACTIVE:
                op = this.createOperation(this.OPERATION_NAME.REMOVE);
                break;
        }
        
        return op;
    };
    
    this.createOperation = function(operationName) {
        var ope = Super.createOperation.call(this, operationName);
        switch (operationName) {
            case this.OPERATION_NAME.ADD: 
            case this.OPERATION_NAME.REMOVE:
                ope.option = this.getID();
                ope.step = this.parent.getID();
                break;
            default:
                console.log("No operation linked to " + operationName + " for BaseSimpleElement");
                ope = {};
                break;
        }
        
        return ope;
    };
    
    this.update = function(data) {
        Super.update.call(this, data);
        this.updateInternal(data);
    };

    this.updateInternal = function(data) {
        // override by subclasses
    };
    
    if (step) {
        this.init(step, parent);  
    }
};

CONFIGURATOR.ELEMENTS.BaseSimpleElement.prototype = new CONFIGURATOR.ELEMENTS.BaseElement;