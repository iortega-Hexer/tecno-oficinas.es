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

class CFControllerCore extends Module
{
	protected $module_ajax_url = '';
	protected $module_config_url = '';
	protected $sibling;
	protected $helper_form;
	protected $helper_list;
	protected $params = array();

	protected $key_tab = 'ModuleCategoryfields';

	public function __construct($sibling, $params = array())
	{
		$this->sibling = $sibling;
		if (!empty($params))
			$this->params = $params;

		parent::__construct();

		$this->module_ajax_url = $this->getShopBaseUrl().'ajax.php';

		if (Tools::getValue('controller') == 'AdminModules')
			$this->module_config_url = AdminController::$currentIndex.'&configure='.$this->sibling->name.'&token='.Tools::getAdminTokenLite('AdminModules');
		else
			$this->module_config_url = '';

		if (AdminController::$currentIndex != '')
			$this->module_tab_url = AdminController::$currentIndex.'&'.'updateproduct&id_product='.Tools::getValue('id_product').'&token='.Tools::getAdminTokenLite('AdminProducts').'&key_tab='.$this->key_tab;
	}

	/**
	 * Get the url to the module folder
	 * @return string
	 */
	protected function getShopBaseUrl()
	{
		if (Tools::getShopDomain() != $_SERVER['HTTP_HOST'])
			$domain = $_SERVER['HTTP_HOST'];
		else
			$domain = Tools::getShopDomain();

		if (empty($_SERVER['HTTPS']) || !$_SERVER['HTTPS'])
			return "http://".$domain.__PS_BASE_URI__.'modules/'.$this->sibling->name.'/';
		else
			return "https://".$domain.__PS_BASE_URI__.'modules/'.$this->sibling->name.'/';
	}

	/**
	 * get pth to admin folder
	 * @return mixed
	 */
	protected function getAdminWebPath()
	{
        $admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath);
		return __PS_BASE_URI__.$admin_webpath;
	}

	/* Protected Methods */
	protected function redirect($url_params)
	{
		$url = AdminController::$currentIndex.'&configure='.$this->sibling->name.'&'.$url_params.'&token='.Tools::getAdminTokenLite('AdminModules');
		Tools::redirectAdmin($url);
	}

	protected function setupHelperForm()
	{
		$this->helper_form = new HelperForm();
		$this->helper_form->module = $this->sibling;
		$this->helper_form->identifier = $this->identifier;
		$this->helper_form->token = Tools::getAdminTokenLite('AdminModules');
		$this->helper_form->show_toolbar = false;
		$this->helper_form->submit_action = ""; // PS 1.5.4 adds submitAdd to the form action otherwise which breaks the module

		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		foreach (Language::getLanguages(false) as $lang)
			$this->helper_form->languages[] = array(
				'id_lang' => $lang['id_lang'],
				'iso_code' => $lang['iso_code'],
				'name' => $lang['name'],
				'is_default' => ($id_lang == $lang['id_lang'] ? 1 : 0)
			);

		$this->helper_form->currentIndex = AdminController::$currentIndex.'&configure='.$this->sibling->name;
		$this->helper_form->default_form_language = $id_lang;
		$this->helper_form->allow_employee_form_lang = $id_lang;
		$this->helper_form->toolbar_scroll = true;
	}

	protected function setupHelperList($title)
	{
		$this->helper_list = new HelperList();
		$this->helper_list->shopLinkType = '';
		$this->helper_list->simple_header = true;
		$this->helper_list->actions = array('edit', 'delete');

		$this->helper_list->show_toolbar = true;
		$this->helper_list->title = $title;

		$this->helper_list->currentIndex = AdminController::$currentIndex.'&configure='.$this->sibling->name;
		$this->helper_list->token = Tools::getAdminTokenLite('AdminModules');
		return null;
	}
}