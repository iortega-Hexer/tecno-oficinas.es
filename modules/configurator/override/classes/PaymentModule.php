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

class PaymentModule extends PaymentModuleCore
{
    protected function getEmailTemplateContent($template_name, $mail_type, $var)
    {
        $var = $this->overrideConfigurator();
        $email_configuration = Configuration::get('PS_MAIL_TYPE');
        if ($email_configuration != $mail_type && $email_configuration != Mail::TYPE_BOTH) {
            return '';
        }

        $theme_template_path = _PS_THEME_DIR_ . 'mails' . DIRECTORY_SEPARATOR
            . $this->context->language->iso_code . DIRECTORY_SEPARATOR . $template_name;
        $default_mail_template_path = _PS_MAIL_DIR_ . $this->context->language->iso_code
            . DIRECTORY_SEPARATOR . $template_name;

        if (Tools::file_exists_cache($theme_template_path)) {
            $default_mail_template_path = $theme_template_path;
        }

        if (Tools::file_exists_cache($default_mail_template_path)) {
            $this->context->smarty->assign('list', $var);
            return $this->context->smarty->fetch($default_mail_template_path);
        }
        return '';
    }

    public function overrideConfigurator()
    {
        require_once dirname(__FILE__) . '/../../modules/configurator/classes/ConfiguratorCartDetailModel.php';

        $context = Context::getContext();
        $cart = $context->cart;
        $order = new Order((int)Order::getOrderByCartId($cart->id));

        $product_var_tpl_list = array();
        if (Validate::isLoadedObject($order)) {
            foreach ($order->getProducts() as $product) {
                $product_var_tpl = array(
                    'reference' => $product['reference'],
                    'name' => $product['product_name'],
                    'unit_price' => Tools::displayPrice($product['unit_price_tax_incl'], $context->currency, false),
                    'price' => Tools::displayPrice($product['total_price_tax_incl'], $context->currency, false),
                    'quantity' => (int)$product['product_quantity'],
                    'customization' => array()
                );
                $customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
                if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']])) {
                    $customized_product = $customized_datas[$product['product_id']][$product['product_attribute_id']];
                    foreach ($customized_product[$order->id_address_delivery] as $customization) {
                        $customization_text = '';
                        if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                            foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                $customization_text .= $text['name'] . ': ' . $text['value'] . '<br />';
                            }
                        }

                        if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                            $customization_text .= sprintf(
                                Tools::displayError('%d image(s)'),
                                count($customization['datas'][Product::CUSTOMIZE_FILE])
                            ) . '<br />';
                        }

                        $customization_quantity = (int)$product['product_quantity'];

                        $product_var_tpl['customization'][] = array(
                            'customization_text' => $customization_text,
                            'customization_quantity' => $customization_quantity,
                            'quantity' => Tools::displayPrice(
                                $customization_quantity * $product['unit_price_tax_incl'],
                                $this->context->currency,
                                false
                            )
                        );
                    }
                }
                $product_var_tpl_list[] = $product_var_tpl;
            }
        }

        return $product_var_tpl_list;
    }
}
