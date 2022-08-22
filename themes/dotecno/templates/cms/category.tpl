{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
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
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file='page.tpl'}

{block name='page_title'}
  {$cms_category.name}
{/block}

{block name='page_content'}
  {block name='cms_sub_categories'}
    {if $sub_categories}
      <p>{l s='List of sub categories in %name%:' d='Shop.Theme.Global' sprintf=['%name%' => $cms_category.name]}</p>
      <ul>
        {foreach from=$sub_categories item=sub_category}
          <li><a href="{$sub_category.link}">{$sub_category.name}</a></li>
        {/foreach}
      </ul>
    {/if}
  {/block}

  {block name='cms_sub_pages'}
    {if $cms_pages}
      <ul class="category-sub-menu">
        {foreach from=$cms_pages item=cms_page}
          <li class="cms-pag">
            {assign var=file value="{_PS_ROOT_DIR_}/img/cms/{$cms_page.id_cms}.jpg"}
            <a href="{$cms_page.link}" class="image-cms" title="{$cms_page.meta_title}">
              <div class="fondo-imagen">
                {if file_exists($file)}
                  <img class="imgm" src="{$urls.img_ps_url}cms/{$cms_page.id_cms}.jpg" alt="{$cms_page.meta_title}">
                {else}
                  <img src="{$urls.img_ps_url}cms/es-default-cms_page.jpg" class="imgm"/>
                {/if}
              </div>
              <div class="btn-hover">
                {l s='Ver servicio' d='Shop.Theme.Global'}
              </div>
            </a>
            <a href="{$cms_page.link}" class="cms-nombre">{$cms_page.meta_title}</a>
          </li>
        {/foreach}
      </ul>
    {/if}
  {/block}
{/block}
