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

(function () {
    var IO_MODULE = CONFIGURATOR.IO;

    //
    // Overriding IO modules in order to translate the parameters on-the-fly
    //

    CONFIGURATOR.IO = function (setup) {

        this.id_configurator_quantity = '#quantity-configurator';

        this.setup = {};

        this.init = function (setup) {
            this.setup = setup || this.setup;
            this.io = new IO_MODULE(setup);

            this.bridge = CONFIGURATOR.bridge();

        };


        this.send = function (operations, done) {
            console.log("Using io_bridge !");
            done = done || this.setup.done;

            var data = {
                ajax: 1,
                submitUpdateOption: 1,
                operations: operations,
                qty: $(this.id_configurator_quantity).val()
            };

            this.sendData(data, done);
        };

        this.sendSharedStep = function (operations, done) {
            done = done || this.setup.done;
            var data = {
                ajax: 1,
                submitUpdateOption: 1,
                operations: operations,
                qty: $(this.id_configurator_quantity).val(),
                resetSharedStep: 1
            };
            this.sendData(data, done);
        };

        this.sendData = function (data, done) {
            var self = this;
            console.log("Sending: ");
            console.log(data);
            this.io.send(data, function (data) {
                $('.steps-bottom-buttons button').attr('disabled', false);

                var newData;
                if (data && !data.errors) {
                    newData = self.translate(data.detail, data.steps_errors, data.steps_infos, data.steps_info_text, data.tabs_status);

                    newData = self.bridge.translateAnswer(data, newData);
                } else {
                    newData = data;
                }

                console.log("Data once translated");
                console.log(newData);
                done(newData);
            });
        };

        this.translate = function (data, errors, infos, infosText, tabs_status) {
            var substeps = this.bridge.translateParams(data, errors, infos, infosText);
            var newStep = {
                params: {
                    id: 0
                },
                substeps: substeps,
                errors: [],
                infos: [],
                infosText: null,
                tabs_status: tabs_status
            };
            // end tricky translation from old to new version
            return newStep;
        };

        if (setup) {
            this.init(setup);
        }
    };


})();