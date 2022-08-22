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

CFFrontCategoryController = function(wrapper) {
    var self = this;
    self.wrapper = wrapper;
    self.$wrapper = $(wrapper);

    self.expand = function (sender) {
        var target_wrapper = sender.parents('.categoryextrafield');

        target_wrapper.find('.content a.read-less').fadeIn();
        target_wrapper.find('.content a.read-more').fadeOut();

        target_wrapper.find('.excerpt').hide();
        target_wrapper.find('.content').slideDown();
    };

    self.collapse = function (sender) {
        var target_wrapper = sender.parents('.categoryextrafield');

        target_wrapper.find('.content a.read-less').fadeOut();
        target_wrapper.find('.content a.read-more').fadeIn();

        target_wrapper.find('.excerpt').show();
        target_wrapper.find('.content').slideUp();
    };

    self.init = function () {
    };
    self.init();

    /* Events */
    self.$wrapper.find('a.read-more').click(function () {
        self.expand($(this));
        return false;
    });

    self.$wrapper.find('a.read-less').click(function () {
        self.collapse($(this));
        return false;
    })

};

$(document).ready(function() {
    var cf_front_category_controller = new CFFrontCategoryController(".categoryextrafield");
});