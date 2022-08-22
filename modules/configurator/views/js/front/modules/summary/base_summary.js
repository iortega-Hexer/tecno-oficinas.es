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
 * Class that handles a right panel summary ('Votre configuration')
 * Allows to :
 *      - update all content as needed after a AJAX request by the main module
 *      - update the circle progress
 * @param params    Initialisation parameters
 *                      panel_query     Query to select the targeted panel
 *                      progress        See progressBar.js
 * @param callback  Must be called after the module ended its initialisation
 *                  Usefull when module needs to make async tasks
 */
CONFIGURATOR.MODULES.BaseSummary = function(params, callback) {

    var Super = Object.getPrototypeOf(this);
    
    this.params;

    this.HTMLElement;

    this.progressBar;
    
    /**
     *  Button of add product in cart
     */
    this.query_configurator_form_add_button = '#form_add_configurator_to_cart';
    this.query_configurator_current_button_label = '#current_configurator_to_cart';
    this.query_configurator_wait_button_label = '#wait_configurator_to_cart';
    this.query_configurator_add_to_cart_btn_modal = '#form_add_configurator_to_cart_modal';
    this.query_configurator_add_to_cart_btn = '#add_configurator_to_cart';
    this.query_configurator_confirmation_checkbox = '#configurator_confirmation_checkbox';

    this.init = function(params, callback) {
        Super.init.call(this, params);
        
        this.HTMLElement = $(params.panel_query);
        this.progressBar = new CONFIGURATOR.MODULES.ProgressBar(params.progress);
        this.initButtonAddtoCart();
        callback();
    };

    this.canStartScroll = function() {
        var WinHelper = CONFIGURATOR.WindowHelper;
        return !(WinHelper.isMobile() || WinHelper.isTablet()) && !this.params.contentOnly;
    };

    this.initButtonAddtoCart = function () {
        var self = this;
        
        $(self.HTMLElement).on('submit', self.query_configurator_form_add_button, function (event) {
            $(self.query_configurator_current_button_label).hide();
            $(self.query_configurator_wait_button_label).show();
            $(self.query_configurator_form_add_button).find('button[type=submit]').attr('disabled', 'disabled');
        });
        $(self.HTMLElement).on('submit', self.query_configurator_add_to_cart_btn_modal, function (event) {
            event.preventDefault();
            if (!$(self.query_configurator_confirmation_checkbox).length || $(self.query_configurator_confirmation_checkbox).is(':checked')) {
                $(self.HTMLElement).find(self.query_configurator_add_to_cart_btn).click();
            }
        });
    };

    this.initScroll = function() {
        var self = this;
        this.scroll = new CONFIGURATOR.ScrollFix($);
	this.scroll.init(this.params.panel_query, {
            marginTop: 35,
            removeOffsets: true,
            limit: function() {
                return $('.page-product-box').first().offset().top - $(self.params.panel_query).outerHeight(true);
            }
        });
    };

    /**
     * Replaces the current content on the right panel by the provided one
     * @param {Object} params    Updates
     */
    this.handle = function(params) {
        this.HTMLElement.html(params.previewHtml);
        this.progressBar.launch(params.progress.end);
    };
    
    this.reset = function() {
        // nothing to do on reset for summary panel
        // as it will be changed after server's response
    };

    if (params) {
        this.init(params, callback);
    } 
};

CONFIGURATOR.MODULES.dispatch.registerObject('base_summary', CONFIGURATOR.MODULES.BaseSummary);
CONFIGURATOR.MODULES.BaseSummary.prototype = new CONFIGURATOR.MODULES.BaseModule;
