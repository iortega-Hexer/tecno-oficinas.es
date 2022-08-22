<?php
/**
 * 2007-2019 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
 
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
$languages = Language::getLanguages(true);
if($languages)
{
    $xml ='<?xml version="1.0" encoding="UTF-8"?>'."\n";;
    $xml .='<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";;
        foreach($languages as $language)
        {
            $xml .='<sitemap>'."\n";
                $xml .='<loc>'.Context::getContext()->link->getModuleLink('ybc_blog','sitemap',array(),null,$language['id_lang']).'</loc>'."\n";
                $xml .'<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
            $xml .='</sitemap>'."\n";
        }   
    $xml .='</sitemapindex>';
    if (ob_get_length() > 0) {
        ob_end_clean();
    }
   header("Content-Type: application/xml; charset=ISO-8859-1");
   mb_internal_encoding('UTF-8');
   die(utf8_encode($xml));
}