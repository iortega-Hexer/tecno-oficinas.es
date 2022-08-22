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
<!-- MODULE ybc_blog -->
{if $author && !$suppened}
<li class="lnk_ybc_blog">
	<a href="{$link->getModuleLink('ybc_blog','managementblog',array(),true)|escape:'html':'UTF-8'}" title="{l s='Blog management' mod='ybc_blog'}">
		<i class="icon-user">&nbsp;</i>
        <span>{l s='My blog posts' mod='ybc_blog'}</span>
	</a>
</li>
{/if}
<li class="lnk_ybc_blog">
	<a href="{$link->getModuleLink('ybc_blog','managementcomments',array(),true)|escape:'html':'UTF-8'}" title="{l s='My blog comments' mod='ybc_blog'}">
		<i class="icon-comments">&nbsp;</i>
        <span>{l s='My blog comments' mod='ybc_blog'}</span>
	</a>
</li>
<li class="lnk_ybc_blog">
	<a href="{$link->getModuleLink('ybc_blog','managementmyinfo',array(),true)|escape:'html':'UTF-8'}" title="{l s='My blog info' mod='ybc_blog'}">
		<i class="fa fa-file-text-o">&nbsp;</i>
        <span>{l s='My blog info' mod='ybc_blog'}</span>
	</a>
</li>
<!-- END : MODULE ybc_blog -->