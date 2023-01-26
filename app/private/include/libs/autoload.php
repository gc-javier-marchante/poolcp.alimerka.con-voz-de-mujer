<?php
spl_autoload_register(function ($class) {
    $map = include('autoload.map.php');

    if (isset($map[$class])) {
        include_once($map[$class] . '');

        return true;
    }

    if (starts_with($class, 'App\\Callback\\')) {
        $callback_filename = MODELS_PATH . 'callbacks' . DIRECTORY_SEPARATOR . substr($class, strlen('App\\Callback\\')) . '.php';

        if (file_exists($callback_filename)) {
            include_once($callback_filename);

            return true;
        }
    }

    return false;
});

if (file_exists(INSTALL_PATH . 'vendor/autoload.php')) {
    include_once(INSTALL_PATH . 'vendor/autoload.php');
}

if (file_exists(INCLUDE_PATH . 'libs/autoload.php')) {
    include_once(INCLUDE_PATH . 'libs/autoload.php');
}
