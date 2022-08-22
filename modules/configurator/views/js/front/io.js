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

CONFIGURATOR.IO = function(setup) {
    
    this.setup = {};

    this.errorDispatch = CONFIGURATOR.ERRORS.dispatch;

    this.init = function(setup) {
        this.setup = setup || this.setup;
    };
    
    /**
     * parseJSON with a backup plan in case of faillure (same method as in
     * UPack project)
     * @param {type} json_string
     * @returns {Boolean|Object} False if couldn't parse, parsed object otherwise
     */
    this.parseJSON = function(json_string) {
        try {
          var json_parsed = JSON.parse(json_string);
        } catch (e) {
            console.log("Unable to parse content: ");
            return false;
        }
        return json_parsed;
    };
    
    /**
     * Wrap around to send data to the server
     * @param {Object|Array} data   Data to send
     * @param {function} done       Callback method. When provided, overrides
     *                              callback provided at init time
     * @returns {Object}
     */
    this.send = function(data, done) {
        return new Promise(resolve => {
            done = done || this.setup.done;

            var self = this;

            $.ajax({
                'type': 'POST',
                'url': this.setup.url,
                'data': data,
                'success': function (data) {
                    data = self.parseJSON(data);
                    if (!data) {
                        data = self.createGeneralError();
                    }
                    done(data);
                    self.refreshCart();
                    resolve();
                }
            }).fail(function () {
                var data = self.createGeneralError();
                done(data);
                resolve();
            });
        });
    };

    this.refreshCart = function () {
        if(typeof ajaxCart !== 'undefined') { // 1.6
            ajaxCart.refresh();
        } else if (typeof prestashop === 'object' && typeof prestashop.emit !== 'undefined') {  // 1.7
            prestashop.emit('updatedCart');
        }
    };

    /**
     * Creates an empty reponse with errors filled with 'GENERAL' error
     * @returns a data response with errors field filled
     */
    this.createGeneralError = function() {
        var data = {};
        data.errors = [];
        data.errors.push('GENERAL');
        return data;
    };
    
    if (setup) {
        this.init(setup);
    }
};