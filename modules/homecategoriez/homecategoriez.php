<?php
/**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (false === file_exists(_PS_ROOT_DIR_ . '/vendor/zapalm/prestashopHelpers')) {
    require_once _PS_MODULE_DIR_ . 'homecategoriez/vendor/autoload.php';
}

/**
 * @inheritdoc
 *
 * @author Maksim T. <zapalm@yandex.com>
 */
class HomeCategoriez extends Module
{
    /** @var bool Is smarty vars already assigned */
    private static $vars_assigned = false;

    /** @var array Default settings */
    private $conf_default = array(
        'HOMECATEGORIEZ_CATALOG'      => 1,
        'categories_to_show'          => '0',
        'HOMECATEGORIEZ_COLS'         => 4,
        'HOMECATEGORIEZ_WIDTH_ADJUST' => 538,
    );

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function __construct()
    {
        $this->name          = 'homecategoriez';
        $this->tab           = version_compare(_PS_VERSION_, '1.4', '>=') ? 'front_office_features' : 'Tools';
        $this->version       = '1.5.0';
        $this->author        = 'zapalm';
        $this->need_instance = false;
        $this->bootstrap     = false;

        parent::__construct();

        $this->displayName = $this->l('Categories on the homepage');
        $this->description = $this->l('Displays categories in the middle of your homepage');

        $this->conf_default['HOMECATEGORIEZ_CATALOG'] = Configuration::get('PS_HOME_CATEGORY');
        // $this->conf_default['categories_to_show'] = Configuration::get('categories_to_show');

    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        foreach ($this->conf_default as $c => $v) {
            Configuration::updateValue($c, $v);
        }

        $result = $this->registerHook('header');
        if (version_compare(_PS_VERSION_, '1.6', '<') || version_compare(_PS_VERSION_, '1.7', '>=')) {
            $result &= $this->registerHook('home');
        } else {
            $result &= $this->registerHook('displayHomeTab');
            $result &= $this->registerHook('displayHomeTabContent');
        }
        $result = (bool)$result;

        // The information about registration is need to get usage statistics that will help to improve the module in right way.
        (new \zapalm\prestashopHelpers\components\qualityService\QualityService($this, false))
            ->setTicketData(array(
                'new'  => $this->name . '-' . $this->version,
                'h'    => \zapalm\prestashopHelpers\helpers\UrlHelper::getShopDomain(),
            ))
            ->registerModule($result)
        ;

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function uninstall()
    {
        foreach ($this->conf_default as $c => $v) {
            Configuration::deleteByName($c);
        }

        return parent::uninstall();
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function getContent()
    {
        global $cookie;

        $output = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('submit_save')) {
            $res = 1;
            foreach ($this->conf_default as $k => $v) {
                // $res &= Configuration::updateValue($k, (int)Tools::getValue($k));
                $res &= Configuration::updateValue($k, Tools::getValue($k));
            }

            $output .= $res ? $this->displayConfirmation($this->l('Settings updated')) : $this->displayError($this->l('Some setting not updated'));
        }

        $conf       = Configuration::getMultiple(array_keys($this->conf_default));
        $categories = Category::getHomeCategories($cookie->id_lang, false);
        $root_cat   = Category::getRootCategory($cookie->id_lang);
        $output     .= '
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <fieldset>
                    <legend><img src="' . $this->_path . 'logo.png" width="15" height="16" alt="" />' . $this->l('Settings') . '</legend>
                    <label>' . $this->l('Root category of children categories to display') . '</label>
                    <div class="margin-form">
                        <select name="HOMECATEGORIEZ_CATALOG">
                            <option value="' . $root_cat->id . '"' . ($conf['HOMECATEGORIEZ_CATALOG'] == $root_cat->id ? ' selected="selected"' : '') . '>' . $root_cat->name . '</option>';
                            foreach ($categories as $v) {
                                $output .= '<option value="' . $v['id_category'] . '"' . ($conf['HOMECATEGORIEZ_CATALOG'] == $v['id_category'] ? ' selected="selected"' : '') . '>' . $v['name'] . '</option>';
                            }
                            $output .= '
                        </select>
                        <p class="clear">' . $this->l('Choose a root category (default : Home category).') . '</p>
                    </div>
                    <label>' . $this->l('Choose the categories you want to show.') . '<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" name="categories_to_show" value="' . ($conf['categories_to_show'] ? $conf['categories_to_show'] : '0') . '">
                        <p class="clear">' . $this->l('Separate by commas ( , )') . '</p>
                    </div>
                    <label>' . $this->l('Number of columns to display') . '<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="1" name="HOMECATEGORIEZ_COLS" value="' . ($conf['HOMECATEGORIEZ_COLS'] ? $conf['HOMECATEGORIEZ_COLS'] : '4') . '" />
                        <p class="clear">' . $this->l('A number of columns to display on homepage (default: 4).') . '</p>
                    </div>
                    <label>' . $this->l('Width adjust for the block of categories') . '<sup>*</sup></label>
                    <div class="margin-form">
                        <input type="text" size="3" name="HOMECATEGORIEZ_WIDTH_ADJUST" value="' . ($conf['HOMECATEGORIEZ_WIDTH_ADJUST'] ? $conf['HOMECATEGORIEZ_WIDTH_ADJUST'] : '0') . '" /> px.
                        <p class="clear">' . $this->l('Input a number of pixels to adjust width of the block of categories.') . '</p>
                    </div>
                    <label>' . $this->l('* Only for PrestaShop less then 1.6.') . '</label>
                    <br class="clear" />
                    <div class="margin-form">
                        <input type="submit" name="submit_save" value="' . $this->l('Save') . '" class="button" />
                    </div>
                </fieldset>
            </form>
        ';

        $output .= (new \zapalm\prestashopHelpers\widgets\AboutModuleWidget($this))
            ->setModuleUri('31-block-of-categories-on-the-homepage.html')
            ->setLicenseTitle('Academic Free License (AFL 3.0)')
            ->setLicenseUrl('https://opensource.org/licenses/afl-3.0.php')
        ;

        return $output;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $cssFile = '1.7.css';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $cssFile = '1.6.css';
        } else {
            $cssFile = '1.3-1.5.css';
        }

        return '<link href="' . $this->_path . 'views/css/' . $cssFile . '" rel="stylesheet">';
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookHome($params)
    {
        global $smarty;

        $this->assignCommonVariables($params);

        $conf                = Configuration::getMultiple(array_keys($this->conf_default));
        $block_width         = (int)$conf['HOMECATEGORIEZ_WIDTH_ADJUST'];
        $nb_items_per_line   = (int)$conf['HOMECATEGORIEZ_COLS'];
        $block_width_adjust  = ceil($nb_items_per_line * 2) + 2;
        $block_content_width = $block_width - $block_width_adjust;
        $block_li_width      = ceil($block_content_width / $nb_items_per_line);

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $pic_size_type = 'categorias_home';
        } else {
            $pic_size_type = 'home';
        }

        $smarty->assign(array(
            'block_width'       => $block_width,
            'nb_items_per_line' => $nb_items_per_line,
            'block_li_width'    => $block_li_width,
            'pic_size_type'     => $pic_size_type,
            'pic_size'          => Image::getSize($pic_size_type),
        ));

        $templateName = version_compare(_PS_VERSION_, '1.7', '>=')
            ? 'homecategoriez-boilerplate.tpl'
            : 'homecategoriez.tpl'
        ;

        return $this->display(__FILE__, 'views/templates/' . $templateName);
    }

    /**
     * Assign common variables.
     *
     * @param array $params Hook params.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function assignCommonVariables($params)
    {
        global $smarty, $link;

        if (self::$vars_assigned) {
            return;
        }

        $idLanguage = (int)$params['cookie']->id_lang;
        $categories = Category::getChildren((int)Configuration::get('HOMECATEGORIEZ_CATALOG'), $idLanguage, true);
        $categoria_padre = Category::getChildren(3, $idLanguage, true); /* Segunda mano */
        $categoria_padre2 = Category::getChildren(4, $idLanguage, true); /* Nuevo */
        $categoria_padre3 = Category::getChildren(23, $idLanguage, true); /* Armarios segunda mano */
        $categoria_padre4 = Category::getChildren(33, $idLanguage, true); /* Recepción */
        $categoria_padre5 = Category::getChildren(42, $idLanguage, true); /* Archivadores */

        foreach ($categories as $i => $category) {
            $categories[$i] = new Category($category['id_category'], $idLanguage);
        }
        foreach ($categoria_padre as $i => $category) {
            $categoria_padre[$i] = new Category($category['id_category'], $idLanguage);
        }
        foreach ($categoria_padre2 as $i => $category) {
            $categoria_padre2[$i] = new Category($category['id_category'], $idLanguage);
        }
        foreach ($categoria_padre3 as $i => $category) {
            $categoria_padre3[$i] = new Category($category['id_category'], $idLanguage);
        }
        foreach ($categoria_padre4 as $i => $category) {
            $categoria_padre4[$i] = new Category($category['id_category'], $idLanguage);
        }
        foreach ($categoria_padre5 as $i => $category) {
            $categoria_padre5[$i] = new Category($category['id_category'], $idLanguage);
        }

        $conf       = Configuration::getMultiple(array_keys($this->conf_default));
        $to_show = $conf["categories_to_show"];
        $to_show_list = explode(',', $to_show);
        $to_show = $to_show_list;
        $smarty->assign(array(
            'categories' => $categories,
            'link'       => $link,
            'categoria_padre'      => $categoria_padre,
            'categoria_padre2'      => $categoria_padre2,
            'categoria_padre3'      => $categoria_padre3,
            'categoria_padre4'      => $categoria_padre4,
            'categoria_padre5'      => $categoria_padre5,
            'to_show'    => $to_show,
        ));

        self::$vars_assigned = true;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookDisplayHomeTabContent($params)
    {
        $this->assignCommonVariables($params);

        $pic_size_type = 'category_default';

        $this->smarty->assign(array(
            'pic_size_type' => $pic_size_type,
        ));

        return $this->display(__FILE__, 'views/templates/homecategoriez-bootstrap.tpl');
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookDisplayHomeTab($params)
    {
        return $this->display(__FILE__, 'views/templates/homecategoriez-bootstrap-tab.tpl');
    }
}
