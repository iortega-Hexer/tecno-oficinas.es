<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ConfiguratorAttachment extends ObjectModel
{
    public $file;
    public $file_name;
    public $file_size;
    public $mime;
    public $token;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'configurator_attachment',
        'primary' => 'id_configurator_attachment',
        'fields' => array(
            'file' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 40
            ),
            'mime' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'required' => true,
                'size' => 128
            ),
            'token' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 50),
            'file_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128),
            'file_size' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId')
        ),
    );

    public function add($autodate = true, $null_values = false)
    {
        $this->file_size = filesize(_PS_DOWNLOAD_DIR_ . $this->file);
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        $this->file_size = filesize(_PS_DOWNLOAD_DIR_ . $this->file);
        return parent::update($null_values);
    }

    public function delete()
    {
        @unlink(_PS_DOWNLOAD_DIR_ . $this->file);
        Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'configurator_cartdetail_attachment'
            . ' WHERE id_configurator_attachment = ' . (int)$this->id
        );
        return parent::delete();
    }

    public function deleteSelection($attachments)
    {
        $return = 1;
        foreach ($attachments as $id_attachment) {
            $attachment = new ConfiguratorAttachment((int)$id_attachment);
            $return &= $attachment->delete();
        }
        return $return;
    }

    public static function getAttachments($id_configurator_cart_detail, $id_step = null, $include = true)
    {
        return Db::getInstance()->executeS(
            'SELECT *
			FROM ' . _DB_PREFIX_ . 'configurator_attachment a
			WHERE a.id_configurator_attachment ' . ($include ? 'IN' : 'NOT IN') . ' (
				SELECT pa.id_configurator_attachment
				FROM ' . _DB_PREFIX_ . 'configurator_cartdetail_attachment pa
				WHERE id_configurator_cart_detail = ' . (int)$id_configurator_cart_detail . '
                ' . (!is_null($id_step) ? ' AND id_step = ' . (int)$id_step : '') . '
			)'
        );
    }

    public static function getAttachmentByToken($token)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from('configurator_attachment')
            ->where('token = "' . $token . '"');

        $res = Db::getInstance()->getRow($query);
        $attachment = new ConfiguratorAttachment();

        if (!$res) {
            return $attachment;
        }

        $attachment->hydrate($res);

        return $attachment;
    }

    /**
     * associate $id_product to the current object.
     *
     * @param int $id_configurator_cart_detail id of the product to associate
     * @return bool true if succed
     */
    public function attachCartDetail($id_configurator_cart_detail, $id_step)
    {
        $res = Db::getInstance()->execute('
			INSERT INTO ' . _DB_PREFIX_ . 'configurator_cartdetail_attachment
				(id_configurator_attachment, id_configurator_cart_detail, id_step) VALUES
				(' . (int)$this->id . ', ' . (int)$id_configurator_cart_detail . ', ' . (int)$id_step . ')');

        return $res;
    }

    public static function getAttachmentByProduct($id_configurator_cart_detail)
    {
        $sql = 'SELECT a.* FROM ' . _DB_PREFIX_ . 'configurator_attachment a, `'
            . _DB_PREFIX_ . 'configurator_cartdetail_attachment` ca, `'
            . _DB_PREFIX_ . 'configurator_cart_detail` cd
            WHERE a.`id_configurator_attachment` = ca.`id_configurator_attachment`
            AND ca.`id_configurator_cart_detail` = cd.`id_configurator_cart_detail`
            AND ca.`id_configurator_cart_detail` = ' . (int)$id_configurator_cart_detail;
        return Db::getInstance()->executeS($sql);
    }
}
