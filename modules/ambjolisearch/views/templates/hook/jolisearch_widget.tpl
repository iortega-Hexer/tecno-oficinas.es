{*
* @module       Recherche dynamique avancée (AmbJoliSearch)
* @file         ambjolisearch.tpl
* @subject      template pour champ recherche en haut de page sur le 'front office'
* @copyright    Copyright (c) 2013-2019 Ambris Informatique SARL (http://www.ambris.com/)
* @author       Richard Stefan (@RicoStefan)
* @license      Commercial license
* Support by mail: support@ambris.com
*}
<div id="search_widget" class="search-widget" data-search-controller-url="{$search_controller_url}">
	<form method="get" action="{$search_controller_url}">
		<input type="hidden" name="controller" value="search">
		<input type="text" name="s" value="{$search_string}" placeholder="{l s='Search our catalog' d='Shop.Theme.Catalog'}" aria-label="{l s='Search' d='Shop.Theme.Catalog'}">
		<button type="submit">
			<i class="material-icons search">&#xE8B6;</i>
      <span class="hidden-xl-down">{l s='Search' d='Shop.Theme.Catalog'}</span>
		</button>
	</form>
</div>