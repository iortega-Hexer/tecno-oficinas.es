<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *    @author    Ambris Informatique
 *    @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *    @license   Commercial license
 *    @module     Advanced Search (AmbJoliSearch)
 *    @file        definitions.php
 *    @subject     core class Link decorator
 *    Support by mail: support@ambris.com
 */

require_once _PS_MODULE_DIR_ . 'ambjolisearch/classes/JoliLink.php';

if (version_compare(_PS_VERSION_, '1.5.5.0', '<')) {
    require_once _PS_MODULE_DIR_ . 'ambjolisearch/classes/JoliLink1550.php';
} elseif (version_compare(_PS_VERSION_, '1.6.0.10', '<')) {
    require_once _PS_MODULE_DIR_ . 'ambjolisearch/classes/JoliLink16010.php';
} elseif (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
    require_once _PS_MODULE_DIR_ . 'ambjolisearch/classes/JoliLink161.php';
} else {
    require_once _PS_MODULE_DIR_ . 'ambjolisearch/classes/JoliLink-1.7.php';
}
