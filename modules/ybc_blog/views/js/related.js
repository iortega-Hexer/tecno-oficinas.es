/**
 * 2007-2019 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
 $(document).ready(function(){
    $.ajax({
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: '',
		async: true,
		cache: false,
		dataType : "json",
		data:'displayPostRelatedCategories=1',
		success: function(jsonData)
		{
		      if($('#category #main').length)
              {
                    $('#category #main').append(jsonData.html_block);
              }
              else if($('#category #center_column').length)
              {
                    $('#category #center_column').append(jsonData.html_block);
              }
              if ($('.page_home.ybc_block_slider ul').length > 0)
            	$('.page_home.ybc_block_slider ul').owlCarousel({            
                    items : number_category_posts_per_row,
                    navigation : true,
                    navigationText : ["",""],
                    pagination : false,
                    itemsDesktop : [1199,4],
                    itemsDesktopSmall : [992,3],
                    itemsTablet: [768,2],
                    itemsMobile : [480,1],
                    responsive : {
                        0 : {
                            items : 1
                        },
                        480 : {
                            items : 2
                        },
                        768 : {
                            items : 3
                        },
                        992 : {
                            items : 3
                        },
                        1199 : {
                            items : number_category_posts_per_row
                        }
                    },
                    nav : true,  
                    loop: $(".page_home.ybc_block_slider ul li").length > 1,
                    rewindNav : false,
                    dots : false,         
                    navText: ['', ''],  
                    callbacks: true
                });
        }
	});
 });