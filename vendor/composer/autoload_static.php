<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'geoPHP' => __DIR__ . '/..' . '/phayes/geophp/geoPHP.inc',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525::$classMap;

        }, null, ClassLoader::class);
    }
}
