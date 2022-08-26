/**
 * Point of sale for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

$(document).ready(function () {
    if (!$('body').hasClass('page-sidebar-closed') && !$('body').hasClass('page-topbar'))
        $('body').addClass('page-sidebar-closed');
});