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

CFAdminConfigMainController = function(wrapper) {
	var self = this;
	self.wrapper = wrapper;
	self.$wrapper = $(wrapper);

	/* function render main form into the tab canvas */
	self.render = function() {
		self.renderAddForm();
        self.renderList();
	};

	/**
	 * Load Add blocked date form Partial into view via ajax
 	 */
	self.renderAddForm = function() {
        MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=cfadminconfigmaincontoller&action=renderaddform',
			async: true,
			cache: false,
			data: {
			},
			success: function (html_content) {
				MPTools.waitEnd();
				self.$wrapper.find("#form-categoryfields-add").html(html_content);
				//prestaShopUiKit.init();
			}
		});
	};

    /**
     * Render List of blocked dates
      */
    self.renderList = function()
    {
		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=cfadminconfigmaincontoller&action=renderlist',
			async: true,
			cache: false,
			data: {},
			success: function (html_content) {
                self.$wrapper.find("#categoryfields-list").html(html_content);
				MPTools.waitEnd();
				return false;
			}
		});

    };

    /**
     * Process add category field
     */
	self.processForm = function() {

		if (!MPTools.validateForm("#form-categoryfields-add")) {
			return false;
		}

		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=cfadminconfigmaincontoller&action=processaddform',
			async: true,
			cache: false,
			data: self.$wrapper.find("#form-categoryfields-add :input, select").serialize(),
			success: function (result) {
                self.renderList();
				MPTools.waitEnd();
				return false;
			}
		});
	};

    /**
     * process delete blocked date
     * @param id_ddw_blockeddate
     */
    self.processDelete = function(id_categoryfield) {
		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=cfadminconfigmaincontoller&action=processdelete',
			async: true,
			cache: false,
			data: {
                id_categoryfield: id_categoryfield
            },
			success: function (result) {
                self.renderList();
				MPTools.waitEnd();
				return false;
			}
		});
    };

	/**
	 * Process the renaming of a category field
 	 * @param id_categoryfield
     */
	self.processRename = function(id_categoryfield) {
		MPTools.waitStart();
		$.ajax({
			type: 'POST',
			url: module_config_url + '&route=cfadminconfigmaincontoller&action=processrename',
			async: true,
			cache: false,
			data: {
                id_categoryfield: id_categoryfield
            },
			success: function (result) {
                self.renderList();
				MPTools.waitEnd();
				return false;
			}
		});
	};

	self.init = function() {
		self.render();
	};
	self.init();

	/* Events */

	$("body").on("click", self.wrapper + " #btn-cf-field-save", function () {
		self.processForm();
		return false;
	});

    /**
     * Delete category field
      */
	$("body").on("click", self.wrapper + " .cf-categoryfield-delete", function () {
		if (confirm('Are you sure you want to delete this field completely from all categories?')) {
			self.processDelete($(this).attr("data-id"));
		}
		return false;
	});


	/**
	 * Rename category field icon click
 	 */
	$("body").on("click", self.wrapper + " .cf-categoryfield-edit", function () {
		var $form_add = $("#form-categoryfields-add");
		$form_add.find("input#name").val($(this).attr("data-name"));
		$form_add.find("input#id_categoryfield").val($(this).attr("data-id"));

		var collapsible = $(this).attr("data-collapsible");
		if (collapsible == '1') {
			$form_add.find("input#collapsible").prop("checked", true);
		} else {
			$form_add.find("input#collapsible").prop("checked", false);
		}
		return false;
	});


};

