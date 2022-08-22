<?php
/**
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
*/

class AdminAjaxBonSlickController extends ModuleAdminController
{
    public function ajaxProcessUpdatePositionForm()
    {
        $items = Tools::getValue('item');
        $total = count($items);
        $id_shop = (int)$this->context->shop->id;
        $success = true;

        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'bonslick',
                array('sort_order' => (int)$i),
                '`id_tab` = '.(int)preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.(int)$id_shop
            );
        }
        if (!$success) {
            die(Tools::jsonEncode(array('error' => 'Update Fail')));
        }
        die(Tools::jsonEncode(array('success' => 'Update Success !', 'error' => false)));
    }
}
