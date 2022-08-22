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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function(){

    callNextStep = function(elt, url) {
        $('.status').show();
        $.ajax({
            dataType: "json",
            async: true,
            url: url,
            method: 'GET',
            success: function(data) {

                if (typeof(data.status) != 'undefined'){
                    $('.status').find('ul').append($('<li>').html($('.rebuild-index').data('processing')+' '+data.status));
                }

                if (typeof(data.indexed) != 'undefined' && typeof(data.total) != 'undefined'){
                    $('.indexed-products').html(data.indexed+' / '+data.total);
                }

                if (typeof(data.url) != 'undefined' && data.url !== false) {
                    callNextStep(elt, data.url);
                } else {
                    $('.status').find('ul').append($('<li>').html($('.rebuild-index').data('done')));
                    elt.removeClass('waiting');
                    elt.attr('disabled', false);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(xhr);
            }
        });
    }

    $(document).on('click', '.rebuild-index', function(e){
        $(this).addClass('waiting');
        $(this).attr('disabled', true);
        $('.status').find('ul').html($('<li>').html($('.rebuild-index').data('starting')));

        callNextStep($(this), $(this).data('url'), 0);
    });

});