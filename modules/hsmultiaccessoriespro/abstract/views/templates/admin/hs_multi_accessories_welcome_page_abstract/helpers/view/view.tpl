{**
* Multi Accessories for PrestaShop
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}
    <form id="welcome_page" class="form-horizontal" enctype="multipart/form-data" method="post" action="{$link_module_homepage|escape:'htmlall':'UTF-8'}">
        <div  class="panel {if (!$is_prestashop_16)}prestashop_15{/if}">
            <div class="form-group ">
                <p class="title_block">
                    {$welcome_to|escape:'quotes':'UTF-8'}<span class="module_name"> {$module_display_name|escape:'htmlall':'UTF-8'} {$module_version|escape:'htmlall':'UTF-8'}!</span>
                </p>
                <p class="description">
                    {$an_awesome_PrestaShop_solution_provided_by_prestamonster|escape:'htmlall':'UTF-8'}
                </p>
            </div>
            <div class="form-group ">
                <p class="title_block">
                    {$change_log|escape:'htmlall':'UTF-8'}
                </p>
                {foreach from=$change_logs item='change_log' key='version' name='list_change_log'}
                    <div class="col-lg-9 {if $smarty.foreach.list_change_log.first}first{else}other{/if}">
                        <span class="under_line">{$version|escape:'htmlall':'UTF-8'}</span>
                        <ul>
                            {foreach from=$change_log item='log'}
                                <li> - {$log|escape:'htmlall':'UTF-8'}</li>
                                {/foreach}
                        </ul>
                        {if $change_logs|@count > 1 && $smarty.foreach.list_change_log.first}
                            <p class='read_more'>{$read_more|escape:'htmlall':'UTF-8'}</p>
                        {/if}
                    </div>
                {/foreach}
            </div>
            <div class="form-group ">
                <p class="title_block">
                    {$share_your_reviews|escape:'htmlall':'UTF-8'}
                </p>
                <p class="sub_title">
                    {$add_your|escape:'htmlall':'UTF-8'} <span class="vote_star"><a href="{$link_to_addon_page|escape:'htmlall':'UTF-8'}" target="_blank">{$star|escape:'htmlall':'UTF-8'}</a></span> {$on|escape:'htmlall':'UTF-8'} <a href="{$link_to_addon_page|escape:'htmlall':'UTF-8'}" target="_blank">{$addons_url|escape:'htmlall':'UTF-8'}</a> {$to_help_us_improve|escape:'htmlall':'UTF-8'}
                </p>
                <p class="sub_title">
                    {$and_as_a_result_you_will_get_more_values_from_us|escape:'htmlall':'UTF-8'}
                </p>
                <p class="description">
                    {$just_log_into|escape:'htmlall':'UTF-8'} <a href="{$link_to_addon_page|escape:'htmlall':'UTF-8'}" target="_blank">{$prestashop_addons|escape:'htmlall':'UTF-8'}</a> {$with_your_credentials|escape:'htmlall':'UTF-8'}
                </p>
                <a href="{$link_to_addon_page|escape:'htmlall':'UTF-8'}" target="_blank">
                    {$link_to_addon_page|escape:'htmlall':'UTF-8'}
                </a>
            </div>
            <div class="form-group ">
                <p class="title_block">
                    {$looking_for_even_better_prestashop_modules|escape:'htmlall':'UTF-8'}
                </p>
                <p class="sub_title">
                    {$take_a_look_at_all_modules_developed_by|escape:'quotes':'UTF-8'} <a href="{$link_to_prestamonster|escape:'htmlall':'UTF-8'}" target="_blank">{$prestamonster|escape:'quotes':'UTF-8'}</a>.
                </p>
            </div>	
            <div class="form-group ">
                <div class="col-lg-9">
                    <button class="goto_hompage">{$thank_you_and_take_me_to|escape:'htmlall':'UTF-8'}</button>
                </div>
            </div>
        </div>
    </form>
    {*End of content*}
    <script>
        $(document).ready(function () {
            $('#welcome_page .other').hide();
            $(document).on('click', '#welcome_page .read_more', function () {
                $(this).hide();
                $('#welcome_page .other').show();
            });
        });
    </script>
{/block}

