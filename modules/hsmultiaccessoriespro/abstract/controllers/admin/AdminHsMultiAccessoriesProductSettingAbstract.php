<?php
/**
 * An abstract admin controller of the module
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AdminHsMultiAccessoriesProductSettingAbstract extends ModuleAdminController
{
    public $bootstrap = true;

    /**
     * Show result to view.
     *
     * @var type json
     */
    protected $ajax_json = array(
        'success' => false,
        'data' => null,
        'message' => null,
    );
    /**
     * Change product setting buy main product & accessory together.
     */
    public function displayAjaxChangeProductSettingBuyTogether()
    {
        $buy_together = (int) Tools::getValue('buy_together');
        $id_product = (int) Tools::getValue('id_product');
        exit(Tools::jsonEncode($this->saveProductSettingBuyTogether($id_product, $buy_together)));
    }

    /**
     * Save object HsMaProductSetting.
     *
     * @param int $id_product
     * @param int $buy_together
     *
     * @return bool
     */
    protected function saveProductSettingBuyTogether($id_product, $buy_together)
    {
        if (!$id_product) {
            return $this->ajax_json['success'] = false;
        }
        $product_setting = new HsMaProductSetting($id_product);
        $product_setting->id_product = (int) $id_product;
        $product_setting->buy_together = (int) $buy_together;
        $this->ajax_json['success'] = $product_setting->save();
        $this->ajax_json['message'] = $this->module->i18n['data_saved'];

        return $this->ajax_json;
    }

    /**
     * Change setting custom displayed name.
     *
     * @return json
     */
    public function displayAjaxChangeCustomDisplayedName()
    {
        $this->ajax_json['success'] = false;
        $custom_displayed_name = (int) Tools::getValue('custom_displayed_name');
        $id_product = (int) Tools::getValue('id_product');
        if (!$id_product) {
            exit(Tools::jsonEncode($this->ajax_json));
        }
        $product_setting = new HsMaProductSetting($id_product);
        $product_setting->id_product = (int) $id_product;
        $product_setting->custom_displayed_name = (int) $custom_displayed_name;
        $this->ajax_json['success'] = $product_setting->save();
        $this->ajax_json['message'] = $this->module->i18n['data_saved'];
        exit(Tools::jsonEncode($this->ajax_json));
    }
}
