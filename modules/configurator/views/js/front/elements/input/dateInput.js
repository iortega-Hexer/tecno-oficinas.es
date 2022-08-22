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
 * Handles input with type='number' and data-type=date
 * @param {type} step
 * @param {type} parent
 */

CONFIGURATOR.ELEMENTS.INPUT.dateInput = function(step, parent) {
    
    this.valueInactive = '';
    var Super = Object.getPrototypeOf(this);
    var format = $("input[data-type=datePicker]").attr("data-format").toLowerCase();
       
    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        this.targetEvent = this.getHTMLElement().find('div[data-type=date]');
    };
    
    this.validateData = function (inputContent) {
        return this.targetEvent[0].checkValidity();
    };

    if (step) {
        this.init(step, parent);
    }

  $( function() {
    $( "input[data-type=datePicker]" ).datepicker({
       dateFormat : format
      });
  } );
  
};
CONFIGURATOR.ELEMENTS.dispatch.registerObject('date_input', CONFIGURATOR.ELEMENTS.INPUT.dateInput);
CONFIGURATOR.ELEMENTS.INPUT.dateInput.prototype = new CONFIGURATOR.ELEMENTS.INPUT.BaseInput;