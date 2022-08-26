{*
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<form method="post" action="">
    <div class="close_popup" style="display:none;">{l s='Close' mod='ybc_blog'}</div>
    <div class="form_polls">
        <div class="form-group">
            <label for="to_email">{l s='To' mod='ybc_blog'}</label>
            <input name="to_email" id="to_email" value="{$polls_class->name|escape:'html':'UTF-8'} <{$polls_class->email|escape:'html':'UTF-8'}>" readonly="true" />
        </div>
        <div class="form-group">
            <label for="subject_email">{l s='Subject' mod='ybc_blog'}<span class="required">*</span></label>
            <input name="subject_email" id="subject_email" value=""/>
        </div>
        <div class="form-group">
            <label for="message_email">{l s='Message' mod='ybc_blog'}<span class="required">*</span></label>
            <textarea name="message_email" id="message_email"></textarea> 
        </div>
        <input type="hidden" name="send_mail_polls" value="1" />
        <input name="id_polls" value="{$polls_class->id|intval}" type="hidden" id="id_polls"/>
        <button type="submit" name="send_mail_polls" >{l s='Send' mod='ybc_blog'}</button>
    </div>  
    </div>
</form>