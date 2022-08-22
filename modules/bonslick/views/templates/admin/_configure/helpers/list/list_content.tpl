{*
 * 2015-2017 Bonpresta
 *
 * Bonpresta Awesome Image Slider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    Bonpresta
 *  @copyright 2015-2017 Bonpresta
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
    {if isset($params.type) && $params.type == 'box_image'}
         <img src="{$image_baseurl|escape:'htmlall':'UTF-8'}{$tr.image|escape:'htmlall':'UTF-8'}" class="img-slider" />
    {else}
        {$smarty.block.parent}
    {/if}
{/block}