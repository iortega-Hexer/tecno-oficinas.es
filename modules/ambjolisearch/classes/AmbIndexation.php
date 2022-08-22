<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced search (AmbJoliSearch)
 *   @file       AmbIndexation.php
 *   @subject    indexation
 *   Support by mail: support@ambris.com
 */

define('AMB_PS_SEARCH_MAX_WORD_LENGTH', 15);

/* Copied from Drupal search module, except for \x{0}-\x{2f} that has been replaced by \x{0}-\x{2c}\x{2e}-\x{2f} in order to keep the char '-' */
define(
    'AMB_PREG_CLASS_SEARCH_EXCLUDE',
    '\x{0}-\x{2c}\x{2e}-\x{2f}\x{3a}-\x{40}\x{5b}-\x{60}\x{7b}-\x{bf}\x{d7}\x{f7}\x{2b0}-' .
    '\x{385}\x{387}\x{3f6}\x{482}-\x{489}\x{559}-\x{55f}\x{589}-\x{5c7}\x{5f3}-' .
    '\x{61f}\x{640}\x{64b}-\x{65e}\x{66a}-\x{66d}\x{670}\x{6d4}\x{6d6}-\x{6ed}' .
    '\x{6fd}\x{6fe}\x{700}-\x{70f}\x{711}\x{730}-\x{74a}\x{7a6}-\x{7b0}\x{901}-' .
    '\x{903}\x{93c}\x{93e}-\x{94d}\x{951}-\x{954}\x{962}-\x{965}\x{970}\x{981}-' .
    '\x{983}\x{9bc}\x{9be}-\x{9cd}\x{9d7}\x{9e2}\x{9e3}\x{9f2}-\x{a03}\x{a3c}-' .
    '\x{a4d}\x{a70}\x{a71}\x{a81}-\x{a83}\x{abc}\x{abe}-\x{acd}\x{ae2}\x{ae3}' .
    '\x{af1}-\x{b03}\x{b3c}\x{b3e}-\x{b57}\x{b70}\x{b82}\x{bbe}-\x{bd7}\x{bf0}-' .
    '\x{c03}\x{c3e}-\x{c56}\x{c82}\x{c83}\x{cbc}\x{cbe}-\x{cd6}\x{d02}\x{d03}' .
    '\x{d3e}-\x{d57}\x{d82}\x{d83}\x{dca}-\x{df4}\x{e31}\x{e34}-\x{e3f}\x{e46}-' .
    '\x{e4f}\x{e5a}\x{e5b}\x{eb1}\x{eb4}-\x{ebc}\x{ec6}-\x{ecd}\x{f01}-\x{f1f}' .
    '\x{f2a}-\x{f3f}\x{f71}-\x{f87}\x{f90}-\x{fd1}\x{102c}-\x{1039}\x{104a}-' .
    '\x{104f}\x{1056}-\x{1059}\x{10fb}\x{10fc}\x{135f}-\x{137c}\x{1390}-\x{1399}' .
    '\x{166d}\x{166e}\x{1680}\x{169b}\x{169c}\x{16eb}-\x{16f0}\x{1712}-\x{1714}' .
    '\x{1732}-\x{1736}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17db}\x{17dd}' .
    '\x{17f0}-\x{180e}\x{1843}\x{18a9}\x{1920}-\x{1945}\x{19b0}-\x{19c0}\x{19c8}' .
    '\x{19c9}\x{19de}-\x{19ff}\x{1a17}-\x{1a1f}\x{1d2c}-\x{1d61}\x{1d78}\x{1d9b}-' .
    '\x{1dc3}\x{1fbd}\x{1fbf}-\x{1fc1}\x{1fcd}-\x{1fcf}\x{1fdd}-\x{1fdf}\x{1fed}-' .
    '\x{1fef}\x{1ffd}-\x{2070}\x{2074}-\x{207e}\x{2080}-\x{2101}\x{2103}-\x{2106}' .
    '\x{2108}\x{2109}\x{2114}\x{2116}-\x{2118}\x{211e}-\x{2123}\x{2125}\x{2127}' .
    '\x{2129}\x{212e}\x{2132}\x{213a}\x{213b}\x{2140}-\x{2144}\x{214a}-\x{2b13}' .
    '\x{2ce5}-\x{2cff}\x{2d6f}\x{2e00}-\x{3005}\x{3007}-\x{303b}\x{303d}-\x{303f}' .
    '\x{3099}-\x{309e}\x{30a0}\x{30fb}\x{30fd}\x{30fe}\x{3190}-\x{319f}\x{31c0}-' .
    '\x{31cf}\x{3200}-\x{33ff}\x{4dc0}-\x{4dff}\x{a015}\x{a490}-\x{a716}\x{a802}' .
    '\x{e000}-\x{f8ff}\x{fb29}\x{fd3e}-\x{fd3f}\x{fdfc}-\x{fdfd}' .
    '\x{fd3f}\x{fdfc}-\x{fe6b}\x{feff}-\x{ff0f}\x{ff1a}-\x{ff20}\x{ff3b}-\x{ff40}' .
    '\x{ff5b}-\x{ff65}\x{ff70}\x{ff9e}\x{ff9f}\x{ffe0}-\x{fffd}'
);

define(
    'AMB_PREG_CLASS_NUMBERS',
    '\x{30}-\x{39}\x{b2}\x{b3}\x{b9}\x{bc}-\x{be}\x{660}-\x{669}\x{6f0}-\x{6f9}' .
    '\x{966}-\x{96f}\x{9e6}-\x{9ef}\x{9f4}-\x{9f9}\x{a66}-\x{a6f}\x{ae6}-\x{aef}' .
    '\x{b66}-\x{b6f}\x{be7}-\x{bf2}\x{c66}-\x{c6f}\x{ce6}-\x{cef}\x{d66}-\x{d6f}' .
    '\x{e50}-\x{e59}\x{ed0}-\x{ed9}\x{f20}-\x{f33}\x{1040}-\x{1049}\x{1369}-' .
    '\x{137c}\x{16ee}-\x{16f0}\x{17e0}-\x{17e9}\x{17f0}-\x{17f9}\x{1810}-\x{1819}' .
    '\x{1946}-\x{194f}\x{2070}\x{2074}-\x{2079}\x{2080}-\x{2089}\x{2153}-\x{2183}' .
    '\x{2460}-\x{249b}\x{24ea}-\x{24ff}\x{2776}-\x{2793}\x{3007}\x{3021}-\x{3029}' .
    '\x{3038}-\x{303a}\x{3192}-\x{3195}\x{3220}-\x{3229}\x{3251}-\x{325f}\x{3280}-' .
    '\x{3289}\x{32b1}-\x{32bf}\x{ff10}-\x{ff19}'
);

define(
    'AMB_PREG_CLASS_PUNCTUATION',
    '\x{21}-\x{23}\x{25}-\x{2a}\x{2c}-\x{2f}\x{3a}\x{3b}\x{3f}\x{40}\x{5b}-\x{5d}' .
    '\x{5f}\x{7b}\x{7d}\x{a1}\x{ab}\x{b7}\x{bb}\x{bf}\x{37e}\x{387}\x{55a}-\x{55f}' .
    '\x{589}\x{58a}\x{5be}\x{5c0}\x{5c3}\x{5f3}\x{5f4}\x{60c}\x{60d}\x{61b}\x{61f}' .
    '\x{66a}-\x{66d}\x{6d4}\x{700}-\x{70d}\x{964}\x{965}\x{970}\x{df4}\x{e4f}' .
    '\x{e5a}\x{e5b}\x{f04}-\x{f12}\x{f3a}-\x{f3d}\x{f85}\x{104a}-\x{104f}\x{10fb}' .
    '\x{1361}-\x{1368}\x{166d}\x{166e}\x{169b}\x{169c}\x{16eb}-\x{16ed}\x{1735}' .
    '\x{1736}\x{17d4}-\x{17d6}\x{17d8}-\x{17da}\x{1800}-\x{180a}\x{1944}\x{1945}' .
    '\x{2010}-\x{2027}\x{2030}-\x{2043}\x{2045}-\x{2051}\x{2053}\x{2054}\x{2057}' .
    '\x{207d}\x{207e}\x{208d}\x{208e}\x{2329}\x{232a}\x{23b4}-\x{23b6}\x{2768}-' .
    '\x{2775}\x{27e6}-\x{27eb}\x{2983}-\x{2998}\x{29d8}-\x{29db}\x{29fc}\x{29fd}' .
    '\x{3001}-\x{3003}\x{3008}-\x{3011}\x{3014}-\x{301f}\x{3030}\x{303d}\x{30a0}' .
    '\x{30fb}\x{fd3e}\x{fd3f}\x{fe30}-\x{fe52}\x{fe54}-\x{fe61}\x{fe63}\x{fe68}' .
    '\x{fe6a}\x{fe6b}\x{ff01}-\x{ff03}\x{ff05}-\x{ff0a}\x{ff0c}-\x{ff0f}\x{ff1a}' .
    '\x{ff1b}\x{ff1f}\x{ff20}\x{ff3b}-\x{ff3d}\x{ff3f}\x{ff5b}\x{ff5d}\x{ff5f}-' .
    '\x{ff65}'
);

/**
 * Matches all CJK characters that are candidates for auto-splitting
 * (Chinese, Japanese, Korean).
 * Contains kana and BMP ideographs.
 */
define(
    'AMB_PREG_CLASS_CJK',
    '\x{3041}-\x{30ff}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}\x{4e00}-\x{9fbb}\x{f900}-\x{fad9}'
);

class AmbIndexation
{

    protected $db;
    protected $weight_array = array();
    protected $step_size = 1000;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * @param int $id_product
     * @param int $id_lang
     * @return string
     */
    public function getTags($id_product, $id_lang)
    {
        $tags = '';
        $tagsArray = $this->db->executeS('
        SELECT t.name FROM ' . _DB_PREFIX_ . 'product_tag pt
        LEFT JOIN ' . _DB_PREFIX_ . 'tag t ON (pt.id_tag = t.id_tag AND t.id_lang = ' . (int) $id_lang . ')
        WHERE pt.id_product = ' . (int) $id_product, true, false);
        foreach ($tagsArray as $tag) {
            $tags .= $tag['name'] . ' ';
        }
        return $tags;
    }

    /**
     * @param int $id_product
     * @param int $id_lang
     * @return string
     */
    public function getAttributes($id_product, $id_lang)
    {
        if (!Combination::isFeatureActive()) {
            return '';
        }

        $attributes = '';
        $attributesArray = $this->db->executeS('
        SELECT al.name FROM ' . _DB_PREFIX_ . 'product_attribute pa
        INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute
        INNER JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang . ')
        ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
        WHERE pa.id_product = ' . (int) $id_product, true, false);
        foreach ($attributesArray as $attribute) {
            $attributes .= $attribute['name'] . ' ';
        }
        return $attributes;
    }

    /**
     * @param int $id_product
     * @param int $id_lang
     * @return string
     */
    public function getFeatures($id_product, $id_lang)
    {
        if (!Feature::isFeatureActive()) {
            return '';
        }

        $features = '';
        $featuresArray = $this->db->executeS('
        SELECT fvl.value FROM ' . _DB_PREFIX_ . 'feature_product fp
        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON (fp.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . (int) $id_lang . ')
        WHERE fp.id_product = ' . (int) $id_product, true, false);
        foreach ($featuresArray as $feature) {
            $features .= $feature['value'] . ' ';
        }
        return $features;
    }

    /**
     * @return string
     */
    protected function getSQLProductAttributeFields()
    {
        $sql = '';
        if (is_array($this->weight_array)) {
            foreach ($this->weight_array as $key => $weight) {
                if ((int) $weight) {
                    switch ($key) {
                        case 'pa_reference':
                            $sql .= ', pa.reference AS pa_reference';
                            break;
                        case 'pa_supplier_reference':
                            $sql .= ', pa.supplier_reference AS pa_supplier_reference';
                            break;
                        case 'pa_ean13':
                            $sql .= ', pa.ean13 AS pa_ean13';
                            break;
                        case 'pa_upc':
                            $sql .= ', pa.upc AS pa_upc';
                            break;
                    }
                }
            }
        }
        return $sql;
    }

    protected function getProductsToIndex($id_product = false, $step = false)
    {
        $sql = 'SELECT p.id_product, pl.id_lang, pl.id_shop, l.iso_code';

        if (is_array($this->weight_array)) {
            foreach ($this->weight_array as $key => $weight) {
                if ((int) $weight) {
                    switch ($key) {
                        case 'pname':
                            $sql .= ', pl.name pname';
                            break;
                        case 'reference':
                            $sql .= ', p.reference';
                            break;
                        case 'supplier_reference':
                            $sql .= ', p.supplier_reference';
                            break;
                        case 'ean13':
                            $sql .= ', p.ean13';
                            break;
                        case 'upc':
                            $sql .= ', p.upc';
                            break;
                        case 'description_short':
                            $sql .= ', pl.description_short';
                            break;
                        case 'description':
                            $sql .= ', pl.description';
                            break;
                        case 'cname':
                            $sql .= ', cl.name cname';
                            break;
                        case 'mname':
                            $sql .= ', m.name mname';
                            break;
                    }
                }
            }
        }

        $limit = ($step === false ? '' : 'LIMIT ' . (int) $step * (int) $this->step_size . ',' . (int) $this->step_size);

        $sql .= ' FROM ' . _DB_PREFIX_ . 'product p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl
                ON p.id_product = pl.id_product
            ' . Shop::addSqlAssociation('product', 'p', true, null, true) . '
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON (cl.id_category = product_shop.id_category_default AND pl.id_lang = cl.id_lang AND cl.id_shop = product_shop.id_shop)
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m
                ON m.id_manufacturer = p.id_manufacturer
            LEFT JOIN ' . _DB_PREFIX_ . 'lang l
                ON l.id_lang = pl.id_lang
            WHERE product_shop.indexed = 0
            AND product_shop.visibility IN ("both", "search")
            ' . ($id_product ? 'AND p.id_product = ' . (int) $id_product : '') . '
            AND product_shop.`active` = 1
            AND pl.`id_shop` = product_shop.`id_shop`
            ' . $limit;

        return Db::getInstance()->executeS($sql, false);
    }

    protected function getTotalProductCount()
    {
        $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product p ' . Shop::addSqlAssociation('product', 'p') . ' WHERE product_shop.`visibility` IN ("both", "search") AND product_shop.`active` = 1';

        return $this->db->getValue($sql);
    }

    /**
     * @param int    $id_product
     * @param string $sql_attribute
     *
     * @return array|null
     */
    protected function getAttributesFields($id_product, $sql_attribute)
    {
        return $this->db->executeS('SELECT id_product ' . $sql_attribute . ' FROM ' .
            _DB_PREFIX_ . 'product_attribute pa WHERE pa.id_product = ' . (int) $id_product, true, false);
    }

    protected function getProductsWeightArray()
    {
        return array(
            'pname' => Configuration::get('PS_SEARCH_WEIGHT_PNAME'),
            'reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'pa_reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'supplier_reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'pa_supplier_reference' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'ean13' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'pa_ean13' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'upc' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'pa_upc' => Configuration::get('PS_SEARCH_WEIGHT_REF'),
            'description_short' => Configuration::get('PS_SEARCH_WEIGHT_SHORTDESC'),
            'description' => Configuration::get('PS_SEARCH_WEIGHT_DESC'),
            'cname' => Configuration::get('PS_SEARCH_WEIGHT_CNAME'),
            'mname' => Configuration::get('PS_SEARCH_WEIGHT_MNAME'),
            'tags' => Configuration::get('PS_SEARCH_WEIGHT_TAG'),
            'attributes' => Configuration::get('PS_SEARCH_WEIGHT_ATTRIBUTE'),
            'features' => Configuration::get('PS_SEARCH_WEIGHT_FEATURE'),
        );
    }

    protected function getCategoriesWeightArray()
    {
        return array(
            'name' => 30,
            'description' => 1,
        );
    }

    protected function emptyCurrentIndex($id_product)
    {
        $full = (int) $id_product > 0 ? false : true;

        if ($full) {
            $this->db->execute('TRUNCATE ' . _DB_PREFIX_ . 'search_index');
            $this->db->execute('TRUNCATE ' . _DB_PREFIX_ . 'amb_search_index');
            $this->db->execute('TRUNCATE ' . _DB_PREFIX_ . 'search_word');
            ObjectModel::updateMultishopTable('Product', array('indexed' => 0));
        } else {
            $this->db->execute('DELETE si FROM `' . _DB_PREFIX_ . 'amb_search_index` asi
                INNER JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = si.id_product)
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE product_shop.`visibility` IN ("both", "search")
                AND product_shop.`active` = 1
                AND ' . ($id_product ? 'p.`id_product` = ' . (int) $id_product : 'product_shop.`indexed` = 0'));

            $this->db->execute('DELETE si FROM `' . _DB_PREFIX_ . 'search_index` si
                INNER JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = si.id_product)
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE product_shop.`visibility` IN ("both", "search")
                AND product_shop.`active` = 1
                AND ' . ($id_product ? 'p.`id_product` = ' . (int) $id_product : 'product_shop.`indexed` = 0'));

            $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                SET p.`indexed` = 0, product_shop.`indexed` = 0
                WHERE product_shop.`visibility` IN ("both", "search")
                AND product_shop.`active` = 1
                AND ' . ($id_product ? 'p.`id_product` = ' . (int) $id_product : 'product_shop.`indexed` = 0'));
        }
    }

    protected function copyToPrestashopIndex($id_product = false)
    {
        if ($id_product === false) {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . 'search_word(id_shop, id_lang, word)
            SELECT DISTINCT id_shop, id_lang, word FROM ' . _DB_PREFIX_ . 'amb_search_index;';

            $this->db->execute($query, false);

            $query = 'INSERT INTO ' . _DB_PREFIX_ . 'search_index(id_product, id_word, weight)
            SELECT t1.id_product, t2.id_word, t1.weight
            FROM ' . _DB_PREFIX_ . 'amb_search_index t1
            INNER JOIN ' . _DB_PREFIX_ . 'search_word t2 ON t2.word=t1.word AND t1.id_shop=t2.id_shop AND t1.id_lang=t2.id_lang';

            $this->db->execute($query, false);

            $this->setProductsAsIndexed();
        }
    }

    public function processProducts($id_product = false, $step = false)
    {
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amb_search_index` (
          `id_word` int(10) NOT NULL AUTO_INCREMENT,
          `id_shop` int(11) NOT NULL DEFAULT "1",
          `id_lang` int(10) NOT NULL,
          `word` varchar(15) NOT NULL,
          `weight` varchar(45) DEFAULT NULL,
          `id_product` int(10) DEFAULT NULL,
          PRIMARY KEY (`id_word`),
          INDEX `word` (`word` ASC, `id_lang` ASC, `id_shop` ASC)
        )';

        Db::getInstance()->execute($query);

        $this->weight_array = $this->getProductsWeightArray();

        $cron = Tools::getValue('cron', false);
        $indexed = Tools::getValue('indexed', 0);

        if ((int) $step == 0) {
            $this->emptyCurrentIndex($id_product);
        }

        if ($id_product === false) {
            $total_products = $this->getTotalProductCount();
        } else {
            $total_products = 1;
        }

        $sql_attribute = self::getSQLProductAttributeFields();

        $products = $this->getProductsToIndex($id_product, $step);

        $has_indexed = false;

        $counter = array();

        while ($product = Db::getInstance()->nextRow($products)) {
            if ((int) $this->weight_array['tags']) {
                $product['tags'] = $this->getTags((int) $product['id_product'], (int) $product['id_lang']);
            }
            if ((int) $this->weight_array['attributes']) {
                $product['attributes'] = $this->getAttributes((int) $product['id_product'], (int) $product['id_lang']);
            }
            if ((int) $this->weight_array['features']) {
                $product['features'] = $this->getFeatures((int) $product['id_product'], (int) $product['id_lang']);
            }
            if ($sql_attribute) {
                $attribute_fields = $this->getAttributesFields((int) $product['id_product'], $sql_attribute);
                if ($attribute_fields) {
                    $product['attributes_fields'] = $attribute_fields;
                }
            }

            $scoring = array();

            foreach ($product as $key => $value) {
                $words = explode(' ', self::sanitize($value, (int) $product['id_lang'], true, $product['iso_code']));

                foreach ($words as $word) {
                    if (Tools::strlen($word) == 0) {
                        continue;
                    }

                    $word = Tools::substr($word, 0, AMB_PS_SEARCH_MAX_WORD_LENGTH);

                    if (!isset($scoring[$word])) {
                        $scoring[$word] = 0;
                    }
                    if (isset($this->weight_array[$key])) {
                        $scoring[$word] += $this->weight_array[$key];
                    }
                }
            }

            $query_array = array();

            foreach ($scoring as $word => $score) {
                if ($score) {
                    $query_array[$word] = '(' . (int) $product['id_lang'] . ', ' . (int) $product['id_shop'] . ', \'' . pSQL($word) . '\', ' . (int) $score . ', ' . $product['id_product'] . ')';
                }
            }

            $query = '
                INSERT INTO ' . _DB_PREFIX_ . 'amb_search_index (id_lang, id_shop, word, weight, id_product)
                VALUES ' . implode(',', $query_array);

            $this->db->execute($query, false);

            $has_indexed = true;
            $counter[$product['id_product']] = 1;
        }

        $products_done = $indexed + count($counter);

        if (!$has_indexed) {
            $this->copyToPrestashopIndex($id_product);
        } else {
            $link = new Link();

            if (!$cron) {
                die(
                    json_encode(
                        array(
                            'url' => $link->getAdminLink('AdminModules') . '&configure=ambjolisearch&indexation=products&step=' . ($step + 1) . '&indexed=' . $products_done,
                            'status' => Tools::ps_round(($products_done / $total_products) * 100, 2) . '%',
                            'indexed' => $products_done,
                            'total' => $total_products,
                        )
                    )
                );
            } else {
                Tools::redirectAdmin($link->getAdminLink('AdminModules') . '&configure=ambjolisearch&indexation=products&step=' . ($step + 1));
            }
        }

        if (!$cron) {
            die(
                json_encode(
                    array('url' => false,
                        'indexed' => $products_done,
                        'total' => $total_products,
                        'status' => '100%'
                    )
                )
            );
        } else {
            return true;
        }
    }

    public static function removeProductsSearchIndex($products)
    {
        if (is_array($products) && !empty($products)) {
            Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'search_index WHERE id_product IN (' . implode(',', array_unique(array_map('intval', $products))) . ')');
            ObjectModel::updateMultishopTable('Product', array('indexed' => 0), 'a.id_product IN (' . implode(',', array_map('intval', $products)) . ')');
        }
    }

    protected function setProductsAsIndexed()
    {
        ObjectModel::updateMultishopTable('Product', array('indexed' => 1), 'a.id_product IN (SELECT DISTINCT id_product FROM ' . _DB_PREFIX_ . 'search_index)');
    }

    public static function sanitize($string, $id_lang, $indexation = false, $iso_code = false)
    {
        $string = trim($string);
        if (empty($string)) {
            return '';
        }

        $string = Tools::strtolower(strip_tags($string));
        $string = html_entity_decode($string, ENT_NOQUOTES, 'utf-8');

        $string = preg_replace('/\xc2\xad/', '', $string);
        $string = preg_replace('/([' . AMB_PREG_CLASS_NUMBERS . ']+)[' . AMB_PREG_CLASS_PUNCTUATION . ']+(?=[' . AMB_PREG_CLASS_NUMBERS . '])/u', '\1', $string);
        $string = preg_replace('/[' . AMB_PREG_CLASS_SEARCH_EXCLUDE . ']+/u', ' ', $string);

        if ($indexation) {
            $string = preg_replace('/[._-]+/', ' ', $string);
        } else {
            $words = explode(' ', $string);
            $processed_words = array();
            // search for aliases for each word of the query
            foreach ($words as $word) {
                $alias = new Alias(null, $word);
                if (Validate::isLoadedObject($alias)) {
                    $processed_words[] = $alias->search;
                } else {
                    $processed_words[] = $word;
                }
            }
            $string = implode(' ', $processed_words);
            $string = preg_replace('/[._]+/', '', $string);
            $string = ltrim(preg_replace('/([^ ])-/', '$1 ', ' ' . $string));
            $string = preg_replace('/[._]+/', '', $string);
            $string = preg_replace('/[^\s]-+/', '', $string);
        }

        $blacklist = Tools::strtolower(Configuration::get('PS_SEARCH_BLACKLIST', $id_lang));
        if (!empty($blacklist)) {
            $string = preg_replace('/(?<=\s)(' . $blacklist . ')(?=\s)/Su', '', $string);
            $string = preg_replace('/^(' . $blacklist . ')(?=\s)/Su', '', $string);
            $string = preg_replace('/(?<=\s)(' . $blacklist . ')$/Su', '', $string);
            $string = preg_replace('/^(' . $blacklist . ')$/Su', '', $string);
        }

        // If the language is constituted with symbol and there is no "words", then split every chars
        if (in_array($iso_code, array('zh', 'tw', 'ja')) && function_exists('mb_strlen')) {
            // Cut symbols from letters
            $symbols = '';
            $letters = '';
            foreach (explode(' ', $string) as $mb_word) {
                if (Tools::strlen(Tools::replaceAccentedChars($mb_word)) == mb_strlen(Tools::replaceAccentedChars($mb_word))) {
                    $letters .= $mb_word . ' ';
                } else {
                    $symbols .= $mb_word . ' ';
                }
            }

            if (preg_match_all('/./u', $symbols, $matches)) {
                $symbols = implode(' ', $matches[0]);
            }

            $string = $letters . $symbols;
        } elseif ($indexation) {
            $minWordLen = (int) Configuration::get('PS_SEARCH_MINWORDLEN');
            if ($minWordLen > 1) {
                $minWordLen -= 1;
                $string = preg_replace('/(?<=\s)[^\s]{1,' . $minWordLen . '}(?=\s)/Su', ' ', $string);
                $string = preg_replace('/^[^\s]{1,' . $minWordLen . '}(?=\s)/Su', '', $string);
                $string = preg_replace('/(?<=\s)[^\s]{1,' . $minWordLen . '}$/Su', '', $string);
                $string = preg_replace('/^[^\s]{1,' . $minWordLen . '}$/Su', '', $string);
            }
        }

        $string = Tools::replaceAccentedChars(trim(preg_replace('/\s+/', ' ', $string)));

        return $string;
    }
}
