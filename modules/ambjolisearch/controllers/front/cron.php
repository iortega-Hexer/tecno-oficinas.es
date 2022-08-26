<?php
/**
 *   AmbJoliSearch Module : Search for prestashop
 *
 *   @author    Ambris Informatique
 *   @copyright Copyright (c) 2013-2022 Ambris Informatique SARL
 *   @license   Commercial license
 *   @module     Advanced Search (AmbJoliSearch)
 *   @file       jolisearch.php
 *   @subject    main controller
 *   Support by mail: support@ambris.com
 */

require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/definitions.php';
require_once _PS_ROOT_DIR_ . '/modules/ambjolisearch/classes/AmbIndexation.php';

class AmbjolisearchcronModuleFrontController extends FrontController
{
    const TOKEN_CHECK_START_POS = 34;
    const TOKEN_CHECK_LENGTH = 8;

    public function init()
    {
        if (Tools::substr(
            _COOKIE_KEY_,
            static::TOKEN_CHECK_START_POS,
            static::TOKEN_CHECK_LENGTH
        ) !== Tools::getValue('token')) {
            die('Forbidden');
        }

        $indexation = new AmbIndexation(true, Tools::getvalue('step_size', 100));

        $indexation->token = Tools::substr(
            _COOKIE_KEY_,
            static::TOKEN_CHECK_START_POS,
            static::TOKEN_CHECK_LENGTH
        );

        $indexation->processProducts(false, (int) Tools::getValue('step', 0), (Bool)Tools::getValue('full', true));
    }
}
