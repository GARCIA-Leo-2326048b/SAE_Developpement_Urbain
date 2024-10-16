<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525
{
    public static $prefixLengthsPsr4 = array (
        'b' => 
        array (
            'blog\\' => 5,
        ),
        '_' => 
        array (
            '_assets\\config\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'blog\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/blog',
        ),
        '_assets\\config\\' => 
        array (
            0 => __DIR__ . '/../..' . '/_assets/config',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'geoPHP' => __DIR__ . '/..' . '/phayes/geophp/geoPHP.inc',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0c6f8a8f1b163c98dc2243c4ea3f9525::$classMap;

        }, null, ClassLoader::class);
    }
}