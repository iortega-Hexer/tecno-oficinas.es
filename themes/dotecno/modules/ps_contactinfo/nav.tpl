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
<div id="_mobile_contact_link" class="hidden-md-up">
  <div class="contact-link">
    {if $contact_infos.fax}
      <a href="tel:{$contact_infos.fax}">
        <i class="material-icons">phone_in_talk</i>{$contact_infos.fax}
      </a>
    {/if}
    {if $contact_infos.phone}
      <a href="tel:{$contact_infos.phone}">
        <i class="material-icons">phone_iphone</i>{$contact_infos.phone}
      </a>
    {/if}
  </div>
</div>
<div id="_desktop_contact_link" class="hidden-sm-down">
  <div id="contact-link">
    {if $contact_infos.address}
      {$contact_infos.address.address1}
      {$contact_infos.address.address2},
      {$contact_infos.address.postcode}
      {$contact_infos.address.city}
    {/if}
    {if $contact_infos.fax}
      <a href="tel:{$contact_infos.fax}">
        <i class="material-icons">phone_in_talk</i>{$contact_infos.fax}
      </a>
    {/if}
    {if $contact_infos.phone}
      <a href="tel:{$contact_infos.phone}">
        <i class="material-icons">phone_iphone</i>{$contact_infos.phone}
      </a>
      {* [1][/1] is for a HTML tag. *}
      {* l
        s='Call us: [1]%phone%[/1]'
        sprintf=[
          '[1]' => '<span>',
          '[/1]' => '</span>',
          '%phone%' => $contact_infos.phone
        ]
        d='Shop.Theme.Global'
      *}
    {else}
      <a href="{$urls.pages.contact}">{l s='Contact us' d='Shop.Theme.Global'}</a>
    {/if}
  </div>
</div>
