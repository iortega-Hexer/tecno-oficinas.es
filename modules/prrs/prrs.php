<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
*  MODIFIED BY MYPRESTA.EU FOR PRESTASHOP 1.7 PURPOSES !
*
*/

if (!defined('_PS_VERSION_'))
{
    exit;
}


class prrs extends Module
{

    public function __construct()
    {
        $this->name = 'prrs';
        $this->tab = 'front_office_features';
        $this->version = '1.3.3';
        $this->author = 'MyPresta.eu';
        $this->mypresta_link = 'https://mypresta.eu/modules/advertising-and-marketing/free-product-reviews-comments.html';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->secure_key = Tools::encrypt($this->name);
        $this->displayName = $this->l('Product Comments - reviews rich snippets');
        $this->description = $this->l('Module creates reviews rich snippets - dedicated for free module: Product Comments');

        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => '1.7.99.99'
        );
    }

    public function install()
    {
        if (parent::install() == false || !$this->registerHook('displayFooterProduct') || !$this->registerHook('header') || !$this->registerHook('displayProductAdditionalInfo') || !$this->registerHook('displayValoration'))
        {
            return false;
        }
        return true;
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('submitPrrs'))
        {
            Configuration::updateValue('prrs_on', (int)Tools::getValue('prrs_on'));
            Configuration::updateValue('prrs_nbc', (int)Tools::getValue('prrs_nbc'));
            Configuration::updateValue('prrs_inc_pr', (int)Tools::getValue('prrs_inc_pr'));
            Configuration::updateValue('prrs_dpf', (int)Tools::getValue('prrs_dpf'));
            Configuration::updateValue('prrs_dpai', (int)Tools::getValue('prrs_dpai'));
            Configuration::updateValue('prrs_addr', (int)Tools::getValue('prrs_addr'));
            Configuration::updateValue('prrs_clgd', (int)Tools::getValue('prrs_clgd'));
            Configuration::updateValue('prrs_alws', (int)Tools::getValue('prrs_alws'));

            $this->context->controller->confirmations[] = $this->l('Settings saved');
        }
    }

    public function getContent()
    {
        $this->_html = '';
        $this->_postProcess();
        $this->_html .= $this->renderConfigForm();
        return $this->_html;
    }

    public function psversion($part = 1)
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1)
        {
            return $exp[1];
        }
        if ($part == 2)
        {
            return $exp[2];
        }
        if ($part == 3)
        {
            return $exp[3];
        }
    }

    public function renderConfigForm()
    {
        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Enable reviews rich snippets'),
                        'name' => 'prrs_on',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Create basic product structured data'),
                        'desc' => $this->l('Use this option if your product page does not have product structured datas'),
                        'name' => 'prrs_inc_pr',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Show number of reviews'),
                        'desc' => $this->l('Module will show number of reviews near the stars with average grade'),
                        'name' => 'prrs_nbc',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Do not hide block when product has no comments'),
                        'name' => 'prrs_alws',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Display in displayProductFooter'),
                        'name' => 'prrs_dpf',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Display in displayProductAdditionalInfo'),
                        'name' => 'prrs_dpai',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Display "add review" button'),
                        'name' => 'prrs_addr',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true,
                        'label' => $this->l('Display "add review" button for logged customers only'),
                        'name' => 'prrs_clgd',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitPrrs',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->name;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPrrsConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form_1));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'prrs_on' => Tools::getValue('prrs_on', Configuration::get('prrs_on')),
            'prrs_nbc' => Tools::getValue('prrs_nbc', Configuration::get('prrs_nbc')),
            'prrs_inc_pr' => Tools::getValue('prrs_inc_pr', Configuration::get('prrs_inc_pr')),
            'prrs_dpf' => Tools::getValue('prrs_dpf', Configuration::get('prrs_dpf')),
            'prrs_dpai' => Tools::getValue('prrs_dpai', Configuration::get('prrs_dpai')),
            'prrs_addr' => Tools::getValue('prrs_addr', Configuration::get('prrs_addr')),
            'prrs_clgd' => Tools::getValue('prrs_clgd', Configuration::get('prrs_clgd')),
            'prrs_alws' => Tools::getValue('prrs_alws', Configuration::get('prrs_alws')),
        );
    }

    public function hookdisplayProductAdditionalInfo($params)
    {
        if (Configuration::get('prrs_dpai') != 1 || Tools::getValue('id_product', 'false') == 'false') {
            return;
        }
        return $this->prepareReviewsSummary($params);
    }
    public function hookdisplayValoration($params)
    {
        if ( Tools::getValue('id_product', 'false') == 'false') {
            return;
        }
         return $this->prepareReviewsSummary($params);
    }

    public function hookdisplayFooterProduct($params)
    {
        if (Configuration::get('prrs_dpf') != 1 || Tools::getValue('id_product', 'false') == 'false') {
            return;
        }
        return $this->prepareReviewsSummary($params);
    }

    public function prepareReviewsSummary($params)
    {
        $hide_add_review = false;
        if (Configuration::get('prrs_clgd') == true && $this->context->customer->isLogged() != true) {
            $hide_add_review = true;
        }

        $this->context->smarty->assign(array(
            'prrs_hide_add_review' => $hide_add_review
        ));

        $ratings_counter = self::getCommentNumber(Tools::getValue('id_product'));
        $this->context->smarty->assign(array(
            'prrs_ratings_counter' => $ratings_counter
        ));

        $this->context->smarty->assign(array(
            'prrs_nbc' => Configuration::get('prrs_nbc'),
            'prrs_inc_pr' => Configuration::get('prrs_inc_pr'),
        ));

        if (Configuration::get('prrs_alws') == false && $ratings_counter == 0)
        {
            return;
        }

        $product = new Product(Tools::getValue('id_product'), false, $this->context->language->id);
        $this->context->smarty->assign(array(
            'prrs_product' => $product,
            'ratings' => self::getRatings(Tools::getValue('id_product')),
            'ratings_counter' => $ratings_counter
        ));
        return $this->display(__file__, 'views/templates/prrs.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/prrs.css', 'all');
    }

    public function inconsistency($return)
    {
        return;
    }

    public static function getRatings($id_product)
    {
        $validate = Configuration::get('PRODUCT_COMMENTS_MODERATE');

        $sql = 'SELECT (SUM(pc.`grade`) / COUNT(pc.`grade`)) AS avg,
				MIN(pc.`grade`) AS min,
				MAX(pc.`grade`) AS max
			FROM `'._DB_PREFIX_.'product_comment` pc
			WHERE pc.`id_product` = '.(int)$id_product.'
			AND pc.`deleted` = 0'.
            ($validate == '1' ? ' AND pc.`validate` = 1' : '');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public static function getAverageGrade($id_product)
    {
        $validate = Configuration::get('PRODUCT_COMMENTS_MODERATE');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT (SUM(pc.`grade`) / COUNT(pc.`grade`)) AS grade
		FROM `'._DB_PREFIX_.'product_comment` pc
		WHERE pc.`id_product` = '.(int)$id_product.'
		AND pc.`deleted` = 0'.
            ($validate == '1' ? ' AND pc.`validate` = 1' : ''));
    }

    public static function getCommentNumber($id_product)
    {
        if (!Validate::isUnsignedId($id_product))
            return false;
        $validate = (int)Configuration::get('PRODUCT_COMMENTS_MODERATE');
        $cache_id = 'ProductComment::getCommentNumber_'.(int)$id_product.'-'.$validate;
        if (!Cache::isStored($cache_id))
        {
            $result = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(`id_product_comment`) AS "nbr"
			FROM `'._DB_PREFIX_.'product_comment` pc
			WHERE `id_product` = '.(int)($id_product).($validate == '1' ? ' AND `validate` = 1' : ''));
            Cache::store($cache_id, $result);
        }
        return Cache::retrieve($cache_id);
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 12 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php'))
        {
            @require_once('../modules/' . $this->name . '/key.php');
        }
        else
        {
            if (@file_exists(dirname(__file__) . $this->name . '/key.php'))
            {
                @require_once(dirname(__file__) . $this->name . '/key.php');
            }
            else
            {
                if (@file_exists('modules/' . $this->name . '/key.php'))
                {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }

        if ($form == 1)
        {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_modu\le_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        }
        else
        {
            if (defined('_PS_ADMIN_DIR_'))
            {
                if (Tools::isSubmit('submit_settings_updates'))
                {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') == false)
                {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200))
                    {
                        $actual_version = prrsUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (prrsUpdate::version($this->version) < prrsUpdate::version(Configuration::get('updatev_' . $this->name)))
                    {
                        $this->warning = $this->l('New version available, check http://MyPresta.eu for more informations');
                    }
                }
                if ($display_msg == 1)
                {
                    if (prrsUpdate::version($this->version) < prrsUpdate::version(prrsUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version)))
                    {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    }
                    else
                    {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }
}

class prrsUpdate extends prrs
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3)
        {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2)
        {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1)
        {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0)
        {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen"))
        {
            if (function_exists("file_get_contents"))
            {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}
