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

CONFIGURATOR.ELEMENTS.CHOICES.ChoicesMultiples = function(step, parent){
    
    /**
     * Contains current active element's ID
     * if ID is in array => ACTIVE
     * else => INACTIVE
     */
    this.currents = [];
    
    this.updateInternalState = function() {
        var self = this;
        this.currents = [];
        this.substeps.forEach(function(substep) {
            if (self.isVisibleAndActive(substep)) {
                self.addToCurrents(substep.getID());
            }
        });
        
        if (this.currents.length > 0) {
            this.state = this.STATE.ACTIVE;
        } else {
            this.state = this.STATE.INACTIVE;
        }
    };
    
    /**
     * Add the provided id to the list of active elements
     * @param {type} id     ID to add
     */
    this.addToCurrents = function(id) {
        this.currents.push(id);
    };
    
    /**
     * Removes the provided id from the list if it is inside
     * @param {type} id     ID to remove
     */
    this.removeFromCurrents = function(id) {
        var index = this.currents.indexOf(id);
        if (index > -1) {
            this.currents.splice(index, 1);
        }
    };
    
    this.validateChildAction = function(id, state) {
        var valid = false;

        switch (state) {
            case this.STATE.INACTIVE:
                // Old system: can't remove last selected option when step is required
                /*if (this.step.params.required === "1") {
                    // remove only if at least there are two elements left
                    if (this.currents.length > 1) {
                        this.removeFromCurrents(id);
                        valid = true;
                    }
                } else {
                    this.removeFromCurrents(id);
                    valid = true;
                }*/

                // New system: can remove last selected option in all case
                this.removeFromCurrents(id);
                valid = true;
                break;
            case this.STATE.ACTIVE:
                this.addToCurrents(id);
                valid = true;
                break;
                
            default:
                console.log("validateChildAction choices-multiples invalid state");
                break;
        }

        return valid;
    };
    
    
    if (step) {
        this.init(step, parent);  
    }
};


CONFIGURATOR.ELEMENTS.dispatch.registerObject('choices_multiples', CONFIGURATOR.ELEMENTS.CHOICES.ChoicesMultiples);
CONFIGURATOR.ELEMENTS.CHOICES.ChoicesMultiples.prototype = new CONFIGURATOR.ELEMENTS.CHOICES.BaseChoices;
