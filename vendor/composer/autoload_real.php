<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitde2bc8666583a40e6e167c5b4821290f
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitde2bc8666583a40e6e167c5b4821290f', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitde2bc8666583a40e6e167c5b4821290f', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitde2bc8666583a40e6e167c5b4821290f::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}