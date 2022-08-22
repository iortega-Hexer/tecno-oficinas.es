{*
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
*}

<div class="row" id="start_products">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-file"></i>
                {l s='Attachments' mod='configurator'}
            </div>
            <div class="table-responsive">
                <table class="table" id="orderProducts">
                    <thead>
                        <tr>
                            <th><span class="title_box ">{l s='Product' mod='configurator'}</span></th>
                            <th><span class="title_box ">{l s='Attachments' mod='configurator'}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $attachements_by_cartdetails as $attachements_by_cartdetail}
                            <tr>
                                <td>{$products[$attachements_by_cartdetail.cart_detail->id_order_detail].product_name}</td>
                                <td>
                                    {foreach $attachements_by_cartdetail.attachments as $key => $attachment}
                                        {if $key > 0}<br>{/if}
                                        <a href="{$attachment_link}{$attachment.token}">{$attachment.file_name}</a>
                                    {/foreach}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>