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
CONFIGURATOR.ELEMENTS.INPUT.NumberQuantityInput = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);

    this.timeOut;
    this.delayBeforeSend = 500;

    // empty input means inactive
    this.valueInactive = '0';
    
    this.initDOMLinks = function(step, parent) {
        
        this.HTMLElement = parent.getHTMLElement().find('.quantity_wanted');
        this.HTMLElementPlus = this.HTMLElement.find('.configurator-quantity-plus');
        this.HTMLElementMinus = this.HTMLElement.find('.configurator-quantity-minus');

        this.targetEvent = parent.getHTMLElement().find('input.qty');

    };

    this.bind = function() {
        let self = this;
        if (typeof this.HTMLElementPlus !== 'undefined') {
            this.HTMLElementPlus.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearTimeout(self.timeOut);

                var stepQty = parseInt($(this).data('step') || 1);
                var newValue = parseInt((parseInt(self.targetEvent.val()) + stepQty) / stepQty) * stepQty;
                self.targetEvent.val(newValue);
                self.timeOut = setTimeout(function () {
                    Super.onInteract.call(self);
                }, self.delayBeforeSend);
            });
        }
        if (typeof this.HTMLElementMinus !== 'undefined') {
            this.HTMLElementMinus.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearTimeout(self.timeOut);

                var stepQty = parseInt($(this).data('step') || 1);
                var newValue = parseInt((parseInt(self.targetEvent.val()) - stepQty) / stepQty) * stepQty;
                if(newValue < 0) {
                    newValue = 0;
                }

                self.targetEvent.val(newValue);
                self.timeOut = setTimeout(function () {
                    Super.onInteract.call(self);
                }, self.delayBeforeSend);
            });
        }

        Super.bind.call(this);
    };

    this.unbind = function() {
        Super.unbind.call(this);
        if (typeof this.HTMLElementPlus !== 'undefined') {
            this.HTMLElementPlus.unbind('click');
        }
        if (typeof this.HTMLElementMinus !== 'undefined') {
            this.HTMLElementMinus.unbind('click');
        }
    };
    
    if (step) {
        this.init(step, parent);
    }
    
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('number_quantity_input', CONFIGURATOR.ELEMENTS.INPUT.NumberQuantityInput);
CONFIGURATOR.ELEMENTS.INPUT.NumberQuantityInput.prototype = new CONFIGURATOR.ELEMENTS.INPUT.NumberInput;
