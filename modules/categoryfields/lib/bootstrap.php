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

/* Library */
include_once(_PS_MODULE_DIR_.'/categoryfields/lib/classes/CFAjaxResponse.php');
include_once(_PS_MODULE_DIR_.'/categoryfields/lib/classes/CFControllerCore.php');

/* Models */
include_once(_PS_MODULE_DIR_.'/categoryfields/models/CFInstall.php');
include_once(_PS_MODULE_DIR_.'/categoryfields/models/CFCategoryFieldModel.php');
include_once(_PS_MODULE_DIR_.'/categoryfields/models/CFCategoryFieldContentModel.php');

/* Controllers */
include_once(_PS_MODULE_DIR_.'/categoryfields/controllers/admin/config/CFAdminConfigMainController.php');
include_once(_PS_MODULE_DIR_.'/categoryfields/controllers/admin/category/CFAdminCategoryMainController.php');
include_once(_PS_MODULE_DIR_.'/categoryfields/controllers/front/CFFrontCategoryController.php');