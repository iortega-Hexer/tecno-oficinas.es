<?php
/**
 * 2007-2019 PrestaShop
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
 * @author    DMConcept <support@dmconcept.fr>
 * @copyright 2015 DMConcept
 * @license   http://opensource.org/licenses/afl-3.0.phpAcademic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class OrderDetail extends OrderDetailCore
{
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public function __construct($id = null, $id_lang = null, $context = null)
    {
        self::$definition['fields']['product_name'] = array(
            'type' => self::TYPE_HTML,
            'validate' => 'isCleanHtml',
            'required' => true
        );
        parent::__construct($id, $id_lang, $context);
    }
    /*
    * module: configurator
    * date: 2021-04-19 09:03:17
    * version: 4.31.0
    */
    public function saveTaxCalculator(Order $order, $replace = false)
    {
        require_once(dirname(__FILE__) . '/../../../modules/configurator/classes/OrderDetailHelper.php');
        if (is_array(OrderDetailHelper::$orderDetails) && in_array($this->id, OrderDetailHelper::$orderDetails)) {
            return true;
        }
        parent::saveTaxCalculator($order, $replace);
    }
}
