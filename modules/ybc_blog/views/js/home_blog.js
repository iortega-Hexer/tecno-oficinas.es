/**
 * 2007-2022 ETS-Soft
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
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
$(document).ready(function(){
    runowl();
});
function runowl()
{
    if ( $('.ybc_blog_rtl_mode').length > 0 ){
        var rtl_blog = true;

    } else {
        var rtl_blog = false;
    }
    if ($('.page_home.ybc_block_slider ul').length > 0) {
        $('.page_home.ybc_block_slider ul').etsowlCarousel({
            items: number_home_posts_per_row,
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 3
                },
                1199: {
                    items: number_home_posts_per_row
                }
            },
            rtl: rtl_blog,
            nav: true,
            dots: true,
            loop: $(".page_home.ybc_block_slider ul li").length > 1,
            rewindNav: false,
            dots: false,
            navText: ['', ''],
            callbacks: true
        });
    }
 
 if ($('.page_home_gallery.ybc_block_slider ul').length > 0)
	$('.page_home_gallery.ybc_block_slider ul').etsowlCarousel({
        items : number_home_posts_per_row,
        navigation : true,
        navigationText : ["",""],
        pagination : false,
        itemsDesktop : [1199,6],
        itemsDesktopSmall : [992,5],
        itemsTablet: [768,4],
        itemsMobile : [480,3],
        rtl: rtl_blog,
        responsive : {
                0 : {
                    items : 3
                },
                480 : {
                    items : 4
                },
                768 : {
                    items : 5
                },
                992 : {
                    items : number_home_posts_per_row
                }
            },
        nav : true,  
        loop: $(".page_home_gallery.ybc_block_slider ul li").length > 1,
        rewindNav : false,
        dots : false,         
        navText: ['', ''],  
        callbacks: true,  
    });
 }