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
CONFIGURATOR.ELEMENTS.PRICELIST = CONFIGURATOR.ELEMENTS.PRICELIST || {};

/**
 * Handles price list with inputs or select as children
 * @param {type} step
 * @param {type} parent
 * @returns {undefined}
 */
CONFIGURATOR.ELEMENTS.PRICELIST.PriceListSimple = function(step, parent) {

    /**
     * In this case, we are assuming that all children are subclass from
     * BaseSimpleElement as it is supposed to determine one dimension each.
     * We can then override the dimension attribute according to the position in
     * the list
     * @returns {Array}
     */
    this.getOperations = function() {
        var op = $.merge([], this.operations);

        this.substeps.forEach(function(substep, id) {
            var ope = substep.getOperations();

            if (ope.length === 1) {
                // dimension are in the same order as the one in children's list
                ope[0].dimension = (substep.step.params.pos + 1) || (id + 1);
            } else if (ope.length > 1) {
                console.log("WARNING: should not happen for a price list !");
            }
            op = $.merge(op, ope);
        });

        return op;
    };

    this.updateState = function() {
        var numberActive = 0;

        // price list considered as active only if all its children are actives
        for (var s in this.substeps) {
            var substep = this.substeps[s];
            if (this.isVisibleAndActive(substep)) {
                numberActive++;
            }
        }

        // comparaison to length in order to take into acount
        // one and two dimension (why not more one day)
        if (numberActive === this.substeps.length) {
            this.state = this.STATE.ACTIVE;
        } else {
            this.state = this.STATE.INACTIVE;
        }
    };


    if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('pricelist_simple_input', CONFIGURATOR.ELEMENTS.PRICELIST.PriceListSimple);
CONFIGURATOR.ELEMENTS.dispatch.registerObject('pricelist_simple_select', CONFIGURATOR.ELEMENTS.PRICELIST.PriceListSimple);
CONFIGURATOR.ELEMENTS.PRICELIST.PriceListSimple.prototype = new CONFIGURATOR.ELEMENTS.PRICELIST.BasePricelist;
