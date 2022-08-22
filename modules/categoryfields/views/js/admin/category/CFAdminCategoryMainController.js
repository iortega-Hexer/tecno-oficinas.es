/*
 * 2007-2015 PrestaShop
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
 *  @author Musaffar Patel <musaffar.patel@gmail.com>
 *  @copyright  2015-2017 Musaffar
 *  @version  Release: $Revision$
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Property of Musaffar Patel
 */

CFAdminCategoryMainController = function(wrapper) {
    var self = this;
    self.wrapper = wrapper;
    self.$wrapper = $(wrapper);

    self.getSelectedCategoryFieldID = function() {
        return self.$wrapper.find("select#id_categoryfield").val();
    };

    /**
     * Creates a hidden field containing category field content (if not exists) for processing after form submit
     * @param id_categoryfield
     * @param id_lang
     */
    self.createField = function(id_categoryfield, id_lang) {
        if (self.$wrapper.find("textarea#cf_content_"+ id_categoryfield+'_'+id_lang).length == 0) {
            $('<textarea>').attr({
                //type: 'hidden',
                id: 'cf_content_' + id_categoryfield+'_'+id_lang,
                name: 'cf_content_' + id_categoryfield + '_' + id_lang,
                style: 'display: none'
            }).appendTo(self.$wrapper);
        }

        if (self.$wrapper.find("textarea#cf_excerpt_"+ id_categoryfield+'_'+id_lang).length == 0) {
            $('<textarea>').attr({
                //type: 'hidden',
                id: 'cf_excerpt_' + id_categoryfield+'_'+id_lang,
                name: 'cf_excerpt_' + id_categoryfield + '_' + id_lang,
				style: 'display: none'
            }).appendTo(self.$wrapper);
        }
    };

    /**
     * set content of hidden field
     * @param id_categoryfield
     * @param id_lang
     * @param content
     */
    self.setFieldContent = function(id_categoryfield, id_lang, content) {
        self.$wrapper.find("textarea#cf_content_"+ id_categoryfield+'_'+id_lang).html(content);
    };

    self.setFieldExcerpt = function(id_categoryfield, id_lang, content) {
        self.$wrapper.find("textarea#cf_excerpt_"+ id_categoryfield+'_'+id_lang).html(content);
    };

    /**
     * Called on blur to set the content of the corresponding hidden field for post processing
     * @param id_categoryfield
     */
    self.applyField = function(id_categoryfield) {
        if (typeof id_categoryfield === 'undefined' || id_categoryfield == '')
            return false;
        for (i=0; i<=languages.length-1; i++) {
            var content = tinymce.get('categoryfield_content_' + languages[i]['id_lang']).getContent();
            var excerpt = tinymce.get('categoryfield_excerpt_' + languages[i]['id_lang']).getContent();
            self.createField(id_categoryfield, languages[i]['id_lang']);
            self.setFieldContent(id_categoryfield, languages[i]['id_lang'], content);
            self.setFieldExcerpt(id_categoryfield, languages[i]['id_lang'], excerpt);
        }
    };

    /**
     * Set the tinymce content based on id_categoryfield
     * @param id_categoryfield
     */
    self.setEditorContent = function(id_categoryfield) {
        for (i=0; i<=languages.length-1; i++) {
            var content = $("textarea#cf_content_" + id_categoryfield + '_' + languages[i]['id_lang']).val();
			var excerpt = $("textarea#cf_excerpt_" + id_categoryfield + '_' + languages[i]['id_lang']).val();
			
            if (typeof content !== 'undefined') {
                tinymce.get('categoryfield_content_' + languages[i]['id_lang']).setContent(content);
            } else {
				tinymce.get('categoryfield_content_' + languages[i]['id_lang']).setContent('');
            }

            if (typeof excerpt !== 'undefined') {
                tinymce.get('categoryfield_excerpt_' + languages[i]['id_lang']).setContent(excerpt);
            } else {
				tinymce.get('categoryfield_excerpt_' + languages[i]['id_lang']).setContent('');
            }
        }
    };

    /**
     * Disable both editors for all languages
     */
    self.disableEditors = function() {
        for (i = 0; i <= languages.length - 1; i++) {
            tinymce.get('categoryfield_content_' + languages[i]['id_lang']).getBody().setAttribute('contenteditable', false);
            tinymce.get('categoryfield_excerpt_' + languages[i]['id_lang']).getBody().setAttribute('contenteditable', false);
        }
    };

    /**
     * Enable both editors for all languages
     */
    self.enabledEditors = function() {
        for (i = 0; i <= languages.length - 1; i++) {
            tinymce.get('categoryfield_content_' + languages[i]['id_lang']).getBody().setAttribute('contenteditable', true);
            tinymce.get('categoryfield_excerpt_' + languages[i]['id_lang']).getBody().setAttribute('contenteditable', true);
        }
    };

    /**
     * Additional init for tinyMCE
     */
    self.initTinyMCE = function() {
        for (i=0; i<=languages.length-1; i++) {
            tinymce.get('categoryfield_content_'+languages[i]['id_lang']).on("blur", function(ed) {
                self.applyField(self.getSelectedCategoryFieldID());
            });
            tinymce.get('categoryfield_excerpt_'+languages[i]['id_lang']).on("blur", function(ed) {
                self.applyField(self.getSelectedCategoryFieldID());
            });
        }
    };

    self.init = function() {
        self.initTinyMCE();
        self.disableEditors();
    };
    self.init();

    /* Events */

    /**
     * on category field change
     */
    self.$wrapper.find("select#id_categoryfield").change(function() {
        self.setEditorContent(self.getSelectedCategoryFieldID());
        self.enabledEditors();
    });

};