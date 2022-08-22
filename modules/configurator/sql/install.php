<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @author    DMConcept <support@dmconcept.fr>
 * @copyright 2015 DMConcept
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

/* * ********************
 * CONFIGURATOR
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator` (
    `id_configurator` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_product` int(10) unsigned NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 0,
    `id_customization_field` INT NOT NULL,
    `use_base_price` tinyint(1) NOT NULL DEFAULT 1,
    `hide_qty_product` tinyint(1) NOT NULL DEFAULT 0,
    `hide_button_add_to_cart` tinyint(1) NOT NULL DEFAULT 0,
    `hide_product_price` tinyint(1) NOT NULL DEFAULT 0,
    `tab_type` VARCHAR(255) NOT NULL DEFAULT "tab",
    `visual_rendering` TINYINT(1) DEFAULT 0,
    `tab_force_require_step` TINYINT(1) DEFAULT 0,
    PRIMARY KEY  (`id_configurator`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_shop` (
    `id_configurator` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id_configurator`,`id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/* * ********************
 * CONFIGURATOR STEPS
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step` (
    `id_configurator_step` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_configurator` int(10) unsigned NOT NULL,
    `id_option_group` int(10) unsigned NOT NULL,
    `type` VARCHAR(20) NOT NULL,
    `required` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `multiple` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `max_options` INT UNSIGNED NULL DEFAULT 0,
    `min_options` INT UNSIGNED NULL DEFAULT 0,
    `displayed_by_yes` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `displayed_in_preview` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `displayed_in_order` TINYINT(1) UNSIGNED NULL DEFAULT 1,
    `use_input` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `use_qty` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `min_qty` TEXT NULL,
    `max_qty` TEXT NULL,
    `max_qty_step_option_id` INT UNSIGNED NULL DEFAULT 0,
    `max_qty_coef` INT UNSIGNED NULL DEFAULT 0,
    `step_qty` INT DEFAULT 0,
    `display_total` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `unique_price` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `unique_price_value` DECIMAL(17,2) UNSIGNED DEFAULT 0,
    `nb_files` INT(10) UNSIGNED NULL DEFAULT 1,
    `max_weight_total` INT(10) UNSIGNED NULL DEFAULT 1,
    `extensions` TEXT NULL,
    `use_division` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `use_custom_template` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `custom_template` VARCHAR( 255 ) NULL,
    `price_list` longtext,
    `price_list_name` VARCHAR(255) NULL,
    `price_list_type` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `price_list_display` VARCHAR( 255 ) NOT NULL,
    `price_list_coeff` decimal(17,2) unsigned DEFAULT 0,
    `determine_base_price` tinyint(1) DEFAULT 0,
    `css` TEXT NULL,
    `class` TEXT NULL,
    `position` int(10) unsigned NOT NULL DEFAULT 0,
    `id_configurator_step_tab` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `use_upload_camera` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `show_upload_image` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `use_shared` TINYINT(1) UNSIGNED NULL DEFAULT 0,
    `formula` TEXT,
    `formula_surface` TEXT,
    `weight` TEXT NULL,
    `dimension_width` TEXT NULL,
    `dimension_height` TEXT NULL,
    `dimension_depth` TEXT NULL,
    `dropzone` TEXT NULL,
    `ignored_if_empty` tinyint(1) DEFAULT 1,
    `use_combination_as_default_value` TINYINT(1) DEFAULT 0,
    `use_url_as_default_value` TINYINT(1) DEFAULT 0,
    `upload_display_progress` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id_configurator_step`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_lang` (
    `id_configurator_step` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `name` varchar(255),
    `public_name` varchar(255),
    `default_value_select` varchar(255),
    `info_text` text, 
    `invoice_name` varchar(255),
    `input_suffix` varchar(45),
    `content` text,
    `header_names` text,
    PRIMARY KEY (`id_configurator_step`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_group` (
    `id_configurator_step_group` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `id_group` int(11) unsigned NOT NULL,
    `id_configurator_step` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_configurator_step_group`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/* * ********************
 * CONFIGURATOR STEPS OPTION
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_option` (
    `id_configurator_step_option` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_configurator_step` int(10) unsigned NOT NULL,
    `id_configurator_step_option_division` int(10) unsigned DEFAULT NULL,
    `id_option` int(10) unsigned NOT NULL,
	`ipa` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `impact_type` VARCHAR( 50 ) NOT NULL,
    `impact_value` decimal(20,6) unsigned DEFAULT 0,
    `impact_value_period` MEDIUMTEXT NULL,
    `impact_step_id` INT unsigned NULL,
    `id_impact_step_option_x` INT unsigned NULL,
    `id_impact_step_option_y` INT unsigned NULL,
    `impact_multiple_step_id` VARCHAR(250) NULL DEFAULT NULL,
    `price_list` mediumtext,
    `unity` VARCHAR( 10 ) NOT NULL,
    `conversion_factor` DECIMAL( 20, 6 ) NOT NULL DEFAULT 0,
    `selected_by_default` tinyint(1) DEFAULT 0,
    `reference` VARCHAR( 250 ) NULL,
    `reference_position` INT unsigned NULL,
    `default_value` TEXT,
    `min_value` TEXT NULL,
    `min_value_if_null` VARCHAR( 250) DEFAULT 0 NULL,
    `max_value` TEXT NULL,
    `max_value_if_null` VARCHAR( 250 ) DEFAULT 0 NULL,
    `force_value` tinyint(1) DEFAULT 0,
    `check_value` tinyint(1) DEFAULT 0,
    `slider` tinyint(1) DEFAULT 0,
    `slider_step` int(10) unsigned NOT NULL DEFAULT 1,
    `textarea` tinyint(1) DEFAULT 0,
    `email` tinyint(1) DEFAULT 0,
    `is_date` tinyint(1) DEFAULT 0,
    `is_ralstep` tinyint(1) DEFAULT 0,
    `id_atribute_ral` int unsigned NULL,
    `weight` TEXT NULL,
	`price_calculation` VARCHAR(100) NOT NULL DEFAULT "with_reduc",
	`display_price_calculation` VARCHAR(100) NOT NULL DEFAULT "with_reduc",
	`position` INT(10) NOT NULL DEFAULT 0,
	`impact_formula` TEXT,
	`used_for_dimension` VARCHAR(20) NULL,
	`dimension_coeff` DECIMAL(20,10) NULL,
    `qty_coeff` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `default_qty` TEXT NULL,
    `id_tax_rules_group_product` INT(11) NULL,
    `id_step_impact_qty` INT DEFAULT 0,
    `id_step_option_impact_qty` INT DEFAULT 0,
    PRIMARY KEY (`id_configurator_step_option`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_option_lang` (
  `id_configurator_step_option` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_lang` int(10) unsigned NOT NULL,
  `content` text,
  PRIMARY KEY (`id_configurator_step_option`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

/* * ********************
 * CONFIGURATOR DISPLAY CONDITIONS
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_display_condition` (
    `id_configurator_step_display_condition` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_configurator_step_display_condition_group` int(10) unsigned NOT NULL,
    `value` int(10) unsigned NOT NULL,
    `min` DECIMAL(20,6) UNSIGNED DEFAULT 0.000000,
    `max` DECIMAL(20,6) UNSIGNED DEFAULT 0.000000,
    `negative_condition` INT(1) NULL DEFAULT 0,
    `formula` TEXT NULL,
    PRIMARY KEY (`id_configurator_step_display_condition`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_display_condition_group` (
    `id_configurator_step_display_condition_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_configurator_step` int(10) unsigned NOT NULL,
    `id_configurator_step_option` int(10) unsigned NOT NULL,
    `negative_condition` INT(1) NULL DEFAULT 0,
    PRIMARY KEY (`id_configurator_step_display_condition_group`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/* * ********************
 * CONFIGURATOR CART DETAIL
 * 
 * // Let `attribute_key` for retrocompatibility
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_cart_detail` (
    `id_configurator_cart_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_configurator` int(10) unsigned NOT NULL,
    `id_product` int(10) unsigned NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `id_guest` INT(11) NULL DEFAULT 0,
    `id_cart` int(10) unsigned NOT NULL,
    `id_order` int(10) unsigned NULL DEFAULT 0,
    `id_order_detail` int(10) unsigned NULL DEFAULT 0,
    `attribute_key` varchar(255) NULL,
    `detail` MEDIUMTEXT,
    `added_in_cart` tinyint(1) NOT NULL DEFAULT 0,
    `price` DECIMAL(20,6) NOT NULL,
    `weight` DECIMAL(20,6) NOT NULL,
    `id_customization` INT NOT NULL,
    `visual_rendering` LONGTEXT NULL,
    `id_tax_rules_group` INT(11) NULL,
    `reference` VARCHAR(255) DEFAULT "",
    PRIMARY KEY (`id_configurator_cart_detail`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/* * ********************
 * CONFIGURATOR ATTACHMENT
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_attachment` (
	`id_configurator_attachment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`file` VARCHAR(40) NOT NULL,
	`file_name` VARCHAR(128) NOT NULL,
	`file_size` BIGINT(10) UNSIGNED NOT NULL DEFAULT 0,
	`mime` VARCHAR(128) NOT NULL,
	`token` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`id_configurator_attachment`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_cartdetail_attachment` (
	`id_configurator_cart_detail` INT(10) UNSIGNED NOT NULL,
	`id_step` INT(10) UNSIGNED NOT NULL,
	`id_configurator_attachment` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`id_configurator_cart_detail`, `id_configurator_attachment`, `id_step`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

/* * ********************
 * CONFIGURATOR STEPS TAB
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_tab` (
        `id_configurator_step_tab` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_configurator` INT(10) UNSIGNED NOT NULL,
        `position` INT NULL,
        PRIMARY KEY (`id_configurator_step_tab`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_tab_lang` (
        `id_configurator_step_tab` INT(10) UNSIGNED NOT NULL,
        `id_lang` INT(10) UNSIGNED NOT NULL,
        `name` VARCHAR(255),
        PRIMARY KEY (`id_configurator_step_tab`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


/* * ********************
 * CONFIGURATOR INDEXES
 * ******************** */
// INDEXES CONFIGURATOR
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator` ADD INDEX(`id_product`);";

// INDEXES CONFIGURATOR CART DETAIL
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_configurator`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_product`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_product_attribute`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_cart`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_order`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_order_detail`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_customization`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_cart_detail` ADD INDEX(`id_guest`);";

// INDEXES CONFIGURATOR STEP
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step` ADD INDEX(`id_configurator`);";

// INDEXES CONFIGURATOR STEP OPTION
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` ADD INDEX(`id_configurator_step`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` ADD INDEX(`id_option`);";
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_option` ADD INDEX(`ipa`);";

// INDEXES CONFIGURATOR STEP CONDITION GROUP
$sql[] = "ALTER TABLE `" . _DB_PREFIX_
    . "configurator_step_display_condition_group` ADD INDEX(`id_configurator_step`);";

// INDEXES CONFIGURATOR STEP CONDITION
$sql[] = "ALTER TABLE `" . _DB_PREFIX_
    . "configurator_step_display_condition` ADD INDEX(`id_configurator_step_display_condition_group`);";

// INDEXES CONFIGURATOR STEP TAB
$sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "configurator_step_tab` ADD INDEX(`id_configurator`);";


/* * ********************
 * CONFIGURATOR STEP FILTER
 * ******************** */
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_filter_group` (
      `id_configurator_step_filter_group` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_configurator_step` int(10) UNSIGNED NOT NULL,
      `id_configurator_step_option` int(10) UNSIGNED NOT NULL,
      PRIMARY KEY (`id_configurator_step_filter_group`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'configurator_step_filter` (
      `id_configurator_step_filter` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_configurator_step_filter_group` int(10) UNSIGNED NOT NULL,
      `type` varchar(30) NOT NULL,
      `id_option` int(10) UNSIGNED NOT NULL,
      `operator` varchar(30) NOT NULL,
      `id_target_step` int(10) UNSIGNED NOT NULL,
      `target_type` varchar(30) NOT NULL,
      `id_target_option` int(10) UNSIGNED NOT NULL,
      `type_value` varchar(30) NOT NULL,
      `formula` TEXT NULL,
      PRIMARY KEY (`id_configurator_step_filter`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
