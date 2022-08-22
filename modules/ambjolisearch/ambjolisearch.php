<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced search (AmbJoliSearch)
 *   @file       ambjolisearch.php
 *   @subject    script principal pour gestion du module (install/config/hook)
 *   Support by mail: support@ambris.com
 */

if (!class_exists('AmbJolisearchModule')) {
    require_once 'classes/AmbJolisearchModule.php';
}

if (!class_exists('AmbIndexation')) {
    require_once 'classes/AmbIndexation.php';
}

if (!defined('PS_SEARCH_START')) {
    define('PS_SEARCH_START', 'PS_SEARCH_START');
}

define('AJS_MAX_ITEMS_KEY', 'AAJJS_MAX_ITEMS');
define('AJS_MAX_MANUFACTURERS_KEY', 'AAJJS_MAX_MANUFACTURERS');
define('AJS_MAX_CATEGORIES_KEY', 'AAJJS_MAX_CATEGORIES');
define('AJS_MAX_PRODUCTS_KEY', 'AAJJS_MAX_PRODUCTS');
define('AJS_PRODUCTS_PRIORITY_KEY', 'AAJJS_PRODUCTS_PRIORITY');
define('AJS_MANUFACTURERS_PRIORITY_KEY', 'AAJJS_MANUFACTURERS_PRIORITY');
define('AJS_CATEGORIES_PRIORITY_KEY', 'AAJJS_CATEGORIES_PRIORITY');
define('AJS_INSTALLATION_COMPLETE', 'AAJJSS_INSTALLATION_COMPLETE');
define('AJS_APPROXIMATIVE_SEARCH', 'AAJJSS_APPROXIMATIVE_SEARCH');
define('AJS_APPROXIMATION_LEVEL', 'AAJJSS_APPROXIMATION_LEVEL');
define('AJS_MORE_RESULTS_STRING', 'AAJJSS_MORE_RESULTS_STRING');
define('AJS_MORE_RESULTS_CONFIG', 'AAJJSS_MORE_RESULTS_CONFIG');
define('AJS_SHOW_PRICES', 'AAJJSS_SHOW_PRICES');
define('AJS_SHOW_FEATURES', 'AAJJSS_SHOW_FEATURES');
define('AJS_SHOW_CATEGORIES', 'AAJJSS_SHOW_CATEGORIES');
define('AJS_SHOW_CAT_DESC', 'AAJJSS_SHOW_CAT_DESC');
define('AJS_ENABLE_AC_PHONE', 'AAJJSS_ENABLE_AC_PHONE');
define('AJS_DISABLE_AC', 'AAJJSS_DISABLE_AC');
define('AJS_MULTILANG_SEARCH', 'AAJJSS_MULTILANG_SEARCH');
define('AJS_JOLISEARCH_THEME', 'AAJJSS_JOLISEARCH_THEME');
define('AJS_PRODUCT_THUMB_NAME', 'AAJJSS_PRODUCT_THUMB_NAME');
define('AJS_MANUFACTURER_THUMB_NAME', 'AAJJSS_MANUFACTURER_THUMB_NAME');
define('AJS_CATEGORY_THUMB_NAME', 'AAJJSS_CATEGORY_THUMB_NAME');
define('AJS_ALLOW_FILTER_RESULTS', 'AAJJSS_ALLOW_FILTER_RESULTS');
define('AJS_DROPDOWN_LIST_POSITION', 'AAJJSS_DROPDOWN_LIST_POSITION');
define('AJS_ONLY_DEFAULT_CATEGORIES', 'AAJJSS_ONLY_DEFAULT_CATEGORIES');
define('AJS_ONLY_LEAF_CATEGORIES', 'AAJJSS_ONLY_LEAF_CATEGORIES');

define('AJS_DISPLAY_CATEGORY', 'AAJJSS_DISPLAY_CATEGORY');
define('AJS_DISPLAY_MANUFACTURER', 'AAJJSS_DISPLAY_MANUFACTURER');

define('AJS_SHOW_PARENT_CATEGORY', 'AAJJSS_SHOW_PARENT_CATEGORY');
define('AJS_FILTER_ON_PARENT_CATEGORY', 'AAJJSS_FILTER_ON_PARENT_CATEGORY');
define('AJS_SEARCH_IN_SUBCATEGORIES', 'AAJJSS_SEARCH_IN_SUBCATEGORIES');

define('AJS_USE_APPROXIMATIVE_FOR_REFERENCES', 'AAJJSS_USE_APPROXIMATIVE_FOR_REFERENCES');
define('AJS_SEARCH_ALL_TERMS', 'AAJJSS_SEARCH_ALL_TERMS');
define('AJS_ALSO_TRY_OR_COMPARATOR', 'AAJJSS_ALSO_TRY_OR_COMPARATOR');
define('AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK', 'AAJJSS_ONLY_SEARCH_PRODUCTS_IN_STOCK');

/* 1.6 specific parameters */
define('AJS_COMPAT', 'AAJJSS_COMPAT');
define('AJS_BLOCKSEARCH_CSS', 'AAJJSS_BLOCKSEARCH_CSS');
define('AJS_USE_STD_SEARCH_BAR', 'AAJJSS_USE_STD_SEARCH_BAR');


require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/definitions.php';
require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/AmbJolisearchModuleProxy.php';

class AmbJoliSearch extends AmbJolisearchModuleProxy
{
    public $ps17 = false;
    public $ps16 = false;
    public $ps15 = false;

    const INSTALL_SQL_FUNCTIONS_FILE = 'functions.sql';

    const INSTALL_SQL_TABLES_FILE = 'tables.sql';

    public static $theme_settings = array(
        'autocomplete' => array(
            'use_template' => false,
        ),
        'modern' => array(
            'use_template' => false,
        ),
        'finder' => array(
            'use_template' => true,
        ),
    );

    public static $approximation_settings = array(
        0 => array(
                'hard_limit' => 0, //Do not accept a lvs higher than X
                'span' => 0, //How much distances should be shown
            ),
        1 => array(
                'hard_limit' => 1, //Do not accept a lvs higher than X
                'span' => 1, //How much distances should be shown
            ),
        2 => array(
                'hard_limit' => 2, //Do not accept a lvs higher than X
                'span' => 1, //How much distances should be shown
            ),
        3 => array(
                'hard_limit' => 2, //Do not accept a lvs higher than X
                'span' => 2, //How much distances should be shown
            ),
        4 => array(
                'hard_limit' => 3, //Do not accept a lvs higher than X
                'span' => 2, //How much distances should be shown
            ),
    );

    public function __construct()
    {
        $this->name = 'ambjolisearch';

        $this->tab = 'search_filter';
        $this->version = '4.3.6';
        $this->author = 'Ambris Informatique';
        $this->module_key = '2642eb17142e5a9c9bad308c9c642f2c';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');

        parent::__construct();

        $this->displayName = $this->l('JoliSearch : Improved Search');
        $this->description = $this->l('Improves instant search displays and handles approximative searches');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps17 = version_compare(_PS_VERSION_, '1.7', '>=');
        $this->ps16 = version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<');
        $this->ps15 = version_compare(_PS_VERSION_, '1.6', '<');

        $this->use_jolisearch_tpl = !$this->ps17;

        $this->initImagesPath();
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        //$this->installSQLTables();
        $this->uninstallSQL();
        $this->installSQLTables();

        $default_image_type = self::getImageFormattedName('small');

        $install_ok = parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('productSearchProvider') &&
        $this->installMeta('jolisearch') &&
        Configuration::updateValue(AJS_MAX_ITEMS_KEY, 10) &&
        Configuration::updateValue(AJS_MAX_MANUFACTURERS_KEY, 4) &&
        Configuration::updateValue(AJS_MAX_CATEGORIES_KEY, 4) &&
        Configuration::updateValue(AJS_MAX_CATEGORIES_KEY, 8) &&
        Configuration::updateValue(AJS_PRODUCTS_PRIORITY_KEY, 4) &&
        Configuration::updateValue(AJS_MANUFACTURERS_PRIORITY_KEY, 2) &&
        Configuration::updateValue(AJS_CATEGORIES_PRIORITY_KEY, 1) &&
        Configuration::updateValue(AJS_MORE_RESULTS_CONFIG, 1) &&
        Configuration::updateValue(AJS_SHOW_PRICES, 1) &&
        Configuration::updateValue(AJS_SHOW_CATEGORIES, 2) &&
        Configuration::updateValue(AJS_SHOW_FEATURES, 1) &&
        Configuration::updateValue(AJS_SHOW_CAT_DESC, 0) &&
        Configuration::updateValue(AJS_JOLISEARCH_THEME, 'modern') &&
        Configuration::updateValue(AJS_PRODUCT_THUMB_NAME, $default_image_type) &&
        Configuration::updateValue(AJS_MANUFACTURER_THUMB_NAME, $default_image_type) &&
        Configuration::updateValue(AJS_CATEGORY_THUMB_NAME, $default_image_type) &&
        Configuration::updateValue(AJS_ALLOW_FILTER_RESULTS, 1) &&
        Configuration::updateValue(AJS_COMPAT, 0) &&
        Configuration::updateValue(AJS_BLOCKSEARCH_CSS, 0) &&
        Configuration::updateValue(AJS_USE_STD_SEARCH_BAR, 0) &&
        Configuration::updateValue(AJS_DROPDOWN_LIST_POSITION, ($this->ps17 ? "right" : "center")) &&
        Configuration::updateValue(AJS_SHOW_PARENT_CATEGORY, 0) &&
        Configuration::updateValue(AJS_FILTER_ON_PARENT_CATEGORY, 0) &&
        Configuration::updateValue(AJS_SEARCH_IN_SUBCATEGORIES, 0) &&
        Configuration::updateValue(AJS_APPROXIMATION_LEVEL, 2) &&
        Configuration::updateValue(AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK, 0);
        Configuration::updateValue(AJS_ONLY_DEFAULT_CATEGORIES, 0);
        Configuration::updateValue(AJS_ONLY_LEAF_CATEGORIES, 0);

        if (!$this->ps17) {
            $install_ok = $this->registerHook('top') &&
            $this->registerHook('displayJolisearch') &&
            $this->registerHook('displaySearch') &&
                $install_ok;
        }

        $this->setControllersLayout();

        return $install_ok;
    }

    public function installSQLTables()
    {
        if (!file_exists(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_TABLES_FILE)) {
            return (false);
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/sql/' . self::INSTALL_SQL_TABLES_FILE)) {
            return (false);
        }

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        if (version_compare(_PS_VERSION_, 1.4, '>=')) {
            $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        } else {
            $sql = str_replace('MYSQL_ENGINE', 'MyISAM', $sql);
        }

        $sql = preg_split('/;\s*[\r\n]+/', $sql);

        foreach ($sql as $query) {
            if (preg_match('/(^#.*$)|(^[\s\t]*$)/', $query)) {
                continue;
            }

            if (!Db::getInstance()->Execute(trim($query))) {
                $html = '<ul><li>Erreur SQL : '
                . Db::getInstance()->getNumberError() . ' : ' . Db::getInstance()->getMsgError() . '<br /><br />
                            <pre>' . $query . '</pre></li></ul>';
                return $this->displayError($html);
            }
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->uninstallSQL() ||
            !$this->uninstallMeta('jolisearch')) {
            return false;
        }

        return true;
    }

    public function debugReset()
    {
        //$this->log($this->installSQLFunctions(), __FILE__, __METHOD__, __LINE__, 'installSQLFunctions (true or false)');
        $this->log($this->installSQLTables(), __FILE__, __METHOD__, __LINE__, 'installSQLTables (true or false)');

        parent::debugReset();
    }

    public function uninstallSQL()
    {
        $query = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'ambjolisearch_synonyms;';
        if (!Db::getInstance()->Execute(trim($query))) {
            return (false);
        }

        return true;
    }

    protected function checkInstallation()
    {
        $return = '';
        $error_count = 0;

        if (!$this->checkTables()) {
            $error_count++;
            $return .= $this->displayError(
                $this->l('Table') . ' ' . _DB_PREFIX_ . 'ambjolisearch_synonyms '
                . $this->l('does not exist.')
            );
        }

        if ($error_count == 0) {
            Configuration::updateValue(AJS_INSTALLATION_COMPLETE, 1);
        }

        return $return;
    }

    protected function checkTables()
    {
        $query = '
        SELECT *
        FROM information_schema.tables
        WHERE table_schema = "' . _DB_NAME_ . '"
            AND table_name = "' . _DB_PREFIX_ . 'ambjolisearch_synonyms"
        LIMIT 1;';
        $result = Db::getInstance()->ExecuteS($query);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function hookHeader()
    {
        /* added to support compatibility with the bad hook call of PM_advancedSearch4 */
        return $this->hookDisplayHeader();
    }

    public function hookDisplayHeader()
    {
        if (!$this->ps17) {
            $this->retroCompatIncludes();
            $this->context->controller->addJS($this->_path . 'views/js/ambjolisearch.js');
        } else {
            if (Module::isEnabled('ps_searchbar')) {
                $this->context->controller->unregisterJavascript('modules-searchbar');
            } else {
                $this->context->controller->addJqueryUI('ui.autocomplete');
            }

            $this->context->controller->registerJavascript('modules-ambjolisearch', 'modules/' . $this->name . '/views/js/ambjolisearch.js', array('position' => 'bottom', 'priority' => 1000));
        }

        $theme = Configuration::get(AJS_JOLISEARCH_THEME);
        if (empty($theme)) {
            $theme = 'autocomplete';
        }

        $this->context->controller->addCSS($this->_path . 'views/css/jolisearch-common.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/jolisearch-' . $theme . '.css', 'all');

        $this->addAdvancedSearch4Support();
        return $this->assignJolisearchVars();
    }

    public function addAdvancedSearch4Support()
    {
        if (class_exists('PM_AdvancedSearch4')) {
            if (Tools::getValue('search_query') != null) {
                $search_query = Tools::getValue('search_query');
            } elseif (Tools::getValue('productFilterListSource') != null) {
                /* call from ajax page loading when criteria has been selected in pm_advancedsearch4 */
                $filter_source = Tools::getValue('productFilterListSource');
                if ($filter_source == 'jolisearch') {
                    $search_query = As4SearchEngine::$productFilterListData;
                } else {
                    $arr = explode('|', $filter_source);
                    if ($arr[0] == 'jolisearch') {
                        $search_query = $arr[1];
                    }
                }
            }
            if (isset($search_query) && !empty($search_query)) {
                $this->setAdvancedSearch4Results($search_query);
            }
        }
    }

    public function setAdvancedSearch4Results($search_query)
    {
        if (!class_exists('AmbSearch')) {
            require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/AmbSearch.php';
        }

        $searcher = new AmbSearch(true, $this->context, $this);
        $searcher->search($this->context->language->id, $search_query, 1, -1, 'position', 'desc');
        //Charge la liste des ids produit correspondant aux critères
        $search_results = $searcher->getResultIds();

/*
// Advanced Search 4 >= 4.11
As4SearchEngine::$productFilterListQuery = implode(',', $search_results);
As4SearchEngine::$productFilterListSource = 'jolisearch';
 */
        // Advanced Search 4 >= 4.11
        if (property_exists('As4SearchEngine', 'productFilterListQuery')) {
            As4SearchEngine::$productFilterListQuery = implode(',', $search_results);
            As4SearchEngine::$productFilterListSource = 'jolisearch';
        } else {
            // Advanced Search 4 < 4.11
            $product_filter_list = 'productFilterList';
            if (property_exists("PM_AdvancedSearch4", $product_filter_list)) {
                PM_AdvancedSearch4::$$product_filter_list = $search_results;
            }

            $product_filter_list_source = 'productFilterListSource';
            if (property_exists("PM_AdvancedSearch4", $product_filter_list_source)) {
                PM_AdvancedSearch4::$$product_filter_list_source = 'jolisearch|' . Tools::replaceAccentedChars(urldecode($search_query)); /* transmit search_query for future calls */
            }
        }
    }

    public function void($param)
    {
        return $param;
    }

    protected function getConfigFormValues()
    {
        $default_image_type = self::getImageFormattedName('small');
        return array(
            'AJS_MAX_ITEMS_KEY' => Configuration::get(AJS_MAX_ITEMS_KEY),
            'AJS_MAX_MANUFACTURERS_KEY' => Configuration::get(AJS_MAX_MANUFACTURERS_KEY),
            'AJS_MAX_CATEGORIES_KEY' => Configuration::get(AJS_MAX_CATEGORIES_KEY),
            'AJS_MAX_PRODUCTS_KEY' => Configuration::get(AJS_MAX_PRODUCTS_KEY),
            'AJS_PRODUCTS_PRIORITY_KEY' => Configuration::get(AJS_PRODUCTS_PRIORITY_KEY),
            'AJS_MANUFACTURERS_PRIORITY_KEY' => Configuration::get(AJS_MANUFACTURERS_PRIORITY_KEY),
            'AJS_CATEGORIES_PRIORITY_KEY' => Configuration::get(AJS_CATEGORIES_PRIORITY_KEY),
            'AJS_APPROXIMATIVE_SEARCH' => Configuration::get(AJS_APPROXIMATIVE_SEARCH),
            'AJS_MORE_RESULTS_CONFIG' => Configuration::get(AJS_MORE_RESULTS_CONFIG),
            'AJS_SHOW_PRICES' => Configuration::get(AJS_SHOW_PRICES),
            'AJS_SHOW_FEATURES' => Configuration::get(AJS_SHOW_FEATURES),
            'AJS_SHOW_CATEGORIES' => Configuration::get(AJS_SHOW_CATEGORIES),
            'AJS_SHOW_CAT_DESC' => Configuration::get(AJS_SHOW_CAT_DESC),
            'AJS_ENABLE_AC_PHONE' => Configuration::get(AJS_ENABLE_AC_PHONE),
            'AJS_DISABLE_AC' => !Configuration::get(AJS_DISABLE_AC, null, null, null, false),
            'AJS_MULTILANG_SEARCH' => Configuration::get(AJS_MULTILANG_SEARCH),
            'PS_SEARCH_START' => Configuration::get(PS_SEARCH_START),
            'AJS_PRODUCT_THUMB_NAME' => Configuration::hasKey(AJS_PRODUCT_THUMB_NAME) ? Configuration::get(AJS_PRODUCT_THUMB_NAME) : $default_image_type,
            'AJS_MANUFACTURER_THUMB_NAME' => Configuration::hasKey(AJS_MANUFACTURER_THUMB_NAME) ? Configuration::get(AJS_MANUFACTURER_THUMB_NAME) : $default_image_type,
            'AJS_CATEGORY_THUMB_NAME' => Configuration::hasKey(AJS_CATEGORY_THUMB_NAME) ? Configuration::get(AJS_CATEGORY_THUMB_NAME) : $default_image_type,
            'AJS_JOLISEARCH_THEME' => Configuration::get(AJS_JOLISEARCH_THEME),
            'AJS_ALLOW_FILTER_RESULTS' => Configuration::get(AJS_ALLOW_FILTER_RESULTS),
            'AJS_DROPDOWN_LIST_POSITION' => Configuration::get(AJS_DROPDOWN_LIST_POSITION),
            'AJS_COMPAT' => Configuration::get(AJS_COMPAT),
            'AJS_BLOCKSEARCH_CSS' => Configuration::get(AJS_BLOCKSEARCH_CSS),
            'AJS_USE_STD_SEARCH_BAR' => Configuration::get(AJS_USE_STD_SEARCH_BAR),
            'AJS_DISPLAY_CATEGORY' => Configuration::get(AJS_DISPLAY_CATEGORY),
            'AJS_DISPLAY_MANUFACTURER' => Configuration::get(AJS_DISPLAY_MANUFACTURER),
            'AJS_SHOW_PARENT_CATEGORY' => Configuration::get(AJS_SHOW_PARENT_CATEGORY),
            'AJS_FILTER_ON_PARENT_CATEGORY' => Configuration::get(AJS_FILTER_ON_PARENT_CATEGORY),
            'AJS_SEARCH_IN_SUBCATEGORIES' => Configuration::get(AJS_SEARCH_IN_SUBCATEGORIES),
            'AJS_USE_APPROXIMATIVE_FOR_REFERENCES' => Configuration::get(AJS_USE_APPROXIMATIVE_FOR_REFERENCES),
            'AJS_SEARCH_ALL_TERMS' => (Configuration::hasKey(AJS_SEARCH_ALL_TERMS) ? Configuration::get(AJS_SEARCH_ALL_TERMS) : true),
            'AJS_ALSO_TRY_OR_COMPARATOR' => (Configuration::hasKey(AJS_ALSO_TRY_OR_COMPARATOR) ? Configuration::get(AJS_ALSO_TRY_OR_COMPARATOR) : false),
            'AJS_APPROXIMATION_LEVEL' => (Configuration::hasKey(AJS_APPROXIMATION_LEVEL) ? Configuration::get(AJS_APPROXIMATION_LEVEL) : (Configuration::get(AJS_APPROXIMATIVE_SEARCH) ? 2 : 0)),
            'AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK' => (Configuration::hasKey(AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK) ? Configuration::get(AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK) : false),
            'AJS_ONLY_DEFAULT_CATEGORIES' => (Configuration::hasKey(AJS_ONLY_DEFAULT_CATEGORIES) ? Configuration::get(AJS_ONLY_DEFAULT_CATEGORIES) : false),
            'AJS_ONLY_LEAF_CATEGORIES' => (Configuration::hasKey(AJS_ONLY_LEAF_CATEGORIES) ? Configuration::get(AJS_ONLY_LEAF_CATEGORIES) : false),

        );
    }

    protected function getConfigFormTypes()
    {
        return array(
            'AJS_MAX_ITEMS_KEY' => 'NullOrInt',
            'AJS_MAX_MANUFACTURERS_KEY' => 'NullOrInt',
            'AJS_MAX_CATEGORIES_KEY' => 'NullOrInt',
            'AJS_MAX_PRODUCTS_KEY' => 'NullOrInt',
            'AJS_PRODUCTS_PRIORITY_KEY' => 'Int',
            'AJS_MANUFACTURERS_PRIORITY_KEY' => 'Int',
            'AJS_CATEGORIES_PRIORITY_KEY' => 'Int',
            'AJS_APPROXIMATIVE_SEARCH' => 'Bool',
            'AJS_MORE_RESULTS_CONFIG' => 'Bool',
            'AJS_SHOW_PRICES' => 'Bool',
            'AJS_SHOW_FEATURES' => 'Bool',
            'AJS_SHOW_CATEGORIES' => 'Int',
            'AJS_SHOW_CAT_DESC' => 'Bool',
            'AJS_ENABLE_AC_PHONE' => 'Bool',
            'AJS_DISABLE_AC' => 'Bool',
            'AJS_MULTILANG_SEARCH' => 'Bool',
            'PS_SEARCH_START' => 'Bool',
            'AJS_PRODUCT_THUMB_NAME' => 'String',
            'AJS_MANUFACTURER_THUMB_NAME' => 'String',
            'AJS_CATEGORY_THUMB_NAME' => 'String',
            'AJS_JOLISEARCH_THEME' => 'String',
            'AJS_ALLOW_FILTER_RESULTS' => 'Bool',
            'AJS_DROPDOWN_LIST_POSITION' => 'String',
            'AJS_COMPAT' => 'Bool',
            'AJS_BLOCKSEARCH_CSS' => 'Bool',
            'AJS_USE_STD_SEARCH_BAR' => 'Bool',
            'AJS_DISPLAY_CATEGORY' => 'Bool',
            'AJS_DISPLAY_MANUFACTURER' => 'Bool',
            'AJS_SHOW_PARENT_CATEGORY' => 'Bool',
            'AJS_FILTER_ON_PARENT_CATEGORY' => 'Bool',
            'AJS_SEARCH_IN_SUBCATEGORIES' => 'Bool',
            'AJS_USE_APPROXIMATIVE_FOR_REFERENCES' => 'Bool',
            'AJS_SEARCH_ALL_TERMS' => 'Bool',
            'AJS_ALSO_TRY_OR_COMPARATOR' => 'Bool',
            'AJS_APPROXIMATION_LEVEL' => 'Int',
            'AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK' => 'Bool',
            'AJS_ONLY_DEFAULT_CATEGORIES' => 'Bool',
            'AJS_ONLY_LEAF_CATEGORIES' => 'Bool',
        );
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitAmbJoliSearchModule')) {
            $form_values = $this->getConfigFormValues();
            $form_types = $this->getConfigFormTypes();
            $errors = array();

            foreach (array_keys($form_values) as $key) {
                if (Tools::getIsset($key)) {
                    $value = Tools::getValue($key);

                    if ($form_types[$key] == 'Int') {
                        if (!Validate::isInt($value)) {
                            $errors[] = $this->getFormFieldLabel($key) . ' : ' . $this->l('Invalid values');
                        }
                    } elseif ($form_types[$key] == 'NullOrInt') {
                        if (!empty($value) && !Validate::isInt($value)) {
                            $errors[] = $this->getFormFieldLabel($key) . ' : ' . $this->l('Invalid values') . ' empty:' . (int) empty($value) . ' valid:' . (int) Validate::isInt($value);
                        }
                    } elseif ($form_types[$key] == 'Bool') {
                        if (!Validate::isBool((bool) $value)) {
                            $errors[] = $this->getFormFieldLabel($key) . ' : ' . $this->l('Invalid values');
                        }
                    }
                    if (count($errors)) {
                        $this->_html .= $this->displayError(implode('<br />', $errors));
                        $errors = array();
                        continue;
                    }

                    if ($key == 'AJS_APPROXIMATION_LEVEL' && Configuration::get(AJS_APPROXIMATION_LEVEL) != Tools::getValue($key)) {
                        $this->resetSynonyms();
                    }

                    if ($key == 'AJS_DISABLE_AC') {
                        Configuration::updateValue(constant($key), !Tools::getValue($key));
                    } else {
                        Configuration::updateValue(constant($key), Tools::getValue($key));
                    }
                }
            }
        }
    }

    public function getFormFieldLabel($key)
    {
        static $forms;
        if (is_null($forms)) {
            $forms = $this->getConfigForm();
        }

        foreach ($forms as $form) {
            foreach ($form['form']['input'] as $field) {
                if ($field['name'] == $key) {
                    return $field['label'];
                }
            }
        }
        return $key;
    }

    public function getContent()
    {
        $this->_html = '';
        $output = array('pre' => '', 'post' => '');
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitAmbJoliSearchModule')) == true) {
            $this->postProcess();
        }

        if (Tools::isSubmit('submitResetSynonyms')) {
            $this->resetSynonyms();
            $output['pre'] .= $this->displayConfirmation($this->l('Synonyms have been reset'));
        }

        if (!(bool) Configuration::get(AJS_INSTALLATION_COMPLETE)) {
            $output['pre'] .= $this->checkInstallation();
        }

        $this->context->smarty->assign('documentation_link', $this->_path . 'docs/' . $this->l('readme_en.pdf'));
        $this->context->smarty->assign('rebuild_index_url', $this->context->link->getAdminLink('AdminModules').'&configure=ambjolisearch&indexation=products');

        list($total, $indexed) = Db::getInstance()->getRow('SELECT COUNT(*) as "0", SUM(product_shop.indexed) as "1" FROM '._DB_PREFIX_.'product p '.Shop::addSqlAssociation('product', 'p').' WHERE product_shop.`visibility` IN ("both", "search") AND product_shop.`active` = 1');

        $this->context->smarty->assign(array(
            'module_dir', $this->_path,
            'nbSynonyms' => $this->getNbSynonyms(),
            'request_uri' => Tools::safeOutput($_SERVER['REQUEST_URI']),
            'path' => $this->_path,
            'compat' => $this->compat,
            'forms' => $this->renderForm(),
            'indexed' => $indexed,
            'total' => $total
        ));

        $output['pre'] .= $this->display(__FILE__, 'views/templates/admin/documentation.tpl');
        $output['content'] = $this->display(__FILE__, 'views/templates/admin/configure.tpl');
        $output['post'] .= $this->display(__FILE__, 'views/templates/admin/synonyms.tpl');


        if (Tools::isSubmit('successOk')) {
            $output['pre'] .= $this->displayConfirmation($this->l('Settings updated'));
        }
        if ($this->ps16 && Configuration::get(AJS_USE_STD_SEARCH_BAR) && !Module::isEnabled('blocksearch')) {
            $output['pre'] .= $this->displayWarning($this->l('"Use standard search bar" option is activated, but standard search bar module (blocksearch) is disabled. You need to active it.'));
        }


        if (((bool)Tools::getIsset('indexation')) == true) {
            $indexer = new AmbIndexation();
            $method = 'process'.Tools::ucfirst(Tools::getValue('indexation'));
            if (method_exists($indexer, $method)) {
                $indexer->{$method}(false, Tools::getValue('step', 0));
            }
        }

        return $this->_html . $output['pre'] . $output['content'] . $output['post'] . $this->getDebug();
    }

    public function resetSynonyms()
    {
        return Db::getInstance()->Execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'ambjolisearch_synonyms');
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAmbJoliSearchModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $forms = $this->getConfigForm();

        $form_tpl = array();
        foreach ($forms as $key => $form) {
            if ($this->ps15) {
                $form = $this->adaptConfigForm($form);
            }

            $form_tpl[$key] = $helper->generateForm(array($form));
        }

        return $form_tpl;
    }

    public function getDropdownListSettingsForm()
    {
        $product_image_types = ImageType::getImagesTypes('products');
        $manufacturer_image_types = ImageType::getImagesTypes('manufacturers');
        $category_image_types = ImageType::getImagesTypes('categories');

        $default_image_type = self::getImageFormattedName('small');

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Dropdown list settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate instant searches'),
                        'name' => 'AJS_DISABLE_AC',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate instant searches on mobile phones'),
                        'name' => 'AJS_ENABLE_AC_PHONE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_MAX_PRODUCTS_KEY',
                        'label' => $this->l('Maximum of products to display'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_MAX_MANUFACTURERS_KEY',
                        'label' => $this->l('Maximum of manufacturers to display'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_MAX_CATEGORIES_KEY',
                        'label' => $this->l('Maximum of categories to display'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate "Show more results" option'),
                        'name' => 'AJS_MORE_RESULTS_CONFIG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show prices during instant searches'),
                        'name' => 'AJS_SHOW_PRICES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use filtered search link for categories and manufacturers'),
                        'name' => 'AJS_ALLOW_FILTER_RESULTS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display manufacturer'),
                        'desc' => $this->l('Display manufacturer name of product in dropdown results list'),
                        'name' => 'AJS_DISPLAY_MANUFACTURER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display category'),
                        'desc' => $this->l('Display category name of product in dropdown results list'),
                        'name' => 'AJS_DISPLAY_CATEGORY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display parent category'),
                        'desc' => $this->l('Also display parent category name of product in dropdown results list'),
                        'name' => 'AJS_SHOW_PARENT_CATEGORY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only use default categories'),
                        'desc' => $this->l('Only use the default category of product to filter.'),
                        'name' => 'AJS_ONLY_DEFAULT_CATEGORIES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only use leaf (lowest level) categories'),
                        'desc' => $this->l('Only search in the lowest level of categories of product to filter.'),
                        'name' => 'AJS_ONLY_LEAF_CATEGORIES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use parent category to filter'),
                        'desc' => $this->l('Use parent category rather than default category of product to filter.'),
                        'name' => 'AJS_FILTER_ON_PARENT_CATEGORY',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show product features'),
                        'name' => 'AJS_SHOW_FEATURES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'label' => $this->l('Image type for products in results (thumbnails)'),
                        'name' => 'AJS_PRODUCT_THUMB_NAME',
                        'default_value' => $default_image_type,
                        'type' => 'select',
                        'options' => array(
                            'query' => $product_image_types,
                            'id' => 'name',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col' => 3,
                        'label' => $this->l('Image type for manufacturers in results (thumbnails)'),
                        'name' => 'AJS_MANUFACTURER_THUMB_NAME',
                        'default_value' => $default_image_type,
                        'type' => 'select',
                        'options' => array(
                            'query' => $manufacturer_image_types,
                            'id' => 'name',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col' => 3,
                        'label' => $this->l('Image type for categories in results (thumbnails)'),
                        'name' => 'AJS_CATEGORY_THUMB_NAME',
                        'default_value' => $default_image_type,
                        'type' => 'select',
                        'options' => array(
                            'query' => $category_image_types,
                            'id' => 'name',
                            'name' => 'name',
                        ),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function getResultsPageSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Search results page settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_SHOW_CATEGORIES',
                        'label' => $this->l('Show categories on top of search page'),
                        'desc' => $this->l('Number of categories to display on search results page (in order to not display any category on the results page, set to 0)'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show category description'),
                        'desc' => $this->l('Add category description on search results page'),
                        'name' => 'AJS_SHOW_CAT_DESC',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function getDesignSettingsForm()
    {
        $positions = array(
            array('id' => 'left', 'name' => $this->l('Left align')),
            array('id' => 'center', 'name' => $this->l('Centered')),
            array('id' => 'right', 'name' => $this->l('Right align')),
        );

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Design settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'label' => $this->l('Dropdown list theme'),
                        'name' => 'AJS_JOLISEARCH_THEME',
                        'default_value' => 'autocomplete',
                        'type' => 'select',
                        'options' => array(
                            'query' => array(array('id' => 'autocomplete', 'name' => 'Classic'), array('id' => 'modern', 'name' => 'Modern'), array('id' => 'finder', 'name' => 'Finder-like')),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'col' => 3,
                        'label' => $this->l('Dropdown list alignement with search field'),
                        'name' => 'AJS_DROPDOWN_LIST_POSITION',
                        'default_value' => 'right',
                        'type' => 'select',
                        'options' => array(
                            'query' => $positions,
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function getCompatibilitySettingsForm()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Compatibility settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('JS compatibility mode'),
                        'name' => 'AJS_COMPAT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        if ($this->ps16 || $this->ps15) {
            $form['form']['input'][] = array(
                'type' => 'switch',
                'label' => $this->l('Use default prestashop searchblock stylesheets'),
                'name' => 'AJS_BLOCKSEARCH_CSS',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            );
        }
        if ($this->ps16) {
            $form['form']['input'][] = array(
                'type' => 'switch',
                'label' => $this->l('Use standard search bar'),
                'desc' => $this->l('To improve compatibility with your theme, Jolisearch can hook to the standard search bar. In this case, you will need to active Prestashop standard search bar module.'),
                'name' => 'AJS_USE_STD_SEARCH_BAR',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled'),
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled'),
                    ),
                ),
            );
        }
        return $form;
    }

    public function getPrioritySettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Priority settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_PRODUCTS_PRIORITY_KEY',
                        'label' => $this->l('Products priority'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_MANUFACTURERS_PRIORITY_KEY',
                        'label' => $this->l('Manufacturers priority'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'AJS_CATEGORIES_PRIORITY_KEY',
                        'label' => $this->l('Categories priority'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function getSearchSettingsForm()
    {
        $is_correctly_installed = (bool) Configuration::get(AJS_INSTALLATION_COMPLETE);
        $approximation_level = (Configuration::hasKey(AJS_APPROXIMATION_LEVEL) ? Configuration::get(AJS_APPROXIMATION_LEVEL) : (Configuration::get(AJS_APPROXIMATIVE_SEARCH) ? 2 : 0));
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Approximate Search Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'condition' => $is_correctly_installed,
                        'type' => 'html',
                        'label' => $this->l('Search approximation level'),
                        'name' => 'AJS_APPROXIMATION_LEVEL',
                        'html_content' => '
                        <div class="col-xs-12">
                            <div style="float:left;width:25%">No approximative search<br />Best performance</div>
                            <div style="float:left;width:10%">&nbsp;</div>
                            <div style="float:left;width:30%;text-align:center">Medium approximative search<br />Medium performance</div>
                            <div style="float:left;width:10%;text-align:right;">&nbsp;</div>
                            <div style="float:left;width:25%; text-align:right;">Strong approximative search<br />Worst performance</div>
                        </div>
                        <div class="slidecontainer">
                          <input type="range" name="AJS_APPROXIMATION_LEVEL" min="0" max="4" value="'.$approximation_level.'" class="slider" id="myRange">
                        </div>
                        <div class="col-xs-12">
                            <div style="float:left;width:25%">0</div>
                            <div style="float:left;width:10%">1</div>
                            <div style="float:left;width:30%;text-align:center">2</div>
                            <div style="float:left;width:10%;text-align:right;">3</div>
                            <div style="float:left;width:25%; text-align:right;">4</div>
                        </div>',
                        'hint' => $this->l('The default value is 2'),
                        'desc' => $this->l('The stronger the approximation search level, the more results jolisearch will return. If set to 0, the search will not try to correct any mistake.')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use approximative search for references'),
                        'desc' => $this->l('Use approximative search for terms with numbers only or mixing letters and numbers'),
                        'name' => 'AJS_USE_APPROXIMATIVE_FOR_REFERENCES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search within word'),
                        'name' => 'PS_SEARCH_START',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use approximative search for references'),
                        'desc' => $this->l('Use approximative search for terms with numbers only or mixing letters and numbers'),
                        'name' => 'AJS_USE_APPROXIMATIVE_FOR_REFERENCES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search in all languages'),
                        'name' => 'AJS_MULTILANG_SEARCH',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search in subcategories'),
                        'desc' => $this->l('Also search in subcategories when a filter is used.'),
                        'name' => 'AJS_SEARCH_IN_SUBCATEGORIES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search all terms'),
                        'desc' => $this->l('When enabled, products match if they contain all searched terms (combine with AND). If disabled, products match if they contain at least one of searched terms (combine with OR).'),
                        'name' => 'AJS_SEARCH_ALL_TERMS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Also try OR comparator'),
                        'desc' => $this->l('When enabled, if no products are found using the AND comparator, a second search will be performed using the OR comparator'),
                        'name' => 'AJS_ALSO_TRY_OR_COMPARATOR',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only search for available products'),
                        'desc' => $this->l('When enabled, only in-stock products will appear in search results (may increase searching time).'),
                        'name' => 'AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function getConfigForm()
    {

        $forms = array(
            'design_settings' => $this->getDesignSettingsForm(),
            'dropdown_list_settings' => $this->getDropdownListSettingsForm(),
            'results_page_settings' => $this->getResultsPageSettingsForm(),
            'priority_settings' => $this->getPrioritySettingsForm(),
            'search_settings' => $this->getSearchSettingsForm(),
        );

        if (!$this->ps17) {
            $forms['compatibility_settings'] = $this->getCompatibilitySettingsForm();
        }

        return $forms;
    }

    private function getNbSynonyms()
    {
        $query = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'ambjolisearch_synonyms';
        return Db::getInstance()->getValue($query, false);
    }

    public function hookProductSearchProvider($params)
    {
        if ($this->ps17) {
            require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/src/Amb_ProductSearchProvider.php';

            if (Tools::getValue('s', false) !== false) {
                return new AmbProductSearchProvider($this);
            }
        }
    }

    protected function assignJolisearchVars()
    {
        $templateVars = $this->getSettings();

        $this->context->smarty->assign($templateVars);

        if (method_exists('Media', 'addJsDef')) {
            Media::addJsDef(array('jolisearch' => $templateVars));
        } else {
            $this->context->smarty->assign(array(
                'ambjolisearch_jsdefs' => array(
                    'jolisearch' => $templateVars,
                ),
            ));
            return $this->display(__FILE__, 'views/templates/hook/js15.tpl');
        }
    }

    public function getSettings()
    {
        $joli_link = new JoliLink($this->context->link);
        $action = $joli_link->getModuleLink('ambjolisearch', 'jolisearch', array(), Tools::usingSecureMode());
        $link = $joli_link->getModuleLink('ambjolisearch', 'jolisearch', array(), Tools::usingSecureMode());
        $controller_name = 'jolisearch';

        $ga_acc = !Configuration::get('GANALYTICS_ID') ? 0 : Configuration::get('GANALYTICS_ID');

        if (Configuration::get(AJS_DISABLE_AC)) {
            $use_autocomplete = 0;
        } elseif (Configuration::get(AJS_ENABLE_AC_PHONE)) {
            $use_autocomplete = 2;
        } else {
            $use_autocomplete = 1;
        }

        $positions = array(
            'left' => array(
                'my' => 'left top',
                'at' => 'left bottom',
                'collision' => 'flipfit none',
            ),
            'center' => array(
                'my' => 'center top',
                'at' => 'center bottom',
                'collision' => 'fit none',
            ),
            'right' => array(
                'my' => 'right top',
                'at' => 'right bottom',
                'collision' => 'flipfit none',
            ),
        );

        $selected_alignement = Configuration::hasKey(AJS_DROPDOWN_LIST_POSITION) ? Configuration::get(AJS_DROPDOWN_LIST_POSITION) : ($this->ps17 ? "right" : "center");

        return array(
            'amb_joli_search_action' => $action,
            'amb_joli_search_link' => $link,
            'amb_joli_search_controller' => $controller_name,
            'blocksearch_type' => 'top',
            'show_cat_desc' => (int) Configuration::get(AJS_SHOW_CAT_DESC),
            'ga_acc' => $ga_acc,
            'id_lang' => $this->context->language->id,
            'url_rewriting' => $joli_link->isUrlRewriting(),
            'use_autocomplete' => $use_autocomplete,
            'minwordlen' => (int) Configuration::get('PS_SEARCH_MINWORDLEN'),
            'l_products' => $this->l('Products'),
            'l_manufacturers' => $this->l('Manufacturers'),
            'l_categories' => $this->l('Categories'),
            'l_no_results_found' => $this->l('No results found'),
            'l_more_results' => $this->l('More results »'),
            'ENT_QUOTES' => ENT_QUOTES,
            'search_ssl' => Tools::usingSecureMode(),
            'self' => dirname(__FILE__),
            'position' => $positions[$selected_alignement],
            'classes' => ($this->ps17 ? 'ps17' : 'ps16') . ($selected_alignement == 'center' ? ' centered-list' : ''),
            'display_manufacturer' => Configuration::hasKey(AJS_DISPLAY_MANUFACTURER) ? Configuration::get(AJS_DISPLAY_MANUFACTURER) : true,
            'display_category' => Configuration::hasKey(AJS_DISPLAY_CATEGORY) ? Configuration::get(AJS_DISPLAY_CATEGORY) : true,
        );
    }

    /* image management */
    public $no_image_path = array();
    public $ssl = false;
    public $product_image_type = false;
    public $manufacturer_image_type = false;
    public $category_image_type = false;

    public function initImagesPath()
    {
        $default_image_type = self::getImageFormattedName('small');
        $iso_code = $this->context->language->iso_code;

        $this->ssl = Tools::usingSecureMode();
        $this->product_image_type = Configuration::get(AJS_PRODUCT_THUMB_NAME);
        if (!$this->product_image_type) {
            $this->product_image_type = $default_image_type;
        }

        $this->manufacturer_image_type = Configuration::get(AJS_MANUFACTURER_THUMB_NAME);
        if (!$this->manufacturer_image_type) {
            $this->manufacturer_image_type = $default_image_type;
        }

        $this->category_image_type = Configuration::get(AJS_CATEGORY_THUMB_NAME);
        if (!$this->category_image_type) {
            $this->category_image_type = $default_image_type;
        }

        if (Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/' . $this->name . '/views/img/no-image.png')) {
            $img_path = ($this->ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_) . _PS_THEME_DIR_
            . 'modules/' . $this->name . '/views/img/no-image.png';
            $this->no_image_path['p'] = $img_path;
            $this->no_image_path['m'] = $img_path;
            $this->no_image_path['c'] = $img_path;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . $this->name . '/views/img/no-image.png')) {
            $img_path = ($this->ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_) . _MODULE_DIR_
            . $this->name . '/views/img/no-image.png';
            $this->no_image_path['p'] = $img_path;
            $this->no_image_path['m'] = $img_path;
            $this->no_image_path['c'] = $img_path;
        } else {
            $this->no_image_path['p'] = _PS_IMG_ . "p/$iso_code-default-" . $this->product_image_type;
            $this->no_image_path['m'] = _PS_IMG_ . "m/$iso_code-default-" . $this->manufacturer_image_type;
            $this->no_image_path['c'] = _PS_IMG_ . "c/$iso_code-default-" . $this->category_image_type;
        }
    }

    public function getProductImage($product)
    {
        if (is_array($product) && (isset($product['imgid']) && $product['imgid'] != null)) {
            return $this->context->link->getImageLink(
                $product['prewrite'],
                $product['imgid'],
                $this->product_image_type
            );
        } else {
            return $this->no_image_path['p'];
        }
    }

    public function getManufacturerImage($manufacturer)
    {
        $uri_path = '';
        if (Tools::file_exists_cache(
            _PS_IMG_DIR_ . 'm/' . $manufacturer->id . '-' . $this->manufacturer_image_type . '.jpg'
        )) {
            return $this->context->link->protocol_content
            . Tools::getMediaServer($uri_path) . _PS_IMG_ . 'm/'
            . $manufacturer->id . '-' . $this->manufacturer_image_type . '.jpg';
        } else {
            return $this->no_image_path['m'];
        }
    }

    public function getCategoryImage($category, $id_lang)
    {
        $id_image = file_exists(_PS_CAT_IMG_DIR_ . $category->id . '.jpg') ?
        (int) $category->id : Language::getIsoById($id_lang) . '-default';
        if (Tools::file_exists_cache(
            _PS_CAT_IMG_DIR_ . $id_image . '-' . $this->category_image_type . '.jpg'
        )) {
            return $this->context->link->getCatImageLink($category->link_rewrite, $id_image, $this->category_image_type);
        } else {
            return $this->no_image_path['c'];
        }
    }

    /////////////////////////////////// FOR 1.6 AND BELOW /////////////////////////////////////////
    public function displaySearchBar($hook, $params)
    {
        if (!$this->ps17 && Configuration::get(AJS_USE_STD_SEARCH_BAR)) {
            return;
        }

        if ($this->use_jolisearch_tpl === true) {
            return $this->display(__FILE__, 'views/templates/hook/ambjolisearch.tpl');
        } else {
            if ($this->ps17) {
                return $this->renderWidget($hook, $params);
            }
        }
    }

    public function hookDisplayTop($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayJolisearch($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookdisplayMobileTopSiteMap($params)
    {
        $this->smarty->assign(array('hook_mobile' => true, 'instantsearch' => false));
        $params['hook_mobile'] = true;
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplaySearch($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayNav($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayHeaderLeft($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayHeaderTopLeft($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function hookDisplayMobileBar($params)
    {
        return $this->displaySearchBar(Tools::substr(__FUNCTION__, 4), $params);
    }

    public function adaptConfigForm($arr)
    {
        if (is_array($arr)) {
            if (is_array($arr['form']['input'])) {
                foreach ($arr['form']['input'] as &$input) {
                    if ($input['type'] == 'switch') {
                        $input['type'] = 'radio';
                        $input['class'] = 't';
                    }
                }
            }
        }

        return $arr;
    }

    public function includeJqueryUi()
    {
        if (Configuration::get(AJS_COMPAT) || $this->ps15) {
            $this->context->controller->addJS($this->_path . 'views/js/jquery/jquery-1.11.2.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/jquery/jquery-ui-1.9.2.custom.js');
            $this->context->controller->addJqueryPlugin('autocomplete.html', $this->_path . 'views/js/jquery/plugins/');
            $this->context->controller->addJS($this->_path . 'views/js/jquery/jquery-fix-compatibility.js');
        } else {
            $this->context->controller->addJS($this->_path . 'views/js/jquery/jquery-ui-1.9.2.custom.js');
            $this->context->controller->addJqueryPlugin(
                'autocomplete.html',
                $this->_path . 'views/js/jquery/plugins/'
            );
        }

        $this->context->controller->addCSS($this->_path . 'views/css/no-theme/jquery-ui-1.9.2.custom.css', 'all');
    }

    public function retroCompatIncludes()
    {
        $this->includeJqueryUi();

        if (Configuration::get(AJS_BLOCKSEARCH_CSS)) {
            $this->context->controller->addCSS(_THEME_CSS_DIR_ . 'modules/blocksearch/blocksearch.css');
        } else {
            if ($this->ps16 && !Configuration::get(AJS_USE_STD_SEARCH_BAR)) {
                $this->context->controller->addCSS($this->_path . 'views/css/ambjolisearch-1.6.css', 'all');
            }

            if ($this->ps15) {
                $this->context->controller->addCSS($this->_path . 'views/css/ambjolisearch-1.5.css', 'all');
            }
        }

        $this->context->controller->addCSS(_THEME_CSS_DIR_ . 'category.css', 'all');
        $this->context->controller->addCSS(_THEME_CSS_DIR_ . 'product_list.css');

        if ($this->ps16 && Configuration::get(AJS_USE_STD_SEARCH_BAR)) {
            $theme_blocksearch_js = _THEME_CSS_DIR_ . 'blocksearch/blocksearch.js';
            $this->context->controller->removeJS($theme_blocksearch_js);

            $blocksearch_js = _MODULE_DIR_ . 'blocksearch/blocksearch.js';
            $this->context->controller->removeJS($blocksearch_js);

            // Compatibility with Warehouse theme
            if (Module::isEnabled('blocksearch_mod')) {
                $theme_blocksearch_mod_js = _THEME_CSS_DIR_ . 'blocksearch_mod/blocksearch_mod.js';
                $this->context->controller->removeJS($theme_blocksearch_mod_js);

                $blocksearch_mod_js = _MODULE_DIR_ . 'blocksearch_mod/blocksearch_mod.js';
                $this->context->controller->removeJS($blocksearch_mod_js);
            }

            // Compatibility with ThemeMonster search
            if (Module::isEnabled('tmsearch')) {
                $tmsearch_js = 'tmsearch/views/js/tmsearch.js';
                $this->context->controller->removeJS(_THEME_CSS_DIR_.$tmsearch_js);
                $this->context->controller->removeJS(_MODULE_DIR_.$tmsearch_js);
            }
        }
    }

    // Widget Interface for Prestashop 1.7+
    public function getWidgetVariables($hookName, array $configuration = array())
    {
        $joli_link = new JoliLink($this->context->link);
        $action = $joli_link->getModuleLink('ambjolisearch', 'jolisearch', array(), Tools::usingSecureMode());

        $widgetVariables = array(
            'search_controller_url' => $action,
        );

        if (!array_key_exists('search_string', $this->context->smarty->getTemplateVars())) {
            $widgetVariables['search_string'] = '';
        }

        return $widgetVariables;
    }

    public function renderWidget($hookName, array $configuration = array())
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        $this->context->controller->addCSS($this->_path . 'views/css/ambjolisearch-1.7.css', 'all');

        return $this->display(__FILE__, 'views/templates/hook/jolisearch_widget.tpl');
    }

    public static function getImageFormattedName($name)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            return ImageType::getFormatedName($name);
        } else {
            return ImageType::getFormattedName($name);
        }
    }

    public function hookDisplayBackofficeHeader($params)
    {
        parent::hookDisplayBackofficeHeader($params);
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'ambjolisearch') {
            $this->context->controller->addCSS($this->_path . 'views/css/backoffice.css', 'all');
        }
    }

    public function hookDisplayBackofficeFooter($params)
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'ambjolisearch') {
            return '<script type="text/javascript" src="' . $this->_path . 'views/js/backoffice.js"></script>';
        }
    }

    private function setControllersLayout()
    {
        if ($this->ps17) {
            $theme_repository = (new PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder($this->context, Db::getInstance()))->buildRepository();
            $theme_manager = (new PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder($this->context, Db::getInstance()))->build();
            $theme = $theme_repository->getInstanceByName($this->context->shop->theme->getName());
            $search_layout = $theme->getLayoutNameForPage('search');
            $theme_layouts = $theme->getPageLayouts();
            if (empty($theme_layouts) || empty($search_layout)) {
                return;
            }
            $theme_layouts['module-' . $this->name . '-jolisearch'] = $search_layout;
            $this->context->shop->theme->setPageLayouts($theme_layouts);
            $theme_manager->saveTheme($this->context->shop->theme);
        } else {
            if (isset($this->context) && !empty($this->context->theme) && is_object($this->context->theme)
                && Validate::isLoadedObject($this->context->theme) && method_exists($this->context->theme, 'hasColumns')) {
                $columns = $this->context->theme->hasColumns('search');
                if (is_array($columns) && isset($columns['left_column']) && isset($columns['right_column'])) {
                    $controller_name = 'module-' . $this->name . '-jolisearch';
                    $id_meta = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_meta` FROM `'._DB_PREFIX_.'meta` WHERE `page` = "'.pSQL($controller_name).'"');
                    if (!empty($id_meta)) {
                        Db::getInstance()->insert('theme_meta', array(
                            array('id_theme' => (int)$this->context->theme->id, 'id_meta' => (int)$id_meta, 'left_column' => (int)$columns['left_column'], 'right_column' => (int)$columns['right_column']),
                        ), false, false, Db::REPLACE);
                    }
                }
            }
        }
    }
}
