<?php
/**
 * Powerful Form Generator
 *
 * This modules aims to provide for your customer any kind of form you want.
 *
 * If you find errors, bugs or if you want to share some improvments,
 * feel free to contact at contact@prestaddons.net ! :)
 * Si vous trouvez des erreurs, des bugs ou si vous souhaitez
 * tout simplement partager un conseil ou une amélioration,
 * n'hésitez pas à me contacter à contact@prestaddons.net
 *
 * @author    Cyril Nicodème <contact@prestaddons.net>
 * @copyright Copyright (C) April 2014 prestaddons.net <@email:contact@prestaddons.net>. All rights reserved.
 * @license   Nicodème Cyril
 * @package   modules
 * @since     2014-04-15
 * @version   2.7.9
 */

require_once(dirname(__FILE__).'//models/PFGModel.php');
require_once(dirname(__FILE__).'/models/PFGFieldModel.php');
require_once(dirname(__FILE__).'/models/PFGSubmissionModel.php');


class PFGRenderer
{
    private static $template_path;
    private static $moduleId = null;
    public static function setTemplatePath($template_path)
    {
        self::$template_path = $template_path;
    }

    public static function setModuleId($moduleId)
    {
        self::$moduleId = $moduleId;
    }

    private $form;
    private $fields;
    private $errors = array();

    /**
     * Create new instance of the PFGRenderer class
     * Verify if the given form id is valid
     *
     * @param int $id The form id
     */
    public function __construct($id, $processSubmit = true)
    {
        $this->form = new PFGModel($id);
        if (!$this->form->active) {
            throw new Exception('This form is not active.');
        }

        $this->fields = PFGFieldModel::findFields($this->form->id);
        if (count($this->fields) == 0) {
            throw new Exception('No fields available for this form.');
        }

        if ($processSubmit && Tools::isSubmit('submitMessage')) {
            $this->processSubmit();
        }
    }

    /**
     * Returns the current form
     *
     * @return Object
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Indicate if this form is allowed to be displayed
     * for the current user
     *
     * @param int $id Id of the form to check
     * @param boolean $checkUrl (default false) : Also check if only URL is allowed
     *
     * @return boolean
     */
    public function isAllowed($check_url = false)
    {
        if (((bool)$this->form->is_only_connected) && !Context::getContext()->customer->isLogged()) {
            return false;
        }

        if ($check_url) {
            if ($this->form->accessible === '2') {
                return false;
            }
        }

        return true;
    }

    public function getModuleId()
    {
        if (!is_null(self::$moduleId)) {
            return self::$moduleId;
        }

        return Module::getModuleIdByName('PowerfulFormGenerator');
    }

    /**
    * Generate and returns the form
    *
    * @return string
    */
    public function displayForm()
    {
        $form_fields = array();
        foreach ($this->fields as $field) {
            $type = 'text';
            $element = '';
            switch ($field['type']) {
                case 'datepicker': // Fall through intentional
                    $field['type'] = 'text';
                    if (!isset($field['classname'])) {
                        $field['classname'] = '';
                    }
                    if (strpos($field['classname'], 'pfg-datepicker-elements') === false) {
                        $field['classname'] .= ' pfg-datepicker-elements';
                    }
                case 'text': // Fall through intentional
                case 'number': // Fall through intentional
                case 'email': // Fall through intentional
                case 'url': // Fall through intentional
                case 'file': // Fall through intentional
                    $element = '<input type="'.$field['type'].'" ';
                    if (Tools::isSubmit($field['name'])) {
                        $element .= 'value="'.Tools::getValue($field['name']).'" ';
                    } elseif ($field['type'] === 'email') {
                        $id_customer = Context::getContext()->customer->id;
                        if (!empty($id_customer)) {
                            $element .= 'value="'.Context::getContext()->customer->email.'" ';
                        }
                    }

                    $element = $this->addAttributes($element, $field);
                    $element .= '/>';
                    break;
                case 'hidden':
                    $type = 'hidden';
                    $element = '<input type="'.$field['type'].'" ';
                    if (Tools::isSubmit($field['name'])) {
                        $element .= 'value="'.Tools::getValue($field['name']).'" ';
                    } else {
                        $element .= 'value="'.$field['values'].'" ';
                    }

                    $element = $this->addAttributes($element, $field);
                    $element .= '/>';
                    break;
                case 'textarea':
                    $type = $field['type'];
                    $element = '<textarea rows="15" cols="10" ';
                    $element = $this->addAttributes($element, $field);
                    $element .= '>';

                    $element .= Tools::getValue($field['name']);

                    $element .= '</textarea>';
                    break;
                case 'select':
                    $type = $field['type'];
                    $value_post = Tools::getValue($field['name']);

                    $element = '<select ';
                    $element = $this->addAttributes($element, $field);
                    $element .= '>';
                    if (!empty($field['values'])) {
                        $values = explode(',', $field['values']);
                        foreach ($values as $value) {
                            $value = trim($value);
                            if (strpos($value, '|') === false) {
                                $label = $value;
                            } else {
                                $parts = explode('|', $value);
                                $value = trim($parts[0]);
                                $label = trim($parts[1]);
                            }

                            $element .= '<option value="'.$value.'"';
                            if ($value_post === $value) {
                                $element .= ' selected';
                            }

                            $element .= '>'.$label.'</option>';
                        }
                    }
                    $element .= '</select>';
                    break;
                case 'radio':
                    $type = $field['type'];
                    $value_post = Tools::getValue($field['name']);

                    if (!empty($field['values'])) {
                        $values = explode(',', $field['values']);
                        $element = '<span>';
                        foreach ($values as $value) {
                            $value = trim($value);
                            if (Tools::substr(_PS_VERSION_, 0, 3) === '1.6') {
                                $element .= '<label class="checkbox">';
                            } else {
                                $element .= '<label class="input">';
                            }

                            $element .= '<input type="radio" value="'.$value.'" ';
                            if ($value_post === $value) {
                                $element .= 'checked ';
                            }

                            $element = $this->addAttributes($element, $field, false);
                            $element .= '/> '.$value.'</label>';
                        }
                        $element .= '</span>';
                    }

                    break;
                case 'checkbox':
                    $type = $field['type'];
                    if (Tools::substr(_PS_VERSION_, 0, 3) === '1.6') {
                        $element = '<label class="checkbox"><input type="checkbox" value="true" ';
                    } else {
                        $element = '<label class="input"><input type="checkbox" value="true" ';
                    }

                    if (Tools::getValue($field['name']) === 'true') {
                        $element .= 'checked ';
                    }

                    $element = $this->addAttributes($element, $field);
                    $element .= '/> '.$field['values'].'</label>';
                    break;
                case 'multicheckbox':
                    $type = $field['type'];
                    $values = explode(',', $field['values']);
                    $element = '<span>';
                    foreach ($values as $key => $value) {
                        if (Tools::substr(_PS_VERSION_, 0, 3) === '1.6') {
                            $element .= '<label class="checkbox"><input type="checkbox" value="'.$key.'" ';
                        } else {
                            $element .= '<label class="input"><input type="checkbox" value="'.$key.'" ';
                        }

                        if (Tools::getValue($field['name']) === 'true') {
                            $element .= 'checked ';
                        }

                        $element = $this->addAttributes($element, $field);
                        $element .= '/> '.$value.'</label>';
                    }
                    $element .= '</span>';

                    break;
                case 'captcha':
                    $element = '<label class="checkbox"><img src="'.__PS_BASE_URI__.
                                    'modules/powerfulformgenerator/controllers/front/captcha.php?t='.time().'" alt="Captcha value" /></label>';
                    $element .= '<input type="'.$field['type'].'" ';
                    $element = $this->addAttributes($element, $field);
                    $element .= '/>';
                    break;
                case 'recaptcha':
                    $element = '<script src="https://www.google.com/recaptcha/api.js" async defer></script><div class="g-recaptcha" data-sitekey="'.$this->form->recaptcha_public.'"></div>';
                    break;
                case 'separator':
                    $type = $field['type'];
                    $element = '<hr />';
                    break;
                case 'legend': // Fall through intentional
                case 'static': // Fall through intentional
                    $type = $field['type'];
                    $element = '<br />';
                    break;
            }

            if (empty($element)) {
                continue;
            }

            $form_fields[] = array (
                'type'      => $type,
                'name'      => $field['name'],
                'label'     => $field['label'],
                'value'     => $field['values'],
                'element'   => $element,
                'id'        => 'field_'.$field['name'],
                'classname' => $field['classname'],
                'required'  => ($field['required'] === '1') ? true : false
            );
        }

        $smarty_assign = array (
            'title'     => $this->form->title[Context::getContext()->language->id],
            'header'    => $this->form->header[Context::getContext()->language->id],
            'footer'    => $this->form->footer[Context::getContext()->language->id],
            'success'   => empty($this->form->success[Context::getContext()->language->id]) ? null : $this->form->success[Context::getContext()->language->id],
            'label_btn' => $this->form->send_label[Context::getContext()->language->id],
            'fields'    => $form_fields,
            'form_id'   => $this->form->id,
            'errors'    => $this->errors,
            'id_module' => $this->getModuleId()
        );

        if (Context::getContext()->controller instanceof PowerfulFormGeneratorDisplayModuleFrontController) {
            $smarty_assign['path'] = $this->form->title[Context::getContext()->language->id];
        }

        Context::getContext()->smarty->assign($smarty_assign);

        if (method_exists('Media', 'addJsDef')) {
            Media::addJsDef(array(
                'contact_fileDefaultHtml' => $this->l('No file selected'),
                'contact_fileButtonHtml'  => $this->l('Choose File'),
            ));
        }

        Module::getInstanceByName('powerfulformgenerator'); // Will set the template path
        return Context::getContext()->smarty->fetch(self::$template_path);
    }

    /**
     * Add attributes to the current field
     * Attributes like required, style, extra, etc
     *
     * @param string $element Current HTML structure of the element
     * @param array $field Field datas from the database
     * @param boolean $ignore_id Indicate if weither we add an ID or not to this field
     *
     * @return string
     */
    private function addAttributes($element, $field, $ignore_id = false)
    {
        //  f.name, f.required, f.style, f.extra
        if (strpos($field['extra'], 'multiple') !== false && Tools::substr($field['name'], -2) !== '[]') {
            $field['name'] .= '[]';
        } elseif ($field['type'] === 'multicheckbox' && Tools::substr($field['name'], -2) !== '[]') {
            $field['name'] .= '[]';
        }

        $element .= 'name="'.$field['name'].'" ';

        if (!$ignore_id) {
            $element .= 'id="field_'.$field['name'].'" ';
        }

        if ($field['required'] === '1' && $field['type'] !== 'multicheckbox') {
            $element .= 'required ';
        }

        if (Tools::substr(_PS_VERSION_, 0, 3) === '1.6' && $field['type'] !== 'file') {
            $element .= 'class="form-control "';
        }

        if (isset($field['style']) && !empty($field['style'])) {
            $element .= 'style="'.$field['style'].'" ';
        }
        if (isset($field['extra']) && !empty($field['extra'])) {
            $element .= ' '.$field['extra'].' ';
        }

        return $element;
    }

    /**
     * Little helper to translate strings in this class
     *
     * @param string $message The original message string
     * @param array $sprintf Possible variables to replace in the given $message
     *
     * @return string
     */
    private function l($message, $sprintf = array())
    {
        return Translate::getModuleTranslation('powerfulformgenerator', $message, __CLASS__, $sprintf);
    }

    /**
     * Process the submitted form
     */
    private function processSubmit()
    {
        if (((int)Tools::getValue('pfg_form_id')) !== ((int)$this->form->id)) {
            return;
        }

        $results = array ();
        $contains_files = false;
        $files = array ();

        $news_letter_optin = false;
        $senders_email = false;
        $admin_email = false;

        foreach ($this->fields as $field) {
            if ($field['type'] === 'legend') {
                continue;
            }

            $validated_field = $this->validateField($field);
            if (!is_null($validated_field)) {
                $results[$field['name']] = $validated_field;
            }

            if ($field['type'] === 'static') {
                $results[$field['name']] = $field['values'];
            }

            if ($field['type'] === 'file' && isset($results[$field['name']])) {
                $contains_files = true;

                if (!is_array($results[$field['name']])) {
                    $results[$field['name']] = array($results[$field['name']]);
                }

                foreach ($results[$field['name']] as $key => $value) {
                    if (is_null($value)) {
                        continue;
                    }

                    $field_name = $field['name'];
                    if (Tools::substr($field_name, -2) === '[]') {
                        $field_name = Tools::substr($field_name, 0, -2);
                    }

                    $files[] = array (
                        'old' => (is_array($_FILES[$field_name]['tmp_name']) ? $_FILES[$field_name]['tmp_name'][$key] : $_FILES[$field_name]['tmp_name']),
                        'new' => $results[$field['name']][$key]
                    );

                    $results[$field['name']][$key] = _PS_BASE_URL_.__PS_BASE_URI__.'upload/pfg/'.$results[$field['name']][$key];
                }
            }

            switch ($field['related']) {
                case 'destination':
                    $admin_email = Tools::getValue($field['name']);
                    break;
                case 'email':
                    $senders_email = Tools::getValue($field['name']);
                    break;
                case 'subject':
                    $sender_subject = Tools::getValue($field['name']);
                    $admin_subject = Tools::getValue($field['name']);
                    break;
                case 'newsletter':
                    $field_name = Tools::getValue($field['name']);
                    $news_letter_optin = !empty($field_name);
                    break;
            }
        }

        if (count($this->errors) > 0) {
            return;
        }

        // We replace the variables with their true values
        foreach ($this->fields as $field) {
            $replace_value = null;
            if (!isset($results[$field['name']]) || empty($results[$field['name']])) {
                $replace_value = '';
            } else {
                if ($field['type'] === 'multicheckbox') {
                    $multi_values = explode(',', $field['values']);
                    $tmp_values = array();
                    foreach ($results[$field['name']] as $element) {
                        $tmp_values[] = $multi_values[(int)$element];
                    }

                    $replace_value = implode(', ', $tmp_values);
                } else if (is_array($results[$field['name']])) {
                    $replace_value = implode(', ', $results[$field['name']]);
                } else {
                    $replace_value = $results[$field['name']];
                }
            }

            $this->form->subject_sender[Context::getContext()->language->id] = str_replace('{$'.$field['name'].'}', $replace_value, $this->form->subject_sender[Context::getContext()->language->id]);

            $this->form->subject_admin[Context::getContext()->language->id] = str_replace('{$'.$field['name'].'}', $replace_value, $this->form->subject_admin[Context::getContext()->language->id]);

            $this->form->success[Context::getContext()->language->id] = str_replace('{$'.$field['name'].'}', $replace_value, $this->form->success[Context::getContext()->language->id]);

            $this->form->message_admin[Context::getContext()->language->id] = str_replace('{$'.$field['name'].'}', $replace_value, $this->form->message_admin[Context::getContext()->language->id]);

            $this->form->message_sender[Context::getContext()->language->id] = str_replace('{$'.$field['name'].'}', $replace_value, $this->form->message_sender[Context::getContext()->language->id]);
        }

        if (empty($sender_subject)) {
            $sender_subject = (empty($this->form->subject_sender[Context::getContext()->language->id]) ? $this->l('No subject') : $this->form->subject_sender[Context::getContext()->language->id]);
        }

        if (empty($admin_subject)) {
            $admin_subject = (empty($this->form->subject_admin[Context::getContext()->language->id]) ? $this->l('No subject') : $this->form->subject_admin[Context::getContext()->language->id]);
        }

        if ($contains_files) {
            $destination_directory = _PS_ROOT_DIR_.'/upload/pfg/';
            if (!file_exists($destination_directory)) {
                mkdir($destination_directory, 0777, true);
            }

            foreach ($files as $file) {
                rename($file['old'], $destination_directory.$file['new']);
                chmod($destination_directory.$file['new'], 0644);
            }
        }

        // Subscribing to the newsletter
        if ($news_letter_optin && !empty($senders_email)) {
            if (count(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'newsletter";')) > 0) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.
                    'newsletter` (`id_shop`, `id_shop_group`, `email`, `newsletter_date_add`, `ip_registration_newsletter`, `active`)
                    VALUES ('.pSQL((int)Context::getContext()->shop->id).', '.pSQL((int)Context::getContext()->shop->id_shop_group).', "'.
                    pSQL($senders_email).'", NOW(), "'.pSQL($_SERVER['REMOTE_ADDR']).'", 1)'
                );
            }

            Db::getInstance()->execute('UPDATE IGNORE `'._DB_PREFIX_.'customer` SET newsletter = 1 WHERE email = "'.pSQL($senders_email).'" LIMIT 1;');
        }

        $id_customer = Context::getContext()->customer->id;
        if (!empty($id_customer)) {
            $customer = Context::getContext()->customer;

            $results['_customer'] = array (
                'id' => $id_customer,
                'display' => $customer->firstname.' '.$customer->lastname,
            );
        }

        if (Context::getContext()->controller instanceof ProductController && Context::getContext()->controller->getProduct()) {
            $results['_product'] = array (
                'id' => Context::getContext()->controller->getProduct()->id,
                'display' => Context::getContext()->controller->getProduct()->name,
                'url' => Context::getContext()->link->getProductLink(Context::getContext()->controller->getProduct())
            );
        }

        $admin_message = null;
        switch ($this->form->action_admin) {
            case 'message':
                $admin_message = array (
                    '{message_txt}'  => strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $this->form->message_admin[Context::getContext()->language->id])),
                    '{message_html}' => $this->form->message_admin[Context::getContext()->language->id]
                );
                break;
            case 'form':
                $admin_message = $this->generateMessageFromResults($results);
                break;
        }

        $this->createMailFolder(Context::getContext()->language->iso_code);

        // We save before sending emails
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'pfg_submissions` (`id_pfg`, `entry`, `date_add`) VALUES ('.
            pSQL((int)$this->form->id).', "'.pSQL(Tools::jsonEncode($results)).'", NOW());'
        );

        if ($admin_message) {
            $email_admins = false;
            if ($admin_email) {
                if (!empty($senders_email) && version_compare(Tools::substr(_PS_VERSION_, 0, 5), '1.6.1', '>=')) {
                    $email_admins = Mail::Send((int)Context::getContext()->language->id, 'message', $admin_subject, $admin_message, $admin_email, null, null, null, null, null, dirname(__FILE__).'/../mails/', false, null, null, $senders_email);
                } else {
                    $email_admins = Mail::Send((int)Context::getContext()->language->id, 'message', $admin_subject, $admin_message, $admin_email, null, null, null, null, null, dirname(__FILE__).'/../mails/');
                }
            } else {
                $emails = array_map('trim', explode(',', $this->form->send_mail_to));
                foreach ($emails as $email) {
                    $email_admins = false;
                    if (!empty($senders_email) && version_compare(Tools::substr(_PS_VERSION_, 0, 5), '1.6.1', '>=')) {
                        $email_admins = Mail::Send((int)Context::getContext()->language->id, 'message', $admin_subject, $admin_message, $email, null, null, null, null, null, dirname(__FILE__).'/../mails/', false, null, null, $senders_email);
                    } else {
                        $email_admins = Mail::Send((int)Context::getContext()->language->id, 'message', $admin_subject, $admin_message, $email, null, null, null, null, null, dirname(__FILE__).'/../mails/');
                    }

                    if (!$email_admins) {
                        break;
                    }
                }
            }

            if (!$email_admins) {
                $this->errors[] = $this->l('An error occured while sending the email.');
                return;
            }
        }

        if ($senders_email) {
            $sender_message = null;
            switch ($this->form->action_sender) {
                case 'message':
                    $sender_message = array (
                        '{message_txt}'  => strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $this->form->message_sender[Context::getContext()->language->id])),
                        '{message_html}' => $this->form->message_sender[Context::getContext()->language->id]
                    );
                    break;
                case 'form':
                    $sender_message = $this->generateMessageFromResults($results);
                    break;
            }

            if ($sender_message) {
                if (!Mail::Send((int)Context::getContext()->language->id, 'message', $sender_subject, $sender_message, $senders_email, null, null, null, null, null, dirname(__FILE__).'/../mails/', false)) {
                    $this->errors[] = $this->l('An error occured while sending the email.');
                    return;
                }
            }
        }

        $success_url = $this->form->success[Context::getContext()->language->id];
        if (!empty($success_url) && Tools::substr($success_url, 0, 4) === 'http') {
            Tools::redirect($success_url);
        } else {
            Context::getContext()->smarty->assign('confirmation', 1);
        }
    }

    /**
     * Validate the field agains't the various restrictions
     * Like required, type (email, select), etc
     *
     * @param array $field Field data from the database
     *
     * @return mixed Value of the field(s) or null if a validation error occured.
     */
    private function validateField($field)
    {
        if (Tools::substr($field['name'], -2) === '[]') {
            $value = Tools::getValue(Tools::substr($field['name'], 0, -2));
        } else {
            $value = Tools::getValue($field['name']);
        }

        if ($field['required'] === '1' && empty($value) && !in_array($field['type'], array('file', 'recaptcha'))) {
            $this->errors[] = $this->l('The field %s is required.', array ($field['label']));
        }

        if (empty($value) && !in_array($field['type'], array('file', 'recaptcha'))) {
            return null;
        }

        if ($field['type'] === 'number') {
            if (!is_numeric($value)) {
                $this->errors[] = $this->l('The field %s must be a valid number.', array ($field['label']));
            }
        } elseif ($field['type'] === 'email') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = $this->l('The field %s must be a valid email.', array ($field['label']));
            }
        } elseif ($field['type'] === 'url') {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $this->errors[] = $this->l('The field %s must be a valid URL.', array ($field['label']));
            }
        } elseif (in_array($field['type'], array('select', 'radio'))) {
            $values = array_map('trim', explode(',', $field['values']));
            if (!empty($value)) {
                if (is_array($value)) {
                    foreach ($value as $select_val) {
                        $select_val = Tools::htmlentitiesDecodeUTF8($select_val);

                        if (!in_array($select_val, $values)) {
                            $this->errors[] = $this->l('Invalid entry given for %s.', array ($field['label']));
                        }
                    }
                } else {
                    $value = Tools::htmlentitiesDecodeUTF8($value);
                    $tmpValues = array();
                    foreach ($values as $val) {
                        if (strpos($val, '|') !== false) {
                            $parts = explode('|', $val);
                            $val = trim($parts[0]);
                        }
                        $tmpValues[] = $val;
                    }
                    $values = $tmpValues;
                    unset($tmpValues);

                    if (!in_array($value, $values)) {
                        $this->errors[] = $this->l('Invalid entry given for %s.', array ($field['label']));
                    }
                }
            }
        } elseif ($field['type'] === 'multicheckbox') {
            if ($field['required'] === '1' && (empty($value) || count($value) === 0)) {
                $this->errors[] = $this->l('The field %s is required.', array ($field['label']));
                return;
            }
        } elseif ($field['type'] === 'file') {
            // Tools is buggy for files

            $field_name = $field['name'];
            $is_multiple = false;
            if (Tools::substr($field_name, -2) === '[]') {
                $is_multiple = true;
                $field_name = Tools::substr($field_name, 0, -2);
            }

            if ($field['required'] === '1') {
                if ($is_multiple) {
                    if (empty($_FILES[$field_name][0]['name'])) {
                        $this->errors[] = $this->l('The field %s is required.', array ($field['label']));
                        return;
                    }
                } else {
                    if (empty($_FILES[$field_name]['name'])) {
                        $this->errors[] = $this->l('The field %s is required.', array ($field['label']));
                        return;
                    }
                }
            }

            $values = array_map('trim', explode(',', $field['values']));

            if (count($_FILES[$field_name]['name']) === 1) {
                if (empty($_FILES[$field_name]['name'])) {
                    return;
                }

                $extension = pathinfo('./'.$_FILES[$field_name]['name'], PATHINFO_EXTENSION);

                if (!in_array($extension, $values)) {
                    $this->errors[] = $this->l('Invalid file format given for %s (Only %s allowed).', array ($field['label'], $field['values']));
                }

                return uniqid().'.'.$extension;
            } elseif (count($_FILES[$field_name]['name']) > 1) {
                $files_results = array();

                foreach ($_FILES[$field_name]['name'] as $key => $filename) {
                    if (empty($_FILES[$field_name]['name'][$key])) {
                        continue;
                    }
                    $extension = pathinfo('./'.$_FILES[$field_name]['name'][$key], PATHINFO_EXTENSION);

                    if (!in_array($extension, $values)) {
                        $this->errors[] = $this->l('Invalid file format given for %s (Only %s allowed).', array ($field['label'], $field['values']));
                    }

                    $files_results[$key] = uniqid().'.'.$extension;
                }

                return $files_results;
            }
        } elseif ($field['type'] === 'captcha') {
            if ($value !== Context::getContext()->cookie->pfg_captcha_string) {
                $this->errors[] = $this->l('Invalid captcha value.', array ($field['label']));
            }

            unset(Context::getContext()->cookie->pfg_captcha_string);
            return null;
        } elseif ($field['type'] === 'recaptcha') {
            $google_result = Tools::getValue('g-recaptcha-response');
            if (empty($google_result)) {
                $this->errors[] = $this->l('Invalid captcha value.');
                return null;
            }

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array (
                    'secret' => $this->form->recaptcha_private,
                    'response' => $google_result,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3
            ));

            //execute post
            $result = Tools::jsonDecode(curl_exec($ch), true);
            curl_close($ch);

            if (is_null($result) || $result['success'] === false) {
                $this->errors[] = $this->l('Invalid captcha value.');
            }
        }

        return $value;
    }

    /**
     * Generate the message based on the form data
     * This will be used in the back office (when displaying an entry)
     *     and for the email sent.
     *
     * @param array $results An associative array of field_name => value
     *
     * @return array The formatted text, in txt and html format
     */
    private function generateMessageFromResults($results)
    {
        $message_txt = '';
        $message_html = '';
        foreach ($this->fields as $field) {
            if (!isset($results[$field['name']])) {
                continue;
            }
            if ($field['type'] === 'separator' || $field['type'] === 'captcha') {
                continue;
            }

            $value = $results[$field['name']];

            if ('true' === $value) {
                $value = $this->l('Yes');
            }
            if ('false' === $value) {
                $value = $this->l('No');
            }

            $message_txt .= ' * '.$field['label'].' : ';
            $message_html .= '<strong>'.$field['label'].'</strong> : ';

            if (is_array($value)) {
                if ($field['type'] === 'multicheckbox') {
                    $multi_values = explode(',', $field['values']);
                    $tmp_values = array();
                    foreach ($value as $element) {
                        $tmp_values[] = $multi_values[(int)$element];
                    }

                    $value = $tmp_values;
                }

                $message_txt .= implode(', ', $value)."\n";

                $message_html .= '<ul><li>';
                $message_html .= implode('</li><li>', $value);
                $message_html .= '</li></ul>'."\n";
            } else {
                $message_txt .= $value."\n";
                $message_html .= $value.'<br />';
            }
        }

        if (isset($results['_customer'])) {
            $message_txt .= ' * Customer informations : '.$results['_customer']['display'].' (ID: '.$results['_customer']['id'].')'."\n";
            $message_html .= '<strong>Customer informations</strong> : '.$results['_customer']['display'].' (ID: '.$results['_customer']['id'].')<br />';
        }

        if (isset($results['_product'])) {
            $message_txt .= ' * Product informations : '.$results['_product']['url']."\n";
            $message_html .= '<strong>Product informations</strong> : <a href="'.$results['_product']['url'].'">'.$results['_product']['display'].'</a><br />';
        }

        return array (
            '{message_txt}'  => $message_txt,
            '{message_html}' => $message_html
        );
    }

    /**
     * In case the current language is not one pre-created
     * We create it on the fly to enable email sending.
     *
     * @param string $language_code Language code of the email
     */
    private function createMailFolder($language_code)
    {
        $mail_dir = dirname(__FILE__).'/../mails/';
        if (file_exists($mail_dir.$language_code)) {
            return true;
        }

        mkdir($mail_dir.$language_code);
        Tools::copy($mail_dir.'orig/index.php', $mail_dir.Tools::strtolower($language_code).'/index.php');
        Tools::copy($mail_dir.'orig/message.html', $mail_dir.Tools::strtolower($language_code).'/message.html');
        Tools::copy($mail_dir.'orig/message.txt', $mail_dir.Tools::strtolower($language_code).'/message.txt');
    }
}
