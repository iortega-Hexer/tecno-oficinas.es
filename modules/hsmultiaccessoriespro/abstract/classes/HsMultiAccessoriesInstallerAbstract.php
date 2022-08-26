<?php
/**
 * An abstract install of the module
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsMultiAccessoriesInstallerAbstract
{
    /**
     * Parent menu tab.
     *
     * @var string
     */
    const CLASS_PARENT_TAB = 'AdminCatalog';

    /**
     * Module name.
     *
     * @var string
     */
    protected $module_name;

    /**
     * Controller admin name.
     *
     * @var string
     */
    protected $class_controller_admin_group;

    /**
     * Name of an admin tab.
     *
     * @var string
     */
    protected $display_name;

    /**
     * List of SQL queries during installing the module.
     *
     * @var array
     */
    protected $install_queries = array();

    /**
     * List of SQL queries during un-installing the module.
     *
     * @var array
     */
    protected $uninstall_queries = array();

    /**
     * Constructor.
     *
     * @param string $module_name                  technical name of the module
     * @param string $class_controller_admin_group class name of an admin tab
     * @param string $display_name                 name of an admin tab
     */
    public function __construct($module_name, $class_controller_admin_group = null, $display_name = null)
    {
        $this->module_name = $module_name;
        $this->class_controller_admin_group = $class_controller_admin_group;
        $this->display_name = $display_name;
        $this->install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_group`
            (
                `id_accessory_group` int(10) NOT NULL AUTO_INCREMENT,
                `active` tinyint(1) unsigned DEFAULT 1,
                `show_name` tinyint(1) unsigned DEFAULT 1,
                `collapse_expand` int(2) unsigned DEFAULT 0,
                `position` int(10) default 0,
                `display_style` int(1) unsigned DEFAULT ' . (int) HsMaDisplayStyle::USE_DEFAULT . ',
                `buy_together` int(2) unsigned DEFAULT ' . (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT . ',
                PRIMARY KEY (`id_accessory_group`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $this->install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_group_lang` 
            (
                `id_accessory_group` int(10) NOT NULL DEFAULT 0,
                `id_lang` int(10) NOT NULL DEFAULT 0,
                `name` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id_accessory_group`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $this->install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_group_product`
            (
                `id_accessory_group_product` int(10) NOT NULL AUTO_INCREMENT,
                `id_accessory_group` int(10) NOT NULL DEFAULT 0,
                `id_product` int(10) NOT NULL DEFAULT 0,
                `default_quantity` int(10) NOT NULL DEFAULT 1,
                `min_quantity` int(10) NOT NULL DEFAULT 1,
                `required` int(1) default 0,
                `position` int(10) default 0,
                `id_accessory` int(10) DEFAULT NULL,
                `id_product_attribute` int(10) DEFAULT NULL,
                PRIMARY KEY (`id_accessory_group_product`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $this->install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_group_product_lang` 
            (
                `id_accessory_group_product` int(10) NOT NULL DEFAULT 0,
                `id_lang` int(10) NOT NULL DEFAULT 0,
                `name`	varchar(255),
                PRIMARY KEY (`id_accessory_group_product`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $this->install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_product_setting`
            (
                `id_accessory_product_setting` int(10) NOT NULL AUTO_INCREMENT,    
                `id_product` int(10) NOT NULL,
                `buy_together` int(1) NOT NULL DEFAULT 2,
                `custom_displayed_name` tinyint(1) unsigned DEFAULT 1,
                PRIMARY KEY (`id_accessory_product_setting`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $this->uninstall_queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'accessory_group`;';
        $this->uninstall_queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'accessory_group_lang`;';
        $this->uninstall_queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'accessory_group_product`;';
        $this->uninstall_queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'accessory_group_product_lang`;';
        $this->uninstall_queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'accessory_product_setting`;';
    }

    /*
     * Install all queries
     * @return boolean
     */

    public function installTables()
    {
        if (!empty($this->install_queries)) {
            foreach ($this->install_queries as $install_query) {
                if (!Db::getInstance()->execute($install_query)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Uninstall queries.
     *
     * @return bool
     */
    public function uninstallTables()
    {
        if (!empty($this->uninstall_queries)) {
            foreach ($this->uninstall_queries as $uninstall_query) {
                if (!Db::getInstance()->execute($uninstall_query)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Install admin tab.
     *
     * @return bool
     */
    public function installTabs()
    {
        $flag = false;
        if (self::CLASS_PARENT_TAB) {
            $id_tab = (int) Tab::getIdFromClassName(self::CLASS_PARENT_TAB); // get id parent tab
            if (!$id_tab) {
                // install parent tab
                $this->installModuleTab(self::CLASS_PARENT_TAB, $this->display_name, 0);
                $id_tab = (int) Tab::getIdFromClassName(self::CLASS_PARENT_TAB); // get id parent exit tab
            }
            if (isset($id_tab)) {
                $flag = ($this->installModuleTab($this->class_controller_admin_group, $this->display_name, $id_tab));
            }
        }

        return $flag;
    }

    /**
     * Install an Admin Tab (menu).
     *
     * @param string $tab_class
     * @param string $tab_name
     * @param int    $id_tab_parent
     * @param int    $position
     *
     * @return bool
     */
    protected function installModuleTab($tab_class, $tab_name, $id_tab_parent = -1, $position = 0)
    {
        $tab = new Tab();
        $name = array();
        foreach (Language::getLanguages(false) as $language) {
            $name[$language['id_lang']] = $tab_name;
        }
        $tab->name = $name;
        $tab->class_name = (string) $tab_class;
        $tab->module = $this->module_name;
        if ($id_tab_parent != null) {
            $tab->id_parent = $id_tab_parent;
        }
        if ((int) $position > 0) {
            $tab->position = (int) $position;
        }

        return $tab->add(true);
    }

    /**
     * Uninstall an Admin Tab (menu).
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        if ($this->proccessRemoveTab($this->class_controller_admin_group)) {
            return true;
        }

        return false;
    }

    /**
     * Remove a tab.
     *
     * @param string $name_tab
     *
     * @return bool
     */
    protected function proccessRemoveTab($name_tab)
    {
        $flag = false;
        $id_tab = (int) Tab::getIdFromClassName((string) $name_tab);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $flag = $tab->delete();
            }
        }

        return $flag;
    }

    /**
     *
     * @return boolean
     */
    public function installConfigs()
    {
        $success = array();
        $success[] = Configuration::updateValue('HSMA_DISPLAY_STYLE', 0);
        $success[] = Configuration::updateValue('HSMA_SHOW_IMAGES', 1);
        $success[] = Configuration::updateValue('HSMA_SHOW_PRICE', 1);
        $success[] = Configuration::updateValue('HSMA_EACH_ACCESSORY_TO_BASKET', 0);
        $success[] = Configuration::updateValue('HSMA_SHOW_PRICE_TABLE', 1);
        $success[] = Configuration::updateValue('HSMA_CHANGE_MAIN_PRICE', 1);
        $success[] = Configuration::updateValue('HSMA_APPLY_FANCYBOX_TO_IMAGE', 1);
        $success[] = Configuration::updateValue('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB', 1);
        $success[] = Configuration::updateValue('HSMA_BUY_ACCESSORY_MAIN_TOGETHER', 1);
        $success[] = Configuration::updateValue('HSMA_SHOW_TOTAL_PRICE', 1);
        $success[] = Configuration::updateValue('HSMA_SHOW_CUSTOM_QUANTITY', 1);
        $success[] = Configuration::updateValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', 0);
        $success[] = Configuration::updateValue('HSMA_SHOW_ACCESSORIES_OFS', 1);
        $success[] = Configuration::updateValue('HSMA_CHANGE_ACCESSORY_NAME', 1);
        $success[] = Configuration::updateValue('HSMA_SHOW_ICON_OUT_OF_STOCK', 1);
        $success[] = Configuration::updateValue('HSMA_SCROLL_TO_TABLE_PRICE', 1);
        $success[] = Configuration::updateValue('HSMA_DEL_ALL_WHEN_DEL_MPRODUCT', 1);
        $success[] = Configuration::updateValue('HSMA_DEL_ALL_WHEN_DEL_ACCESSORY', 1);

        return array_sum($success) >= count($success);
    }

    /**
     * Add more fields in version 2.4.0.
     *
     * @return bool
     */
    public function updateTable24()
    {
        $flag = true;
        $column_exits = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group_product\' AND COLUMN_NAME = \'id_product_attribute\'';
        $result = Db::getInstance()->getValue($column_exits);
        if (empty($result)) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `id_product_attribute` int(10) default 0 AFTER `id_product`';
            $flag = Db::getInstance()->execute($sql);
        }

        return $flag;
    }

    /**
     * Add more fields & new table & copy data.
     *
     * @return bool
     */
    public function updateTable25()
    {
        $sqls = array();
        $flag = true;
        // For table accessory_group_product
        // Add new column id_accessory_group_product
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `id_accessory_group_product` INT(10) NOT NULL AFTER `id_accessory_group`';
        // Drop all primary key
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` DROP PRIMARY KEY';
        // Modify id_accessory_group_product to primary key
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` MODIFY `id_accessory_group_product` INT(10) AUTO_INCREMENT PRIMARY KEY';

        // For table product_short_name_lang
        // Change name table product_short_name_lang to accessory_group_product_lang
        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_group_product_lang`(
																`id_accessory_group_product` int(10) NOT NULL DEFAULT 0,
																`id_lang` int(10) NOT NULL DEFAULT 0,
																`name`	varchar(100),
																PRIMARY KEY (`id_accessory_group_product`,`id_lang`)
														)';
        foreach ($sqls as $sql) {
            $flag = $flag && Db::getInstance()->execute($sql);
        }

        return $flag && $this->copyData25();
    }

    /**
     * Copy custom accessory names from the old table (`product_short_name`) to the new table (`accessory_group_product`)
     * From now on, custom name of an accessory will be product specific and accessory group specific.
     *
     * @return bool
     */
    protected function copyData25()
    {
        // collect raw data
        $accessory_group_products = HsAccessoriesGroupProduct::getAccessoryGroupProducts();
        $short_names = HsAccessoriesGroupProduct::getShortNames();
        $languages = Language::getLanguages(false);
        $flag = true;

        // detect accessories with and without custom names
        $accessory_group_product_lang_rows = array();
        $all_duplicated_id_accessories = array();
        $duplicated_id_accessories_with_custom_name = array();

        foreach ($accessory_group_products as $accessory_group_product) {
            $all_duplicated_id_accessories[] = $accessory_group_product['id_accessory'];
        }
        foreach ($short_names as $short_name) {
            $duplicated_id_accessories_with_custom_name[] = $accessory_group_product['id_accessory'];
        }

        $all_id_accessories = array_unique($all_duplicated_id_accessories);
        $id_accessories_with_custom_name = array_unique($duplicated_id_accessories_with_custom_name);
        $id_accessories_without_custom_name = array_diff($all_id_accessories, $id_accessories_with_custom_name);

        // get instances of all accessories without custom name
        $no_name_product_collection = new PrestaShopCollection('product');
        $no_name_product_collection->where('id_product', 'in', $id_accessories_without_custom_name);
        $tmp_products = $no_name_product_collection->getResults();

        // format the array of products so that the key is id_product
        $no_name_products = array();
        foreach ($tmp_products as $tmp_product) {
            $no_name_products[$tmp_product->id] = $tmp_product;
        }

        // prepare the rows to be inserted
        foreach ($accessory_group_products as $accessory_group_product) {
            // accessories with custom name
            if (in_array($accessory_group_product['id_accessory'], $id_accessories_with_custom_name)) {
                $accessory_group_product_lang_rows[] = '(' . $accessory_group_product['id_accessory_group_product'] . ',' . $short_name['id_lang'] . ',"' . pSQL($short_name['name']) . '")';
            } elseif (isset($no_name_products[$accessory_group_product['id_accessory']])) {
                // accessories without custom name

                foreach ($languages as $language) {
                    $accessory_name = $no_name_products[$accessory_group_product['id_accessory']]->name[$language['id_lang']];
                    $accessory_group_product_lang_rows[] = '(' . $accessory_group_product['id_accessory_group_product'] . ',' . $language['id_lang'] . ',"' . pSQL($accessory_name) . '")';
                }
            }
        }

        // Now, let's insert to database
        $unique_values = array_unique($accessory_group_product_lang_rows);
        $chunked_arrays = array_chunk($accessory_group_product_lang_rows, 50); // insert 50 rows each
        foreach ($chunked_arrays as $chunked_array) {
            $unique_values = array_unique($chunked_array);
            if (!empty($unique_values)) {
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'accessory_group_product_lang`(`id_accessory_group_product`,`id_lang`,`name`) VALUES';
                $sql .= implode(',', $unique_values);
                $flag = $flag & Db::getInstance()->execute($sql);
            }
        }

        return $flag;
    }

    /**
     * Install new tab for version 2.4.3.
     *
     * @param array $admin_tab
     */
    public function installTab243(array $admin_tab)
    {
        return $this->installHideTab($admin_tab);
    }

    protected function installHideTab(array $admin_tab)
    {
        $flag = true;
        if (self::CLASS_PARENT_TAB) {
            $id_parent_tab = -1;
            $count_tab_controller_admin = count($admin_tab);
            foreach ($admin_tab as $controller_admin => $tab_name) {
                $count_tab_controller_admin -= (int) $this->installModuleTab($controller_admin, $tab_name, $id_parent_tab);
            } // install sub tab
            $flag = ($count_tab_controller_admin == 0 ? true : false);
        }

        return $flag;
    }

    /**
     * Add new image type with name "hsma_default".
     *
     * @return bool
     */
    public function addNewImageType()
    {
        $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
        $add_new_image_type = true;
        if (!Validate::isLoadedObject($image_type)) {
            $image_type->name = 'hsma_default';
            $image_type->width = 55;
            $image_type->height = 55;
            $image_type->products = 1;
            $add_new_image_type = $image_type->add() && Configuration::updateValue('HSMA_IMAGE_TYPE', $image_type->name) && Configuration::updateValue('HSMA_ID_IMAGE_TYPE', $image_type->id);
        }

        return $add_new_image_type;
    }

    /**
     * Add more fields in version 2.5.2.
     *
     * @return bool
     */
    public function updateTable252()
    {
        $flag = true;
        $display_style_exits = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group\' AND COLUMN_NAME = \'display_style\'');
        if (empty($display_style_exits)) {
            $add_column_display_style = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group` ADD COLUMN `display_style` int(1) unsigned DEFAULT ' . (int) HsMaDisplayStyle::USE_DEFAULT . ' AFTER `active`';
            $flag = $flag && Db::getInstance()->execute($add_column_display_style);
        }
        $default_quantity_exits = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group_product\' AND COLUMN_NAME = \'default_quantity\'');
        if (empty($default_quantity_exits)) {
            $add_column_default_quantity = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `default_quantity` int(10) default 1 AFTER `id_product`';
            $flag = $flag && Db::getInstance()->execute($add_column_default_quantity);
        }

        return $flag;
    }

    /**
     * Update module for version 2.7.
     *
     * @return bool
     */
    public function update27(array $tabs27)
    {
        return $this->updateTable27() && $this->installTab27($tabs27);
    }

    /**
     * Update module for version 2.7.3.
     *
     * @return bool
     */
    public function update273()
    {
        $add_position_accessory_groups = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group` ADD COLUMN `position` int(10) default 0 AFTER `active`';
        $add_position_accessory_group_products = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `position` int(10) default 0 AFTER `required`';
        $flag = false;
        if (Db::getInstance()->execute($add_position_accessory_groups) && Db::getInstance()->execute($add_position_accessory_group_products)) {
            $flag = $this->updateDefaultPositionAccessoryGroups();
        }

        return $flag;
    }

    /**
     * Update default position for accessory group.
     *
     * @return bool
     */
    protected function updateDefaultPositionAccessoryGroups()
    {
        $sql = 'SELECT id_accessory_group FROM `' . _DB_PREFIX_ . 'accessory_group` WHERE 1';
        $accessory_groups = Db::getInstance()->executeS($sql);
        $position = array();
        if (!empty($accessory_groups)) {
            foreach ($accessory_groups as $index => $accessory_group) {
                $position[] = '(' . $accessory_group['id_accessory_group'] . ',' . $index . ')';
            }
        }
        $flag = true;
        if (!empty($position)) {
            $flag = Db::getInstance()->execute('REPLACE INTO `' . _DB_PREFIX_ . 'accessory_group` (id_accessory_group, position) VALUES ' . implode(',', $position) . '');
        }

        return $flag;
    }

    /**
     * Update database for version 2.7.0.
     *
     * @return bool
     */
    public function updateTable27()
    {
        $create_table_product_setting = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_product_setting` (
																`id_accessory_product_setting` int(10) NOT NULL AUTO_INCREMENT,
																`id_product` int(10) NOT NULL,
																`buy_together` int(1) NOT NULL DEFAULT 2,
																`custom_displayed_name` tinyint(1) unsigned DEFAULT 1,
																PRIMARY KEY (`id_accessory_product_setting`)
														)';
        $flag = Db::getInstance()->execute($create_table_product_setting);
        $column_required_exists = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group_product\' AND COLUMN_NAME = \'required\'');
        if (empty($column_required_exists)) {
            $add_column_required = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `required` int(1) default 0 AFTER `default_quantity`';
            $flag = $flag && Db::getInstance()->execute($add_column_required);
        }

        return $flag;
    }

    /**
     * Install new tabs for version 2.7.
     *
     * @param array $tabs27
     *
     * @return bool
     */
    public function installTab27(array $tabs27)
    {
        $flag = true;
        foreach ($tabs27 as $tab27) {
            $flag = $flag && $this->installHideTab($tab27);
        }

        return $flag;
    }

    public function updateTable284()
    {
        $create_table_product_cart_rule = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_product_cart_rule` (
                                                        `id_main_product` int(10),
                                                        `id_accessory` int(10),
                                                        `id_cart_rule` int(10) NOT NULL
                                        )';
        return Db::getInstance()->execute($create_table_product_cart_rule);
    }

    /**
     *
     * @return boolean
     */
    public function updateTable287()
    {
        $flag = true;
        $is_min_quantity_existed = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group_product\' AND COLUMN_NAME = \'min_quantity\'');
        if (empty($is_min_quantity_existed)) {
            $flag = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group_product` ADD COLUMN `min_quantity` int(10) DEFAULT 1 AFTER `default_quantity`');
        }
        return $flag;
    }

    /**
     *
     * @return boolean
     */
    public function updateConfiguration287()
    {
        $success = array();
        $success[] = Configuration::updateValue('HSMA_SHOW_CUSTOM_QUANTITY', 1);
        $success[] = Configuration::updateValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', 1);
        return array_sum($success) >= count($success);
    }

    /**
     *
     * @return boolean
     */
    public function updateConfiguration2810()
    {
        $success = array();
        $success[] = Configuration::updateValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', Configuration::get('HSMA_ALLOW_CUSTOMER_TO_CHANGE_QUANTITY'));
        $success[] = array_sum($success) >= count($success) && Configuration::deleteByName('HSMA_ALLOW_CUSTOMER_TO_CHANGE_QUANTITY');
        return array_sum($success) >= count($success);
    }

    /**
     *
     * @return boolean
     */
    public function updateTable2810()
    {
        $success = array();
        $existing_tables = array(
            'accessory_group',
            'accessory_group_lang',
            'accessory_group_product',
            'accessory_group_product_lang',
            'accessory_product_setting'
        );
        foreach ($existing_tables as $table) {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . $table . '` CONVERT TO CHARACTER SET utf8;';
            $success[] = Db::getInstance()->execute($sql);
        }
        return array_sum($success) >= count($success);
    }

    /**
     * Add table for version 2.9.0
     */
    public function installTables290()
    {
        $queries = array();
        $success = array();
        $queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'accessory_cart_product`
            (
                `id_accessory_cart_product` int(10) NOT NULL AUTO_INCREMENT,
                `id_product` int(10) NOT NULL,
                `id_cart` int(10) NOT NULL,
                `id_product_attribute` int(10) DEFAULT 0,
                `id_product_customization` int(10) DEFAULT 0,
                `id_customization` int(10) DEFAULT 0,
                `id_accessory` int(10) NOT NULL,
                `id_accessory_attribute` int(10) DEFAULT 0,
                `quantity` int(10) NOT NULL DEFAULT 1,
                `prev_quantity` int(10) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_accessory_cart_product`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        foreach ($queries as $query) {
            $success[] = Db::getInstance()->execute($query);
        }
        return array_sum($success) >= count($success);
    }

    /**
     *
     * @return boolean
     */
    public function updateConfiguration290()
    {
        $success = array();
        $success[] = Configuration::updateValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', Configuration::get('HSMA_ALLOW_CUSTOMER_TO_CHANGE_QUANTITY'));
        return array_sum($success) >= count($success);
    }
    /**
     *
     * @return boolean
     */
    public function updateConfiguration2101()
    {
        return Configuration::updateValue('HSMA_SHOW_ACCESSORIES_OFS', 1);
    }
    
    /**
     * All update for version 3.0.0
     * @return boolean
     */
    public function updateVersion300()
    {
        $success = array();
        $is_show_name_existed = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group\' AND COLUMN_NAME = \'show_name\'');
        $is_collapse_expand_existed = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group\' AND COLUMN_NAME = \'collapse_expand\'');
        if (empty($is_show_name_existed) && empty($is_collapse_expand_existed)) {
            $success[] = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group` ADD COLUMN `show_name` int(10) DEFAULT 1 AFTER `active`');
            $success[] = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group` ADD COLUMN `collapse_expand` int(10) DEFAULT 0 AFTER `active`');
        }
        if (array_sum($success) >= count($success)) {
            $success[] = $this->updateSettingCollapseExpand();
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Update Collapse|Expand setting of accessory group based on old global setting
     * @return boolean
     */
    protected function updateSettingCollapseExpand()
    {
        $current_setting = Configuration::get('HSMA_COLLAPSE_EXPAND_GROUPS');
        switch ($current_setting) {
            case HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND:
                $result = Db::getInstance()->update('accessory_group', array('collapse_expand' => (int) HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND));
                break;
            case HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND_FIRST:
                $result = Db::getInstance()->update('accessory_group', array('collapse_expand' => (int) HsMaDisplayStyle::DISPLAY_GROUPS_EXPAND_FIRST), 'position = 0');
                break;
            case HsMaDisplayStyle::DISPLAY_GROUPS_COLLAPSE:
                $result = Db::getInstance()->update('accessory_group', array('collapse_expand' => (int) HsMaDisplayStyle::DISPLAY_GROUPS_COLLAPSE));
                break;
            default:
                $result = true;
                break;
        }
        if ($result) {
            Configuration::deleteByName('HSMA_COLLAPSE_EXPAND_GROUPS');
        }
        return $result;
    }
    
    /**
     * All update for version 4.0.0
     * @return boolean
     */
    public function updateVersion400()
    {
        $success = array();
        $is_id_customization_existed = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_cart_product\' AND COLUMN_NAME = \'id_customization\'');
        if (empty($is_id_customization_existed)) {
            $success[] = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'accessory_cart_product` ADD COLUMN `id_customization` int(10) DEFAULT 0 AFTER `id_product_attribute`');
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Add more field in version 4.2.0.
     * @return boolean
     */
    public function updateTable420()
    {
        $success = array();
        $column_buy_together_exits = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_group\' AND COLUMN_NAME = \'buy_together\'');
        if (empty($column_buy_together_exits)) {
            $add_column_buy_together = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_group` ADD COLUMN `buy_together` int(2) unsigned DEFAULT ' . (int) HsMaProductSetting::BUY_TOGETHER_USE_DEFAULT . ' AFTER `display_style`';
            $success[] = Db::getInstance()->execute($add_column_buy_together);
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * Add more field in version 4.3.1
     * @return boolean
     */
    public function updateTable431()
    {
        $success = array();
        $column_id_product_customization_exits = Db::getInstance()->getValue('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND TABLE_NAME = \'' . _DB_PREFIX_ . 'accessory_cart_product\' AND COLUMN_NAME = \'id_product_customization\'');
        if (empty($column_id_product_customization_exits)) {
            $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'accessory_cart_product` ADD COLUMN `id_product_customization` int(10) DEFAULT 0 AFTER `id_product_attribute`';
            $success[] = Db::getInstance()->execute($query);
        }
        return array_sum($success) >= count($success);
    }
}
