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

/**
 * Base group element that representes a HTML element that contains several 'smaller'
 * element. For example a step containing severals products will be something like:
 *      products -> [product, product, product, ...]
 * @param {type} step
 * @param {Object} parent     Object from the same hierarchie as baseElement
 *                              Should not be null as it is the only way for the object
 *                              to communicate
 * @returns {Object}          A new object
 */
CONFIGURATOR.ELEMENTS.BaseGroupElement = function(step, parent) {
    
    var Super = Object.getPrototypeOf(this);
    
    /**
     * Given an ID, look for the corresponding object in the children's list
     * @param {number} substepPosition       Substep's position, must be matching 'getPosition()'
     * @returns {Boolean|Object}  FALSE if not found otherwise the corresponding object
     */
    this.containsSubsteps = function(substepPosition) {
        var found = false;
        var ite = 0;
        var step;
        while (!found && ite < this.substeps.length) {
            found = (this.substeps[ite].getPosition() === substepPosition);
            if (found) {
                step = this.substeps[ite];
            }
            ite++;
        }

        return step || found;
    };
    
    /**
     * Inserts the substep at the good index depending on its position
     * @param {type} substep
     */
    this.insertSubsteps = function(substep) {
        var inserted = false;
        var ite = -1;
        var sPosition = substep.getPosition();

        while (!inserted && ite < this.substeps.length - 1) {
            // inserts before the first position > substepsID
            if (this.substeps[ite + 1].getPosition() > sPosition) {
                this.substeps.splice(ite + 1, 0, substep);
                inserted = true;
            }
            ite++;
        }
        
        if (!inserted) {
            this.substeps.push(substep);
        }
    };
    
    /**
     * Updates all substeps when they exist or creates them if needed.
     * Indeed, the server is only sending info on currently displayed steps.
     * So in some cases (conditional steps), we can have occasionnaly new steps
     * @param {array} substeps data for substeps
     */
    this.mergeAndUpdate = function(substeps){
        for (var s in substeps) {
            var substep = substeps[s];
            var position = substep.params.position;
            var dispatch = CONFIGURATOR.ELEMENTS.dispatch;
            
            var associatedSubstep = this.containsSubsteps(position);

            if (associatedSubstep) {
                associatedSubstep.update(substep);
            } else {
                // new elements, creating it !
                // parent is current element
                var obj = dispatch.getAssociatedObject(substep.params.element_type);
                
                if (obj && typeof(obj) !== 'string') {
                    obj = new obj(substep, this);
                    this.insertSubsteps(obj);
                } else {
                    console.log("Unable to init object: ");
                    console.log(substep);
                }
            }
        }
    };
    
    /**
     * Hides the current HTML Element. If the current element has children,
     * hides them as well. An hidden element will stay hidden
     */
    this.hideAll = function() {
        this.substeps.forEach(function(substep){
            substep.hideAll();
        });

        // FIX : cacher le container des étapes donne un effet de scroll top non ergonomique pour l'utilisateur
        if (typeof this.HTMLElement !== 'undefined' && this.HTMLElement.attr('id') !== 'configurator_block') {
            this.hide();
        }
    };

    this.showAll = function() {
        this.substeps.forEach(function(substep){
            substep.showAll();
        });

        this.show();
    };

    this.bind = function() {
        this.substeps.forEach(function(substep){
            substep.bind();
        });
    };

    this.unbind = function() {
        this.substeps.forEach(function(substep){
            substep.unbind();
        });
    };

    /**
     * Returns operation from element and all its children
     * @return {array} operations
     */
    this.getOperations = function () {
        /* var dropzone_selected = $('#dmviewer2d-dropzone-' + this.step.params.id + ' .dmviewer2d-dropzone-item-selected img');
         if (dropzone_selected.length > 0) {
             var positions = [];
             dropzone_selected.each(function (i, item_selected) {
                 positions.push({
                     option: $(item_selected).data('option-id'),
                     position: $(item_selected).parent().parent().data('position')
                 });
             });
             this.operations = [{
                 action: "vierwer2DDropzonePosition",
                 step: this.step.params.id,
                 positions: positions
             }];
         }*/
        
        /**
         * @todo: A revoir à mettre dans module 2D
         */
        var op = $.merge([], this.operations);

        this.substeps.forEach(function (substep) {
            op = $.merge(op, substep.getOperations());

            var current = op[op.length - 1];
            if (current) {
                var dropzone_selected = $('#dmviewer2d-dropzone-' + current.step + ' .dmviewer2d-dropzone-item-selected img')
                    .filter(function () {
                        return $(this).data("option-id") === 'step_option_' + current.step + '_' + (current.option);
                    });
                if (dropzone_selected.length > 0) {
                    current.dropzone_positions = [];
                    dropzone_selected.each(function (i, item_selected) {
                        current.dropzone_positions.push({
                            option: $(item_selected).data('option-id'),
                            position: $(item_selected).parent().parent().data('position')
                        });
                    });
                }

                op[op.length - 1] = current;
            }
        });

        return op;
    };

    /**
     * Called by one of its children. Allows children to notify when they want
     * to change their state. If these changement is validated by the current object,
     * we follow up the information.
     * @param {type} id     substeps ID, MUST match one of this.susbteps' key
     * @param {type} state  as defined in baseElement.js
     * return {boolean}     TRUE if operation is allowed, FALSE otherwise
     */
    this.notify = function(id, state) {
        console.log("I am " + this.step.params.element_type + " and i just received (" + id + ", " + state + ")");
        var allowed = this.validateChildAction(id, state);

        return allowed && this.parent.notify(id, state);
    };
    
    
    /**
     * Validate child action. When a child wants to go to the state 'state', we
     * have to validate these changement. This methods allows to do so.
     * @param {type} id     child's ID
     * @param {type} state  child's state
     * @returns {Boolean}   true if action is authorized, false otherwise
     */
    this.validateChildAction = function(id, state) {
        var valid = false;

        switch (state) {
            case this.STATE.INACTIVE:
                valid = (this.step.params.required === "0");
                break;
            case this.STATE.ACTIVE:
                valid = true;
                break;
                
            default:
                console.log("validateChildAction baseGroupElement invalid state");
                break;
        }

        return valid;
    }; 

    this.init = function(step, parent){
        // by default, we are assuming that a 'base group element' is a step
        // we need to bind before calling to super because baseElement is 
        // calling show !
        // then mergeAndUpdate initialize children that may want to access
        // such element
        this.initDOMLinks(step, parent);
        
        Super.init.call(this, step, parent);

        
        //
        //  Contains subteps objects. IDs correspond to substep id
        //  in order match them with future events and/or update
        //  Otherwise we wouldn't be able to send futher data to the 
        //  good substeps
        //
        this.substeps = [];
        this.mergeAndUpdate(step.substeps);
        this.updateState();
    };

    this.update = function(data) {
        Super.update.call(this, data);
        
        this.mergeAndUpdate(data.substeps);
        this.updateState();
    };
    
    this.updateState = function() {
        var foundActive = false;
        var i = 0;
        // look for an active child, when found, stop research
        while (!foundActive && i < this.substeps.length) {
            var substep = this.substeps[i];
            if (this.isVisibleAndActive(substep)) {
                this.current = substep.getID();
                foundActive = true;
            }
            i++;
        }
                
        if (foundActive) {
            this.state = this.STATE.ACTIVE;
        } else {
            this.state = this.STATE.INACTIVE;
        }
    };
    
    /**
     * Send actions contained in params.actions on if must be
     */
    this.sendActions = function() {
        Super.sendActions.call(this);
        // send data only if visible and active
        if (this.isVisibleAndActive(this)) {
            this.substeps.forEach(function(substeps) {
                substeps.sendActions();
            });
        }
    };

    if (step) {
        this.init(step, parent);  
    }
};

CONFIGURATOR.ELEMENTS.BaseGroupElement.prototype = new CONFIGURATOR.ELEMENTS.BaseElement;