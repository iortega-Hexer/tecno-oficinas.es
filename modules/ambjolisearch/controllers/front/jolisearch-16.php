<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced Search (AmbJoliSearch)
 *   @file       jolisearch.php
 *
 *   @subject    main controller
 *   Support by mail: support@ambris.com
 */

require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/definitions.php';
require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/AmbSearch.php';

class AmbjolisearchjolisearchModuleFrontController extends ModuleFrontController
{
    public $priorities;
    public $max_items;
    public $allow;
    public $search_string;

    public function init()
    {
        parent::init();

        $this->search_string = Tools::getValue('s');
        if (!$this->search_string) {
            $this->search_string = Tools::getValue('search_query');
        }
        if (!$this->search_string) {
            $this->search_string = Tools::getValue('q');
        }
    }

    public function initContent()
    {
        parent::initContent();

        $this->module = Module::getInstanceByName('ambjolisearch');
        $this->searcher = new AmbSearch(true, $this->context, $this->module);

        $real_query = urldecode($this->search_string);
        $query = Tools::replaceAccentedChars($real_query);

        $id_category = Tools::getValue('ajs_cat', false);
        $id_manufacturer = Tools::getValue('ajs_man', false);

        $this->context->link = new JoliLink($this->context->link);

        if (empty($query)) {
            $results = array();
            $nb_products = 0;
            $categories = array();
        } else {
            $order_by = Tools::replaceAccentedChars(urldecode(Tools::getValue('orderby', 'position')));
            $order_way = Tools::replaceAccentedChars(urldecode(Tools::getValue('orderway', 'desc')));
            $id_lang = Tools::getValue('id_lang', $this->context->language->id);

            $product_per_page = isset($this->context->cookie->nb_item_per_page) ? (int) $this->context->cookie->nb_item_per_page : Configuration::get('PS_PRODUCTS_PER_PAGE');
            $this->productSort();
            $n = abs((int) (Tools::getValue('n', $product_per_page)));
            $p = abs((int) Tools::getValue('p', 1));

            $this->searcher->search($id_lang, $query, $p, $n, $order_by, $order_way, $id_category, $id_manufacturer);

            $results = $this->searcher->getResults();
            $nb_products = $this->searcher->getTotal();
            $categories = $this->searcher->getCategories();

            Hook::exec('actionSearch', array('expr' => $query, 'total' => $nb_products));

            if (version_compare(_PS_VERSION_, 1.6, '>=')) {
                $this->addColorsToProductList($results);
            }

            $this->pagination($nb_products);
            $this->assignProductSort();
        }

        $template_vars = array(
            'products' => $results,
            // DEPRECATED (since to 1.4), not use this: conflict with block_cart module
            'search_products' => $results,
            'nbProducts' => $nb_products,
            'search_query' => $query,
            'real_query' => $real_query,
            'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'subcategories' => $categories,
            'show_cat_desc' => pSQL(Configuration::get(AJS_SHOW_CAT_DESC)),
            'link' => $this->context->link,
            'request' => $this->context->link->getPaginationLink(false, false, false, true),
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),
            'meta_title' => $this->getListingLabel(),
        );

        if ($id_category) {
            $cat = new Category($id_category, $this->context->language->id);
            if (Validate::isLoadedObject($cat)) {
                $template_vars['amb_search_context'] = $cat->name;
            }
        }
        if ($id_manufacturer) {
            $man = new Manufacturer($id_manufacturer, $this->context->language->id);
            if (Validate::isLoadedObject($man)) {
                $template_vars['amb_search_context'] = $man->name;
            }
        }

        $this->context->smarty->assign($template_vars);

        $this->setTemplate('search-1.6.tpl');
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
            return $this->module->l('Search results for', 'jolisearch-16') . ' "' . $this->search_string.'"';
        } else {
            return $this->module->l('Search results', 'jolisearch-16') . ' "' . $this->search_string . '" ' . $this->module->l('in', 'jolisearch-16') . ' "' . $filter_name . '"';
        }
    }

    public function run()
    {
        if (Tools::getValue('ajax', false) == true) {
            // to respond using the same protocol as the caller page
            $this->ssl = Tools::usingSecureMode();
            $this->init();
            if ($this->checkAccess()) {
                $this->displayAjax();
            }
        } else {
            parent::run();
        }
    }

    public function displayAjax()
    {
        $this->module = Module::getInstanceByName('ambjolisearch');
        //$this->allow = (int) Configuration::get('PS_REWRITING_SETTINGS');

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

        $real_query = urldecode(Tools::getValue('s'));
        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('s')));
        $id_lang = Tools::getValue('id_lang', $this->context->language->id);

        $this->searcher = new AmbSearch(true, $this->context, $this->module);
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
            $params = array('search_query' => $real_query);

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
            'rendered_products' => $this->render('dropdown-list.tpl', $search),
        );

        die(Tools::jsonEncode($response));
    }

    public static function find(
        $id_lang,
        $expr,
        $page_number = 1,
        $limit = 10,
        $order_by = 'position',
        $order_way = 'desc',
        $ajax = false,
        $use_cookie = true,
        Context $context = null,
        $return_ids = false
    ) {
        if (!$context) {
            $context = Context::getContext();
        }

        $searcher = new AmbSearch($use_cookie, $context, Module::getInstanceByName('ambjolisearch'));
        $searcher->search($id_lang, $expr, $page_number, $limit, $order_by, $order_way);
        //Charge la liste des ids produit correspondant aux critères
        if ($return_ids) {
            return $searcher->getResultIds();
            //Récupère la liste des ids produit
        } else {
            return $searcher->getResults($ajax);
            //Effectue la recherche sur la base des ids produit trouvés par search()
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        if (Configuration::get('PS_COMPARATOR_MAX_ITEM')) {
            $this->addJS(_THEME_JS_DIR_ . 'products-comparison.js');
        }
    }

    public function assignProductSort()
    {
        $order_by_values = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity', 7 => 'reference');
        $order_way_values = array(0 => 'asc', 1 => 'desc');

        $orderBy = Tools::strtolower(Tools::getValue('orderby', null));
        $orderWay = Tools::strtolower(Tools::getValue('orderway', null));
        $orderByDefault = '';
        $orderWayDefault = 'asc';

        if ($orderBy == null) {
            $orderBy = '';
        }

        $this->context->smarty->assign(array(
            'orderby' => $orderBy,
            'orderbydefault' => $orderByDefault,
        ));
    }

    private function render($template, $params = array())
    {
        $scope = $this->context->smarty->createData(
            $this->context->smarty
        );

        $scope->assign($params);

        $template_path = $this->getTemplatePath($template) . ($this->module->ps15 ? $template : '');

        $tpl = $this->context->smarty->createTemplate(
            $template_path,
            $scope
        );

        return $tpl->fetch();
    }
}
