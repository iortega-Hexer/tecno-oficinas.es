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
<div id="js-product-list-header">
    {assign var="textoSeo" value="{hook h="categoryField" name="Texto SEO"}"}
    {if $listing.pagination.items_shown_from == 1 && ($category.description || $category.image.large.url || $textoSeo!="")}
        <div class="block-category card card-block">
            {if $category.description}
                <div id="category-description" class="text-muted">{$category.description nofilter}</div>
            {/if}
            {if $textoSeo!=""}
              <div id="category-description-seo">{hook h="categoryField" name="Texto SEO"}</div>
            {/if}
        </div>
    {/if}
</div>
