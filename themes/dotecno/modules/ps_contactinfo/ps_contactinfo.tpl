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

<div class="block-contact links wrapper">
  <div class="footer-logo">
    <a href="{$urls.base_url}">
      <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
    </a>
    <div class="iconos">
      <img src="/img/cms/iconos-footer.png" alt="Certificados">
    </div>
  </div>
  <div class="footer-info">

      {if $contact_infos.address}
        <div class="direccion">
          {$contact_infos.address.address1} -
          {$contact_infos.address.address2}.
          {$contact_infos.address.postcode}
          {$contact_infos.address.city} /
          {$contact_infos.address.country}
        </div>
      {/if}
      {if $contact_infos.email}
        <div class="email">
          <a href="mailto:{$contact_infos.email}" class="dropdown">{$contact_infos.email}</a>
        </div>
      {/if}
      <div class="telefonos">
        {if $contact_infos.fax}
          <span class="tel">
            <a href="tel:{$contact_infos.fax}" class="dropdown">{$contact_infos.fax}</a>
          </span>
        {/if}
        <span class="separador"> - </span>
        {if $contact_infos.phone}
          <span class="tel">
            <a href="tel:{$contact_infos.phone}" class="dropdown">{$contact_infos.phone}</a>
          </span>
        {/if}
        <span class="separador"></span>
        <img src="/img/cms/modos-pago.jpg" alt="Modos de pago seguro">
        {hook h='displayNav2'}
      </div>
      {if $contact_infos.email}
        <div class="horario">
          {l s='Horario de verano: [1]%horario%[/1]'
            sprintf=[ '[1]' => '<span>', '[/1]' => '</span>', '%horario%' => $shop.registration_number ]
            d='Shop.Theme.Global'
          }
        </div>
      {/if}
  </div>
</div>
