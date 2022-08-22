{*
* @module       Recherche dynamique avancée (AmbJoliSearch)
* @file         js15.tpl
* @subject      template to include javascript initialisation for prestashop 1.5
* @copyright    Copyright (c) 2013-2019 Ambris Informatique SARL (http://www.ambris.com/)
* @author       Richard Stefan (@RicoStefan)
* @license      Commercial license
* Support by mail: support@ambris.com

*}
{if isset($ambjolisearch_jsdefs) && $ambjolisearch_jsdefs|@count}
  <script type="text/javascript">
    {foreach from=$ambjolisearch_jsdefs key=var_name item=var_value}
    var {$var_name} = {$var_value|json_encode nofilter};
    {/foreach}
  </script>
{/if}
