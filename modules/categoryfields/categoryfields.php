<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . '/categoryfields/lib/bootstrap.php');

class CategoryFields extends Module
{

    const __MA_MAIL_DELIMITOR__ = ',';

    public $module_folder = 'categoryfields';
    public $module_file = __FILE__;
    public $base_url = '';

    protected $controller_front;
    protected $controller_admin_hooks;
    protected $controller_config;
    protected $controller_config_carrier;
    protected $controller_daterange;
    protected $controller_timeslots;

    public function __construct()
    {
        $this->name = 'categoryfields';
        $this->tab = 'others';
        $this->version = '2.0.10';
        $this->author = 'Musaffar Patel';
        parent::__construct();
        $this->displayName = $this->l('Category Fields');
        $this->description = $this->l('Category Fields display additional rich content fields on your category pages');
        $this->module_key = '905b029390317dd0b3c0d631036ffbee';
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->file = __FILE__;
        $this->bootstrap = true;

        $this->base_url = Tools::getShopProtocol() . Tools::getShopDomain() . __PS_BASE_URI__;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('displayBackOfficeCategory')
            || !$this->registerHook('actionCategoryAdd')
            || !$this->registerHook('actionCategoryUpdate')
            || !$this->registerHook('categoryField')
            || !$this->installModule()
        ) {
            return false;
        }
        return true;
    }

    private function installModule()
    {
        return CFInstall::installDB();
    }

    public function uninstall()
    {
        CFInstall::uninstall();
        return parent::uninstall();
    }

    /* Call set media for all the various controllers in this module.  Each controller will decide if the time is appropriate for queuing it's css and js */
    public function setMedia()
    {
        (new CFAdminConfigMainController($this))->setMedia();
        (new CFAdminCategoryMainController($this))->setMedia();
        (new CFFrontCategoryController($this))->setMedia();
    }

    public function route()
    {
        switch (Tools::getValue('route')) {
            case 'cfadminconfigmaincontoller':
                return (new CFAdminConfigMainController($this))->route();
            default:
                return (new CFAdminConfigMainController($this))->route();
        }
    }

    public function getContent()
    {
        return $this->route();
    }

    public function hookHeader($params)
    {
        $this->setMedia();
    }

    public function hookBackOfficeHeader($params)
    {
        $this->setMedia();
    }

    public function hookDisplayBackOfficeCategory($params)
    {
        return (new CFAdminCategoryMainController($this, $params['request']))->route();
    }

    public function hookActionCategoryUpdate($params)
    {
        return (new CFAdminCategoryMainController($this))->hookActionCategoryUpdate($params);
    }

    /**
     * Front end template hook to render field content
     * @param $params
     * @return mixed
     */
    public function hookCategoryField($params)
    {
        return (new CFFrontCategoryController($this))->hookCategoryField($params);
    }
}
