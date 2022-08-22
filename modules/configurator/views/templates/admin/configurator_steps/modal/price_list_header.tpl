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
<div class="modal-body">
        <table class="table"> 
                <thead> 
                        <tr> 
                                <th>{l s='Name' mod='configurator'}</th>
                                <th>{l s='Size' mod='configurator'}</th>
                                <th>{l s='Operations' mod='configurator'}</th>                                    
                        </tr> 
                </thead> 
                <tbody> 
                        <tr class="header-row" data-id="0">
                                <td><input class="form-control header-name" required type="text" placeholder="{l s='Header\'s name' mod='configurator'}"></td>
                                <td><input class="form-control header-size" required type="number" min="1" placeholder="{l s='Size' mod='configurator'}"></td>
                                <td>
                                        <button type="button" class="btn btn-danger header-delete-action">{l s='Delete' mod='configurator'}</button>
                                </td>
                        </tr>              
                </tbody>
        </table>
</div>

<form method="post" action="" class="form-horizontal" id="formula_form">
        <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default header-cancel" data-dismiss="modal" type="button">Annuler</button>
                <button class="btn btn-primary header-add" type="submit">{l s='Add header' mod='configurator'}</button>
                <button class="btn btn-success header-save" type="submit">{l s='Save' mod='configurator'}</button>
        </div>
</form>

<script type="text/javascript">
        $(document).on('ready', function(){
            
                if (typeof languages === 'undefined') {
                    // means we are not in the appropriate page for price_list_header
                    return;
                }
            
                var dataSRC = '#header-group .translatable-field[style*="display: block"] input';
                if (languages.length === 1) {
                       dataSRC = '#header_names_1';
                }
                
                if ($(dataSRC).length === 0) {
                    // means there is no input link to header
                    // security measures
                    return;
                }
             

                var config = {
                        dataSRC: dataSRC,
                        selectorShow: '#btn-show-price-list-header',
                        selectorPanel: '#modal_configurator_header',
                        selectorAddHeader: '#modal_configurator_header .modal-footer .header-add',
                        selectorSaveHeader:'#modal_configurator_header .modal-footer .header-save',
                        row: '.header-row',
                        messageDeleteRow: '{l s='Are you sure ?' mod='configurator'}'
                };

                CONFIGURATOR.PriceListHeader.init(config);
        });
</script>