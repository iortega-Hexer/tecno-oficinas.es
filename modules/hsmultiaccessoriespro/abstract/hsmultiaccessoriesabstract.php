<?php
/**
 * An abstract / main controller of the module
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/HsMaModule.php';
require_once dirname(__FILE__) . '/classes/HsAccessoriesGroupAbstract.php';
require_once dirname(__FILE__) . '/classes/HsAccessoryCartProductAbstract.php';
require_once dirname(__FILE__) . '/classes/HsMaSpecificPrice.php';
require_once dirname(__FILE__) . '/classes/HsMaProduct.php';
require_once dirname(__FILE__) . '/classes/HsMaLink.php';
require_once dirname(__FILE__) . '/classes/HsMaImageType.php';
require_once dirname(__FILE__) . '/classes/HsMaDisplayStyle.php';
require_once dirname(__FILE__) . '/classes/HsMaCartRule.php';

abstract class HsMultiAccessoriesAbstract extends HsMaModule
{
    const DEFAULT_QTY = 1;

    /**
     * The toolkit which is responsible for installing.
     *
     * @var object
     */
    protected $installer;

    /**
     * The toolkit which is responsible for uninstalling.
     *
     * @var object
     */
    protected $uninstaller;

    /**
     * Path to js folder.
     *
     * @var string
     */
    const PATH_JS = 'abstract/views/js/';

    /**
     * Path to css folder.
     *
     * @var string
     */
    const PATH_CSS = 'abstract/views/css/';

    /**
     * Path to image folder.
     *
     * @var string
     */
    const PATH_IMG = 'abstract/views/img/';

    /**
     * A list of translatable texts.
     *
     * @var array
     */
    public $i18n = array();

    /**
     * A list of urls to be used.
     *
     * @var array
     */
    public $urls = array();

    /**
     * check if cart is exists or not.
     *
     * @var bool
     */
    protected static $has_cart_been_added = false;

    /**
     * Array configuration key using setting.
     *
     * @var array
     *  <pre>
     *      array(
     *          'HSMA_DISPLAY_STYLE' => 'isString',
     *          'HSMA_SHOW_IMAGES' => 'isString',
     *          'HSMA_APPLY_FANCYBOX_TO_IMAGE' => 'isInt',
     *          'HSMA_SHOW_PRICE' => 'isString',
     *          'HSMA_SHOW_PRICE_TABLE' => 'isString',
     *          'HSMA_TITLE' => 'isString',
     *          'HSMA_MESSAGE_AVAILABLE_LATER' => 'isString',
     *          'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => 'isInt',
     *          'HSMA_ALERT_MESSAGE' => 'isString',
     *          'HSMA_SHOW_CUSTOM_QUANTITY' => 'isInt',
     *          'HSMA_CHANGE_MAIN_PRICE' => 'isInt'
     *      )
     */
    public $configuration_keys = array(
        'HSMA_DISPLAY_STYLE' => 'isInt',
        'HSMA_SHOW_IMAGES' => 'isInt',
        'HSMA_SHOW_SHORT_DESCRIPTION' => 'isInt',
        'HSMA_SHOW_PRICE' => 'isInt',
        'HSMA_SHOW_COMBINATION' => 'isInt',
        'HSMA_SHOW_PRICE_TABLE' => 'isInt',
        'HSMA_TITLE' => 'isString',
        'HSMA_MESSAGE_AVAILABLE_LATER' => 'isString',
        'HSMA_EACH_ACCESSORY_TO_BASKET' => 'isInt',
        'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB' => 'isInt',
        'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => 'isInt',
        'HSMA_SHOW_TOTAL_PRICE' => 'isInt',
        'HSMA_ALERT_MESSAGE' => 'isString',
        'HSMA_SHOW_CUSTOM_QUANTITY' => 'isInt',
        'HSMA_ALLOW_CUSTOMER_CHANGE_QTY' => 'isInt',
        'HSMA_CHANGE_MAIN_PRICE' => 'isInt',
        'HSMA_APPLY_FANCYBOX_TO_IMAGE' => 'isInt',
        'HSMA_IMAGE_SIZE_IN_FANCYBOX' => 'isString',
        'HSMA_SHOW_ACCESSORIES_OFS' => 'isInt',
        'HSMA_SHOW_ICON_OUT_OF_STOCK' => 'isInt',
        'HSMA_CHANGE_ACCESSORY_NAME' => 'isInt',
        'HSMA_SCROLL_TO_TABLE_PRICE' => 'isInt',
        'HSMA_DEL_ALL_WHEN_DEL_MPRODUCT' => 'isInt',
        'HSMA_DEL_ALL_WHEN_DEL_ACCESSORY' => 'isInt',
        'HSMA_SHOW_NW_VISIBILITY_PRODUCTS' => 'isInt',
    );

    /**
     * Array configuration language keys using setting.
     *
     * @var array
     *  <pre>
     *      array(
     *          'HSMA_TITLE',
     *          'HSMA_MESSAGE_AVAILABLE_LATER',
     *          'HSMA_ALERT_MESSAGE',
     *      )
     */
    public $configuration_lang_keys = array(
        'HSMA_TITLE',
        'HSMA_MESSAGE_AVAILABLE_LATER',
        'HSMA_ALERT_MESSAGE',
    );

    /**
     * check if hookActionCartSave is executed?
     *
     * @var bool
     */
    protected static $is_executed = false;

    /**
     * a list of id accessory.
     *
     * @var array
     */
    protected $accessories = array();

    /**
     * construct function.
     */
    public function __construct()
    {
        $this->tab = 'front_office_features';
        $this->author = 'PrestaMonster';
        parent::__construct();
        $this->loadLink();
        if (defined('_PS_ADMIN_DIR_')) {
            if (Validate::isLoadedObject($this->context->employee)) {
                $this->assignAdminUrls();
            }
        }
        $this->initTranslations();
        /*$this->registerHook('displayAdminAccessories');*/
    }
    
    public function hookDisplayAdminAccessories()
    {
        return $this->hookDisplayAdminListBefore();
    }

    /**
     * Retro-compatible with PrestaShop 1.5.3.1 and older where Context->link is not intialized ahead of Dispatcher (in config.inc.php)
     */
    protected function loadLink()
    {
        if (empty($this->context->link)) {
            $protocol_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
            $protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
            $this->context->link = new Link($protocol_link, $protocol_content);
        }
    }

    /**
     * Install module.
     *
     * @return bool
     */
    public function install()
    {
        $success = array();
        $success[] = parent::install();

        $success[] = $this->installer->installTables();
        $success[] = $this->installer->addNewImageType();

        $success[] = $this->installer->installTabs();
        $success[] = $this->installer->installTab243($this->tab_admin_welcome_page);
        $success[] = $this->installer->installTab27($this->tabs27);
        $success[] = $this->installer->updateTable284();
        $success[] = $this->installer->installTables290();

        $success[] = $this->installer->installConfigs();
        $success[] = $this->setDefaultAlertMessage();
        $success[] = $this->setDefaultTitle();

        $success[] = $this->registerHooks();

        return array_sum($success) >= count($success);
    }

    /**
     * Register neccessary hooks.
     *
     * @return bool
     */
    protected function registerHooks()
    {
        $success = array();
        // display at Back office
        $success[] = $this->registerHook('displayAdminListBefore');
        $success[] = $this->registerHook('displayAdminProductsExtra');

        // action at Back office
        if ($this->isPrestashop15()) {
            $success[] = $this->registerHook('actionAdminControllerSetMedia');
        } else {
            $success[] = $this->registerHook('actionAdminProductsFormModifier');
        }
        $success[] = $this->registerHook('actionProductDelete');
        $success[] = $this->registerHook('actionProductAdd');
        $success[] = $this->registerHook('actionProductUpdate');

        // display at Front office
        $success[] = $this->registerHook('displayHeader');
        if ($this->isPrestashop17()) {
            $success[] = $this->registerHook('displayReassurance');
            $success[] = $this->registerHook('displayNavFullWidth');
            if ($this->isPrestashop1711()) {
                $success[] = $this->registerHook('actionObjectProductInCartDeleteAfter');
            } else {
                $success[] = $this->registerHook('actionDeleteProductInCartAfter');
            }
        } else {
            $success[] = $this->registerHook('displayFooter');
            $success[] = $this->registerHook('displayRightColumnProduct');
            $success[] = $this->registerHook('actionAfterDeleteProductInCart');
            $success[] = $this->registerHook('displayTop');
        }
        $success[] = $this->registerHook('displayMultiAccessoriesProduct');
        $success[] = $this->registerHook('displayShoppingCartFooter'); // this is unstable yet
        // action at Front office
        $success[] = $this->registerHook('actionCartSave');
        $success[] = $this->registerHook('actionObjectCartAddAfter');
        $success[] = $this->registerHook('actionObjectHsAccessoriesGroupAbstractDeleteAfter');
        $success[] = $this->registerHook('deleteProductAttribute');
        
        return array_sum($success) >= count($success);
    }

    /** Uninstall module
     * @return bool
     */
    public function uninstall()
    {
        $success = array(parent::uninstall());
        $success[] = array_sum($success) >= count($success) && $this->uninstaller->uninstallTabs();
        // Don't uninstall tables for now
        // $success[] = array_sum($success) >= count($success) && $this->uninstaller->uninstallTables();
        return array_sum($success) >= count($success);
    }

    /**
     * Set default alert message when install module.
     *
     * @return bool
     */
    protected function setDefaultAlertMessage()
    {
        // set default alert message when install
        $flag = true;
        $languages = Language::getLanguages(false);
        $titles = array();
        if (!empty($languages)) {
            foreach ($languages as $language) {
                $titles[$language['id_lang']] = $this->i18n['you_have_to_select_at_least_1_accessory_in_this_group'];
            }
        }
        if (!empty($titles)) {
            $flag = $flag && Configuration::updateValue('HSMA_ALERT_MESSAGE', $titles);
        }

        return $flag;
    }

    /**
     * hook header.
     */
    public function hookDisplayHeader()
    {
        if (!$this->isHookedInCurrentPage()) {
            return;
        }
        if ($this->isPrestashop17()) {
            $this->context->controller->registerStylesheet('modules-'. $this->name, 'modules/' . $this->name . '/abstract/views/css/multiaccessoriespro.min.css', array('media' => 'all', 'priority' => 150));
            $this->context->controller->registerJavascript('modules-'. $this->name, 'modules/' . $this->name . '/abstract/views/js/multi_accessories_pro.min.js', array('position' => 'bottom', 'priority' => 150));
        } else {
            if ($this->isPrestashop16()) {
                $this->context->controller->addCSS($this->getCSSPath() . 'multiaccessories_16.css', 'all');
                $this->context->controller->addJS($this->getJsPath() . 'accessoriesprice_16.js');
            } else {
                $this->context->controller->addCSS($this->getCSSPath() . 'multiaccessories_15.css', 'all');
                $this->context->controller->addJS($this->getJsPath() . 'accessoriesprice_15.js');
            }
            $this->context->controller->addJS($this->getJsPath() . 'hsma_display_style.js');
            $this->context->controller->addJS($this->getJsPath() . 'admin_product_setting.js');
            $this->context->controller->addJS($this->getJsPath() . 'pricetable.js');
            $this->context->controller->addJS($this->getJsPath() . 'format_string.js');

            $this->context->controller->addJS($this->getJsPath() . 'jquery.ddslick.js');
            $this->context->controller->addJS($this->getJsPath() . 'jquery.visible.js');
            $this->context->controller->addJS($this->getJsPath() . 'hsma_render_accessories.js');
            $this->context->controller->addJS($this->getJsPath() . 'hsma_customization_popup.js');
            
            $this->context->controller->addCSS($this->getCSSPath() . 'tableprice.css', 'all');
            $this->context->controller->addCSS($this->getCSSPath() . 'multiaccessories.css', 'all');
        }
        $this->assignSmartyVariables();
        return $this->display($this->name . '.php', 'display_header.tpl');
    }

    /**
     * hook footer.
     */
    public function hookDisplayFooter()
    {
        if (!$this->isHookedInCurrentPage()) {
            return;
        }
        if ($this->isPrestashop16()) {
            $add_to_basket_js_file = $this->isEnableBlockCartAjax() ? 'accessoriescart_16.js' : 'accessories_add_to_cart.js';
        } else {
            $add_to_basket_js_file = $this->isEnableBlockCartAjax() ? 'accessoriescart_15.js' : 'accessories_add_to_cart.js';
        }
        $this->context->controller->addJS($this->getJsPath() . $add_to_basket_js_file);
        $this->context->controller->addJS($this->getJsPath() . 'pricetable.js');
    }

    /**
     * To decide if we should utilize module blockcart's function or not (when adding an accessory to basket).
     */
    protected function isEnableBlockCartAjax()
    {
        // probably, we only need to consider about that in product page; otherwsie, disable!
        $blockcart_modules = array(
            'ps_shoppingcart', // PS 1.7
            'blockcart',
            'blockslidingcart',
            'blockcart_mod'// found in theme "transformer"
        );
        foreach ($blockcart_modules as $blockcart) {
            if (Module::isEnabled($blockcart)) {
                if (Configuration::get('PS_BLOCK_CART_AJAX')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isProductPage()
    {
        return $this->context->controller instanceof ProductController;
    }

    /**
     * Check if Multi Accessories block is available in current page.
     *
     * @return bool
     */
    protected function isHookedInCurrentPage()
    {
        $id_groups = HsAccessoriesGroupAbstract::getIdGroups($this->context->language->id, true);
        if ($this->context->controller instanceof ProductController) {
            $id_product = Tools::getValue('id_product');
            if (empty($id_groups) || empty($id_product) || !HsAccessoriesGroupAbstract::haveAccessories(array($id_product), (int) $this->context->language->id)) {
                return false;
            }
            return true;
        }

        if ($this->context->controller instanceof OrderController || $this->context->controller instanceof OrderOpcController) {
            if ($this->isRegisteredInHook('displayShoppingCart') || $this->isRegisteredInHook('displayShoppingCartFooter')) {
                return true;
            }
        }
        return empty($id_groups) ? false : (bool) Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER');
    }

    /**
     * @see AdminController::renderForm()
     * @param array $params
     * <pre>
     * array(
     *  'fields' => array(),
     *  'fields_value' => array(),
     *  'form_vars' => array()
     * )
     */
    public function hookActionAdminProductsFormModifier(array $params = array())
    {
        $this->context->controller->addJS($this->getJsPath() . 'admin_product_setting.js');
        $this->context->controller->addJS($this->getJsPath() . 'admin_multi_accessories.js');
        $this->context->controller->addJS($this->getJsPath() . 'jquery-confirm.min.js');
        $this->context->controller->addJS($this->getJsPath() . 'hsma_sidebar_closed.js');
        $this->context->controller->addCSS($this->getCssPath() . 'accessory_admin_tab.css');
        $this->context->controller->addCSS($this->getCssPath() . 'jquery-confirm.min.css');
        if (!$this->isPrestashop16()) {
            $this->context->controller->addCSS($this->getCssPath() . 'accessory_admin_tab_15.css');
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        return $this->hookActionAdminProductsFormModifier();
    }
    
    /**
     * Assign all possible urls which will be in use within the module.
     */
    protected function assignAdminUrls()
    {
        $this->urls = array(
            'ajaxSaveNameUrl' => $this->getTargetUrl($this->class_controller_admin_group, 'saveName'),
            'ajaxAddAccessory' => $this->getTargetUrl($this->class_controller_admin_group, 'addAccessory'),
            'ajaxDeleteAccessory' => $this->getTargetUrl($this->class_controller_admin_group, 'deleteAccessory'),
            'ajaxChangeProductCombination' => $this->getTargetUrl($this->class_controller_admin_group, 'changeProductCombination'),
            'ajaxRegenerateThumbnails' => $this->getTargetUrl($this->class_controller_admin_group, 'regenerateThumbnails'),
            'ajaxChangeDisplayStyle' => $this->getTargetUrl($this->class_controller_admin_group, 'changeDisplayStyle'),
            'ajaxChangeMinQuantity' => $this->getTargetUrl($this->class_controller_admin_group, 'changeMinQuantity'),
            'ajaxChangeDefaultQuantity' => $this->getTargetUrl($this->class_controller_admin_group, 'changeDefaultQuantity'),
            'ajaxAutoCompleteSearch' => $this->getTargetUrl($this->class_controller_accessory_search, 'autoCompleteSearch'),
            'ajaxChangeProductSettingBuyTogether' => $this->getTargetUrl($this->class_controller_admin_product_setting, 'changeProductSettingBuyTogether'),
            'ajaxChangeCustomDisplayedName' => $this->getTargetUrl($this->class_controller_admin_product_setting, 'changeCustomDisplayedName'),
            'ajaxCopyAccessories' => $this->getTargetUrl($this->class_controller_admin_group, 'copyAccessories'),
            'ajaxChangeAccessorySettingBuyTogether' => $this->getTargetUrl($this->class_controller_admin_group, 'changeAccessorySettingBuyTogether'),
            'ajaxProcessUpdateAccessoryProductPosition' => $this->getTargetUrl($this->class_controller_admin_group, 'updateAccessoryProductPosition'),
            'ajaxProcessFilterByCategories' => $this->getTargetUrl($this->class_controller_admin_group, 'filterByCategories'),
            'ajaxChangeDiscountValue' => $this->getTargetUrl($this->class_controller_admin_group, 'changeDiscountValue'),
            'ajaxAssignAccessories' => $this->getTargetUrl($this->class_controller_admin_group, 'assignAccessories'),
            'ajaxCopyAccessories' => $this->getTargetUrl($this->class_controller_admin_group, 'copyAccessories'),
            'ajaxDeleteOldAccessories' => $this->getTargetUrl($this->class_controller_admin_group, 'deleteOldAccessories'),
            'ajaxReplaceAccessory' => $this->getTargetUrl($this->class_controller_admin_group, 'replaceAccessory'),
            'ajaxRenderAccessoryImages' => $this->getTargetUrl($this->class_controller_admin_group, 'renderAccessoryImages'),
            'ajaxRenderBlockModuleSetting' => $this->getTargetUrl($this->class_controller_admin_group, 'renderBlockModuleSetting'),
        );
    }

    /**
     * All translatable texts which can be easy to use in Smarty or any module controllers.
     *
     * <br />
     * For example:<br />
     * - Smarty<br />
     * {$hs_i18n.text_1}<br />
     * - controller<br />
     * $this->module->i18n[text_1]
     */
    protected function initTranslations()
    {
        $group_accessory_link = '';
        if (($this->context->controller instanceof AdminProductsController) || ($this->context->controller instanceof AdminHsMultiAccessoriesWelcomePageProController) || ($this->context->controller instanceof AdminHsMultiAccessoriesGroupProController)) {
            $group_accessory_link_without_action = $this->context->link->getAdminLink($this->class_controller_admin_group);
            $group_accessory_link = $group_accessory_link_without_action.'&addaccessory_group';
        }
        $source = basename(__FILE__, '.php');
        $this->i18n = array(
            'add_to_cart' => $this->l('Add to cart', $source),
            'automatic_to_change_the_accessory_name_when_you_change_the_product_name_in_the_edit_product_page' => $this->l('Automatic to change the accessory name when you change the product name in the edit product page.', $source),
            'an_error_occurred_while_attempting_to_move_this_accessory' => $this->l('An error occurred while attempting to move this accessory.', $source),
            'select_accessory' => $this->l('Select an accessory', $source),
            'there_is_not_any_accessory_group' => sprintf($this->l("There is not any accessory group. Let's %s create the first one. %s", $source), '<a href="' . $group_accessory_link . '">', '</a>'),
            'id' => $this->l('ID', $source),
            'group_name' => $this->l('Group name', $source),
            'active' => $this->l('Active', $source),
            'multi_accessories' => $this->l('Multi Accessories', $source),
            'data_saved' => $this->l('Data saved', $source),
            'the_accessory_has_been_successfully_added' => $this->l('The accessory has been successfully added.', $source),
            'the_accessory_has_been_successfully_copied' => $this->l('The accessory has been successfully copied.', $source),
            'copy_related_products_or_accessories_of_PS_15_16_to_this_module' => $this->l('Copy related products (or accessories of PS 1.5, 1.6) to this module.', $source),
            'delete_all_the_added_accessories_of_module' => $this->l('Delete all added accessories of this module.', $source),
            'the_accessory_has_been_successfully_deleted' => $this->l('The accessory has been successfully deleted.', $source),
            'delete_selected_items' => $this->l('Delete selected items', $source),
            'display_icon_out_of_stock_at_the_front_end' => $this->l('Display icon out of stock at the front end', $source),
            'show_quantity_box_of_accessories' => $this->l('Show quantity box of accessories', $source),
            'display_quantity_box_along_with_each_accessory' => $this->l('Display quantity box along with each accessory.', $source),
            'change_accessory_name_when_change_product_name' => $this->l('Change accessory name when change product name', $source),
            'checkbox' => $this->l('Checkbox', $source),
            'dropdown' => $this->l('Dropdown', $source),
            'radio' => $this->l('Radio', $source),
            'settings' => $this->l('Settings', $source),
            'display_style' => $this->l('Display style', $source),
            'define_how_accessories_look_like_at_product_page' => $this->l('Define how accessories look like at product page.', $source),
            'display_images_along_with_each_accessory' => $this->l('Display images along with each accessory.', $source),
            'display_price_along_with_each_accessory' => $this->l('Display price along with each accessory.', $source),
            'tell_your_customers_a_summary' => $this->l('Tell your customers a summary of which accessories to pick up and how much to pay.', $source),
            'add_an_icon_where_people_can_read_description_instead_of_open_that_accessory' => $this->l('Add an icon where people can read description instead of open that accessory.', $source),
            'title_of_accessory_block_at_product_page' => $this->l('Title of accessory block at product page.', $source),
            'show_images' => $this->l('Show images', $source),
            'show_price' => $this->l('Show price', $source),
            'show_short_description' => $this->l('Show short description', $source),
            'show_price_table' => $this->l('Show price table', $source),
            'show_total_price_instead_of_the_main_product_price_at_the_product_list_page' => $this->l('Show total price instead of the main product price at the product list page', $source),
            'show_total_price_main_product_price_required_accessories_price_instead_of_the_main_product_price_at_the_product_list_page' => $this->l('Show total price (main product price + required accessories price) instead of the main product price at the product list page.', $source),
            'title' => $this->l('Title', $source),
            'save' => $this->l('Save', $source),
            'open_new_tab' => $this->l('Open in a new tab', $source),
            'view' => $this->l('view', $source),
            'must_have_accessories' => $this->l('Must-have accessories', $source),
            'save_and_stay' => $this->l('Save and stay', $source),
            'cancel' => $this->l('Cancel', $source),
            'sub_total' => $this->l('Sub total', $source),
            'you_have_to_select_at_least_1_accessory_in_this_group' => $this->l('You have to select at least 1 accessory in this group', $source),
            'quantity' => $this->l('Quantity', $source),
            'edit_group' => $this->l('Edit group', $source),
            'add_a_new_accessory_group' => $this->l('Add a new accessory group', $source),
            'default_quantity' => $this->l('Default qty', $source),
            'group' => $this->l('Group', $source),
            'name' => $this->l('Name', $source),
            'enabled' => $this->l('Yes', $source),
            'disabled' => $this->l('No', $source),
            'ok' => $this->l('ok', $source),
            'error' => $this->l('error', $source),
            'search_for_items' => $this->l('Search for items ...', $source),
            'search_for_a_product' => $this->l('Search for a product ...', $source),
            'accessory_group' => $this->l('Accessory group', $source),
            'invalid_characters' => $this->l('Invalid characters:', $source),
            'alert_message' => $this->l('Alert message', $source),
            'tell_your_customer_when_they_dont_choose_any_accessories_to_buy_together_with_main_product' => $this->l('Tell your customer when they don\'t choose any accessories to buy together with main product.', $source),
            'apply_fancybox_to_images' => $this->l('Apply Fancybox to images', $source),
            'show_accessory_images_in_a_fancybox' => $this->l('Show accessory images in a Fancybox.', $source),
            'image_size_in_fancybox' => $this->l('Image size in Fancybox', $source),
            'display_prices_along_with_each_accessory' => $this->l('Display prices along with each accessory.', $source),
            'change_the_main_item_s_price_accordingly' => $this->l('Change the main item\'s price accordingly', $source),
            'whenever_an_accessory_is_added_or_removed_the_main_item_s_price_is_changed_and_your_customers_clearly_know_the_amount' => $this->l('Whenever an accessory is added or removed, the main item\'s price is changed, and your customers clearly know the amount.', $source),
            'add_custom_quantity_to_basket' => $this->l('Add custom quantity to basket', $source),
            'allow_customer_add_custom_quantity_of_each_accessory_to_basket' => $this->l('Allow customer add custom quantity of each accessory to basket.', $source),
            'allow_your_customers_to_change_item_quantity' => $this->l('Allow your customers to change item quantity.', $source),
            'buy_main_product_accessories_together' => $this->l('Buy main product & accessories together', $source),
            'tell_your_customers_that_they_need_to_buy_main_product_and_accessories_together' => $this->l('Tell your customers that they need to buy main product and accessories together.', $source),
            'buy_main_product_accessories_of_this_group_together' => $this->l('Buy main product & accessories of this group together', $source),
            'tell_your_customers_that_they_need_to_buy_main_product_and_accessories_of_this_group_together' => $this->l('Tell your customers that they need to buy main product and accessories of this group together.', $source),
            'tell_your_customers_that_this_accessory_is_out_of_stock' => $this->l('Tell your customers that this accessory is out of stock', $source),
            'add_each_accessory_to_basket' => $this->l('Add each accessory to basket', $source),
            'allow_customer_add_separated_accessory_to_basket' => $this->l('Allow customer add separated accessory to basket.', $source),
            'open_accessories_in_a_new_tab' => $this->l('Open accessories in a new tab', $source),
            'global_update' => $this->l('Global update', $source),
            'select_a_combination_optional' => $this->l('Select a combination (optional)', $source),
            'click_to_view_details' => $this->l('Click to view details', $source),
            'you_must_save_this_product_before_adding_accessories' => $this->l('You must save this product before adding accessories', $source),
            'update_successful' => $this->l('Update successful', $source),
            'use_default' => $this->l('Use default', $source),
            'accessory_is_out_of_stock' => $this->l('Oops! This item is out of stock.', $source),
            'there_is_not_enough_product_in_stock' => $this->l('There is not enough product in stock.', $source),
            'yes' => $this->l('Yes', $source),
            'you_do_not_have_the_right_permission' => $this->l('You do not have the right permission', $source),
            'no' => $this->l('No', $source),
            'use_default' => $this->l('Use default', $source),
            'let_me_specify' => $this->l('Let me specify', $source),
            'buy_main_product_accessory_together' => $this->l('Buy main product accessory together', $source),
            'product_settings' => $this->l('Product settings', $source),
            'required' => $this->l('Required?', $source),
            'if_the_text_displayed_text_when_backordering_is_allowed_in_product_edit_page_is_empty' => $this->l('If the text "Displayed text when backordering is allowed" in product edit page is empty , this message will be displayed.', $source),
            'displayed_text_when_backordering_is_allowed' => $this->l('Displayed text when backordering is allowed', $source),
            'out_of_stock_but_backordering_is_allowed' => $this->l('Out of stock but backordering is allowed.', $source),
            'out_of_stock' => $this->l('Out of stock', $source),
            'only_use_custom_displayed_names_for_this_product' => $this->l('Only use custom displayed names for this product', $source),
            'otherwise_wherever_that_accessory_is_displayed' => $this->l('Otherwise, wherever that accessory is displayed (in Multi Accessories block only), they share the same displayed name.', $source),
            'advanced_settings_for_this_product_only' => $this->l('Advanced settings (for this product only)', $source),
            'accessory' => $this->l('Accessory', $source),
            'displayed_name' => $this->l('Displayed name', $source),
            'price' => $this->l('Price', $source),
            'min_qty' => $this->l('Min qty', $source),
            'invalid_product' => $this->l('Invalid product', $source),
            'oops_something_goes_wrong' => $this->l('Oops! Something goes wrong!', $source),
            'min_quantity_must_be_less_than_available_quantity' => $this->l('Minimum quantity must be less than available quantity.', $source),
            'default_quantity_should_be_greater_than_or_equal_to_minimum_quantity' => $this->l('Default quantity should be greater than or equal to minimum quantity.', $source),
            'quantity_must_be_greater_than_or_equal_to_minimum_quantity' => $this->l('Quantity must be greater than or equal to {0}.', $source),
            'oops_cannot_update_accessory' => $this->l('Oops! Cannot update accessory', $source),
            'position' => $this->l('Position', $source),
            'action' => $this->l('Action', $source),
            'item_inside' => $this->l('%s item inside', $source),
            'items_inside' => $this->l('%s items inside', $source),
            'click_to_edit' => $this->l('Click to edit', $source),
            'there_is_no_accessory_in_this_group' => $this->l('There is no accessory in this group.', $source),
            'there_isnt_enough_product_in_stock' => $this->l('There isn\'t enough product in stock.', $source),
            'discount' => $this->l('Discount', $source),
            'final_price' => $this->l('Final Price', $source),
            'amount' => $this->l('amount', $source),
            'percent' => $this->l('%', $source),
            'discount_for_accessory' => $this->l('Discount for accessory %s', $source),
            'only_valid_when_buying_with_main_product' => $this->l('Only valid when buying with main product ', $source),
            'can_not_save_cart_rule' => $this->l('Can\'t save cart rule', $source),
            'this_rule_is_applied_for_product_level' => $this->l('This rule is applied for product level', $source),
            'copy_accessories_from' => $this->l('Copy accessories from', $source),
            'copy_accessories' => $this->l('Copy accessories', $source),
            'you_are_about_to_copy_accessories_from_another_product_to_this_product' => $this->l('You are about to copy accessories from another product to this product. Do you want to keep current accessories of this product?', $source),
            'cannot_copy_accessories' => $this->l('Cannot copy accessories', $source),
            'invalid_product' => $this->l('Invalid product', $source),
            'yes' => $this->l('Yes', $source),
            'no' => $this->l('No', $source),
            'cancel' => $this->l('Cancel', $source),
            'add_a_discount' => $this->l('Add a discount', $source),
            'you_are_about_to_add_a_discount_for_the_product_and_accessory' => $this->l('You are about to add a discount for the product and accessory. Do you want to apply this discount to all the main products that have this accessory? (Yes: apply to all main products. No: only apply to this main product.)', $source),
            'none' => $this->l('None', $source),
            'display_combination_info_in_price_table' => $this->l('Display combination info in price table', $source),
            'collapse_expand_accessory_groups' => $this->l('Collapse/expand accessory groups', $source),
            'expand_all_groups' => $this->l('Expand all groups', $source),
            'expand' => $this->l('Expand', $source),
            'expand_the_first_group' => $this->l('Expand the first group', $source),
            'collapse_all_groups' => $this->l('Collapse all groups', $source),
            'show_group_name' => $this->l('Show group name', $source),
            'show_hide_group_name_at_the_front_end' => $this->l('Show hide group name at the front end', $source),
            'collapse_expand_group_at_the_front_end' => $this->l('Collapse|expand group at the front end', $source),
            'collapse_expand_group' => $this->l('Collapse|expand group', $source),
            'collapse' => $this->l('Collapse', $source),
            'free' => $this->l('Free', $source),
            'there_was_a_connecting_problem' => $this->l('There was a connecting problem. Please check your internet connection and try again.', $source),
            'request_time_out' => $this->l('Request time out.', $source),
            'requested_page_not_found' => $this->l('Requested page not found.', $source),
            'internal_server_error' => $this->l('Internal server error.', $source),
            'ajax_request_is_aborted' => $this->l('Ajax request is aborted.', $source),
            'add_multi_accessories_for_multi_products' => $this->l('Add multi accessories for multi products', $source),
            'select_categories_products' => $this->l('Select categories products', $source),
            'filter_by_category' => $this->l('Filter by category', $source),
            'select_all' => $this->l('Select all', $source),
            'product_name' => $this->l('Product name', $source),
            'image' => $this->l('Image', $source),
            'products' => $this->l('Products', $source),
            'accessories' => $this->l('Accessories', $source),
            'accessory_name' => $this->l('Accessory name', $source),
            'assign' => $this->l('Assign', $source),
            'product_categories' => $this->l('Product categories', $source),
            'accessory_categories' => $this->l('Accessory categories', $source),
            'select_an_accessory_group' => $this->l('Select an accessory group', $source),
            'get_products_accessories' => $this->l('Get products and accessories', $source),
            'please_select_at_least_1_accessory' => $this->l('Please select at least 1 accessory.', $source),
            'please_select_at_least_1_accessory_category' => $this->l('Please select at least 1 accessory category.', $source),
            'please_select_at_least_1_product' => $this->l('Please select at least 1 product.', $source),
            'please_select_at_least_1_product_category' => $this->l('Please select at least 1 product category.', $source),
            'please_select_a_group_accessory' => $this->l('Please select a group accessory.', $source),
            'there_is_no_product' => $this->l('There is no product.', $source),
            'there_is_no_accessory' => $this->l('There is no accessory.', $source),
            'display_accessories_out_of_stock_at_the_front_end' => $this->l('Display accessories & combinations out of stock at the front end', $source),
            'display_or_hide_accessories_out_of_stock_at_the_front_end' => $this->l('Display or hide accessories & combinations out of stock at the front end', $source),
            'please_uncheck_all_categories_after_that_select_1_or_2_categories_and_filter_again' => $this->l('Please uncheck all categories after that select 1 or 2 categories and filter again.', $source),
            'created_by' => $this->l('Created by', $source),
            'current_version' => $this->l('Current version:', $source),
            'documentation' => $this->l('Documentation', $source),
            'rate_us' => $this->l('Rate us', $source),
            'need_help' => $this->l('Need Help ?', $source),
            'all_modules' => $this->l('All modules developed by ', $source),
            'scroll_to_the_table_price_when_customers_select_unselect_an_accessory_at_the_front_end' => $this->l('Scroll to the table price when customers select | unselect an accessory at the front end.', $source),
            'scroll_to_the_table_price' => $this->l('Scroll to the table price', $source),
            'delete_main_product_and_all_accessories_when_delete_main_product_from_basket' => $this->l('Delete main product and all accessories when delete main product from basket', $source),
            'automatic_to_delete_main_product_and_all_accessories_of_this_product_when_your_customer_deletes_main_product_from_the_basket' => $this->l('Automatic to delete main product and all accessories of this product when your customer deletes main product from the basket.', $source),
            'delete_main_product_and_all_accessories_when_delete_an_accessory_from_basket' => $this->l('Delete main product and all accessories when delete an accessory from basket', $source),
            'automatic_to_delete_main_product_and_all_accessories_for_this_product_when_your_customer_deletes_an_accessory_from_the_basket' => $this->l('Automatic to delete main product and all accessories for this product when your customer deletes an accessory from the basket.', $source),
            'display_nowhere_visibility_products_at_the_front_end' => $this->l('Display NoWhere Visibility products at the front end.', $source),
            'accessory_customization' => $this->l('Add accessory customization', $source),
            'dont_forget_to_save_your_customization_to_be_able_to_add_to_cart' => $this->l('Do not forget to save your customization to be able to add to cart.', $source),
            'your_message_here' => $this->l('Your message here', $source),
            'your_customization' => $this->l('Your customization', $source),
            'remove_image' => $this->l('Remove Image', $source),
            'no_selected_file' => $this->l('No selected file', $source),
            'choose_file' => $this->l('Choose file', $source),
            'png_jpg_gif' => $this->l('.png .jpg .gif', $source),
            'save_customization' => $this->l('Save Customization', $source),
            'customize' => $this->l('Customize', $source),
            'more' => $this->l('More', $source),
            'please_fill_the_required_custom_fields_to_complete_the_sale' => $this->l('Please fill the required custom fields to complete the sale', $source),
            'add_customization_data' => $this->l('Add customization data', $source),
            'char_max' => $this->l('250 char. max', $source),
            'required_fields' => $this->l('Required fields', $source),
            'cannot_submit_a_empty_form' => $this->l('Cannot submit a empty form.', $source),
            'copy_default_PS_accessories_in_this_module' => $this->l('Copy related products (accessories) to this module', $source),
            'copy_default_accessories' => $this->l('Copy related products (accessories)', $source),
            'delete_old_accessories' => $this->l('Delete old accessories', $source),
            'the_product_is_no_longer_available_in_this_quantity' => $this->l('The product is no longer available in this quantity.', $source),
            'pack_content' => $this->l('Pack content', $source),
            'this_pack_contains' => $this->l('This pack contains', $source),
            'accessory_not_found' => $this->l('Accessory not found', $source),
            'an_error_occurred_while_showing_the_list_of_products_in_the_package' => $this->l('An error occurred while showing the list of products in the package.', $source),
            'an_error_occurred_while_showing_the_accessory_customization' => $this->l('An error occurred while showing the accessory customization.', $source),
            'this_is_a_packaged_product_please_click_here_to_view_the_list_of_products_for_this_pack' => $this->l('This is a packaged product. Please click here to view the list of products for this pack.', $source),
            'do_you_want_to_delete_all_added_accessories' => $this->l('Do you want to delete all added accessories?', $source),
            'replace_an_accessory_with_another_accessory' => $this->l('Replace an accessory with another accessory', $source),
            'you_have_added_a_product_like_an_accessory_to_some_products' => $this->l('You have added a product (like an accessory) to some products. Now, this product is out of stock and you will never buy it again. You can replace it with another product in this tab.', $source),
            'replace' => $this->l('Replace', $source),
            'search_select_old_accessory' => $this->l('Search & select old accessory', $source),
            'search_select_new_accessory' => $this->l('Search & select new accessory', $source),
            'search_for_old_accessory' => $this->l('Search for old accessory...', $source),
            'search_for_new_accessory' => $this->l('Search for new accessory...', $source),
            'please_search_select_a_new_old_accessory' => $this->l('Please search and select a new or old accessory.', $source),
            'old_new_accessories_should_be_different' => $this->l('Old and new accessories should be different.', $source),
            'the_accessory_has_been_successfully_replaced' => $this->l('The accessory has been successfully replaced.', $source),
            'an_error_occurred_while_replacing_the_accessory' => $this->l('An error occurred while replacing the accessory.', $source),
            'quickly_fix_common_errors' => $this->l('Quickly fix common errors', $source),
            'missing_accessory_images' => $this->l('Missing accessory images at the front end', $source),
            'render_accessory_image' => $this->l('Render accessory image', $source),
            'does_not_show_block_module_settings_on_this_page' => $this->l('Does not show block module settings on this page', $source),
            'show_block_settings' => $this->l('Show block settings', $source),
            'an_error_occurred_while_adding_the_image_type' => $this->l('An error occurred while adding the image type.', $source),
            'the_accessory_images_have_been_successfully_rendered' => $this->l('The accessory images have been successfully rendered.', $source),
            'the_block_module_setting_has_been_successfully_registed' => $this->l('The block module setting has been successfully registed.', $source),
            'an_error_occurred_while_registering_block_module_setting' => $this->l('An error occurred while registering block module setting.', $source),
            'after_clicking_on_the_fixing_button_please_help_us_turn_off_all_the_cache_options' => $this->l('After clicking on the fixing button, please help us turn off all the cache options, clear your browser cache before testing. If the problem is still there, please contact with us, we will help you find out and resolve the problem quickly.', $source),
        );
        $this->context->smarty->assign('hs_i18n', $this->i18n);
    }

    /**
     * combine an Ajax URL for the default controller of module.
     * @param string $action
     * @return string full Ajax Url
     */
    public function getTargetUrl($controller = '', $action = '', $ajax = true)
    {
        $params = array();
        $params['ajax'] = $ajax;
        $action = trim($action);
        if (!empty($action)) {
            $params['action'] = $action;
        }
        $query = array();
        $params['controller'] = $controller;
        $params['token'] = Tools::getAdminTokenLite($controller);
        foreach ($params as $key => $value) {
            $query[] = $key . '=' . $value;
        }
        return $this->getAdminUrlForPsVersion() . '?' . implode('&', $query);
    }
    
    /**
     * Get relative path to js files of module.
     *
     * @return string
     */
    public function getJsPath()
    {
        return $this->_path . self::PATH_JS;
    }

    /**
     * Get relative path to css files of module.
     *
     * @return string
     */
    public function getCssPath()
    {
        return $this->_path . self::PATH_CSS;
    }

    /**
     * Get relative path to images files of module.
     *
     * @return string
     */
    public function getImgPath()
    {
        return $this->_path . self::PATH_IMG;
    }

    /**
     * Form Config Methods.
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink($this->class_controller_admin_group));
    }

    protected function assignSmartyVariables()
    {
        $id_product = $this->isProductPage() ? $this->context->controller->getProduct()->id : 0;
        $link = new Link();
        $setting_buy_together = HsMaProductSetting::getBuyTogetherCurrentValue($id_product);
        $json_setting_buy_together = empty($setting_buy_together) ? Tools::jsonEncode($setting_buy_together) : Tools::jsonEncode($setting_buy_together[$id_product]);
        $this->context->smarty->assign(array(
            'accessory_alert_message' => Configuration::get('HSMA_ALERT_MESSAGE', $this->context->language->id),
            'msg_accessory_is_out_of_stock' => $this->i18n['accessory_is_out_of_stock'],
            'msg_out_of_stock' => $this->i18n['out_of_stock'],
            'msg_available_later' => $this->i18n['out_of_stock_but_backordering_is_allowed'],
            'msg_empty_form' => $this->i18n['cannot_submit_a_empty_form'],
            'utilize_block_cart_ajax' => (int) $this->isEnableBlockCartAjax(),
            'order_url' => Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 ? $link->getPageLink('order-opc') : $link->getPageLink('order'),
            'buy_main_accessory_together' => $json_setting_buy_together,
            'display_style' => Configuration::get('HSMA_DISPLAY_STYLE'),
            'is_show_icon_out_of_stock' => (int) Configuration::get('HSMA_SHOW_ICON_OUT_OF_STOCK'),
            'is_enabling_option_buy_to_gether' => (bool) Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER'),
            'ajaxRenderAccessories' => $link->getModuleLink($this->name, 'Accessories'),
            'hsma_format_currency' => $this->getFormatCurrency(),
            'hsma_price_display_precision' => $this->getDecimals(),
            'path_theme' => $this->isPrestashop17() ? '17/' : '',
        ));
    }
    protected function getJsTranslateText()
    {
        $js_translate_text = array();
        $js_translate_text['customize'] = $this->i18n['customize'];
        $js_translate_text['accessory_customization'] = $this->i18n['accessory_customization'];
        $js_translate_text['dont_forget_to_save_your_customization_to_be_able_to_add_to_cart'] = $this->i18n['dont_forget_to_save_your_customization_to_be_able_to_add_to_cart'];
        $js_translate_text['your_message_here'] = $this->i18n['your_message_here'];
        $js_translate_text['your_customization'] = $this->i18n['your_customization'];
        $js_translate_text['remove_image'] = $this->i18n['remove_image'];
        $js_translate_text['no_selected_file'] = $this->i18n['no_selected_file'];
        $js_translate_text['choose_file'] = $this->i18n['choose_file'];
        $js_translate_text['png_jpg_gif'] = $this->i18n['png_jpg_gif'];
        $js_translate_text['save_customization'] = $this->i18n['save_customization'];
        $js_translate_text['please_fill_the_required_custom_fields_to_complete_the_sale'] = $this->i18n['please_fill_the_required_custom_fields_to_complete_the_sale'];
        $js_translate_text['add_customization_data'] = $this->i18n['add_customization_data'];
        $js_translate_text['char_max'] = $this->i18n['char_max'];
        $js_translate_text['required_fields'] = $this->i18n['required_fields'];
        $js_translate_text['cancel'] = $this->i18n['cancel'];
        $js_translate_text['pack_content'] = $this->i18n['pack_content'];
        $js_translate_text['an_error_occurred_while_showing_the_list_of_products_in_the_package'] = $this->i18n['an_error_occurred_while_showing_the_list_of_products_in_the_package'];
        $js_translate_text['an_error_occurred_while_showing_the_accessory_customization'] = $this->i18n['an_error_occurred_while_showing_the_accessory_customization'];
        return $js_translate_text;
    }

    /**
     *
     * @return array
     */
    protected function preparePriceSpecifications()
    {
        $currency = $this->context->currency;
        /* New method since PS 1.7.6 */
        if (isset($this->context->currentLocale) && method_exists($this->context->currentLocale, 'getPriceSpecification')) {
            $price_specification = $this->context->currentLocale->getPriceSpecification($currency->iso_code);
            return $price_specification->toArray();
        }
        $formats = explode(';', $currency->format);
        if (count($formats) > 1) {
            $positive_pattern = $formats[0];
            $negative_pattern = $formats[1];
        } else {
            $positive_pattern = $currency->format;
            $negative_pattern = $currency->format;
        }
        return array(
            'positivePattern' => $positive_pattern,
            'negativePattern' => $negative_pattern
        );
    }
    
    protected function getFormatCurrency()
    {
        if ($this->isPrestashop176()) {
            $currency_format = $this->preparePriceSpecifications();
            $format = $currency_format['positivePattern'];
        } else {
            $format = $this->context->currency->format;
        }
        switch ($format) {
            case '#,##0.00 ¤':
                $format_currency = array(
                    'format_type' => 4,
                    'blank' => 1,
                );
                break;
            case '#,##0.00¤':
                $format_currency = array(
                    'format_type' => 4,
                    'blank' => 0,
                );
                break;
            case '# ##0,00 ¤':
            case '###0,00 ¤':
                $format_currency = array(
                    'format_type' => 2,
                    'blank' => 1,
                );
                break;
            case '# ##0,00¤':
            case '###0,00¤':
                $format_currency = array(
                    'format_type' => 2,
                    'blank' => 0,
                );
                break;
            case '¤ #,##0.00':
                $format_currency = array(
                    'format_type' => 1,
                    'blank' => 1,
                );
                break;
            case '¤#,##0.00':
                $format_currency = array(
                    'format_type' => 1,
                    'blank' => 0,
                );
                break;
            case '¤ #.##0,00':
                $format_currency = array(
                    'format_type' => 3,
                    'blank' => 1,
                );
                break;
            case '¤#.##0,00':
                $format_currency = array(
                    'format_type' => 3,
                    'blank' => 0,
                );
                break;
            case '¤ #\##0.00':
                $format_currency = array(
                    'format_type' => 5,
                    'blank' => 1,
                );
                // no break
            case '¤#\##0.00':
                $format_currency = array(
                    'format_type' => 5,
                    'blank' => 0,
                );
                break;
            default:
                $format_currency = array(
                    'format_type' => 1,
                    'blank' => 1,
                );
                break;
        }
        return $format_currency;
    }
    /**
     * HOOK_SHOPPING_CART_EXTRA.
     *
     * @param array $params see self::hookDisplayShoppingCartFooter()
     */
    public function hookDisplayShoppingCart(array $params = array())
    {
        return $this->hookDisplayShoppingCartFooter($params);
    }

    /**
     * HOOK_SHOPPING_CART.
     *
     * @param array $params output of Cart::getSummaryDetails()
     * <pre>
     * array(
     *  'delivery' => Address,
     *  'delivery_state' => ?,
     *  'invoice' => Address,
     *  'invoice_state' => ?,
     *  'formattedAddresses' => array,
     *  'products' => array,
     *  'gift_products' => array,
     *  'discounts' => array,
     *  'is_virtual_cart' => int (0/1)
     *  'total_discounts' => float,
     *  'total_discounts_tax_exc' => float,
     *  'total_wrapping' => float,
     *  'total_wrapping_tax_exc' => float,
     *  'total_shipping' => float,
     *  'total_shipping_tax_exc' => float,
     *  'total_products_wt' => float,
     *  'total_products' => float,
     *  'total_price' => float,
     *  'total_tax' => float,
     *  'total_price_without_tax' => float,
     *  'is_multi_address_delivery' => ?,
     *  'free_ship' => ?,
     *  'carrier' => Carrier,
     *  'cookie' => Cookie,
     *  'cart' => Cart
     * )
     */
    public function hookDisplayShoppingCartFooter(array $params = array())
    {
        return;
//        $id_products = $this->getIdProductsFromCart();
//        if (empty($id_products)) {
//            return;
//        }
//        $this->context->controller->addJS(_THEME_JS_DIR_.'product.js');
//        if (Tools::getValue('ajax')) {
//            $this->assignSmartyVariables();
//        }
//
//        return $this->renderBlockAccessories($id_products, false);
    }

    /**
     * @return array
     * <pre>
     *  array(
     *   int,
     *   int,
     *   ...
     *  )
     */
    protected function getIdProductsFromCart()
    {
        $products = $this->context->cart->getProducts();
        $id_products = array();
        foreach ($products as $product) {
            $id_products[] = $product['id_product'];
        }

        return $id_products;
    }

    /**
     * Get table prices of main product + accessories products.
     *
     * @param array $accessory
     * <pre>
     *  array(
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_quantity' => int,
     *   'required' => boolean,
     *   'position' => int,
     *   'link_rewrite' => varchar,
     *   'description_short' => text,
     *   'available_later' => boolean,
     *   'id_product' => int,
     *   'stock_available' => int,
     *   'out_of_stock' => int,
     *   'id_accessory_group_product' => int
     *   'id_product_attribute' => int,
     *   'id_image' => int,
     *   'name' => varchar,
     *   'combinations' => array(
     *                          'id_product_attribute' => array(
     *                                                      'id_product_attribute' => int,
     *                                                      'stock_available' => int,
     *                                                      'out_of_stock' => int,
     *                                                      'id_image' => int,
     *                                                      'combination' => varchar,
     *                                                      'image' => varchar,
     *                                                    ),
     *                                                    ......................
     *                     ),
     *   'is_available_when_out_of_stock' => int,
     *   'is_available_for_order' => int
     *   'is_available_buy_together' => int,
     *   'image' => varchar
     *  )
     *
     * @return array
     * <pre>
     *  array(
     *   'name' => varchar,
     *   'description_short' => text,
     *   'qty' => int,
     *   'avaiable_quantity' => int,
     *   'out_of_stock' => int,
     *   'is_available_when_out_of_stock' => int,
     *   'available_later' => varchar,
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_id_product_attribut' => int,
     *   'default_quantity' => int,
     *   'combinations' => array(
     *                      'id_product_attribute' => array(
     *                                                  'id_product_attribute' => int,
     *                                                  'stock_available' => int,
     *                                                  'out_of_stock' => int,
     *                                                  'id_image' => int,
     *                                                  'combination' => varchar,
     *                                                  'image' => varchar,
     *                                                ),
     *                                                ......................
     *                    )
     *  )
     */
    protected function formatAccessory(array $accessory = array())
    {
        $default_id_product_attribute = (int) Product::getDefaultAttribute($accessory['id_accessory'], self::DEFAULT_QTY);
        $formatted_accessory = array();
        $formatted_accessory['name'] = $accessory['name'];
        $formatted_accessory['description_short'] = $accessory['description_short'];
        $formatted_accessory['qty'] = (int) $accessory['default_quantity'];
        $formatted_accessory['avaiable_quantity'] = (int) $accessory['stock_available'];
        $formatted_accessory['out_of_stock'] = HsMaProduct::isAvailableWhenOutOfStock($accessory['out_of_stock']);
        $formatted_accessory['is_available_when_out_of_stock'] = (HsMaProduct::isAvailableWhenOutOfStock($accessory['out_of_stock']) && $accessory['stock_available'] < $accessory['default_quantity']) ? 1 : 0;
        $formatted_accessory['available_later'] = $this->getMessageAvailableLater($accessory['available_later']);
        $formatted_accessory['id_accessory_group'] = (int) $accessory['id_accessory_group'];
        $formatted_accessory['id_accessory'] = (int) $accessory['id_accessory'];
        $formatted_accessory['default_id_product_attribute'] = (int) $accessory['id_product_attribute'] ? $accessory['id_product_attribute'] : $default_id_product_attribute;
        $formatted_accessory['default_quantity'] = (int) $accessory['default_quantity'] > 0 ? (int) $accessory['default_quantity'] : (int) self::DEFAULT_QTY;
        $formatted_accessory['min_quantity'] = (int) $accessory['min_quantity'];
        $formatted_accessory['customizable'] = $accessory['customizable'];
        $formatted_accessory['customization'] = $accessory['customization'];
        
        $array_id_product_attributes = array();
        if (empty($accessory['combinations'])) {
            $accessory['combinations'][] = $this->createDefaultCombination($accessory);
        } else {
            foreach ($accessory['combinations'] as $combination) {
                if (!empty($combination['id_product_attribute'])) {
                    $array_id_product_attributes[] = $combination['id_product_attribute'];
                }
            }
            if (!empty($array_id_product_attributes)) {
                $valid_id_product_attributes = $accessory['id_product_attribute'] ? array($accessory['id_product_attribute']) : $array_id_product_attributes;
                $valid_combinations = array_intersect_key($accessory['combinations'], array_flip($valid_id_product_attributes));
                $accessory['combinations'] = $valid_combinations;
            }
        }
        $formatted_accessory['combinations'] = $this->formatCombinations($accessory);
        return $formatted_accessory;
    }

    /**
     * In case an accessory don't have any combination => create a default
     * @param array
     * <pre>
     *  array(
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_quantity' => int,
     *   'required' => boolean,
     *   'position' => int,
     *   'link_rewrite' => varchar,
     *   'description_short' => text,
     *   'available_later' => boolean,
     *   'id_product' => int,
     *   'stock_available' => int,
     *   'out_of_stock' => int,
     *   'id_accessory_group_product' => int
     *   'id_product_attribute' => int,
     *   'id_image' => int,
     *   'name' => varchar,
     *   'combinations' => array(
     *                          'id_product_attribute' => array(
     *                                                      'id_product_attribute' => int,
     *                                                      'stock_available' => int,
     *                                                      'out_of_stock' => int,
     *                                                      'id_image' => int,
     *                                                      'combination' => varchar,
     *                                                      'image' => varchar,
     *                                                    ),
     *                                                    ......................
     *                     ),
     *   'is_available_when_out_of_stock' => int,
     *   'is_available_for_order' => int
     *   'is_available_buy_together' => int,
     *   'image' => varchar
     *  )
     * @return array a default combination
     * <pre>
     *  array(
     *      'id_product_attribute' => int,
     *      'stock_available' => int,
     *      'out_of_stock' => int,
     *      'id_image' => int,
     *      'combination' => varchar,
     *      'image' => varchar
     *  )
     */
    protected function createDefaultCombination(array $accessory = array())
    {
        return array(
            'id_product_attribute' => $accessory['id_product_attribute'],
            'stock_available' => $accessory['stock_available'],
            'out_of_stock' => $accessory['out_of_stock'],
            'id_image' => $accessory['id_image'],
            'combination' => $accessory['name'],
            'image' => $accessory['image'],
            'name' => ''
        );
    }

    /**
     * format product.
     *
     * @param Object $product Product object
     *
     * @return array
     * <pre>
     *  array(
     *   'name' => varchar,
     *   'description_short' => text,
     *   'qty' => int,
     *   'avaiable_quantity' => int,
     *   'out_of_stock' => int,
     *   'is_available_when_out_of_stock' => int,
     *   'available_later' => varchar,
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_id_product_attribut' => int,
     *   'default_quantity' => int,
     *   'combinations' => array(
     *                      'id_product_attribute' => array(
     *                                                  'id_product_attribute' => int,
     *                                                  'stock_available' => int,
     *                                                  'out_of_stock' => int,
     *                                                  'id_image' => int,
     *                                                  'name' => varchar,
     *                                                  'image' => varchar,
     *                                                ),
     *                                                ......................
     *                   )
     *  )
     */
    protected function formatProduct(Product $product)
    {
        $default_id_product_attribute = (int) Product::getDefaultAttribute($product->id, self::DEFAULT_QTY);
        $product->id_product_attribute = $default_id_product_attribute;
        $formatted_product = array();
        $formatted_product['id_product'] = $product->id;
        $formatted_product['link_rewrite'] = $product->link_rewrite;
        $formatted_product['name'] = $product->name;
        $formatted_product['qty'] = self::DEFAULT_QTY;
        $formatted_product['default_quantity'] = $formatted_product['qty'];
        $formatted_product['out_of_stock'] = Product::isAvailableWhenOutOfStock($product->out_of_stock);
        $formatted_product['available_quantity'] = (int) $product->quantity;
        $formatted_product['description_short'] = $product->description_short;
        $formatted_product['default_id_product_attribute'] = $product->id_product_attribute;
        $combinations = HsMaProduct::getCombinations((int) $product->id, (int) $this->context->shop->id);
        if (!empty($combinations)) {
            $formatted_product['combinations'] = $combinations;
        } else {
            $formatted_product['id_product_attribute'] = $formatted_product['default_id_product_attribute'];
            $formatted_product['combinations'][] = $this->createDefaultProductCombination($formatted_product);
        }
        $formatted_product['combinations'] = $this->formatMainProductCombinations($formatted_product);
        return $formatted_product;
    }
    
    protected function createDefaultProductCombination(array $product = array())
    {
        return array(
            'id_product_attribute' => $product['id_product_attribute'],
            'out_of_stock' => $product['out_of_stock'],
            'combination' => $product['name'],
            'name' => $product['name']
        );
    }
    
    /**
     * format main product combination the same format of accessories combinations
     * @param array $product
     * @return array
     */
    protected function formatMainProductCombinations(array $product)
    {
        $id_customer = $this->getIdCustomer();
        $formated_combinations = array();
        foreach ($product['combinations'] as $id_product_attribute => $combination) {
            $price = HsMaProduct::getPriceStatic($product['id_product'], $this->isUsetax(), $combination['id_product_attribute'], $this->getDecimals());
            $formated_combinations[$id_product_attribute] = array(
                'price' => $price,
                'name' => $combination['name'],
                'specific_prices' => HsMaSpecificPrice::getSpecificPrices($product['id_product'], $id_customer, $this->getIdGroup($id_customer), $this->getIdCountry($id_customer), $this->getIdCurrency(), $this->getIdShop(), !$this->isPrestashop16(), $combination['id_product_attribute']),
                //'avaiable_quantity' => (int) $combination['stock_available'],
                'out_of_stock' => HsMaProduct::isAvailableWhenOutOfStock($combination['out_of_stock']),
            );
        }
        return $formated_combinations;
    }
    
    /**
     * Format combinations.
     *
     * @param array $accessory
     * <pre>
     *  array(
     *   'id_accessory_group' => int,
     *   'id_accessory' => int,
     *   'default_quantity' => int,
     *   'required' => boolean,
     *   'position' => int,
     *   'link_rewrite' => varchar,
     *   'description_short' => text,
     *   'available_later' => boolean,
     *   'id_product' => int,
     *   'stock_available' => int,
     *   'out_of_stock' => int,
     *   'id_accessory_group_product' => int
     *   'id_product_attribute' => int,
     *   'id_image' => int,
     *   'name' => varchar,
     *   'combinations' => array(
     *                      [id_product_attribute] => array(
     *                          'id_product_attribute' => array(
     *                          'id_product_attribute' => int,
     *                          'stock_available' => int,
     *                          'out_of_stock' => int,
     *                          'id_image' => int,
     *                          'combination' => varchar,
     *                          'image' => varchar,
     *                          ),
     *                      [id_product_attribute] => array( )
     *                      ......................
     *                     ),
     *   'is_available_when_out_of_stock' => int,
     *   'is_available_for_order' => int
     *   'is_available_buy_together' => int,
     *   'image' => varchar
     *  )
     * @return array
     * <pre>
     * array(
     *      [id_product_attribute] => array(
     *          'price' => int,
     *          'final_price' => int,
     *          'is_cart_rule' => boolean,
     *          'image_fancybox' => varchar,
     *          'image_default' => varchar,
     *          'name' => varchar,
     *          'avaiable_quantity' => varchar,
     *          'out_of_stock' => varchar,
     *          'specific_prices' => Array(),
     *      ),
     *     [id_product_attribute] => array( )
     *     ......................
     * ),
     */
    protected function formatCombinations(array $accessory)
    {
        $id_customer = $this->getIdCustomer();
        $is_cart_rule = !empty($accessory['cart_rule']) ? true : false;
        $formated_combinations = array();
        foreach ($accessory['combinations'] as $id_product_attribute => $combination) {
            $price = HsMaProduct::getPriceStatic($accessory['id_accessory'], $this->isUsetax(), $combination['id_product_attribute']);
            $final_price = HsAccessoriesGroupAbstract::getFinalPrice($price, $accessory['cart_rule']);
            $id_attributes = isset($combination['id_attributes']) ? $combination['id_attributes'] : 0;
            $formated_combinations[$id_product_attribute] = array(
                'price' => $price,
                'final_price' => $final_price,
                'is_cart_rule' => $is_cart_rule,
                'image_fancybox' => HsMaLink::getProductImageLink($accessory['link_rewrite'], $combination['id_image'], Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX')),
                'image_default' => $combination['image'],
                'name' => $combination['name'],
                'id_attributes' => $id_attributes,
                'specific_prices' => HsMaSpecificPrice::getSpecificPrices($accessory['id_accessory'], $id_customer, $this->getIdGroup($id_customer), $this->getIdCountry($id_customer), $this->getIdCurrency(), $this->getIdShop(), !$this->isPrestashop16(), $combination['id_product_attribute']),
                'avaiable_quantity' => (int) $combination['stock_available'],
                'out_of_stock' => HsMaProduct::isAvailableWhenOutOfStock($combination['out_of_stock']),
                'is_stock_available' => (int) $this->isStockAvailable((int) $accessory['default_quantity'], $combination),
                'is_available_when_out_of_stock' => (HsMaProduct::isAvailableWhenOutOfStock($combination['out_of_stock']) && $combination['stock_available'] < $accessory['default_quantity']) ? 1 : 0
            );
        }
        return $formated_combinations;
    }

    /**
     * Checking product is out of stock.
     * @param int $quantity
     * @param array $combination
     * @return boolean
     */
    protected function isStockAvailable($quantity, $combination)
    {
        $flag = false;
        if (!empty($combination)) {
            if (Product::isAvailableWhenOutOfStock($combination['out_of_stock']) || (!Product::isAvailableWhenOutOfStock($combination['out_of_stock']) && $combination['stock_available'] >= (int) $quantity)) {
                $flag = true;
            }
        }
        return $flag;
    }
    
    /**
     * Get ID customer.
     *
     * @return int
     */
    protected function getIdCustomer()
    {
        return ($this->context->customer->isLogged()) ? (int) $this->context->customer->id : 0;
    }

    /**
     * Get ID Group.
     *
     * @param int $id_customer
     *
     * @return int
     */
    protected function getIdGroup($id_customer)
    {
        return ($id_customer ? Customer::getDefaultGroupId((int) $id_customer) : (int) Group::getCurrent()->id);
    }

    /**
     * Get ID Country.
     *
     * @param int $id_customer
     *
     * @return int
     */
    protected function getIdCountry($id_customer)
    {
        return ($id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT'));
    }

    /**
     * Get ID shop.
     *
     * @return int
     */
    protected function getIdShop()
    {
        return (int) $this->context->shop->id;
    }

    protected function getIdCurrency()
    {
        return (int) $this->context->currency->id;
    }

    /**
     * Check if the system used tax or not.
     *
     * @return bool
     */
    protected function isUsetax()
    {
        return Product::getTaxCalculationMethod($this->context->customer->id) ? false : true;
    }

    /**
     * Get current decimal.
     *
     * @return int
     */
    protected function getDecimals()
    {
        if ($this->isPrestashop176()) {
            $decimals = (int) $this->context->currency->precision;
        } else {
            $decimals = (int) $this->context->currency->decimals * _PS_PRICE_DISPLAY_PRECISION_;
        }
        return $decimals;
    }

    /**
     * Render the main block of accessories based on id_products.
     *
     * @param array $id_products
     * @param boolean $is_product_page
     * @return html
     */
    protected function renderBlockAccessories($id_products, $is_product_page = true)
    {
        $id_groups = HsAccessoriesGroupAbstract::getIdGroups($this->context->language->id, true);
        $include_out_of_stock = Configuration::get('HSMA_SHOW_ACCESSORIES_OFS');
        $accessories_groups = HsAccessoriesGroupAbstract::getAccessoriesByGroups($id_groups, $id_products, true, $this->context->language->id, $include_out_of_stock, $is_product_page);
        $accessories_table_price = array();
        $currency_decimals = $this->getDecimals();
        $use_tax = $this->isUsetax();
        $random_main_product_id = $this->getRandomId();
        if ($is_product_page) {
            $product = $this->context->controller->getProduct();
            $accessories_table_price[$random_main_product_id] = $this->formatProduct($product);
        }
        $id_products_buy_together = array();
        foreach ($accessories_groups as &$accessories_group) {
            foreach ($accessories_group as &$accessory) {
                $product = new Product((int) $accessory['id_accessory'], false, (int) $this->context->language->id);
                $random_product_accessories_id = $this->getRandomId();
                if (!Validate::isLoadedObject($product)) {
                    unset($accessory);
                    continue;
                }
                /* customization*/
                $accessory['customization'] = $product->customizable ? $product->getCustomizationFields($this->context->language->id) : false;
                $accessory['customizable'] = $product->customizable;
                $accessory['is_package'] = $this->isProductPackage($product);
                $accessory = $this->getCustomizationData($accessory);
                /*End of customization*/
                if ($accessory['is_available_buy_together']) {
                    $id_products_buy_together[$accessory['id_accessory_group']] = $accessory['id_accessory'];
                }
                if ($is_product_page) {
                    $accessories_table_price[$random_product_accessories_id] = $this->formatAccessory($accessory);
                }
                //@todo: Fix the price different with group customer
                $default_id_product_attribute = (int) $accessory['id_product_attribute'];
                $price = HsMaProduct::getPriceStatic($accessory['id_accessory'], $use_tax, $default_id_product_attribute, $currency_decimals);
                $accessory['price'] = $price;
                $accessory['random_product_accessories_id'] = $random_product_accessories_id;
                $accessory['default_id_product_attribute'] = $default_id_product_attribute;
                if ($this->isPrestashop17()) {
                    $accessory['link'] = $this->context->link->getProductLink($product, $product->link_rewrite, $product->category, $product->ean13, (int) $this->context->language->id, (int) $this->context->shop->id, $product->cache_default_attribute);
                } else {
                    $accessory['link'] = $this->context->link->getProductLink($product);
                }
                $accessories_table_price[$random_product_accessories_id]['link'] = $accessory['link'];
                $accessories_table_price[$random_product_accessories_id]['is_package'] = $accessory['is_package'];
                
                if (!empty($accessory['customization'])) {
                    $accessories_table_price[$random_product_accessories_id]['customizations'] = $accessory['customizations'];
                    $accessories_table_price[$random_product_accessories_id]['id_customization'] = $accessory['id_customization'];
                    $accessories_table_price[$random_product_accessories_id]['is_customizable'] = $accessory['is_customizable'];
                    $accessories_table_price[$random_product_accessories_id]['is_enough_customization'] = $accessory['is_enough_customization'];
                }
                $accessory['available_later'] = $this->getMessageAvailableLater($accessory['available_later']);
            }
        }
        $setting_buy_together = HsMaProductSetting::getBuyTogetherCurrentValue($id_products[0]);
        $this->context->smarty->assign(array(
            'accessory_configuration_keys' => Configuration::getMultiple(array_keys($this->configuration_keys)),
            'accessory_block_title' => Configuration::get('HSMA_TITLE', $this->context->language->id),
            'accessory_image_type' => Configuration::get('HSMA_IMAGE_TYPE'),
            'change_main_price' => Configuration::get('HSMA_CHANGE_MAIN_PRICE'),
            'image_size_fancybox' => Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX'),
            'show_table_price' => Configuration::get('HSMA_SHOW_PRICE_TABLE'),
            'show_combination' => Configuration::get('HSMA_SHOW_COMBINATION'),
            'sync_accessory_quantity' => (int) $this->getOptionAcessoryQuantitySetting(),
            'accessory_groups' => HsAccessoriesGroupAbstract::getGroups($this->context->language->id, true),
            'accessories_table_price' => Tools::jsonEncode($accessories_table_price),
            'js_translate_text' => Tools::jsonEncode($this->getJsTranslateText()),
            'random_main_product_id' => $random_main_product_id,
            'sub_total' => $this->i18n['sub_total'],
            'accessories_groups' => $accessories_groups,
            'static_token' => Tools::getToken(false),
            'is_enabling_cart_ajax' => (int) $this->isEnableBlockCartAjax(),
            'main_product_minimal_quantity' => $is_product_page ? $product->minimal_quantity : 1,
            'buy_main_accessory_together_group' => $setting_buy_together[$id_products[0]],
            'is_product_page' => $is_product_page,
            'isPrestashop17' => $this->isPrestashop17(),
            'id_products_buy_together' => $id_products_buy_together,
            'path_theme' => $this->isPrestashop17() ? '17/' : '',
        ));
        return $this->display($this->name . '.php', 'multi_accessories.tpl');
    }
    
    /**
     *
     * @param object $product
     * @return int
     */
    public function isProductPackage($product)
    {
        return $product->getType() == Product::PTYPE_PACK ? 1 : 0;
    }

    public function getCustomizationData(array $product)
    {
        if ($product['customizable']) {
            $customizationData = array(
                'fields' => array(),
            );
            $customized_data = array();
            $already_customized = $this->context->cart->getProductCustomization((int) $product['id_product'], null, true);
            $id_customization = 0;
            foreach ($already_customized as $customization) {
                $id_customization = $customization['id_customization'];
                $customized_data[$customization['index']] = $customization;
            }
            $customization_fields = $product['customization'];
            $is_enough_customization = true;
            if (is_array($customization_fields)) {
                $field = array();
                foreach ($customization_fields as $customization_field) {
                    $key = $customization_field['id_customization_field'];
                    $field['label'] = $customization_field['name'];
                    $field['id_customization_field'] = $customization_field['id_customization_field'];
                    $field['required'] = $customization_field['required'];
                    switch ($customization_field['type']) {
                        case Product::CUSTOMIZE_FILE:
                            $field['type'] = 'image';
                            $field['image'] = null;
                            $field['input_name'] = 'file' . $customization_field['id_customization_field'];
                            break;
                        case Product::CUSTOMIZE_TEXTFIELD:
                            $field['type'] = 'text';
                            $field['text'] = '';
                            $field['input_name'] = 'textField' . $customization_field['id_customization_field'];
                            break;
                        default:
                            $field['type'] = null;
                    }
                    if (array_key_exists($key, $customized_data)) {
                        $data = $customized_data[$key];
                        $field['is_customized'] = true;
                        switch ($customization_field['type']) {
                            case Product::CUSTOMIZE_FILE:
                                $field['image'] = HsMaLink::getCustomizationImage($this->context->link, $data['value']);
                                $field['remove_image_url'] = $this->context->link->getProductDeletePictureLink($product, $customization_field['id_customization_field']);
                                break;
                            case Product::CUSTOMIZE_TEXTFIELD:
                                $field['text'] = $data['value'];
                                break;
                        }
                    } else {
                        $field['is_customized'] = false;
                    }
                    if ($field['required'] && !$field['is_customized']) {
                        $is_enough_customization = false;
                    }
                    $customizationData['fields'][] = $field;
                }
            }
            $product['customizations'] = $customizationData;
            $product['id_customization'] = $id_customization;
            $product['is_customizable'] = true;
            $product['is_enough_customization'] = (int) $is_enough_customization;
        } else {
            $product['customizations'] = array(
                'fields' => array(),
            );
            $product['id_customization'] = 0;
            $product['is_customizable'] = false;
        }
        return $product;
    }

    /**
     * Display accessories in groups within product page.
     *
     * @return HTML string
     */
    public function hookDisplayRightColumnProduct()
    {
        $id_product = Tools::getValue('id_product', false);
        if (!empty($id_product)) {
            if (HsAccessoriesGroupAbstract::haveAccessories(array($id_product), (int) $this->context->language->id)) {
                return $this->renderBlockAccessories(array($id_product), true);
            }
        }
    }

    /**
     * Display accessories in groups within product page.
     *
     * @return HTML string
     */
    public function hookDisplayLeftColumnProduct()
    {
        return $this->hookDisplayRightColumnProduct();
    }
    
    /**
     * Display accessories in groups within right column in product page.
     *
     * @return HTML string
     */
    public function hookDisplayRightColumn()
    {
        if ($this->context->controller instanceof ProductController) {
            return $this->hookDisplayRightColumnProduct();
        }
    }

    /**
     * Display accessories in groups within left column in product page.
     *
     * @return HTML string
     */
    public function hookDisplayLeftColumn()
    {
        if ($this->context->controller instanceof ProductController) {
            return $this->hookDisplayRightColumnProduct();
        }
    }

    /**
     * A custom hook so that we can place the Multi Accessories block anywhere on product page's template <br/>
     * For example:<br/>
     * {hook h="displayMultiAccessoriesProduct"}.
     *
     * @return HTML
     */
    public function hookDisplayMultiAccessoriesProduct()
    {
        return $this->hookDisplayRightColumnProduct();
    }

    /**
     * Display accessories in groups within block product button in product page.
     *
     * @return HTML string
     */
    public function hookDisplayProductButtons()
    {
        return $this->hookDisplayRightColumnProduct();
    }
    
    public function hookDisplayReassurance()
    {
        return $this->hookDisplayRightColumnProduct();
    }

    /**
     * show a tab in product tab page.
     *
     * @return HTML string
     */
    public function hookDisplayProductTab()
    {
        if ($this->isPrestashop15()) {
            $id_product = (int) Tools::getValue('id_product', 0);
            if (HsAccessoriesGroupAbstract::haveAccessories(array($id_product), (int) $this->context->language->id)) {
                $this->context->smarty->assign(array(
                    'tab_name' => Configuration::get('HSMA_TITLE', $this->context->language->id)
                ));

                return $this->display($this->name . '.php', 'product_tab_15.tpl');
            }
        }
    }

    /**
     * show all accessories in a tab of product page.
     *
     * @return HTML string
     */
    public function hookDisplayProductTabContent()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        if (HsAccessoriesGroupAbstract::haveAccessories(array($id_product), (int) $this->context->language->id)) {
            $this->context->smarty->assign(array(
                'tab_name' => Configuration::get('HSMA_TITLE', $this->context->language->id),
                'is_prestashop_16' => $this->isPrestashop16()
            ));
        }
        return $this->hookDisplayRightColumnProduct();
    }

    /**
     * Insert accessories into shopping cart <br />
     * Update the quantity of the main product <br />.
     *
     * @return bool
     */
    public function hookActionCartSave()
    {
        if (!Validate::isLoadedObject($this->context->cart)) {
            return;
        }
        
        /* Fix error +1 when delete an accessory with setting delele main product & accessory is disabled*/
        if (Tools::getValue('delete')) {
            return;
        }
        $id_product = (int) Tools::getValue('id_product');
        $id_accesories_attributes = array();
        $custom_qty = array();
        $qty = 1;
        $group = Tools::getValue('group');
        if ($this->isPrestashop17() && !empty($group)) {
            $id_product_customization = (int) Tools::getValue('id_customization', 0);
            $id_main_product_attribute = (int) Product::getIdProductAttributesByIdAttributes($id_product, Tools::getValue('group'));
        } else {
            $id_main_product_attribute = Tools::getValue('ipa', 0);
            $main_product = new Product($id_product, false, $this->context->language->id, $this->context->shop->id);
            if ($main_product->customizable) {
                $customization_datas = $this->context->cart->getProductCustomization($main_product->id, null, true);
            }
            $id_product_customization = empty($customization_datas) ? null : $customization_datas[0]['id_customization'];
        }
        if (!Tools::getValue('ajax') && !$this->isEnableBlockCartAjax() && $id_product && !$this->isPrestashop17()) {
            $accessories = HsAccessoriesGroupProductAbstract::getAccessoriesByIdProducts(array($id_product));
            if (!empty($accessories)) {
                $this->accessories = explode(',', $accessories[$id_product]['id_accessories']);
                $custom_qty = explode(',', $accessories[$id_product]['custom_qty']);
                $id_accesories_attributes = explode(',', $accessories[$id_product]['id_accessories_combination']);
                $id_customizations = explode(',', $accessories[$id_product]['id_customizations']);
            }
        } else {
            $qty = (int) Tools::getValue('qty', 1);

            if (Tools::getValue('id_accesories_attributes')) {
                $id_accesories_attributes = explode(',', Tools::getValue('id_accesories_attributes'));
            }

            if (Tools::getValue('id_accessories')) {
                $this->accessories = explode(',', Tools::getValue('id_accessories'));
            }

            if (Tools::getValue('custom_qty')) {
                $custom_qty = explode(',', Tools::getValue('custom_qty'));
            }
            if (Tools::getValue('id_customizations')) {
                $id_customizations = explode(',', Tools::getValue('id_customizations'));
            }
        }
        if (self::$has_cart_been_added) {
            self::$has_cart_been_added = false;

            return;
        } else {
            if (!self::$is_executed) {
                self::$is_executed = true;
                if (empty($this->accessories) && $this->isSummaryPage()) {
                    if ($this->getOptionAcessoryQuantitySetting() == 2) {
                        $this->updateQuantityOfPackage();
                    } else {
                        if (Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER')) {
                            $this->syncQuantity();
                        }
                    }
                    return;
                }

                // start process insert accessories to cart
                $i = 0;
                $accessory_qty = array();
                foreach ($this->accessories as $id_accessory) {
                    if ($id_accessory == $id_product) {
                        continue;
                    }
                    $product = new Product($id_accessory, true, $this->context->language->id);
                    if (!empty($id_accesories_attributes[$i])) {
                        $id_product_attribute = (int) $id_accesories_attributes[$i];
                    } elseif ($product->hasAttributes()) {
                        $id_product_attribute = Product::getDefaultAttribute($product->id);
                    } else {
                        $id_product_attribute = 0;
                    }

                    if (empty($custom_qty)) {
                        $accessory_qty[$i] = $qty;
                    } else {
                        $accessory_qty = $custom_qty;
                    }
                    if (!empty($id_customizations)) {
                        $id_accessory_customization = (int) $id_customizations[$i];
                    } else {
                        $id_accessory_customization = false;
                    }

                    if ($product->hasAttributes()) {
                        if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($id_product_attribute, $accessory_qty[$i])) {
                            $accessory_qty[$i] = Product::getQuantity($id_accessory);
                        }
                    } elseif (!$product->checkQty($accessory_qty[$i])) {
                        $accessory_qty[$i] = Product::getQuantity($id_accessory);
                    }

                    if ($accessory_qty[$i] > 0) {
                        $is_added = true;
                        if ($this->isPrestashop17()) {
                            $is_added = $this->context->cart->updateQty($accessory_qty[$i], $id_accessory, $id_product_attribute, $id_accessory_customization);
                        } else {
                            /* PS version < 1.6 doesn't support the column id_customization on the table cart_product */
                            $is_added = $this->context->cart->updateQty($accessory_qty[$i], $id_accessory, $id_product_attribute);
                        }
                        if ($is_added) {
                            // add table accessory cart product
                            $cart_product = new HsAccessoryCartProductAbstract($this->context->cart->id, $id_product, $id_main_product_attribute, $id_accessory, $id_product_attribute, $id_accessory_customization, $id_product_customization);
                            if (Validate::isLoadedObject($cart_product)) {
                                $cart_product->quantity = $cart_product->quantity + (int) $accessory_qty[$i];
                                $cart_product->prev_quantity = $cart_product->prev_quantity + (int) $accessory_qty[$i];
                                $cart_product->update();
                            } else {
                                $cart_product->id_cart = (int) $this->context->cart->id;
                                $cart_product->id_product = (int) $id_product;
                                $cart_product->id_product_attribute = $id_main_product_attribute > 0 ? $id_main_product_attribute : (int) Product::getDefaultAttribute($id_product);
                                $cart_product->id_product_customization = (int) $id_product_customization;
                                $cart_product->id_accessory = (int) $id_accessory;
                                $cart_product->id_accessory_attribute = (int) $id_product_attribute;
                                $cart_product->id_customization = (int) $id_accessory_customization;
                                $cart_product->quantity = (int) $accessory_qty[$i];
                                $cart_product->prev_quantity = (int) $accessory_qty[$i];
                                $cart_product->add();
                            }
                        }
                    }
                    ++$i;
                }
            }
        }
    }
    
    /**
     * Check the action change cart quantity in the cart summary page.
     * @return boolean
     */
    protected function isSummaryPage()
    {
        $is_summary_page = false;
        if ($this->isPrestashop17()) {
            if (Tools::getIsset('op') && Tools::getIsset('update')) {
                $is_summary_page = true;
            }
        } else {
            if (Tools::getIsset('summary')) {
                $is_summary_page = true;
            }
        }
        return $is_summary_page;
    }

    protected function updateQuantityOfPackage()
    {
        $operation = Tools::getValue('op', 'up');
        if ($operation === 'down') {
            $id_product = (int) Tools::getValue('id_product');
            $id_main_product_attribute = (int) Tools::getValue('ipa', Tools::getValue('id_product_attribute'));
            $id_customization = (int) Tools::getValue('id_customization', 0);
            $id_address_delivery = (int) Tools::getValue('id_address_delivery', 0);
            if (!$id_main_product_attribute) {
                $id_main_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
            }
            $products = $this->context->cart->getProducts();
            $current_product_quantity_incart = 0;
            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($product['id_product'] == $id_product && $product['id_product_attribute'] == $id_main_product_attribute) {
                        $current_product_quantity_incart = (int) $product['cart_quantity'];
                        break;
                    }
                }
                if ($current_product_quantity_incart > 0) {
                    $total_product_incart = HsAccessoryCartProductAbstract::getTotalProductInCart($this->context->cart->id, $id_product, $id_main_product_attribute);
                    if ($total_product_incart > 0 && $current_product_quantity_incart < $total_product_incart) {
                        $qty = (int) Tools::getValue('qty', 1);
                        $product_accessories = HsAccessoryCartProductAbstract::getProductByIdCartAccessory($this->context->cart->id, $id_product, $id_main_product_attribute, $id_customization);
                        if (!empty($product_accessories)) {
                            foreach ($product_accessories as $product_accessory) {
                                $accessory_cart_product = new HsAccessoryCartProductAbstract($this->context->cart->id, $product_accessory['id_product'], $product_accessory['id_product_attribute'], $product_accessory['id_accessory'], $product_accessory['id_accessory_attribute'], $product_accessory['id_customization'], $product_accessory['id_product_customization']);
                                if (Validate::isLoadedObject($accessory_cart_product)) {
                                    if ($accessory_cart_product->quantity == $qty) {
                                        if ($this->isPrestashop17()) {
                                            $data = array(
                                                'id_cart' => (int) $this->context->cart->id,
                                                'id_product' => $id_product,
                                                'id_product_attribute' => $id_main_product_attribute,
                                                'customization_id' => $id_customization,
                                                'id_address_delivery' => $id_address_delivery
                                            );
                                            if ($this->isPrestashop1711()) {
                                                Hook::exec('actionObjectProductInCartDeleteAfter', $data);
                                            } else {
                                                Hook::exec('actionDeleteProductInCartAfter', $data);
                                            }
                                        }
                                    } else {
                                        $accessory_cart_product->quantity = $accessory_cart_product->quantity - (int) $qty;
                                        $accessory_cart_product->prev_quantity = $accessory_cart_product->prev_quantity - (int) $qty;
                                        $accessory_cart_product->update();
                                    }
                                }
                                break;
                            }
                        }
                    }
                } elseif ($this->isPrestashop17()) {
                    $data = array(
                        'id_cart' => (int)$this->context->cart->id,
                        'id_product' => $id_product,
                        'id_product_attribute' => $id_main_product_attribute,
                        'customization_id' => $id_customization,
                        'id_address_delivery' => $id_address_delivery
                    );
                    if ($this->isPrestashop1711()) {
                        Hook::exec('actionObjectProductInCartDeleteAfter', $data);
                    } else {
                        Hook::exec('actionDeleteProductInCartAfter', $data);
                    }
                }
            }
        }
    }
    
    /**
     * This is the new feature from version 4.1.0
     * Sync quantity of main product & accessories if the customer change it at the order summary page
     */
    protected function syncQuantity()
    {
        $operation = Tools::getValue('op', 'up');
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('ipa', Tools::getValue('id_product_attribute'));
        $qty_to_check = (int) Tools::getValue('qty', 1);
        $id_customization = (int) Tools::getValue('id_customization', 0);
        $id_cart = (int)$this->context->cart->id;
        $success = true;
        $main_product = HsAccessoryCartProductAbstract::getIdMainProduct($id_cart, $id_product, $id_product_attribute, $id_customization);
        if (!empty($main_product)) {
            /*Update main product quantity*/
            $id_product = $main_product[0]['id_product'];
            $id_product_attribute = $main_product[0]['id_product_attribute'];
            $id_customization = $main_product[0]['id_product_customization'];
            $cart_products = $this->context->cart->getProducts(true);
            if (!empty($cart_products)) {
                foreach ($cart_products as $cart_product) {
                    if ($cart_product['id_product'] == $id_product && $cart_product['id_product_attribute'] == $id_product_attribute) {
                        $product = new Product($id_product, false, $this->context->language->id);
                        if ($product->hasAttributes()) {
                            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($id_product_attribute, $qty_to_check)) {
                                $qty_to_check = Product::getQuantity($id_product);
                            }
                        } elseif (!$product->checkQty($qty_to_check)) {
                            $qty_to_check = Product::getQuantity($id_product);
                        }
                        $minimal_quantity = $id_product_attribute > 0 ? (int)Attribute::getAttributeMinimalQty($id_product_attribute) : (int)$product->minimal_quantity;
                        if ($cart_product['cart_quantity'] <= $minimal_quantity && $operation == 'down') {
                            // Revert accessory quantity
                            $this->context->cart->updateQty((int) Tools::getValue('qty', 1), (int) Tools::getValue('id_product'), (int) Tools::getValue('ipa', 0), (int) Tools::getValue('id_customization', 0), 'up');
                            exit(Tools::jsonEncode(array(
                                    'hasError' => true,
                                    'errors' => array(sprintf('You must add %d minimum quantity', $minimal_quantity)),
                            )));
                        }
                        if ($qty_to_check > 0) {
                            $success = $this->context->cart->updateQty($qty_to_check, $id_product, $id_product_attribute, $id_customization, $operation);
                        }
                        break;
                    }
                }
            }
        }
        /* Fix for case main product is out of stock, we only add the current accessory to cart.(without main product + other accessories) */
        if (!$success) {
            return;
        }
        /* Main product quanity has been updated */
        /* Update all accessories quantity with rule: new quantity = main_product_quantity * default quantity */
        $cart_accessories = HsAccessoryCartProductAbstract::getAccessoriesInCart($id_cart, $id_product, $id_product_attribute, $id_customization);
        $reload_cart_products = $this->context->cart->getProducts(true);
        $current_main_product_quantity = 0;
        if (!empty($reload_cart_products)) {
            foreach ($reload_cart_products as $cart_product) {
                if ($cart_product['id_product'] == $id_product && $cart_product['id_product_attribute'] == $id_product_attribute) {
                    $current_main_product_quantity = (int) $cart_product['cart_quantity'];
                }
            }
        }
        if (!empty($cart_accessories) && !empty($reload_cart_products)) {
            foreach ($cart_accessories as $cart_accessory) {
                foreach ($reload_cart_products as $cart_product) {
                    if ($cart_accessory['id_accessory'] == $cart_product['id_product'] && $cart_accessory['id_accessory_attribute'] == $cart_product['id_product_attribute']) {
                        $new_quantity = $cart_accessory['default_quantity'] * $current_main_product_quantity;
                        if ($cart_product['id_product'] == (int) Tools::getValue('id_product')) {
                            $qty_to_check = $cart_accessory['default_quantity'] - (int) Tools::getValue('qty', 1);
                        } else {
                            $qty_to_check = $cart_accessory['default_quantity'] * (int) Tools::getValue('qty', 1);
                        }
                        if ($qty_to_check > 0) {
                            if (!$this->context->cart->updateQty($qty_to_check, $cart_product['id_product'], $cart_product['id_product_attribute'], $cart_product['id_customization'], $operation)) {
                                exit(Tools::jsonEncode(array(
                                            'hasError' => true,
                                            'errors' => array($this->i18n['the_product_is_no_longer_available_in_this_quantity']),
                                )));
                            }
                        }
                        $accessory_cart_product = new HsAccessoryCartProductAbstract($id_cart, $cart_accessory['id_product'], $cart_accessory['id_product_attribute'], $cart_accessory['id_accessory'], $cart_accessory['id_accessory_attribute'], $cart_accessory['id_customization'], $cart_accessory['id_product_customization']);
                        $accessory_cart_product->quantity = $new_quantity;
                        $accessory_cart_product->prev_quantity = $new_quantity;
                        $accessory_cart_product->update();
                        break;
                    }
                }
            }
        }
    }

    /**
     * Delete accessories of a product.
     */
    public function hookActionProductDelete($params)
    {
        $id_product = (int) $params['id_product'];
        if (!$id_product) {
            return;
        }
        $hs_ma_product = new HsMaProduct();
        $hs_ma_product->id = $id_product;
        $hs_ma_product->deleteAccessories();
    }

    /**
     * Turn on a flag in case the event (adding a main product into shopping cart) occurs.
     */
    public function hookActionObjectCartAddAfter()
    {
        self::$has_cart_been_added = true;
    }

    /**
     * get min quantity of array quantity of products.
     *
     * @param array $quantities = array (1 => int, 2 => int,....)
     *
     * @return int
     */
    protected function getMinQuantity(array $quantities)
    {
        return min($quantities);
    }

    /**
     * show a setting form in the top of list accessory group.
     *
     * @return html
     */
    public function hookDisplayAdminListBefore()
    {
        $controller_name = Tools::getValue('controller');
        if ($controller_name === $this->class_controller_admin_group) {
            $this->context->controller->addCSS($this->getCSSPath() . 'accessory_admin_tab.css', 'all');
            $this->context->controller->addCSS($this->getCSSPath() . 'adminpage.css', 'all');
            if (!$this->isPrestashop16()) {
                $this->context->controller->addCSS($this->getCSSPath() . 'adminpage_15.css', 'all');
            }

            return $this->renderForm();
        }
    }

    /**
     * Render form settings
     * @return html
     */
    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->i18n['settings'],
                    'image' => $this->getImgPath() . 'setting.gif',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->i18n['display_style'],
                        'name' => 'HSMA_DISPLAY_STYLE',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['define_how_accessories_look_like_at_product_page'],
                        'options' => array(
                            'query' => $this->getDisplayStyles(),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['show_images'],
                        'name' => 'HSMA_SHOW_IMAGES',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['display_images_along_with_each_accessory'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_IMAGES_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_IMAGES_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    $this->getFancyboxOption(),
                    $this->renderImageSizeInFancyBox(),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['show_price'],
                        'name' => 'HSMA_SHOW_PRICE',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['display_prices_along_with_each_accessory'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_PRICE_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_PRICE_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['show_price_table'],
                        'name' => 'HSMA_SHOW_PRICE_TABLE',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['tell_your_customers_a_summary'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_PRICE_TABLE_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_PRICE_TABLE_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['display_combination_info_in_price_table'],
                        'name' => 'HSMA_SHOW_COMBINATION',
                        'form_group_class' => 'block_show_combination',
                        'hint' => $this->i18n['display_combination_info_in_price_table'],
                        'class' => $this->isPrestashop15() ? 't' : '',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_COMBINATION_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_COMBINATION_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['change_the_main_item_s_price_accordingly'],
                        'name' => 'HSMA_CHANGE_MAIN_PRICE',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['whenever_an_accessory_is_added_or_removed_the_main_item_s_price_is_changed_and_your_customers_clearly_know_the_amount'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_CHANGE_MAIN_PRICE_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_CHANGE_MAIN_PRICE_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['show_quantity_box_of_accessories'],
                        'name' => 'HSMA_SHOW_CUSTOM_QUANTITY',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['display_quantity_box_along_with_each_accessory'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_CUSTOM_QUANTITY_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_CUSTOM_QUANTITY_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['allow_your_customers_to_change_item_quantity'],
                        'name' => 'HSMA_ALLOW_CUSTOMER_CHANGE_QTY',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['allow_your_customers_to_change_item_quantity'],
                        'class' => $this->isPrestashop15() ? 't' : '',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'HSMA_ALLOW_CUSTOMER_CHANGE_QTY_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_ALLOW_CUSTOMER_CHANGE_QTY_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['buy_main_product_accessories_together'],
                        'name' => 'HSMA_BUY_ACCESSORY_MAIN_TOGETHER',
                        'form_group_class' => 'buytogetheroption',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['tell_your_customers_that_they_need_to_buy_main_product_and_accessories_together'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_BUY_ACCESSORY_MAIN_TOGETHER_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_BUY_ACCESSORY_MAIN_TOGETHER_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['show_total_price_instead_of_the_main_product_price_at_the_product_list_page'],
                        'name' => 'HSMA_SHOW_TOTAL_PRICE',
                        'form_group_class' => 'showtotalpriceoption',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['show_total_price_main_product_price_required_accessories_price_instead_of_the_main_product_price_at_the_product_list_page'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_TOTAL_PRICE_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_TOTAL_PRICE_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['add_each_accessory_to_basket'],
                        'name' => 'HSMA_EACH_ACCESSORY_TO_BASKET',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['allow_customer_add_separated_accessory_to_basket'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_EACH_ACCESSORY_TO_BASKET_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_EACH_ACCESSORY_TO_BASKET_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['delete_main_product_and_all_accessories_when_delete_main_product_from_basket'],
                        'name' => 'HSMA_DEL_ALL_WHEN_DEL_MPRODUCT',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['automatic_to_delete_main_product_and_all_accessories_of_this_product_when_your_customer_deletes_main_product_from_the_basket'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_DEL_ALL_WHEN_DEL_MPRODUCT_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_DEL_ALL_WHEN_DEL_MPRODUCT_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['delete_main_product_and_all_accessories_when_delete_an_accessory_from_basket'],
                        'name' => 'HSMA_DEL_ALL_WHEN_DEL_ACCESSORY',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['automatic_to_delete_main_product_and_all_accessories_for_this_product_when_your_customer_deletes_an_accessory_from_the_basket'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_DEL_ALL_WHEN_DEL_ACCESSORY_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_DEL_ALL_WHEN_DEL_ACCESSORY_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['display_accessories_out_of_stock_at_the_front_end'],
                        'name' => 'HSMA_SHOW_ACCESSORIES_OFS',
                        'form_group_class' => 'accessories_out_of_stock_option',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['display_or_hide_accessories_out_of_stock_at_the_front_end'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_ACCESSORIES_OFS_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_ACCESSORIES_OFS_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['display_nowhere_visibility_products_at_the_front_end'],
                        'name' => 'HSMA_SHOW_NW_VISIBILITY_PRODUCTS',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['display_nowhere_visibility_products_at_the_front_end'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_NW_VISIBILITY_PRODUCTS_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_NW_VISIBILITY_PRODUCTS_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['change_accessory_name_when_change_product_name'],
                        'name' => 'HSMA_CHANGE_ACCESSORY_NAME',
                        'form_group_class' => 'change_accessory_name_option',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['automatic_to_change_the_accessory_name_when_you_change_the_product_name_in_the_edit_product_page'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_CHANGE_ACCESSORY_NAME_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_CHANGE_ACCESSORY_NAME_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['display_icon_out_of_stock_at_the_front_end'],
                        'name' => 'HSMA_SHOW_ICON_OUT_OF_STOCK',
                        'form_group_class' => 'buytogetheroption',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['tell_your_customers_that_this_accessory_is_out_of_stock'],
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SHOW_ICON_OUT_OF_STOCK_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SHOW_ICON_OUT_OF_STOCK_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    $this->renderAlertMessage(),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['open_accessories_in_a_new_tab'],
                        'name' => 'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB',
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => $this->isPrestashop16() ? 'switch' : 'radio',
                        'label' => $this->i18n['scroll_to_the_table_price'],
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['scroll_to_the_table_price_when_customers_select_unselect_an_accessory_at_the_front_end'],
                        'name' => 'HSMA_SCROLL_TO_TABLE_PRICE',
                        'is_bool' => true,
                        'class' => !$this->isPrestashop16() ? 't' : '',
                        'values' => array(
                            array(
                                'id' => 'HSMA_SCROLL_TO_TABLE_PRICE_on',
                                'value' => 1,
                                'label' => $this->i18n['enabled']
                            ),
                            array(
                                'id' => 'HSMA_SCROLL_TO_TABLE_PRICE_off',
                                'value' => 0,
                                'label' => $this->i18n['disabled']
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->i18n['title'],
                        'class' => 'field-text-input',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['title_of_accessory_block_at_product_page'],
                        'name' => 'HSMA_TITLE',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->i18n['displayed_text_when_backordering_is_allowed'],
                        'class' => 'field-text-input',
                        $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['if_the_text_displayed_text_when_backordering_is_allowed_in_product_edit_page_is_empty'],
                        'name' => 'HSMA_MESSAGE_AVAILABLE_LATER',
                        'lang' => true,
                    )
                ),
                'submit' => array(
                    'title' => $this->i18n['save'],
                    'name' => 'submitSetting'
                )
            ),
        );

        if (!$this->isPrestashop16()) {
            foreach ($fields_form['form']['input'] as $key => $input) {
                if (empty($input)) {
                    unset($fields_form['form']['input'][$key]);
                }
            }
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = $this->identifier;
        $helper->submit_action = '';
        $helper->currentIndex = $this->context->link->getAdminLink($this->class_controller_admin_group, false);
        $helper->token = Tools::getAdminTokenLite($this->class_controller_admin_group);
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigurationValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Render field form alert message.
     *
     * @return array
     */
    protected function renderAlertMessage()
    {
        $fields_form = array();
        $alert_message = array(
            'type' => 'text',
            'class' => 'field-text-input',
            'form_group_class' => 'alertmessage',
            'label' => $this->i18n['alert_message'],
            $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['tell_your_customer_when_they_dont_choose_any_accessories_to_buy_together_with_main_product'],
            'name' => 'HSMA_ALERT_MESSAGE',
            'lang' => true,
        );

        // check this to make sure it works with both PS 1.5 & 1.6
        if ($this->isPrestashop16()) {
            $fields_form = $alert_message;
        } elseif (Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER')) {
            $fields_form = $alert_message;
        }

        return $fields_form;
    }

    /**
     * Render field form apply fancybox to image.
     *
     * @return array
     */
    protected function getFancyboxOption()
    {
        return array(
            'type' => $this->isPrestashop16() ? 'switch' : 'radio',
            'label' => $this->i18n['apply_fancybox_to_images'],
            $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['show_accessory_images_in_a_fancybox'],
            'name' => 'HSMA_APPLY_FANCYBOX_TO_IMAGE',
            'is_bool' => true,
            'class' => !$this->isPrestashop16() ? 't' : '',
            'form_group_class' => 'apply_fancybox',
            'values' => array(
                array(
                    'id' => 'HSMA_APPLY_FANCYBOX_TO_IMAGE_on',
                    'value' => 1,
                    'label' => $this->i18n['enabled'],
                ),
                array(
                    'id' => 'HSMA_APPLY_FANCYBOX_TO_IMAGE_off',
                    'value' => 0,
                    'label' => $this->i18n['disabled'],
                ),
            ),
        );
    }

    /**
     * Render field form image size in fancybox.
     *
     * @return array
     */
    protected function renderImageSizeInFancyBox()
    {
        $image_sizes = $this->getSizeOfImages();
        return array(
            'type' => 'select',
            'label' => $this->i18n['image_size_in_fancybox'],
            'form_group_class' => 'image_size',
            'name' => 'HSMA_IMAGE_SIZE_IN_FANCYBOX',
            $this->isPrestashop16() ? 'hint' : 'desc' => $this->i18n['image_size_in_fancybox'],
            'options' => array(
                'query' => $image_sizes['image_sizes'],
                'id' => 'id',
                'name' => 'name',
                'default' => array(
                    'label' => $image_sizes['default']['name'] ? $image_sizes['default']['name'] : '---',
                    'value' => $image_sizes['default']['id'] ? $image_sizes['default']['id'] : 0,
                ),
            ),
        );
    }

    /**
     * Get value of configuration.
     *
     * @return array
     *               <pre>
     *               Array(
     *               'key_configuration' => int,
     *               );
     */
    protected function getConfigurationValues()
    {
        $fields_values = array(
            'HSMA_DISPLAY_STYLE' => Tools::getValue('HSMA_DISPLAY_STYLE', Configuration::get('HSMA_DISPLAY_STYLE')),
            'HSMA_SHOW_IMAGES' => Tools::getValue('HSMA_SHOW_IMAGES', Configuration::get('HSMA_SHOW_IMAGES')),
            'HSMA_SHOW_PRICE' => Tools::getValue('HSMA_SHOW_PRICE', Configuration::get('HSMA_SHOW_PRICE')),
            'HSMA_SHOW_SHORT_DESCRIPTION' => Tools::getValue('HSMA_SHOW_SHORT_DESCRIPTION', Configuration::get('HSMA_SHOW_SHORT_DESCRIPTION')),
            'HSMA_SHOW_PRICE_TABLE' => Tools::getValue('HSMA_SHOW_PRICE_TABLE', Configuration::get('HSMA_SHOW_PRICE_TABLE')),
            'HSMA_EACH_ACCESSORY_TO_BASKET' => Tools::getValue('HSMA_EACH_ACCESSORY_TO_BASKET', Configuration::get('HSMA_EACH_ACCESSORY_TO_BASKET')),
            'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB' => Tools::getValue('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB', Configuration::get('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB')),
            'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => Tools::getValue('HSMA_BUY_ACCESSORY_MAIN_TOGETHER', Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER')),
            'HSMA_SHOW_TOTAL_PRICE' => Tools::getValue('HSMA_SHOW_TOTAL_PRICE', Configuration::get('HSMA_SHOW_TOTAL_PRICE')),
            'HSMA_SHOW_CUSTOM_QUANTITY' => Tools::getValue('HSMA_SHOW_CUSTOM_QUANTITY', Configuration::get('HSMA_SHOW_CUSTOM_QUANTITY')),
            'HSMA_APPLY_FANCYBOX_TO_IMAGE' => Tools::getValue('HSMA_APPLY_FANCYBOX_TO_IMAGE', Configuration::get('HSMA_APPLY_FANCYBOX_TO_IMAGE')),
            'HSMA_IMAGE_SIZE_IN_FANCYBOX' => Tools::getValue('HSMA_IMAGE_SIZE_IN_FANCYBOX', Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX')),
            'HSMA_CHANGE_MAIN_PRICE' => Tools::getValue('HSMA_CHANGE_MAIN_PRICE', Configuration::get('HSMA_CHANGE_MAIN_PRICE')),
            'HSMA_ALLOW_CUSTOMER_CHANGE_QTY' => Tools::getValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', Configuration::get('HSMA_ALLOW_CUSTOMER_CHANGE_QTY')),
            'HSMA_SHOW_COMBINATION' => Tools::getValue('HSMA_SHOW_COMBINATION', Configuration::get('HSMA_SHOW_COMBINATION')),
            'HSMA_SHOW_ACCESSORIES_OFS' => Tools::getValue('HSMA_SHOW_ACCESSORIES_OFS', Configuration::get('HSMA_SHOW_ACCESSORIES_OFS')),
            'HSMA_CHANGE_ACCESSORY_NAME' => Tools::getValue('HSMA_CHANGE_ACCESSORY_NAME', Configuration::get('HSMA_CHANGE_ACCESSORY_NAME')),
            'HSMA_SCROLL_TO_TABLE_PRICE' => Tools::getValue('HSMA_SCROLL_TO_TABLE_PRICE', Configuration::get('HSMA_SCROLL_TO_TABLE_PRICE')),
            'HSMA_DEL_ALL_WHEN_DEL_MPRODUCT' => Tools::getValue('HSMA_DEL_ALL_WHEN_DEL_MPRODUCT', Configuration::get('HSMA_DEL_ALL_WHEN_DEL_MPRODUCT')),
            'HSMA_DEL_ALL_WHEN_DEL_ACCESSORY' => Tools::getValue('HSMA_DEL_ALL_WHEN_DEL_ACCESSORY', Configuration::get('HSMA_DEL_ALL_WHEN_DEL_ACCESSORY')),
            'HSMA_SHOW_ICON_OUT_OF_STOCK' => Tools::getValue('HSMA_SHOW_ICON_OUT_OF_STOCK', Configuration::get('HSMA_SHOW_ICON_OUT_OF_STOCK')),
            'HSMA_SHOW_NW_VISIBILITY_PRODUCTS' => Tools::getValue('HSMA_SHOW_NW_VISIBILITY_PRODUCTS', Configuration::get('HSMA_SHOW_NW_VISIBILITY_PRODUCTS')),
        );

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $fields_values['HSMA_TITLE'][$lang['id_lang']] = Tools::getValue('HSMA_TITLE', Configuration::get('HSMA_TITLE', $lang['id_lang']));
            $fields_values['HSMA_MESSAGE_AVAILABLE_LATER'][$lang['id_lang']] = Tools::getValue('HSMA_MESSAGE_AVAILABLE_LATER', Configuration::get('HSMA_MESSAGE_AVAILABLE_LATER', $lang['id_lang']));
            $fields_values['HSMA_ALERT_MESSAGE'][$lang['id_lang']] = Tools::getValue('HSMA_ALERT_MESSAGE', Configuration::get('HSMA_ALERT_MESSAGE', $lang['id_lang']));
        }

        return $fields_values;
    }

    /**
     * Get display styles.
     *
     * @param bool $use_default // use value display style of default setting
     *
     * @return array
     *      array(<pre>
     *          'id' => int,
     *          'name' =>   string,
     *      ),
     *      ...
     *      )</pre>
     */
    public function getDisplayStyles($use_default = false)
    {
        $type_options = array(
            array(
                'id' => HsMaDisplayStyle::CHECKBOX,
                'name' => $this->i18n['checkbox'],),
            array(
                'id' => HsMaDisplayStyle::DROPDOWN,
                'name' => $this->i18n['dropdown'],),
            array(
                'id' => HsMaDisplayStyle::RADIO,
                'name' => $this->i18n['radio'],),
        );
        if ($use_default) {
            $type_options = array_merge(array(array('id' => HsMaDisplayStyle::USE_DEFAULT, 'name' => $this->i18n['use_default'])), $type_options);
        }

        return $type_options;
    }
    
    /**
     * Set default title of Multi Accessories.
     *
     * @return bool
     */
    protected function setDefaultTitle()
    {
        $languages = Language::getLanguages(false);
        $titles = array();
        if (!empty($languages)) {
            foreach ($languages as $language) {
                $titles[$language['id_lang']] = $this->i18n['must_have_accessories'];
            }
        }

        if (!empty($titles)) {
            return Configuration::updateValue('HSMA_TITLE', $titles);
        }

        return false;
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
        $success = array();
        switch ($version) {
            case '2.0':
                $success[] = Configuration::updateValue('HSMA_DISPLAY_STYLE', 1);
                $success[] = Configuration::updateValue('HSMA_SHOW_IMAGES', 1);
                $success[] = Configuration::updateValue('HSMA_SHOW_PRICE', 1);
                $success[] = $this->setDefaultTitle();
                $success[] = $this->registerHook('displayAdminListBefore');
                $success[] = $this->registerHook('displayAdminProductsExtra');
                $success[] = $this->proccessRemoveTab($this->class_controller_admin_product_setting);
                $success[] = $this->installer->addNewImageType();
                break;

            case '2.1':
                $success[] = Configuration::updateValue('HSMA_EACH_ACCESSORY_TO_BASKET', 1);
                $success[] = Configuration::updateValue('HSMA_SHOW_PRICE_TABLE', 1);
                break;

            case '2.1.2':
                $success[] = $this->registerHook('displayMultiAccessoriesProduct');
                break;

            case '2.2':
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'tab_lang` tl
						 LEFT JOIN `' . _DB_PREFIX_ . 'tab` t ON t.`id_tab` = tl.`id_tab`
						 SET tl.`name` = "' . pSQL($this->i18n['multi_accessories']) . '"
						 WHERE t.`module` = "' . pSQL($this->name) . '"';
                $success[] = Db::getInstance()->execute($sql);
                $success[] = $this->setDefaultAlertMessage();
                break;

            case '2.3':
                $success[] = Configuration::updateValue('HSMA_CHANGE_MAIN_PRICE', 1);
                $success[] = Configuration::updateValue('HSMA_APPLY_FANCYBOX_TO_IMAGE', 1);
                break;

            case '2.4':
                $success[] = Configuration::updateValue('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB', 1);
                $success[] = $this->installer->updateTable24();
                break;

            case '2.4.3':
                $success[] = $this->installer->installTab243($this->tab_admin_welcome_page);
                break;

            case '2.4.5':
                $success[] = $this->registerHook('displayFooter');
                break;

            case '2.5':
                $success[] = $this->installer->updateTable25();
                $success[] = $this->registerHook('actionProductAdd');
                break;

            case '2.5.2':
                $success[] = $this->installer->updateTable252();
                break;

            case '2.7':
                $success[] = $this->installer->update27($this->tabs27);
                break;

            case '2.7.3':
                $success[] = $this->installer->update273();
                break;

            case '2.8':
                $success[] = $this->registerHook('displayShoppingCartFooter');
                break;

            case '2.8.4':
                $success[] = $this->installer->updateTable284();
                break;

            case '2.8.7':
                $success[] = $this->installer->updateTable287();
                $success[] = $this->installer->updateConfiguration287();
                break;

            case '2.8.8':
                $success[] = $this->registerHook('actionAdminProductsFormModifier');
                $success[] = $this->unregisterHook('displayBackOfficeHeader');
                break;

            case '2.8.10':
                $success[] = $this->installer->updateConfiguration2810();
                $success[] = $this->installer->updateTable2810();
                if ($this->isPrestashop15()) {
                    $success[] = $this->registerHook('actionAdminControllerSetMedia');
                }
                break;

            case '2.9.0':
                $success[] = $this->installer->installTables290();
                $success[] = $this->registerHook('actionAfterDeleteProductInCart');
                $success[] = $this->installer->updateConfiguration290();
                break;
            case '2.9.1':
                if ($this->isPrestashop17()) {
                    $success[] = $this->registerHook('displayReassurance');
                    if ($this->isPrestashop1711()) {
                        $success[] = $this->registerHook('actionObjectProductInCartDeleteAfter');
                    } else {
                        $success[] = $this->registerHook('actionDeleteProductInCartAfter');
                    }
                }
                break;
            case '2.10.1':
                $success[] = $this->installer->updateConfiguration2101();
                break;
            case '2.10.5':
                $success[] = $this->registerHook('actionProductUpdate');
                break;
            case '3.0.0':
                $success[] = $this->installer->updateVersion300();
                $success[] = Configuration::updateValue('HSMA_SCROLL_TO_TABLE_PRICE', 1);
                $success[] = Configuration::updateValue('HSMA_DEL_ALL_WHEN_DEL_MPRODUCT', 1);
                $success[] = Configuration::updateValue('HSMA_DEL_ALL_WHEN_DEL_ACCESSORY', 1);
                break;
            case '3.1.0':
                $success[] = $this->registerHook('actionObjectHsAccessoriesGroupAbstractDeleteAfter');
                $success[] = $this->registerHook('deleteProductAttribute');
                break;
            case '4.0.0':
                $success[] = $this->isPrestashop17() ? $this->registerHook('displayNavFullWidth') : $this->registerHook('displayTop');
                $success[] = $this->installer->updateVersion400();
                break;
            case '4.2.0':
                $success[] = $this->installer->updateTable420();
                break;
            case '4.3.1':
                $success[] = $this->installer->updateTable431();
                break;
            
            default:
                break;
        }

        return array_sum($success) >= count($success);
    }

    /**
     * create product tab "Multi Accessories" in admin product detail.
     *
     * @return html
     */
    public function hookDisplayAdminProductsExtra($params = array())
    {
        $id_product = $this->isPrestashop17() ? $params['id_product'] : Tools::getValue('id_product', 1);
        $product = new Product($id_product, false, $this->context->language->id);

        if (Validate::isLoadedObject($product)) {
            $this->renderFormAccessoryGroup($product);
        } else {
            $this->adminDisplayWarning($this->i18n['you_must_save_this_product_before_adding_accessories']);
        }

        $st_hsmultiaccessories = array(
            'url' => $this->urls,
            'lang' => $this->i18n,
        );
        $this->context->smarty->assign(array(
            'st_hsmultiaccessories' => Tools::jsonEncode($st_hsmultiaccessories),
            'id_product' => $id_product,
            'is_ps17' => $this->isPrestashop17(),
            'js_path' => $this->getJsPath(),
            'languages' => Language::getLanguages(false),
            'css_path' => $this->getCssPath(),
            'base_uri' => __PS_BASE_URI__,
            'is_product_page' => true,
            'module_version' => $this->version,
            'currency' => $this->context->currency
        ));
        return $this->display($this->name . '.php', 'hsma_display_admin_product_extra.tpl');
    }

    /**
     * Show  group accessories of each product.
     *
     * @param Product $product
     */
    protected function renderFormAccessoryGroup($product)
    {
        $id_groups = HsAccessoriesGroupAbstract::getIdGroups((int) $this->context->language->id);
        $accessories_groups = HsAccessoriesGroupAbstract::getAccessoriesByGroups($id_groups, array($product->id));
        $languages = Language::getLanguages(true);
        $meta_language = array();
        foreach ($languages as $lang) {
            $meta_language[] = $lang['iso_code'];
        }
        $product_setting = new HsMaProductSetting((int) $product->id);
        $this->context->smarty->assign(array(
            'groups' => HsAccessoriesGroupAbstract::getGroups((int) $this->context->language->id),
            'product_setting' => $product_setting,
            'is_prestashop16' => $this->isPrestashop16(),
            'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'show_custom_quantity' => 1,
            'accessories_groups' => $accessories_groups,
            'languages' => $languages,
            'url_change_accessory_position' => $this->urls['ajaxProcessUpdateAccessoryProductPosition'],
            'img_path' => $this->getImgPath(),
            'display_styles' => $this->getDisplayStyles(true),
            'buy_together_options' => $this->getProductSettingBuyTogetherOptions(),
            'buy_together_default' => HsMaProductSetting::getBuyTogetherDefault((int) $product->id),
            'tax_calculation_method' => !Product::getTaxCalculationMethod(),
        ));
    }

    /**
     * Check prestashop current version is 1.6.
     *
     * @return boolean
     */
    public function isPrestashop16()
    {
        return version_compare(_PS_VERSION_, '1.6') === 1;
    }

    /**
     * Check prestashop current version is 1.5.
     *
     * @return boolean
     */
    public function isPrestashop15()
    {
        return version_compare(_PS_VERSION_, '1.6', '<');
    }

    /**
     * Remove a tab.
     *
     * @param string $name_tab
     *
     * @return bool
     */
    protected function proccessRemoveTab($name_tab)
    {
        $flag = false;
        $id_tab = (int) Tab::getIdFromClassName((string) $name_tab);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $flag = $tab->delete();
            }
        }

        return $flag;
    }

    /**
     * Get display styles.
     *
     * @return array
     *               array
     *               (<pre>
     *               [image_sizes] => array
     *               (
     *               [0] => array
     *               (
     *               [id] => 29
     *               [name] => cart_default (80x80)
     *               )
     *               [1] => array
     *               (
     *               [id] => 32
     *               [name] => home_default (250x250)
     *               )
     *               ...
     *               )
     *               [default] => array
     *               (
     *               [id] => 34
     *               [name] => thickbox_default (800x800)
     *               )
     *               )</pre>
     */
    protected function getSizeOfImages()
    {
        $image_types = ImageType::getImagesTypes('products');
        $image_sizes = array();
        $i = 0;
        $default_image = array();
        $max_width = 0;
        $id_image_type_default = Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX');
        foreach ($image_types as $image_type) {
            if ($id_image_type_default > 0) {
                if ($image_type['name'] === $id_image_type_default) {
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } else {
                    $image_sizes[$i]['id'] = $image_type['name'];
                    $image_sizes[$i]['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                }
            } else {
                if ($max_width == 0) {
                    $max_width = $image_type['width'];
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } elseif ($max_width < $image_type['width']) {
                    $max_width = $image_type['width'];
                    $image_sizes[$i] = $default_image;
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } else {
                    $image_sizes[$i] = $default_image;
                }
            }
            ++$i;
        }

        return array('image_sizes' => array_map('unserialize', array_unique(array_map('serialize', $image_sizes))), 'default' => $default_image);
    }

    /**
     * Get random id.
     *
     * @return string
     */
    protected function getRandomId()
    {
        return Tools::passwdGen(8, 'NO_NUMERIC');
    }

    /**
     * Get key go to welcome page.
     *
     * @return string
     */
    public function getKeyWelcomePage()
    {
        return Tools::strtoupper(md5($this->name . $this->version));
    }

    /**
     * Duplicate accessories.
     *
     * @param array $params
     * <pre>
     * array (
     *  [product] => Product(
     *      [id_manufacturer] => int
     *      [id_supplier] => int
     *      [id_category_default] => int
     *      [id_shop_default] => int
     *      [manufacturer_name] => string
     *      [supplier_name] =>
     *      [name] => array()
     *      [description] => array()
     *      [quantity] => int
     *      [minimal_quantity] => int
     *      [available_now] => array()
     *      [available_later] => array()
     *      [price] => float
     *      [specificPrice] => float
     *      [additional_shipping_cost] => float
     *      [wholesale_price] => float
     *      [on_sale] => int
     *      [online_only] => boolean
     *      [unity] => string
     *      [unit_price] => float
     *      [unit_price_ratio] => float
     *      [ecotax] => float
     *      [reference] => string
     *      [supplier_reference] =>  string
     *      [location] => string
     *      [width] => float
     *      [height] => float
     *      [depth] => float
     *      [weight] => float
     *      [ean13] => string
     *      [upc] => string
     *      [link_rewrite] => array()
     *      [meta_description] => array()
     *      [meta_keywords] => array ()
     *      [meta_title] => array()
     *      [quantity_discount] => int
     *      [customizable] => int
     *      [new] => string
     *      [uploadable_files] => int
     *      [text_fields] => int
     *      [active] => boolean
     *      [redirect_type] => int
     *      [id_product_redirected] => int
     *      [available_for_order] => boolean
     *      [available_date] => date time
     *      [condition] => string
     *      [show_price] => boolean
     *      [indexed] => int
     *      [visibility] => string
     *      [date_add] => date time
     *      [date_upd] => date time
     *      [tags] => string
     *      [base_price] => float
     *      [id_tax_rules_group] => int
     *      [id_color_default] => int
     *      [advanced_stock_management] => boolean
     *      [out_of_stock] => int
     *      [depends_on_stock] =>  boolean
     *      [isFullyLoaded] => boolean
     *      [cache_is_pack] => boolean
     *      [cache_has_attachments] => boolean
     *      [is_virtual] => boolean
     *      [id_pack_product_attribute] => int
     *      [cache_default_attribute] => int
     *      [category] => int
     *      [pack_stock_type] => int
     *      [tax_name] => string
     *      [tax_rate] => float
     *  <pre/>
     * )
     */
    public function hookActionProductAdd($params)
    {
        $from_id_product = $this->getFromIdProduct();
        $from_product = new HsMaProduct($from_id_product);
        if (!Validate::isLoadedObject($from_product) || !Validate::isLoadedObject($params['product'])) {
            return;
        }
        return $this->copyAccessories($from_product, $params['product']);
    }
    
    public function hookActionProductUpdate($params)
    {
        if (!Configuration::get('HSMA_CHANGE_ACCESSORY_NAME') || empty($params['product'])) {
            return;
        }
        $product = $params['product'];
        if (!Validate::isLoadedObject($product)) {
            return;
        }
        return $this->updateGlobalAccessoryName($product);
    }
    
    protected function updateGlobalAccessoryName($product)
    {
        $success = array();
        $success[] = HsAccessoriesGroupProduct::updateGlobalName($product->id, $product->name);
        $cart_rule_names = $this->generateCartRuleNames($product);
        $success[] = HsMaCartRule::updateCartRuleName($product->id, $cart_rule_names);
        return array_sum($success) >= count($success);
    }
    
    protected function getFromIdProduct()
    {
        $from_id_product = 0;
        if ($this->isPrestashop17()) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $request_uri = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
            }
            if (!empty($request_uri)) {
                $array_uri = explode('?', $request_uri);
                $uri_params = explode('/', $array_uri[0]);
                $from_id_product = (int) end($uri_params);
            }
        } else {
            $from_id_product = (int) Tools::getValue('id_product');
        }
        return $from_id_product;
    }

    /**
     * Get array values buy main product & accessory together.
     *
     * @return array
     *               array(<pre>
     *               array(
     *               'id'   => int,
     *               'name' => string,
     *               ),
     *               ...
     *               )</pre>
     */
    public function getProductSettingBuyTogetherOptions()
    {
        $buy_together_options = array(
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_NO,
                'name' => $this->i18n['no'],),
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_YES,
                'name' => $this->i18n['yes'],),
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT,
                'name' => $this->i18n['use_default'],),
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_REQUIRED,
                'name' => $this->i18n['let_me_specify'],),
        );

        return $buy_together_options;
    }

    /**
     * Get message available later of accessory.
     *
     * @param string $available_later
     *
     * @return string
     */
    protected function getMessageAvailableLater($available_later)
    {
        $message_available_later = $this->i18n['out_of_stock_but_backordering_is_allowed'];
        $config_message_available_later = Configuration::get('HSMA_MESSAGE_AVAILABLE_LATER', (int) $this->context->language->id);
        if (!empty($available_later)) {
            $message_available_later = $available_later;
        } elseif (!empty($config_message_available_later)) {
            $message_available_later = $config_message_available_later;
        }

        return $message_available_later;
    }

    public function renderAccessories($params)
    {
        if (empty($params['id_products']) || !Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER')) {
            return;
        }
        $use_tax = $this->isUsetax();
        $decimals = $this->getDecimals();
        $list_accessories = array(
            'success' => true,
            'show_total_price' => (int) Configuration::get('HSMA_SHOW_TOTAL_PRICE'),
            'accessories' => HsAccessoriesGroupProductAbstract::getAccessoriesByIdProducts($params['id_products'], $use_tax, $decimals),
            'total_price' => HsAccessoriesGroupProductAbstract::getTotalPrice(),
            'total_price_without_discount' => HsAccessoriesGroupProductAbstract::getTotalPriceWithOutDiscount(),
            'i18n_more' => $this->i18n['more'],
            'i18n_customize' => $this->i18n['customize'],
        );
        return Tools::jsonEncode($list_accessories);
    }
    
    /**
     * This hook implement for Prestashop >= 1.7.1.1
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        return $this->hookActionAfterDeleteProductInCart($params);
    }

    /**
     * This hook implement for Prestashop 1.7.0.5 to < 1.7.1.1
     * @param array $params
     * @return boolean
     */
    public function hookActionDeleteProductInCartAfter($params)
    {
        return $this->hookActionAfterDeleteProductInCart($params);
    }

    public function hookActionAfterDeleteProductInCart($params)
    {
        $old_id_product = (int) Tools::getValue('id_product');
        $id_cart = $params['id_cart'];
        $id_product = $params['id_product'];
        $id_product_attribute = $params['id_product_attribute'];
        $id_product_customization = $params['customization_id'];
        if (!$id_cart || !$id_product) {
            return;
        }
        $accessories = HsAccessoryCartProductAbstract::getAccessoriesByIdCartProduct($id_cart, $id_product, $id_product_attribute, $id_product_customization);
        $operator = 'down';
        $success = array();
        if (!empty($accessories)) {
            // The case delete all accessories when delete main product.
            if ((int) Configuration::get('HSMA_DEL_ALL_WHEN_DEL_MPRODUCT') === 1) {
                foreach ($accessories as $accessory) {
                    if ($accessory['id_accessory'] !== $old_id_product) {
                        $success[] = $this->context->cart->updateQty((int) $accessory['quantity'], (int) $accessory['id_accessory'], (int) $accessory['id_accessory_attribute'], (int) $accessory['id_customization'], $operator);
                    }
                }
                if (array_sum($success) >= count($success)) {
                    HsAccessoryCartProductAbstract::deteleteProductAccessories($id_cart, $id_product, $id_product_attribute, $id_product_customization);
                }
            } else {
                HsAccessoryCartProductAbstract::deteleteProductAccessories($id_cart, $id_product, $id_product_attribute, $id_product_customization);
            }
        } else {
            // the case delete main product when delete an accessory
            $product_accessories = HsAccessoryCartProductAbstract::getProductByIdCartAccessory($id_cart, $id_product, $id_product_attribute, $id_product_customization);
            if (!empty($product_accessories)) {
                if ((int) Configuration::get('HSMA_DEL_ALL_WHEN_DEL_ACCESSORY') === 1) {
                    $main_products = array();
                    foreach ($product_accessories as $product_accessory) {
                        $id_main_products = array('id_product' => $product_accessory['id_product'], 'id_product_attribute' => $product_accessory['id_product_attribute'], 'id_product_customization' => $product_accessory['id_product_customization']);
                        $main_products[] = $id_main_products;
                    }
                    foreach ($main_products as $main_product) {
                        $id_main_product = $main_product['id_product'];
                        $id_main_product_attribute = $main_product['id_product_attribute'];
                        $id_main_product_id_customization = $main_product['id_product_customization'];
                        $accessories = HsAccessoryCartProductAbstract::getAccessoriesByIdCartProduct($id_cart, $id_main_product, $id_main_product_attribute, $id_main_product_id_customization);
                        if (!empty($accessories)) {
                            // the case delete accessories when delete main product
                            foreach ($accessories as $accessory) {
                                if ($accessory['id_accessory'] !== $old_id_product) {
                                    $success[] = $this->context->cart->updateQty($accessory['quantity'], $accessory['id_accessory'], $accessory['id_accessory_attribute'], $accessory['id_customization'], $operator);
                                }
                            }
                            if (array_sum($success) >= count($success)) {
                                $this->context->cart->deleteProduct($id_main_product, $id_main_product_attribute, $id_main_product_id_customization);
                                HsAccessoryCartProductAbstract::deteleteProductAccessories($id_cart, $id_main_product, $id_main_product_attribute, $id_main_product_id_customization);
                            }
                        }
                    }
                } else {
                    // Remove record cart product of this accessry
                    HsAccessoryCartProductAbstract::deteleteAccessory($id_cart, $id_product, $id_product_attribute);
                }
            }
        }
    }
    
    public function isPrestashop17()
    {
        return (int) version_compare(_PS_VERSION_, '1.7', '>=');
    }
    
    public function isPrestashop1711()
    {
        return (int) version_compare(_PS_VERSION_, '1.7.1.1', '>=');
    }
    
    public function isPrestashop176()
    {
        return (int) version_compare(_PS_VERSION_, '1.7.6', '>=');
    }
    
    public function getAdminUrlForPsVersion()
    {
        return Tools::getCurrentUrlProtocolPrefix() . Tools::getShopDomain() . __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . '/index.php';
    }
    
    /**
    * Copy accessory group products from another product.
    *
    * @param HsMaProduct $from_product
    * @param HsMaProduct $to_product
    * @return boolean
    */
    public function copyAccessories($from_product, $to_product)
    {
        $flag = true;
        $accessories = HsMaProduct::getAccessoriesDiff($from_product, $to_product);
        if (!empty($accessories)) {
            foreach ($accessories as $accessory) {
                $accessory->id_product = (int) $to_product->id;
                $this->copyCartRule($from_product, $to_product, $accessory);
                unset($accessory->id);
                $flag &= $accessory->add();
            }
        }
        return $flag;
    }

    protected function copyCartRule($from_product, $to_product, $accessory)
    {
        $id_cart_rule = HsMaCartRule::getIdCartRuleByAccessoryProduct($accessory->id_accessory, $from_product->id);
        if ($id_cart_rule > 0) {
            $from_cart_rule = new CartRule($id_cart_rule);
            $accessory_group_product = new HsAccessoriesGroupProduct((int) $accessory->id_accessory_group_product);
            if (!Validate::isLoadedObject($accessory_group_product)) {
                return;
            }
            $cart_rule = new HsMaCartRule();
            $cart_rule->product = $to_product;
            $cart_rule->accessory = $accessory_group_product;
            if (Shop::isFeatureActive()) {
                $cart_rule->id_shops = array_intersect($this->getEnabledShops(), $this->context->employee->getAssociatedShops());
            }
            $cart_rule->addCartRule(0, $from_cart_rule->reduction_percent, $this->generateCartRuleNames($accessory_group_product), $this->getCartRuleDescription());
        }
    }
    
    /**
     *
     * @return string
     */
    public function getCartRuleDescription()
    {
        return $this->i18n['only_valid_when_buying_with_main_product'];
    }
    
    /**
     * Generate cart rule names (multiple language) for associated accessory
     * @param HsAccessoriesGroupProduct $accessory
     * <pre>
     * array(
     *  int => string // id_lang => name
     * )
     */
    public function generateCartRuleNames($accessory)
    {
        $languages = Language::getLanguages(false);
        $names = array();
        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = sprintf($this->i18n['discount_for_accessory'], $accessory->name[$lang['id_lang']]);
        }
        return $names;
    }
    
    /**
     * Get relative path to document file of module.
     *
     * @return string
     */
    public function getDocumentPath()
    {
        return $this->_path . 'readme_en.pdf';
    }
    
    /**
     * Delete all accessories after deleting accessory group.
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectHsAccessoriesGroupAbstractDeleteAfter($params)
    {
        $success = array();
        $accessories_group = $params['object'];
        if (!empty($accessories_group) && Validate::isLoadedObject($accessories_group)) {
            $success[] = HsAccessoriesGroupProductAbstract::deteleteProductAccessoriesByIdGroup($accessories_group->id);
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Delete all accessories after deleting product combination.
     * @param array $params
     * @return boolean
     */
    public function hookDeleteProductAttribute($params)
    {
        $success = array();
        $id_product = $params['id_product'];
        $id_product_attribute = $params['id_product_attribute'];
        if ($id_product && $id_product_attribute) {
            $success[] = HsAccessoriesGroupProductAbstract::deteleteProductAccessoriesByIdProduct($id_product, $id_product_attribute);
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Display popup accessory (for version 1.5, 1.6)
     * @return html
     */
    public function hookDisplayTop()
    {
        if ($this->isProductPage()) {
            $id_product = Tools::getValue('id_product', false);
            if (!empty($id_product)) {
                if (HsAccessoriesGroupAbstract::haveAccessories(array($id_product), (int) $this->context->language->id)) {
                    return $this->display($this->name . '.php', 'display_top.tpl');
                }
            }
        }
    }

    /**
     * Display popup accessory for Ps 1.7
     * @return html
     */
    public function hookDisplayNavFullWidth()
    {
        return $this->hookDisplayTop();
    }
    
    /**
     * Get the setting of accessory quantity
     * 0: Hide quantity box, sync accessories quantity + main product quantity
     * 1: Show quantity box, sync accessories quantity + main product quantity
     * 2: Show quantity box, allow customer to change accessory quantity
     * @return int
     */
    public function getOptionAcessoryQuantitySetting()
    {
        return (int) Configuration::get('HSMA_ALLOW_CUSTOMER_CHANGE_QTY') + (int) Configuration::get('HSMA_SHOW_CUSTOM_QUANTITY');
    }
}
