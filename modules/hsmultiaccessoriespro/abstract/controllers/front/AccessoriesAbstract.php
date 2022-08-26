<?php
/**
 * Multi Accessories Pro | An abstract controller for front end
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AccessoriesAbstract extends ModuleFrontController
{
    public $errors = array();

    /**
     * Get accessories of list products.
     */
    public function displayAjaxRenderAccessories()
    {
        $params = array();
        $params['id_products'] = Tools::getValue('id_products');
        $params['id_shop'] = $this->context->shop->id;
        echo $this->module->renderAccessories($params);
    }
    
    /**
     * [Fixed] Error 500 from Google Search Console
     */
    public function initContent()
    {
        parent::initContent();
        if (!Tools::getValue('ajax')) {
            exit();
        }
    }

    /**
     * Get accessories of list products.
     */
    public function displayAjaxIsStockAvailable()
    {
        $success = array(
            'hasError' => false,
            'errors' => $this->errors
        );
        $id_accessory = (int) Tools::getValue('id_accessory');
        $id_product_attribute = (int) Tools::getValue('id_accessory_combination', 0);
        $qty_to_check = (int) Tools::getValue('new_quantity', 0);
        $product = new Product($id_accessory, true, $this->context->language->id);
        $cart_products = $this->context->cart->getProducts();

        if (is_array($cart_products)) {
            foreach ($cart_products as $cart_product) {
                if ((!isset($id_product_attribute) || $cart_product['id_product_attribute'] == $id_product_attribute) &&
                        (isset($id_accessory) && $cart_product['id_product'] == $id_accessory)) {
                    $qty_to_check += $cart_product['cart_quantity'];
                    break;
                }
            }
        }
        // Check product quantity availability
        if ($id_product_attribute) {
            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($id_product_attribute, $qty_to_check)) {
                $this->errors[] = $this->module->i18n['there_isnt_enough_product_in_stock'];
            }
        } elseif ($product->hasAttributes()) {
            $minimumQuantity = ($product->out_of_stock == 2) ? !Configuration::get('PS_ORDER_OUT_OF_STOCK') : !$product->out_of_stock;
            $id_product_attribute = Product::getDefaultAttribute($product->id, $minimumQuantity);
            if (!$id_product_attribute) {
                Tools::redirectAdmin($this->context->link->getProductLink($product));
            } elseif (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($id_product_attribute, $qty_to_check)) {
                $this->errors[] = $this->module->i18n['there_isnt_enough_product_in_stock'];
            }
        } elseif (!$product->checkQty($qty_to_check)) {
            $this->errors[] = $this->module->i18n['there_isnt_enough_product_in_stock'];
        }

        if ($this->errors) {
            $success = array(
                'hasError' => true,
                'errors' => $this->errors
            );
        }
        if ($this->module->isPrestashop15()) {
            die(Tools::jsonEncode($success));
        } else {
            $this->ajaxDie(Tools::jsonEncode($success));
        }
    }
    
    /**
     * Get accessories of list products.
     */
    public function displayAjaxGetProductCombination()
    {
        $params = array();
        $params['id_products'] = Tools::getValue('id_products');
        $params['id_shop'] = $this->context->shop->id;
        echo $this->module->renderAccessories($params);
    }
    
    /**
     * Add accessory customization from popup
     */
    public function displayAjaxAddAccessoryCustomization()
    {
        $id_product = (int) Tools::getValue('id_product');
        $product = new Product($id_product, true, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (Validate::isLoadedObject($product)) {
            if (!$this->context->cart->id && isset($_COOKIE[$this->context->cookie->getName()])) {
                $this->context->cart->add();
                $this->context->cookie->id_cart = (int) $this->context->cart->id;
            }
            $this->pictureUpload($product);
            $this->textRecord($product);
        } else {
            $this->errors[] = Tools::displayError('Accessory not found');
        }
        if ($this->errors) {
            $success = array(
                'success' => false,
                'errors' => $this->errors
            );
        } else {
            $data_customization = $this->proccessAssignAccessoryCustomizations($product);
            $success = array(
                'success' => true,
                'errors' => $this->errors,
                'customizations' => $data_customization['customizations'],
                'is_enough_customization' => (int) $data_customization['is_enough_customization'],
                'id_customization' => (int) $data_customization['id_customization'],
                
            );
        }
        if ($this->module->isPrestashop15()) {
            die(Tools::jsonEncode($success));
        } else {
            $this->ajaxDie(Tools::jsonEncode($success));
        }
    }
    
    protected function proccessAssignAccessoryCustomizations($object_product)
    {
        $product = get_object_vars($object_product);
        $product['id_product'] = $object_product->id;
        $product['customization'] = $object_product->customizable ? $object_product->getCustomizationFields($this->context->language->id) : false;
        $product['customizable'] = $object_product->customizable;
        return $this->module->getCustomizationData($product);
    }

    /**
     * Upload and add picture customization
     * @param objec $product
     * @return boolean
     */
    protected function pictureUpload($product)
    {
        if (!$field_ids = $product->getCustomizationFieldIds()) {
            return false;
        }
        $authorized_file_fields = array();
        foreach ($field_ids as $field_id) {
            if ($field_id['type'] == Product::CUSTOMIZE_FILE) {
                $authorized_file_fields[(int)$field_id['id_customization_field']] = 'file'.(int)$field_id['id_customization_field'];
            }
        }
        $indexes = array_flip($authorized_file_fields);
        foreach ($_FILES as $field_name => $file) {
            if (in_array($field_name, $authorized_file_fields) && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $file_name = md5(uniqid(rand(), true));
                if ($error = ImageManager::validateUpload($file, (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))) {
                    $this->errors[] = $error;
                }

                $product_picture_width = (int)Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                $product_picture_height = (int)Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                if ($error || (!$tmp_name || !move_uploaded_file($file['tmp_name'], $tmp_name))) {
                    return false;
                }
                /* Original file */
                if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_.$file_name)) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } elseif (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_.$file_name.'_small', $product_picture_width, $product_picture_height)) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } elseif (!chmod(_PS_UPLOAD_DIR_.$file_name, 0777) || !chmod(_PS_UPLOAD_DIR_.$file_name.'_small', 0777)) {
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                } else {
                    $this->context->cart->addPictureToProduct($product->id, $indexes[$field_name], Product::CUSTOMIZE_FILE, $file_name);
                }
                unlink($tmp_name);
            }
        }
        return true;
    }

    /**
     * Add text customization
     * @param object $product
     * @return boolean
     */
    protected function textRecord($product)
    {
        if (!$field_ids = $product->getCustomizationFieldIds()) {
            return false;
        }
        $authorized_text_fields = array();
        foreach ($field_ids as $field_id) {
            if ($field_id['type'] == Product::CUSTOMIZE_TEXTFIELD) {
                $authorized_text_fields[(int) $field_id['id_customization_field']] = 'textField' . (int) $field_id['id_customization_field'];
            }
        }
        $indexes = array_flip($authorized_text_fields);
        foreach ($_POST as $field_name => $value) {
            if (in_array($field_name, $authorized_text_fields) && $value != '') {
                if (!Validate::isMessage($value)) {
                    $this->errors[] = Tools::displayError('Invalid message');
                } else {
                    $this->context->cart->addTextFieldToProduct((int) $product->id, $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value);
                }
            } elseif (in_array($field_name, $authorized_text_fields) && $value == '') {
                $this->context->cart->deleteCustomizationToProduct((int) $product->id, $indexes[$field_name]);
            }
        }
    }

    /**
     * Delete accessory customization image
     */
    public function displayAjaxDeleteCustomizationImage()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_customization_field = (int) Tools::getValue('id_customization_field');
        $product = new Product($id_product, true, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (!$this->context->cart->deleteCustomizationToProduct($id_product, $id_customization_field)) {
            $this->errors[] = Tools::displayError('An error occurred while deleting the selected picture.');
        }
        if ($this->errors) {
            $success = array(
                'success' => false,
                'errors' => $this->errors
            );
        } else {
            $data_customization = $this->proccessAssignAccessoryCustomizations($product);
            $success = array(
                'success' => true,
                'errors' => $this->errors,
                'customizations' => $data_customization['customizations'],
                'is_enough_customization' => (int) $data_customization['is_enough_customization'],
                'id_customization' => (int) $data_customization['id_customization'],
            );
        }
        if ($this->module->isPrestashop15()) {
            die(Tools::jsonEncode($success));
        } else {
            $this->ajaxDie(Tools::jsonEncode($success));
        }
    }
    
    /**
     * Show package content
     */
    public function displayAjaxDisplayPackageContent()
    {
        $id_accessory = (int) Tools::getValue('id_accessory');
        $qty = (int) Tools::getValue('mQty');
        $product = new Product($id_accessory, false, (int) $this->context->language->id, (int) $this->context->shop->id);
        if (Validate::isLoadedObject($product)) {
            $this->context->smarty->assign(array(
                'hsma_pack_items' => Pack::getItemTable($id_accessory, $this->context->language->id, true),
                'link' => $this->context->link,
                'qty' => $qty,
            ));
            $success = array(
                'hasError' => false,
                'pack_title' => $this->module->i18n['this_pack_contains'],
                'pack_content' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/abstract/views/templates/hook/display_pack_product.tpl'),
            );
        } else {
            $success = array(
                'hasError' => true,
                'errors' => $this->module->i18n['accessory_not_found'],
            );
        }
        if ($this->module->isPrestashop15()) {
            die(Tools::jsonEncode($success));
        } else {
            $this->ajaxDie(Tools::jsonEncode($success));
        }
    }
}
