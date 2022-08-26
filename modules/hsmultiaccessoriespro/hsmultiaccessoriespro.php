<?php
/**
 * Multi Accessories Pro
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/abstract/hsmultiaccessoriesabstract.php';
require_once dirname(__FILE__) . '/classes/HsAccessoriesGroupProduct.php';
require_once dirname(__FILE__) . '/classes/HsMaProductSetting.php';

class HsMultiAccessoriesPro extends HsMultiAccessoriesAbstract
{

    /**
     * construct method.
     */
    public function __construct()
    {
        $this->name = 'hsmultiaccessoriespro';
        $this->version = '4.5.0';
        $this->tab = 'front_office_features';
        $this->displayName = $this->l('Multi Accessories Pro');
        $this->class_controller_admin_group = 'AdminHsMultiAccessoriesGroupPro';
        $this->class_controller_admin_welcome_page = 'AdminHsMultiAccessoriesWelcomePagePro';
        $this->class_controller_accessory_search = 'AdminHsMultiAccessoriesSearchPro';
        $this->class_controller_admin_product_setting = 'AdminHsMultiAccessoriesProductSetting';
        $this->module_key = '32491cc370c598602cd7d07bdf4bdf14';
        $this->author_address = '0x741A23f5969c86d9118808f1eAC29D40B37562F1';
        $this->author = 'PrestaMonster';
        parent::__construct();
        $this->description = $this->l('Manage accessories of a product in groups and offer ability to check out the main product and its accessories in 1 click.');
        $this->confirmUninstall = sprintf($this->l('Do you want to uninstall %s?'), $this->displayName);
        $this->tab_admin_welcome_page = array('AdminHsMultiAccessoriesWelcomePagePro' => $this->l('Welcome page'));
        $this->tabs27 = array(
            array($this->class_controller_accessory_search => $this->l('Accessory search')),
            array($this->class_controller_admin_product_setting => $this->l('Product Setting')),
        );
    }

    /**
     * Install module.
     *
     * @return bool
     */
    public function install()
    {
        require_once dirname(__FILE__) . '/classes/HsMultiAccessoriesInstaller.php';
        $this->installer = new HsMultiAccessoriesInstaller($this->name, $this->class_controller_admin_group, $this->l('Multi Accessories Pro'));

        return parent::install();
    }

    /**
     * Uninstall module.
     *
     * @return bool
     */
    public function uninstall()
    {
        require_once dirname(__FILE__) . '/classes/HsMultiAccessoriesInstaller.php';
        $this->uninstaller = new HsMultiAccessoriesInstaller($this->name, $this->class_controller_admin_group, $this->displayName);

        return parent::uninstall();
    }

    /**
     * Dedicated callback to upgrading process.
     *
     * @param type $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        require_once dirname(__FILE__) . '/classes/HsMultiAccessoriesInstaller.php';
        $this->installer = new HsMultiAccessoriesInstaller($this->name, $this->class_controller_admin_product_setting, $this->class_controller_admin_group, $this->displayName);

        return parent::upgrade($version);
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (!($this->context->controller instanceof AdminProductsController) && !($this->context->controller instanceof AdminHsMultiAccessoriesWelcomePageProController) && !($this->context->controller instanceof AdminHsMultiAccessoriesGroupProController)) {
            return;
        }
        return parent::hookActionAdminControllerSetMedia();
    }
}
