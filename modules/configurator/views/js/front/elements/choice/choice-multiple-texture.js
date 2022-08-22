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

CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultipleTexture = function(step, parent){
    
    var Super = Object.getPrototypeOf(this);

    /**
     * Tricky way to do multiple inheritances. Used in order to avoid
     * duplicate methods with choiceSimpleTexture
     */
    var simpleTexture = new CONFIGURATOR.ELEMENTS.CHOICE.ChoiceSimpleTexture;
    
    this.bind = function() {
        Super.bind.call(this);

        // Condition for 2D Viewer with dropzone
        var step = $(this.getHTMLElement()).closest('.step_group');
        if (!$(step).hasClass('dmviewer2d-step-dropzone')) {
            this.getHTMLElement().bind('click', $.proxy(simpleTexture.redirectClick, this));
        }
    };

    this.unbind = function() {
        Super.unbind.call(this);
        this.getHTMLElement().unbind('click', $.proxy(simpleTexture.redirectClick, this));
    };

    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choice_multiple_texture', CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultipleTexture);
CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultipleTexture.prototype = new CONFIGURATOR.ELEMENTS.CHOICE.ChoiceMultiple;
