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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<script type="text/javascript">
var text_update_position='{l s='Successful update' mod='ybc_blog'}';
</script>
<div class="panel ybc-blog-panel{if isset($class)} {$class|escape:'html':'UTF-8'}{/if}">
    <div class="panel-heading">{$title nofilter}
        {if isset($totalRecords) && $totalRecords>0}<span class="badge">{$totalRecords|intval}</span>{/if}
        <span class="panel-heading-action">
            {if !isset($show_add_new) || isset($show_add_new) && $show_add_new}            
                <a class="list-toolbar-btn" href="{$currentIndex|escape:'html':'UTF-8'}">
                    <span data-placement="top" data-html="true" data-original-title="{l s='Add new' mod='ybc_blog'}" class="label-tooltip" data-toggle="tooltip" title="">
        				<i class="process-icon-new"></i>
                    </span>
                </a>            
            {/if}
            {if isset($preview_link) && $preview_link}            
                <a target="_blank" class="list-toolbar-btn" href="{$preview_link|escape:'html':'UTF-8'}">
                    <span data-placement="top" data-html="true" data-original-title="{l s='Preview ' mod='ybc_blog'} ({$title|escape:'html':'UTF-8'})" class="label-tooltip" data-toggle="tooltip" title="">
        				<i style="margin-left: 5px;" class="icon-search-plus"></i>
                    </span>
                </a>            
            {/if}
        </span>
    </div>
    {if $fields_list}
        <div class="table-responsive clearfix">
            <form method="post" action="{$currentIndex|escape:'html':'UTF-8'}&list=true">
                <table class="table configuration">
                    <thead>
                        <tr class="nodrag nodrop">
                            {if $name=='ybc_comment' && count($field_values)}
                                <script type="text/javascript">
                                    var detele_confirm ="{l s='Do you want to delete this item?' mod='ybc_blog'}";
                                </script>
                                <th class="fixed-width-xs">
                                    <span class="title_box">
                                        <input value="" class="message_readed_all" type="checkbox" />
                                    </span>
                                </th>
                            {/if}
                            {foreach from=$fields_list item='field' key='index'}
                                <th class="{$index|escape:'html':'UTF-8'}">
                                    <span class="title_box">
                                        {$field.title|escape:'html':'UTF-8'}
                                        {if isset($field.sort) && $field.sort}
                                            <a href="{$currentIndex|escape:'html':'UTF-8'}&sort={$index|escape:'html':'UTF-8'}&sort_type=desc&list=true{$filter_params nofilter}" {if isset($sort)&& $sort==$index && isset($sort_type) && $sort_type=='desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
                                            <a href="{$currentIndex|escape:'html':'UTF-8'}&sort={$index|escape:'html':'UTF-8'}&sort_type=asc&list=true{$filter_params nofilter}" {if isset($sort)&& $sort==$index && isset($sort_type) && $sort_type=='asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
                                        {/if}
                                    </span>
                                </th>                            
                            {/foreach}
                            {if $show_action}
                                <th style="text-align: center;">{l s='Action' mod='ybc_blog'}</th>
                            {/if}
                        </tr>
                        {if $show_toolbar}
                            <tr class="nodrag nodrop filter row_hover">
                                {if $name=='ybc_comment' && count($field_values)}
                                    <th>&nbsp;</th>
                                {/if}
                                {foreach from=$fields_list item='field' key='index'}
                                    <th class="{$index|escape:'html':'UTF-8'}">
                                        {if isset($field.filter) && $field.filter}
                                            {if $field.type=='text'}
                                                <input class="filter" name="{$index|escape:'html':'UTF-8'}" type="text" {if isset($field.width)}style="width: {$field.width|intval}px;"{/if} {if isset($field.active)}value="{$field.active|escape:'html':'UTF-8'}"{/if}/>
                                            {/if}
                                            {if $field.type=='select' || $field.type=='active'}
                                                <select  {if isset($field.width)}style="width: {$field.width|intval}px;"{/if}  name="{$index|escape:'html':'UTF-8'}">
                                                    {if $index!='has_post'}
                                                        <option value=""> -- </option>
                                                    {/if}
                                                    {if isset($field.filter_list.list) && $field.filter_list.list}
                                                        {assign var='id_option' value=$field.filter_list.id_option}
                                                        {assign var='value' value=$field.filter_list.value}
                                                        {foreach from=$field.filter_list.list item='option'}
                                                            <option {if ($field.active!=='' && $field.active==$option.$id_option) || ($field.active=='' && $index=='has_post' && $option.$id_option==1 )} selected="selected"{/if} value="{$option.$id_option|escape:'html':'UTF-8'}">{$option.$value|escape:'html':'UTF-8'}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>                                            
                                            {/if}
                                            {if $field.type=='int'}
                                                <label for="{$index|escape:'html':'UTF-8'}_min"><input type="text" placeholder="{l s='Min' mod='ybc_blog'}" name="{$index|escape:'html':'UTF-8'}_min" value="{$field.active.min|escape:'html':'UTF-8'}" /></label>
                                                <label for="{$index|escape:'html':'UTF-8'}_max"><input type="text" placeholder="{l s='Max' mod='ybc_blog'}" name="{$index|escape:'html':'UTF-8'}_max" value="{$field.active.max|escape:'html':'UTF-8'}" /></label>
                                            {/if}
                                        {else}
                                           {l s=' -- ' mod='ybc_blog'}
                                        {/if}
                                    </th>
                                {/foreach}
                                {if $show_action}
                                    <th class="actions">
                                        <span class="pull-right">
                                            <input type="hidden" name="post_filter" value="yes" />
                                            {if $show_reset}<a  class="btn btn-warning"  href="{$currentIndex|escape:'html':'UTF-8'}&list=true"><i class="icon-eraser"></i> {l s='Reset' mod='ybc_blog'}</a> &nbsp;{/if}
                                            <button class="btn btn-default" name="ybc_submit_{$name|escape:'html':'UTF-8'}" id="ybc_submit_{$name|escape:'html':'UTF-8'}" type="submit">
            									<i class="icon-search"></i> {l s='Filter' mod='ybc_blog'}
            								</button>
                                        </span>
                                    </th>
                                {/if}
                            </tr>
                        {/if}
                    </thead>
                    {if $field_values}
                    <tbody id="list-{$name|escape:'html':'UTF-8'}">
                        {foreach from=$field_values item='row'}
                            <tr {if $name=='ybc_post'}id="posts-{$row.id_post|intval}"{/if} {if $name=='ybc_category'}id="cateogires-{$row.id_category|intval}"{/if} {if $name=='ybc_slide'}id="slides-{$row.id_slide|intval}"{/if} {if $name=='ybc_gallery'}id="galleries-{$row.id_gallery|intval}"{/if} {if isset($row.viewed) && !$row.viewed}class="no-viewed"{/if}>
                                {if $name=='ybc_comment'}
                                    <td class="message-more-action">
                                        <input type="checkbox" name="message_readed[{$row.id_comment|intval}]" class="message_readed" value="1" data-approved="{if $row.approved}1{else}0{/if}" data-viewed="{if $row.viewed}1{else}0{/if}"/>
                                    </td> 
                                {/if} 
                                {foreach from=$fields_list item='field' key='key'}                             
                                    <td class="{$key|escape:'html':'UTF-8'} {if isset($sort)&& $sort==$key && isset($sort_type) && $sort_type=='asc' && isset($field.update_position) && $field.update_position}pointer dragHandle center{/if}" >
                                        {if isset($field.rating_field) && $field.rating_field}
                                            {if isset($row.$key) && $row.$key > 0}
                                                {for $i=1 to (int)$row.$key}
                                                    <div class="star star_on"></div>
                                                {/for}
                                                {if (int)$row.$key < 5}
                                                    {for $i=(int)$row.$key+1 to 5}
                                                        <div class="star"></div>
                                                    {/for}
                                                {/if}
                                            {else}
                                                {l s=' -- ' mod='ybc_blog'}
                                            {/if}
                                        {elseif $field.type != 'active'}
                                            {if isset($field.update_position) && $field.update_position}
                                                <div class="dragGroup">
                                                <span class="positions">
                                            {/if}
                                            {if isset($row.$key) && !is_array($row.$key)}{if isset($field.strip_tag) && !$field.strip_tag}{$row.$key nofilter}{else}{$row.$key|strip_tags:'UTF-8'|truncate:120:'...'|escape:'html':'UTF-8'}{/if}{/if}
                                            {if isset($row.$key) && is_array($row.$key) && isset($row.$key.image_field) && $row.$key.image_field}
                                                <a class="ybc_fancy" href="{$row.$key.img_url|escape:'html':'UTF-8'}"><img style="{if isset($row.$key.height) && $row.$key.height}max-height: {$row.$key.height|intval}px;{/if}{if isset($row.$key.width) && $row.$key.width}max-width: {$row.$key.width|intval}px;{/if}" src="{$row.$key.img_url|escape:'html':'UTF-8'}" /></a>
                                            {/if} 
                                            {if isset($field.update_position) && $field.update_position}
                                                </div>
                                                </div>
                                            {/if}                                       
                                        {else}
                                            {if $name=='ybc_post'}                                            
                                                {if isset($row.$key) && $row.$key}
                                                    {if $row.$key==-1}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as published' mod='ybc_blog'}"><i class="icon-clock-o"></i></a>
                                                        {if isset($row.status_author) && $row.status_author==-1 && $key=='enabled'}<span style="color:black;">{l s='Hidden' mod='ybc_blog'}</span>{/if}
                                                    {elseif $row.$key==2 && isset($row.datetime_active)}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as published' mod='ybc_blog'}"><i class="icon-clock-o"></i></a>
                                                        <span style="color:black;">({$row.datetime_active|escape:'html':'UTF-8'})</span>{if isset($row.status_author) && $row.status_author==-1 && $key=='enabled'}<span style="color:black;">{l s='Hidden' mod='ybc_blog'}</span>{/if}
                                                    {elseif $row.$key==-2}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-draft  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as published' mod='ybc_blog'}"><i class="fa fa-eye"></i></a>
                                                    {else}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='is_featured'}{l s='Click to unmark featured' mod='ybc_blog'}{else}{l s='Click to mark as disabled' mod='ybc_blog'}{/if}"><i class="icon-check"></i></a>
                                                        {if isset($row.status_author) && $row.status_author==-1 && $key=='enabled'}<span style="color:black;">{l s='Hidden' mod='ybc_blog'}</span>{/if}
                                                    {/if}
                                                {else}
                                                    <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='is_featured'}{l s='Click to mark as featured' mod='ybc_blog'}{else}{l s='Click to publish' mod='ybc_blog'}{/if}"><i class="icon-remove"></i></a>
                                                    {if isset($row.status_author) && $row.status_author==-1 && $key=='enabled'}<span style="color:black;">{l s='Hidden' mod='ybc_blog'}</span>{/if}
                                                {/if}
                                            {elseif $name=='ybc_blog_employee' || $name=='ybc_blog_customer'}
                                                {if $key!='has_post'}
                                                    {if !isset($row.id_profile) || (isset($row.id_profile) && $row.id_profile!=1)}
                                                        {if isset($row.$key) && $row.$key}
                                                            {if $row.$key==1}
                                                                <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to suspend' mod='ybc_blog'}"><i class="icon-check"></i></a>
                                                            {else}
                                                                <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to active' mod='ybc_blog'}"><i class="icon-remove"></i>{l s='Hide posts' mod='ybc_blog'}</a>
                                                            {/if}
                                                        {else}
                                                            <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to active' mod='ybc_blog'}"><i class="icon-remove"></i></a>
                                                        {/if}
                                                    {else}
                                                        <span  class="list-action list-action-enable action-enabled"><i class="icon-check"></i></span>
                                                    {/if}
                                                {else}
                                                    {if isset($row.$key) && $row.$key}
                                                        {l s='Yes' mod='ybc_blog'}
                                                    {else}
                                                        {l s='No' mod='ybc_blog'}
                                                    {/if}
                                                {/if}
                                            {elseif $name=='ybc_comment'}
                                                {if $key=='approved'}
                                                    {if isset($row.$key) && $row.$key}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as unapproved' mod='ybc_blog'}"><i class="icon-check"></i></a>
                                                    {else}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as approved' mod='ybc_blog'}"><i class="icon-remove"></i></a>
                                                    {/if}
                                                {else}
                                                    {if isset($row.$key) && $row.$key}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to unreport' mod='ybc_blog'}"><i class="icon-check"></i></a>
                                                    {else}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as reported' mod='ybc_blog'}"><i class="icon-remove"></i></a>
                                                    {/if}
                                                {/if}
                                            {elseif $name=='ybc_gallery'}
                                                {if $key=='is_featured'}
                                                    {if isset($row.$key) && $row.$key}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to unmark featured' mod='ybc_blog'}"><i class="icon-check"></i></a>
                                                    {else}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to mark as featured' mod='ybc_blog'}"><i class="icon-remove"></i></a>
                                                    {/if}
                                                {else}
                                                    {if isset($row.$key) && $row.$key}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to disable' mod='ybc_blog'}"><i class="icon-check"></i></a>
                                                    {else}
                                                        <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to enable' mod='ybc_blog'}"><i class="icon-remove"></i></a>
                                                    {/if}
                                                {/if}
                                            {elseif $name=='ybc_polls'}
                                                {if isset($row.$key) && $row.$key}
                                                    <a name="{$name|escape:'html':'UTF-8'}"  href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='reported'}{l s='Click to unreport' mod='ybc_blog'}{else}{l s='Click to mark this as unhelpful' mod='ybc_blog'}{/if}"><i class="icon-check"></i></a>
                                                {else}
                                                    <a name="{$name|escape:'html':'UTF-8'}" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='reported'}{l s='Click to mark as reported' mod='ybc_blog'}{else}{l s='Click to mark this as helpful' mod='ybc_blog'}{/if}"><i class="icon-remove"></i></a>
                                                {/if}
                                            {else}
                                                {if isset($row.$key) && $row.$key}
                                                    <a name="{$name|escape:'html':'UTF-8'}"  href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='reported'}{l s='Click to unreport' mod='ybc_blog'}{else}{l s='Click to Disabled' mod='ybc_blog'}{/if}"><i class="icon-check"></i></a>
                                                {else}
                                                    <a name="{$name|escape:'html':'UTF-8'}" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{if $key=='reported'}{l s='Click to mark as reported' mod='ybc_blog'}{else}{l s='Click to Enabled' mod='ybc_blog'}{/if}"><i class="icon-remove"></i></a>
                                                {/if}
                                            {/if}
                                        {/if}
                                    </td>
                                {/foreach}
                                {if $show_action}
                                    <td class="text-right">                                
                                            <div class="btn-group-action">
                                                <div class="btn-group pull-right">
                                                    {if $name!='ybc_polls'}
                                                        {if isset($row.child_view_url) && $row.child_view_url}
                                                            <a class="btn btn-default" href="{$row.child_view_url|escape:'html':'UTF-8'}">{if $name=="ybc_category"}<i class="icon-search-plus"></i> {l s='Sub categories' mod='ybc_blog'}{else}<i class="icon-search-plus"></i> {l s='View' mod='ybc_blog'}{/if}</a>
                                                        {else}
                                                            <a class="edit btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}"><i class="icon-pencil"></i> {l s='Edit' mod='ybc_blog'}</a>
                                                        {/if}
                                                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
                                    						<i class="icon-caret-down"></i>&nbsp;
                                    					</button>
                                                    {if in_array('delete',$actions) || (isset($row.view_url) && $row.view_url) || (isset($row.view_post_url) && $row.view_post_url)||(isset($row.delete_post_url) && $row.delete_post_url)}
                                                        <ul class="dropdown-menu">
                                                            {if isset($row.child_view_url) && $row.child_view_url}
                                                                <li><a class="edit" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}"><i class="icon-pencil"></i> {l s='Edit' mod='ybc_blog'}</a></li>
                                                                <li class="divider"></li>
                                                            {/if}
                                                            {if isset($row.view_url) && $row.view_url}
                                                                <li><a target="_blank" href="{$row.view_url|escape:'html':'UTF-8'}"><i class="icon icon-external-link" aria-hidden="true"></i> {if isset($row.view_text) && $row.view_text} {$row.view_text|escape:'html':'UTF-8'}{else} {l s='View' mod='ybc_blog'}{/if}</a></li>
                                                                <li class="divider"></li>
                                                            {/if}
                                                            {if isset($row.view_post_url) && $row.view_post_url}
                                                                <li><a target="_blank" href="{$row.view_post_url|escape:'html':'UTF-8'}"><i class="icon-search-plus"></i>{l s='View posts' mod='ybc_blog'}</a></li>
                                                                <li class="divider"></li>
                                                            {/if}
                                                            {if isset($row.delete_post_url) && $row.delete_post_url}
                                                                <li><a onclick="return confirm('{l s='Do you want to delete posts?' mod='ybc_blog'}');" href="{$row.delete_post_url|escape:'html':'UTF-8'}"><i class="icon-trash"></i>{l s='Delete all posts' mod='ybc_blog'}</a></li>
                                                                <li class="divider"></li>
                                                            {/if}
                                                            {if in_array('delete',$actions)}
                                                                <li><a onclick="return confirm('{l s='Do you want to delete this item?' mod='ybc_blog'}');" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&del=yes"><i class="icon-trash"></i> {l s='Delete' mod='ybc_blog'}</a></li>
                                                            {/if}
                                                        </ul>
                                                    {/if}
                                                    {else}
                                                        <a class="edit btn btn-default" onclick="return confirm('{l s='Do you want to delete this item?' mod='ybc_blog'}');" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&del=yes"><i class="icon-trash"></i> {l s='Delete' mod='ybc_blog'}</a>
                                                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
                                    						<i class="icon-caret-down"></i>&nbsp;
                                    					</button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="send_mail_form" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&sendmailform=yes"><i class="icon-email"></i> {l s='Send email' mod='ybc_blog'}</a></li>
                                                            <li class="divider"></li>
                                                            {if isset($row.id_user) && $row.id_user}
                                                                <li><a href="{$link_customer|escape:'html':'UTF-8'}&id_customer={$row.id_user|intval}"><i class="icon-user"></i> {l s='View customer' mod='ybc_blog'}</a></li>
                                                                <li class="divider"></li>
                                                            {/if} 
                                                        </ul>
                                                    {/if}
                                                </div>
                                            </div>
                                     </td>
                                {/if}
                            </tr>
                        {/foreach}                    
                    </tbody>
                    {/if}
                </table>
                {if !$field_values}
                    {l s='No items found' mod='ybc_blog'}
                {/if}
                {if $name=='ybc_comment'}
                    <select id="bulk_action_message" name="bulk_action_message" style="display:none;width: 200px;">
                        <option value="">{l s='Bulk actions' mod='ybc_blog'}</option>
                        <option value="mark_as_approved">{l s='Approved' mod='ybc_blog'}</option>
                        <option value="mark_as_unapproved">{l s='Unapproved' mod='ybc_blog'}</option>
                        <option value="mark_as_read">{l s='Mark as read' mod='ybc_blog'}</option>
                        <option value="mark_as_unread">{l s='Mark as  unread' mod='ybc_blog'}</option>
                        <option value="delete_selected">{l s='Delete selected' mod='ybc_blog'}</option>
                    </select>
                {/if}
                {if $paggination}
                    <div class="ybc_paggination" style="margin-top: 10px;">
                        {$paggination nofilter}
                    </div>
                {/if}
            </form>
        </div>
    {/if}
</div>
</span>
{if $name=='ybc_polls'}
    <div class="popup-form-send-email-polls">
        <div class="popup-form-send-email-polls-content">
        </div>
    </div>
{/if}