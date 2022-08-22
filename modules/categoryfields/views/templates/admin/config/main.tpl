{*
* 2007-2017 Musaffar
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2007-2017 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div id="cf-main-wrapper" class="cf-wrapper">
    <div id="form-categoryfields-add" class="cf-tab-panel panel">
    </div>

    <div id="categoryfields-list" class="cf-tab-panel panel" style="padding-top: 50px;">
    </div>
</div>

<script>
    $(document).ready(function () {
        module_config_url = '{$module_config_url|escape:'quotes':'UTF-8'}';
        cf_admin_config_main_controller = new CFAdminConfigMainController('#cf-main-wrapper');
    });
</script>