{**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE homecategoriez -->
<div id="homecategoriez">
    {* <div>{l s='Popular categories' mod='homecategoriez'}</div> *}
    <div class="row" id="home_categories">
        {foreach from=$categories item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
        {foreach from=$categoria_padre item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
        {foreach from=$categoria_padre2 item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
        {foreach from=$categoria_padre3 item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
        {foreach from=$categoria_padre4 item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
        {foreach from=$categoria_padre5 item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            {assign var='catDesc' value=$category->description}
            {if $category->id_category|in_array:$to_show}
                <div class="col-xs-12 col-sm-6 col-lg-3 category-box">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {if $category->id_image|intval > 0}
                            <img
                                src="{$imageLink}"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                alt="{$category->name|escape:html:'UTF-8'}"
                                title="{$category->name|escape:html:'UTF-8'}"
                            >
                        {else}
                            <img
                                src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                                width="{$pic_size.width}"
                                height="{$pic_size.height}"
                                title="{l s='No image' mod='homecategoriez'}"
                            >
                        {/if}
                    </a>
                    <div class="category-title">
                        {$category->name|escape:html:'UTF-8'}
                    </div>
                    <div class="category-description">
                        {$catDesc|strip_tags:false|truncate:120:'...'}
                    </div>
                    <a class="btn-secondary" href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                      {l s='See products' d='Shop.Theme.Catalog'}
                    </a>
                </div>
            {/if}
        {/foreach}
    </div>
</div>
<!-- /MODULE homecategoriez -->
