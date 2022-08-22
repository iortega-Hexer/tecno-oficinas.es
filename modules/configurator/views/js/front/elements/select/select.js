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
CONFIGURATOR.ELEMENTS.SELECT = CONFIGURATOR.ELEMENTS.SELECT || {};

/**
 * Handles html SELECT element
 * @param {type} step
 * @param {type} parent
 * @returns {undefined}
 */
CONFIGURATOR.ELEMENTS.SELECT.Select = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);
        
    this.init = function(step, parent) {
        this.initDOMLinks(step, parent);
        this.cleanOption(step);
        
        Super.init.call(this, step, parent);

        this.updateInternalState();        
    };
    
    this.initDOMLinks = function(step, parent) {
        Super.initDOMLinks.call(this, step, parent);
        this.targetEvent = this.getHTMLElement().find('select');
    };
    
    this.cleanOption= function(step) {
        // remove only option connected to JS's objects
        // condition on ID attribute allows to avoid deleting default option
        this.targetEvent.find('option[id^="option_"]').remove();
    };
    
    this.updateInternalState = function() {        
        
        this.selected = undefined;
        var self = this;
        
        // retrieving current active option
        this.substeps.forEach(function(elt) {
            if (self.isVisibleAndActive(elt)) {
                self.selected = elt.getID().toString();
            }
        });
        
        // if no selected item was found, means that the previously selected one
        // was displayed according to a condition, and this condition is no more
        // valid. Thus we have to select a default value, which is the first one
        if(this.selected === undefined) {
            $(this.targetEvent).find('option:disabled').attr('selected', 'selected');
            this.state = this.STATE.INACTIVE;
        } else {
             this.state = this.STATE.ACTIVE;
        }
        
        // need to reset uniform after dealing with default selected item
        $(this.targetEvent).uniform();
    };
    
    this.update = function(data) {
        this.cleanOption(data);
        Super.update.call(this, data);

        this.updateInternalState();
    };
    
    /**
     * 
     * @param {type} selectedItems
     */
    this.notifyChildren = function(selectedItems) {
        // if undefined or null, means default value
        if (typeof(selectedItems) === 'undefined' ||
            selectedItems === null) {
            // reset default option
            $(this.targetEvent).find('option:disabled').attr('selected', 'selected');
            return;
        };
        
        var self = this;
        //
        // Asks children to update themself according to the given state
        // Allows to update state according to the newly selected item
        // It allows also to deselected the possible hidden option
        // and avoid 500 internal error on server side
        //
        this.substeps.forEach(function(elt) {
            /**
             * @todo: Dernier check à faire c'est être sûr que cette fonction a besoin peut être mis au niveau de priceListSimple ou autre
             */
            if(selectedItems.indexOf(',') === -1) {
                if (parseInt(selectedItems) === parseInt(elt.getPosition())) {
                    elt.goToState(self.STATE.ACTIVE);
                } else {
                    elt.goToState(self.STATE.INACTIVE);
                }
            } else {
                if (selectedItems.indexOf(elt.getPosition()) === 0) {
                    elt.goToState(self.STATE.ACTIVE);
                } else {
                    elt.goToState(self.STATE.INACTIVE);
                }
            }
        });
    };
    
    this.action = function() {
        var selectedItems = this.targetEvent.val();

        // retrieve ID in order to give this information to parent
        var selected = this.containsSubsteps(selectedItems);
        var id = (selected) ? selected.getID() : 0;

        var oldSelected = this.selected;
        this.notifyChildren(selectedItems);

        var allowed = Super.notify.call(this, id, this.STATE.ACTIVE);

        if (allowed) {
            this.selected = selectedItems;
        } else {
            this.selected = oldSelected;
            this.notifyChildren(this.selected);
        }
    };
    
    this.getOperations = function() {
        var op = Super.getOperations.call(this);

        var o = op[0];
        if (o && o.action === this.OPERATION_NAME.REMOVE) {
            if (this.selected) {
                o.option = this.selected;
            }
        }
        return op;
    };

    this.getSelected = function() {
        return this.selected;
    };
    
    this.bind = function() {
        this.targetEvent.bind('change', $.proxy(this.action, this));
    };

    this.unbind = function() {
        this.targetEvent.unbind('change', $.proxy(this.action, this));
    };
    
    if (step) {
        this.init(step, parent);
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('select', CONFIGURATOR.ELEMENTS.SELECT.Select);
CONFIGURATOR.ELEMENTS.SELECT.Select.prototype = new CONFIGURATOR.ELEMENTS.BaseGroupElement;
