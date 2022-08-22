<?php
/**
* 2612-2015 PrestaSOO
*
* NOTICE OF LICENSE
*
* This is a commercial license
* Do not allow to re-sales, edit without permission from Vietmoonlight.
* International Registered Trademark & Property of Vietmoonlight
*
* @author    Frank <sales@vietmoonlight.com>
* @copyright PrestaSOO.com
* @license   Commercial License. All right reserved
*/

if (!defined('_PS_VERSION_'))
exit;

class AdminSoobrandlogoSliderController extends AdminController {
	public function __construct()
	{
		//redirect
		$path = $_SERVER['SCRIPT_NAME'];
		Tools::redirectAdmin($path.'?controller=AdminModules&token='
			.Tools::getAdminTokenLite('AdminModules').'&configure=soobrandlogoslider&tab_module=front_office_features&module_name=soobrandlogoslider');
	}
}
