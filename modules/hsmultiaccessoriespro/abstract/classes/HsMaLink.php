<?php
/**
 * HsMaLink for Multi Accessories
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMaLink extends Link
{
    /**
     * @param string $link_rewrite
     * @param int $id_image
     * @param string $default_image_type
     * @return string
     */
    public static function getProductImageLink($link_rewrite, $id_image, $default_image_type)
    {
        $small_image_type = HsMaImageType::getFormatedNameByPsVersion('small');
        $hsma_image_type = ($default_image_type == $small_image_type) ? Configuration::get('HSMA_IMAGE_TYPE') : Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX');
        $context = Context::getcontext();
        return $id_image ? $context->link->getImageLink($link_rewrite, $id_image, $hsma_image_type) : _THEME_PROD_DIR_.$context->language->iso_code.'-default-'.$default_image_type.'.jpg';
    }
    
    /**
     * Get link of customization image
     * @param object $link
     * @param string $imageHash
     * @return array
     */
    public static function getCustomizationImage($link, $imageHash)
    {
        $large_image_url = rtrim($link->getBaseLink(), '/') . '/upload/' . $imageHash;
        $small_image_url = $large_image_url . '_small';
        $small = [
            'url' => $small_image_url,
        ];
        $large = [
            'url' => $large_image_url,
        ];
        $medium = $large;
        return [
            'bySize' => [
                'small' => $small,
                'medium' => $medium,
                'large' => $large,
            ],
            'small' => $small,
            'medium' => $medium,
            'large' => $large,
            'legend' => '',
        ];
    }
}
