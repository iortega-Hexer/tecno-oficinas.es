{*
* 2012-2018 PrestaSOO
*
* NOTICE OF LICENSE
*
* This is a commercial license
* Do not allow to re-sales, edit without permission from PrestaSOO.
* International Registered Trademark & Property of PrestaSOO
*
* @author    Frank Nguyen <addons@prestaSOO.com>
* @copyright prestaSOO.com
* @license   Commercial License. All right reserved
*}
<div class="soo-overlay">
	<a id="myclosebtn" href="{$soomm_ajax_url|escape:'quotes':'UTF-8'}" class="material-icons sooform_flybutton">keyboard_arrow_left</a>
	<a class="sooform_powerlink sooform_flybutton sooform_version" title="{if $thisversion < $livev}{l s='Download new version' mod='soodiscount'}{/if}" href="https://www.prestasoo.com/prestashop-modules/soo-prestashop-discount.html">
			{if $thisversion >= $livev}
    			v{$thisversion}
			{else}
				<i class="material-icons">cloud_download</i> New v{$livev} is here
			{/if}
	</a>
	<a class="sooform_powerlink sooform_flybutton sooform_support" title="{l s='Support' mod='soodiscount'}" href="https://www.prestasoo.com/Support/categories/1-prestashop-discuss.html"><i class="material-icons">bug_report</i></a>
	<a class="sooform_powerlink sooform_flybutton sooform_doc" title="{l s='Documentation' mod='soodiscount'}" href="{$thispath}readme_en.pdf"><i class="material-icons">class</i></a>
	<div class="soo-overlay-content soo-overlay-contentkaka soo-overlay-contentkeke {if $isconfigpage === true}isconfigpage{/if} mdl-grid mdl-grid--no-spacing">
	    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header sooslide_father">
	      <div class="custom-header mdl-layout__header mdl-layout__header--waterfall sooform_header">
		      <div class="sooform_logo_effect1"></div>
		      <div class="sooform_logo_effect2"></div>
		      <div class="sooform_logo">
			  	<img class="soo-magic-logo" src="{$thispath}logo.png" width="100%" />
		      </div>
	      </div>
	      <div class="sooslide">
		      <div style="width: 75%; margin:0 auto">
			      <div class="sooslide_apps">
					<a href="https://www.prestasoo.com/prestashop-modules/sweet-sitemap-prestashop.html?utm_source=prestashopbackend&utm_medium=iconlink" title="SOO Sweet Sitemap | SEO"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/sweet.png" /></a>
					<a href="https://www.prestasoo.com/prestashop-modules/cancel-an-order.html?utm_source=prestashopbackend&utm_medium=iconlink" title="Cancel an Order | Sales"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/cancel.png" /></a>
					<a href="https://www.prestasoo.com/prestashop-modules/subscriptions-prestashop-module.html?utm_source=prestashopbackend&utm_medium=iconlink" title="SOO Subscriptions | Payment"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/subsc.png" /></a>
					<a href="https://www.prestasoo.com/prestashop-modules/soo-magic-menu.html?utm_source=prestashopbackend&utm_medium=iconlink" title="SOO Magic Menu | Navigation"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/magic.png" /></a>
					<a href="https://www.prestasoo.com/prestashop-modules/cart-expiration-prestashop-module.html?utm_source=prestashopbackend&utm_medium=iconlink" title="SOO Cart Expiration | Sales"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/cart.png" /></a>
					<a href="https://www.prestasoo.com/prestashop-modules/soo-prestashop-gift-certificate-module.html?utm_source=prestashopbackend&utm_medium=iconlink" title="SOO Gift Certificate | Gift Cards"><img class="sooslide_apps_soo-magic-logo" src="{$thispath}views/img/gift.png" /></a>
			      </div>
			      <div class="sooslide_apps_kontent">
				      <div class="sooimtyping headline">
						  {$kontent}
					  </div>
			      </div>
		      </div>
	      </div>