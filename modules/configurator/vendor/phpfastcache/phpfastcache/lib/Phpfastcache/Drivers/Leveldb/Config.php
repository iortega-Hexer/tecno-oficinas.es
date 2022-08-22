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

declare(strict_types=1);

namespace Phpfastcache\Drivers\Leveldb;

use Phpfastcache\Config\ConfigurationOption;

class Config extends ConfigurationOption
{
    /**
     * @var string
     */
    protected $htaccess = true;

    /**
     * @return string
     */
    public function getHtaccess(): string
    {
        return $this->htaccess;
    }

    /**
     * @param string $htaccess
     * @return self
     */
    public function setHtaccess(string $htaccess): self
    {
        $this->htaccess = $htaccess;
        return $this;
    }
}