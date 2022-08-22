{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<!doctype html>
<html lang="{$language.iso_code}">

  <head>
    {block name='head'}
      {include file='_partials/head.tpl'}
    {/block}
  </head>

  <body id="{$page.page_name}" class="{$page.body_classes|classnames}">

    {block name='hook_after_body_opening_tag'}
      {hook h='displayAfterBodyOpeningTag'}
    {/block}

    <main>
      {block name='product_activation'}
        {include file='catalog/_partials/product-activation.tpl'}
      {/block}

      <header id="header">
        {block name='header'}
          {include file='_partials/header.tpl'}
        {/block}
      </header>

      {if $page.page_name != 'index'}
        {assign var="id" value=$page.meta.title|lower|replace:"í":"i"|replace:"ú":"u"|replace:" ":""}
        <div id="{$id}" class="banner-interna"></div>
      {/if}

      {block name='notifications'}
        {include file='_partials/notifications.tpl'}
      {/block}

      <section id="wrapper">
        {hook h="displayWrapperTop"}
        <div class="container2">
          {if $page.page_name != 'index'}
            {if $page.page_name == 'product'}
              {assign var="titulo" value=$breadcrumb.links.{$breadcrumb.count-2}.title}
            {else}
              {assign var="titulo" value=$page.meta.title}
            {/if}
            {assign var="ruta" value='/img/banner/'}
            {assign var="imageurl" value=$ruta|cat:{$titulo|lower|replace:'á':'a'|replace:'é':'e'|replace:'í':'i'|replace:'ó':'o'|replace:'ú':'u'|replace:' ':'-'}|cat:'.jpg'}
            {assign var="urlabsoluta" value={_PS_CORE_DIR_}|cat:{$imageurl}}
            {if !file_exists($urlabsoluta)}
              {if $page.page_name == 'category'} {* si la categoría no tiene foto *}
                {* Se toma la foto de la categoría padre y si esta no existe se utiliza la categoría por defecto *}
                {assign var="catpadre" value=$breadcrumb.links.{$breadcrumb.count-2}.title}
                {assign var="imageurl" value=$ruta|cat:{$catpadre|lower|replace:'á':'a'|replace:'é':'e'|replace:'í':'i'|replace:'ó':'o'|replace:'ú':'u'|replace:' ':'-'}|cat:'.jpg'}
                {assign var="urlabsoluta" value={_PS_CORE_DIR_}|cat:{$imageurl}}
                {if !file_exists($urlabsoluta)}
                  {assign var="imageurl" value=$ruta|cat:'default-cabecera.jpg'}
                {/if}
              {elseif $page.page_name == 'product'} {* si la categoría del producto no tiene foto *}
                {* Se toma la foto de la categoría padre y si esta no existe se utiliza la categoría por defecto *}
                {assign var="catpadre" value=$breadcrumb.links.{$breadcrumb.count-3}.title}
                {assign var="imageurl" value=$ruta|cat:{$catpadre|lower|replace:'á':'a'|replace:'é':'e'|replace:'í':'i'|replace:'ó':'o'|replace:'ú':'u'|replace:' ':'-'}|cat:'.jpg'}
                {assign var="urlabsoluta" value={_PS_CORE_DIR_}|cat:{$imageurl}}
                {if !file_exists($urlabsoluta)}
                  {assign var="imageurl" value=$ruta|cat:'default-cabecera.jpg'}
                {/if}
              {else} {* Para el resto de páginas se toma la foto que corresponda (segun su page_name) *}
                {assign var="tipopag" value=$page.page_name}
                {assign var="imageurl" value=$ruta|cat:{$tipopag|lower|replace:'á':'a'|replace:'é':'e'|replace:'í':'i'|replace:'ó':'o'|replace:'ú':'u'|replace:' ':'-'}|cat:'.jpg'}
                {assign var="urlabsoluta" value={_PS_CORE_DIR_}|cat:{$imageurl}}
                {if !file_exists($urlabsoluta)}
                  {assign var="imageurl" value=$ruta|cat:'default-cabecera2.jpg'}
                {/if}
              {*else*}
                {*assign var="imageurl" value=$ruta|cat:'default-cabecera.jpg'*}
              {/if}
            {/if}
            <div class="cabecera-interna" style="background-image: url({$imageurl});">
              <div class="container">
                <div class="titulo-interna">
                  {assign var="flagH1" value="0"}
                  {if $page.page_name == 'category'}
                    {assign var="titH1" value="{hook h="categoryField" name="Titulo h1"}"}
                    {if !$titH1!=""}
                      {assign var="flagH1" value="1"}
                    {/if}
                  {/if}
                  {if $page.page_name == 'cms' || $flagH1 }<h1 class="titulo-pagina">{/if}
                    {$titulo}
                  {if $page.page_name == 'cms' || $flagH1 }</h1>{/if}
                </div>
                <div class="bread-crumb">
                  {block name='breadcrumb'}
                    {include file='_partials/breadcrumb.tpl'}
                  {/block}
                </div>
              </div>
            </div>
            {if $page.page_name == 'category'}
              <div class="cat-info">
                <div class="container">
                  {if $subcategories}
                    <div class="arbol-categorias">
                      {hook h="displayTopCategory"}
                    </div>
                  {/if}
                  <div class="descripcion">
                    {assign var="textoSeo" value="{hook h="categoryField" name="Texto SEO"}"}
                    <div class="tit1-seo">
                      <h1>{$titH1|strip|strip_tags:false}</h1>
                    </div>
                    {if $listing.pagination.items_shown_from == 1 && ($category.description || $category.image.large.url || $textoSeo!="")}
                        <div class="block-category card card-block">
                            {if $textoSeo!=""}
                              <div id="category-description-seo">{hook h="categoryField" name="Texto SEO"}</div>
                            {/if}
                        </div>
                    {/if}
                  </div>
                </div>
              </div>
            {/if}
          {/if}
          <div class="container">
            {block name="left_column"}
              <div id="left-column" class="col-xs-12 col-sm-4 col-md-3">
                {if $page.page_name == 'product'}
                  {hook h='displayLeftColumnProduct'}
                {else}
                  {hook h="displayLeftColumn"}
                {/if}
              </div>
            {/block}

            {block name="content_wrapper"}
              <div id="content-wrapper">
                {hook h="displayContentWrapperTop"}
                {block name="content"}
                  <p>Hello world! This is HTML5 Boilerplate.</p>
                {/block}
                {hook h="displayContentWrapperBottom"}
              </div>
            {/block}

            {block name="right_column"}
              <div id="right-column" class="col-xs-12 col-sm-4 col-md-3">
                {if $page.page_name == 'product'}
                  {hook h='displayRightColumnProduct'}
                {else}
                  {hook h="displayRightColumn"}
                {/if}
              </div>
            {/block}
          </div>
          {if $page.page_name == 'category'}
            {include file='catalog/_partials/category-footer.tpl' listing=$listing category=$category}
          {/if}
        </div>
        {hook h="displayWrapperBottom"}
      </section>

      {if $page.page_name == 'product'}
        <div class="inforeassurance">
          <div class="container product-blockreassurance">
            {block name='hook_display_reassurance'}
              {hook h='displayReassurance'}
            {/block}
          </div>
        </div>
      {/if}

      {if $page.page_name == 'index'}
        <section>
          {hook h='displayFullHome'}
        </section>
      {/if}

      <section>
        <div class="container">
          <div class="row">
            {hook h='displayCustomSlick'}
          </div>
        </div>
      </section>

      {if $page.page_name == 'index'}
        <section>
          {hook h='displayHomeSeo'}
        </section>
      {/if}
      </section>

      {if $page.page_name == 'index'}
        <section>
          {hook h='displayFullBlogHome'}
        </section>
      {/if}

      <footer id="footer">
        {block name="footer"}
          {include file="_partials/footer.tpl"}
        {/block}
      </footer>

    </main>

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}

    {block name='hook_before_body_closing_tag'}
      {hook h='displayBeforeBodyClosingTag'}
    {/block}
  </body>

</html>
