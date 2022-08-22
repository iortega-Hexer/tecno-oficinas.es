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

progressBarHandler = {
	id: '',
	start_color: '#00b0ee',
	end_color: '#026799',
	init: function(id, progress_data) {
		this.id = id;
		if(progress_data.start_color && progress_data.end_color){
			this.start_color = progress_data.start_color;
			this.end_color = progress_data.end_color;
		}
		this.launch(progress_data.start, progress_data.end);
	},
	
	launch: function(start, end) {
		var self = this;
		var el = $(self.id);
		if(self.id !== '' && el.length > 0){
			// start and end are percent value 
			if(start === undefined){ start = 0; }
			if(end === undefined){ end = 0; }
			el.circleProgress({
				startAngle: -Math.PI / 2,
				animationStartValue: parseInt(start) / 100,
				value: parseInt(end) / 100,
				size: el.width(),
				lineCap: 'round',
				fill: { gradient: [self.start_color, self.end_color], gradientAngle: Math.PI }
			}).on('circle-animation-progress', function(event, progress, stepValue) {
				$(this).find('strong').html(parseInt(100 * stepValue) + '%');
			});
		}
	}
};

configuratorHandler = {
	// Properties
	detail: {},
	qty: 1,
	progressive_display: 0,
	action: '',
	image_format: '',
	fancybox_image_format: '',
	image_ext: '.jpg',
	visual_rendering: 0,
	width_max_tablet: 992,
        query_inputs_type_radio: 'input[type="radio"]',
        query_inputs_type_checkbox: 'input[type="checkbox"]',
	query_inputs_choice: 'input[type="radio"], input[type="checkbox"]',
	query_inputs_text: 'input[type="text"]',
	query_select_option: 'option[is-select-option]',
	query_inputs_select: 'select',
	query_product_box: '.page-product-box:first',
	query_configurator_block: '#configurator_block > div',
	query_input_option_qty: '.quantity_wanted input',
        query_table_cell: '.table-cell',
	template_preview: '#tmpl_cart_preview',
	id_module: '#module-configurator-default',
	id_block: '#configurator_block',
	id_preview: '#configurator_preview',
	id_step: '#step_',
	id_option: '#step_option_',
	id_yes_radio: '#yes_radio_',
	id_qty: '#quantity-configurator',
	id_progress_bar: '#configurator-progress',
	class_error_step: '.error-step',
	class_option_block: '.option_block',
	class_option_group: '.option_group',
	class_option_input: '.option_input',
	class_step_list: '.step_list',
	class_step_options: '.step_options',
	class_step_group: '.step_group',
	class_footer_container: '.footer-container',
	class_no_radio: '.no_radio',
	class_yes_radio: '.yes_radio',
	class_label_percent: '.percent',
	class_label_multiplier: '.multiplier',
	class_label_total: '.totalprice',
	class_label_pricelist: '.pricelist',
	class_info: '.info',
	class_info_fancybox: '.info-fb',
	class_zoom_fancybox: '.fancybox-inner',
	class_zoom_texture: '.configurator-zoom',
	attr_unique_qty: 'unique-qty',
	open_collapse: false,
	i18n_total_price: '',
	i18n_tax: '',
        newVersion: undefined,
        newElements: [],
	// Literal object
	layers_manager: {},
	// Methods
	init: function() {
		
                if (typeof CONFIGURATOR !== 'undefined') {
			this.layers_manager = CONFIGURATOR.LayersManager || {};
		}
		// progress addJsDef
		this.initProgressBar(progress_data);
		this.bindAll();
		// addJsDef
		this.detail = detail;
                
		this.progressive_display = progressive_display;
		this.action = action;
		this.image_format = image_format;
		this.fancybox_image_format = fancybox_image_format;
		this.visual_rendering = visual_rendering;
		if (total_price_i18n !== 'undefined' && tax_i18n !== 'undefined') {
			// Translations
			this.i18n_total_price = total_price_i18n;
			this.i18n_tax = tax_i18n;
		}
		/**
		 * RENDER CONFIGURATOR
		 */
		this.buildSteps();
	},
	initProgressBar: function(progress_data) {
		progressBarHandler.init(this.id_progress_bar, progress_data);
	},
	bindAll: function() {
		var self = this;
		// Popover
		$(self.class_info).popover({
			html: true,
			placement: 'top',
			trigger: 'click hover'
		});
		$(self.id_block + ' ' + self.class_option_block).popover({
			html: true,
			placement: 'top',
			trigger: 'hover'
		});
		// Fancybox
		$(self.class_info_fancybox).on('click', function(){
			if (!!$.prototype.fancybox){
				$.fancybox.open([
				{
					'openEffect': 'elastic',
					'closeEffect': 'elastic',
					'type': 'inline',
					'autoScale': true,
					'minHeight': 30,
					'content': $(this).data('content')
				}]);
			}
		});
		// Click on color or texture launch click on radio
		$(self.class_option_block).on('click', function(e) {
			var zoom_class_str = self.class_zoom_texture.replace('.','');
			if($(e.target).hasClass(zoom_class_str) || $(e.target).parent().hasClass(zoom_class_str)) {
				return true;
			}
			if ($(e.target).get(0).tagName !== 'INPUT') {
				$(this).find(self.query_inputs_choice).trigger('click');
				self.resetUniform();
			}
		});
		// Click on option launch update, if texture or color, add a class used by css
		$(self.class_step_options + ' ' + self.query_inputs_choice).on('click', function() {
			self.processSelectOptionBlock($(this));
			self.update($(this));
		});
		// Set value in input text, update (EXCLUDE QUANTITY TOO MUCH SPECIFIC)
		$(self.class_step_options + ' ' + self.query_inputs_text + ':not(.qty), ' +
				self.class_step_options + ' ' + self.query_inputs_select).on('change', function() {
			if ($(this).get(0).tagName === 'SELECT') {
				if ($(this).hasClass('select_option')) {
					self.processSelectOption($(this));
				} else {
					self.processSelectValues($(this));
				}
			} else {
				self.processInputTextValues($(this));
			}
		});
		// Click on no_radio, remove options
		$(self.class_no_radio).on('click', function() {
			self.processNoRadio($(this));
		});
		// Input qty click + change
		$(self.query_input_option_qty).on('click', function() {
			$(this).select();
		});
		$(self.query_input_option_qty).on('keyup', function() {
			var element = $(this);
			self.delay(function(){
				if(element.attr(self.attr_unique_qty) !== 'undefined') {
					self.processUpdateUniqueOptionQty(element);
				} else {
					self.processUpdateOptionQty(element);
				}
			},400);
		});
             
                $(self.class_step_options + ' ' + self.query_table_cell).on('click', function(event) {
                        var tdContent = $(event.target).text().trim();
                        if(tdContent.length > 0) {
                            $(self.class_step_options + ' ' + self.query_table_cell).removeClass('selected');
                            $(this).addClass('selected');
                            self.update($(this));                            
                        }
		});
            
		// Zoom on texture
		if (!!$.prototype.fancybox) {
			$(self.class_zoom_texture).on('click', function() {
				var img = $(this).closest(self.class_option_block).find('img');
				if (!!$.prototype.fancybox){
					$.fancybox.open([
					{
						'openEffect': 'elastic',
						'closeEffect': 'elastic',
						'autoScale': true,
						'content': img.clone()
					}]);
				}
			});
		}
	},
	delay: function(callback, ms) {
		clearTimeout(this.timer);
		this.timer = setTimeout(callback, ms);
	},
	lock: function() {
		this.unbindAll();
		$('input,select').not(this.class_no_radio).attr('disabled', true);
	},
	unlock: function() {
		this.bindAll();
		$('input,select').attr('disabled', false);
	},
	unbindAll: function() {
		var self = this;
		$(self.class_step_options + ' ' + self.query_inputs_text).unbind('change');
		$(self.class_step_options + ' ' + self.query_inputs_select).unbind('change');
		$(self.class_step_options + ' ' + self.query_inputs_choice).unbind('click');
		$(self.class_option_block).unbind('click');
		$(self.class_no_radio).unbind('click');
		$(self.query_input_option_qty).unbind('click');
		$(self.query_input_option_qty).unbind('keyup');
                $(self.class_step_options + ' ' + self.query_table_cell).unbind('click');
		$(self.class_zoom_texture).unbind('click');
		$(window).unbind('click');
	},
	buildSteps: function() {
                var self = this;
                // Unbind => No problem to trigger some radio/checkbox manually
		this.unbindAll();
		
                var detail = this.detail;
		if (this.progressive_display) {
                        detail = this.filterDetailProgressiveDisplay(this.detail);
		}
		
		// Hide all steps/options and remove values from inputs
		$(this.class_step_group + ', ' + this.class_option_group).hide();
                $(this.class_step_group).each(function(){
			$(this).removeClass('form-error');
			$(this).removeClass('form-ok');
			$(this).find(self.query_inputs_text).val('');
			$(this).find('select').val('');
                        $(this).find(self.query_inputs_type_radio).prop('checked', false);
                        $(this).find(self.query_inputs_type_checkbox).each(function(id, elt){
                                // in order to keep display from boostrap, we need
                                // to trigger a false 'click' to deselect (css classes)
                                // do not make infinite loop because of the unbindAll
                                // at the beginning of buildSteps
                                if($(elt).is(':checked')){
                                        $(elt).trigger('click');
                                }
                        });
                        $(this).find(self.class_step_options + ' ' + self.query_table_cell).removeClass('selected');                        
                });
                
		// For each step to display
		for (var id_step in detail) {
                        var stepElement = $(this.id_step + id_step);
			stepElement.show();
			this.open_collapse = false;
                        

                        // specific case due to 2D table
                        if(stepElement.has(self.query_table_cell).length > 0){
                                // We are getting the selected cell with the data-value
                                // options. The cell is placed at the intersection of
                                // data-value-1 and data-value-2
                                // WARNING: This solution is sensitive to duplicate value
                                // if multiple cells match (data-value-1, data-value-2), it's
                                // going to be tricky !
                                
                                var options = detail[id_step]['options'];
                                // options array is indexed on option_id... need only
                                // the first two one
                                var keys = Object.keys(options);
                                var option1 = options[keys[0]];
                                var option2 = options[keys[1]];

                                var cellQuery = 'td' + self.query_table_cell + 
                                                      '[data-value-1="' + option1['value'] +'"]' +
                                                      '[data-value-2="' + option2['value'] +'"]';

                                var selectedCell = stepElement.find(cellQuery);

                                selectedCell.addClass('selected');
                        }else{
                                // For each option to display
                                for (var id_option in detail[id_step]['options']) {
                                        var option = detail[id_step]['options'][id_option];
                                        this.processUpdateLabelPrice(id_step, option);
                                        this.processUpdateLabelTotalPrice(id_step, option);
                                        var stepOption = $(this.id_option + id_step + '_' + id_option);
                                        stepOption.show();
                                        // Option is selected
                                        var subquery = this.query_inputs_choice + ', ' + this.query_inputs_text + ', ' + this.query_select_option +', ' + this.query_inputs_select;
                                        var input = $(this.id_option + id_step + '_' + id_option).find(subquery);
                                        if (input.attr('type') === 'text') {
                                                this.processText(id_step, id_option, input);
                                        } else if(input.length > 0 && (input.get(0).tagName === 'OPTION' ||
                                                                        input.get(0).tagName === 'SELECT')) {
                                                this.processOptionSelection(id_step, id_option, input);   
                                        } else {
                                                this.processRadioCheckbox(id_step, id_option, input);
                                        }
                                        if (this.visual_rendering) {
                                                if (option['selected'] && option['layer'] !== null) {
                                                        this.layers_manager.add(
                                                                option['id'],
                                                                detail[id_step]['position']+id_option,
                                                                option['layer']+'-'+this.image_format+this.image_ext
                                                        );
                                                } else {
                                                        this.layers_manager.remove(option['id']);
                                                }
                                        }   
                                }
                        }
                        
			
			// Trigger collapse when step is displayed by yes button
			if (this.open_collapse) {
				$(this.id_yes_radio + id_step).trigger('click');
				this.open_collapse = false;
			}
		}
		this.resetUniform();
		// Bind all event again when build is finished
		this.bindAll();
	},
	processNoRadio: function(radio) {
		var self = this;
		radio.closest('.row').find(self.class_step_options).find(self.query_inputs_choice).each(function() {
			$(this).prop('checked', false);
			self.processSelectOptionBlock($(this));
		});
		this.update(radio, 'resetStep');
	},
	processInputTextValues: function(input) {
		input.parents(this.class_option_group).removeClass('form-error');
		input.parents(this.class_option_group).removeClass('form-ok');
		var min_value = input.data('min');
		var max_value = input.data('max');
		var launchUpdate = true;
		if (min_value !== undefined && max_value !== undefined) {
			var val = input.val();
			if ((val < min_value || val > max_value) && val !== '') {
				launchUpdate = false;
				input.parents(this.class_option_group).addClass('form-error');
			} else if (val !== '') {
				input.parents(this.class_option_group).addClass('form-ok');
			}
		}
		if (launchUpdate) {
			this.update(input);
		}
	},
	processUpdateOptionQty: function(qty_input) {
		var qty = parseInt(qty_input.val());
		var option = qty_input.closest(this.class_option_group);
		
		option.find('.quantity_wanted').removeClass('form-error');
		if (qty <= 0) {
			option.find('.quantity_wanted').addClass('form-error');
		} else if (option.hasClass('selected')) {
			this.update(option.find(this.query_inputs_choice), 'updateWantedQty');
		} else {
			if (option.hasClass('option')) {
				option.find(this.query_inputs_choice).trigger('click');
			} else {
				option.trigger('click');
			}
		}
	},
	processUpdateUniqueOptionQty: function(qty_input) {
		var qty = parseInt(qty_input.val());
		var container = qty_input.closest(this.class_step_group);
		var option = container.find('option:selected');
		
		container.find('.quantity_wanted').removeClass('form-error');
		if (qty <= 0) {
			container.find('.quantity_wanted').addClass('form-error');
		} else if (option.val() !== '') {
			this.update(option, 'updateWantedQty');
		}
	},
	processSelectOption: function(select) {
		var option = select.find('option:selected');
		this.update(option);
	},
	processSelectValues: function(select) {
		this.update(select);
	},
	processOptionSelection: function(id_step, id_option, option_tag) {
                if(option_tag.get(0).tagName === 'OPTION'){
                        var input_qty = option_tag.closest(this.class_step_group).find(this.query_input_option_qty);
                        if (this.detail[id_step]['options'][id_option]['selected']) {
                                this.open_collapse = true;
                                if(!option_tag.is(':selected')) {
                                        option_tag.prop('selected', true);
                                }
                                var qty = this.detail[id_step]['options'][id_option]['qty'];
                                if (qty === 0) {
                                        input_qty.val(1);
                                } else {
                                        input_qty.val(qty);
                                }
                        }    
                }else{
                        if (this.detail[id_step]['options'][id_option]['selected']) {
                               $(option_tag).val(this.detail[id_step]['options'][id_option]['value']);
                        }
                }
	},
	processRadioCheckbox: function(id_step, id_option, input) {
		var input_qty = input.closest(this.class_option_group).find(this.query_input_option_qty);
		if (this.detail[id_step]['options'][id_option]['selected']) {
			if (!input.is(':checked')) {
				input.trigger('click');
			}
                        this.processSelectOptionBlock(input);
			if ($(this.id_yes_radio + id_step).length > 0 && !$(this.id_yes_radio + id_step).is(':checked')) {
				this.open_collapse = true;
			}
			var qty = this.detail[id_step]['options'][id_option]['qty'];
			if (qty === 0) {
				input_qty.val(1);
			} else {
				input_qty.val(qty);
			}
		} else {
                        input_qty.val(0);
		}
	},
	processText: function(id_step, id_option, input) {
		// Input text has value
		if (this.detail[id_step]['options'][id_option]['value']) {
			this.open_collapse = true;
			input.val(this.detail[id_step]['options'][id_option]['value']);
		}
	},
	processUpdateLabelPrice: function(id_step, option) {
		var query = this.id_option + id_step + '_' + option['id'] + ' ';
		var sign = '+';
		if(isNaN(option['display_amount']) && option['display_amount'].indexOf('-') === 0) {
			sign = '';
		}
		if (option['display_amount']) {
			// Specific to select option ...
			if($(query).get(0).tagName === 'OPTGROUP') {
				this.processUpdateSelectOptionPrice($(query).find('option'), sign + ' ' + option['display_amount']);
				return;
			}
			
			$(	query + this.class_label_percent + ', ' + 
				query + this.class_label_pricelist + ', ' +
				query + this.class_label_multiplier
			).html(sign + ' ' + option['display_amount']);
		} else {
			// Specific to select option ...
			if($(query).length > 0 && $(query).get(0).tagName === 'OPTGROUP') {
				this.processUpdateSelectOptionPrice($(query).find('option'), '');
				return;
			}
			$(query + this.class_label_multiplier).html('');
		}
	},
	processUpdateLabelTotalPrice: function(id_step, option) {
		var query = this.id_option + id_step + '_' + option['id'] + ' ';
		if (option['total_price'] && this.i18n_total_price !== '' && this.i18n_tax !== '') {
			option['total_price'] = option['total_price'].replace('/ /g', '&nbsp;');
			var sentence = this.i18n_total_price + ' ' + option['total_price'] + ' ('+this.i18n_tax+')';
			if($(query).get(0).tagName === 'OPTGROUP') {
				this.processUpdateSelectOptionPrice($(query).find('option'), sentence);
				return;
			}
			$(query + this.class_label_total + ', ' + query + this.class_label_pricelist).html(sentence);
		} else if($(query).length > 0 && $(query).get(0).tagName === 'OPTGROUP') {
			this.processUpdateSelectOptionPrice($(query).find('option'), '');
		}
	},
	processUpdateSelectOptionPrice: function(tag_option, amount_text) {
		var values = tag_option.text().split(' (');
		var option_value = values[0];
		
		if(amount_text !== '') {
			option_value += ' (' + amount_text + ')';
		}
		
		tag_option.text(option_value);
	},
        /**
         * Returns default operation values
         * Needs to be filled afterwards
         */
        getDefaultOperation: function(){
                return {
                        action: 'add',
                        value: '',
                        dimension: 1,
                        step: 0,
                        option: 0
                }
        },
	update: function(input, action) {
		var self = this;
		var data = {
			ajax: 1,
			submitUpdateOption: 1,
		};

		$(self.id_preview).addClass('loading');
		self.lock();

		self.qty = parseInt($(self.id_qty).val());

		$(self.id_step + data.step + ' ' + self.class_error_step).html('');
		$(self.id_step + data.step + ' ' + self.query_inputs_text).closest(self.class_option_group).removeClass('form-error');
		
                if (input.attr('type') === 'text' || (input.get(0) && input.get(0).tagName === 'SELECT')) {

                        var ope = self.getDefaultOperation();

                        ope.step = parseInt(input.data('step'));
			ope.value = input.val();
			ope.dimension = parseInt(input.data('dimension'));
			ope.option = parseInt(input.data('option'));
			if (ope.value === '') {
				ope.action = 'remove';
			}

                        if (action !== undefined) {
                                ope.action = action;
                        }
                                                
                                                
                        data.operations = [ope];
                } else if (input.get(0) && input.get(0).tagName === 'OPTION') {

                        var ope = self.getDefaultOperation();
                        ope.step = parseInt(input.data('step'));
                    
			var value = input.val();
			if(value === '') {
				for (var i in self.detail) {
					if (data.step === parseInt(self.detail[i]['id'])) {
						for (var j in self.detail[i]['options']) {
							if (self.detail[i]['options'][j]['selected']) {
								ope.option = self.detail[i]['options'][j]['id'];
								break;
							}
						}
					}
				}
				ope.action = 'remove';
			} else {
				ope.option = parseInt(input.val());
				ope.option_qty = parseInt(input.closest(this.class_step_group).find(self.query_input_option_qty).val());
			}
                               
                        if (action !== undefined) {
                                ope.action = action;
                        }                
                
                        data.operations = [ope];
                } else if(input.get(0) && input.get(0).tagName === 'TD'){
                        // Case of table display
                        // from the given TD we retrieve
                        // option's ID and values
                        var ope = self.getDefaultOperation();
                        ope.action = 'addPriceList';
                        var step = parseInt(input.data('step'));
                        ope.step = step;
                        
                        ope.optionDim1 = parseInt(input.data('option-1'));
                        ope.optionDim2 = parseInt(input.data('option-2'));
                        
                        ope.valueDim1 = input.data('value-1');
                        ope.valueDim2 = input.data('value-2');
                        
                        data.operations = [ope];
                }else {

			var ope = self.getDefaultOperation();
                        ope.step = parseInt(input.data('step'));
                        if (action === undefined && input.attr('type') === 'checkbox' && !input.is(':checked')) {
                                ope.action = 'remove';
                        }

                        ope.option = parseInt(input.val());
                        ope.option_qty = parseInt(input.closest(this.class_option_group).find(self.query_input_option_qty).val());

                        if (action !== undefined) {
                                ope.action = action;
                        }
                        
                        data.operations = [ope];
		}

		$.post(self.action, data, function(result) {
			result = JSON.parse(result);

			self.unlock();
			$(self.id_preview).removeClass('loading');

			self.detail = result.detail;
			self.buildSteps();
			$(self.id_preview).html(result.previewHtml);
			$(self.id_qty).val(self.qty);
			progressBarHandler.launch(result.progress_start, result.progress_end);
				
			if(!result.success && typeof result.steps_errors === 'object' && Object.keys(result.steps_errors).length > 0) {
				var steps_errors = result.steps_errors;
				var options_error = result.options_error;
				for (var id_step in steps_errors) {
					$(self.id_step + id_step + ' ' + self.class_error_step).html(steps_errors[id_step]);
				}
				for (var k in options_error) {
					var option = $(self.id_block + " [data-option='" + options_error[k] + "']");
					if (option.attr('type') === 'text') {
						option.parents(self.class_option_group).removeClass('form-ok');
						option.parents(self.class_option_group).addClass('form-error');
					}
				}
			}
		});
	},
	processSelectOptionBlock: function(input) {
		var container = input.parents(this.class_step_options);
		if (input.attr('type') === 'radio') {
			container.find(this.class_option_block).removeClass('selected');
			input.parents(this.class_option_block).addClass('selected');
		} else if (!input.is(':checked')) {
			input.parents(this.class_option_block).removeClass('selected');
		} else {
			input.parents(this.class_option_block).addClass('selected');
		}
	},
	resetUniform: function() {
		// Trigger cause some problems with uniform plugin
		if ($.uniform !== undefined) {
			$.uniform.restore(this.query_inputs_select);
			$.uniform.restore(this.query_inputs_choice);
			$(this.query_inputs_choice).uniform();
			$(this.query_inputs_select).uniform();
		}
	},
	filterDetailProgressiveDisplay : function(detail) {
		var ids_step_to_display = new Array();
		var new_detail = {};
		for (var id_step in detail) {
                        var search_next_step = false;
			
			// Cas du premier
			if ($(this.id_step + id_step).prev(this.class_step_group).length === 0) {
				ids_step_to_display.push(id_step);
			}
			
			if (!search_next_step) {
                                // Traitement différent pour les grilles tarifaires
                                if(detail[id_step]['price_list']){
                                    search_next_step = true;
                                    
                                    for (var id_option in detail[id_step]['options']) {
                                        var option = detail[id_step]['options'][id_option];
                                        
                                        if (!option['selected'] || !option['value']) {
                                            search_next_step = false;
                                            break;
                                        }
                                    }
                                        
                                } else {
                                    // On cherche au moins une option sélectionnée ou avec une valeur
                                    for (var id_option in detail[id_step]['options']) {
                                            var option = detail[id_step]['options'][id_option];
                                            if (option['selected'] || option['value']) {
                                                    search_next_step = true;
                                                    break;
                                            }
                                    }
                                }
			}
                        // Si les conditions précédentes sont remplis alors chercher les autres étapes car c'est renseigné
                        // Sinon arrêter car aucuns intérêts !
			
			if (!search_next_step) {
				//continue;
                                break;
			}
			
			var current_id_step = id_step;
			/**
			 * On recherche la prochaine étape à afficher tant qu'on trouve une prochaine étape dans le DOM
			 * ET qu'on ne le trouve pas dans la configuration courante des étapes qu'on a le droit d'afficher
			 */
			do {
				var next_id_step = this.getNextStepId(current_id_step);
				if (next_id_step && detail[next_id_step] !== undefined) {
					ids_step_to_display.push(next_id_step);
				}
				current_id_step = next_id_step;
			} while(next_id_step && detail[next_id_step] === undefined);
		}
		
		for (var i in ids_step_to_display) {
			var id_step = ids_step_to_display[i];
			new_detail[id_step] = detail[id_step];
		}
		
		return new_detail;
	},
	getNextStepId: function(id_step) {
		var step = $(this.id_step + id_step);
		var next_step = step.next(this.class_step_group);
		
		if (next_step.length === 0) {
			return 0;
		}
		
		var id = next_step.attr('id');
		var splitted = id.split('step_');
		
		if (splitted[1] === undefined) {
			return 0;
		}
		
		return parseInt(splitted[1]);
	},
	addLayersToZoom: function() {
		if (!this.visual_rendering) {
			return;
		}
		var layers = this.layers_manager.get();
		var old_layers = layers;
		var old_element = this.layers_manager.getElement();
		this.layers_manager.setElement(this.class_zoom_fancybox);
		for (var i in layers) {
			layers[i]['image'] = layers[i]['image'].replace(this.image_format, this.fancybox_image_format);
		}
		this.layers_manager.set(layers);
		this.layers_manager.apply();
		this.layers_manager.setElement(old_element);
		this.layers_manager.set(old_layers);
	}
};

productPriceBlockHandler = {
	// Properties
	id_add_to_cart: '#add_to_cart',
	id_buy_block: '#buy_block',
	class_configure: '.configure_link',
	class_box_cart: '.box-cart-bottom',
	class_from_price: '.from_price',
	class_our_price_display: '.our_price_display',
	class_product_container: '.product-container',
	class_content_price: '.content_price',
	class_btns_container: '.button-container',
	class_product_options: '.product_options',
	class_ajax_add_to_cart: '.ajax_add_to_cart_button',
    class_link_view: '.lnk_view',
	list_cart_btn_classes: 'button btn btn-default configure_link',
	// Methods
	init: function() {
		this.bindAll();
		this.processChangeHtmlProductPrice();
	},
	bindAll: function() {

	},
	getConfigureButton: function(options) {
		return $('<a/>')
				.attr({
					'class': this.list_cart_btn_classes,
					'title': options.l_configure,
					'href': options.link
				})
				.html($('<span/>').html(options.l_configure))
				;
	},
	processChangeHtmlProductPrice: function() {
		var self = this;
		$(self.class_from_price).each(function() {
			var el = $(this);
			var container = $(this).parents(self.class_content_price);
			container.prepend(el.clone());
			el.remove();
		});
	},
	// For product listing ... No hooks to overriding button :(
	processSetHtmlLinkToConfigurator: function(options) {
		var self = this;
		var link_el = self.getConfigureButton(options);
		$(self.class_from_price + '_' + options.id_product).each(function() {
			var el = $(this);
			var container = el.parents(self.class_product_container);
            container.find(self.class_ajax_add_to_cart).hide();
            container.find(self.class_link_view).hide();
			var btns_container = container.find(self.class_btns_container);
			if (btns_container.length > 0 && btns_container.find(self.class_configure).length === 0) {
				btns_container.prepend(link_el);
			}
		});
	}
};

orderSummaryHandler = {
	// Properties
	cartDetails: {},
	cartDetailsCustomizations: {},
	// /!\ Warning if updated, used for cartsummary AND blockcart
	name_id_product: "product_",
	id_order_detail: "#order-detail-content",
	class_order_item: "tbody tr.item",
	class_cart_item: ".cart_item",
	class_blockcart_item: ".cart_block dl dt",
	class_cart_product: ".cart_product",
	class_cart_description: ".cart_description",
	class_cart_quantity: ".cart_quantity",
	class_cart_delete: ".cart_delete",
	class_cart_unit_price: ".cart_unit",
	class_cart_total_price: ".cart_total .price",
	class_blockcart_info: ".cart-info",
	i18n_nb_files_uploaded: "",
	// Methods
	init: function(context, cDetails, cartDetailsCustomizations) {
        if(prestashopVersion > 16) {
        	return;
        }
		if (typeof nb_files_uploaded_i18n !== 'undefined') {
			// Translations
			this.i18n_nb_files_uploaded = nb_files_uploaded_i18n;
		}
		if (cDetails !== undefined) {
			this.bindAll();
			// addJsDef
			this.cartDetails = cDetails;
			this.cartDetailsCustomizations = cartDetailsCustomizations;
			if (context === 'order') {
				this.processChangeHtmlCartSummary();
			} else if (context === 'history') {
				this.processChangeHtmlHistory();
			} else if (context === 'blockcart') {
				this.processChangeHtmlBlockCart();
			}
		}
	},
	getDetail: function(cartDetails) {
		var detail = JSON.parse(cartDetails.detail);
		var return_detail = {};
		// On réorganise par position et non par ID ...
		for(var id_step in detail) {
			return_detail[parseInt(detail[id_step]['position'])] = detail[id_step];
		}
		return return_detail;
	},
	bindAll: function() {

	},
	processChangeHtmlCartSummary: function() {
		var self = this;
		$(self.class_cart_item).each(function() {
			var id = $(this).attr('id');
			for (var i in self.cartDetails) {
				var cartDetail = self.cartDetails[i];
				if (id.indexOf(self.name_id_product + cartDetail.id_product) !== -1) {
					//self.processHtmlCartItem(cartDetail, $(this));
				}
			}
		});
		self.processHtmlCartCustomization();
	},
	processHtmlCartCustomization: function() {
		/* CUSTOMIZATION */
		var self = this;
		if(prestashopVersion === 16) {
			for (var j in self.cartDetailsCustomizations) {
				var cartDetailCustomization = self.cartDetailsCustomizations[j];
				var link = configurator_update_urls[cartDetailCustomization.id_customization];
				var id = self.name_id_product+cartDetailCustomization.id_product+'_[0-9]+_';
                
				var product_parent = $("tr:regex(id,"+id+"0)").first();
				var product = $("tr:regex(id,"+id+cartDetailCustomization.id_customization+")").first();

				product_parent.hide();

				var desc = product_parent.find(self.class_cart_description).html();
				product_parent.find(self.class_cart_description).append(product.find('.typedText').parent().html());
				product_parent.find(self.class_cart_description).append('<a href="'+link+'">'+configurator_update_label+'</a>');
				product_parent.find(self.class_cart_unit_price).html(cartDetailCustomization.unit_price);
				product_parent.find(self.class_cart_quantity).html(product.find(self.class_cart_quantity).html());
				product_parent.find(self.class_cart_delete).html(product.find(self.class_cart_delete).html());
				product_parent.find(self.class_cart_total_price).html(cartDetailCustomization.total_price);

				product.html(product_parent.html());
				product_parent.find(self.class_cart_description).html(desc);
			}
		} else {
			// PRESTASHOP 1.7
			for (var j in cartDetails) {
				var cartDetail = cartDetails[j];
				var item = $('body').find('a[data-id_customization="'+cartDetail.id_customization+'"]');
				if(item.length > 0) {
					item.attr('href',item.attr('href')+'?configurator_update='+cartDetail.id);
				}
			}
		}
	},
	processHtmlCartItem: function(cartDetail, cart_item) {
		var cart_description = cart_item.find(this.class_cart_description);
		var html = cart_description.html();
		if (html != null && html.indexOf(cartDetail.attribute_key) !== -1) {
			html = this.getUpdatedHtml(html, cartDetail);
			cart_description.html(html);
		}
	},
	processChangeHtmlBlockCart: function() {
		var self = this;
		$(self.class_blockcart_item).each(function() {
			var id = $(this).data('id');
			for (var i in self.cartDetails) {
				var cartDetail = self.cartDetails[i];
				if (id.indexOf(self.name_id_product + cartDetail.id_product) !== -1) {
					//self.processHtmlBlockCartItem(cartDetail, $(this));
				}
			}
		});
		self.processHtmlCartCustomization();
	},
	processHtmlBlockCartItem: function(cartDetail, cart_item) {
		var cart_description = cart_item.find(this.class_blockcart_info);
		var html = cart_description.html();
		if (typeof html !== 'undefined' && html.indexOf(cartDetail.attribute_key) !== -1) {
			html = this.getUpdatedHtml(html, cartDetail);
			cart_description.html(html);
		}
	},
	processChangeHtmlHistory: function() {
		var self = this;
		$(self.id_order_detail + ' ' + self.class_order_item).each(function() {
			var html = $(this).html();
			for (var i in self.cartDetails) {
				var cartDetail = self.cartDetails[i];
				self.processHtmlHistoryItem(cartDetail, $(this));
			}
		});
		self.processHtmlCartCustomization();
	},
	processHtmlHistoryItem: function(cartDetail, order_item) {
		var html = order_item.html();
		var regexBr = new RegExp('&lt;br \/&gt;', 'gm');
		html = html.replace(regexBr, "<br/>");
		var regexImg = new RegExp('&lt;img');
		html = html.replace(regexImg, "<img");
		var regexImgEnd = new RegExp('\/&gt;');
		html = html.replace(regexBr, "/>");
		order_item.html(html);
	},
	getUpdatedHtml: function(html, cartDetail) {
		var regex = new RegExp(cartDetail.attribute_key, 'g');
		var regexLink = new RegExp('#\/[0-9]*-*configurator-' + cartDetail.attribute_key, 'g');
		html = html.replace(regexLink, "");
		return html.replace(regex, this.getHtmlDetail(cartDetail));
	},
	getHtmlDetail: function(cartDetail) {
		var detail = this.getDetail(cartDetail);
		var list = $('<ul/>').addClass('configurator');
		for (var position in detail) {
			var array_options = new Array();
			var display = false;
			
			if(detail[position]['is_upload'] && detail[position]['file_has_been_uploaded']) {
				display = true;
				array_options.push(detail[position]['attachments'].length + ' ' + this.i18n_nb_files_uploaded);
			} else if(!detail[position]['is_upload']) {
				for (var id_option in detail[position]['options']) {
					if (detail[position]['options'][id_option]['value'] !== '' && detail[position]['options'][id_option]['value'] !== false) {
						array_options.push(
								detail[position]['options'][id_option]['name'] +
								' : ' +
								detail[position]['options'][id_option]['value'] +
								detail[position]['input_suffix']
								);
						display = true;
					} else if (detail[position]['options'][id_option]['selected']) {
						var label = detail[position]['options'][id_option]['name'];
						if (parseInt(detail[position]['use_qty'])) {
							label = detail[position]['options'][id_option]['qty'] + 'x ' + label;
						}
						array_options.push(label);
						display = true;
					}
				}
			}
			
			if (display) {
				var li = $('<li/>');
				var step_name = $('<strong/>').html(detail[position]['name'] + ' : ');
				li.append(step_name);
				li.append(array_options.join(', '));
				list.append(li);
			}
		}
		return list.get(0).outerHTML;
	}
};

// Launcher
(function($) {
	
	$(function() {
		productPriceBlockHandler.init();
		/**
		 * PARTIE COMMANDE
		 */
		if ($('body').hasClass('order')  || $('body').hasClass('order-opc')) {
			if (typeof (cartDetails) !== 'undefined') 
			{
				orderSummaryHandler.init("order", cartDetails, cartDetailsCustomizations);
			}
		/**
		 * PARTIE PRODUIT
		 */
		} else if ($('body').attr('id') === 'product' && typeof (detail) !== 'undefined') {
                    var useNewVersion = true;
                    
                    // allows to change from old to new version
                    if (useNewVersion) {
                        var bridge = new CONFIGURATOR.bridge();

                        var data = bridge.translateInit(detail);
                        if(typeof data.tabs_status !== 'undefined')
                        {
                            data.tabs_status = tabs_status;
                        }      
                        Front = new CONFIGURATOR.Front(data);
                        return;
                    }

			configuratorHandler.init();

			var PreviewScrollFix = new CONFIGURATOR.ScrollFix(jQuery);
			PreviewScrollFix.init('#configurator_preview', {
				marginTop: 35, 
				removeOffsets: true,
				limit: function() {
					return $('.page-product-box').first().offset().top - $('#configurator_preview').outerHeight(true);
				}
			});

			var ImageBlockScrollFix = new CONFIGURATOR.ScrollFix(jQuery);
			ImageBlockScrollFix.init('#image-block', {
				marginTop: 35, 
				removeOffsets: true,
				limit: function() {
					return $('.page-product-box').first().offset().top - $('#image-block').outerHeight(true);
				}
			});

			if (!CONFIGURATOR.WindowHelper.isMobile() && !CONFIGURATOR.WindowHelper.isTablet()) {
				PreviewScrollFix.start();
			}

			if (typeof(contentOnly) !== 'undefined' && typeof(visual_rendering) !== 'undefined')
			{
				if (visual_rendering && !CONFIGURATOR.WindowHelper.isMobile()) {
					ImageBlockScrollFix.start();
				}
				if (!contentOnly && visual_rendering && !!$.prototype.fancybox) {
					$('li:visible .fancybox, .fancybox.shown').fancybox({
						beforeShow : function() {
							configuratorHandler.addLayersToZoom();
						}
					});
				}
			}
		}
		/**
		 * PARTIE BLOCKCART
		 * Always check for blockcart
		 */
		if(typeof (cartDetails) !== 'undefined') {
			orderSummaryHandler.init("blockcart", cartDetails);
		}
	});

    $( document ).ready(function() {
    	if (typeof configurator_floating_preview !== "undefined" && parseInt(configurator_floating_preview) === 1) {
            $("#configurator_preview").scrollFix({
                side: 'bottom',
            }).scroll();
        }
        //var stickybit = stickybits('.pp-left-column');
    });
   
})(jQuery);