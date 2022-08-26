<?php
/**
 * Multi Accessories for Prestashop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaImageType extends ImageType
{
    public static function getFormatedNameByPsVersion($type)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $image_type = ImageType::getFormattedName($type);
        } else {
            $image_type = ImageType::getFormatedName($type);
        }
        return $image_type;
    }
}
