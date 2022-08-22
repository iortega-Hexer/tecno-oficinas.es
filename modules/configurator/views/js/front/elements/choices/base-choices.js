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
CONFIGURATOR.ELEMENTS.CHOICES = CONFIGURATOR.ELEMENTS.CHOICES || {};

CONFIGURATOR.ELEMENTS.CHOICES.BaseChoices = function(step, parent) {

    var Super = Object.getPrototypeOf(this);
    
    this.init = function(step, parent){
        Super.init.call(this, step, parent);
        this.updateInternalState();
    };
    
    
    /**
     * Called right after children's update in order to set parent's state
     * depending on children. Indeed, for checkboxes and radio button, we have
     * to list active elements
     */
    this.updateInternalState = function() {
        // override by subclasses
    };
    
    this.update = function(data) {
        Super.update.call(this, data);
    
        // update state after children's one
        this.updateInternalState();
    };
    
    
    if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.CHOICES.BaseChoices.prototype = new CONFIGURATOR.ELEMENTS.BaseGroupElement;
