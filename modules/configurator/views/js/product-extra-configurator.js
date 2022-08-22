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


/* UPLOAD */
var CONFIGURATOR_PRODUCT_UPLOAD_MANAGER = {
    
    id_file: '#cover',
    id_btn_add_file: '#btn_add_file',
    id_div_preview_cover: '#configurator-preview-cover',
    id_img_preview_cover: '#configurator-preview-cover-img',
    
    init: function () {
        var self = this;
        $('body').find(self.id_btn_add_file).click(function() {
            var url = $('body').find(self.id_btn_add_file).data('target-url');
            var data = new FormData();
            if ($('body').find(self.id_file)[0].files[0]) {
                data.append('cover', $('body').find(self.id_file)[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.success === 1) {
                            showSuccessMessage(response.message);
                            $("body").find(self.id_file).val("");
                            $('body').find(self.id_img_preview_cover).attr('src',response.cover_url);
                            $('body').find(self.id_div_preview_cover).show('slow');
                        } else {
                            showErrorMessage(response.errors);
                        }
                    },
                    error: function(response) {
                        console.log('upload error');
                        console.log(response);
                    }
                });
            }
        });
    },
    
};


/* DUPLICATE */

var CONFIGURATOR_PRODUCT_DUPLICATE_MANAGER = {
    
    id_btn_duplicate: '#configurator_btn_duplicate',
    
    init: function () {
		$(this.id_btn_duplicate).click(function() {
			var value = $('select[name="duplicate_configurator"]').val();
			window.location.href = $(this).data('href')+value;
		});
    },
    
};