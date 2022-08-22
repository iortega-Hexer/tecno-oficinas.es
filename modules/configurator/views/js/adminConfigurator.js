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

priceImpactHandler = {
    // CLASS
    price_impact_block_class: '.price_impact',
    select_class: '.select_impact',
    input_value_class: '.impact_value',
    input_value_form_class: '.form_value',
    input_group_after_class: '.input-group-after',
    devise_class: '.devise',
    percent_class: '.percent',
    multiplier_class: '.multiplier',
    step_impact_class: '.select_step_impact',
    step_impact_multiple_class: '.select_step_impact_multiple',
    step_impact_all_class: '.select_step_impact_all',
    step_impact_singleinput_class: '.select_step_impact_singleinput',
    area_suffix_class: '.suffix',
    pricelist_input_class: '.pricelist_input',
    alert_area_class: '.alert-area',
    info_multiplier_class: '.info-multiplier',
    extension_area_class: '.area-extension',
    value_displayed_option_class: '.value-displayed-options',
    xy_area_class: '.area-xy',
    value_xy_area_class: '.value-area-xy',
    step_option_impact_class: '.select_step_impact_step_option',
    step_option_impact_option_class: '.select_option_impact_step_option',
    select_impact_qty_step_class: '.select_impact_qty_step',
    select_impact_qty_step_option_class: '.select_impact_qty_step_option',
    price_impact_period_block_class: '.price_impact_period_block',
    price_impact_quantity_block_class: '.price_impact_quantity_block',
    price_impact_quantity_step_block_class: '.price_impact_quantity_step_block',

    // Initialization
    init: function() {
        this.bindAll();
        this.update();
    },
    update: function() {
        var self = this;
        var container = $('#configurator_step_form');

        $(container).find(self.select_class).each(function() {
            self.processEditInputGroupAddon($(this));
        });
        $(container).find(self.step_impact_class).each(function() {
            self.processEditAreaSuffix($(this));
        });
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $('#configurator_step_form');

        container.on('change', self.select_class, function() {
            self.processEditInputGroupAddon($(this));
            self.processEditInputFormValue($(this));
        });
        container.on('change', self.input_value_class, function() {
            self.processEditInputFormValue($(this));
        });
        container.on('change', self.step_impact_class, function() {
            self.processEditAreaSuffix($(this));
        });
        container.on('change', self.step_option_impact_class, function() {
            self.processUpdateOptions($(this));
        }).trigger('change');
        container.on('change', self.select_impact_qty_step_class, function() {
            self.processUpdateImpactQtyOptions($(this));
        }).trigger('change');
    },
    // Change currency or percent suffix's input group addon depending of select's option
    processEditInputGroupAddon: function(select) {
        var parent_container = select.parents(this.price_impact_block_class);
        var option_value = select.find('option:selected').val();
        parent_container.find(this.input_group_after_class).hide();
        parent_container.find(this.percent_class).hide();
        parent_container.find(this.multiplier_class).hide();
        parent_container.find(this.devise_class).show();
        parent_container.find(this.step_impact_class).closest('.form-group').hide();
        parent_container.find(this.step_impact_multiple_class).closest('.form-group').hide();
        parent_container.find(this.step_impact_all_class).closest('.form-group').hide();
        parent_container.find(this.step_impact_singleinput_class).closest('.form-group').hide();
        parent_container.find(this.input_value_class).closest('.form-group').hide();
        parent_container.find(this.pricelist_input_class).closest('.form-group').hide();
        parent_container.find(this.alert_area_class).hide();
        parent_container.find(this.info_multiplier_class).hide();
        parent_container.find(this.extension_area_class).hide();
        parent_container.find(this.xy_area_class).hide();
        parent_container.find(this.price_impact_period_block_class).hide();
        if (option_value === 'amount' || option_value === 'reduction') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
        } else if (option_value === 'percent' || option_value === 'neg_percent') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            parent_container.find(this.percent_class).show();
            parent_container.find(this.devise_class).hide();
        } else if (option_value === 'area') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            var is_xy_area = parent_container.find(this.xy_area_class).find('.switch > input:checked').val();
            parent_container.find(this.input_group_after_class).show();
            if(is_xy_area == "0") {
                parent_container.find(this.step_impact_class).closest('.form-group').show();
            }
            parent_container.find(this.alert_area_class).show();
            parent_container.find(this.extension_area_class).show();
            parent_container.find(this.xy_area_class).show();
        } else if (option_value === 'area_multiple') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            parent_container.find(this.input_group_after_class).show();
            parent_container.find(this.step_impact_multiple_class).closest('.form-group').show();
            parent_container.find(this.alert_area_class).show();
            parent_container.find(this.extension_area_class).show();
        } else if (option_value === 'pricelist') {
            var is_xy_area = parent_container.find(this.xy_area_class).find('.switch > input:checked').val();
            if(is_xy_area == "0") {
                parent_container.find(this.step_impact_class).closest('.form-group').show();
                // Display without quantity step
                parent_container.find(this.step_impact_class).find('option').show();
                parent_container.find(this.step_impact_class).find('option[data-step-type="quantity"]').hide();
            }
            parent_container.find(this.pricelist_input_class).closest('.form-group').show();
            parent_container.find(this.xy_area_class).show();
        } else if (option_value === 'pricelist_quantity') {
            parent_container.find(this.step_impact_class).closest('.form-group').show();
            
            // Display quantity step only
            parent_container.find(this.step_impact_class).find('option').hide();
            parent_container.find(this.step_impact_class).find('option[value=""]').show();
            parent_container.find(this.step_impact_class).find('option[data-step-type="quantity"]').show();
            
            parent_container.find(this.pricelist_input_class).closest('.form-group').show();
        } else if (option_value === 'pricelist_multi') {
            parent_container.find(this.step_impact_multiple_class).closest('.form-group').show();
            parent_container.find(this.pricelist_input_class).closest('.form-group').show();
        } else if (option_value === 'pricelist_area' || option_value === 'pricelist_area_square') {
            parent_container.find(this.step_impact_class).closest('.form-group').show();
            parent_container.find(this.pricelist_input_class).closest('.form-group').show();
        } else if (option_value === 'multiplier' || option_value === 'neg_multiplier') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            parent_container.find(this.step_impact_singleinput_class).closest('.form-group').show();
            parent_container.find(this.multiplier_class).show();
            parent_container.find(this.info_multiplier_class).show();
            parent_container.find(this.devise_class).hide();
        } else if (option_value === 'multiplier_price') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            parent_container.find(this.step_impact_all_class).closest('.form-group').show();
            parent_container.find(this.multiplier_class).show();
            parent_container.find(this.devise_class).hide();
        } else if (option_value === 'amount_period') {
            parent_container.find(this.input_value_class).closest('.form-group').show();
            parent_container.find(this.price_impact_period_block_class).show();
        }
    },
    processEditAreaSuffix: function(select) {
        var parent_container = select.parents(this.price_impact_block_class);
        var suffix = select.find('option:selected').data('suffix');
        parent_container.find(this.input_group_after_class + ' ' + this.area_suffix_class).html(suffix);
    },
    // Edit Input form value, which is used when saving price's impact
    processEditInputFormValue: function(el) {
        var parent_container = el.parents(this.price_impact_block_class);
        var input_form_value = parent_container.find(this.input_value_form_class);
        var select = parent_container.find(this.select_class);
        var option_value = select.find('option:selected').val();
        var value = parent_container.find(this.input_value_class).val();

        input_form_value.val(option_value + ',' + value);
    },
    showUnityForm: function(el, show) {
        var parent_container = el.closest(this.extension_area_class);
        if (show) {
            parent_container.find(this.value_displayed_option_class).fadeIn();
        } else {
            parent_container.find(this.value_displayed_option_class).fadeOut();
            parent_container.find('input').val('');
        }
    },
    showXYForm: function(el, show) {
        var parent_container = el.closest(this.xy_area_class);
        if (show) {
            parent_container.find(this.value_xy_area_class).fadeIn();
            parent_container.parent().find(this.step_impact_class).closest('.form-group').fadeOut();
        } else {
            parent_container.find(this.value_xy_area_class).fadeOut();
            parent_container.parent().find(this.step_impact_class).closest('.form-group').fadeIn();
        }
    },
    showQuantityForm: function(el, show) {
        var parentContainer = el.closest(this.price_impact_quantity_block_class);
        if (show) {
            parentContainer.find(this.price_impact_quantity_step_block_class).fadeIn();
        } else {
            parentContainer.find(this.price_impact_quantity_step_block_class).fadeOut();
        }
    },
    processUpdateOptions: function(el) {
        var parent_container = el.parent().parent();
        var select_option = parent_container.find(this.step_option_impact_option_class);
        select_option.find('option').hide();
        select_option.find("option[data-step-id='"+el.val()+"']").show();
        var current_option = select_option.find("option:selected");
        if(current_option.data('step-id') != el.val()) {
            var options = select_option.find("option[data-step-id='"+el.val()+"']");
            select_option.val(options.val());
        }
    },
    processUpdateImpactQtyOptions: function(el) {
        var parentContainer = el.closest('.form-group');
        var selectImpactQtyStepOption = parentContainer.find(this.select_impact_qty_step_option_class);
        selectImpactQtyStepOption.find('option').hide();
        var defaultOption = selectImpactQtyStepOption.find("option[data-step-id='0']");
        defaultOption.show();
        selectImpactQtyStepOption.find("option[data-step-id='"+el.val()+"']").show();
        var currentOption = selectImpactQtyStepOption.find("option:selected");
        if(currentOption.data('step-id') != el.val()) {
            selectImpactQtyStepOption.val(defaultOption.val());
        }
    },
};

displayConditionsHandler = {
    // Properties
    conditions: {},
    // ID
    form_id: '#configurator_step_form',
    template_group_id: '#tmpl_conditions_group',
    template_row_id: '#tmpl_conditions_row',
    // CLASS
    container_class: '.conditions_block',
    form_group_class: '.form-group',
    group_list_class: '.condition_group_list',
    btn_add_group_class: '.add_condition_group',
    btn_add_condition_class: '.add_condition',
    btn_delete_condition_class: '.delete_condition',
    conditions_panel_class: '.conditions-panel',
    condition_group_class: '.condition_group',
    condition_row_class: '.condition_row',
    select_step_class: '.select_step',
    select_option_class: '.select_option',
    select_block_class: '.select_block',
    div_min_max: '.div_min_max',
    btn_negative_condition_class: '.btn-negative_condition',
    // CLASS NAME
    // --
    // Initialization
    init: function() {
        var self = this;
        // bind events
        self.bindAll();

        $(self.select_step_class).each(function() {
            self.showSelectOption($(this));
        });
    },
    update: function() {
        var self = this;
        var container = $('#configurator_step_form');
        container.find(self.select_step_class).each(function() {
            self.showSelectOption($(this));
        });
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $('#configurator_step_form');
        var form = $(self.form_id);

        form.on('submit', function() {
            self.processSubmit();
        });

        container.on('click', self.btn_add_group_class, function(e, datas) {
            e.preventDefault();
            self.addConditionsGroup(e, datas);
        });

        container.on('click', self.btn_add_condition_class, function(e) {
            e.preventDefault();
            self.addCondition($(this));
        });

        container.on('click', self.btn_negative_condition_class, function(e) {
            e.preventDefault();
            self.changeNegativeCondition($(e.currentTarget));
        });

        container.on('change', self.select_step_class, function() {
            self.showSelectOption($(this));
        });

        container.on('click', self.btn_delete_condition_class, function(e) {
            e.preventDefault();
            self.removeCondition($(this));
        });

        container.on('click', self.condition_group_class, function() {
            self.setActive($(this).parents(self.container_class).find(self.group_list_class), $(this));
        });
    },
    getCompiledTemplate: function(id) {
        return Handlebars.compile($(id).html());
    },
    changeNegativeCondition: function (btn){
        if(btn.find('i').hasClass('icon-check')){
            btn.find('i').attr('class','icon-remove action-disabled list-action-enable');
        } else {
            btn.find('i').attr('class','icon-check action-enabled list-action-enable');
        }
    },
    processSubmit: function() {
        var self = this;
        // For each conditions block
        $(self.container_class).each(function() {
            var container = $(this);
            var type = container.data('type');
            var type_id = container.data('id');
            var conditions = {};
            // For each conditions group
            $(this).find(self.condition_group_class).each(function() {
                var group_id = $(this).data('id');
                var negative_condition = $(this).find(self.btn_negative_condition_class+' i').hasClass('icon-remove');
                conditions[group_id] = {};
                // For each condition row
                $(this).find(self.condition_row_class).each(function() {
                    var id = $(this).data('id');
                    var formula = $(this).data('formula');
                    var min = parseFloat($(this).data('min'));
                    var max = parseFloat($(this).data('max'));
                    if( min > max && max > 0){
                        var temp_min = min;
                        min = max;
                        max = temp_min;
                    }

                    if(typeof conditions[group_id]['datas'] == 'undefined'){
                        conditions[group_id]['datas'] = {};
                    }
                    conditions[group_id]['datas'][id] = {"id" : id, "min" : min, "max" : max, "formula": formula};
                    conditions[group_id]['negative_condition'] = negative_condition;
                });
            });
            container.append($('<input type="hidden" />').attr('name', 'conditions[' + type + '][' + type_id + ']').val(JSON.stringify(conditions)));
        });
    },
    renderConditions: function(id, datas) {
        var el = $(id);
        if (datas !== '') {
            datas = JSON.parse(datas);
            for (var i in datas) {
                el.find(this.btn_add_group_class).trigger('click', {negative_condition: datas[i].negative_condition });
                console.log(el);
                for (var j in datas[i].datas) {
                    var id = datas[i].datas[j]['value'];
                    var min = datas[i].datas[j]['min'];
                    var max = datas[i].datas[j]['max'];
                    var formula = datas[i].datas[j]['formula'];
                    var option = el.find(this.select_option_class + ' option[value="' + id + '"]');
                    var btn = option.parents('.form-group').find(this.btn_add_condition_class);
                    var parentid = option.parents('select').data('parentid');
                    var step = el.find(this.select_step_class + ' option[value="' + parentid + '"]').text();
                    var option_more = '';
                    if(min > 0 || max > 0){
                        option_more = ' ( min: ' + min + ' max: ' + max + ' )';
                    }

                    if (typeof btn !== 'undefined') {
                        btn = el.find(this.btn_add_condition_class);
                    }

                    this.addCondition(btn, {
                        step: (id > 0) ? step : '',
                        option: (id > 0) ? option.text() + option_more : '',
                        id: id,
                        min: min,
                        max: max,
                        formula: formula
                    });
                }
            }
        }
    },
    addConditionsGroup: function(event, datas) {
        var negative_condition = false;
        var container = $(event.currentTarget).parents(this.container_class);
        var group_list = container.find(this.group_list_class);
        var template = this.getCompiledTemplate(this.template_group_id);

        var count = group_list.find(this.condition_group_class).length;
        var show_separator = count > 0;

        if(typeof datas !== 'undefined' && typeof datas.negative_condition !== 'undefined'){
            negative_condition = datas.negative_condition;
        }

        container.find(this.conditions_panel_class).fadeIn();
        group_list.append(template({
            separator: show_separator,
            id: count + 1,
            negative_condition: negative_condition
        }));
        this.setActive(group_list, group_list.find(this.condition_group_class + ':last-child'));
    },
    addCondition: function(btn, datas) {
        var container = btn.parents(this.container_class);
        var form_group = btn.parents(this.form_group_class);
        var tbody = container.find(this.condition_group_class + '.alert-info tbody');
        var template = this.getCompiledTemplate(this.template_row_id);
        var count = tbody.find('tr').length;
        var show_separator = count > 0;

        if (datas === undefined) {
            tinyMCE.triggerSave();
            var selected_step = form_group.find('select.select_step option:selected');
            var selected_option = form_group.find('select.select_option:visible option:selected');
            var formula = form_group.find('textarea.condition_step_formula');
            var input_min = (form_group.find('div.div_min_max:visible .min').val() !== undefined)? form_group.find('div.div_min_max:visible .min').val().replace(',','.'):0;
            var input_max = (form_group.find('div.div_min_max:visible .max').val() !== undefined)? form_group.find('div.div_min_max:visible .max').val().replace(',','.'):0;
            if(!input_min) input_min = 0;
            if(!input_max) input_max = 0;
            if( parseFloat(input_min) > parseFloat(input_max) && parseFloat(input_max) > 0){
                var temp_min = input_min;
                input_min = input_max;
                input_max = temp_min;
            }
            var option_more = '';
            if(input_min > 0 || input_max > 0 ){
                option_more = ' ( min: ' + input_min + ' max: ' + input_max + ' )';
            }

            let value = null;
            if (typeof selected_option.val() === 'undefined') {
                if (formula.val().length === 0) {
                    return;
                }
                do {
                    value = parseInt(Math.random() * 1000000000) + 1000000000;
                } while($('body [data-id="' + value + '"]').length !== 0);
            } else {
                value = selected_option.val();
            }

            var datas = {
                step: selected_step.text(),
                option: selected_option.text() + option_more,
                id: value,
                min: input_min,
                max: input_max,
                formula: formula.val()
            };
        }

        tbody.append(template({
            separator: show_separator,
            step: datas.step,
            option: datas.option,
            id: datas.id,
            min: datas.min,
            max: datas.max,
            formula: (typeof datas.formula !== 'undefined') ? datas.formula : ''
        }));

        form_group.find('div.div_min_max:visible .min').val('');
        form_group.find('div.div_min_max:visible .max').val('');
    },
    removeCondition: function(btn) {
        var tr = btn.parents('tr');
        if (tr.prev().length) {
            tr.prev().remove();
        } else {
            tr.next().remove();
        }
        tr.remove();
    },
    setActive: function(group_list, groupBlock) {
        $(group_list.find(this.condition_group_class)).each(function() {
            $(this).removeClass('alert-info');
            $(this).find('table.table').removeClass('alert-info');
        });

        groupBlock.addClass('alert-info');
        groupBlock.find('table.table').addClass('alert-info');
    },
    showSelectOption: function(select_step) {
        var container = select_step.parents(this.container_class);
        var id = select_step.find('option:selected').val();

        container.find(this.select_option_class).hide();
        container.find(this.select_option_class + '[data-parentid="' + id + '"]').fadeIn();

        container.find(this.div_min_max).hide();
        container.find(this.div_min_max + '[data-parentid="' + id + '"]').fadeIn();


    }
};

filtersHandler = {
    // Properties
    filters: {},
    fields: ['type', 'option', 'operator', 'target_step', 'target_type', 'target_option', 'value'],
    field_id: 0,
    // ID
    form_id: '#configurator_step_form',
    template_group_id: '#tmpl_filters_group',
    template_row_id: '#tmpl_filters_row',
    formula_id: '#filter_formula',
    // CLASS
    container_class: '.filters_block',
    form_group_class: '.filters-panel',
    group_list_class: '.filter_group_list',
    btn_add_group_class: '.add_filter_group',
    btn_add_filter_class: '.add_filter',
    btn_delete_filter_class: '.delete_filter',
    filters_panel_class: '.filters-panel',
    filter_group_class: '.filter_group',
    filter_row_class: '.filter_row',
    select_step_class: '.select_target_step',
    select_operator_class: '.select_operator',
    select_value_class: '.select_value',
    select_option_class: '.select_option',
    select_block_class: '.select_block',
    div_min_max: '.div_min_max',
    btn_negative_filter_class: '.btn-negative_filter',
    // CLASS NAME
    // --
    // Initialization
    init: function() {
        var self = this;
        // bind events
        self.bindAll();
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $('#configurator_step_form');
        var form = $(self.form_id);

        form.on('submit', function() {
            self.processSubmit();
        });

        container.on('click', self.btn_add_group_class, function(e, datas) {
            e.preventDefault();
            self.addFiltersGroup(e, datas);
        });

        container.on('click', self.btn_add_filter_class, function(e) {
            e.preventDefault();
            self.addFilter($(this));
        });

        container.on('click', self.btn_delete_filter_class, function(e) {
            e.preventDefault();
            self.removeFilter($(this));
        });

        container.on('click', self.filter_group_class, function() {
            self.setActive($(this).parents(self.container_class).find(self.group_list_class), $(this));
        });

        container.on('change', self.select_step_class, function() {
            self.displayForm();
        });

        container.on('change', self.select_operator_class, function() {
            self.displayForm();
        });
    },
    getCompiledTemplate: function(id) {
        return Handlebars.compile($(id).html());
    },
    processSubmit: function() {
        var self = this;
        // For each filters block
        $(self.container_class).each(function() {
            var container = $(this);
            var type = container.data('type');
            var type_id = container.data('id');
            var filters = {};
            // For each filters group
            $(this).find(self.filter_group_class).each(function() {
                var group_id = $(this).data('id');
                filters[group_id] = {};
                // For each filter row
                $(this).find(self.filter_row_class).each(function() {
                    var id = $(this).data('id');
                    var values = $(this).data('values');
                    if(typeof filters[group_id]['datas'] == 'undefined'){
                        filters[group_id]['datas'] = {};
                    }
                    filters[group_id]['datas'][id] = {"id" : id, "values" : values};
                });
            });
            container.append($('<input type="hidden" />').attr('name', 'filters[' + type + '][' + type_id + ']').val(JSON.stringify(filters)));
        });
    },
    renderFilters: function(id, datas) {
        var el = $(id);
        if (datas !== '') {
            datas = JSON.parse(datas);
            for (var i in datas) {
                el.find(this.btn_add_group_class).trigger('click');
                for (var j in datas[i].datas) {
                    var btn = $(this.container_class).find(this.btn_add_filter_class);
                    var form_group = btn.parents(this.form_group_class);
                    for (var f in this.fields) {
                        form_group.find('select.select_' + this.fields[f]).val(datas[i].datas[j][this.fields[f]]).change();
                    }
                    $(this.formula_id).val(datas[i].datas[j].formula);
                    this.addFilter(btn);
                }
            }
        }
    },
    addFiltersGroup: function(event) {
        var container = $(event.currentTarget).parents(this.container_class);
        var group_list = container.find(this.group_list_class);
        var template = this.getCompiledTemplate(this.template_group_id);

        var count = group_list.find(this.filter_group_class).length;
        var show_separator = count > 0;

        container.find(this.filters_panel_class).fadeIn();
        group_list.append(template({
            separator: show_separator,
            id: count + 1
        }));
        this.setActive(group_list, group_list.find(this.filter_group_class + ':last-child'));
    },
    addFilter: function(btn, datas) {
        var container = btn.parents(this.container_class);
        var form_group = btn.parents(this.form_group_class);
        var tbody = container.find(this.filter_group_class + '.alert-info tbody');
        var template = this.getCompiledTemplate(this.template_row_id);
        var template_data = {};

        var count = tbody.find('tr').length;
        var show_separator = count > 0;
        template_data.separator = show_separator;
        template_data.values = {};

        if (datas === undefined) {
            for (var i in this.fields) {
                template_data.values[this.fields[i]] = form_group.find('select.select_' + this.fields[i] + ' option:selected').val();
                template_data[this.fields[i]] = form_group.find('select.select_' + this.fields[i] + ' option:selected').text();
            }
            var step_type = $(this.select_step_class + ' option:selected').data('step-type');
            if (step_type === 'features' || step_type === 'attributes') {
                template_data[this.fields[4]] = '-';
                template_data[this.fields[5]] = '-';
            }

            // AdvancedFormula module
            template_data.values['formula'] = '';
            var val = $(this.select_operator_class + ' option:selected').val();
            if ($(this.formula_id) && val.slice(val.length - 7) === 'FORMULA') {
                tinyMCE.triggerSave();
                var formula = $('#filter_formula').val().replace(/(\r\n|\n|\r)/gm,"");
                template_data.values['formula'] = formula;
                template_data['value'] = formula;
                template_data[this.fields[4]] = '-';
                template_data[this.fields[5]] = '-';
            }

            template_data.id = this.field_id;
            this.field_id++;
        }

        template_data.values = JSON.stringify(template_data.values);

        tbody.append(template(template_data));
    },
    removeFilter: function(btn) {
        var tr = btn.parents('tr');
        if (tr.prev().length) {
            tr.prev().remove();
        } else {
            tr.next().remove();
        }
        tr.remove();
    },
    setActive: function(group_list, groupBlock) {
        $(group_list.find(this.filter_group_class)).each(function() {
            $(this).removeClass('alert-info');
            $(this).find('table.table').removeClass('alert-info');
        });

        groupBlock.addClass('alert-info');
        groupBlock.find('table.table').addClass('alert-info');
    },
    displayForm: function() {
        var self = this;
        var container = $('#configurator_step_form');

        // Step
        var step_type = $(self.select_step_class + ' option:selected').data('step-type');
        if (step_type === 'features' || step_type === 'attributes') {
            container.find('select.select_target_type').parent().hide('slow');
            container.find('select.select_target_option').parent().hide('slow');
        } else {
            container.find('select.select_target_type').parent().show('slow');
            container.find('select.select_target_option').parent().show('slow');
        }

        // Operator
        var val = $(self.select_operator_class + ' option:selected').val();
        if (val.slice(val.length - 7) === 'FORMULA') {
            container.find(self.select_value_class).parent().hide('slow');
            container.find(self.formula_id).parent().show('slow');
            container.find('select.select_target_type').parent().hide('slow');
            container.find('select.select_target_option').parent().hide('slow');
        } else {
            container.find(self.select_value_class).parent().show('slow');
            container.find(self.formula_id).parent().hide('slow');
        }
    }
};

impactValuePeriodHandler = {
    // Properties
    filters: {},
    field_id: 0,
    // ID
    form_id: '#configurator_step_form',
    template_group_id: '#tmpl_impact_value_period_group',
    template_row_id: '#tmpl_impact_value_period_row',
    // CLASS
    container_class: '.price_impact_period_block',
    form_group_class: '.price_impact_period_panel',
    group_list_class: '.price_impact_period_group_list',
    btn_add_period_class: '.add_price_impact_period',
    btn_delete_filter_class: '.delete_impact_value_period',
    period_row_class: '.impact_value_period_row',
    input_date_start_id: '#price_impact_period_start',
    input_date_end_id: '#price_impact_period_end',
    input_value_id: '#input_impact_value_period',
    input_impact_value_period_values_id: '#impact_value_period_values',
    // CLASS NAME
    // --
    // Initialization
    init: function() {
        var self = this;
        // bind events
        self.bindAll();
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $(this.container_class);
        var form = $(self.form_id);

        form.on('submit', function() {
            self.processSubmit();
        });

        container.on('click', self.btn_add_period_class, function(e) {
            e.preventDefault();
            self.addPeriod();
        });

        container.on('click', self.btn_delete_filter_class, function(e) {
            e.preventDefault();
            self.removePeriod($(this));
        });
    },
    getCompiledTemplate: function(id) {
        return Handlebars.compile($(id).html());
    },
    processSubmit: function() {
        var self = this;
        var container = $(this.container_class);
        var periods = {};
        // For each filter row
        container.find(self.period_row_class).each(function() {
            var id = $(this).data('id');
            var values = $(this).data('values');
            periods[id] = {"id" : id, "values" : values};
        });
        $(this.input_impact_value_period_values_id).val(JSON.stringify(periods));
    },
    renderPeriods: function(id, datas) {
        this.addImpactValuePeriodGroup();
        if (datas !== '') {
            datas = JSON.parse(datas);
            for (var i in datas) {
                var container = $(this.container_class);
                var form_group = container.find(this.form_group_class);
                form_group.find(this.input_date_start_id).val(datas[i].values.date_start).change();
                form_group.find(this.input_date_end_id).val(datas[i].values.date_end).change();
                form_group.find(this.input_value_id).val(datas[i].values.specific_value).change();
                this.addPeriod();
            }
        }
    },
    addImpactValuePeriodGroup: function() {
        var container = $(this.container_class);
        var group_list = container.find(this.group_list_class);
        var template = this.getCompiledTemplate(this.template_group_id);

        container.find(this.filters_panel_class).fadeIn();
        group_list.append(template());
    },
    addPeriod: function() {
        var container = $(this.container_class);
        var group_list = container.find(this.group_list_class);
        var form_group = container.find(this.form_group_class);
        var tbody = group_list.find('.table tbody');
        var template = this.getCompiledTemplate(this.template_row_id);
        var template_data = {};

        template_data.values = {};
        template_data.values['date_start'] = form_group.find(this.input_date_start_id).val();
        template_data.values['date_end'] = form_group.find(this.input_date_end_id).val();
        template_data.values['specific_value'] = form_group.find(this.input_value_id).val();
        template_data.values_json = JSON.stringify(template_data.values);
        template_data.id = this.field_id;
        this.field_id++;

        tbody.append(template(template_data));
    },
    removePeriod: function(btn) {
        btn.parents('tr').remove();
    }
};

divisionHandler = {
    setup: {
        // ID
        form_id: '#configurator_step_form',
        // CLASS
        container_class: '.division_block',
        step_container_class: '.step_division_block',
        select_step_class: '.select_step',
        select_option_class: '.select_option',
        input_name: 'id_configurator_step_option_division',
        input_multiple: true
    },
    // CLASS NAME
    // --
    // Initialization
    init: function(setup) {
        var self = this;

        $.extend(true, self.setup, setup);

        self.initValue();
        // bind events
        self.bindAll();

        $(self.setup.select_step_class).each(function() {
            self.showSelectOption($(this));
        });
    },
    update: function() {
        var self = this;
        var container = $('#configurator_step_form');
        self.initValue();
        container.find(self.setup.select_step_class).each(function() {
            self.showSelectOption($(this));
        });
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $('#configurator_step_form');
        var form = $(self.setup.form_id);

        form.on('submit', function() {
            self.processSubmit();
        });

        container.on('change', self.setup.select_step_class, function() {
            self.showSelectOption($(this));
        });
    },
    showSelectOption: function(select_step) {
        var block = select_step.parents(this.setup.container_class);
        var id = select_step.find('option:selected').val();

        block.find(this.setup.select_option_class).hide();
        block.find(this.setup.select_option_class + '[data-parentid="' + id + '"]').fadeIn();
    },
    processSubmit: function() {
        var self = this;
        var container = $('#configurator_step_form');

        container.find(self.setup.step_container_class).each(function() {
            var block = $(this);
            var current_step_id = block.data('id');
            var step_id = block.find(self.setup.select_step_class + ' option:selected').val();
            var option_id = block.find(self.setup.select_option_class + '[data-parentid="' + step_id + '"] option:selected').val();
            var input_name = self.setup.input_name;
            if(self.setup.input_multiple) {
                input_name += '['+current_step_id+']';
            }
            block.append($('<input type="hidden" />')
                .attr('name', input_name)
                .val( option_id ));
        });
    },
    initValue: function() {
        var self = this;
        var container = $('#configurator_step_form');
        container.find(self.setup.step_container_class).each(function() {
            var block = $(this);
            var value = block.data('value');

            var option_option = block.find(self.setup.select_option_class + ' option[value="' + value + '"]');
            option_option.attr("selected","selected");
            var step_id = option_option.parent().data('parentid');
            var step_option = block.find(self.setup.select_step_class + ' option[value="' + step_id + '"]');
            step_option.attr("selected","selected");
        });
    }
};

configuratorStepHandler = {
    // ID
    steptype_id: '#steptype',
    text_field_block_id: '#text_field_block',
    price_list_block_id: '#price_list_block',
    price_list_header_id: '#header-group',
    price_impact_block_id: '#price_impact_',
    option_settings_block_id: '#option_settings_',
    display_conditions_block_id: '#display_conditions_block_',
    division_block_id: '#division_block_',
    use_input_id_prefix: '#use_input_',
    use_shared_id_prefix: '#use_shared_',
    use_qty_id_prefix: '#use_qty_',
    multiple_id_prefix: '#multiple_',
    max_options_block_id: '#max_options_block',
    unique_price_block_id: '#unique_price_block',
    unique_price_id_prefix: '#unique_price_',
    max_qty_block_id: '#max_qty_block',
    max_qty_group_id: '#max_qty_group',
    max_qty_coef_id: '#max_qty_group_coef',
    use_pricelist_id_prefix: '#use_pricelist_',
    display_total_id_prefix: '#display_total_',
    custom_template_block_id: '#custom_template_block',
    // CLASS
    table_option_class:'.step_option',
    used_btn_class: '.step_option .list-action-enable',
    edit_btn_class: '.step_option .edit',
    default_btn_class: '.step_option .default',
    iconcheck_class: '.icon-check',
    iconremove_class: '.icon-remove',
    price_impact_class: '.price_impact',
    display_conditions_class: '.display_conditions_block',
    division_class: '.division_block',
    option_settings_block_class: '.option_settings',
    tr_default_selected_class: '.default-selected',
    fileupload_class: '.selectbutton',
    fileupload_wrapper_class : '.fileupload-wrapper',
    fileupload_input_class : '.fileupload-file',
    // CLASS NAME
    loader_class_name: 'process-icon-loading',
    actiondisable_class_name: 'action-disabled',
    actionenable_class_name: 'action-enabled',
    // OTHER
    href_edit_conditions_link: 'edit_conditions_link',
    // Initialization
    init: function() {
        displayConditionsHandler.init();
        filtersHandler.init();
        priceImpactHandler.init();
        divisionHandler.init();
        /*divisionHandler.init({
            container_class: '.display_steps_block',
            step_container_class: '.display_steps_block',
            input_name: 'max_qty_step_option_id',
            input_multiple: false
        });*/
        this.bindAll();
        this.updateSettingsPanel($(this.steptype_id).val(), false);
    },
    // Bind all events needed
    bindAll: function() {
        var self = this;
        var container = $('#configurator_step_form');
        // Event process when click on used button for an option
        container.on('click', self.used_btn_class, function(e) {
            e.preventDefault();
            $(this).css('text-decoration', 'none');
            self.processAjaxUsed($(this));
        });
        // Event process when click on edit button for display conditions
        container.on('click', self.edit_btn_class, function(e) {
            e.preventDefault();
            self.saveCurrentOption();
            self.displayOptionForm($(this));
            self.processShowPriceImpact($(this));
            self.processShowDisplayCondition($(this));
            self.processShowDivision($(this));
            self.processShowOptionSettings($(this));
        });
        // Event process when click on default button for set default selected option
        container.on('click', self.default_btn_class, function(e) {
            e.preventDefault();
            if(!$(this).hasClass('link-disabled')) {
                self.processSelectedDefault($(this));
            }
        });
        // Envent process when click on asterisk
        container.on('click', self.tr_default_selected_class+' i', function(){
            var id_step_option = $(this).parent().parent().find('.id-step-option').html();
            self.processSelectedDefault($(this), true, id_step_option);
        });
        container.on('click', self.fileupload_class, function(){
            $(this).closest(self.fileupload_wrapper_class).find('input[type="file"]').trigger('click');
        });
        container.on('change', self.fileupload_input_class, function(){
            $(this).closest(self.fileupload_wrapper_class).find('input[type="text"]').val($(this).val());
        });
        // When step's type changed, some options disappears and some appears
        $(self.steptype_id).on('change', function(){
            self.showWarningBlock('alert-change-type');
            self.updateSettingsPanel($(this).val());
        });
    },
    saveCurrentOption: function() {
        displayConditionsHandler.processSubmit();
        divisionHandler.processSubmit();
        impactValuePeriodHandler.processSubmit();
        tinyMCE.triggerSave();
        var form = document.getElementById("configurator_step_form");
        var formData = new FormData(form);
        var step_option_id = $('#configurator_option_content').find('.option_settings').data('step-id');
        if(step_option_id != null) {
            $.ajax({
                url: window.location.href+'&save_option=1&ajax=1&step_id='+step_option_id,
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (result) {
                    result = JSON.parse(result);
                    showSuccessMessage(result.message);
                },
                error: function(result) {
                    result = JSON.parse(result);
                    showErrorMessage(result.message);
                }
            });
        }
    },
    displayOptionForm: function(elt) {
        var id_option = elt.attr('href');
        this.load(elt);
        $.post(window.location.href+'&id_option_html='+id_option, {
            'ajax': true,
        }, function(html) {
            $('#configurator_option_content').html(html);
            $('html, body').animate({
                scrollTop: $('#configurator_option_content').offset().top - 200
            }, 1000);
            priceImpactHandler.update();
            displayConditionsHandler.update();
            divisionHandler.update();
            $('[data-toggle="tooltip"]').tooltip();
            tinySetup({
                editor_selector :"autoload_rte"
            });
            tabs_manager.allow_hide_other_languages = false;
            hideOtherLanguage(id_language);
        });
    },
    load: function(elt) {
        $('#configurator_option_content').html('<div class="loading"></div>');
        $(this.table_option_class+' tbody > tr').each(function() {
            $(this).removeClass('active');
        });
        elt.parent().parent().parent().parent().addClass('active');
    },
    updateSettingsPanel: function(type, disable_options) {
        if(disable_options === undefined) {
            disable_options = true;
        }
        $('[step-type]').hide();
        $('[step-type]').each(function(){
            if(disable_options) {
                var switch_el = $(this).find('.switch');
                if(switch_el.length > 0) {
                    switch_el.find('[id*="_off"]').click();
                }
            }
        });
        $('[step-type*='+type+']').show('slow');
    },
    updateStateDisplayTotalAndMultipleAndUseQtyOption: function(disable) {
        if (disable) {
            $(this.display_total_id_prefix + 'off').trigger('click');
            $(this.display_total_id_prefix + 'on').attr('disabled', true);
            $(this.use_qty_id_prefix + 'off').trigger('click');
            $(this.use_qty_id_prefix + 'on').attr('disabled', true);
            $(this.multiple_id_prefix + 'off').trigger('click');
            $(this.multiple_id_prefix + 'on').attr('disabled', true);
        } else if(!disable
            && !$(this.use_pricelist_id_prefix + 'on').is(':checked')
            && !$(this.use_input_id_prefix + 'on').is(':checked')
        ){
            $(this.display_total_id_prefix + 'on').attr('disabled', false);
            $(this.display_total_id_prefix + 'off').attr('disabled', false);
            $(this.use_qty_id_prefix + 'on').attr('disabled', false);
            $(this.use_qty_id_prefix + 'off').attr('disabled', false);
            $(this.multiple_id_prefix + 'on').attr('disabled', false);
            $(this.multiple_id_prefix + 'off').attr('disabled', false);
        }
    },
    updateStateUniquePrice: function(disable) {
        if (disable) {
            $(this.unique_price_id_prefix + 'off').trigger('click');
            $(this.unique_price_id_prefix + 'on').attr('disabled', true);
        } else if(!disable
            && !$(this.use_pricelist_id_prefix + 'on').is(':checked')
            && !$(this.use_input_id_prefix + 'on').is(':checked')
        ){
            $(this.unique_price_id_prefix + 'off').attr('disabled', false);
            $(this.unique_price_id_prefix + 'on').attr('disabled', false);
        }
    },
    updateStateUseShared: function(disable) {
        if (disable) {
            $(this.use_shared_id_prefix + 'off').trigger('click');
            $(this.use_shared_id_prefix + 'on').attr('disabled', true);
            $(this.use_shared_id_prefix + 'off').attr('disabled', true);
        } else {
            $(this.use_shared_id_prefix + 'on').attr('disabled', false);
            $(this.use_shared_id_prefix + 'off').attr('disabled', false);
        }
    },
    updateStatePriceListAndInputOptions: function(disable) {
        if (disable) {
            $(this.use_input_id_prefix + 'off').trigger('click');
            $(this.use_pricelist_id_prefix + 'off').trigger('click');
            $(this.use_input_id_prefix + 'on').attr('disabled', true);
            $(this.use_pricelist_id_prefix + 'on').attr('disabled', true);
        } else if(!disable
            && !$(this.display_total_id_prefix + 'on').is(':checked')
            && !$(this.use_qty_id_prefix + 'on').is(':checked')
            && !$(this.multiple_id_prefix + 'on').is(':checked')
            && !$(this.unique_price_id_prefix + 'on').is(':checked')
        ) {
            $(this.use_input_id_prefix + 'on').attr('disabled', false);
            $(this.use_input_id_prefix + 'off').attr('disabled', false);
            $(this.use_pricelist_id_prefix + 'on').attr('disabled', false);
            $(this.use_pricelist_id_prefix + 'off').attr('disabled', false);
        }
    },
    updateStatePriceList: function(disable) {
        if (disable) {
            $(this.use_pricelist_id_prefix + 'off').trigger('click');
            $(this.use_pricelist_id_prefix + 'on').attr('disabled', true);
            $(this.use_pricelist_id_prefix + 'off').attr('disabled', true);
        } else if(!disable
            && !$(this.display_total_id_prefix + 'on').is(':checked')
            && !$(this.use_qty_id_prefix + 'on').is(':checked')
            && !$(this.multiple_id_prefix + 'on').is(':checked')
            && !$(this.unique_price_id_prefix + 'on').is(':checked')
            && !$(this.use_input_id_prefix + 'on').is(':checked')
        ) {
            $(this.use_pricelist_id_prefix + 'on').attr('disabled', false);
            $(this.use_pricelist_id_prefix + 'off').attr('disabled', false);
        }
    },
    updateStateInputOption: function(disable) {
        if (disable) {
            $(this.use_input_id_prefix + 'off').trigger('click');
            $(this.use_input_id_prefix + 'on').attr('disabled', true);
            $(this.use_input_id_prefix + 'off').attr('disabled', true);
        } else if(!disable
            && !$(this.display_total_id_prefix + 'on').is(':checked')
            && !$(this.use_qty_id_prefix + 'on').is(':checked')
            && !$(this.multiple_id_prefix + 'on').is(':checked')
            && !$(this.unique_price_id_prefix + 'on').is(':checked')
            && !$(this.use_pricelist_id_prefix + 'on').is(':checked')
        ) {
            $(this.use_input_id_prefix + 'on').attr('disabled', false);
            $(this.use_input_id_prefix + 'off').attr('disabled', false);
        }
    },
    updateMaxQty: function(show) {
        var form_group = $(this.max_qty_group_id);
        var form_coef = $(this.max_qty_coef_id);
        if (show) {
            form_group.fadeIn();
            form_coef.fadeOut();
        }
        else {
            form_group.fadeOut();
            form_coef.fadeIn();
        }
    },
    showUniquePrice: function(show) {
        var form_group = $(this.unique_price_block_id);
        if (show) {
            form_group.fadeIn();
        }
        else {
            form_group.fadeOut();
        }
    },
    // Show input maximum options when multiple choices is enabled
    showInputMaximumOptions: function(show) {
        var form_group = $(this.max_options_block_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    // Show block maximum quantities when input quantities is enabled
    showMaxQtyForm: function(show) {
        var form_group = $(this.max_qty_block_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    // Show input suffix form when text field used
    showInputSuffixForm: function(show) {
        var form_group = $(this.text_field_block_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    showPriceListForm: function(show) {
        var form_group = $(this.price_list_block_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    showPriceListHeader: function(show) {
        var form_group = $(this.price_list_header_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    showCustomTemplateForm: function(show) {
        var form_group = $(this.custom_template_block_id);
        if (show) {
            form_group.fadeIn();
        } else {
            form_group.fadeOut();
        }
    },
    // Show specific warning block
    showWarningBlock: function(id) {
        $('#' + id).fadeIn();
    },
    hideWarningBlock: function(id) {
        $('#' + id).hide();
    },
    // Process ajax when click event triggered on list-action-enable btn
    processAjaxUsed: function(el) {
        var self = this;
        var href = el.attr('href');
        var loading = self.getLoader();

        el.find('i').addClass('hidden');
        el.append(loading);

        $.post(href, {
            'ajax': true,
            'action': 'updateOptionUsed'
        }, function(data) {
            data = JSON.parse(data);
            if (data.status === 'ok') {
                el.find('i.' + self.loader_class_name).remove();
                showSuccessMessage(data.message);
                self.processEnableUsedButton(el, data.enable);
                self.changeId(el.parent().parent(), data.new_id);
            } else {
                showErrorMessage(data.message);
            }
        });
    },
    changeId: function(el, new_id) {
        el.find('.id-step-option').html(new_id);
        el.find('.btn-group-action .edit').attr('href',new_id);
        var href = el.find('.btn-group-action .dropdown-menu a').attr('href');
        href = href.slice(0, href.lastIndexOf('='));
        el.find('.btn-group-action .dropdown-menu a').attr('href',href+'='+new_id);

        el.find('.btn-group-action .edit').removeAttr('disabled');
        el.find('.btn-group-action .dropdown-menu a').removeClass('link-disabled');
        if(new_id < 1) {
            el.find('.btn-group-action .edit').attr("disabled","disabled");
            el.find('.btn-group-action .dropdown-menu a').addClass('link-disabled');
        }
    },
    processShowPriceImpact: function(el) {
        var id = el.attr('href');
        this.hideWarningBlock('alert-price-impact');
        $(this.price_impact_class).hide();
        $(this.price_impact_block_id + id).fadeIn();
    },
    processShowDisplayCondition: function(el) {
        var id = el.attr('href');
        this.hideWarningBlock('alert-display-conditions');
        $(this.display_conditions_class).hide();
        $(this.display_conditions_block_id + id).fadeIn();
    },
    processShowDivision: function(el) {
        var id = el.attr('href');
        this.hideWarningBlock('alert-division');
        $(this.division_class).hide();
        $(this.division_block_id + id).fadeIn();
    },
    processShowOptionSettings: function(el) {
        var id = el.attr('href');
        this.hideWarningBlock('alert-option-settings');
        $(this.option_settings_block_class).hide();
        $(this.option_settings_block_id + id).fadeIn();
    },
    processSelectedDefault: function(el, only_delete, id_step_option) {
        if (only_delete === undefined) {
            only_delete = 0;
        } else if (only_delete) {
            only_delete = 1;
        }
        var href = el.attr('href');
        var self = this;
        $.post(href, {
            'ajax': true,
            'action': 'updateSelectedDefaultOption',
            'deletedefaultoption': only_delete,
            'id_step_option': id_step_option
        }, function(data) {
            data = JSON.parse(data);
            if (data.status === 'ok') {
                showSuccessMessage(data.message);
                self.editSelectedDefaultHtml(el, only_delete, data.multiple);
            } else {
                showErrorMessage(data.message);
            }
        });
    },
    editSelectedDefaultHtml: function(el, only_delete, multiple) {
        if (only_delete === undefined) {
            only_delete = false;
        }
        if (multiple === undefined) {
            multiple = 0;
        }
        if (parseInt(multiple) < 1) {
            var table = el.closest('table');
            table.find('tbody ' + this.tr_default_selected_class).html('');
        }
        if(only_delete){
            el.closest('tr').find(this.tr_default_selected_class).html('');
        } else {
            el.closest('tr').find(this.tr_default_selected_class).html('<i class="icon-asterisk"></i>');
        }
    },
    // Process edit html list-action-enable btn if enable or not
    processEnableUsedButton: function(btn, enable) {
        if (enable) {
            btn.removeClass(this.actiondisable_class_name);
            btn.addClass(this.actionenable_class_name);
            btn.find('i' + this.iconcheck_class).removeClass('hidden');
        } else {
            btn.removeClass(this.actionenable_class_name);
            btn.addClass(this.actiondisable_class_name);
            btn.find('i' + this.iconremove_class).removeClass('hidden');
        }
    },
    // Get icon loader
    getLoader: function() {
        return $('<i>').addClass(this.loader_class_name);
    }
};
