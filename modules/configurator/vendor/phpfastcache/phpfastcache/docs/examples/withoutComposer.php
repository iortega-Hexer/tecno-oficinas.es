<?php
/**
 *
 * This file is part of phpFastCache.
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the docs/CREDITS.txt file.
 *
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> https://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 *
 */

use Phpfastcache\CacheManager;

/**
 * You don't use composer ?
 * Fine! PhpFastCache provides
 * his own autoloader.
 */
require __DIR__ . '/../src/autoload.php';

$InstanceCache = CacheManager::getInstance('memcache');

/**
 * Try to get $products from Caching First
 * product_page is "identity keyword";
 */
$key = "product_page";
$CachedString = $InstanceCache->getItem($key);

if (is_null($CachedString->get())) {
    //$CachedString = "APC Cache --> Cache Enabled --> Well done !";
    // Write products to Cache in 5 seconds with same keyword
    $CachedString->set("Memcache Cache --> Cache Enabled --> Well done !")->expiresAfter(5);
    $InstanceCache->save($CachedString);

    echo "FIRST LOAD // WROTE OBJECT TO CACHE // RELOAD THE PAGE AND SEE // ";
    echo $CachedString->get();

} else {
    echo "READ FROM CACHE // ";
    echo $CachedString->get();
}

echo '<br /><br /><a href="/">Back to index</a>&nbsp;--&nbsp;<a href="./' . basename(__FILE__) . '">Reload</a>';
