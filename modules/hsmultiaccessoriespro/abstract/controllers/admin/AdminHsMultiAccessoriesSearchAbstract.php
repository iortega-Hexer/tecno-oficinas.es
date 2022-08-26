<?php
/**
 * An abstract admin search accessories controller of the module
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once dirname(__FILE__).'/../../classes/HsMaSearch.php';
class AdminHsMultiAccessoriesSearchAbstract extends ModuleAdminController
{
    /**
     * Auto complete search accessories.
     */
    public function displayAjaxAutoCompleteSearch()
    {
        $keyword = Tools::getValue('q', false);
        if (!$keyword || $keyword == '' || Tools::strlen($keyword) < 1) {
            exit();
        }
        if ($pos = strpos($keyword, ' (ref:')) {
            $keyword = Tools::substr($keyword, 0, $pos);
        }

        $exclude_ids = Tools::getValue('excludeIds', false);
        if ($exclude_ids && $exclude_ids != 'NaN') {
            $exclude_ids = implode(',', array_map('intval', explode(',', $exclude_ids)));
        } else {
            $exclude_ids = '';
        }

        // Excluding downloadable products from packs because download from pack is not supported
        $exclude_virtuals = (bool) Tools::getValue('excludeVirtuals', false);
        $exclude_packs = (bool) Tools::getValue('exclude_packs', false);
        $is_setting_page = (bool) Tools::getValue('is_setting_page', false);
        $items = HsMaSearch::searchAccessories($exclude_ids, $keyword, $exclude_virtuals, $exclude_packs);

        
        if ($items) {
            if ($this->module->isPrestashop17() && !$is_setting_page) {
                // packs
                $results = array();
                foreach ($items as $item) {
                    $product = array(
                        'id' => (int) $item['id_product'],
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                    );
                    array_push($results, $product);
                }
                echo Tools::jsonEncode($results);
            } else {
                foreach ($items as $item) {
                    echo trim($item['name']).(!empty($item['reference']) ? ' (ref: '.$item['reference'].')' : '').'|'.(int) $item['id_product']."\n";
                }
            }
        } else {
            if ($this->module->isPrestashop17() && !$is_setting_page) {
                echo Tools::jsonEncode(array());
            } else {
                Tools::jsonEncode(new stdClass());
            }
        }
    }
}
