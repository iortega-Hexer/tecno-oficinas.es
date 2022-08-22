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

var CONFIGURATOR_PRODUCT_MANAGER = {
    setup: {
        // CLASS
        btn_confirm_tab_class: '.btn_confirm_tab',
        configurator_tab_list_btn_edit_class: '.configurator-tab-list-btn-edit',
        configurator_tab_list_btn_edit_pos_down_class: '.configurator-tab-list-btn-edit-position-down',
        configurator_tab_list_btn_edit_pos_up_class: '.configurator-tab-list-btn-edit-position-up',
        configurator_tab_list_btn_delete_class: '.configurator-tab-list-btn-delete',
        // ID
        id_configurator_step_tab_id: '#id_configurator_step_tab',
        btn_add_tab_id: '#btn_add_tab',
        tab_name_id_prefix: '#tab_name_',
        add_tab_id: '#add_tab',
        input_id_configurator_step_tab_id: '#input_id_configurator_step_tab',
        configurator_tab_list_id: '#configurator-tab-list',
        configurator_template_tab_id: '#configurator_template_tab',
        alert_table_empty_id: '#alert-table-empty',
        // OTHER
        isVisible: ':visible',
        isHidden: ':hidden',
        // DATA
        id_configurator: 0,
        admin_link: '',
        tabs: [],
        lang_list: [],
        translation: {}
    },
    /**
     * Initialization
     * @return {undefined}
     */
    init: function (setup) {
        var self = this;

        $.extend(true, self.setup, setup);

        self.initTableTabs();

        // Init differents elements
        self.bindAll();
        //self.initTranslation();
        self.displayTable();
        self.checkLang();
    },
    /**
     * Open form when button add a tab is clicked
     * @returns {undefined}
     */
    processAddEditBlock: function (event) {
        var self = this;
        var $currentTarget = $(event.currentTarget);

        if ($(self.setup.btn_confirm_tab_class).data('process') === 'adding') {
            /**
             * Empty input name
             */
            $.each(self.setup.lang_list, function (key, lang) {
                $(self.setup.tab_name_id_prefix + lang.id_lang).val('');
            });
        } else if ($(self.setup.btn_confirm_tab_class).data('process') === 'editing') {
            /**
             *  Put tab id in input tab id
             */
            $(self.setup.id_configurator_step_tab_id).val($currentTarget.data('id'));
            /**
             * Put tab name in each lang in input tab name
             */
            $.each(self.setup.lang_list, function (key, lang) {
                var value = $currentTarget.parent().parent().find('.lang-' + lang.id_lang).text();
                $(self.setup.tab_name_id_prefix + lang.id_lang).val(value);
            });
        }
    },
    /**
     * @param {type} event
     * @returns {undefined}
     */
    processAjaxPosition: function (event, type = 'down') {
        var self = this;
        var $currentTarget = $(event.currentTarget);

        var datas = {
            ajax: true,
            id: $currentTarget.data('id'),
            type: type,
            action: 'position'
        };

        self.processAjaxSendDatas(event, datas);
    },
    movePosition: function (event, type = 'down') {
        var self = this;
        var tr = $(event.currentTarget).parent().parent();

        if (type == 'down') {
            var next = tr.next();
            next.after(tr);
        } else {
            var prev = tr.prev();
            prev.before(tr);
        }

        console.log(tr);
    },
    /**
     * Delete a tab when delete button on a row is clicked
     * @param {type} event
     * @returns {undefined}
     */
    processAjaxDelete: function (event) {
        var self = this;
        var $currentTarget = $(event.currentTarget);

        var datas = {
            ajax: true,
            id: $currentTarget.data('id'),
            action: 'delete'
        };

        if (confirm(self.getTranslationbyKey('confirm_delete'))) {
            self.processAjaxSendDatas(event, datas);
        }
    },
    /**
     * Add a tab when add button in add/edit block is clicked
     * @returns {undefined}
     */
    processAjaxAdd: function (event) {
        var self = this;

        var datas = {
            ajax: true,
            id_configurator: self.setup.id_configurator,
            action: 'add'
        };

        self.processAjaxSendDatas(event, datas);
    },
    /**
     * Update a tab when edit button in add/edit block is clicked
     * @returns {undefined}
     */
    processAjaxUpdate: function (event) {
        var self = this;

        var datas = {
            ajax: true,
            id: $(self.setup.id_configurator_step_tab_id).val(),
            action: 'update'
        };

        self.processAjaxSendDatas(event, datas);
    },
    /**
     * Send ajax when action is add, delete or update, same process for all
     * @param {type} event
     * @param {type} datas
     * @returns {undefined}
     */
    processAjaxSendDatas: function (event, datas) {
        var self = this;
        var $currentTarget = $(event.currentTarget);
        var buttonsDelete = document.getElementsByClassName(self.setup.configurator_tab_list_btn_delete_class);

        $.each(self.setup.lang_list, function (key, lang) {
            datas['name_' + lang.id_lang] = $(self.setup.tab_name_id_prefix + lang.id_lang).val();
        });
        if (datas.action !== 'delete') {
            $(self.setup.btn_confirm_tab_class)
                    .attr("disabled", "disabled")
                    .find('i').attr('class', "process-icon-loading");
        }

        $.post(self.setup.admin_link,
                datas,
                function (result) {
                    result = JSON.parse(result);

                    if (datas.action !== 'delete') {
                        $(self.setup.btn_confirm_tab_class)
                                .removeAttr("disabled")
                                .find('i').attr('class', "process-icon-save");
                    }

                    if (result.success === 1) {
                        showSuccessMessage(result.message);

                        if (typeof datas.id === 'undefined') {
                            datas['id'] = result.tab.id;
                        }

                        if (datas.action === 'delete') {
                            $currentTarget.parent().parent().remove();
                        } else if (datas.action === 'position') {
                            self.movePosition(event, datas.type);
                        } else {
                            self.showTemplate(datas);
                            $(self.setup.btn_add_tab_id).trigger('click');
                            self.checkLang();
                        }

                        self.displayTable();
                    } else {
                        showErrorMessage(result.message);
                    }
                }
        );
    },
    /**
     * Attach all HTML elements to JS actions
     * @return {undefined}
     */
    bindAll: function () {
        var self = this;

        // Ouverture du block ajout / modification tab
        $(self.setup.btn_add_tab_id).click(function (event) {
            self.setFocusInputName();
            $(self.setup.btn_confirm_tab_class).data('process', 'adding');
            self.processAddEditBlock(event);
        });

        // Clic sur le bouton edit pour une tabs particulière
        $(self.setup.configurator_tab_list_id).on('click', self.setup.configurator_tab_list_btn_edit_class, function (event) {
            self.setFocusInputName();
            $(self.setup.btn_confirm_tab_class).data('process', 'editing');
            self.processAddEditBlock(event);
        });

        // Click happened on button position on a row
        $(self.setup.configurator_tab_list_id).on('click', self.setup.configurator_tab_list_btn_edit_pos_down_class, function (event) {
            self.processAjaxPosition(event, 'down');
        });
        $(self.setup.configurator_tab_list_id).on('click', self.setup.configurator_tab_list_btn_edit_pos_up_class, function (event) {
            self.processAjaxPosition(event, 'up');
        });

        // Click happened on button delete on a row
        $(self.setup.configurator_tab_list_id).on('click', self.setup.configurator_tab_list_btn_delete_class, function (event) {
            self.processAjaxDelete(event);
        });

        $(self.setup.btn_confirm_tab_class).on('click', function (event) {
            // Click happened on button edit in add/edit block
            if ($(self.setup.btn_confirm_tab_class).data('process') === 'editing') {
                self.processAjaxUpdate(event);
                // Click happened on button add in add/edit block
            } else if ($(self.setup.btn_confirm_tab_class).data('process') === 'adding') {
                self.processAjaxAdd(event);
            }
        });

        $(self.setup.alert_table_empty_id).find('a').on('click', function (event) {
            $(self.setup.btn_add_tab_id).trigger('click');
        });
    },
    /**
     * Function used to hide translatable fields when they are added in table, or when page loads
     * @returns {undefined}
     */
    checkLang: function () {
        var self = this;
        var lang_visible = self.getInputNameVisible();

        if (typeof tabs_manager !== 'undefined') {
            tabs_manager.allow_hide_other_languages = false;
            hideOtherLanguage(lang_visible);
        }
    },
    setFocusInputName: function () {
        var self = this;

        $(self.setup.tab_name_id_prefix + self.getInputNameVisible()).focus();
    },
    getInputNameVisible: function () {
        var self = this;
        var lang_visible;

        $.each(self.setup.lang_list, function (key, lang) {
            // happens when no input name is visible or in first loop
            if (typeof lang_visible === 'undefined') {
                lang_visible = lang.id_lang;
            }
            if ($(self.setup.tab_name_id_prefix + lang.id_lang).is(self.setup.isVisible)) {
                lang_visible = lang.id_lang;
                return false;
            }
        });

        return lang_visible;
    },
    /**
     * Show compiled template with datas
     * @param {type} datas
     * @returns {undefined}
     */
    showTemplate: function (datas) {
        var self = this;
        var $content_block = $(self.setup.configurator_tab_list_id + ' tbody');
        var template = self.getCompiledTemplate(self.setup.configurator_template_tab_id);

        if (datas.action === 'add') {
            $content_block.append(template(datas));
        } else if (datas.action === 'update') {
            $content_block.find('tr[data-id=' + datas.id + ']').replaceWith(template(datas));
        }
    },
    /**
     * Init content of the table 
     * @returns {undefined}
     */
    initTableTabs: function () {
        var self = this;

        $.each(self.setup.tabs, function (key, value) {
            var datas = {
                id: value.id,
                action: 'add'
            };

            $.each(self.setup.lang_list, function (key, lang) {
                datas['name_' + lang.id_lang] = value.name[lang.id_lang];
            });

            self.showTemplate(datas);
        });
    },
    /**
     * Show or hide table and alert depending on whether the table is empty or not
     * @returns {undefined}
     */
    displayTable: function () {
        var self = this;

        if ($(self.setup.configurator_tab_list_id + ' tbody tr').length === 0) {
            $(self.setup.configurator_tab_list_id).parent().hide();
            $(self.setup.alert_table_empty_id).parent().show();
        }
        else {
            $(self.setup.configurator_tab_list_id).parent().show();
            $(self.setup.alert_table_empty_id).parent().hide();
        }
    },
    initTranslation: function () {
        var self = this;

        self.setup.translation = {
        };
    },
    /**
     * 
     * @param {type} key
     * @returns {Window.setup.translation}
     */
    getTranslationbyKey: function (key) {
        var self = this;

        return self.setup.translation[key];
    },
    /**
     * Récupère un objet Handlebars pour la compilation ensuite.
     * Plus simple à administrer
     * 
     * @param {int} id
     * @return {Handlebars}
     */
    getCompiledTemplate: function (id) {
        return Handlebars.compile($(id).html());
    }

};
