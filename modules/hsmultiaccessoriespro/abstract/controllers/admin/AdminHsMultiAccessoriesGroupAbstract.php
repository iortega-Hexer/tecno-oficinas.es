<?php
/**
 * An abstract admin controller of the module.
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once dirname(__FILE__) . '/../../classes/HsAccessoriesGroupAbstract.php';
require_once dirname(__FILE__) . '/../../classes/HsAccessoriesGroupProductAbstract.php';
require_once dirname(__FILE__) . '/../../classes/HsMaCartRule.php';
require_once dirname(__FILE__) . '/../../classes/HsMaSearch.php';

class AdminHsMultiAccessoriesGroupAbstract extends ModuleAdminController
{
    public $bootstrap = true;
    protected $position_identifier = 'id_accessory_group';

    /**
     * All javascript files which should be loaded.
     *
     * @var array
     */
    protected $module_media_js = array(
        'admin_product_setting.js',
        'admin_behavior.js',
        'admin_multi_accessories.js',
        'jquery-confirm.min.js',
        'admin_setting_multi_accessories.js',
        'hsma_filter_by_category.js',
        'hsma_display_style.js'
    );
    
    protected $module_media_css = array(
        'accessory_admin_tab.css',
        'adminpage.css',
        'jquery-confirm.min.css',
    );
    /**
     * Show result to view.
     *
     * @var type json
     */
    protected $ajax_json = array(
        'success' => false,
        'message' => null,
    );

    /**
     * Start time regenerate image.
     *
     * @var int
     */
    protected $start_time = 0;

    /**
     * Max time regenerate images.
     *
     * @var type
     */
    protected $max_execution_time = 18000;

    public function __construct()
    {
        $this->table = 'accessory_group';
        $this->className = 'HsAccessoriesGroupAbstract';
        $this->lang = true;
        parent::__construct();

        $this->_defaultOrderBy = 'position';

        // check status visit welcome page, if != 1 go to welcome page
        if ((int) Configuration::get($this->module->getKeyWelcomePage()) !== 1 && !$this->ajax) {
            Tools::redirectAdmin($this->context->link->getAdminLink($this->module->class_controller_admin_welcome_page));
        }
        $this->fields_list = array(
            'id_accessory_group' => array(
                'title' => $this->module->i18n['id'],
                'width' => 25,
            ),
            'name' => array(
                'title' => $this->module->i18n['group_name'],
                'width' => 'auto',
                'filter_key' => 'b!name',
            ),
            'active' => array(
                'title' => $this->module->i18n['active'],
                'width' => 40,
                'align' => 'center',
                'active' => 'status',
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'align' => 'center',
                'position' => 'position',
            ),
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->i18n['delete_selected_items'],
                'confirm' => $this->module->i18n['delete_selected_items'] . '?',
            ),
        );
        
        if ($this->module->isPrestashop16()) {
            // only support feature add multi accessories for multi product from version 1.6
            $this->fields_options = array(
                'hsma_add_accessories' => array(
                    'title' => $this->module->i18n['add_multi_accessories_for_multi_products'],
                    'icon' => 'icon-plus',
                    'fields' => $this->generateFormAddAccessories()
                ),
                'hsma_copy_accessories' => array(
                    'title' => $this->module->i18n['copy_default_PS_accessories_in_this_module'],
                    'icon' => 'icon-copy',
                    'fields' => $this->generateFormCopyAccessories()
                ),
                'hsma_replace_accessory' => array(
                    'title' => $this->module->i18n['replace_an_accessory_with_another_accessory'],
                    'icon' => 'icon-copy',
                    'fields' => $this->generateFormReplaceAccessory()
                ),
                'hsma_quick_fix' => array(
                    'title' => $this->module->i18n['quickly_fix_common_errors'],
                    'icon' => 'icon-bug',
                    'fields' => $this->generateFormQuickFix()
                )
            );
        }
    }
    
    protected function generateFormQuickFix()
    {
        return array(
            'HSMA_QUICK_FIX' => array(
                'type' => 'hsma_quick_fix',
                'is_prestashop16' => (int) $this->module->isPrestashop16(),
                'module_version' => $this->module->version,
                'document_url' => $this->module->getDocumentPath(),
                'module_author' => $this->module->author,
                'module_year' => '2013 - ' . date("Y"),
            ),
        );
    }
    protected function generateFormAddAccessories()
    {
        return array(
            'HSMA_ADD_ACCESSORIES' => array(
                'type' => 'hsma_add_accessores'
            ),
        );
    }
    
    protected function generateFormCopyAccessories()
    {
        return array(
            'HSMA_COPY_ACCESSORIES' => array(
                'type' => 'hsma_copy_accessories'
            ),
        );
    }
    protected function generateFormReplaceAccessory()
    {
        return array(
            'HSMA_REPLACE_ACCESSORY' => array(
                'type' => 'hsma_replace_accessory'
            ),
        );
    }
    /**
     * Render block categories tree
     * @param string $id_block_categories
     * @return html
     */
    protected function getCategoryTree($id_block_categories)
    {
        // Generate category selection tree
        $tree = new HelperTreeCategories($id_block_categories, $this->module->i18n['filter_by_category']);
        $tree->setId($id_block_categories);
        $tree->setUseSearch(true);
        $tree->setAttribute($id_block_categories, true)->setAttribute('base_url', preg_replace('#&id_category=[0-9]*#', '', self::$currentIndex) . '&token=' . $this->token)->setInputName('id-category')->setUseCheckBox(true)->setSelectedCategories(array(0));
        return $tree->setRootCategory((int) Category::getRootCategory()->id)->render();
    }

    /**
     * The last call for any ajax action if method "displayAjax[ActionName]" is not found
     */
    public function displayAjax()
    {
        exit(Tools::jsonEncode($this->ajax_json));
    }

    /**
     * Process save all settings.
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitSetting')) {
            foreach ($this->module->configuration_keys as $config_name => $config_validate) {
                if (!in_array($config_name, $this->module->configuration_lang_keys)) {
                    $config_validate = $config_validate; // fix validator ps not use
                    if (Validate::$config_validate(Tools::getValue($config_name))) {
                        Configuration::updateValue($config_name, Tools::getValue($config_name));
                    }
                }
            }
            $languages = $this->getLanguages();
            $title = array();
            $messages_available_later = array();
            $alert_message = array();
            foreach ($languages as $lang) {
                $title[$lang['id_lang']] = Tools::getValue('HSMA_TITLE_' . $lang['id_lang']);
                $messages_available_later[$lang['id_lang']] = Tools::getValue('HSMA_MESSAGE_AVAILABLE_LATER_' . $lang['id_lang']);
                if (Tools::getValue('HSMA_ALERT_MESSAGE_' . $lang['id_lang'])) {
                    $alert_message[$lang['id_lang']] = Tools::getValue('HSMA_ALERT_MESSAGE_' . $lang['id_lang']);
                }
            }

            Configuration::updateValue('HSMA_TITLE', $title);
            Configuration::updateValue('HSMA_MESSAGE_AVAILABLE_LATER', $messages_available_later);
            if (!empty($alert_message)) {
                Configuration::updateValue('HSMA_ALERT_MESSAGE', $alert_message);
            }

            $this->confirmations[] = $this->_conf[6];
        }
        parent::postProcess();
    }

    /**
     * Render all accessories groups.
     *
     * @return HTML string
     */
    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * Show form add a group.
     *
     * @return HTML string
     */
    public function renderForm()
    {
        if ($this->display == 'edit') {
            $this->toolbar_title = $this->module->i18n['edit_group'];
        } else {
            $this->toolbar_title = $this->module->i18n['add_a_new_accessory_group'];
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->i18n['group'],
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->i18n['name'],
                    'name' => 'name',
                    'lang' => true,
                    'size' => 33,
                    'hint' => $this->module->i18n['invalid_characters'] . ' <>;=#{}',
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->i18n['display_style'],
                    'name' => 'display_style',
                    'hint' => $this->module->i18n['define_how_accessories_look_like_at_product_page'],
                    'options' => array(
                        'query' => $this->module->getDisplayStyles(true),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => $this->module->isPrestashop16() ? 'switch' : 'radio',
                    'label' => $this->module->i18n['show_group_name'],
                    'hint' => $this->module->i18n['show_hide_group_name_at_the_front_end'],
                    'name' => 'show_name',
                    'required' => false,
                    'class' => !$this->module->isPrestashop16() ? 't' : '',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->i18n['enabled'],
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->i18n['disabled'],
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->i18n['collapse_expand_group'],
                    'name' => 'collapse_expand',
                    'default_value' => HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND,
                    'hint' => $this->module->i18n['collapse_expand_group_at_the_front_end'],
                    'options' => array(
                        'query' => $this->getGroupBehaviors(),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->i18n['buy_main_product_accessories_of_this_group_together'],
                    'name' => 'buy_together',
                    'default_value' => HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT,
                    'hint' => $this->module->i18n['tell_your_customers_that_they_need_to_buy_main_product_and_accessories_of_this_group_together'],
                    'options' => array(
                        'query' => $this->getGroupSettingBuyTogetherOptions(),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => $this->module->isPrestashop16() ? 'switch' : 'radio',
                    'label' => $this->module->i18n['active'],
                    'name' => 'active',
                    'required' => false,
                    'class' => !$this->module->isPrestashop16() ? 't' : '',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->i18n['enabled'],
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->i18n['disabled'],
                        ),
                    ),
                ),
            ),
        );
        $this->fields_form['submit'] = array(
            'title' => $this->module->i18n['save'],
        );

        return parent::renderForm();
    }

    /**
     *
     * @return array
     */
    protected function getGroupBehaviors()
    {
        $behaviors = array(
            array(
                'id' => HsMaDisplayStyle::DISPLAY_GROUPS_NONE,
                'name' => $this->module->i18n['no'],
                ),
            array(
                'id' => HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND,
                'name' => $this->module->i18n['expand'],
                ),
            array(
                'id' => HsMaDisplayStyle::DISPLAY_GROUPS_COLLAPSE,
                'name' => $this->module->i18n['collapse'],
                ),
        );
        return $behaviors;
    }
    
    /**
     *
     * @return array
     */
    protected function getGroupSettingBuyTogetherOptions()
    {
        $buy_together_options = array(
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT,
                'name' => $this->module->i18n['use_default'],
            ),
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_NO,
                'name' => $this->module->i18n['no'],
            ),
            array(
                'id' => HsMaProductSetting::BUY_TOGETHER_YES,
                'name' => $this->module->i18n['yes'],
            ),
        );

        return $buy_together_options;
    }

    /**
     * Save short name of an accessory.
     */
    public function ajaxProcessSaveName()
    {
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');
        $this->ajax_json['success'] = false;
        if ($id_accessory_group_product) {
            $accessory = new HsAccessoriesGroupProduct($id_accessory_group_product);
            if (!Validate::isLoadedObject($accessory)) {
                exit(Tools::jsonEncode($this->ajax_json));
            }

            $product_update = Tools::getValue('product_update');
            $names = Tools::getValue('names');
            if (!$product_update) {
                $this->ajax_json['success'] = HsAccessoriesGroupProduct::updateGlobalName($accessory->id_accessory, $names);
            } else {
                $accessory->name = $names;
                $this->ajax_json['success'] = $accessory->update();
            }
            if ($this->ajax_json['success']) {
                $this->ajax_json['message'] = $this->module->i18n['data_saved'];
            }
        }
        die(Tools::jsonEncode($this->ajax_json));
    }

    /**
     * Create button add new accessory group.
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_accessory_group'] = array(
                'href' => self::$currentIndex . '&addaccessory_group&token=' . $this->token,
                'desc' => $this->module->i18n['add_a_new_accessory_group'],
                'icon' => 'process-icon-new',
            );
        }

        parent::initPageHeaderToolbar();
    }

    /**
     * Set Media file include when controller called.
     */
    public function setMedia($is_new_theme = false)
    {
        if ($this->module->isPrestashop17()) {
            parent::setMedia($is_new_theme);
        } else {
            parent::setMedia();
        }
        $this->addjQueryPlugin(array(
                'autocomplete',
                'tablednd',
        ));
        if (!empty($this->module_media_js) && is_array($this->module_media_js)) {
            $js_files = array();
            foreach ($this->module_media_js as $js_file) {
                $js_file = $this->module->getJsPath() . $js_file;
                $js_files[] = $js_file;
            }
            $this->addJS($js_files);
        }
        if (!empty($this->module_media_css) && is_array($this->module_media_css)) {
            $css_files = array();
            foreach ($this->module_media_css as $css_file) {
                $css_file = $this->module->getCssPath() . $css_file;
                if (version_compare(_PS_VERSION_, '1.6') === -1) {
                    $css_file = $css_file . '?' . $this->module->name . '=' . $this->module->version;
                }
                $css_files[] = $css_file;
            }
            $this->addCSS($css_files);
        }
    }

    /**
     * display list combinations.
     */
    public function displayAjaxAddAccessory()
    {
        $id_group = (int) Tools::getValue('id_group');
        $id_product = (int) Tools::getValue('id_product');
        $colspan = (int) Tools::getValue('colspan');
        $id_main_product = (int) Tools::getValue('id_main_product');
        $product = new HsMaProduct($id_product);
        if (!Validate::isLoadedObject($product)) {
            exit(false);
        }
        $accessory = new HsAccessoriesGroupProduct();
        $accessory->id_accessory = $id_product;
        $accessory->id_product = $id_main_product;
        $accessory->id_accessory_group = $id_group;
        $accessory->id_product_attribute = 0;
        $accessory->default_quantity = (int) $product->minimal_quantity;
        $accessory->min_quantity = (int) $accessory->default_quantity;
        $accessory->name = $product->name;
        $accessory->position = HsAccessoriesGroupProductAbstract::getHighestPosition($id_group, $id_main_product) + 1;
        $accessory->add();

        $image_products = Image::getImages($this->context->language->id, $id_product);
        $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
        $product_image_dir = _PS_PROD_IMG_DIR_;

        foreach ($image_products as $image_product) {
            $image = new Image($image_product['id_image']);
            $existing_image = $product_image_dir . $image->getExistingImgPath() . '.jpg';
            if (file_exists($existing_image) && filesize($existing_image)) {
                if (!file_exists($product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg')) {
                    ImageManager::resize($existing_image, $product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg', (int) $image_type->width, (int) $image_type->height);
                }
            }
        }

        $id_images = HsMaProduct::getCover($product->id);
        $accessory->image = '';
        if (!empty($id_images)) {
            $accessory->image = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($product->link_rewrite[$this->context->language->id], $id_images['id_image'], HsMaImageType::getFormatedNameByPsVersion('small')));
        }

        $accessory->combinations = HsMaProduct::getCombinations($id_product, $this->context->shop->id, $this->context->language->id);
        // @todo: Find this similar block in class HsAccessoriessGroupAbstract::getAccessoriesByGroups()
        // @todo: Move to another place so we can re-use
        foreach ($accessory->combinations as &$combination) {
            if (empty($combination['id_image'])) {
                if (!empty($id_images)) {
                    $combination['id_image'] = $id_images['id_image'];
                }
            }
            $combination['image'] = str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($product->link_rewrite[$this->context->language->id], $combination['id_image'], HsMaImageType::getFormatedNameByPsVersion('small')));
        }

        $accessory->id_accessory_group_product = $accessory->id;
        $languages = Language::getLanguages(true);
        $meta_language = array();
        foreach ($languages as $lang) {
            $meta_language[] = $lang['iso_code'];
        }
        $specific_price_output = null;
        $accessory->old_price = Product::getPriceStatic($accessory->id_accessory, true, $accessory->id_product_attribute, (int)_PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $this->context);
        $accessory->cart_rule = HsMaCartRule::getCartRule((array) $accessory, $id_main_product);
        $accessory->final_price = HsAccessoriesGroupAbstract::getFinalPrice($accessory->old_price, $accessory->cart_rule);
        $group = array('id_accessory_group' => $id_group);
        $this->context->smarty->assign(array(
            'id_group' => $id_group,
            'group' => $group,
            'id_product' => $id_product,
            'accessory' => (array) $accessory, // the template expects to accept array instead of object
            'is_prestashop16' => $this->module->isPrestashop16(),
            'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'show_custom_quantity' => (int) $this->getOptionShowCustomQuantity(),
            'languages' => $languages,
            'img_path' => $this->module->getImgPath(),
            'buy_together_default' => HsMaProductSetting::getBuyTogetherDefault($id_main_product),
            'colspan' => $colspan,
            'path_theme' => $this->module->isPrestashop17() ? '17/' : '',
        ));
        $data = array();
        $count_accessory = HsAccessoriesGroupAbstract::countAccessories($id_main_product, array($id_group));
        $data['is_stock_available'] = (int) $this->isStockAvailable((int) $id_product, (int) $accessory->id_product_attribute, (int) $accessory->default_quantity);
        $data['content'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/display_accessory_row.tpl');
        $data['xx_items_inside'] = sprintf($count_accessory > 1 ? $this->module->i18n['items_inside'] : $this->module->i18n['item_inside'], $count_accessory);
        $data['count_accessory'] = $count_accessory;
        $data['message'] = $this->module->i18n['the_accessory_has_been_successfully_added'];
        exit(Tools::jsonEncode($data));
    }

    /**
     * Checking product is out of stock.
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $quantity             default quantity
     *
     * @return bool
     */
    protected function isStockAvailable($id_product, $id_product_attribute, $quantity)
    {
        $flag = false;
        $stock_status = HsMaProduct::getStockStatus((int) $id_product, (int) $id_product_attribute, $this->context->shop);
        if (!empty($stock_status)) {
            if (Product::isAvailableWhenOutOfStock($stock_status['out_of_stock']) || (!Product::isAvailableWhenOutOfStock($stock_status['out_of_stock']) && $stock_status['quantity'] >= (int) $quantity)) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * Delete accessory.
     */
    public function displayAjaxDeleteAccessory()
    {
        $result = array();
        $result['success'] = false;
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');
        $colspan = (int) Tools::getValue('colspan');
        if ($id_accessory_group_product) {
            $accessory = new HsAccessoriesGroupProduct($id_accessory_group_product);
            if (Validate::isLoadedObject($accessory)) {
                $this->context->smarty->assign(array(
                    'colspan' => $colspan,
                ));
                $id_product = $accessory->id_product;
                $id_accessory_group = $accessory->id_accessory_group;
                $result['success'] = $accessory->delete();
                if ($result['success']) {
                    // delete cart rule for this accessory
                    $id_cart_rule = HsMaCartRule::getIdCartRuleByAccessoryProduct($accessory->id_accessory, $accessory->id_product);
                    if ($id_cart_rule) {
                        $cart_rule = new HsMaCartRule($id_cart_rule);
                        if (Validate::isLoadedObject($cart_rule)) {
                            $cart_rule->delete();
                        }
                    }
                    $count_accessory = HsAccessoriesGroupAbstract::countAccessories($id_product, array($id_accessory_group));
                    $result['xx_items_inside'] = sprintf($count_accessory > 1 ? $this->module->i18n['items_inside'] : $this->module->i18n['item_inside'], $count_accessory);
                    $result['ids_accessory'] = $accessory->id_accessory . ':' . $accessory->id_product_attribute;
                    $result['id_group'] = $accessory->id_accessory_group;
                    $result['count_accessory'] = $count_accessory;
                    $result['content'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/abstract/views/templates/hook/display_no_accessory.tpl');
                    $result['message'] = $this->module->i18n['the_accessory_has_been_successfully_deleted'];
                }
            }
        }
        die(Tools::jsonEncode($result));
    }

    /**
     * Change product combination.
     */
    public function displayAjaxChangeProductCombination()
    {
        $id_group = (int) Tools::getValue('id_group');
        $id_product = (int) Tools::getValue('id_product');
        $id_main_product = (int) Tools::getValue('id_main_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        $id_accessory_group_product = HsAccessoriesGroupProduct::getIdAccessoryGroupProduct($id_product, $id_main_product, $id_group);
        $accessory = new HsAccessoriesGroupProduct($id_accessory_group_product);
        $accessory->id_product_attribute = $id_product_attribute;
        $result = $accessory->update();
        $specific_price_output = null;
        $accessory->old_price = Product::getPriceStatic($accessory->id_accessory, true, $accessory->id_product_attribute, (int)_PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $this->context);
        $accessory->cart_rule = HsMaCartRule::getCartRule((array) $accessory, $id_main_product);
        $accessory->final_price = HsAccessoriesGroupAbstract::getFinalPrice($accessory->old_price, $accessory->cart_rule);
        $data = array();
        $data['is_stock_available'] = (int) $this->isStockAvailable((int) $id_product, (int) $accessory->id_product_attribute, (int) $accessory->default_quantity);
        $data['success'] = $result;
        if ($data['success']) {
            $data['message'] = $this->module->i18n['data_saved'];
        }
        $data['old_price'] = Tools::displayPrice($accessory->old_price);
        $data['final_price'] = Tools::displayPrice($accessory->final_price);
        exit(Tools::jsonEncode($data));
    }

    /**
     * Change display style.
     */
    public function displayAjaxChangeDisplayStyle()
    {
        $id_group = (int) Tools::getValue('id_group');
        $display_style = (int) Tools::getValue('display_style');
        if (!$id_group) {
            die(Tools::jsonEncode(false));
        }
        $group = new HsAccessoriesGroupAbstract($id_group);

        if (!Validate::isLoadedObject($group)) {
            die(Tools::jsonEncode(false));
        }
        $group->display_style = $display_style;
        $result = $group->update();
        die(Tools::jsonEncode($result));
    }

    /**
     * Change discount value of accessory product
     */
    public function ajaxProcessChangeDiscountValue()
    {
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');
        $id_main_product = (int) Tools::getValue('id_main_product');
        $discount_type = (int) Tools::getValue('discount_type', 0);// 0 means '%'
        $discount_value = (float) Tools::getValue('discount_value');
        $multi_adding = (int) Tools::getValue('multi_adding');

        $accessory = new HsAccessoriesGroupProduct((int) $id_accessory_group_product);
        if (!$id_accessory_group_product || !Validate::isLoadedObject($accessory)) {
            return;
        }

        $product = new Product($id_main_product, false, $this->context->language->id);
        if (!$id_main_product || !Validate::isLoadedObject($product)) {
            return;
        }

        $this->ajax_json['success'] = true;
        $this->deleteExistingCartRules($product, $accessory, $multi_adding);
        $this->ajax_json['message'] = $this->module->i18n['data_saved'];
        $this->ajax_json['id_accessory'] = $accessory->id_accessory;
        $final_price = $accessory->getAccessoryPrice();
        if ($discount_value > 0) {
            $cart_rule = new HsMaCartRule();
            $cart_rule->product = $product;
            $cart_rule->accessory = $accessory;
            $cart_rule->multi_adding = $multi_adding;
            if (Shop::isFeatureActive()) {
                $cart_rule->id_shops = array_intersect($this->module->getEnabledShops(), $this->context->employee->getAssociatedShops());
            }
            $cart_rule->addCartRule($discount_type, $discount_value, $this->module->generateCartRuleNames($accessory), $this->module->getCartRuleDescription());
            if (Validate::isLoadedObject($cart_rule)) {
                $final_price = $this->getDiscountedPrice($accessory, $cart_rule);
            } else {
                $this->ajax_json['success'] = false;
                $this->ajax_json['message'] = $this->module->i18n['can_not_save_cart_rule'];
            }
        }
        $this->ajax_json['final_price'] = Tools::displayPrice($final_price);
    }

    /**
     *
     * @param Product $product main product
     * @param HsAccessoriesGroupProduct $accessory current accessory associated with the main product
     * @return boolean
     */
    protected function deleteExistingCartRules(Product $product, HsAccessoriesGroupProduct $accessory, $multi_adding)
    {
        $id_cart_rule = (int) HsMaCartRule::getIdCartRuleByAccessoryProduct($accessory->id_accessory, $product->id);
        $cart_rule = new HsMaCartRule($id_cart_rule);
        $success = array();
        if (Validate::isLoadedObject($cart_rule)) {
            $success[] = $cart_rule->deleteMulti($product->id, $accessory->id_accessory, $multi_adding);
        }
        if ($multi_adding == 1) {
            $other_cart_rules = HsMaCartRule::getOtherIdCartRuleByAccessory($accessory->id_accessory, array($id_cart_rule));
            if (!empty($other_cart_rules)) {
                foreach ($other_cart_rules as $other_cart_rule) {
                    $other_cart_rule = new HsMaCartRule($other_cart_rule['id_cart_rule']);
                    if (Validate::isLoadedObject($other_cart_rule)) {
                        $success[] = $other_cart_rule->delete();
                    }
                }
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * get the price after discount of an accessory
     * @param HsAccessoriesGroupProduct $accessory
     * @param HsMaCartRule $cart_rule
     *
     * @return float
     */
    protected function getDiscountedPrice(HsAccessoriesGroupProduct $accessory, HsMaCartRule $cart_rule)
    {
        $base_price = $accessory->getAccessoryPrice();
        if ($cart_rule->reduction_percent) {
            $final_price = $base_price - ($base_price * ($cart_rule->reduction_percent / 100));
        } elseif ($cart_rule->reduction_amount) {
            $final_price = $base_price - $cart_rule->reduction_amount;
        } else {
            $final_price = $base_price;
        }
        return $final_price;
    }
    
    /**
     * Change minimum quantity of accessory.
     */
    public function displayAjaxChangeDefaultQuantity()
    {
        $json_data = array();
        $json_data['success'] = false;
        $quantity = (int) Tools::getValue('quantity');
        if ($quantity < 0) {
            $quantity = 0;
        }
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');

        if (!$id_accessory_group_product) {
            exit(Tools::jsonEncode($json_data));
        }
        $accessory = new HsAccessoriesGroupProduct((int) $id_accessory_group_product);
        if (!Validate::isLoadedObject($accessory)) {
            exit(Tools::jsonEncode($json_data));
        }

        $is_stock_available = (int) $this->isStockAvailable((int) $accessory->id_accessory, (int) $accessory->id_product_attribute, (int) $quantity);
        $json_data['is_stock_available'] = $is_stock_available;
        if ($is_stock_available) {
            $accessory->default_quantity = (int) $quantity;
            $json_data['success'] = $accessory->update();
            $json_data['message'] = $this->module->i18n['data_saved'];
        }
        exit(Tools::jsonEncode($json_data));
    }

    /**
     * Change required buy product & accessory together.
     */
    public function displayAjaxChangeAccessorySettingBuyTogether()
    {
        $required = (int) Tools::getValue('required');
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');
        $this->ajax_json['success'] = $this->updateAccessoriesGroupProduct($id_accessory_group_product, $required);
        $this->ajax_json['message'] = $this->module->i18n['data_saved'];
        exit(Tools::jsonEncode($this->ajax_json));
    }

    /**
     * Update object AccessoriesGroupProduct.
     *
     * @param int $id_accessory_group_product
     * @param int $required
     *
     * @return bool
     */
    protected function updateAccessoriesGroupProduct($id_accessory_group_product, $required)
    {
        if (!$id_accessory_group_product) {
            return false;
        }
        $accessory = new HsAccessoriesGroupProduct((int) $id_accessory_group_product);
        if (!Validate::isLoadedObject($accessory)) {
            return false;
        }
        $accessory->required = (int) $required;

        return $accessory->update();
    }

    /**
     * Process change position of accessories.
     */
    public function ajaxProcessUpdateAccessoryProductPosition()
    {
        if ($this->tabAccess['edit'] === '0') {
            return die(Tools::jsonEncode(array('error' => $this->module->i18n['you_do_not_have_the_right_permission'])));
        }
        $flag = false;
        if ($accessories_positions = Tools::getValue('accessories_positions')) {
            $flag = true;
            $accessories_positions = Tools::stripslashes($accessories_positions);
            $array_accessories_positions = Tools::jsonDecode($accessories_positions, true);
            foreach ($array_accessories_positions as $string_ids => $position) {
                $list_ids = explode('_', $string_ids);
                if (isset($list_ids[1])) {
                    $accessory = new HsAccessoriesGroupProduct((int) $list_ids[1]);
                    $accessory->position = (int) $position;
                    $flag &= $accessory->update();
                }
            }
        }
        if ($flag) {
            $this->ajax_json['success'] = true;
            $this->ajax_json['message'] = $this->module->i18n['update_successful'];
        } else {
            $this->jsonError(Tools::displayError($this->module->i18n['an_error_occurred_while_attempting_to_move_this_accessory']));
        }
    }

    /**
     * Update accessory group.
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int) Tools::getValue('way');
        $id_accessory_group = (int) Tools::getValue('id');
        $accessory_group_positions = Tools::getValue('accessory_group');
        if (is_array($accessory_group_positions)) {
            foreach ($accessory_group_positions as $position => $accessory_group) {
                $group = explode('_', $accessory_group);
                if ((isset($group[1]) && isset($group[2])) && (int) $group[2] === $id_accessory_group) {
                    $hs_accessories_group = new HsAccessoriesGroupAbstract((int) $group[2]);
                    if (Validate::isLoadedObject($hs_accessories_group)) {
                        if (isset($position) && $hs_accessories_group->updatePosition($way, $position)) {
                            $this->ajax_json['success'] = true;
                        }
                    }
                    break;// only need to detect the changed group, other groups' positions will be updated accordingly in HsAccessoriesGroupAbstract::updatePosition()
                }
            }
        }
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    /**
     * Change min quantity of accessory.
     */
    public function ajaxProcessChangeMinQuantity()
    {
        $default_quantity = (int) Tools::getValue('default_quantity');
        $min_quantity = (int) Tools::getValue('min_quantity');
        $id_accessory_group_product = (int) Tools::getValue('id_accessory_group_product');
        
        if ($default_quantity < 0 || $min_quantity < 0 || empty($id_accessory_group_product)) {
            $this->ajax_json['message'] = $this->module->i18n['oops_something_goes_wrong'];
            return;
        }
        
        $accessory_group_product = new HsAccessoriesGroupProduct($id_accessory_group_product);
        if (!Validate::isLoadedObject($accessory_group_product)) {
            $this->ajax_json['message'] = $this->module->i18n['invalid_product'];
            return;
        }
        
        $this->ajax_json['is_stock_available'] = (int) $this->isStockAvailable((int) $accessory_group_product->id_accessory, (int) $accessory_group_product->id_product_attribute, (int) $min_quantity);
        if ($this->ajax_json['is_stock_available']) {
            $accessory_group_product->default_quantity = $default_quantity;
            $accessory_group_product->min_quantity = $min_quantity;
            $this->ajax_json['success'] = $accessory_group_product->update();
            if ($this->ajax_json['success']) {
                $this->ajax_json['message'] = $this->module->i18n['data_saved'];
            } else {
                $this->ajax_json['message'] = $this->module->i18n['oops_cannot_update_accessory'];
            }
        } else {
            $this->ajax_json['message'] = $this->module->i18n['accessory_is_out_of_stock'];
        }
    }
    
    /**
     * Copy accessories form product
     */
    public function ajaxProcessCopyAccessories()
    {
        $from_id_product  = (int)Tools::getValue('from_id_product');
        $to_id_product = (int)Tools::getValue('to_id_product');
        $keep_accessories   = (int)Tools::getValue('keep_accessories');
        
        $from_product = new HsMaProduct($from_id_product);
        if (!Validate::isLoadedObject($from_product)) {
            $this->ajax_json['message'] = $this->module->i18n['invalid_product'];
            return;
        }
        $to_product = new HsMaProduct($to_id_product);
        if (!Validate::isLoadedObject($to_product)) {
            $this->ajax_json['message'] = $this->module->i18n['invalid_product'];
            return;
        }
        if (!$keep_accessories) {
            // delete cart rule
            $this->deleteCartRules($to_product->id);
            $to_product->deleteAccessories();
        }
        $this->ajax_json['success'] = $this->module->copyAccessories($from_product, $to_product);
        $this->ajax_json['is_prestashop17'] = $this->module->isPrestashop17();
        if ($this->ajax_json['success']) {
            $this->ajax_json['product_link'] = $this->context->link->getAdminLink('AdminProducts') . '&' . 'updateproduct&id_product=' . (int) $to_id_product . '&key_tab=Module' . Tools::toCamelCase($this->module->name, true) . '&conf=4';
        } else {
            $this->ajax_json['message'] = $this->module->i18n['cannot_copy_accessories'];
        }
    }
    
    /**
     * @param int $id_main_product
     * @return boolean
     */
    protected function deleteCartRules($id_main_product)
    {
        $success = array();
        $id_cart_rules = HsMaCartRule::getIdCartRuleByIdProduct($id_main_product);
        if (!empty($id_cart_rules)) {
            foreach ($id_cart_rules as $id_cart_rule) {
                $cart_rule = new HsMaCartRule((int) $id_cart_rule['id_cart_rule']);
                if (Validate::isLoadedObject($cart_rule)) {
                    $success[] = $cart_rule->delete();
                }
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * @see parent::initContent()
     */
    public function initContent()
    {
        if ($this->module->isPrestashop16()) {
            $st_hsmultiaccessories = array(
                'url' => $this->module->urls,
                'lang' => $this->module->i18n,
            );
            $this->context->smarty->assign(array(
                'is_prestashop16' => (int) $this->module->isPrestashop16(),
                'st_hsmultiaccessories' => Tools::jsonEncode($st_hsmultiaccessories),
                'category_tree_product' => $this->getCategoryTree('is_category_filter_product'),
                'category_tree_accessory' => $this->getCategoryTree('is_category_filter_accessory'),
                'groups' => HsAccessoriesGroupAbstract::getGroups($this->context->language->id),
                'buy_together_default' => HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT,
                'is_ps17' => (int) $this->module->isPrestashop17(),
                'is_product_page' => false
            ));
        }
        parent::initContent();
        $token = Tools::getAdminTokenLite('AdminHsMultiAccessoriesGroupPro');
        $this->context->smarty->assign('token', $token);
    }
    
    /**
     * process filter by categories
     */
    public function ajaxProcessFilterByCategories()
    {
        $this->ajax_json['success'] = false;
        $product_selected_id_categories = Tools::getValue('product_id_categories');
        $accessory_selected_id_categories = Tools::getValue('accessory_id_categories');
        $id_group = Tools::getValue('id_group');
        if (!$product_selected_id_categories || !$accessory_selected_id_categories) {
            exit;
        }
        $product_id_categories = $this->getChildrenCategories($product_selected_id_categories);
        $accessory_id_categories = $this->getChildrenCategories($accessory_selected_id_categories);
        
        if (empty($product_id_categories) || empty($accessory_id_categories)) {
            exit;
        }
        $products = array();
        $accessories = array();
        if ($product_id_categories) {
            $products = HsMaSearch::searchProductsByCategories($product_id_categories, $this->context);
        }
        if ($accessory_id_categories) {
            $accessories = HsMaSearch::searchProductsByCategories($accessory_id_categories, $this->context);
        }
        $id_products = $this->getIdProducts($products);
        
        if (!empty($id_products)) {
            $product_accessories = HsAccessoriesGroupAbstract::getAccessoriesByIdGroup($id_group, $id_products, false, (int) $this->context->language->id);
            $group_accessories = array();
            if (!empty($product_accessories)) {
                $group_accessories = $product_accessories[$id_group];
            }
            if (!empty($group_accessories)) {
                foreach ($products as &$product) {
                    $is_added = array();
                    foreach ($group_accessories as $accessory) {
                        $id_product_id_accessories = $accessory['id_product'].'_'.$accessory['id_accessory'];
                        if ($product['id_product'] == $accessory['id_product'] && !in_array($id_product_id_accessories, $is_added)) {
                            $product['accessories'][] = $accessory;
                            $is_added[] = $id_product_id_accessories;
                        }
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'groups' => HsAccessoriesGroupAbstract::getGroupById((int)$id_group, (int) $this->context->language->id),
            'show_custom_quantity' => (int) $this->getOptionShowCustomQuantity(),
            'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'is_prestashop16' => (int) $this->module->isPrestashop16(),
            'is_ps17' => (int) $this->module->isPrestashop17(),
            'buy_together_default' => HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT,
            'img_path' => $this->module->getImgPath(),
            'products' => $products,
            'accessories' => $accessories,
            'id_lang' => $this->context->language->id,
            'link' => $this->context->link,
            'use_tax' => Configuration::get('PS_TAX') && !Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer),
        ));
        $this->ajax_json['success'] = true;
        $template_path = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/product_list.tpl';
        $this->ajax_json['data']['html'] = $this->context->smarty->fetch($template_path);
    }
    
    /**
     * Get children categories by id parent categories
     * @param array $id_categories
     * @return array
     */
    protected function getChildrenCategories($id_categories)
    {
        $children_id_categories = array();
        foreach ($id_categories as $id_category) {
            $category = new Category($id_category);
            $categories = $category->getAllChildren();
            foreach ($categories as $cat) {
                $children_id_categories[] = $cat->id_category;
            }
        }
        return array_unique(array_merge($children_id_categories, $id_categories));
    }
    
    /**
     * Get id_product from list products
     * @param array $products
     * @return array id_products
     */
    protected function getIdProducts($products)
    {
        $id_products = array();
        if (!empty($products)) {
            foreach ($products as $product) {
                $id_products[] = (int) $product['id_product'];
            }
        }
        return $id_products;
    }
    
    public function displayAjaxAssignAccessories()
    {
        $id_products = explode(',', Tools::getValue('id_products'));
        $id_accessories = explode(',', Tools::getValue('id_accessories'));
        $id_group = Tools::getValue('id_group');
        $this->ajax_json['success'] = false;
        if (empty($id_products) || empty($id_accessories) || !$id_group) {
            exit(Tools::jsonEncode($this->ajax_json));
        }
        $this->ajax_json['content'] = array();
        $this->ajax_json['success'] = true;
        $this->ajax_json['message'] = $this->module->i18n['data_saved'];
        foreach ($id_products as $id_product) {
            $id_accessories_exist = HsAccessoriesGroupProduct::getIdAccessoriesByGroupProduct($id_group, $id_product);
            $id_accessories_without_id_product = array_diff($id_accessories, array($id_product));
            $id_accessories_to_add = !empty($id_accessories_exist) ? array_diff($id_accessories_without_id_product, $id_accessories_exist) : $id_accessories_without_id_product;
            if (!empty($id_accessories_to_add)) {
                $this->ajax_json['content'][$id_product] = $this->proccessAssignAccessories($id_product, $id_accessories_to_add, $id_group);
            }
        }
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    protected function proccessAssignAccessories($id_main_product, $id_accessories, $id_group)
    {
        foreach ($id_accessories as $id_accessory) {
            $product = new HsMaProduct($id_accessory);
            if (Validate::isLoadedObject($product)) {
                $accessory = new HsAccessoriesGroupProduct();
                $accessory->id_accessory = $id_accessory;
                $accessory->id_product = $id_main_product;
                $accessory->id_accessory_group = $id_group;
                $accessory->id_product_attribute = 0;
                $accessory->default_quantity = (int) $product->minimal_quantity;
                $accessory->min_quantity = (int) $accessory->default_quantity;
                $accessory->name = $product->name;
                $accessory->position = HsAccessoriesGroupProductAbstract::getHighestPosition($id_group, $id_main_product) + 1;
                $accessory->add();

                $image_products = Image::getImages($this->context->language->id, $id_accessory);
                $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
                $product_image_dir = _PS_PROD_IMG_DIR_;

                foreach ($image_products as $image_product) {
                    $image = new Image($image_product['id_image']);
                    $existing_image = $product_image_dir . $image->getExistingImgPath() . '.jpg';
                    if (file_exists($existing_image) && filesize($existing_image)) {
                        if (!file_exists($product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg')) {
                            ImageManager::resize($existing_image, $product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg', (int) $image_type->width, (int) $image_type->height);
                        }
                    }
                }

                $id_images = HsMaProduct::getCover($product->id);
                $accessory->image = '';
                if (!empty($id_images)) {
                    $accessory->image = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($product->link_rewrite[$this->context->language->id], $id_images['id_image'], HsMaImageType::getFormatedNameByPsVersion('small')));
                }

                $accessory->combinations = HsMaProduct::getCombinations($id_accessory, $this->context->shop->id, $this->context->language->id);
                // @todo: Find this similar block in class HsAccessoriessGroupAbstract::getAccessoriesByGroups()
                // @todo: Move to another place so we can re-use
                foreach ($accessory->combinations as &$combination) {
                    if (empty($combination['id_image'])) {
                        if (!empty($id_images)) {
                            $combination['id_image'] = $id_images['id_image'];
                        }
                    }
                    $combination['image'] = str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($product->link_rewrite[$this->context->language->id], $combination['id_image'], HsMaImageType::getFormatedNameByPsVersion('small')));
                }

                $accessory->id_accessory_group_product = $accessory->id;
                $specific_price_output = null;
                $accessory->old_price = Product::getPriceStatic($accessory->id_accessory, true, $accessory->id_product_attribute, (int)_PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $this->context);
                $accessory->cart_rule = HsMaCartRule::getCartRule((array) $accessory, $id_main_product);
                $accessory->final_price = HsAccessoriesGroupAbstract::getFinalPrice($accessory->old_price, $accessory->cart_rule);
            }
        }
        $product_accessories = HsAccessoriesGroupAbstract::getAccessoriesByIdGroup($id_group, array($id_main_product), false, (int) $this->context->language->id);
        $group_accessories = array();
        if (!empty($product_accessories)) {
            $group_accessories = $product_accessories[$id_group];
        }

        $this->context->smarty->assign(array(
                'groups' => HsAccessoriesGroupAbstract::getGroupById((int)$id_group, (int) $this->context->language->id),
                'id_main_product' => $id_main_product,
                'product_accessories' => $group_accessories,
                'is_prestashop16' => $this->module->isPrestashop16(),
                'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
                'show_custom_quantity' => (int) $this->getOptionShowCustomQuantity(),
                'id_lang' => $this->context->language->id,
                'img_path' => $this->module->getImgPath(),
                'is_ps17' => (int) $this->module->isPrestashop17(),
                'buy_together_default' => HsMaProductSetting::getBuyTogetherDefault($id_main_product),
            ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/setting_display_accessory.tpl');
    }
    
    /**
     * In the back office, always show default quantity field. (Fixed = 1)
     * @return int
     */
    protected function getOptionShowCustomQuantity()
    {
        return 1;
    }
    
    /**
     * Copy accessories of PS to this module
     */
    public function displayAjaxCopyAccessories()
    {
        $id_group = (int) Tools::getValue('id_group');
        if (!$id_group) {
            exit(Tools::jsonEncode($this->ajax_json));
        }
        $success = array();
        $products_accessories = HsMaProduct::getDefaultPSProductsAccessories();
        if (!empty($products_accessories)) {
            foreach ($products_accessories as $product_accessories) {
                $id_accessories = explode('_', $product_accessories['id_accessories']);
                $success[] = $this->proccessCopyAccessories($product_accessories['id_product_1'], $id_accessories, $id_group);
            }
        }
        $this->ajax_json['message'] = $this->module->i18n['the_accessory_has_been_successfully_copied'];
        $this->ajax_json['success'] = array_sum($success) >= count($success);
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    
    protected function proccessCopyAccessories($id_main_product, $id_accessories, $id_group)
    {
        $success = array();
        $isset_accessories = HsAccessoriesGroupProductAbstract::getIdAccessoriesByGroupProduct($id_group, $id_main_product);
        if (!empty($id_accessories)) {
            foreach ($id_accessories as $id_accessory) {
                if (!in_array($id_accessory, $isset_accessories)) {
                    $product = new HsMaProduct($id_accessory);
                    if (Validate::isLoadedObject($product)) {
                        $accessory = new HsAccessoriesGroupProduct();
                        $accessory->id_accessory = (int) $id_accessory;
                        $accessory->id_product = (int) $id_main_product;
                        $accessory->id_accessory_group = (int) $id_group;
                        $accessory->id_product_attribute = 0;
                        $accessory->default_quantity = (int) $product->minimal_quantity;
                        $accessory->min_quantity = (int) $accessory->default_quantity;
                        $accessory->name = $product->name;
                        $success[] = $accessory->save();
                    }
                }
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Delete all the accessories in module Multi Accessories Pro
     */
    public function displayAjaxDeleteOldAccessories()
    {
        $this->ajax_json['content'] = array();
        $this->ajax_json['success'] = HsAccessoriesGroupProductAbstract::deteleteAllAccesories();
        $this->ajax_json['message'] = $this->module->i18n['the_accessory_has_been_successfully_deleted'];
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    /**
     * Replace accessory
     */
    public function displayAjaxReplaceAccessory()
    {
        $this->ajax_json['success'] = true;
        $old_id_accessory = (int) Tools::getValue('old_id_accessory');
        $new_id_accessory = (int) Tools::getValue('new_id_accessory');
        $new_accessory = new Product($new_id_accessory, false);
        if (Validate::isLoadedObject($new_accessory)) {
            if (HsAccessoriesGroupProduct::updateGlobalName($old_id_accessory, $new_accessory->name)) {
                $this->ajax_json['success'] = HsAccessoriesGroupProduct::replaceAccessory($old_id_accessory, $new_id_accessory);
            }
        }
        if ($this->ajax_json['success']) {
            $this->ajax_json['message'] = $this->module->i18n['the_accessory_has_been_successfully_replaced'];
        } else {
            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_while_replacing_the_accessory'];
        }
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    /**
     * Render accessory images
     */
    public function displayAjaxRenderAccessoryImages()
    {
        // Check and add image type
        $this->ajax_json['success'] = true;
        if ($this->addNewImageType()) {
            $this->renderAccessoriesImage();
            $this->ajax_json['message'] = $this->module->i18n['the_accessory_images_have_been_successfully_rendered'];
        } else {
            $this->ajax_json['success'] = false;
            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_while_adding_the_image_type'];
        }
        exit(Tools::jsonEncode($this->ajax_json));
    }
    
    /**
     * Use this function for the case: The accessories images are missing
     *
     */
    protected function renderAccessoriesImage()
    {
        $accessories = HsAccessoriesGroupProduct::getAllAccessories();
        if (empty($accessories)) {
            return;
        }
        foreach ($accessories as $accessory) {
            $id_accessory = $accessory['id_accessory'];
            $image_products = Image::getImages($this->context->language->id, $id_accessory);
            $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
            $product_image_dir = _PS_PROD_IMG_DIR_;
            if (!empty($image_products)) {
                foreach ($image_products as $image_product) {
                    $image = new Image($image_product['id_image']);
                    $existing_image = $product_image_dir . $image->getExistingImgPath() . '.jpg';
                    if (file_exists($existing_image) && filesize($existing_image)) {
                        if (!file_exists($product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg')) {
                            ImageManager::resize($existing_image, $product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg', (int) $image_type->width, (int) $image_type->height);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Use this function for the case: The accessories images are missing
     * Add the new image type for accessories
     * @return boolean
     */
    protected function addNewImageType()
    {
        $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
        $add_new_image_type = true;
        if (!Validate::isLoadedObject($image_type)) {
            $image_type->name = 'hsma_default';
            $image_type->width = 55;
            $image_type->height = 55;
            $image_type->products = 1;
            $add_new_image_type = $image_type->add() && Configuration::updateValue('HSMA_IMAGE_TYPE', $image_type->name) && Configuration::updateValue('HSMA_ID_IMAGE_TYPE', $image_type->id);
        }
        return $add_new_image_type;
    }
    
    /**
     * Register hook displayAdminListBefore to show block module settings.
     */
    public function displayAjaxRenderBlockModuleSetting()
    {
        $this->ajax_json['success'] = $this->module->registerHook('displayAdminListBefore');
        if ($this->ajax_json['success']) {
            $this->ajax_json['message'] = $this->module->i18n['the_block_module_setting_has_been_successfully_registed'];
        } else {
            $this->ajax_json['message'] = $this->module->i18n['an_error_occurred_while_registering_block_module_setting'];
        }
        exit(Tools::jsonEncode($this->ajax_json));
    }
}
