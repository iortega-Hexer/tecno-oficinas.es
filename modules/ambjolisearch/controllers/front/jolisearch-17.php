<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced Search (AmbJoliSearch)
 *   @file       jolisearch.php
 *   @subject    main controller
 *   Support by mail: support@ambris.com
 */

require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/definitions.php';
require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/AmbSearch.php';

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class AmbjolisearchjolisearchModuleFrontController extends ProductListingFrontController
{
    public $priorities;
    public $max_items;
    public $allow;

    public $php_self;
    public $module;

    public function __construct()
    {
        $this->module = Module::getInstanceByName(Tools::getValue('module'));
        if (!$this->module->active) {
            Tools::redirect('index');
        }

        $this->page_name = 'module-' . $this->module->name . '-' . Dispatcher::getInstance()->getController();

        parent::__construct();

        $this->controller_type = 'modulefront';

        $themes_resources = array(
            'module' => $this->module->getLocalPath() . 'views/templates/front',
        );
        Context::getContext()->smarty->registerResource('jolisearch_template', new SmartyResourceModule($themes_resources));
    }

    protected function getProductSearchQuery()
    {
        $query = new ProductSearchQuery();
        $query
            ->setSortOrder(new SortOrder('product', Tools::getProductsOrder('by', 'position'), Tools::getProductsOrder('way', 'desc')))
            ->setSearchString($this->search_string)
            ->setSearchTag($this->search_tag)
        ;

        return $query;
    }

    protected function getDefaultProductSearchProvider()
    {
        return new AmbProductSearchProvider($this->module);
    }

    public function getListingLabel()
    {
        $filter_name = '';
        $id_manufacturer = Tools::getValue('ajs_man', false);
        if ((int) $id_manufacturer > 0) {
            $m = new Manufacturer($id_manufacturer);
            if (Validate::isLoadedObject($m)) {
                $filter_name = $m->name;
            }
        }

        $id_category = Tools::getValue('ajs_cat', false);
        if ((int) $id_category > 0) {
            $c = new Category($id_category, $this->context->language->id);
            if (Validate::isLoadedObject($c)) {
                $filter_name = $c->name;
            }
        }

        if (empty($filter_name)) {
            return $this->module->l('Search results for', 'jolisearch-17') . ' "' . $this->search_string.'"';
        } else {
            return $this->module->l('Search results for', 'jolisearch-17') . ' "' . $this->search_string . '" ' . $this->module->l('in', 'jolisearch-17') . ' "' . $filter_name . '"';
        }
    }

    public function init()
    {
        parent::init();

        // set php_self (only useful for AdvancedSearch4) after parent::init to avoid bad redirection
        $this->php_self = 'module-ambjolisearch-jolisearch';

        $this->search_string = Tools::getValue('s');
        if (!$this->search_string) {
            $this->search_string = Tools::getValue('search_query');
        }
        if (!$this->search_string) {
            $this->search_string = Tools::getValue('q');
        }
        $this->search_tag = Tools::getValue('tag');

        $this->context->smarty->assign(array(
            'search_string' => $this->search_string,
            'search_tag' => $this->search_tag,
        ));
    }

    public function initContent()
    {
        parent::initContent();

        $this->doProductSearch('module:ambjolisearch/views/templates/front/search-1.7.tpl', array('entity' => 'jolisearch'));
    }

    public function setTemplate($template, $params = array(), $locale = null)
    {
        if (strpos($template, 'module:') === 0) {
            $this->template = $template;
        } else {
            parent::setTemplate($template, $params, $locale);
        }
    }

    public function run()
    {
        if (Tools::getValue('ajax', false) == true) {
            // to respond using the same protocol as the caller page
            $this->ssl = Tools::usingSecureMode();
            $this->init();
            if ($this->checkAccess()) {
                $this->displayJolisearchAjax();
            }
        } else {
            parent::run();
        }
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = $this->getListingLabel();

        return $page;
    }

    public function displayJolisearchAjax()
    {
        $this->module = Module::getInstanceByName('ambjolisearch');
        $this->searcher = new AmbSearch(true, $this->context, $this->module);

        $this->max_items = array();
        $this->max_items['all'] = Configuration::get(AJS_MAX_ITEMS_KEY);
        $this->max_items['manufacturers'] = Configuration::get(AJS_MAX_MANUFACTURERS_KEY);
        $this->max_items['categories'] = Configuration::get(AJS_MAX_CATEGORIES_KEY);
        $this->max_items['products'] = Configuration::hasKey(AJS_MAX_PRODUCTS_KEY) ? Configuration::get(AJS_MAX_PRODUCTS_KEY) : 10;

        $this->priorities = array();
        $this->priorities['products'] = (int) Configuration::get(AJS_PRODUCTS_PRIORITY_KEY);
        $this->priorities['manufacturers'] = (int) Configuration::get(AJS_MANUFACTURERS_PRIORITY_KEY);
        $this->priorities['categories'] = (int) Configuration::get(AJS_CATEGORIES_PRIORITY_KEY);
        asort($this->priorities);

        $show_price = (bool) Configuration::get(AJS_SHOW_PRICES);
        $show_features = (bool) Configuration::get(AJS_SHOW_FEATURES);
        $allow_filter_results = (bool) Configuration::get(AJS_ALLOW_FILTER_RESULTS);

        $real_query = urldecode($this->search_string);
        $query = Tools::replaceAccentedChars(urldecode($this->search_string));
        $id_lang = Tools::getValue('id_lang', $this->context->language->id);

        $this->searcher->search(
            $id_lang,
            $query,
            1,
            null,
            'position',
            'desc'
        );

        $total = $this->searcher->getTotal();

        if ($total == 0) {
             die(Tools::jsonEncode(array(
                'use_rendered_products' => false,
                'products' => array(array(
                    'type' => 'no_results_found',
                )))));
        }

        $search = $this->searcher->presentForAjaxResponse($show_price, $show_features, $this->max_items, $allow_filter_results);

        if (Configuration::get(AJS_MORE_RESULTS_CONFIG)) {
            $params = array('s' => $real_query);

            $joli_link = new JoliLink($this->context->link);
            $action = $joli_link->getModuleLink('ambjolisearch', 'jolisearch', $params);

            $this->priorities['more_results'] = 999;
            $search['more_results'] = array(array(
                'type' => 'more_results',
                'link' => $action,
                'results' => $total,
            ));
        }

        $search_results = array();
        foreach (array_keys($this->priorities) as $key) {
            $search_results = array_merge($search_results, $search[$key]);
        }

        $search['settings'] = $this->module->getSettings();

        $selected_theme = Configuration::hasKey(AJS_JOLISEARCH_THEME) ? Configuration::get(AJS_JOLISEARCH_THEME) : 'classic';

        $response = array(
            'use_rendered_products' => AmbJoliSearch::$theme_settings[$selected_theme]['use_template'],
            'products' => $search_results,
            'rendered_products' => $this->renderCustomTemplate('module:ambjolisearch/views/templates/front/dropdown-list.tpl', $search),
        );

        die(Tools::jsonEncode($response));
    }

    public function setMedia()
    {
        parent::setMedia();

        if (Configuration::get('PS_COMPARATOR_MAX_ITEM')) {
            $this->addJS(_THEME_JS_DIR_ . 'products-comparison.js');
        }
    }

    protected function renderCustomTemplate($template, array $params = array())
    {
        $templateContent = '';
        $scope = $this->context->smarty->createData(
            $this->context->smarty
        );

        $scope->assign($params);

        try {
            $tpl = $this->context->smarty->createTemplate(
                $template,
                $scope
            );

            $templateContent = $tpl->fetch();
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog($e->getMessage());

            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
                $this->warning[] = $e->getMessage();
                $scope->assign(array('notifications' => $this->prepareNotifications()));

                $tpl = $this->context->smarty->createTemplate(
                    $this->getTemplateFile('_partials/notifications'),
                    $scope
                );

                $templateContent = $tpl->fetch();
            }
        }

        return $templateContent;
    }

    protected function getAjaxProducts()
    {
        // the search provider will need a context (language, shop...) to do its job
        $context = $this->getProductSearchContext();

        // the controller generates the query...
        $query = $this->getProductSearchQuery();

        $provider = $this->getDefaultProductSearchProvider();

        $resultsPerPage = 15;

        // we need to set a few parameters from back-end preferences
        $query
            ->setResultsPerPage($resultsPerPage)
            ->setPage(1)
        ;

        // We're ready to run the actual query!
        $result = $provider->runQuery(
            $context,
            $query
        );

        // prepare the products
        $products = $this->prepareMultipleProductsForTemplate(
            $result->getProducts()
        );

        $searchVariables = array(
            'label' => $this->getListingLabel(),
            'products' => $products,
            'js_enabled' => $this->ajax,
            'current_url' => $this->updateQueryString(),
        );

        return $searchVariables;
    }
}
