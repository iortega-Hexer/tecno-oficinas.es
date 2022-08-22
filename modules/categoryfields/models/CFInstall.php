<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2017 Musaffar Patel
 * @license   LICENSE.txt
 */

class CFInstall
{
    public static function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cf_categoryfield` (
            `id_categoryfield` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(64) NOT NULL,
            `id_shop` mediumint(8) unsigned NOT NULL,
            `collapsible` tinyint(3) unsigned NOT NULL,
            PRIMARY KEY (`id_categoryfield`)		
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cf_categoryfield_content` (
            `id_categoryfield_content` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_categoryfield` int(10) unsigned NOT NULL,
            `id_category` int(10) NOT NULL,
            `id_lang` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            `content` text NOT NULL,
            `excerpt` text NOT NULL,            
		    PRIMARY KEY (`id_categoryfield_content`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');
        return $return;
    }

    public static function addColumn($table, $name, $type)
    {
        try {
            $return = Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` ADD `'.$name.'` '.$type);
        } catch (Exception $e) {
            return true;
        }
        return true;
    }

    private static function dropColumn($table, $name)
    {
        Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` DROP `'.$name.'`');
    }

    protected static function dropTable($table_name)
    {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$table_name.'`';
        DB::getInstance()->execute($sql);
    }

    public static function installData()
    {
    }

    public static function uninstall()
    {
    }
}
