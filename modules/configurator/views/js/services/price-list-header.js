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
 * Handles modal that manipulates headers of a price list.
 * 
 * Behaviour very close to AngularJS with data structure 'bind' to html
 * Want to make it better ? Use AngularJS
 */
CONFIGURATOR.PriceListHeader = (function($){
    
	var self = {};

        /**
         * Configuration object given through init()
         * Contains: 
         *      dataSRC             Input where data are stored (Prestashop's input)
         *      selectorShow        Button which triggers the popup
         *      selectorPanel:      Panel to show and to work with
         *      selectorAddHeader:  Add header action
         *      selectorSaveHeader: Save current configuration
         *      row:                Row element
         *      messageDeleteRow:   Message displayed to confirm row's deletion
         */
        var conf;
        
        /**
         * Row's data, contains all element displayed in table
         * Contains:
         *      id:                 Element's ID, allows to order them
         *      name:               Name of the header
         *      size:               Its size (colspan) 
         */
        var DATA_DEFAULT = [
            {id: 1, name: '', size: 1},
        ];
        
        var data;
        
        
        /**
         * Will contain the DOM element to show a row
         * This element will be duplicate as many times as necessary
         * to display all elements in 'data' array
         */
        var row;
        
        /**
         * 'isValid' corresponds to the integrity of 'data' array
         * When all elements fit the requirements, value is 'TRUE', 'FALSE'
         * otherwise
         */
        var isValid = false;
        
        /**
         * Event binded to a 'delete' button on each row.
         * After confirmation, delete the current row
         */
        var deleteRow = function(event){
                var src = event.target || event.srcElement;

                var id = $(src).closest('tr').attr('data-id');
                var answer = confirm(conf.messageDeleteRow);
                if(answer){
                        data.splice(id, 1);
                        updateRows();
                }else{
                        return false;
                }
        }
        
        /**
         * Event binded to a 'add header' element
         * When triggered, add an empty row to the data
         */
        var addRow = function(){
                data.push({id: data.length, name: '', size: ''});
                updateRows();
        }
        
        /**
         * Given a domElt row and its future binded data
         * Associates all elements (both Events and data)
         */
        var associate = function(domElt, elt){
                domElt.attr('data-id', elt.id);
                var nameDOM = domElt.find('.header-name');
                nameDOM.val(elt.name);
                nameDOM.on('change', function(){
                        var name = nameDOM.val();
                        data[elt.id].name = name;
                        checkingIntegrity();
                });
                
                var sizeDOM = domElt.find('.header-size');                
                sizeDOM.val(elt.size);
                sizeDOM.on('change', function(){
                        var size = sizeDOM.val();
                        data[elt.id].size = size;
                        checkingIntegrity();
                });
                
                domElt.find('.header-delete-action').on('click', deleteRow);
        }
        
        /**
         * Updates both data elements and DOM
         * Override IDs in order to keep them contiguous
         * Deletes all displayed elements and adds them all again
         * Allows to take in consideration update, add, delete etc
         */
        var updateRows = function(){
                var tbody = $(conf.selectorPanel + ' tbody');
                tbody.empty();
                data.forEach(function(elt, id){

                        // overide IDs
                        elt.id = id;

                        var current = row.clone();
                        associate(current, elt);
                        tbody.append(current);

                });

                checkingIntegrity();
        }
        
        /**
         * Validates data's integretigy and updates 'isValid' value.
         * Checks: 
         *      Name:   if not empty
         *      Size:   if not empty and typeof int
         */
        var checkingIntegrity = function(){
                isValid = true;
                data.forEach(function(elt){
                        if(elt.name === ''){
                                isValid = false;
                        }

                        if(elt.size === '' ||
                            isNaN(parseInt(elt.size, 10))){
                                isValid = false;
                        }
                });

                updateSaveState();
        }
        
        /**
         * Update state of HTML save button
         * isValid === TRUE => enable
         * isValid === FALSE => disable
         */
        var updateSaveState = function(){
                var save = $(conf.selectorSaveHeader);
                save.attr('disabled', !isValid);
        }
        
        /**
         * Binds elements
         */
        var bind = function(){
                /**
                 * Add row operation
                 */
                $(conf.selectorAddHeader).on('click', function(){
                        addRow();
                        checkingIntegrity();
                        return false;
                });

                /**
                 * Save headers operation
                 */
                $(conf.selectorSaveHeader).on('click', function(e){
                        e.preventDefault();

                        $(conf.dataSRC).val(JSON.stringify(data));
                        $(conf.selectorPanel).modal('hide');
                        return false;
                });
                
                /**
                 * Display modal operation
                 */
                $(conf.selectorShow).on('click', function(e){
                        e.preventDefault();
                        self.update();
                        $(conf.selectorPanel).modal();
                        return false;
                })
        }
        
        /**
         * Init modules
         */
        self.init = function(config){
                conf = config;

                row = $(config.row);
                row.detach();
                bind();
                
                self.update();
        }
        
        /**
         * Update input's value in which we store data
         * (input provided by prestashop)
         */
        self.update = function(){
                var incomingData = $(conf.dataSRC).val();

                if(incomingData !== ''){
                        data = JSON.parse(incomingData);
                }else{
                        // copy DATA_DEFAULT
                        data = [$.extend(true, {}, DATA_DEFAULT[0])];
                }
                updateRows();
        }
        
        
	return self;
})(jQuery);