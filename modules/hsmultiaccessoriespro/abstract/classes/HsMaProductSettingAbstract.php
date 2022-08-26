<?php
/**
 * Product setting abstract of this module
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaProductSettingAbstract extends ObjectModel
{
    /**
     * Buy product & accessory together is not required.
     *
     * @var int
     */
    const BUY_TOGETHER_NO = 0;

    /*
     * Buy product & accessory together is required
     * @var int
     */
    const BUY_TOGETHER_YES = 1;

    /*
     * Use global setting
     * In this case buy together will be equal 0 or 1.
     * @var int
     */
    const BUY_TOGETHER_USE_DEFAULT = 2;

    /**
     * Admin will be choose accessories which are buy together with main product.
     *
     * @var int
     */
    const BUY_TOGETHER_REQUIRED = 3;

    /**
     * Primary key of table.
     *
     * @var int
     */
    public $id_accessory_product_setting;

    /**
     * Id of product.
     *
     * @var int
     */
    public $id_product;

    /**
     * Custom display name.
     *
     * @var bool
     *           )
     */
    public $custom_displayed_name;

    /**
     * Store id accessory product setting.
     *
     * @var array
     *            array (
     *            [int] => int // [id_product] => $id_accessory_product_setting
     *            )
     */
    protected static $cache_id_accessory_product_settings = array();

    /**
     * Config setting buy main product & accessory together
     * 0: No
     * 1: Yes
     * 2: use default global setting
     * 3: required.
     *
     * @var int
     */
    public $buy_together;

    /**
     * define all fields of table accessory product setting.
     *
     * @var array
     */
    public static $definition = array(
        'table' => 'accessory_product_setting',
        'primary' => 'id_accessory_product_setting',
        'multilang' => false,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'buy_together' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'custom_displayed_name' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );

    /**
     * Constructor.
     *
     * @param int $id_product
     */
    public function __construct($id_product)
    {
        $id_accessory_product_setting = self::getId($id_product);
        parent::__construct($id_accessory_product_setting);
    }

    /**
     * Get id accessory product setting.
     *
     * @param int $id_product
     *
     * @return int
     */
    protected static function getId($id_product)
    {
        if (!isset(self::$cache_id_accessory_product_settings[$id_product])) {
            $sql = 'SELECT `id_accessory_product_setting` FROM `'._DB_PREFIX_.'accessory_product_setting` WHERE `id_product` = '.(int) $id_product;
            self::$cache_id_accessory_product_settings[$id_product] = (int) Db::getInstance()->getValue($sql);
        }

        return self::$cache_id_accessory_product_settings[$id_product];
    }

    /**
     * Get current value of product setting buy product & accessory together at front end.
     * @param int $id_product
     * @return array
     */
    public static function getBuyTogetherCurrentValue($id_product)
    {
        $groups_settings = HsAccessoriesGroupAbstract::getSettingBuyTogethers(true);
        $buy_together = array();
        if (!empty($groups_settings)) {
            $product_setting = new HsMaProductSetting((int) $id_product);
            $buy_together_group = array();
            if (Validate::isLoadedObject($product_setting)) {
                /* Main product has buy together setting*/
                $main_product_buy_together =  (int)$product_setting->buy_together;
                if ($main_product_buy_together === (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT) {
                    foreach ($groups_settings as $group_setting) {
                        $buy_together_group[$group_setting['id_accessory_group']] = (int) $group_setting['buy_together'] === (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT ? (int) Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER') : (int) $group_setting['buy_together'];
                    }
                } else {
                    foreach ($groups_settings as $group_setting) {
                        $buy_together_group[$group_setting['id_accessory_group']] = $main_product_buy_together;
                    }
                }
            } else {
                /* Main product hasn't buy together setting*/
                foreach ($groups_settings as $group_setting) {
                    $buy_together_group[$group_setting['id_accessory_group']] = (int) $group_setting['buy_together'] === (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT ? (int) Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER') : (int) $group_setting['buy_together'];
                }
            }
            if (!empty($buy_together_group)) {
                $buy_together[$id_product] = $buy_together_group;
            }
        }
        return $buy_together;
    }

    /**
     * Get default value of option buy together at back end.
     *
     * @param int $id_product
     *
     * @return int
     */
    public static function getBuyTogetherDefault($id_product)
    {
        return self::getBuyTogether($id_product, HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT);
    }

    /**
     * Get setting buy together of product.
     *
     * @param int $id_product
     * @param int $buy_together
     *
     * @return int
     */
    protected static function getBuyTogether($id_product, $buy_together)
    {
        $product_setting = new HsMaProductSetting((int) $id_product);
        if (Validate::isLoadedObject($product_setting)) {
            $buy_together = (int)$product_setting->buy_together === (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT ? (int) Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER') : (int)$product_setting->buy_together;
        }

        return $buy_together;
    }
}
