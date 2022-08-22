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

CONFIGURATOR.bridge = function(){
        
    var self = {
        zIndex: 100,
        /**
         * Concats arguments and separates them with '_'. With the
         * resulting ID, makes a Jquery research and returns the results
         * Example: 
         *      - getStep() -> return
         *      - getStep('arg1') -> $('#arg1')
         *      - getStep('arg1', 'arg2') -> $('#arg1_arg2')
         * @returns JQuery result's
         */
        getStep: function() {
            if (arguments.length === 0) return;
            var query = '#';
            for (var i = 0; i < arguments.length-1; i++) {
                query += arguments[i] + '_';
            }
            query += arguments[arguments.length-1];
            return $(query);
        },
        /**
         * "Aucun" element for HTML select element when the step is not required
         * @returns params for select_option element
         */
        getSelectOptionNone: function() {
            return{params: {
                        element_type: "select_option",
                        id: "0",
                        name: none,         // global variable defined in product.tpl
                        value: "value",
                        selected: 1,
                        display_amount: "",
                        position: -1

                    },
                    substeps: [],
                    errors: [],
                    actions: [],
                    infos: [],
                    infosText: null
                };
        },
        /**
         * Quantity input element. The one used for radio,checkbox and select
         * with quantity
         * @param {type} id
         * @param {type} selected
         * @param {type} quantity
         * @param {type} position
         */
        getQuantityInput: function(id, selected, quantity, position) {
            return {
                params: {
                        id: id,
                        name: '',
                        value: quantity,
                        selected: selected,
                        element_type: 'number_quantity_input',
                        position: position

                    },
                    substeps: [],
                    errors: [],
                    actions: [],
                    infos: [],
                    infosText: null
                };
        },
        /**
         * Transform select > optgroup > option into select > option
         * @param {type} htmlElt
         * @param {type} id
         * @returns {undefined}
         */
        removeOptGroup: function(htmlElt, id) {
            // remove optgroup
            if (htmlElt.find('optgroup').length) {
                var opt = htmlElt.find('select optgroup option').clone();
                htmlElt.find('optgroup').remove();
                // only one remaining option which is the default one when nothing is selected
                htmlElt.find('option').prop('disabled', 'disabled');
                htmlElt.find('select').prop('id', 'step_option_' + id);
                htmlElt.find('select').append(opt);
            }
        },
        translateOptions: function(stepId, oldOptions, stepPosition){
            var options = [];

            for (var o in oldOptions) {

                var oldOption = oldOptions[o];
                var position = stepPosition + "," + (("00000" + oldOption.id).slice(-5));
                
                var option = {
                    params: {
                        id: oldOption.id,                           // BD's ID of the option
                        name: oldOption.name,                       // Name as displayed on top of each step
                        value: oldOption.value,                     // Options value, if set to false means no quantity ?
                        selected: oldOption.selected,               // Whether this option is selected or not
                        display_amount: oldOption.display_amount,   //
						display_reduc: oldOption.display_reduc,	//
                        disabled: oldOption.disabled,
                        pos: oldOption.position || 0,
                        position: position                                 // position within the step, as there is no position for options
                                                                    // in old version, we use the index in array.
                    },
                    substeps: [],                   // Children's step, a step can contain several substeps  
                    errors: [],                     // fill if step contains errors
                    actions: [],                     // contains all action which can be send by elements to its parent (layers update etc)
                    infos: [],
                    infosText: null
                };
                
                var htmlElt = self.getStep('step', 'option', stepId, option.params.id);
                htmlElt.attr('data-position', position);
                //console.log(htmlElt);
                
                //get amount if one and retrieve sign
                //especially used for changing value (% from price etc)
                var amount = htmlElt.find('.label.label-default');
                if (amount.length > 0) {
                    // value can be either the right amount or
                    // (int) 0 or '' (empty string) when not set
                    // Would be great to always put the same answer for default
                    // value ;)
                    var display_amount = option.params.display_amount;
                    if (display_amount === null || display_amount === '' || display_amount === 0 || display_amount.length === 0) {
                        option.params.display_amount = '';
                    } else {
                        var sign = '+';
                        // same condition as in the original front.js file
                        if (isNaN(option['display_amount']) && display_amount.indexOf('-') === 0) {
                                sign = '';
                        }

                        option.params.display_amount = sign + ' ' + display_amount;
                    }
                }
				
				// reduc
				var reduc = htmlElt.find('.label.label-danger.reduc');
                if (reduc.length > 0) {
                    // value can be either the right amount or
                    // (int) 0 or '' (empty string) when not set
                    // Would be great to always put the same answer for default
                    // value ;)
                    var display_reduc = option.params.display_reduc;
                    if (display_reduc === 0 || display_reduc.length === 0) {
                        option.params.display_reduc = '';
                    } else {
                        var sign = '+';
                        // same condition as in the original front.js file
                        if (isNaN(option['display_reduc']) && display_reduc.indexOf('-') === 0) {
                                sign = '';
                        }

                        option.params.display_reduc = sign + ' ' + display_reduc;
                    }
                }
                
                var element_type = '';
                var opQuantity = undefined;
                if (htmlElt.find('input').length > 1) {
                    // case of quantity
                    // we have an inpput for checkbox and one for quantity
                    opQuantity = self.getQuantityInput(oldOption.id + 1,
                        oldOption.selected, (oldOption.selected === 1) ? oldOption.qty : 0,
                        o + 1);

                    htmlElt.find('input[type=text]').prop('type', 'number');
                    opQuantity.params.min = 0;
                }

                if(htmlElt.find('input').length) {
                    if (htmlElt.find('input[type=checkbox]').length) {
                        if (htmlElt.hasClass('colortexture') ||
                            htmlElt.hasClass('custom')) {
                            element_type = 'choice_multiple_texture';
                        } else {
                            element_type = 'choice_multiple';
                        }
                    } else if (htmlElt.find('input[type=radio]').length) {
                        if (htmlElt.hasClass('colortexture') ||
                            htmlElt.hasClass('custom')) {
                            element_type = 'choice_simple_texture';
                        } else {
                            element_type = 'choice_simple';
                        }
                    } else if (htmlElt.find('input[type=email]').length) {
                        element_type = 'email_input';
                    }else if(htmlElt.find('input[data-type=datePicker]').length){
                        element_type = 'date_input';
                    }else if(htmlElt.find('input[data-type=ral_input]').length){
                        element_type = 'ral_input';
                    } else {
                        var input = htmlElt.find('input');
                        // handling input text and number
                        if ((input.data('min') && !isNaN(input.data('min'))) || (input.data('max') && !isNaN(input.data('max')))) {
                            // assuming for now that if we have a data-min attribute
                            // of type 'int' then we have a regular pricelist input
                            // with only numbers allowed
                            input.prop('type', 'number');
                            input.prop('step', 'any');
                            option.params.min = parseFloat(oldOption.min); // parseInt(oldOption.min, 10);
                            option.params.max = parseFloat(oldOption.max); // parseInt(oldOption.max, 10);
                            element_type = 'number_input';
                        } else {
                            element_type = 'text_input';
                        }

                        if (input.data('force') === 1) {
                            /**
                             * @todo: Ecrire de nouveau cette partie pour faire une généralité avec tous les éléments pas seulement les input
                             */
                            option.params.readonly = true;
                            input.prop('readonly', true);
                        }
                    }
                } else if (htmlElt.find('textarea').length) {
                    element_type = 'textarea_input';
                } else if (htmlElt.find('div[data-type=slider]').length) {
                    var slider = htmlElt.find('div[data-type=slider]');
                    // handling input text and number
                    if ((slider.data('min') && !isNaN(slider.data('min'))) || (slider.data('max') && !isNaN(slider.data('max')))) {
                        option.params.min = parseFloat(oldOption.min, 10);
                        option.params.max = parseFloat(oldOption.max, 10);
                        element_type = 'slider_input';
                    }
                } else if (htmlElt.find('select').length) {
                    element_type = 'select';

                    //
                    // there is no JSON in the previous version for element in price list
                    // displayed as select. So we have to parse the current DOM and create
                    // it 
                    //
                    htmlElt.find('select option').each(function(id, elt) {
                        elt = $(elt);
                        
                        // skipping default value
                        if (id === 0) {
                            elt.prop('disabled', 'disabled');
                            return;
                        }
                        
                        // skipping "Aucun" element
                        if (elt.val() === "-1") {
                            return;
                        }

                        elt.prop('id', 'option_' + stepId + '_' + id);
                        
                        var opt = {
                                params: {
                                    element_type: "select_option_pricelist",
                                    id: oldOption.id,
                                    name: elt.text(),
                                    value: elt.text(),
                                    selected: option.params.value === elt.text(),
                                    display_amount: "",
                                    position: id
                                   
                                },
                                substeps: [],
                                errors: [], 
                                actions: [],
                                infos: [],
                                infosText: null
                            };
                        
                        //console.log(opt);
                        option.substeps.push(opt);
                    });
                    
                    option.params.id = stepId;
                    option.params.required = oldOption.required;

                    // if not required, add a "Aucun" option
                    if (option.params.required === "0") {
                        var opt = self.getSelectOptionNone();
                        option.substeps.splice(0, 0, opt);
                    }                    

                    
                } else if (htmlElt.get(0) && 
                            (htmlElt.get(0).tagName === 'OPTGROUP' ||
                            htmlElt.get(0).tagName === 'OPTION')) {
                    console.log('optgroup or option');
                } else {
                    var htmlParent = self.getStep('step', stepId);
                    //htmlParent.find('select').parent().attr('data-position', o);
                    if (htmlParent.find('select').length) {
                        element_type = 'select_option';
                    } else {
                        // do not print warning if it's a table (price_list_table step)
                        if (htmlParent.find('table').length === 0) {
                            console.log('Unkown option');
                            console.log(htmlParent);
                        }
                    }
                }
                
                option.params.element_type = element_type;
				
				// Permet de modifier l'option dans un autre module (comme un hook)
                if(CONFIGURATOR.MODULES.CALLBACK) {
                    $.each(CONFIGURATOR.MODULES.CALLBACK, function(callback_name, callback) {
                        if(typeof callback.bridgeTranslateOptions === 'function') {
                            option = callback.bridgeTranslateOptions(oldOption, option);
                        }
                    });
                }
                
                if (opQuantity) {
                    //console.log(position);
                    //console.log(htmlElt);
                    var type;
                    //
                    // if type starts with "choice_simple", simple choice or subclasses
                    if (option.params.element_type.indexOf('choice_simple') === 0) {
                        type = 'choice_simple_quantity';
                    } else {
                        type = 'choice_multiple_quantity';
                    }

                    //
                    // baseQuantity is used as a grouping element that is not bound
                    // to any real element (unlike all other cases so far). It will
                    // provide methods to filter and dispatch content in order to handle
                    // quantity
                    //
                    var baseQuantity = {
                        params: {
                            id: stepId,
                            name: '',
                            element_type: type,
                            action_step: stepId,
                            action_option: oldOption.id,
                            position: position
                        },
                        substeps: [],                   // Children's step, a step can contain several substeps  
                        errors: [],                     // fill if step contains errors
                        actions: [],                     // same as for options
                        infos: [],
                        infosText: null
                    };
                    
                    baseQuantity.substeps.push(option);
                    baseQuantity.substeps.push(opQuantity);
                    options.push(baseQuantity);
                    // adding position to element in order to be found with 'find'
                    // later
                    htmlElt.attr('data-position', position);
                } else {
                    options.push(option);
                }
            }
            
            return options;
        },
        /**
         * Translates oldParams coming from configurator 1.11.0 to the new
         * @param {type} oldParams Old structure
         * @param {Array} errors  Errors from 'steps_errors'
         * @returns {type} New structure
         */
        translateParams: function(oldParams, errors, infos, infosText) {
            oldParams = self.beforeTranslateParams(oldParams);
            // avoid zIndex going to infinite
            // reset here in order to touch all options
            self.zIndex = 100;
            
            var params = [];
            
            for (var s in oldParams) {
                var oldStep = oldParams[s];
                
                var position = ("0000" + oldStep.position).slice(-4);
                // handling step
                var step = {
                    params: {
                        id: oldStep.id,             // BD's ID of the step
                        name: oldStep.name,         // Name as displayed on top of each step
                        required: oldStep.required, // Whether a step is required or not (same meaning as in a form)
                        position: position,          // position in HTML, which can be different from id
                                                    // the position's attribute is the only one we can refert to when we need an order
                        display_step_amount: oldStep.display_step_amount,
                        disabled: oldStep.disabled,
                    },
                    substeps: [],                   // Children's step, a step can contain several substeps  
                    errors: [],                     // fill if step contains errors
                    actions: [],                     // same as for options
                    infos: [],
                    infosText: oldStep.infosText
                };

                if (errors && errors[oldStep.id]) {
                    var mess = {
                        type: "ERROR",
                        title: "",
                        message: errors[oldStep.id]
                    };

                    step.errors.push(mess);
                }

                if (infos && infos[oldStep.id]) {
                    step.infos.push(infos[oldStep.id]);
                }
                
                if (infosText && infosText[oldStep.id]) {
                    step.infosText = infosText[oldStep.id];
                }
                
                var htmlElt = self.getStep('step', step.params.id);
                htmlElt.attr('data-position', position);
                //console.log(htmlElt);
                
                htmlElt.find('.display-step-amount').html(step.params.display_step_amount);
                
                if(parseFloat(oldStep.total_step_amount) === 0.00) {
                    htmlElt.find('.display-step-amount').fadeTo('slow',0);
                } else {
                    htmlElt.find('.display-step-amount').fadeTo('slow',1);
                }
                
                var element_type = '';
                var isYesNo = false;
                if (htmlElt.find('[id^=no_radio]').length) {
                    htmlElt = htmlElt.find('#collapse_' + step.params.id);
                    isYesNo = true;
                } 
                
                if (oldStep.price_list) {
                    // three kinds of price list displayed so far
                    // input, select and table
                    // so we look for such elements and fix element_type accordingly 
                    if(htmlElt.find('input').length) {
                        element_type = 'pricelist_simple_input';
                    }else if(htmlElt.find('select').length) {
                        element_type = 'pricelist_simple_select';

                        // adding required element to substeps as 'select' elements display
                        // 'Aucun' option if not required
                        for (var i in oldStep.options) {
                            oldStep.options[i].required = oldStep.required;
                        }
                    }else if(htmlElt.find('table').length) {
                        element_type = 'pricelist_table';
                    }else{
                        console.log('Unknown price list type !!');
                    }

                    // deleting options that are not linking to a actual DOM element
                    // indeed, for now in the back office it is possible to select more dimension
                    // than necessary. Need to clean them
                    if (element_type !== 'pricelist_table') {
                        for (var i in oldStep.options) {
                            var o = oldStep.options[i];
                            if (self.getStep('option', step.params.id, o.id).length === 0) {
                                delete oldStep.options[i];
                            }
                        }
                    }

                    // handling options
                    step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                    
                } else if (oldStep.use_qty === "1") {
                    // if use_qty = 1 we can select + input or input
                    if (htmlElt.find('select').length) {

                        var opSelect = {
                            params: {
                                    id: oldStep.id,
                                    name: '',
                                    element_type: 'select',
                                    position: position + ',00000'

                                },
                                substeps: [],
                                errors: [],
                                actions: [],
                                infos: [],
                                infosText: null
                            };

                        $(htmlElt.find('select').parent().attr('data-position', opSelect.params.position));

                        element_type = 'select_simple_quantity';

                        // remove optgroup
                        self.removeOptGroup(htmlElt, step.params.id);
                        
                        opSelect.substeps = self.translateOptions(step.params.id, oldStep.options, position);

                        // looking for a selected child and its quantity
                        var childIsSelected = 0;
                        var childQuantity = 0;
                        for (var o in oldStep.options) {
                            var option = oldStep.options[o];
                            if (option.selected) {
                                childIsSelected = 1;
                                childQuantity = option.qty;
                            }
                        }

                        if (step.params.required === "0") {
                            var option = self.getSelectOptionNone();

                            opSelect.substeps.splice(0, 0, option);
                        }

                        var opQuantity  = self.getQuantityInput(oldStep.id + 1,
                                    childIsSelected, (childIsSelected === 1) ? childQuantity : 0,
                                    99);    // WARNIONG: POSITION SHOULD BE UNIQUE AND COMING FROM SERVER

                        step.substeps.push(opSelect);
                        step.substeps.push(opQuantity);

                    } else {
                        if (htmlElt.find('input[type=radio]').length) {
                            element_type = 'choices_simples';
                            // handling options
                            step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                        } else if (htmlElt.find('input[type=checkbox]').length) {
                            element_type = 'choices_multiples';
                            // handling options
                            step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                        } else {
                            // select quantity
                            element_type = 'quantity_multiple';
                        }
                        // handling options
                        step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                    }

                } else {
                    // step contains either radio button or checkbox
                    if (htmlElt.find('input[type=radio]').length) {
                        element_type = 'choices_simples';

                        // handling options
                        step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                    } else if (htmlElt.find('input[type=checkbox]').length) {
                        element_type = 'choices_multiples';

                        // handling options
                        step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                    } else if (htmlElt.find('select').length) {
                        element_type = 'select';


                        // remove optgroup
                        self.removeOptGroup(htmlElt, step.params.id);

                        step.substeps = self.translateOptions(step.params.id, oldStep.options, position);

                        if (step.params.required === "0") {
                            var option = self.getSelectOptionNone();

                            step.substeps.splice(0, 0, option);
                        }

                    } else if (htmlElt.find('input[type=file]').length || oldStep.is_upload) {
                        element_type = 'file_upload';
                    } else if (htmlElt.find('input[type=text]').length || htmlElt.find('input[type=number]').length || htmlElt.find('div[data-type=slider]').length
                        || htmlElt.find('textarea').length || htmlElt.find('input[type=email]').length) {
                        element_type = 'group_inputs';

                        // handling options
                        step.substeps = self.translateOptions(step.params.id, oldStep.options, position);
                    } else if (htmlElt.find('button.dmdesigner-btn-open').length) {
                        element_type = 'designer';
                    } else {
                        console.log(htmlElt);
                        console.log('Unknown type !!');
                    }
                }

                step.params.element_type = element_type;

                // Zone de drop pour la 2D
                if (oldStep.dropzone) {
                    step.params.dropzone = oldStep.dropzone;
                }
                
                if (isYesNo) {
                    // step in a yesNo must have a position within the big step
                    // adding one manually here without overriding children's one
                    // (children's one should be position,9999,0000 and so on)
                    var dummyPosition = position + ',9999';
                    step.params.position = dummyPosition;
                    
                    var elt = self.getStep('step', step.params.id);
                    // remove default behavior
                    elt.find('[id^=no_radio], [id^=yes_radio]').removeAttr('data-toggle');
                    elt.find('.collapse').removeClass('collapse').addClass('in').attr('data-position', dummyPosition);

                    var yesNoStep = {
                        params: {
                            id: oldStep.id,
                            name: oldStep.name,
                            required: oldStep.required,
                            position: position,
                            element_type: 'yes_no',
                            yes_no_value: oldStep.yes_no_value || false
                        },
                        substeps: [step],
                        errors: [],
                        actions: [],
                        infos: [],
                        infosText: null
                    };
                    params.push(yesNoStep);
                } else {
                    params.push(step);
                }
            }

            params = self.afterTranslateParams(params);

            return params;
        },
        beforeTranslateParams: function(params) {
            var p = {};
            for (var s in params) {
                p[params[s]['position']] = JSON.parse(JSON.stringify(params[s]));
                p[params[s]['position']]['options'] = {};
                for (var o in params[s]['options']) {
                    p[params[s]['position']]['options'][params[s]['options'][o]['position']] = params[s]['options'][o];
                }
            }
            return p;
        },
        afterTranslateParams: function(params) {
            for (var s in params) {
                // Permet de modifier l'option dans un autre module (comme un hook)
                if (CONFIGURATOR.MODULES.CALLBACK) {
                    $.each(CONFIGURATOR.MODULES.CALLBACK, function (callback_name, callback) {
                        if (typeof callback.bridgeTranslateStep === 'function') {
                            params[s] = callback.bridgeTranslateStep(params[s]);
                        }
                    });
                }
            }
            return params;
        },
        /**
         * Translates server's respons
         * @param {type} answer     server's respons to translate
         * @param {type} data       part of answer already translated
         * @returns {unresolved}
         */
        translateAnswer: function(answer, data) {
            // moving preview to params as an action for a module
            var summary = {};
            summary.previewHtml = answer.previewHtml;
            
            summary.progress = {};
            summary.progress.start = answer.progress_start;
            summary.progress.end = answer.progress_end;
            summary.element_type = 'base_summary';
            
            data.actions = data.actions || [];
            data.actions.push(summary);
            data.params.progressive_display = progressive_display;
            data.params.queryLoading = "#configurator_preview_buttons";
            return data;
        },
        /**
         * Create init structure by translated parameters and adding
         * globals variables in it and other parameters
         * @param {type} oldParams
         * @returns {Object} new structure with all needed parameters
         */
        translateInit: function(oldParams) {
            var substeps = self.translateParams(oldParams);
            var newStep = {
                params: {
                    id: 0,
                    progressive_display: progressive_display,       // progressive_display option   when set to TRUE, means that all steps are not visible
                                                                    // only the active ones and the first inactive (which corresponds to the first step that the
                                                                    // user did not fill yet
                    queryLoading: '#configurator_preview_buttons'
                },
                substeps: substeps,
                actions: [],
                modules: []
            };
            

            // adding errors div for Front
            var err = $('<div></div>', {class: 'errors'});
            $('#configurator_block > div').first().prepend(err);


            var summary = {};
            
            summary.panel_query = '#configurator_preview_container';
            // add progress_data that is in global namespace
            summary.progress = progress_data;
            summary.progress.progress_query = '#configurator-progress';
            // dispatch ID for summary module (right one)
            summary.element_type =  'base_summary';
            newStep.modules[summary.element_type] = summary;

            // Some theme doen't use contentOnly
            if(typeof contentOnly == 'undefined') {
                var contentOnly = false;
            }
			
			// Permet de modifier l'option dans un autre module (comme un hook)
            if(CONFIGURATOR.MODULES.CALLBACK) {
                $.each(CONFIGURATOR.MODULES.CALLBACK, function(callback_name, callback) {
                    if(typeof callback.bridgeTranslateInit === 'function') {
                        newStep = callback.bridgeTranslateInit(newStep);
                    }
                });
            }

            return newStep;

        }
    };
    
    
    
    return self;
};