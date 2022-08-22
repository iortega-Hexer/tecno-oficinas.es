<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2019 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class AdvancedPackCoreClass extends Module
{
    const INSTALL_SQL_FILE = 'install.sql';
    const DYN_CSS_FILE = 'views/css/dynamic-{id_shop}.css';
    public static $_module_prefix = 'AP5';
    protected $_coreClassName;
    protected $_html = '';
    protected $baseConfigUrl = '';
    protected $_file_to_check = array();
    protected $_support_link = false;
    protected $_getting_started = false;
    protected $copyrightLink = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    public function __construct()
    {
        parent::__construct();
        $this->_coreClassName = Tools::strtolower(get_class());
        $forum_url_tab = array(
            'fr' => 'http://www.prestashop.com/forums/topic/372622-module-pm-advanced-pack-5/',
            'en' => 'http://www.prestashop.com/forums/topic/372623-module-pm-advanced-pack-5/'
        );
        $forum_url = $forum_url_tab['en'];
        if ($this->context->language->iso_code == 'fr') {
            $forum_url = $forum_url_tab['fr'];
        }
        $doc_url = '#/advanced-pack';
        $this->_support_link = array(
            array('link' => $forum_url, 'target' => '_blank', 'label' => $this->l('Forum topic', $this->_coreClassName)),
            
            array('link' => 'http://addons.prestashop.com/contact-community.php?id_product=1015', 'target' => '_blank', 'label' => $this->l('Support contact', $this->_coreClassName)),
        );
    }
    public static function _isFilledArray($array)
    {
        return ($array && is_array($array) && count($array));
    }
    protected static function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', array($data));
        } else {
            return current(array_map($type . '_encode', array($data)));
        }
    }
    protected static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', array($data));
        } else {
            return current(array_map($type . '_decode', array($data)));
        }
    }
    public static function array_cartesian($pA)
    {
        if (count($pA) == 0) {
            return array(array());
        }
        $a = array_shift($pA);
        $c = self::array_cartesian($pA);
        $r = array();
        foreach ($a as $v) {
            foreach ($c as $p) {
                $r[] = array_merge(array($v), $p);
            }
        }
        return $r;
    }
    protected function installDatabase()
    {
        if (!Tools::file_exists_cache(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return false;
        } elseif (!$sqlFile = Tools::file_get_contents(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return false;
        }
        $sqlFile = preg_split("/;\s*[\r\n]+/", str_replace(array('PREFIX_', 'MYSQL_ENGINE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sqlFile));
        foreach ($sqlFile as $sqlQuery) {
            if (!Db::getInstance()->Execute(trim($sqlQuery))) {
                return false;
            }
        }
        return true;
    }
    public function _checkIfModuleIsUpdate($updateDb = false, $displayConfirm = true, $firstInstall = false)
    {
        if (!$updateDb && $this->version != Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false)) {
            return false;
        }
        if ($firstInstall) {
        }
        if ($updateDb) {
            if (!$firstInstall) {
                try {
                    $this->installOverrides();
                } catch (Exception $e) {
                    $this->context->controller->errors[] = sprintf('Unable to install override: %s', $e->getMessage());
                    $this->uninstallOverrides();
                }
            }
            if (method_exists($this, 'registerNewHooks')) {
                $this->registerNewHooks(Configuration::get('PM_' . self::$_module_prefix . '_LAST_VERSION', false), $this->version);
            }
            Configuration::updateValue('PM_' . self::$_module_prefix . '_LAST_VERSION', $this->version);
            if (!Configuration::getGlobalValue('PM_AP5_SECURE_KEY')) {
                Configuration::updateGlobalValue('PM_AP5_SECURE_KEY', Tools::strtoupper(Tools::passwdGen(16)));
            }
            $this->_updateDb();
            $config = $this->_getModuleConfiguration();
            foreach ($this->_defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            $this->_setModuleConfiguration($config);
            AdvancedPack::clearAP5Cache();
            $this->cleanModuleDatas();
            $this->_generateCSS();
            if ($displayConfirm) {
                $this->context->controller->confirmations[] = $this->l('Module updated successfully', $this->_coreClassName);
            }
        }
        return true;
    }
    protected function _columnExists($table, $column, $createIfNotExist = false, $type = false, $insertAfter = false)
    {
        $columnsList = Db::getInstance()->ExecuteS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . $table . "`", true, false);
        foreach ($columnsList as $columnRow) {
            if ($columnRow['Field'] == $column) {
                return true;
            }
        }
        if ($createIfNotExist && Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $type . ' ' . ($insertAfter ? ' AFTER `' . $insertAfter . '`' : '') . '')) {
            return true;
        }
        return false;
    }
    protected function _checkPermissions()
    {
        if (self::_isFilledArray($this->_file_to_check)) {
            $errors = array();
            foreach ($this->_file_to_check as $fileOrDir) {
                if (!is_writable(dirname(__FILE__) . '/' . $fileOrDir)) {
                    $errors[] = dirname(__FILE__) . '/' . $fileOrDir;
                }
            }
            if (!count($errors)) {
                return true;
            } else {
                $vars = array(
                    'permission_errors' => $errors,
                );
                $this->context->controller->errors[] = $this->fetchTemplate('core/permissions_check.tpl', $vars);
                return false;
            }
        }
        return true;
    }
    protected function showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_'.self::$_module_prefix .'_DISMISS_RATING');
        if ($show && $dismiss != 1 && $this->getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    private function getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM '._DB_PREFIX_.'configuration
                WHERE name = \''.pSQL('PM_'.self::$_module_prefix .'_LAST_VERSION').'\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    protected function fetchTemplate($tpl, $customVars = array(), $configOptions = array())
    {
        $this->context->smarty->assign(array(
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'current_iso_lang' => $this->context->language->iso_code,
            'current_id_lang' => (int)$this->context->language->id,
            'options' => $configOptions,
            'base_config_url' => $this->baseConfigUrl,
        ));
        if (sizeof($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
    
    protected function getPMdata()
    {
        $param = array();
        $param[] = 'ver-'._PS_VERSION_;
        $param[] = 'current-'.$this->name;
        
        $result = $this->getPMAddons();
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $moduleName => $moduleVersion) {
                $param[] = $moduleName . '-' . $moduleVersion;
            }
        }
        return $this->getDataSerialized(implode('|', $param));
    }
    protected function getPMAddons()
    {
        $pmAddons = array();
        $result = Db::getInstance()->ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    protected function doHttpRequest($data = array(), $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->context->language->iso_code),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ), $data);
        $postData = http_build_query($data);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            )
        ));
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = Tools::jsonDecode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    protected function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix  . '_AM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix  . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return array();
        }
        $dataToStore = array();
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = array(
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            );
        }
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_AM', Tools::jsonEncode($dataToStore));
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_AMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix  . '_AM'), true);
    }
    protected function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$_module_prefix  . '_PMM');
        $modules_date = Configuration::get('PM_' . self::$_module_prefix  . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(array('list' => $this->getPMAddons()), 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return array();
        }
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_PMM', Tools::jsonEncode($jsonResponse));
        Configuration::updateValue('PM_' . self::$_module_prefix  . '_PMMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$_module_prefix  . '_PMM'), true);
    }
    protected function shuffleArray(&$a)
    {
        if (is_array($a) && sizeof($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = array();
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    protected function displaySupport()
    {
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = array();
        }
        if (!is_array($pm_products)) {
            $pm_products = array();
        }
        $this->shuffleArray($pm_addons_products);
        if (is_array($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && is_array($pm_products['ignoreList']) && sizeof($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && is_array($addonsList) && sizeof($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = array(
            'support_links' => (is_array($this->_support_link) && sizeof($this->_support_link) ? $this->_support_link : array()),
            'copyright_link' => (is_array($this->copyrightLink) && sizeof($this->copyrightLink) ? $this->copyrightLink : false),
            'pm_module_version' => $this->version,
            'pm_data' => $this->getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
        );
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    protected function _getModuleConfiguration()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            $config = Tools::jsonDecode($conf, true);
            foreach ($this->_defaultConfiguration as $configKey => $configValue) {
                if (!isset($config[$configKey])) {
                    $config[$configKey] = $configValue;
                }
            }
            return $config;
        } else {
            return $this->_defaultConfiguration;
        }
    }
    public static function getModuleConfigurationStatic()
    {
        $conf = Configuration::get('PM_' . self::$_module_prefix . '_CONF');
        if (!empty($conf)) {
            return Tools::jsonDecode($conf, true);
        } else {
            return array();
        }
    }
    protected function _setModuleConfiguration($newConf)
    {
        Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($newConf));
    }
    protected function _setDefaultConfiguration()
    {
        if (!is_array($this->_getModuleConfiguration()) || !sizeof($this->_getModuleConfiguration())) {
            Configuration::updateValue('PM_' . self::$_module_prefix . '_CONF', Tools::jsonEncode($this->_defaultConfiguration));
        }
        return true;
    }
    public function getContent()
    {
        $this->context->controller->addJqueryUI('ui.tabs');
        $this->context->controller->addJqueryPlugin('chosen');
        $this->context->controller->addCSS($this->_path . 'views/css/colpick.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/jquery.tiptip.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/colpick.min.js');
        $this->context->controller->addCSS($this->_path . 'views/css/admin-module.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin-module.js');
        $this->baseConfigUrl = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name;
    }
    protected function _pmClear()
    {
        $this->_html .= '<div class="clear"></div>';
    }
    public static function _getCssRule($selector, $rule, $value, $is_important = false, $params = false, &$css_rules = array())
    {
        $css_rule = '';
        if ((is_array($value) && count($value)) || (Tools::strlen($value) > 0 && $value != '')) {
            switch ($rule) {
                case 'keyframes_spin':
                case 'bg_gradient':
                    if (!is_array($value)) {
                        $val = explode(self::$_gradient_separator, $value);
                    } else {
                        $val = $value;
                    }
                    if (isset($val [1]) && $val [1]) {
                        $color1 = htmlentities($val [0], ENT_COMPAT, 'UTF-8');
                        $color2 = htmlentities($val [1], ENT_COMPAT, 'UTF-8');
                    } elseif (isset($val [0]) && $val [0]) {
                        $color1 = htmlentities($val [0], ENT_COMPAT, 'UTF-8');
                    }
                    if (! isset($color1)) {
                        return '';
                    }
                    if ($rule == 'bg_gradient') {
                        $css_rule .= 'background:' . $color1 . ($is_important ? '!important' : '') . ';';
                        if (isset($color2)) {
                            $css_rule .= 'background: -webkit-gradient(linear, 0 0, 0 bottom, from(' . $color1 . '), to(' . $color2 . '))' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -webkit-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -moz-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -ms-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: -o-linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= 'background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                            $css_rule .= '-pie-background: linear-gradient(' . $color1 . ', ' . $color2 . ')' . ($is_important ? '!important' : '') . ';';
                        }
                    } elseif ($rule == 'keyframes_spin') {
                        if (!isset($color2)) {
                            $color2 = $color1;
                        }
                        $css_rule .= '@keyframes ap5loader { 0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em '. $color2 .'; } 40% { box-shadow: 0 2.5em 0 0 '. $color1 .'; } }';
                        $css_rule .= '@-webkit-keyframes ap5loader { 0%, 80%, 100% { box-shadow: 0 2.5em 0 -1.3em '. $color2 .'; } 40% { box-shadow: 0 2.5em 0 0 '. $color1 .'; } } ';
                    }
                    break;
                case 'color':
                    $css_rule .= 'color:' . $value . ($is_important ? '!important' : '') . ';';
                    break;
                case 'border_color':
                    $css_rule .= 'border-color:' . $value . ($is_important ? '!important' : '') . ';';
                    break;
                case 'border_top_color':
                    if (is_array($value)) {
                        $value = current($value);
                    }
                    $css_rule .= 'border-top-color:' . $value . ($is_important ? '!important' : '') . ';';
                    break;
                case 'border':
                    if ($value == 'none') {
                        $css_rule .= 'border:none!important;';
                    } else {
                        if (!is_array($value)) {
                            $val = explode(self::$_border_separator, $value);
                        } else {
                            $val = $value;
                        }
                        if (isset($val [5]) && $val [5]) {
                            $top    = htmlentities(str_replace('px', '', $val [0]), ENT_COMPAT, 'UTF-8');
                            $right    = htmlentities(str_replace('px', '', $val [1]), ENT_COMPAT, 'UTF-8');
                            $bottom    = htmlentities(str_replace('px', '', $val [2]), ENT_COMPAT, 'UTF-8');
                            $left    = htmlentities(str_replace('px', '', $val [3]), ENT_COMPAT, 'UTF-8');
                            $style    = htmlentities(str_replace('px', '', $val [4]), ENT_COMPAT, 'UTF-8');
                            $color    = htmlentities(str_replace('px', '', $val [5]), ENT_COMPAT, 'UTF-8');
                        } else {
                            return '';
                        }
                        $css_rule .= 'border-top:'   . $top . ($top ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'border-right:'  . $right . ($right ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'border-bottom:' . $bottom . ($bottom ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'border-left:'  . $left .  ($left ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'border-style:' . $style . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'border-color:' . $color . ($is_important ? '!important' : '') . ';';
                    }
                    break;
                case 'box_shadow':
                    if (!is_array($value)) {
                        $val = explode(self::$_shadow_separator, $value);
                    } else {
                        $val = $value;
                    }
                    if ($value == 'none' || (is_array($val) && sizeof($val) == 6 && !$val[0] && !$val[1] && !$val[2] && !$val[3])) {
                        $css_rule .= '-webkit-box-shadow:none!important;';
                        $css_rule .= '-moz-box-shadow:none!important;';
                        $css_rule .= 'box-shadow:none!important;';
                    } else {
                        $css_rule .= '-webkit-box-shadow:' . (isset($val[4]) && $val[4] != 'outset' ? $val[4].' ' : '') . $val[0].($val[0] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[1] .($val[1] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[2] .($val[2] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[3].($val[3] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):''). (isset($val[5]) ? ' '.$val[5] : '') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= '-moz-box-shadow:' . (isset($val[4]) && $val[4] != 'outset' ? $val[4].' ' : '') . $val[0].($val[0] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[1] .($val[1] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[2] .($val[2] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[3].($val[3] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):''). (isset($val[5]) ? ' '.$val[5] : '') . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'box-shadow:' . (isset($val[4]) && $val[4] != 'outset' ? $val[4].' ' : '') . $val[0] .($val[0] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[1] .($val[1] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[2] .($val[2] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[3].($val[3] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):''). (isset($val[5]) ? ' '.$val[5] : '') . ($is_important ? '!important' : '') . ';';
                    }
                    break;
                case 'text_shadow':
                    if (!is_array($value)) {
                        $val = explode(self::$_shadow_separator, $value);
                    } else {
                        $val = $value;
                    }
                    if ($value == 'none' || (is_array($val) && sizeof($val) == 4 && !$val[0] && !$val[1] && !$val[2])) {
                        $css_rule .= 'text-shadow:none!important;';
                    } else {
                        $css_rule .= 'text-shadow:' . $val[0] .($val[0] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[1] .($val[1] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):'').' '. $val[2] .($val[2] ? ($params && isset($params ['suffix']) ? $params ['suffix'] : 'px'):''). ' ' . $val[3] . ($is_important ? '!important' : '') . ';';
                        $css_rule .= 'filter: dropshadow(color='.$val[3].', offx='.$val[0].', offy='.$val[1].')' . ($is_important ? '!important' : '') . ';';
                    }
                    break;
            }
        }
        if (!isset($css_rules[$selector])) {
            $css_rules[$selector] = array();
        }
        $css_rules[$selector][] = $css_rule;
        return $css_rules;
    }
    protected function _generateCSS()
    {
        $advanced_styles = '';
        $css_rules_array = array();
        $config = $this->_getModuleConfiguration();
        foreach ($this->_cssMapTable as $var => $cssRules) {
            foreach ($cssRules as $cssRule) {
                self::_getCssRule($cssRule['selector'], $cssRule['type'], $config[$var], true, false, $css_rules_array);
            }
        }
        if (self::_isFilledArray($css_rules_array)) {
            foreach ($css_rules_array as $selector => $rules) {
                if (self::_isFilledArray($rules)) {
                    if (preg_match('/keyframes_/i', $selector)) {
                        $advanced_styles .= implode('', $rules) . "\n";
                    } else {
                        $advanced_styles .= $selector.' {'.implode('', $rules).'}'."\n";
                    }
                }
            }
        }
        $dynamic_css_file = str_replace('{id_shop}', $this->context->shop->id, self::DYN_CSS_FILE);
        $advanced_styles .= "\n" . $this->_getAdvancedStylesDb();
        if (is_writable(dirname(__FILE__) . '/views/css/')) {
            file_put_contents(dirname(__FILE__) . '/' . $dynamic_css_file, $advanced_styles);
        } else {
            if (!is_writable(dirname(__FILE__) . '/views/css/')) {
                $this->context->controller->errors[] = $this->l('Please set write permision to folder:', $this->_coreClassName). ' '.dirname(__FILE__) . '/views/css/';
            } elseif (!is_writable(dirname(__FILE__) . '/' . $dynamic_css_file)) {
                $this->context->controller->errors[] = $this->l('Please set write permision to file:', $this->_coreClassName). ' '.dirname(__FILE__) . '/' . $dynamic_css_file;
            }
        }
    }
    protected function _updateAdvancedStyles($css_styles)
    {
        Configuration::updateValue('PM_'.self::$_module_prefix.'_ADVANCED_STYLES', self::getDataSerialized(trim($css_styles)));
        $this->_generateCSS();
    }
    protected function _getAdvancedStylesDb()
    {
        $advanced_css_file_db = Configuration::get('PM_'.self::$_module_prefix.'_ADVANCED_STYLES');
        if ($advanced_css_file_db !== false) {
            return self::getDataUnserialized($advanced_css_file_db);
        }
        return false;
    }
    public static function getThumbnailImageHTML($idProduct, $idImage = null)
    {
        if (empty($idImage)) {
            $idImage = Product::getCover($idProduct);
            if (is_array($idImage) && !empty($idImage['id_image'])) {
                $idImage = (int)$idImage['id_image'];
            }
        }
        $image = new Image((int)$idImage);
        $imageType = Context::getContext()->controller->imageType;
        $imagePath = _PS_IMG_DIR_.'p/'.$image->getExistingImgPath().'.'.$imageType;
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $imageManager = new PrestaShop\PrestaShop\Adapter\ImageManager(new PrestaShop\PrestaShop\Adapter\LegacyContext());
            return $imageManager->getThumbnailForListing($idImage);
        } else {
            return ImageManager::thumbnail($imagePath, 'product_mini_'.(int)$idProduct.'.'.$imageType, 45, $imageType);
        }
    }
    protected function removeJSFromController($jsFile)
    {
        if (method_exists($this->context->controller, 'removeJS')) {
            $this->context->controller->removeJS($jsFile);
        } else {
            $jsPath = Media::getJSPath($jsFile);
            if ($jsPath && array_search($jsPath, $this->context->controller->js_files) !== false) {
                unset($this->context->controller->js_files[array_search($jsPath, $this->context->controller->js_files)]);
            }
        }
    }
    protected static $_sortArrayByKeyColumn = null;
    protected static $_sortArrayByKeyOrder = null;
    protected function sortArrayByKey($a, $b)
    {
        if ($a[self::$_sortArrayByKeyColumn] > $b[self::$_sortArrayByKeyColumn]) {
            return (self::$_sortArrayByKeyOrder == 1 ? 1 : -1);
        } elseif ($a[self::$_sortArrayByKeyColumn] < $b[self::$_sortArrayByKeyColumn]) {
            return (self::$_sortArrayByKeyOrder == 1 ? -1 : 1);
        }
        return 0;
    }
}
