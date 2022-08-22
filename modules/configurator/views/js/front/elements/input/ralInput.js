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
 * Handle email
 * @param {type} step
 * @param {type} parent
 */
CONFIGURATOR.ELEMENTS.INPUT.RalInput = function (step, parent) {

    var Super = Object.getPrototypeOf(this);
    this.valueInactive = '';

    this.onChangeRal = function (event) {
        var $ral_block = $(event.currentTarget);
        var ref = $ral_block.data('ref');

        var step_id = $(this.targetEvent).data('step');
        var option_id = $(this.targetEvent).data('option');

        $('#configuratorRalModal-'+step_id+'-'+option_id+' .configurator-ral-list-option').removeClass('selected');
        $ral_block.addClass('selected');

        // Close modal
        $('#configuratorRalModal-'+step_id+'-'+option_id).modal('hide');

        // Change value in operation
        this.targetEvent.val(ref);

        this.onInteract();
    };
    
    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        this.targetEvent = this.getHTMLElement().find('input[data-type=ral_input]');

        var step_id = $(this.targetEvent).data('step');
        var option_id = $(this.targetEvent).data('option');

        // Bind configurator-ral-attribute
        $('#configuratorRalModal-'+step_id+'-'+option_id+' .configurator-ral-list-option').bind('click', $.proxy(this.onChangeRal, this));
    };   
    
    if (step) {
        this.init(step, parent);
    }
    
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('ral_input', CONFIGURATOR.ELEMENTS.INPUT.RalInput);
CONFIGURATOR.ELEMENTS.INPUT.RalInput.prototype = new CONFIGURATOR.ELEMENTS.INPUT.BaseInput;
