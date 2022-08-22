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

CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimpleTexture = function(step, parent){
    
    var Super = Object.getPrototypeOf(this);

    /**
     * "Zoom" element displayed on custom textures in order to zoom on it
     * (pop up like).
     */
    this.zoom_class = 'configurator-zoom';

    /**
     * Allows to catch click on textures and redirect it to the input
     * 'this' MUST be binded to the current object (using $.proxy for example)
     * @param {jQuery.Event} event Click event
     */
    this.redirectClick = function(event) {
        var target = $(event.target);
        // check if click originated from zoom element
        // if so cancel current action
        if (target.hasClass(this.zoom_class) || 
            target.parent().hasClass(this.zoom_class)) {
            return true;
        }
        
        if (!target.is('input')) {
            event.preventDefault();
            this.targetEvent.trigger('click');
        }
    };
    
    this.bind = function() {
        Super.bind.call(this);
        this.getHTMLElement().bind('click', $.proxy(this.redirectClick, this));
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.getHTMLElement().unbind('click', $.proxy(this.redirectClick, this));
    };

    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choice_simple_texture', CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimpleTexture);
CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimpleTexture.prototype = new CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimple;
