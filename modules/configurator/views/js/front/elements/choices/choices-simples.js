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

CONFIGURATOR.ELEMENTS.CHOICES.ChoicesSimples = function(step, parent){
    
    var Super = Object.getPrototypeOf(this);
    
    /**
     * Contains current active child's ID
     * Set to undefined when none is active
     */
    this.current;

    this.updateInternalState = function() {
        var updated = false;
        var i = 0;
        this.current = undefined;
        // look for an active child, when found, stop research
        while (!updated && i < this.substeps.length) {
            var substep = this.substeps[i];
            if (this.isVisibleAndActive(substep)) {
                this.current = substep.getID();
                updated = true;
            }
            i++;
        }
                
        if (this.current > 0) {
            this.state = this.STATE.ACTIVE;
        } else {
            this.state = this.STATE.INACTIVE;
        }
        
    };
    
    this.notify = function(id, state) {
        var allowed = Super.notify.call(this, id, state);
      
        // if allowed, means the checked element has changed
        // ask children to update their state
        if (allowed) {
            this.substeps.forEach(function(substep) {
                substep.updateStateFromHtml();
            });
        }
        
        return allowed;
    };
    
    this.validateChildAction = function(id, state) {
        var valid = false;
        
        switch (state) {
            case this.STATE.INACTIVE:
                if (this.step.params.required !== "1") {
                    this.current = undefined;
                    valid = true;
                }
                break;
            case this.STATE.ACTIVE:
                this.current = id;
                valid = true;
                break;
                
            default:
                console.log("validateChildAction choices-simples invalid state");
                break;
        }

        return valid;
    };

    if (step) {
        this.init(step, parent);  
    }
};

CONFIGURATOR.ELEMENTS.dispatch.registerObject('choices_simples', CONFIGURATOR.ELEMENTS.CHOICES.ChoicesSimples);
CONFIGURATOR.ELEMENTS.CHOICES.ChoicesSimples.prototype = new CONFIGURATOR.ELEMENTS.CHOICES.BaseChoices;
