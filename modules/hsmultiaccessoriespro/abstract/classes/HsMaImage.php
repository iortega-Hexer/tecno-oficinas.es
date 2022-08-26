<?php
/**
 * Multi Accessories for Prestashop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaImage extends Image
{
    /**
     * This is similar to Image::getImages() but this one only gets the first image of each combination
     * @param int $id_product
     * @return array
     * <pre>
     * array(
     *  int => int // id_product_attribute => id_image
     * )
     */
    public static function getMaImages($id_product)
    {
        $sql = 'SELECT `id_product_attribute`, SUBSTRING_INDEX(GROUP_CONCAT(i.`id_image` ORDER BY i.`position` ASC),",",1) AS `id_image`';
        $sql .= ' FROM `' . _DB_PREFIX_ . 'image` i';
        $sql .= ' JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON pai.`id_image` = i.`id_image`';
        $sql .= ' WHERE i.`id_product` = ' . (int) $id_product;
        $sql .= ' GROUP BY `id_product_attribute`';
        $images = Db::getInstance()->executeS($sql);
        $formatted_images = array();
        foreach ($images as $image) {
            $formatted_images[$image['id_product_attribute']] = $image['id_image'];
        }
        return $formatted_images;
    }
}
