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

class AmbSearch
{
    public $id_lang;
    public $expr;
    public $page_number;
    public $limit;
    public $order_by;
    public $order_way;
    public $context;
    public $db;
    public $mode = 'normal';
    public $id_customer;
    public $ajax;
    public $search_all_terms = true;

    public $where;
    public $having;
    public $nb = 0;

    public $main_order_by = '';

    protected $results;
    protected $product_ids = array();

    public $words = array();

    public $categories = array();
    public $manufacturers = array();

    public $search_parameter = 'search_query';

    public function __construct($use_cookie, $context, $module)
    {
        $this->module = $module;
        $this->db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        if ($this->module->ps17)
            $this->search_parameter = 's';

        if (!$context) {
            $this->context = Context::getContext();
        } else {
            $this->context = $context;
        }

        if ($use_cookie) {
            $this->id_customer = $this->context->customer->id;
        } else {
            $this->id_customer = 0;
        }

        $this->search_all_terms = Configuration::hasKey(AJS_SEARCH_ALL_TERMS) ? (bool) Configuration::get(AJS_SEARCH_ALL_TERMS) : true;
        $this->also_try_or_comparator = Configuration::hasKey(AJS_ALSO_TRY_OR_COMPARATOR) ? (bool) Configuration::get(AJS_ALSO_TRY_OR_COMPARATOR) : true;
        $this->approximation_level = (Configuration::hasKey(AJS_APPROXIMATION_LEVEL) ? Configuration::get(AJS_APPROXIMATION_LEVEL) : (Configuration::get(AJS_APPROXIMATIVE_SEARCH) ? 2 : 0));
    }

    public function search($id_lang, $expr, $page_number, $limit, $order_by, $order_way, $id_category = null, $id_manufacturer = null)
    {
        static $findCache = array();

        $this->id_lang = $id_lang;
        $this->iso_lang = Language::getIsoById($this->id_lang);
        $this->expr = $expr;
        $this->page_number = $page_number;
        $this->limit = $limit;
        $this->order_by = $order_by;
        if (strpos($this->order_by, '.') > 0) {
            $this->order_by = explode('.', $this->order_by);
            $this->order_by = pSQL($this->order_by[0]) . '.`' . pSQL($this->order_by[1]) . '`';
        }
        $this->order_way = $order_way;

        $show_only_products_in_stock = Configuration::hasKey(AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK) ? (bool) Configuration::get(AJS_ONLY_SEARCH_PRODUCTS_IN_STOCK) : false;

        // fallback for functions calls from faceted modules (Amazzing Filters & AdvancedSearch4)
        if (is_null($id_category) && Tools::getValue('ajs_cat', false)) {
            $id_category = Tools::getValue('ajs_cat', false);
        }

        if (is_null($id_manufacturer) && Tools::getValue('ajs_man', false)) {
            $id_manufacturer = Tools::getValue('ajs_man', false);
        }

        // use cache only after object attributes are initialized (issue when differents languages are used)
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset($findCache[$cacheKey])) {
            $this->product_ids = $findCache[$cacheKey];
            return;
        }

        if (!Validate::isOrderBy($this->order_by) || !Validate::isOrderWay($this->order_way)) {
            return;
        }

        $this->words = explode(' ', Search::sanitize($this->expr, $this->id_lang, false, $this->iso_lang));

        if (count($this->words) > 1) {
            $this->words['concat'] = str_replace(' ', '', Search::sanitize($this->expr, $this->id_lang, false, $this->iso_lang));
        }

        $alias = '';
        $need_name = false;

        if ($this->order_by == 'price') {
            $alias = 'product_shop.';
        }

        if ($this->order_by == 'name') {
            $need_name = true;
            $alias = 'pl.';
        }

        // if ($this->order_by == 'date_add' || $this->order_by == 'date_upd') {
        //     $need_name = true;
        //     $alias = 'product_shop.';
        // }

        $this->main_order_by = ($this->order_by ? 'ORDER BY  ' . $alias . $this->order_by : '') . ($this->order_way ? ' ' . $this->order_way : '') . ',p.id_product';

        $word_conditions = array();
        $check_terms = array();

        $categories_restriction = '';
        if (!empty($id_category)) {
            $search_in_subcategories = Configuration::get(AJS_SEARCH_IN_SUBCATEGORIES);
            if ($search_in_subcategories) {
                $cat = new Category((int) $id_category);
                if (Validate::isLoadedObject($cat)) {
                    $categories_restriction = 'SELECT id_category FROM ' . _DB_PREFIX_ . 'category WHERE nleft >= ' . (int) $cat->nleft . ' AND nright <= ' . (int) $cat->nright;
                }
            } else {
                $categories_restriction = (int) $id_category;
            }
        }


        $eligible_products_request = '
                SELECT
                DISTINCT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_group` cg
                INNER JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.`id_category` = cg.`id_category`
                INNER JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category`
                INNER JOIN `' . _DB_PREFIX_ . 'product` p ON cp.`id_product` = p.`id_product`
                ' . Shop::addSqlAssociation('product', 'p', false) . '
                ' . ($show_only_products_in_stock ? Product::sqlStock('p', 0) : '') . '
                WHERE c.`active` = 1
                    AND product_shop.`active` = 1
                    AND product_shop.`visibility` IN ("both", "search")
                    AND product_shop.indexed = 1
                    ' . (!empty($id_category) ? ' AND cg.`id_category` IN (' . $categories_restriction . ')' : '') . '
                    ' . (!empty($id_manufacturer) ? ' AND p.`id_manufacturer` = ' . (int) $id_manufacturer : '') . '
                    ' . ($show_only_products_in_stock ? ' AND (stock.quantity IS NOT NULL AND stock.quantity > 0) ' : '') . '
                    AND cg.`id_group` ' . (!$this->id_customer ? '=' . (int) Configuration::get('PS_UNIDENTIFIED_GROUP') : 'IN (
                        SELECT id_group FROM ' . _DB_PREFIX_ . 'customer_group
                        WHERE id_customer = ' . (int) $this->id_customer . ')
                    ');

        $this->module->log($eligible_products_request, __FILE__, __METHOD__, __LINE__, '$eligible_products_request');

        $nb_suitable_words = 0;

        $use_approximative_search = true;
        $use_approximative_on_references = (bool) Configuration::get(AJS_USE_APPROXIMATIVE_FOR_REFERENCES);
        $reference_pattern = '/^([a-z-_]*\d+[a-z-_]*)*$/i';

        foreach ($this->words as $key => $word) {
            if (!empty($word) && (Tools::strlen($word) >= (int) Configuration::get('PS_SEARCH_MINWORDLEN') || in_array($this->iso_lang, array('zh', 'tw', 'ja')))) {
                $naked_word = $word;
                $word = str_replace('%', '\\%', $word);
                $word = str_replace('_', '\\_', $word);

                if ((int) Configuration::get(PS_SEARCH_START) == 1) {
                    $my_word = $word[0] == '-'
                    ? '%' . pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)) . '%'
                    : '%' . pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)) . '%';
                } else {
                    $my_word = $word[0] == '-'
                    ? pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)) . '%'
                    : pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)) . '%';
                }

                $my_term_word = $word[0] == '-'
                ? '%' . pSQL(Tools::substr($word, 1, PS_SEARCH_MAX_WORD_LENGTH)) . '%'
                : '%' . pSQL(Tools::substr($word, 0, PS_SEARCH_MAX_WORD_LENGTH)) . '%';

                if ($use_approximative_search && !in_array($this->iso_lang, array('zh', 'tw', 'ja')) && ($use_approximative_on_references || !(bool) preg_match($reference_pattern, $naked_word, $matches))) {
                    //If we are not in compat mode, we check for synonyms
                    $request = '
                                    SELECT sw.id_word
                                    FROM ' . _DB_PREFIX_ . 'search_word sw
                                    WHERE word LIKE "' . $my_word . '"';

                    $results = $this->db->executeS($request);

                    if (($results === false || count($results) == 0) && $key . '' != 'concat') {
                        $synonyms_results = $this->searchSynonyms($my_word);

                        if (count($synonyms_results['ids']) == 0 && $use_approximative_search) {
                            if ($this->applyLevenshtein($my_word, $naked_word, $id_lang)) {
                                $synonyms_results = $this->searchSynonyms($my_word);
                            } else {
                                //return;
                            }
                        }

                        if (count($synonyms_results['ids']) > 0) {
                            $word_conditions[] = '
                                (sw.id_word IN(' . implode(',', $synonyms_results['ids']) . '))';
                        }
                    } else {
                        if ($results !== false) {
                            $results_ids = array();
                            foreach ($results as $result) {
                                $results_ids[$result['id_word']] = $result['id_word'];
                            }
                            if (count($results_ids) > 0) {
                                $word_conditions[] = '(sw.id_word IN(' . implode(',', $results_ids) . '))';
                            }
                        }
                    }
                } else {
                    //If we are in compat mode, no synonym check
                    $word_conditions[] = '(sw.word LIKE "' . $my_word . '")';
                }

                if ($key . '' != 'concat') {
                    $nb_suitable_words++;
                    $likes = array();

                    $likes[] = 'terms LIKE "' . $my_term_word . '"';

                    if (isset($synonyms_results['words']) && is_array($synonyms_results['words'])) {
                        foreach ($synonyms_results['words'] as $synonym) {
                            $likes[] = 'terms LIKE "%' . $synonym . '%"';
                        }
                    }

                    $check_terms[] = '(' . implode(' OR ', $likes) . ')';
                }
            }
        }

        if ($nb_suitable_words == 0) {
            $this->context->smarty->assign('no_suitable_words', true);
            $this->context->smarty->assign('min_length', (int) Configuration::get('PS_SEARCH_MINWORDLEN'));
            return;
        }
        $this->where = implode(' OR ', $word_conditions);
        $this->having = (count($check_terms) > 0) ? ' HAVING ' . implode(($this->search_all_terms ? ' AND ' : ' OR '), $check_terms) : '';
        $sql_limit = $this->limit > 0 ? ' LIMIT ' . ($this->page_number - 1) * $this->limit . ',' . $this->limit : '';
        $pl = $need_name ? ' INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON si.id_product=pl.id_product AND pl.id_lang=' . (int) $id_lang . ' ' : ' ';

        if ((int) Configuration::get(AJS_MULTILANG_SEARCH) == 1) {
            $language_ids = Language::getLanguages(true, $this->context->shop->id, true);
        }

        $main_request = '
                    SELECT
                    SQL_CALC_FOUND_ROWS
                    si.id_product, SUM(si.weight) position, GROUP_CONCAT(sw.word SEPARATOR \' \') as terms,
                    IFNULL(stock.quantity, 0) as quantity
                    FROM ' . _DB_PREFIX_ . 'search_word sw
                    LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word
                    LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product=si.id_product
                    ' . Shop::addSqlAssociation('product', 'si', false)
        . $pl .
        ' ' . Product::sqlStock('p', 0) . '
        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
        WHERE ' . ((int) Configuration::get(AJS_MULTILANG_SEARCH) == 1 ? 'sw.id_lang IN (' . implode(',', $language_ids) . ')' : 'sw.id_lang = ' . (int) $id_lang)
            . ' AND sw.id_shop = ' . $this->context->shop->id . ' ' .
        (Tools::strlen($this->where) > 0 ? 'AND (' . $this->where . ')' : '') . '
                        AND si.id_product IN(' . $eligible_products_request . ')
                    GROUP BY si.id_product '
        . $this->having
        . $this->main_order_by;

        //. $sql_limit;

        $this->module->log($main_request, __FILE__, __METHOD__, __LINE__, '$main_request');

        $results = $this->db->executeS($main_request);
        $this->nb = $this->db->getValue('SELECT FOUND_ROWS() AS nb', false);

        if (is_array($results) && count($results) > 0) {
            foreach ($results as $row) {
                $this->full_product_ids[] = $row['id_product'];
            }
            $this->product_ids = array_slice($this->full_product_ids, ($this->page_number - 1) * $this->limit, $this->limit);
        } elseif ($this->also_try_or_comparator && $this->search_all_terms === true) {
            $this->search_all_terms = false;
            $this->search($id_lang, $expr, $page_number, $limit, $order_by, $order_way, $id_category, $id_manufacturer);
        } else {
        }
        $findCache[$cacheKey] = $this->getResultIds();
    }

    public function getResults($ajax = false, $limit = false)
    {
        if (count($this->product_ids) == 0) {
            return array();
        }

        if ((int) $limit > 0) {
            $product_ids = array_slice($this->product_ids, 0, (int) $limit);
        } else {
            $product_ids = $this->product_ids;
        }

        if ($ajax) {
            if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
                $image_join = '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
                Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image`
                        AND il.`id_lang` = ' . (int) $this->id_lang . ')';
            } else {
                $image_join = '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                        ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $this->context->shop->id . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image`
                        AND il.`id_lang` = ' . (int) $this->id_lang . ')';
            }
            $image_select = 'IFNULL(image_shop.`id_image`, (SELECT i.`id_image` FROM ' . _DB_PREFIX_ . 'image i where i.`id_product`= p.`id_product` ORDER BY i.cover DESC LIMIT 1)) imgid';

            $sql = 'SELECT DISTINCT pl.name pname, cl.name cname,
                    cl.link_rewrite crewrite, pl.link_rewrite prewrite, pl.link_rewrite link_rewrite,
                    m.`name` mname, m.`id_manufacturer` manid, cs.id_category as catid,
                    p.*,
                    product_shop.*,
                    ' . $image_select . '
                FROM ' . _DB_PREFIX_ . 'product p
                ' . Shop::addSqlAssociation('product', 'p') . '
                INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = ' . (int) $this->id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON cs.id_category=product_shop.id_category_default
                    AND cs.id_shop=' . $this->context->shop->id . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON (
                    product_shop.`id_category_default` = c.`id_category`
                    AND c.active=1
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
                    c.`id_category` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int) $this->id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
                ' . $image_join . '
                WHERE p.`id_product` IN(' . implode(',', $product_ids) . ')';

            $this->module->log($sql, __FILE__, __METHOD__, __LINE__, 'if $ajax $sql');
        } else {
            if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
                $image_join = '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
                Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image`
                        AND il.`id_lang` = ' . (int) $this->id_lang . ')';
            } else {
                $image_join = '
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                        ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $this->context->shop->id . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image`
                        AND il.`id_lang` = ' . (int) $this->id_lang . ')';
            }
            $image_select = 'IFNULL(image_shop.`id_image`, (SELECT i.`id_image` FROM ' . _DB_PREFIX_ . 'image i where i.`id_product`= p.`id_product` ORDER BY i.cover DESC LIMIT 1)) `id_image`,';

            $sql = 'SELECT DISTINCT(p.id_product), p.*, product_shop.*, stock.out_of_stock,
                IFNULL(stock.quantity, 0) as quantity,
                pl.`description_short`, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`name`,
                ' . $image_select . '
             il.`legend`, m.`name` manufacturer_name,
             product_attribute_shop.`id_product_attribute`, 1 as position,
                DATEDIFF(
                    p.`date_add`,
                    DATE_SUB(
                        NOW(),
                        INTERVAL '
            . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ?
                Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
                    )
                ) > 0 new
                FROM ' . _DB_PREFIX_ . 'product p
                ' . Shop::addSqlAssociation('product', 'p') . '
                INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = ' . (int) $this->id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
                ' . Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1')
            . ' ' . Product::sqlStock('p', 0) . '
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
                ' . $image_join . '
                WHERE p.`id_product` IN(' . implode(',', $product_ids) . ')
                GROUP BY p.id_product
                ' . $this->main_order_by;

            $this->module->log($sql, __FILE__, __METHOD__, __LINE__, 'if not $ajax $sql');
        }

        $result_properties = $this->db->executeS($sql);
        $dbresults = array();
        $dbres = array();

        if ($this->order_by == 'position') {
            if (is_array($result_properties)) {
                foreach ($result_properties as $v) {
                    $dbres[$v['id_product']] = $v;
                }
            }

            if (is_array($product_ids)) {
                foreach ($product_ids as $product_id) {
                    if (isset($dbres[$product_id])) {
                        $dbresults[] = $dbres[$product_id];
                    }
                }
            }
        } else {
            $dbresults = $result_properties;
        }

        $dbresults = Product::getProductsProperties((int) $this->id_lang, $dbresults);
        $this->categories = $this->getCategoriesOfProducts($this->id_lang, $this->context->shop->id, $this->full_product_ids, array('where' => $this->where, 'having' => $this->having), $ajax);
        $this->manufacturers = $this->getManufacturersOfProducts($this->id_lang, $this->context->shop->id, $this->full_product_ids, array('where' => $this->where, 'having' => $this->having), $ajax);

        return $dbresults;
    }

    public function getCategories()
    {
        if (isset($this->categories) && count($this->categories) > 0) {
            foreach ($this->categories as &$row) {
                $row['id_image'] = file_exists(_PS_CAT_IMG_DIR_ . $row['id_category'] . '.jpg') ?
                (int) $row['id_category']
                : Language::getIsoById($this->id_lang) . '-default';
                $row['legend'] = 'no picture';
                $row['image']['legend'] = 'no picture';

                $cat = new Category($row['id_category'], $this->context->language->id);
                $row['image']['large']['url'] = $this->module->getCategoryImage($cat, $this->context->language->id);
                $row['thumb_url'] = $this->getCategoryThumb($cat, $this->context->language->id);
                $row['url'] = $this->context->link->getCategoryLink($cat);
            }
        } else {
            $this->categories = array();
        }

        return $this->categories;
    }

    public function getManufacturers()
    {
        if (isset($this->manufacturers) && count($this->manufacturers) > 0) {
            foreach ($this->manufacturers as &$row) {
                $row['id_image'] = file_exists(_PS_MANU_IMG_DIR_ . $row['id_manufacturer'] . '.jpg') ?
                (int) $row['id_manufacturer']
                : Language::getIsoById($this->id_lang) . '-default';
                $row['legend'] = 'no picture';
                $row['image']['legend'] = 'no picture';

                $cat = new Manufacturer($row['id_manufacturer'], $this->context->language->id);
                $row['image']['large']['url'] = $this->module->getManufacturerImage($cat, $this->context->language->id);
                $row['url'] = $this->context->link->getManufacturerLink($cat);
            }
        } else {
            $this->manufacturers = array();
        }

        return $this->manufacturers;
    }

    public function getTotal()
    {
        return $this->nb;
    }

    public function getResultIds()
    {
        return $this->product_ids;
    }

    public function presentForAjaxResponse($show_price = true, $show_features = true, $max_items = null, $allow_filter_results = false)
    {
        if (empty($max_items)) {
            $max_items = array();
            $max_items['all'] = Configuration::get(AJS_MAX_ITEMS_KEY);
            $max_items['manufacturers'] = Configuration::get(AJS_MAX_MANUFACTURERS_KEY);
            $max_items['categories'] = Configuration::get(AJS_MAX_CATEGORIES_KEY);
            $max_items['products'] = Configuration::hasKey(AJS_MAX_PRODUCTS_KEY) ? Configuration::get(AJS_MAX_PRODUCTS_KEY) : 10;
        }

        $search_results = $this->getResults(true, $max_items['products']);
        $total = $this->getTotal();
        $sr_categories = $this->getCategories();
        $sr_manufacturers = $this->getManufacturers();

        if ($total == 0) {
            die(Tools::jsonEncode(array(
                array(
                    'type' => 'no_results_found',
                ))));
        }

        $show_parent_category = Configuration::get(AJS_SHOW_PARENT_CATEGORY);
        $filter_on_parent_category = Configuration::get(AJS_FILTER_ON_PARENT_CATEGORY);

        $price_display = Product::getTaxCalculationMethod();
        $show_price = $show_price
            && (!(bool) Configuration::get('PS_CATALOG_MODE') && (bool) Group::getCurrent()->show_prices);

        foreach ($search_results as &$product) {
            $link = $this->context->link->getProductLink(
                $product['id_product'],
                $product['prewrite'],
                $product['crewrite']
            );

            $product['link'] = $link . '?'. $this->search_parameter . '=' . $this->expr . '&fast_search=fs';
            $product['img'] = $this->module->getProductImage($product);
            $product['type'] = 'product';

            $feats = array();

            if ($show_features) {
                foreach ($product['features'] as $feature) {
                    $feats[] = $feature['name'] . ': ' . $feature['value'];
                }
            }

            $product['feats'] = implode(', ', $feats);

            if ($show_price && isset($product['show_price']) && $product['show_price']) {
                if (!$price_display) {
                    $product['price_raw'] = $product['price'];
                    $product['price'] = Tools::displayPrice(
                        $product['price'],
                        (int) $this->context->cookie->id_currency
                    );
                } else {
                    $product['price_raw'] = $product['price_tax_exc'];
                    $product['price'] = Tools::displayPrice(
                        $product['price_tax_exc'],
                        (int) $this->context->cookie->id_currency
                    );
                }
            } else {
                $product['price_raw'] = '';
                $product['price'] = '';
            }
        }

        $manufacturers = array();
        if (!empty($sr_manufacturers)) {
            foreach ($sr_manufacturers as $manufacturer) {
                $manufacturers[$manufacturer['id_manufacturer']] = $manufacturer;
            }
        }

        $search_manufacturers = array();
        foreach ($manufacturers as $manufacturer) {
            $manu = new Manufacturer();
            $manu->id = $manufacturer['id_manufacturer'];

            $link = '#';
            if ($allow_filter_results) {
                $link = $this->context->link->getModuleLink('ambjolisearch', 'jolisearch', array($this->search_parameter => $this->expr, 'ajs_man' => (int) $manufacturer['id_manufacturer'], 'fast_search' => 'fs'));
            } else {
                $link = $this->context->link->getManufacturerLink($manu, Tools::link_rewrite($manufacturer['name'])) . '?'. $this->search_parameter . '=' . $this->expr . '&fast_search=fs';
            }

            $search_manufacturers[] = array('type' => 'manufacturer',
                'man_id' => $manufacturer['id_manufacturer'],
                'man_name' => $manufacturer['name'],
                'img' => $this->module->getManufacturerImage($manu),
                'link' => $link,
                'products_count' => $manufacturer['products_count'],
            );
        }


        $categories = array();
        if (!empty($sr_categories)) {
            foreach ($sr_categories as $category) {
                $categories[$category['id_category']] = $category;
            }
        }

        $search_categories = array();
        foreach ($categories as $category) {
            $cat = new Category($category['id_category'], $this->id_lang);
            $cname = $cat->name;

            if ($filter_on_parent_category) {
                $parent = new Category($cat->id_parent, $this->id_lang);
                if (isset($categories[$parent->id]) || isset($search_categories[$parent->id])) {
                    // parent is already in list or was already done
                    continue;
                } elseif ($parent->level_depth >= 2) {
                    $cat = $parent;
                    $cname = $cat->name;
                    $category['id_category'] = $cat->id;
                }
            }

            if ($show_parent_category) {
                $parent = new Category($cat->id_parent, $this->id_lang);
                if ($parent->level_depth >= 2) {
                    $cname = $parent->name . ' > ' . $cname;
                }
            }

            if ($allow_filter_results) {
                $link = $this->context->link->getModuleLink('ambjolisearch', 'jolisearch', array($this->search_parameter => $this->expr, 'ajs_cat' => (int) $category['id_category'], 'fast_search' => 'fs'));
            } else {
                $link = $this->context->link->getCategoryLink($cat, $cat->link_rewrite, $this->id_lang) . '?'. $this->search_parameter . '=' . $this->expr . '&fast_search=fs';
            }

            $search_categories[$category['id_category']] = array('type' => 'category',
                'cat_id' => $category['id_category'],
                'cat_name' => $cname,
                'img' => $this->module->getCategoryImage($cat, $this->id_lang),
                'link' => $link,
                'products_count' => $category['products_count'],
            );
        }

        $search = array(
            'products' => array(),
            'manufacturers' => array(),
            'suppliers' => array(),
            'categories' => array(),
        );
        if (count($search_manufacturers) > 0) {
            if (isset($max_items['manufacturers']) && Tools::strlen($max_items['manufacturers']) > 0) {
                $search['manufacturers'] = array_slice($search_manufacturers, 0, (int) $max_items['manufacturers']);
            }

            foreach ($search['manufacturers'] as &$manufacturer) {
                if ((int) $manufacturer['products_count'] == 0) {
                    unset($search['manufacturers'][$manufacturer['man_id']]);
                    continue;
                }
                $manufacturer['results'] = (int) $manufacturer['products_count'];
                $manufacturer['man_results'] = (int) $manufacturer['products_count'] . ' ' . $this->module->l('products found', 'AmbSearch');
            }
        }

        if (count($search_categories) > 0) {
            if (isset($max_items['categories']) && Tools::strlen($max_items['categories']) > 0) {
                $search['categories'] = array_slice($search_categories, 0, (int) $max_items['categories']);
            }

            foreach ($search['categories'] as &$category) {
                // do not display categories if there is no results in it
                // (possible if search in subcategories is disabled and show only parent is enabled)
                if ((int) $category['products_count'] == 0) {
                    unset($search['categories'][$category['cat_id']]);
                    continue;
                }

                $category['results'] = (int) $category['products_count'];
                $category['cat_results'] = (int) $category['products_count'] . ' ' . $this->module->l('products found', 'AmbSearch');
            }
        }

        if (count($search_results) > 0) {
            if (isset($max_items['products']) && Tools::strlen($max_items['products']) > 0) {
                $search['products'] = array_slice($search_results, 0, (int) $max_items['products']);
            } else {
                $search['products'] = $search_results;
            }
        }

        return $search;
    }

    private function getCategoriesOfProducts($id_lang, $id_shop, $products, $criteria, $ajax = false)
    {
        $nb_categories = $ajax ? pSQL(Configuration::hasKey(AJS_MAX_CATEGORIES_KEY) ? Configuration::get(AJS_MAX_CATEGORIES_KEY) : 0) : pSQL(Configuration::get(AJS_SHOW_CATEGORIES));

        $categories_request = '';

        if ($nb_categories > 0) {
            $categories_limit = ' LIMIT 0,' . $nb_categories;

            $categories_request = '
                         SELECT
                        DISTINCT cp.id_category, pscl.*, SUM(si.weight) position,
                        GROUP_CONCAT(sw.word SEPARATOR \' \') as terms, count(distinct cp.id_product) as products_count
                        FROM ' . _DB_PREFIX_ . 'search_word sw
                        LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word
                        ' . Shop::addSqlAssociation('product', 'si', false) . '
                        LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON cp.id_product=si.id_product
                        LEFT JOIN ' . _DB_PREFIX_ . 'category_group cg ON cg.id_category=cp.id_category
                        INNER JOIN ' . _DB_PREFIX_ . 'category psc ON psc.id_category=cp.id_category AND psc.is_root_category=0
                            AND psc.active = 1
                        ' . (Configuration::get(AJS_ONLY_DEFAULT_CATEGORIES) ? ' AND psc.id_category=product_shop.id_category_default' : '') . '
                        ' . (Configuration::get(AJS_ONLY_LEAF_CATEGORIES) ? ' AND psc.nright-psc.nleft = 1' : '') . '
                        ' . Shop::addSqlAssociation('category', 'psc') . '
                        LEFT JOIN ' . _DB_PREFIX_ . 'category_lang pscl ON pscl.id_category=psc.id_category
                            AND pscl.id_lang=' . (int) $id_lang . '
                            AND pscl.id_shop = ' . (int) $id_shop . '
                        WHERE sw.id_lang = ' . (int) $id_lang . '
                            AND sw.id_shop = ' . (int) $id_shop . '
                            AND (' . $criteria['where'] . ')
                            AND si.id_product IN(' . implode(',', $products) . ')
                            AND cg.`id_group` ' . (!$this->id_customer ? '=' . (int) Configuration::get('PS_UNIDENTIFIED_GROUP') : 'IN (
                        SELECT id_group FROM ' . _DB_PREFIX_ . 'customer_group
                        WHERE id_customer = ' . (int) $this->id_customer . ')')
                        .' GROUP BY cp.id_category'
                . $criteria['having']
                . ' ORDER BY position DESC '
                . $categories_limit;

            //$module->log($categories_request, __FILE__, __METHOD__, __LINE__, '$categories_request');

            $categories = Db::getInstance()->ExecuteS($categories_request);
            return $categories;
        } else {
            return array();
        }
    }

    private function getManufacturersOfProducts($id_lang, $id_shop, $products, $criteria, $ajax = false)
    {
        $nb_manufacturers = $ajax ? pSQL(Configuration::hasKey(AJS_MAX_MANUFACTURERS_KEY) ? Configuration::get(AJS_MAX_MANUFACTURERS_KEY) : 0) : 0;

        $manufacturers_request = '';

        if ($nb_manufacturers > 0) {
            $manufacturers_limit = ' LIMIT 0,' . $nb_manufacturers;

            if ((int) Configuration::get(AJS_MULTILANG_SEARCH) == 1) {
                $language_ids = Language::getLanguages(true, $this->context->shop->id, true);
            }

            $manufacturers_request = '
                         SELECT
                        DISTINCT m.id_manufacturer, m.*, ml.*, SUM(si.weight) position,
                        GROUP_CONCAT(sw.word SEPARATOR \' \') as terms, count(distinct si.id_product) as products_count
                        FROM ' . _DB_PREFIX_ . 'search_word sw
                        LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word
                        ' . Shop::addSqlAssociation('product', 'si', false) . '
                        INNER JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = si.id_product
                        INNER JOIN ' . _DB_PREFIX_ . 'manufacturer m ON p.id_manufacturer = m.id_manufacturer
                        INNER JOIN ' . _DB_PREFIX_ . 'manufacturer_lang ml ON p.id_manufacturer = ml.id_manufacturer AND ml.id_lang = sw.id_lang
                        WHERE ' . ((int) Configuration::get(AJS_MULTILANG_SEARCH) == 1 ? 'sw.id_lang IN (' . implode(',', $language_ids) . ')' : 'sw.id_lang = ' . (int) $id_lang) . '
                            AND sw.id_shop = ' . (int) $id_shop . '
                            AND (' . $criteria['where'] . ')
                            AND si.id_product IN(' . implode(',', $products) . ')
                         GROUP BY m.id_manufacturer'
                . $criteria['having']
                . ' ORDER BY position DESC '
                . $manufacturers_limit;

            //$module->log($manufacturers_request, __FILE__, __METHOD__, __LINE__, '$manufacturers_request');
            $manufacturers = Db::getInstance()->ExecuteS($manufacturers_request);
            return $manufacturers;
        } else {
            return array();
        }
    }

    private function searchSynonyms($my_word)
    {
        $request = '
                        SELECT DISTINCT synonyms.id_word, sw.word
                        FROM ' . _DB_PREFIX_ . 'ambjolisearch_synonyms synonyms
                        LEFT JOIN ' . _DB_PREFIX_ . 'search_word sw
                            ON synonyms.id_word = sw.id_word
                        WHERE
                            synonyms.synonym LIKE "' . pSQL($my_word) . '"';

        $synonyms = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($request);
        $return = array('words' => array(), 'ids' => array());
        foreach ($synonyms as $synonym) {
            $return['words'][] = $synonym['word'];
            $return['ids'][] = $synonym['id_word'];
        }

        return $return;
    }

    private function applyLevenshtein($my_word, $naked_word, $id_lang)
    {

        if (Tools::strlen($naked_word) <= 2) {
            return false;
        }

        //Levehnstein procedure
        $cuts = array();
        $cutting = '';
        $cutsize = 3;
        $source_word = '__' . $naked_word . '__';

        for ($i = 0, $max = (Tools::strlen($source_word) - $cutsize + 1); $i < $max; $i++) {
            $cut = '';
            for ($j = 0; $j < $cutsize; $j++) {
                $cut .= $source_word{($i + $j)};
            }

            $cuts[] = $cut;
        }

        foreach ($cuts as $key => &$cut) {
            $cut = '%' . $cut . '%';
            $cut = preg_replace('/(%_{1,2})|(_{1,2}%)/', '', $cut);
        }

        $count = count($cuts);

        $clean_cuts = array();

        for ($i = 0; $i < $count; $i++) {
            for ($j = $count - 1; $j >= 0; $j--) {
                if (((substr_count($cuts[$i], '%') == 1 && substr_count($cuts[$j], '%') == 1) && Tools::strlen($naked_word) > 5) || $cuts[$i] == $cuts[$j]) {
                    continue;
                } else {
                    if (!isset($clean_cuts[$cuts[$i] . '-' . $cuts[$j]]) && !isset($clean_cuts[$cuts[$j] . '-' . $cuts[$i]])) {
                        $clean_cuts[$cuts[$i] . '-' . $cuts[$j]] = '(sw.word LIKE "' . $cuts[$i] . '" AND sw.word LIKE "' . $cuts[$j] . '")';
                    }
                }
            }
        }

        $cutting = implode(' OR ', $clean_cuts);

        $request = '
            SELECT COUNT(sw.word) as nb_words
            FROM ' . _DB_PREFIX_ . 'search_word sw
            WHERE word="' . $naked_word . '"';

        $existing_words = Db::getInstance()->executeS($request);

        if ($existing_words[0]['nb_words'] == 0) {
            $request = '
                SELECT
                DISTINCT sw.id_word, sw.word
                FROM ' . _DB_PREFIX_ . 'search_word sw
                LEFT JOIN ' . _DB_PREFIX_ . 'search_index si ON sw.id_word = si.id_word
                WHERE sw.id_lang = ' . (int) $id_lang . '
                    AND sw.id_shop = ' . Context::getContext()->shop->id . '
                    AND (' . $cutting . ')';

            $this->module->log($request, __FILE__, __METHOD__, __LINE__, 'levenhstein $request');
            $filtered_results = Db::getInstance()->executeS($request, false);

            $weighted_results = array();
            while ($row = Db::getInstance()->nextRow($filtered_results)) {
                $lvs = levenshtein($naked_word, Tools::substr($row['word'], 0, Tools::strlen($naked_word)));
                $weighted_results[$lvs][] = $row;
            }

            $settings = AmbJoliSearch::$approximation_settings[$this->approximation_level];

            $hard_limit = $settings['hard_limit']; //Do not accept a lvs higher than 3
            $span = $settings['span']; //How much distances should be shown
            $minimum_results = isset($settings['minimum_results']) ? Configuration::get(AJS_MAX_PRODUCTS_KEY) : 0; //Keep spanning if less than expected results are displayed

            $selected_results = array();
            for ($i = 0; $i <= $hard_limit; $i++) {
                if (isset($weighted_results[$i]) && ($span > 0 || count($selected_results) < $minimum_results)) {
                    $selected_results = array_merge($selected_results, $weighted_results[$i]);
                    $span--;
                }
            }

            foreach ($selected_results as $result) {
                try {
                    Db::getInstance()->insert(
                        'ambjolisearch_synonyms',
                        array(
                            'synonym' => $naked_word,
                            'id_word' => $result['id_word'],
                        )
                    );

                    if (Db::getInstance()->getNumberError() == 0) {
                        $got_one = true;
                    }
                } catch (PrestaShopException $e) {
                    continue;
                }
            }
        }

        if (!isset($got_one)) {
            $got_one = false;
        }

        return $got_one;
    }

    private function getCategoryThumb($category, $id_lang)
    {
        $thumb = $category->id . '_thumb.jpg';
        if (file_exists(_PS_CAT_IMG_DIR_ . $thumb)) {
            return _THEME_CAT_DIR_ . $thumb;
        }
        return false;
    }
}
