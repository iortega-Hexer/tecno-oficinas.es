{*
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
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<script type="text/javascript" src="{$js_dir_path|escape:'quotes':'UTF-8'}d3.v3.min.js"></script>
<script type="text/javascript" src="{$js_dir_path|escape:'quotes':'UTF-8'}nv.d3.min.js"></script>
<script type="text/javascript" src="{$js_dir_path|escape:'quotes':'UTF-8'}statistics.js"></script>
<script type="text/javascript">
    var detele_log ='{l s='If you clear "View log", view chart will be reset. Do you want to do that?' js='1' mod='ybc_blog'}';
    var ybc_blog_ajax_post_url ='{$ybc_blog_ajax_post_url|escape:'quotes':'UTF-8'}';
</script>
<div class="bootstrap">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                {$ybc_blog_sidebar nofilter}
                <div class="blog_center_content col-lg-10{if $control} ybc_blog{$control|escape:'html':'UTF-8'}{/if}">
                    <div class="panel statics_form">
                        <div class="panel-heading">
                    		<i class="icon icon-line-chart fa fa-line-chart"></i> {l s='Statistics' mod='ybc_blog'}
                        </div>
                        <div class="form-wrapper">
                            <div class="ets_form_tab_header">
                                <span {if $tab_ets=='chart'}class="active"{/if} data-tab="chart">{l s='Chart' mod='ybc_blog'}</span>
                                <span {if $tab_ets=='view-log'}class="active"{/if}  data-tab="view-log">{l s='Views log' mod='ybc_blog'}</span>
                                {if $YBC_BLOG_ALLOW_LIKE}
                                    <span {if $tab_ets=='like-log'}class="active"{/if}  data-tab="like-log">{l s='Likes log' mod='ybc_blog'}</span>
                                {/if}
                            </div>
                            <div class="form-group-wapper">
                                <div class="ctf_admin_statistic form-group form_group_post chart">
                                    <div class="ctf_admin_chart">
                                        <div class="line_chart">
                                            <svg style="width:100%; height: 500px;"></svg>
                                        </div>
                                    </div>
                                    <div class="ctf_admin_filter">
                                        <form id="ctf_admin_filter_chart" class="defaultForm form-horizontal" action="{$action|escape:'quotes'}" enctype="multipart/form-data" method="POST">
                                            <div class="ctf_admin_filter_chart_settings">
                                                    <div class="ctf_admin_filter_cotactform">
                                                        <label for="id_post_serach">{l s='Post' mod='ybc_blog'}</label>
                                                        <div class="input-group">
                                                            <input id="post_autocomplete_input" class="ac_input" name="post_autocomplete_input" placeholder="ID or name" autocomplete="off" type="text" value="{if $ctf_post}{$ctf_post|escape:'html':'UTF-8'}{/if}" />
                                                            <span class="input-group-addon">
                                                            <i class="icon-search"></i>
                                                            </span>
                                                            <div class="tagify-container-post">
                                                                {if $ctf_post}
                                                                    <span>
                                                                        {$ctf_post_title|escape:'html':'UTF-8'}
                                                                        <span class="close_tagify">{l s='close' mod='ybc_blog'}</span>
                                                                    </span>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                        <input name="id_post" type="hidden" value="{$ctf_post|intval}" />
                                                    </div>
                                                    <div class="ctf_admin_filter_date">
                                                        <label>{l s='Month' mod='ybc_blog'}</label>
                                                        <select id="months" name="months" class="form-control">
                                                            <option value="" {if !$ctf_month} selected="selected"{/if}>{l s='All' mod='ybc_blog'}</option>
                                                            {foreach from=$months key=k item=month}
                                                                <option value="{$k|intval}"{if $ctf_month == $k} selected="selected"{/if}>{l s=$month mod='ybc_blog'}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    <div class="ctf_admin_filter_date">
                                                        <label>{l s='Year' mod='ybc_blog'}</label>
                                                        <select id="years" name="years" class="form-control">
                                                            <option value="" {if !$ctf_year} selected="selected"{/if}>{l s='All' mod='ybc_blog'}</option>
                                                            {foreach from=$years item=year}
                                                                <option value="{$year|intval}" {if $ctf_year == $year} selected="selected"{/if}>{$year|intval}</option>
                                                            {/foreach}
                                                        </select>
                                                    </div>
                                                    <div class="ctf_admin_filter_button">
                                                        <button name="submitFilterChart" class="btn btn-default" type="submit">{l s='Filter' mod='ybc_blog'}</button>
                                                        {if $show_reset}
                                                            <a href="{$action|escape:'quotes'}" class="btn btn-default">{l s='Reset' mod='ybc_blog'}</a>
                                                        {/if}
                                                    </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="ctf_admin_log form-group form_group_post view-log">
                                    {if $viewlogs}
                                        <table id="table-log" class="table log">
                                           <thead>
                                                <tr class="nodrag nodrop">
                                                    <th>{l s='IP address' mod='ybc_blog'}</th>
                                                    <th>{l s='Browser' mod='ybc_blog'}</th>
                                                    <th>{l s='Customer' mod='ybc_blog'}</th>
                                                    <th>{l s='Post' mod='ybc_blog'}</th>
                                                    <th>{l s='Date' mod='ybc_blog'}</th>
                                                    <th>{l s='Action' mod='ybc_blog'}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="list-logs">
                                                {foreach from=$viewlogs item='log'}
                                                    <tr>
                                                        <td>{$log.ip|escape:'html':'UTF-8'}</td>
                                                        <td> <span class="browser-icon {$log.class|escape:'html':'UTF-8'}"></span> {$log.browser|escape:'html':'UTF-8'}</td>
                                                        <td>{if $log.id_customer}<a href="{$link->getAdminLink('AdminCustomers')|escape:'html':'UTF-8'}" title="{if $log.id_customer}{$log.firstname|escape:'html':'UTF-8'}&nbsp;{$log.lastname|escape:'html':'UTF-8'}{/if}">{$log.firstname|escape:'html':'UTF-8'}&nbsp;{$log.lastname|escape:'html':'UTF-8'}</a>{else}--{/if}</td>
                                                        <td>
                                                            {$log.title nofilter}
                                                        </td>
                                                        <td>{$log.datetime_added|escape:'html':'UTF-8'}</td>
                                                        <td>
                                                            <a class="btn btn-default view_location" title="{l s='View location' mod='ybc_blog'}" href="https://www.infobyip.com/ip-{$log.ip|escape:'html':'UTF-8'}.html" target="_blank">{l s='View location' mod='ybc_blog'}</a>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            </tbody>
                                        </table>
                                        <div class="ybc_paggination" style="margin-top: 10px;">
                                        {$pagination_text_view nofilter}
                                        </div>
                                        <form action="{$action|escape:'quotes'}" enctype="multipart/form-data" method="POST">
                                            <input type="hidden" value="1" name="clearviewLogSubmit"/>
                                            <button class="clear-log btn btn-default" type="submit" name="clearviewLogSubmit" onclick="return confirm('{l s='Do you want to clear log?' mod='ybc_blog'}');">{l s='Clear' mod='ybc_blog'}</button>
                                        </form>
                                        <div class="clearfix"></div>
                                    {else}
                                        {l s='No views log is available' mod='ybc_blog'}
                                    {/if}
                                </div>
                                {if $YBC_BLOG_ALLOW_LIKE}
                                    <div class="ctf_admin_log form-group form_group_post like-log">
                                        {if $likelogs}
                                            <table id="table-log" class="table log">
                                               <thead>
                                                    <tr class="nodrag nodrop">
                                                        <th>{l s='IP address' mod='ybc_blog'}</th>
                                                        <th>{l s='Browser' mod='ybc_blog'}</th>
                                                        <th>{l s='Customer' mod='ybc_blog'}</th>
                                                        <th>{l s='Post' mod='ybc_blog'}</th>
                                                        <th>{l s='Date' mod='ybc_blog'}</th>
                                                        <th>{l s='Action' mod='ybc_blog'}</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody id="list-logs">
                                                    {foreach from=$likelogs item='log'}
                                                        <tr>
                                                            <td>{$log.ip|escape:'html':'UTF-8'}</td>
                                                            <td> <span class="browser-icon {$log.class|escape:'html':'UTF-8'}"></span> {$log.browser|escape:'html':'UTF-8'}</td>
                                                            <td>{if $log.id_customer}<a href="{$link->getAdminLink('AdminCustomers')|escape:'html':'UTF-8'}" title="{if $log.id_customer}{$log.firstname|escape:'html':'UTF-8'}&nbsp;{$log.lastname|escape:'html':'UTF-8'}{/if}">{$log.firstname|escape:'html':'UTF-8'}&nbsp;{$log.lastname|escape:'html':'UTF-8'}</a>{else}--{/if}</td>
                                                            <td>
                                                                {$log.title nofilter}
                                                            </td>
                                                            <td>{$log.datetime_added|escape:'html':'UTF-8'}</td>
                                                            <td>
                                                                <a class="btn btn-default view_location" title="{l s='View location' mod='ybc_blog'}" href="https://www.infobyip.com/ip-{$log.ip|escape:'html':'UTF-8'}.html" target="_blank">{l s='View location' mod='ybc_blog'}</a>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                            <div class="ybc_paggination" style="margin-top: 10px;">
                                            {$pagination_text_like nofilter}
                                            </div>
                                            <form action="{$action|escape:'quotes'}" enctype="multipart/form-data" method="POST">
                                                <input type="hidden" value="1" name="clearlikeLogSubmit"/>
                                                <button class="clear-log btn btn-default" type="submit" name="clearlikeLogSubmit" onclick="return confirm('{l s='Do you want to clear log?' mod='ybc_blog'}');">{l s='Clear' mod='ybc_blog'}</button>
                                            </form>
                                            <div class="clearfix"></div>
                                        {else}
                                            {l s='No likes log is available' mod='ybc_blog'}
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ybc_blog_x_days = '{l s='Day' mod='ybc_blog'}';
    var ybc_blog_x_months = '{l s='Month' mod='ybc_blog'}';
    var ybc_blog_x_years = '{l s='Year' mod='ybc_blog'}';
    var ybc_blog_y_label = '{l s='Count' mod='ybc_blog'}';
    var ybc_blog_line_chart = {$lineChart|json_encode}
</script>