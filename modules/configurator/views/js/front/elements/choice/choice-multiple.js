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

CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultiple = function(step, parent){

    var Super = Object.getPrototypeOf(this);

    /**
     * TRUE means checked as default on server side
     * FALSE otherwise
     */
    this.isDefaultOption;

    this.init = function(step, parent) {
        Super.init.call(this, step, parent);
        
        // if state is ACTIVE during init, means element is a
        // default option (selected in backoffice)
        this.isDefaultOption = (this.state === this.STATE.ACTIVE);
    };
    
    this.updateInternal = function(data) {
        Super.updateInternal.call(this, data);
        
        //
        //  if defaultOption and INACTIVE, element has to provide
        //  a 'remove' operation in order to cancel default option from server
        // when leaving "INVISIBLE" state (case of conditionnal appearance)
        if (this.isDefaultOption && 
            this.state === this.STATE.INACTIVE) {
            var op = this.createOperationFromState(this.state);
            this.addOperations(op);
        }
    };

    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choice_multiple', CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultiple);
CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultiple.prototype = new CONFIGURATOR.ELEMENTS.CHOICE.BaseChoice;
