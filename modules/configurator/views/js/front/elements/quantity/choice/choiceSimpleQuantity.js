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
CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE = CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE || {};

CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE.ChoiceSimpleQuantity = function(step, parent){
    
    var Super = Object.getPrototypeOf(this);

    this.getOperations = function() {
        // ask select to update
        var select = this.substeps[0];
        
        var before = select.getState();
        select.updateStateFromHtml();
        var current = select.getState();

        var op;
        if (before !== current && current === this.STATE.INACTIVE) {
            op = [];
        } else {
            op = Super.getOperations.call(this);
        }
        
        return op;
    };

    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choice_simple_quantity', CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE.ChoiceSimpleQuantity);
CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE.ChoiceSimpleQuantity.prototype = new CONFIGURATOR.ELEMENTS.QUANTITY.CHOICE.BaseChoiceQuantity;
