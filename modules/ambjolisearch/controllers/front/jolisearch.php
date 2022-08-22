<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2019 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced Search (AmbJoliSearch)
 *   @file       jolisearch.php
 *   @subject    main controller
 *   Support by mail: support@ambris.com
 */

// Load Search Providers for PrestaShop 1.7
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
    include_once(_PS_ROOT_DIR_ . '/modules/ambjolisearch/controllers/front/jolisearch-17.php');
} else {
    include_once(_PS_ROOT_DIR_ . '/modules/ambjolisearch/controllers/front/jolisearch-16.php');
}
