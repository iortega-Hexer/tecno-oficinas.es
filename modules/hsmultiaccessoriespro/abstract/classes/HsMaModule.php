<?php
/**
 * Abstract Module class
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Extended Module<br/>
 * Overrides:<br/>
 * - Get enabled shops of the current module
 */
abstract class HsMaModule extends Module
{

    /**
     *
     * @return array
     * <pre>
     * array(
     *  int,
     *  int,
     *  ...
     * )
     */
    public function getEnabledShops()
    {
        $id_shops = array();
        $query = new DbQuery();
        $query->select('id_shop')->from('module_shop')->where('`id_module` = ' . (int) $this->id);
        $results = Db::getInstance()->executeS($query);
        if ($results) {
            foreach ($results as $row) {
                $id_shops[] = (int) $row['id_shop'];
            }
        }
        return $id_shops;
    }
}
