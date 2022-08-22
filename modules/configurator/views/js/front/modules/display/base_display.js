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
CONFIGURATOR.MODULES.DISPLAY = CONFIGURATOR.MODULES.DISPLAY || {};

/**
 * Base display module to handle the left panel (where images are displayed)
 * @param params    Init parameter, specific to subclasses
 * @param callback  Must be called after the module ended its initialisation
 *                  Usefull when module needs to make async tasks
 */
CONFIGURATOR.MODULES.DISPLAY.BaseDisplay = function(params, callback) {
    
    this.canStartScroll = function() {
        var WinHelper = CONFIGURATOR.WindowHelper;
        return !WinHelper.isMobile() && !this.params.contentOnly;
    };

    this.initScroll = function() {
        var self = this;
        this.scroll = new CONFIGURATOR.ScrollFix($);
        this.scroll.init(self.params.element, {
            marginTop: 35,
            removeOffsets: true,
            limit: function() {
                    return $('.page-product-box').first().offset().top - $(self.params.element).outerHeight(true);
            }
        });

    };

    if (params) {
        this.init(params, callback);
        // cannot add callback into init method as it is called by subclasses
        // here we are certain that only base_display element call 'callback'
        // and not one of its subclasses
        callback();
    }
};

CONFIGURATOR.MODULES.dispatch.registerObject('default_display', CONFIGURATOR.MODULES.DISPLAY.BaseDisplay);
CONFIGURATOR.MODULES.DISPLAY.BaseDisplay.prototype = new CONFIGURATOR.MODULES.BaseModule;