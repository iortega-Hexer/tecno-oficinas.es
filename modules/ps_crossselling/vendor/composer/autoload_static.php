<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit30b6dc3a5810d215d66c4d468282baaf
{
    public static $classMap = array (
        'Ps_Crossselling' => __DIR__ . '/../..' . '/ps_crossselling.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit30b6dc3a5810d215d66c4d468282baaf::$classMap;

        }, null, ClassLoader::class);
    }
}
