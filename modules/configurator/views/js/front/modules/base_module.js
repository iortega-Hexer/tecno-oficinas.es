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
CONFIGURATOR.MODULES = CONFIGURATOR.MODULES || {};

/**
 * Base module
 * Methods that all modules should implemente
 * @param params    Init parameter, specific to subclasses
 * @param callback  Must be called after the module ended its initialisation
 *                  Usefull when module needs to make async tasks
 */
CONFIGURATOR.MODULES.BaseModule = function(params, callback) {
    
    this.init = function(params, callback) {
        this.params = params;
        //this.initScroll();
        //this.startScroll();
    };

    /**
     * Starts scroll if allowed to
     */
    this.startScroll = function() {
        var canStart = this.canStartScroll();
        if (canStart) {
            if (this.scroll) {
                this.scroll.start();
            } else {
                console.log("WARNING: Cannot start scroll as it is not initialized !");
            }
        }
    };

    /**
     * Whether the conditions allows to start the scroll
     * @returns {Boolean}
     */
    this.canStartScroll = function() {
        return false;
    };

    /**
     * Initialized this.scroll field
     * with a scroll object which must have a 'start' method
     * @see startScroll
     * @returns {undefined}
     */
    this.initScroll = function() {
        // override by subclasses
    };


    /**
     * Single method to handle action on modules.
     * This method do not have any arguments on purpose. Indeed, we cannot
     * know how many arguments we will need.
     */
    this.handle = function() {
        console.log("Calling on handle");
    };
    
    
    /**
     * Called before massive update of the main module
     */
    this.reset = function() {
        console.log("Calling on reset");
    };
};
