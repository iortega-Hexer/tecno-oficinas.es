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
    createChart();
    $(document).on('change','#years',function(){
        changeFilterDate($(this));
    });
    $(document).on('change','#months', function () {
        changeFilterDate($('#years'));
    });
    $('button[name="clearLogSubmit"]').click(function(){
       var result = confirm(detele_log);
       if (result) {
            return true;
       } 
       return false;
    });
    $(document).on('click','.close_tagify',function(){
        $(this).parent().remove();
        $('input[name="id_post"]').val('');
        $('#post_autocomplete_input').val('');
    });
    $('#post_autocomplete_input').autocomplete(ybc_blog_ajax_post_url,{
		minChars: 1,
		autoFill: true,
		max:20,
		matchContains: true,
		mustMatch:false,
		scroll:false,
		cacheLength:0,
		formatItem: function(item) {
			return '<img src="'+item[2]+'" style="width:24px;"/>'+' - '+item[0];
		}
	}).result(ybcAddPost);
    $('.form-group.form_group_post').hide();
    $('.form-group.form_group_post.'+$('.ets_form_tab_header span.active').attr('data-tab')).show();
    $('.ets_form_tab_header span').click(function(){
        if(!$(this).hasClass('active'))
        {
            $('.ets_form_tab_header span').removeClass('active');
            $(this).addClass('active');
            $('.form-group.form_group_post').hide();
            $('.form-group.form_group_post.'+$('.ets_form_tab_header span.active').attr('data-tab')).show();
            createChart();
        }
    });
 });
 function changeFilterDate(selector)
 {
    if (selector.length > 0)
    {
        if (selector.val() == '')
            $('#months option[value=""]').prop('selected', true);
    }
 }
 function createChart()
 {
    if (typeof ybc_blog_line_chart !== "undefined") {
        var slLabel = ybc_blog_x_days;
        if ($('#months').length > 0 && $('#months').val() == '' && $('#years').length > 0 && $('#years').val() != '')
            slLabel = ybc_blog_x_months;
        else if($('#years').length > 0 && $('#years').val() == '')
            slLabel = ybc_blog_x_years;
        nv.addGraph(function() {
            var line_chart = nv.models.lineChart()
                .useInteractiveGuideline(true)
                .x(function(d) { return (d !== undefined ? d[0] : 0); })
                .y(function(d) { return (d !== undefined ? parseInt(d[1]) : 0); }).color(['#0BDDE8','#FCA501','#A247FC'])
                .margin({left: 80})
                .showLegend(true)
                .showYAxis(true)
                .showXAxis(true);
            line_chart.xAxis
                .axisLabel(slLabel)
                .tickFormat(d3.format('d'));
            line_chart.yAxis
                .axisLabel(ybc_blog_y_label)
                .tickFormat(d3.format('d'));
            d3.select('.line_chart svg')
                .datum(ybc_blog_line_chart)
                .transition().duration(500)
                .call(line_chart);
            nv.utils.windowResize(line_chart.update);
            return line_chart;
        });
        
    }
 }
var ybcAddPost = function(event,data,formatted)
{
    if (data == null)
		return false;
	var postid = data[1];
	var postName = data[0];
	$('#product_autocomplete_input').val('');
	$('input[name="id_post"]').val(data[1]);
    $('.tagify-container-post').html('<span>'+data[0]+'<span class="close_tagify"></span></span>');
}