<?php
/**
 * 2007-2019 PrestaShop
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
 * @author    DMConcept <support@dmconcept.fr>
 * @copyright 2015 DMConcept
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once(dirname(__FILE__) . '/../../vendors/PHPImageWorkshop/ImageWorkshop.php');
require_once(dirname(__FILE__) . '/../../classes/ConfiguratorAttachment.php');
require_once(dirname(__FILE__) . '/../../classes/ConfiguratorStepGroupModel.php');

class ConfiguratorProductModuleFrontController extends ProductController
{
    /**
     * Tell to controller that we are on a configurator or not
     * @var boolean $use_module_configurator
     */
    public $module;
    public $use_module_configurator = false;
    public $configurator;
    public $configurator_product;
    /**
     * @var ConfiguratorCartDetailModel
     */
    public $configuratorCartDetail;
    public $configuratorErrors = array();
    /**
     * Doesn't exist in 1.7
     * @var string
     */
    public $success = 1;
    public $message = '';
    public $steps_errors = array();
    public $options_error = array();
    //public $price = 0.0;
    /**
     * If updateCart true,
     * we'll update the configuration in current cart
     */
    public $updateCart = false;

    /**
     * We need to get setTemplate behavior for module
     * @see ModuleFrontController
     * @param type $template
     * @throws PrestaShopException
     */
    public function setTemplate($template, $params = array(), $locale = null)
    {
        if ($this->use_module_configurator) {
            if ($path = $this->getTemplatePath($template)) {
                $this->template = $path;
            }
        } else {
            if (DMTools::getVersionMajor() === 16) {
                return parent::setTemplate($template);
            }

            return parent::setTemplate($template, $params, $locale);
        }
    }

    /**
     * We need to get getTemplatePath behavior for module
     * @see ModuleFrontController
     * @return string
     */
    public function getTemplatePath($template)
    {
        if ($this->use_module_configurator) {
            if (Tools::file_exists_cache(_PS_THEME_DIR_ . 'modules/configurator/' . $template)) {
                return _PS_THEME_DIR_ . 'modules/configurator/' . $template;
            } elseif (Tools::file_exists_cache(
                _PS_THEME_DIR_ . 'modules/configurator/views/templates/front/' . $template
            )) {
                return _PS_THEME_DIR_ . 'modules/configurator/views/templates/front/' . $template;
            } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_ . 'configurator/views/templates/front/' . $template)) {
                return _PS_MODULE_DIR_ . 'configurator/views/templates/front/' . $template;
            }

            return false;
        } else {
            return parent::getTemplatePath($template);
        }
    }

    /**
     * @deprecated ?
     */
    public function setMedia2()
    {
        parent::setMedia();
        $this->addJqueryUi('ui.widget');
        // Uploader
        $this->addJs(_PS_MODULE_DIR_ . 'configurator/views/js/fileupload/jquery.iframe-transport.js');
        $this->addJs(_PS_MODULE_DIR_ . 'configurator/views/js/fileupload/jquery.fileupload.js');
        $this->addJs(_PS_MODULE_DIR_ . 'configurator/views/js/fileupload/jquery.fileupload-process.js');
        $this->addJs(_PS_MODULE_DIR_ . 'configurator/views/js/fileupload/jquery.fileupload-validate.js');
        $this->addJs(_PS_MODULE_DIR_ . 'configurator/views/js/jquery.unform-modified.js');

        $this->addJs(__PS_BASE_URI__ . 'js/vendor/spin.js');
        $this->addJs(__PS_BASE_URI__ . 'js/vendor/ladda.js');
        // Plugins
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/circle-progress.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/jquery-scrolltofixed-min.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/jquery-scrollfix.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/sticky.min.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/bootstrap.min.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/front/modules/fancybox/jquery.fancybox.js');
        $this->addCss(_MODULE_DIR_ . 'configurator/views/css/front/modules/fancybox/jquery.fancybox.css');
        // Services
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/services/tools.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/services/scroll-fix.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/services/window-helper.js');
        $this->addJs(_MODULE_DIR_ . 'configurator/views/js/services/layers-manager.js');
    }

    /**
     * Override de ControllerCore::ajaxDie()
     * Cette méthode n'existe pas pour des boutiques < 1.6.0.13
     * @param type $value
     * @param string $controller
     * @param string $method
     */
    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        if (version_compare(_PS_VERSION_, '1.6.0.13', '>=') === true) {
            parent::ajaxDie($value, $controller, $method);
        }

        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace();
            $method = $bt[1]['function'];
        }

        Hook::exec('actionBeforeAjaxDie', array('controller' => $controller, 'method' => $method, 'value' => $value));
        Hook::exec('actionBeforeAjaxDie' . $controller . $method, array('value' => $value));

        die($value);
    }


    protected function configuratorInit()
    {
        // Retrieve cart (from admin for example)
        if (Tools::getValue('hash')) {
            $hash = md5(_COOKIE_KEY_ . '-' . Tools::getValue('configurator_update')) . '-' . md5(_COOKIE_KEY_ . '-' . Tools::getValue('id_cart'));
            if ($hash === Tools::getValue('hash')) {
                //Context::getContext()->cart = new Cart(Tools::getValue('id_cart'));
            }
        }

        Hook::exec("configuratorInit", array(
            'controller' => &$this
        ));

		// Retrieve CartDetail if this is an update
        $id_product = (int)$this->product->id;
        $configurator_cart_detail = new ConfiguratorCartDetailModel((int)Tools::getValue('configurator_update', 0));
        if (Validate::isLoadedObject($configurator_cart_detail)
            && ((int)$configurator_cart_detail->id_cart === (int)$this->context->cart->id
				|| (int)$configurator_cart_detail->id_cart === (int)Tools::getValue('id_cart'))) {
            $this->configuratorCartDetail = $configurator_cart_detail;
            $this->configurator = ConfiguratorModel::getFullConfigurator((int)$id_product);
            $this->configurator_product = new Product((int)$id_product, false, (int)$this->context->language->id);
            $this->updateCart = true;
            $this->use_module_configurator = true;
        }

        // ID product is linked to a configurator
        if (!$this->use_module_configurator && ConfiguratorModel::productHasConfigurator((int)$id_product)) {
            $this->configurator = ConfiguratorModel::getFullConfigurator((int)$id_product);
            $this->configurator_product = new Product((int)$id_product, false, (int)$this->context->language->id);
            $this->use_module_configurator = true;
            // ID product is a configurated product
        }
    }

    /**
     * Override de pictureUpload
     * pour faire le même process sur le produit dupliqué
     * la customization
     * @return boolean
     * @deprecated ?
     */
    protected function pictureUpload()
    {
        if (!$field_ids = $this->product->getCustomizationFieldIds()) {
            return false;
        }

        if (!$field_ids_copy = $this->configuratorCartDetail->product->getCustomizationFieldIds()) {
            return false;
        }

        $authorized_file_fields = array();
        $authorized_text_fields_copy = array();
        foreach ($field_ids as $k => $field_id) {
            if ($field_id['type'] == Product::CUSTOMIZE_FILE) {
                $authorized_file_fields[
                    (int)$field_id['id_customization_field']
                ] = 'file' . (int)$field_id['id_customization_field'];
                $authorized_text_fields_copy[
                    (int)$field_ids_copy[$k]['id_customization_field']
                ] = 'file' . (int)$field_id['id_customization_field'];
            }
        }
        $indexes = array_flip($authorized_file_fields);
        $indexes_copy = array_flip($authorized_text_fields_copy);
        foreach ($_FILES as $field_name => $file) {
            if (in_array($field_name, $authorized_file_fields)
                && isset($file['tmp_name'])
                && !empty($file['tmp_name'])
            ) {
                $file_name = md5(uniqid(rand(), true));
                if ($error = ImageManager::validateUpload(
                    $file,
                    (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE')
                )) {
                    $this->errors[] = $error;
                }

                $product_picture_width = (int)Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                $product_picture_height = (int)Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                if ($error || (!$tmp_name || !move_uploaded_file($file['tmp_name'], $tmp_name))) {
                    return false;
                }
                /* Original file */
                if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name)) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } /* A smaller one */
                elseif (!ImageManager::resize(
                    $tmp_name,
                    _PS_UPLOAD_DIR_ . $file_name . '_small',
                    $product_picture_width,
                    $product_picture_height
                )) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777)
                    || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777)
                ) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } else {
                    $this->context->cart->addPictureToProduct(
                        $this->product->id,
                        $indexes[$field_name],
                        Product::CUSTOMIZE_FILE,
                        $file_name
                    );
                    $this->context->cart->addPictureToProduct(
                        $this->configuratorCartDetail->product->id,
                        $indexes_copy[$field_name],
                        Product::CUSTOMIZE_FILE,
                        $file_name
                    );
                }
                unlink($tmp_name);
            }
        }
        return true;
    }

    /**
     * 2 dimensional array for each error
     *
     * @param array $steps_errors
     */
    public function setTranslatedErrors(array $steps_errors)
    {
        foreach ($steps_errors as $id_step => $error) {
            if (is_array($error)) {
                $steps_errors[$id_step] = sprintf($this->module->getErrorTranslation($error['key']), $error['args']);
            } else {
                $steps_errors[$id_step] = $this->module->getErrorTranslation($error);
            }
        }
        // Pas de array_merge on a besoin de garder les id step en clé numérique
        $this->steps_errors = $this->steps_errors + $steps_errors;
    }

    public function init()
    {
        $this->module = new Configurator();
        $this->setCurrentIdProduct();

        // Display a success notification on product_page
        if (DMTools::getVersionMajor() !== 16) {
            $this->success = 0;
        }

        parent::init();

        if ((int)Tools::getValue('resetSharedStep') === 1) {
            // Supprime les produits du panier lié à une étape partagée
            ConfiguratorCartDetailModel::resetSharedStepInCartByIdProduct((int)Tools::getValue('id_product'));
        }

        $this->configuratorInit();
        if ($this->use_module_configurator && !$this->updateCart) {
            $this->configuratorCartDetail = $this->getCurrentConfiguratorCartDetail();
        }
    }

    private function setCurrentIdProduct()
    {
        /**
         * Special process when shome shop use module which
         * remove id product from url ... (eg. : Advanced Url)
         */
        if (!(int)Tools::getValue('id_product') && Tools::getValue('module') === $this->module->name) {
            $id_product = (int)Db::getInstance()->getValue(
                'SELECT `id_product` FROM `' . _DB_PREFIX_
                . 'product_lang` WHERE `link_rewrite` = "' . pSQL(Tools::getValue('url_rewrite')) . '" AND `id_lang` = '
                . (int)$this->context->language->id . ' AND `id_shop` = ' . (int)$this->context->shop->id
            );
            if ($id_product) {
                $_GET['id_product'] = (int)$id_product;
            }
        }
    }

    public function initContent()
    {
        // Conditions de performances
        if (!$this->use_module_configurator || $this->use_module_configurator && !$this->ajax) {
            parent::initContent();
        }

        if ($this->use_module_configurator) {
            // On gère l'affichage des erreurs par étape seulement si on a essayé d'ajouter au panier
            if (Tools::isSubmit('add') && count($this->steps_errors)) {
                foreach ($this->steps_errors as $id_step => $error) {
                    $step = ConfiguratorStepFactory::newObject((int)$id_step, (int)$this->context->language->id);
                    $this->configuratorErrors[] = sprintf(
                        $this->module->getErrorTranslation(Configurator::ERROR_STEP_OCCURED),
                        $step->public_name,
                        $error
                    );
                }
            }

            // This instruction is usefull to prevent problems when displaying product page
            if (!count($this->errors)) {
                $this->errors = $this->configuratorErrors;
            }

            /**
             * Override part of customization
             */
            if ($this->product->customizable && $this->updateCart) {
                $pictures = array();
                $text_fields = array();
                $files = $this->context->cart->getProductCustomization(
                    $this->product->id,
                    Product::CUSTOMIZE_FILE,
                    false
                );
                foreach ($files as $file) {
                    $pictures['pictures_' . $this->product->id . '_' . $file['index']] = $file['value'];
                }

                $texts = $this->context->cart->getProductCustomization(
                    $this->product->id,
                    Product::CUSTOMIZE_TEXTFIELD,
                    false
                );
                foreach ($texts as $text_field) {
                    $text_fields['textFields_' . $this->product->id . '_' . $text_field['index']] = str_replace(
                        '<br />',
                        "\n",
                        $text_field['value']
                    );
                }
                $this->context->smarty->assign(array(
                    'pictures' => $pictures,
                    'textFields' => $text_fields,
                ));
            }

            if (DMTools::getVersionMajor() >= 17) {
                $this->setTemplate('product_17.tpl');
            } else {
                $this->setTemplate('product.tpl');
            }

            // CONFIGURATOR HOOK
            $CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN = Hook::exec(
                'configuratorDisplayFrontProductLeftColumn',
                array(
                    'configurator' => $this->configurator,
                    'configuratorCartDetail' => $this->configuratorCartDetail,
                    'productObject' => $this->product,
                )
            );
            
            $this->setStepsInfoText();

            $datas = array(
                'configurator' => $this->configurator,
                'configuratorCartDetail' => $this->configuratorCartDetail,
                'configuratorStepTab' => ConfiguratorStepTabModel::getTabsByIdConfigurator($this->configurator->id),
                // number of tabs in one line
                'nbTabsGroup' => (int)Tools::ceilf(
                    count(ConfiguratorStepTabModel::getTabsByIdConfigurator($this->configurator->id))
                ),
                'id_lang' => (int)$this->context->language->id,
                'lang_id' => (int)$this->context->language->id,
                'productObject' => $this->product,
                'img_col_dir' => _THEME_COL_DIR_,
                'previewHtml' => $this->getPreviewHtml(),
                'display_price' => (int)Configuration::get('CONFIGURATOR_STEP_PRICE'),
                'DISPLAY_TOOLTIP_FANCYBOX' => (int)Configuration::get('CONFIGURATOR_DISPLAY_TOOLTIP_FANCYBOX'),
                'PROGRESSIVE_DISPLAY' => Configuration::get('CONFIGURATOR_PROGRESSIVE_DISPLAY'),
                'TOOLTIP_DISPLAY' => Configuration::get('CONFIGURATOR_TOOLTIP_DISPLAY'),
                'PROGRESS_START_COLOR' => Configuration::get('CONFIGURATOR_PROGRESS_START_COLOR'),
                'PROGRESS_END_COLOR' => Configuration::get('CONFIGURATOR_PROGRESS_END_COLOR'),
                'CONFIGURATOR_FLOATING_PREVIEW' => Configuration::get('CONFIGURATOR_FLOATING_PREVIEW'),
                'CONFIGURATOR_POPOVER_TRIGGER' => Configuration::get('CONFIGURATOR_POPOVER_TRIGGER'),
                'ERROR_LIST' => $this->getErrorList(),
                'update_cart' => $this->updateCart,
                'use_custom_left_column' => (bool)$CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN,
                'HOOK_CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN' => $CONFIGURATOR_DISPLAY_FRONT_PRODUCT_LEFT_COLUMN,
                'format_date' => Context::getContext()->language->date_format_lite,
                'content_only' => Tools::getValue('content_only'),
                'tabs_status' => $this->getTabsStatus(),
                'configuratorInfoText' => $this->configuratorCartDetail->steps_info_text
            );
            $this->context->smarty->assign($datas);
            $this->context->smarty->assign(array('configuratorHtml' => $this->getConfiguratorHtml($datas)));
        }
    }

    public function getErrorList()
    {
        $errors = array();

        $errors['GENERAL'] = array(
            'title' => $this->module->l('Oops ! Something went wrong', 'product'),
            'type' => 'WARNING',
            'message' => $this->module->l('You should try again or refresh the page', 'product')
        );

        return $errors;
    }

    public function postProcess()
    {
        parent::postProcess();

        if ($this->use_module_configurator) {
            $old_progress = (int)$this->configuratorCartDetail->progress;
            $this->processCartDetail();
            /**
             * Price with configurator without reduction and user group and other computre of Prestahsop price
             */
            //$this->price = $this->configuratorCartDetail->compute($this->configurator_product);
            $this->configuratorCartDetail->compute($this->configurator_product);

            if (count($this->configuratorCartDetail->steps_errors)) {
                $this->success = 0;
                $this->setTranslatedErrors($this->configuratorCartDetail->steps_errors);
                $this->options_error = array_merge(
                    $this->options_error,
                    $this->configuratorCartDetail->option_ids_errors
                );
            }

            $this->setStepsInfos();
            $this->setStepsInfoText();
            $this->refreshDisabled();

            $action = Tools::getValue('action');
            if ($this->ajax && $action === 'upload') {
                $this->displayAjaxUpload();
            }

            if (Tools::isSubmit('add') && !count($this->configuratorCartDetail->steps_errors)) {
                $this->processSubmitCart();
            }

            if ($this->ajax) {
                $return = array(
                    'success' => $this->success,
                    'steps_errors' => $this->steps_errors,
                    'options_error' => $this->options_error,
                    'steps_infos' => $this->configuratorCartDetail->steps_infos,
                    'steps_info_text' => $this->configuratorCartDetail->steps_info_text,
                    'detail' => $this->configuratorCartDetail->getDetail(true),
                    'progress_end' => (int)$this->configuratorCartDetail->progress,
                    'progress_start' => $old_progress,
                    'previewHtml' => $this->getPreviewHtml(),
                    'tabs_status' => $this->getTabsStatus()
                );
                $this->ajaxDie(Tools::jsonEncode($return));
            }
        }
    }

    protected function getTabsStatus()
    {
        $tabsStatus = [];

        if (!$this->configurator->tab_force_require_step) {
            return $tabsStatus;
        }

        $requiredSteps = ConfiguratorStepAbstract::getRequiredSteps((int)$this->configurator->id);
        $detail = $this->configuratorCartDetail->getDetail();
        foreach ($requiredSteps as $step) {

            if ((int)$step->id_configurator_step_tab <= 0) {
                continue;
            }

            if (!isset($tabsStatus[$step->id_configurator_step_tab])) {
                $tabsStatus[$step->id_configurator_step_tab] = [
                    'id' => $step->id_configurator_step_tab,
                    'valid' => true
                ];
            }

            if ($step->ignored_if_empty) {
                // Ignorer la vérification des champs requis si l'étape ne possède pas d'options
                if (isset($detail[$step->id]['options']) && count($detail[$step->id]['options']) === 0) {
                    continue;
                }
            }

            if ((int)$step->displayed_by_yes && isset($detail[$step->id])
                && array_key_exists('yes_no_value', $detail[$step->id])
                && $detail[$step->id]['yes_no_value'] !== true
            ) {
                continue;
            }

            if ($this->configuratorCartDetail->stepCanBeAdded($step)) {
                $found_one_selected = $step->use_input ? true : false;

                if ($step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)
                    && $this->configuratorCartDetail->getNumberOfAttachments((int)$step->id) > 0
                ) {
                    $found_one_selected = true;
                } elseif (!$step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)) {
                    $optionsStep = ConfiguratorStepOptionAbstract::getByIdConfiguratorStep((int)$step->id);
                    foreach ($optionsStep as $option_step) {
                        if (!$step->use_input) {
                            $found_one_selected = $this->configuratorCartDetail->foundOption((int)$option_step->id)
                                ? true
                                : $found_one_selected;
                        } elseif($step->use_input && $this->configuratorCartDetail->existOption((int)$option_step->id )) {
                            // vérifier si id_option est dans configurator_cart_detail
                            $found_one_selected = $found_one_selected
                                && $this->configuratorCartDetail->foundOption((int)$option_step->id, false, true);
                        }
                    }
                }

                if (!$found_one_selected) {
                    $tabsStatus[$step->id_configurator_step_tab]['valid'] = false;
                }
            }
        }

        return $tabsStatus;
    }

    public function refreshDisabled()
    {
        $details = $this->configuratorCartDetail->getDetail();
        foreach ($this->configurator->steps as $step) {
            $disabled = false;
            if ($step->use_shared) {
                $cart_details = ConfiguratorCartDetailModel::getMultipleByIdConfiguratorAndIdCart(
                    $this->configurator->id,
                    Context::getContext()->cart->id
                );
                foreach ($cart_details as $cart_detail) {
                    if ($cart_detail->added_in_cart && $cart_detail->id !== $this->configuratorCartDetail->id) {
                        $disabled = true;
                        break;
                    }
                }
            }
            if (isset($details[$step->id]['id'])) {
                $details[$step->id]['disabled'] = (int)$disabled;
            }
        }
        $this->configuratorCartDetail->setDetail($details);
        $this->configuratorCartDetail->save();
    }

    public function setStepsInfos()
    {
        $detail = $this->configuratorCartDetail->getDetail();

        /** @var ConfiguratorStepAbstract $step */
        foreach ($this->configurator->steps as $step) {
            if ($step->use_shared && count(ConfiguratorCartDetailModel::getMultipleByIdConfiguratorAndIdCart(
                $this->configurator->id,
                Context::getContext()->cart->id
            )) > 0) {
                $str_trans = 'This step is common for all your configured products.';
                $str_trans .= ' If you change it, your other products in the cart will be deleted.';

                $html = '<div class="row"><div class="col-sm-8">';
                $html .= '<p><span class="configurator_step_info"></span>' . $this->module->l($str_trans, 'product') . '</p>';
                $html .= '</div><div class="col-sm-4">';
                $html .= '<button type="button" class="btn btn-info configurator-change-shared-step">';
                $html .= $this->module->l('Change this step', 'product') . '</button>';
                $html .= '</div></div>';
                $this->configuratorCartDetail->steps_infos[$step->id] = $html;
            }
            
            /**
             * Check minimum quantity informations only for customer informations
             */
            if (!$this->configuratorCartDetail->checkMinCurrentQty($step, $detail)) {
                $this->configuratorCartDetail->steps_infos[$step->id] = sprintf(
                    $this->module->l('You must choose at least %s options', 'product'),
                    (int)$step->getMinQty($this->configuratorCartDetail)
                );
            }

            // Check empty step
            if ($step->type !== ConfiguratorStepAbstract::TYPE_STEP_UPLOAD && $step->type !== ConfiguratorStepAbstract::TYPE_STEP_DESIGNER && isset($detail[$step->id]) && count($detail[$step->id]['options']) <= 0) {
                $this->configuratorCartDetail->steps_infos[$step->id] = $this->module->l('There is no option corresponding to your configuration.', 'product');
            }
        }
    }
    
    public function setStepsInfoText()
    {
        $detail = $this->configuratorCartDetail->getDetail();
        foreach ($this->configurator->steps as $step) {
            if ($step->info_text) {
                if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')) {
                    $this->configuratorCartDetail->steps_info_text[$step->id] = AdvancedformulaHelper::loadFormulasInText($this->configuratorCartDetail, $step, $detail, $step->info_text);
                } else {
                    $this->configuratorCartDetail->steps_info_text[$step->id] = $step->info_text;
                }
            }
        }
    }

    public function displayAjaxUpload()
    {
        $file_has_been_uploaded = false;
        $step = ConfiguratorStepFactory::newObject(Tools::getValue('step'));

        if (!Validate::isLoadedObject($step)) {
            $files = array();
            $files[0]['error'] = $this->module->l('Cannot add file because step creation failed.', 'product');
        }

        $file_uploader = $step->getUploader();
        $files = $file_uploader->process();

        foreach ($files as &$file) {
            if (!empty($file['error'])) {
                continue;
            }

            $max_size = (int)$step->getMaxFilesSize();
            if ($max_size > 0) {
                $uploaded_size = (int)$step->getUploadedFilesSize($this->configuratorCartDetail);
                if (((int)$uploaded_size + (int)$file['size']) > (int)$max_size) {
                    $file['error'] = $this->module->l('You have reached the maximum weight.', 'product');
                    continue;
                }
            }

            do {
                $uniqid = sha1(microtime());
            } while (file_exists(_PS_DOWNLOAD_DIR_ . $uniqid));
            if (!@copy($file['save_path'], _PS_DOWNLOAD_DIR_ . $uniqid)) {
                $file['error'] = $this->module->l('File copy failed', 'product');
                continue;
            }

            // Prevent hacking
            @unlink($file['save_path']);
            unset($file['save_path']);

            $attachment = new ConfiguratorAttachment();
            $attachment->file = $uniqid;
            $attachment->file_name = $file['name'];
            $attachment->mime = $file['type'];
            $attachment->token = Tools::encrypt(microtime());

            if (!$attachment->save()) {
                @unlink(_PS_DOWNLOAD_DIR_ . $uniqid);
                $file['error'] = $this->module->l('This attachment was unable to be loaded into the database.', 'product');
                continue;
            }

            $cartdDetailId = Tools::getValue('configurator_update');
            if (!$cartdDetailId) {
                $cartdDetailId = $this->configuratorCartDetail->id;
            }
            if (!$attachment->attachCartDetail((int)$cartdDetailId, (int)$step->id)) {
                $attachment->delete();
                $file['error'] = $this->module->l('We were unable to associate this attachment to configuration\'s detail.', 'product');
                continue;
            }

            $file['link'] = $this->context->link->getModuleLink(
                'configurator',
                'attachment',
                array('token' => $attachment->token)
            );
            $file['delete_url'] = $this->context->link->getModuleLink(
                'configurator',
                'attachment',
                array('token' => $attachment->token, 'action' => 'delete')
            );
            $file_has_been_uploaded = true;
        }

        if ($file_has_been_uploaded) {
            $detail = $this->configuratorCartDetail->getDetail();
            $detail[$step->id]['file_has_been_uploaded'] = $file_has_been_uploaded;
            $detail[$step->id]['attachments'] = $this->configuratorCartDetail->getAttachmentsWithLink((int)$step->id);
            $this->configuratorCartDetail->setDetail($detail);
            $this->configuratorCartDetail->save();
        }

        $this->ajaxDie(Tools::jsonEncode(array($file_uploader->getName() => $files)));
    }


    public function getCurrentConfiguratorCartDetail()
    {
        $configuratorCartDetail = $this->configuratorCartDetail;

        if (!Validate::isLoadedObject($configuratorCartDetail)) {
            $configuratorCartDetail = ConfiguratorCartDetailModel::getByIdConfiguratorAndIdGuest(
                (int)$this->configurator->id,
                (int)$this->context->cart->id_guest ? (int)$this->context->cart->id_guest : (int)$this->context->cookie->id_guest
            );
            // Add CartDetail if not exist
            if (!Validate::isLoadedObject($configuratorCartDetail) || !isset($configuratorCartDetail->id)) {
                $configuratorCartDetail->id_configurator = (int)$this->configurator->id;
                $configuratorCartDetail->id_cart = (int)$this->context->cart->id;
                $configuratorCartDetail->id_guest = (int)$this->context->cart->id_guest;
                $configuratorCartDetail->id_product = (int)$this->configurator_product->id;
                $configuratorCartDetail->id_product_attribute =
                    (int)$this->configurator_product->cache_default_attribute;
                $configuratorCartDetail->product = $this->configurator_product;
                $configuratorCartDetail->add();
                // Suppression du cache produits pour éviter un conflit de prix
                Product::flushPriceCache();
            }
            // If not ajax, we reinit CartDetail
            if (!$this->ajax && !Tools::isSubmit('add') && !Tools::isSubmit('saveCustomization')) {
                $configuratorCartDetail->detail = $configuratorCartDetail->setDetail('');
                $configuratorCartDetail->save();
                $configuratorCartDetail->deleteAttachements();
            }
        }
        return $configuratorCartDetail;
    }

    public function getConfiguratorHtml($datas)
    {
        if (DMTools::getVersionMajor() >= 17) {
            $tpl = $this->context->smarty->createTemplate(
                $this->getTemplatePath('elements/configurator_17.tpl'),
                $this->context->smarty
            );
        } else {
            $tpl = $this->context->smarty->createTemplate(
                $this->getTemplatePath('elements/configurator.tpl'),
                $this->context->smarty
            );
        }
        
        $tpl->assign($datas);
        return $tpl->fetch();
    }

    public function getPreviewModalHtml($params)
    {
        $tpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath('elements/cart_preview_modal.tpl'),
            $this->context->smarty
        );

        $tpl->assign($params);
        return $tpl->fetch();
    }
    
    public function getPreviewHtml()
    {
        if (DMTools::getVersionMajor() >= 17) {
            $tpl = $this->context->smarty->createTemplate(
                $this->getTemplatePath('elements/cart_preview_17.tpl'),
                $this->context->smarty
            );
        } else {
            $tpl = $this->context->smarty->createTemplate(
                $this->getTemplatePath('elements/cart_preview.tpl'),
                $this->context->smarty
            );
        }

        $params = $this->getPreviewParams();
        $params['modal_confirmation'] = $this->getPreviewModalHtml($params);
      
        $tpl->assign($params);
        return $tpl->fetch();
    }

    private function getPreviewParams()
    {
        $qty = (int)Tools::getValue('qty', 1);
        if ($this->updateCart) {
            $customization = new Customization($this->configuratorCartDetail->id_customization);
            $qty = $customization->quantity;
        }
        if ($qty < $this->configuratorCartDetail->product->minimal_quantity) {
            $qty = (int)$this->configuratorCartDetail->product->minimal_quantity;
        }

        $currentGroupDisplayPrice = true;
        $grp = Group::getCurrent();
        if(Validate::isLoadedObject($grp)) {
			$currentGroupDisplayPrice = (bool)$grp->show_prices;
		}


        // CONFIGURATOR PRICE
        $priceDisplayPrecision = _PS_PRICE_DISPLAY_PRECISION_;
        if (!isset($priceDisplayPrecision)) {
            $priceDisplayPrecision = 2;
        }
        $priceDisplay = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
        $tax = false;
        if (!$priceDisplay || $priceDisplay == 2) {
            $tax = true;
        }

        $specific_price_output = null;
        $productUnitPrice = Product::getPriceStatic(
            (int)$this->configuratorCartDetail->product->id,
            $tax,
            null,
            $priceDisplayPrecision,
            null,
            false,
            true,
            1,
            false,
            null,
            null,
            null,
            $specific_price_output,
            true,
            true,
            null,
            true,
            $this->configuratorCartDetail->id_customization
        );
        $productUnitPriceWithoutReduction = Product::getPriceStatic(
            (int)$this->configuratorCartDetail->product->id,
            $tax,
            null,
            $priceDisplayPrecision,
            null,
            false,
            false,
            1,
            false,
            null,
            null,
            null,
            $specific_price_output,
            true,
            true,
            null,
            true,
            $this->configuratorCartDetail->id_customization
        );
        $productPriceTaxExcl = Product::getPriceStatic(
                (int)$this->configuratorCartDetail->product->id,
                false,
                null,
                $priceDisplayPrecision,
                null,
                false,
                true,
                $qty,
                false,
                null,
                null,
                null,
                $specific_price_output,
                true,
                true,
                null,
                true,
                $this->configuratorCartDetail->id_customization
            ) * $qty;
        $productPriceTaxIncl = Product::getPriceStatic(
                (int)$this->configuratorCartDetail->product->id,
                true,
                null,
                $priceDisplayPrecision,
                null,
                false,
                true,
                $qty,
                false,
                null,
                null,
                null,
                $specific_price_output,
                true,
                true,
                null,
                true,
                $this->configuratorCartDetail->id_customization
            ) * $qty;
        $productPriceWithoutReduction = Product::getPriceStatic(
                (int)$this->configuratorCartDetail->product->id,
                $tax,
                null,
                $priceDisplayPrecision,
                null,
                false,
                false,
                $qty,
                false,
                null,
                null,
                null,
                $specific_price_output,
                true,
                true,
                null,
                true,
                $this->configuratorCartDetail->id_customization
            ) * $qty;
        
        
        // Display Specifique price
        $context = Context::getContext();
        $specificPrice = SpecificPriceCore::getSpecificPrice(
                $this->configuratorCartDetail->product->id,
                $context->shop->id,
                $context->currency->id,
                $context->country->id,
                null,
                $qty
            );
        $displayReduction = null;
        if (isset($specificPrice['reduction_type']) && isset($specificPrice['reduction']) && $specificPrice['reduction_type'] === 'percentage') {
            $reduction = $specificPrice['reduction'];
            if (Tools::displayPrice($productPriceWithoutReduction * $reduction) === Tools::displayPrice($productPriceWithoutReduction - $productUnitPrice)) {
                $displayReduction = '-' . $reduction * 100 . '%';
            }
        }

        return array(
            'configurator' => $this->configurator,
            'content_only' => Tools::getValue('content_only', 0),
            'base_product' => $this->configurator_product,
            'productObject' => $this->product,
            'configuratorCartDetail' => $this->configuratorCartDetail,
            'product' => $this->configuratorCartDetail->product,
            'id_cart_detail' => $this->configuratorCartDetail->id,
            'qty' => $qty,
            'cartDetail' => $this->configuratorCartDetail->getDetail(),
            //'price' => $this->price,
            'productUnitPrice' => $productUnitPrice,
            'productUnitPriceWithoutReduction' => $productUnitPriceWithoutReduction,
            'productPrice' => ($tax) ? $productPriceTaxIncl : $productPriceTaxExcl,
            'productPriceTaxExcl' => $productPriceTaxExcl,
            'productPriceTaxIncl' => $productPriceTaxIncl,
            'productPriceWithoutReduction' => $productPriceWithoutReduction,
            'priceDisplay' => $priceDisplay,
            'priceDisplayPrecision' => $priceDisplayPrecision,
            'display_tax_label' => (bool)$this->context->country->display_tax_label,
            'tax_enabled' => Configuration::get('PS_TAX'),
            'update_cart' => $this->updateCart,
            'display_name' => (int)Configuration::get('CONFIGURATOR_NAME_STEPS'),
            'link' => Context::getContext()->link,
            'DISPLAY_PROGRESS' => (int)Configuration::get('CONFIGURATOR_PROGRESS_COMPENENT'),
            'nbTabsGroup' => (int)Tools::ceilf(
                count(ConfiguratorStepTabModel::getTabsByIdConfigurator($this->configurator->id))
            ),
            'disable_addtocart_btn' => (int)Configuration::get('CONFIGURATOR_DISABLE_ADDTOCART_BTN'),
            'progress_value' => $this->configuratorCartDetail->progress,
            'ajax' => $this->ajax,
			'currentGrpDisplayPrice' => $currentGroupDisplayPrice,
            'configuratorDisplayPrice' => Configuration::get('CONFIGURATOR_DISPLAY_PRICE'),
            'displayReduction' => $displayReduction
        );
    }

    public function checkRequiredSteps()
    {
        $required_steps = ConfiguratorStepAbstract::getRequiredSteps((int)$this->configurator->id);
        $detail = $this->configuratorCartDetail->getDetail();
        $result = true;
        /** @var ConfiguratorStepAbstract $step **/
        foreach ($required_steps as $step) {
            if ($step->ignored_if_empty) {
                // Ignorer la vérification des champs requis si l'étape ne possède pas d'options
                if (isset($detail[$step->id]['options']) && count($detail[$step->id]['options']) === 0) {
                    continue;
                }
            }

            if ((int)$step->displayed_by_yes && isset($detail[$step->id])
                && array_key_exists('yes_no_value', $detail[$step->id])
                && $detail[$step->id]['yes_no_value'] !== true
            ) {
                continue;
            }

            if ($this->configuratorCartDetail->stepCanBeAdded($step)) {
                $found_one_selected = null;
                Hook::exec('configuratorCheckRequiredStep', array(
                    'found_one_selected' => &$found_one_selected,
                    'configurator_step' => $step,
                    'configurator_cart_detail' => $this->configuratorCartDetail
                ));

                if ($found_one_selected === null) {
                    $found_one_selected = $step->use_input ? true : false;

                    if ($step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)
                        && $this->configuratorCartDetail->getNumberOfAttachments((int)$step->id) > 0
                    ) {
                        $found_one_selected = true;
                    } elseif (!$step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)) {
                        $optionsStep = ConfiguratorStepOptionAbstract::getByIdConfiguratorStep((int)$step->id);
                        foreach ($optionsStep as $option_step) {
                            if (!$step->use_input) {
                                $found_one_selected = $this->configuratorCartDetail->foundOption((int)$option_step->id)
                                    ? true
                                    : $found_one_selected;
                            } elseif ($step->use_input && $this->configuratorCartDetail->existOption((int)$option_step->id)) {
                                // vérifier si id_option est dans configurator_cart_detail
                                $found_one_selected = $found_one_selected
                                    && $this->configuratorCartDetail->foundOption((int)$option_step->id, false, true);
                            }
                        }
                    }
                }

                if (!$found_one_selected) {
                    $result = false;
                    $this->configuratorErrors[] = $step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)
                        ? sprintf(
                            $this->module->getErrorTranslation(Configurator::ERROR_STEP_FILEUPLOAD_REQUIRED),
                            $step->public_name
                        )
                        : sprintf(
                            $this->module->getErrorTranslation(Configurator::ERROR_STEP_REQUIRED),
                            $step->public_name
                        );
                }
                
                /**
                 * Error of minimum quantity for a step
                 */
                if (!$this->configuratorCartDetail->checkMinCurrentQty(
                    $step,
                    $this->configuratorCartDetail->getDetail()
                )) {
                    $result = false;
                    $this->configuratorErrors[] = sprintf(
                        $this->module->getErrorTranslation(Configurator::ERROR_MIN_REQUIRED),
                        $step->getMinQty($this->configuratorCartDetail),
                        $step->public_name
                    );
                }
                
                /**
                 * Error of minimum option selected for a step
                 */
                if ($step->min_options > $this->configuratorCartDetail->checkOptionSelected(
                    $step,
                    $this->configuratorCartDetail->getDetail()
                )) {
                    $result = false;
                    $this->configuratorErrors[] = sprintf(
                        $this->module->getErrorTranslation(Configurator::ERROR_MINOPTIONS_REACHED),
                        $step->min_options,
                        $step->public_name
                    );
                }
            }
        }
        return $result;
    }
    
    public function checkMinOptions()
    {
        $steps = ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->configurator->id);
        $result = true;
        
        foreach ($steps as $step) {
            if ($this->configuratorCartDetail->stepCanBeAdded($step)) {
                if ($step->min_options > $this->configuratorCartDetail->checkOptionSelected(
                    $step,
                    $this->configuratorCartDetail->getDetail()
                )) {
                    $result = false;
                    $this->configuratorErrors[] = sprintf(
                        $this->module->getErrorTranslation(Configurator::ERROR_MINOPTIONS_REACHED),
                        $step->min_options,
                        $step->public_name
                    );
                }
            }
        }
        return $result;
    }
    
    public function checkMinMaxQty()
    {
        $steps = ConfiguratorStepAbstract::getStepsByIdConfigurator((int)$this->configurator->id);
        $result = true;
       
        foreach ($steps as $step) {
            // Check min quantity
            if (!$this->configuratorCartDetail->checkMinCurrentQty(
                $step,
                $this->configuratorCartDetail->getDetail()
            )) {
                $result = false;
                $this->configuratorErrors[] = sprintf(
                    $this->module->getErrorTranslation(Configurator::ERROR_MIN_REQUIRED),
                    $step->min_qty,
                    $step->public_name
                );
            }

            // Check max quantity
            if (!$this->configuratorCartDetail->checkMaxCurrentQty(
                $step,
                $this->configuratorCartDetail->getDetail()
            )) {
                $result = false;
                $this->configuratorErrors[] = sprintf(
                    $this->module->getErrorTranslation(Configurator::ERROR_MAXQTY_REACHED),
                    ((int)$step->max_qty < 0) ? 0 : $step->getMaxQty($this->configuratorCartDetail),
                    $step->public_name
                );
            }

            // Check step quantity
            if (!$this->configuratorCartDetail->checkStepCurrentQty(
                $step,
                $this->configuratorCartDetail->getDetail()
            )) {
                $result = false;
                $this->configuratorErrors[] = sprintf(
                    $this->module->getErrorTranslation(Configurator::ERROR_STEPQTY_REACHED),
                    $step->step_qty,
                    $step->public_name
                );
            }
        }
        return $result;
    }
    
    public function checkPricelistValuesSteps()
    {
        $error = false;
        $detail = $this->configuratorCartDetail->getDetail();
        $return = true;

        foreach ($detail as $step_detail) {
            $step = ConfiguratorStepFactory::newObject((int)$step_detail['id'], (int)$this->context->language->id);
            if ($step->use_input || !empty($step->price_list)) {
                $required = $step->required;
                foreach ($step_detail['options'] as $option) {
                    if (!empty($option['value'])) {
                        /**
                         * Required devient vrai si on essai d'ajouter au panier
                         * une configuration avec la moitiée des mesures saisies ...
                         */
                        $required = true;
                    } else {
                        $error = true;
                    }
                }
                /**
                 * Si vraiment rien n'a été remplit et que l'étape
                 * n'est pas requise par défaut, on ne relève pas d'erreur
                 */
                if (!$required) {
                    $error = false;
                }

                if ($error) {
                    $this->configuratorErrors[] = sprintf(
                        $this->module->getErrorTranslation(Configurator::ERROR_PRICELIST_VALUES_FOR),
                        $step->public_name
                    );
                    $return = false;
                }
            }
        }

        return $return;
    }

    protected function createNewCartIfNotExists()
    {
        if (!Validate::isLoadedObject($this->context->cart)) {
            if (Context::getContext()->cookie->id_guest) {
                $guest = new Guest(Context::getContext()->cookie->id_guest);
                $this->context->cart->mobile_theme = $guest->mobile_theme;
            }
            $this->context->cart->add();
            if ($this->context->cart->id) {
                $this->context->cookie->id_cart = (int)$this->context->cart->id;
            }
        }
    }
    
    public function processSubmitCart()
    {
        if ($this->checkRequiredSteps()
            && $this->checkPricelistValuesSteps()
            && $this->checkMinMaxQty()
            && $this->checkMinOptions()
        ) {
            $this->createNewCartIfNotExists();

            $this->configuratorCartDetail->id_cart = $this->context->cart->id;
            $this->configuratorCartDetail->added_in_cart = 1;
            $this->configuratorCartDetail->visual_rendering = Tools::getValue('visual_rendering', '');
            $this->configuratorCartDetail->save();

            $customization = new Customization($this->configuratorCartDetail->id_customization);
            $customization->id_cart = $this->context->cart->id;
            $customization->save();

            $qty = (int)Tools::getValue('qty') - $customization->quantity;
            /**
             * @todo Vérifier le champ personnalisé pour la version 1.6
             */
            //$customization->quantity = (int)Tools::getValue('qty');
            //$customization->in_cart = 1;
            //$customization->save();

            $params = '';
            if (!$this->updateCart) {
                $params = 'add=1&id_product=' . (int)$this->configuratorCartDetail->product->id
                    . '&qty=' . (int)Tools::getValue('qty') . '&id_customization=' . $customization->id . '&';
            } else {
                if ($qty > 0) {
                    $params = 'add=1&id_product=' . (int)$this->configuratorCartDetail->product->id
                        . '&qty=' . (int)$qty . '&id_customization=' . $customization->id . '&';
                } elseif ($qty < 0) {
                    $params = 'add=1&id_product=' . (int)$this->configuratorCartDetail->product->id
                    . '&qty=' . (int)abs($qty) . '&op=down&id_customization=' . $customization->id . '&';
                } else {
                    $params = '';
                }
            }

			// for advancedquote module
			if (Module::isInstalled('advancedquote') && Tools::getValue('id_cart', false)) {
				$params .= '&id_cart=' . $this->context->cart->id;
			}

            // CONFIGURATOR HOOK
            Hook::exec('beforeModuleConfiguratorProcessSubmitCart', array(
                'controller' => $this
            ));
				Tools::redirectLink($this->context->link->getPageLink(
					'cart',
					true,
					null,
					$params . 'configurator=1&token=' . Tools::getToken(false),
					false
				));
			
        }else
        {
            // Permet de recalculer le min et le max en cas de mauvaise saisie du formulaire par le client.
            if (Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')
            ) {
                foreach ($this->configurator->steps as $step) {
                    foreach ($step->options as $configurator_step_option) {
                        $configurator_step_option->max_value = $this->configuratorCartDetail->getMaxValue($configurator_step_option);
                        $configurator_step_option->min_value = $this->configuratorCartDetail->getMinValue($configurator_step_option);
                    }
                }
            }
        }
    }

    /**
     *  Retrieves values from '$array' at '$key' and returns '$default' if it
     *  does not exist. (Same principle as Prestashop's Tools::getValue)
     * @param type $array Array in which we want to retrieve the data
     * @param type $key Key's index of array
     * @param type $default Default value in case $array[$key] does not exist
     * @return type mixed
     */
    private function getValue($array, $key, $default = false)
    {
        return (isset($array[$key])) ? $array[$key] : $default;
    }

    /**
     * Note:
     *      It would be interesting in a incoming refactoring to use $operation
     *      directly instead of using an other array.
     *      We would need to:
     *          - HARMONIZE variable's name in both FRONT and BACK end
     *          - use operation's array as a way to store operation
     */


    /**
     *  Extract information from current $operation and provides default
     *  value if needed
     * @param type $operation
     * @return array    Array containing values from current operation
     */
    private function extractBaseValues($operation)
    {
        $values = array();

        $values['step'] = ConfiguratorStepFactory::newObject(
            (int)$operation['step'],
            (int)$this->context->language->id
        );
        $id_configurator_step_option = $this->getValue($operation, 'option');
        $values['option'] = ConfiguratorStepOptionAbstract::getOption((int)$id_configurator_step_option);
        $values['value'] = $this->getValue($operation, 'value');
        $values['dimension'] = $this->getValue($operation, 'dimension');
        $values['option_qty_wanted'] = $this->getValue($operation, 'option_qty', 1);
        $values['dropzone_positions'] = $this->getValue($operation, 'dropzone_positions', null);

        return $values;
    }

    /**
     *  Handles an 'add' operation.
     * @param type $operation Current operation to handle
     */
    private function handleAdd($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        $option = $values['option'];
        $dropzone_positions = $values['dropzone_positions'];
        if (Validate::isLoadedObject($step) && Validate::isLoadedObject($option)) {
            $extra = array('selected' => true, 'multiple' => $step->multiple);

            if ($step->isValidPricelistValue((float)$values['value'], $values['dimension']) ||
                (!empty($values['value']) || is_numeric($values['value']))) {
                $extra['value'] = $values['value'];
            }

            if ($step->use_qty) {
                $extra['qty'] = $values['option_qty_wanted'];
            }

            $performAdd = true;
            if (!empty($extra['value']) && !empty($step->price_list)) {
                $tmp = $this->configuratorCartDetail->findPriceListValuesFromStepId((int)$step->id);
                $dim = ($values['dimension'] == 1) ? 'x' : 'y';
                $tmp[$dim] = $extra['value'];

                // check only if both value are set
                if ($this->configuratorCartDetail->priceListIsTwoDimension($step)
                    && !empty($tmp['x'])
                    && !empty($tmp['y'])
                ) {
                    $res = $this->configuratorCartDetail->getPriceListValue($step, $tmp['x'], $tmp['y']);

                    $performAdd = !is_null($res);
                }
            }

            if ($performAdd) {
                $this->configuratorCartDetail->addOption((int)$step->id, $option, $extra, true, $dropzone_positions);
            } else {
                $this->success = 0;
                $this->options_error[] = (int)$values['option']->id;
                $this->steps_errors[$step->id][] = $this->module->getErrorTranslation(
                    Configurator::ERROR_PRICELIST_VALUES
                );
            }
        }
    }

    /**
     * Handles 'remove' operation
     * @param array $operation Current operation to handle
     */
    private function handleRemove($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        $option = $values['option'];
        if (Validate::isLoadedObject($step) && Validate::isLoadedObject($option)) {
            $extra = array('selected' => true, 'multiple' => $step->multiple);
            if ($step->isValidPricelistValue((float)$values['value'], $values['dimension']) ||
                (!empty($values['value']) || is_numeric($values['value']))) {
                $extra['value'] = $values['value'];
            }
            if ($step->use_qty) {
                $extra['qty'] = $values['option_qty_wanted'];
            }
            $this->configuratorCartDetail->removeOption((int)$values['option']->id, false, $extra);
        }
    }

    /**
     * Handles 'resetStep' operation
     * @param array $operation Current operation to handle
     */
    private function handleResetStep($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        if (Validate::isLoadedObject($step)) {
            $step->options = $step->getOptions((int)$this->context->language->id);
            $this->configuratorCartDetail->removeStep((int)$step->id);
            $this->processStep($step);
        }
    }

    private function handleAddYesNo($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        if (Validate::isLoadedObject($step)) {
            $extras = array(
                'yes_no_value' => true
            );
            $step->options = $step->getOptions((int)$this->context->language->id);
            $this->configuratorCartDetail->removeStep((int)$step->id);
            $this->processStep($step, $extras);
        }
    }

    private function handleRemoveYesNo($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        if (Validate::isLoadedObject($step)) {
            $extras = array(
                'yes_no_value' => false
            );
            $step->options = $step->getOptions((int)$this->context->language->id);
            $this->configuratorCartDetail->removeStep((int)$step->id);
            $this->processStep($step, $extras);
        }
    }

    /**
     * Handles 'updateWantedQty' operation
     * @param array $operation Current operation to handle
     */
    private function handleUpdateWantedQty($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        $option = $values['option'];
        if (Validate::isLoadedObject($step) && Validate::isLoadedObject($option)) {
            $configurator_step_option = $values['option'];
            $option_qty_wanted = $values['option_qty_wanted'];
            $this->configuratorCartDetail->updateOptionQty($configurator_step_option->id, $option_qty_wanted);
        }
    }

    /*
     * When both values are set at the same time, we need to test if the new
     * coordinates are correct and then performs basic addOption
     * @param type $operation
     */
    private function handleAddPriceList($operation)
    {
        $values = $this->extractBaseValues($operation);
        $step = $values['step'];
        $extra = array('selected' => true, 'multiple' => $step->multiple);

        if (!empty($step->price_list) && $this->configuratorCartDetail->priceListIsTwoDimension($step)) {
            $valueDim1 = $this->getValue($operation, 'valueDim1', null);
            $optionDim1 = $this->getValue($operation, 'optionDim1', false);
            $valueDim2 = $this->getValue($operation, 'valueDim2', null);
            $optionDim2 = $this->getValue($operation, 'optionDim2', false);
            $performAdd = false;
            if (!is_null($valueDim1) && !is_null($valueDim2)) {
                $res = $this->configuratorCartDetail->getPriceListValue($step, $valueDim1, $valueDim2);
                $performAdd = !is_null($res);
            }

            // if values are correct, performs both add
            // otherwise, add error messages and quit
            if ($performAdd) {
                $extra['value'] = $valueDim1;
                $option = ConfiguratorStepOptionAbstract::getOption((int)$optionDim1, (int)$values['step']->id);
                $this->configuratorCartDetail->addOption((int)$step->id, $option, $extra, true);

                $extra['value'] = $valueDim2;
                $option = ConfiguratorStepOptionAbstract::getOption((int)$optionDim2, (int)$values['step']->id);
                $this->configuratorCartDetail->addOption((int)$step->id, $option, $extra, true);
            } else {
                $this->success = 0;
                $this->options_error[] = (int)$values['option']->id;
                $this->steps_errors[$step->id][] = $this->module->getErrorTranslation(
                    Configurator::ERROR_PRICELIST_VALUES
                );
            }
        }
    }

/*    private function handleVierwer2DDropzonePosition($operation)
    {
        $detail = $this->configuratorCartDetail->getDetail();
        //$detail[$operation['step']]['dropzone_position'] = $operation['positions'];

        foreach ($operation['positions'] as $position) {
            $detail[$operation['step']]['dropzone'][$position['position']]['option'] = $position['option'];
        }

        $this->configuratorCartDetail->setDetail($detail);
    }*/

    /**
     * Dispatch according to 'action' options
     * @param array $operation Current operation to handle
     */
    private function processOperation($operation)
    {
        // we need to do this each time in order to update all elements
        // concerning the product
        $detail = $this->configuratorCartDetail->getDetail();
        if (empty($detail)) {
            foreach ($this->configurator->steps as $step) {
                $this->processStep($step);
            }
        }

        switch ($operation['action']) {
            case 'add':
                $this->handleAdd($operation);
                break;
            case 'addPriceList':
                $this->handleAddPriceList($operation);
                break;
            case 'remove':
                $this->handleRemove($operation);
                break;
            case 'resetStep':
                $this->handleResetStep($operation);
                break;
            case 'addYesNo':
                $this->handleAddYesNo($operation);
                break;
            case 'removeYesNo':
                $this->handleRemoveYesNo($operation);
                break;
            case 'updateWantedQty':
                $this->handleUpdateWantedQty($operation);
                break;
            /*case 'vierwer2DDropzonePosition':
                $this->handleVierwer2DDropzonePosition($operation);
                break;*/
            default:
                // should never happen, unknown action
                break;
        }

        // we need to do this each time in order to update all elements
        // concerning the product
        $this->checkIntegrity();
        //$this->configuratorCartDetail->compute($this->configurator_product);
    }

    public function processCartDetail()
    {
        $detail = $this->configuratorCartDetail->getDetail();
        // If first time customer go into configurator
        if (empty($detail)) {
            foreach ($this->configurator->steps as $step) {
                $this->processStep($step);
            }
        }

        // AjaxProcess
        if ($this->ajax && Tools::isSubmit('submitUpdateOption')) {
            $operations = $this->verifyStepGroupSelection();
            $operations = $this->sortOperations($operations);
            foreach ($operations as $operation) {
                $this->processOperation($operation);
            }
            $this->configuratorCartDetail->compute($this->configurator_product);
        }

        Hook::exec(
            'actionConfiguratorCartDetailModifier',
            array(
                'configurator' => &$this->configurator,
                'configuratorCartDetail' => &$this->configuratorCartDetail
            )
        );

        //$this->checkIntegrity();
    }

    private function sortOperations($operations)
    {
        $vierwer2DDropzonePosition = array();
        $operations_ordered = array();
        foreach ($operations as $operation) {
            if ($operation['action'] === 'remove') {
                array_unshift($operations_ordered, $operation);
            } elseif ($operation['action'] === 'vierwer2DDropzonePosition') {
                array_push($vierwer2DDropzonePosition, $operation);
            } else {
                array_push($operations_ordered, $operation);
                if (isset($operation['option_qty'])) {
                    $step = ConfiguratorStepFactory::newObject((int)$operation['step']);
                    if ($step->dropzone) {
                        $operation['action'] = 'remove';
                        array_unshift($operations_ordered, $operation);
                    }
                }
            }
        }

        return array_merge($operations_ordered, $vierwer2DDropzonePosition);
    }

    private function verifyStepGroupSelection()
    {
        $operations = Tools::getValue('operations', array());
        $last_step_selected = $this->findLastStepSelected($operations);
        foreach ($operations as $key => $operation) {
            $id_configurator_step = (int)$operation['step'];
            if ($id_configurator_step != $last_step_selected) {
                $groups = ConfiguratorStepGroupModel::getLinkedStepById($id_configurator_step);
                if (is_array($groups) && count($groups) > 0 && in_array($last_step_selected, $groups)) {
                    $operations[$key]['action'] = 'remove';
                }
            }
        }
        return $operations;
    }

    private function findLastStepSelected($operations)
    {
        $detail = $this->configuratorCartDetail->getDetail();
        foreach ($operations as $operation) {
            if ($operation['action'] == 'add'
                && isset($detail[$operation['step']]['options'][$operation['option']]['selected'])
                && !$detail[$operation['step']]['options'][$operation['option']]['selected']
            ) {
                return (int)$operation['step'];
            } else {
                if ($operation['action'] == 'remove'
                    && isset($detail[$operation['step']]['options'][$operation['option']]['selected'])
                    && $detail[$operation['step']]['options'][$operation['option']]['selected']
                ) {
                    return (int)$operation['step'];
                }
            }
        }
        return 0;
    }

    public function processStep($step, $extras = array())
    {
        $detail = $this->configuratorCartDetail->getDetail();
        $existed = isset($detail[(int)$step->id]);
        if ($this->configuratorCartDetail->stepCanBeAdded($step)) {
            if (!$existed) {
                $this->configuratorCartDetail->addStep($step, $extras);
                $this->processAddOptions($step);
            }
            // If step exist but is not allowed to be added
        } elseif ($existed) {
            $this->configuratorCartDetail->removeStep((int)$step->id);
        }
    }

    public function processAddOptions($step)
    {
        // Attributs récupéré de l'URL (dans le cas des produits déclinés)
        $attributes = [];
        if (DMTools::getVersionMajor() >= 17 && $step->use_combination_as_default_value) {
            $attributes = isset($this->getTemplateVarProduct()['attributes']) ? $this->getTemplateVarProduct()['attributes'] : [];
        }

        $paramsURL = [];
        if ($step->use_url_as_default_value) {
            if (Tools::getValue('step') !== false) {
                foreach (Tools::getValue('step') as $stepId => $optionsId) {
                    $paramsURL[$stepId] = [];
                    if (is_array($optionsId)) {
                        foreach ($optionsId as $optionId) {
                            $paramsURL[$stepId][] = (int)$optionId;
                        }
                    } else {
                        $paramsURL[$stepId][] = (int)$optionsId;
                    }
                }
            }
        }

        foreach ($step->options as $option) {
            if (Validate::isLoadedObject($option)) {
                $selected_by_default = $option->selected_by_default;

                // Utilise les valeurs de la combinaison (déclinaisons) comme valeur par défaut
                if (!empty($attributes) && $step->type === ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES && isset($attributes[$step->id_option_group])) {
                    $selected_by_default = (bool)((int)$attributes[$step->id_option_group]['id_attribute'] === (int)$option->id_option);
                }

                // Utilise les paramètres de l'URL comme valeur par défaut
                if (!empty($paramsURL) && $step->type === ConfiguratorStepAbstract::TYPE_STEP_ATTRIBUTES && isset($paramsURL[$step->id])) {
                    $selected_by_default = in_array((int)$option->id, $paramsURL[$step->id]);
                }

                $this->configuratorCartDetail->addOption((int)$step->id, $option, array(
                    'multiple' => $step->multiple,
                    'selected' => $selected_by_default
                ));
            }
        }
    }

    public function checkIntegrity()
    {
        foreach ($this->configurator->steps as $step) {
            $this->processStep($step);
            foreach ($step->options as $configurator_step_option) {
                if (Validate::isLoadedObject($configurator_step_option)) {
                    $exist = $this->configuratorCartDetail->existOption((int)$configurator_step_option->id);
                    $canBeAdded = $this->configuratorCartDetail->optionCanBeAdded($configurator_step_option);
                    if ($canBeAdded && !$exist) {
                        $this->configuratorCartDetail->addOption(
                            (int)$step->id,
                            $configurator_step_option,
                            array('multiple' => $step->multiple)
                        );
                    } elseif (!$canBeAdded && $exist) {
                        // Remove option if not allowed to be added and found
                        $this->configuratorCartDetail->removeOption(
                            (int)$configurator_step_option->id,
                            true,
                            array('multiple' => $step->multiple)
                        );
                    }
                }
            }
        }
    }
}
