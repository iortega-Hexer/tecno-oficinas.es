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

/**
 * All configurator's steps can be manipulate as one 'big step' containing
 * several smaller steps. That's why Front extends BaseGroupElement in order
 * to take advantage of all methods such as bind, unbind, show, hide etc
 * @param {type} step   Data before refactoring
 * @returns {}
 */
CONFIGURATOR.Front = function(step){
    var Super = Object.getPrototypeOf(this);
    
    this.modules = [];

    this.queryError = '> div > div.errors';

    // we are at the hierarchy's root
    this.notify = function(id, state) {
        console.log("Retrieving operations: ");
        var operations = this.getOperations();
        console.log(operations);


        //if (operations.length > 0) {
        // corresponding bind is in this.update method
        this.unbind();
        this.beforeIO();
        this.io.send(operations);
        //}

        return true;
    };

    this.changeSharedStep = function() {
        var self = this;
        var operations = this.getOperations();
        this.unbind();
        this.beforeIO();
        this.io.sendSharedStep(operations, self.callBackIO());
        return true;
    };
    
    /**
     * Returns a callback method for io purposes
     * @returns {Function}
     */
    this.callBackIO = function() {
        var self = this;
        
        return function(data) {
            self.update(data);
        };
    };

    /**
     * Method called just before calling this.io.send
     */
    this.beforeIO = function() {
        // loading gif
        $(this.step.params.queryLoading).addClass('loading');
        // disable form input and select
        this.disableFormFields(true);
    };

    /**
     * First method to be called in the callback for this.io.send
     */
    this.afterIO = function() {
        $(this.step.params.queryLoading).removeClass('loading');
        // enable form input and select
        this.disableFormFields(false);
        this.refreshSharedStep(this.step);
    };
    
    this.disableFormFields = function(status) {
        $('#configurator_block input').attr('disabled', status);
        $('#configurator_block select').attr('disabled', status);
    };

    this.refreshSharedStep = function(steps) {
        for (var i in steps.substeps) {
            var step = steps.substeps[i];
            if(typeof step.params.disabled !== 'undefined' && step.params.disabled === 1) {
                $('#configurator_block #step_'+step.params.id+' input').attr('disabled', true);
                $('#configurator_block #step_'+step.params.id+' select').attr('disabled', true);
            } else {
                $('#configurator_block #step_'+step.params.id+' input').attr('disabled', false);
                $('#configurator_block #step_'+step.params.id+' select').attr('disabled', false);
            }
        }
    };
    
    this.update = function(data) {
        // Set step list height
        $('#configurator_block .step_list').height($('#configurator_block .step_list').height());

        this.afterIO();
        this.refreshSharedStep(data);
        if (data.infos && data.infos.length) {
            this.step.infos = data.infos;
            this.showInfos();
        }
        if (data.errors && data.errors.length) {
            this.step.errors = data.errors;
            this.showErrors();
        } else {
            // first, we hide all elements as update will reveal elements
            // step by step when they are 'reached' by the update
            this.hideAll();
            this.hideAccordions();
            this.resetModules();

            this.updateModules(data.actions);
            this.step = data;
            
            Super.update.call(this, data);          
        }
        
        this.bind();
        this.progressiveDisplay();
        this.sendActions();
        this.general.displayActionTab();
        this.general.displayActionAccordion();
        this.general.refreshCartPreviewButtons();
        this.general.refreshTabsStatus(data.tabs_status);

        // Reset default step list height
        $('#configurator_block .step_list').height('auto');
    };
    
    this.appendError = function(error) {
        var alertClass;
        switch (error.type) {
            case 'ERROR' :
                alertClass = 'alert-danger';
                break;
            case 'WARNING':
            default:
                alertClass = 'alert-warning';
                break;
        }

        var err = $('<div></div>', {class: 'alert ' + alertClass});
        var title = $('<p></p>', {text: error.title});
        var body = $('<p></p>', {text: error.message});

        err.append(title).append(body);
        this.getHTMLElement().find(this.queryError).append(err);
    };
 
    /**
     * Implements the progressive display option set in backoffice
     * when progressive_display is enabled, we only show active elements until
     * we found the first inactive one
     */
    this.progressiveDisplay = function() {
        if (this.step.params.progressive_display) {
            var foundFirst = false;
            var self = this;
            this.substeps.forEach(function(substep) {
                if (substep.getVisibleState() === self.STATE.VISIBLE) {
                    if (foundFirst) {
                        substep.hideAll();
                    }else if(substep.getState() === self.STATE.INACTIVE) {
                        foundFirst = true;
                    }
                }
            });            
        }
    };

    this.hideAccordions = function() {
        console.log('hideAccordions');
        $('.configurator-card').hide();
    };
    
    /**
     * Dispatchs each entry to the corresponding module according to the 'element_type' field
     * of each entry
     * @param {array} data  data to send to modules. each entry MUST have a element_type property
     *                      in order to route the data.
     */
    this.updateModules = function(data) {
        // dispatch actions for each module
        for (var i in data) {
            var d = data[i];
            var element_type = d.element_type;

            if (this.modules[element_type]) {
                this.modules[element_type].handle(d);
            } else {
                console.log("Cannot handle request for " + element_type);
            }
        }
    };
    
    this.resetModules = function() {
        for (var p in this.modules) {
            var module = this.modules[p];
            module.reset();
        }
    };
    
    this.init = function(step) {
        this.hideAccordions();

        /**
         * @author Matthieu Deutscher
         * @todo: Comprendre l'intérêt du initInternal
         * Rustine ici
         */
        this.initModules(step, function (){});
        
        var self = this;
        //var initInternal = function() {
            Super.init.call(self, step);

            // init IO
            var setup = {
                url: '',
                done: self.callBackIO()
            };
            self.io = new CONFIGURATOR.IO(setup);

            // init general behaviour
            self.general = new CONFIGURATOR.MODULES.General();
            self.general.showAll();
            self.general.eventAction();

            self.refreshSharedStep(step);

            // bind events
            self.bind();
            self.progressiveDisplay();
            self.sendActions();

            this.general.refreshTabsStatus(step.tabs_status);
        //};

        //this.initModules(step, initInternal);
    };

    /**
     * Init modules. Creates all panels present in modules array
     * @param {Object} step       
     * @param {Function} callback Function to call when all modules are initialised
     */
    this.initModules = function(step, callback) {

        /**
         *  Wait for every module until calls 'callback' method
         * @type Function
         */
        var waitForIt = function() {
            var left = Object.keys(step.modules).length;
            return function() {
                left--;
                if (left <= 0) {
                    callback();
                }
            };
        };
        var wait = waitForIt();

        var dispatch = CONFIGURATOR.MODULES.dispatch;
        
        this.modules = [];
    
        for (var key in step.modules) {
            var moduleConf = step.modules[key];
            var obj = dispatch.getAssociatedObject(moduleConf.element_type);
            
            // security mostly for debug purpose as in production all modules must be
            // available
            if (obj && typeof(obj) !== 'string') {
                this.modules[key] = new obj(moduleConf, wait);
            } else {
                console.log(obj);
            }
        }
    };
    
    this.initDOMLinks = function(step, parent) {
        this.HTMLElement = $('#configurator_block');
    };

    if (step) {
        this.init(step);
    }
};

CONFIGURATOR.Front.prototype = new CONFIGURATOR.ELEMENTS.BaseGroupElement;