<?php
define('INSTALL_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('CORE_PATH', INSTALL_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'gestycontrol' . DIRECTORY_SEPARATOR . 'gestymvc-cli' . DIRECTORY_SEPARATOR);
define('PRIVATE_PATH', INSTALL_PATH . 'app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', INSTALL_PATH);
define('LOGS_PATH', PRIVATE_PATH . '.logs' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', PRIVATE_PATH . '.cache' . DIRECTORY_SEPARATOR);
define('ENVIRONMENTS_PATH', PRIVATE_PATH . 'environments' . DIRECTORY_SEPARATOR);
define('INCLUDE_PATH', PRIVATE_PATH . 'include' . DIRECTORY_SEPARATOR);
define('DB_PATH', PRIVATE_PATH . 'migrations' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', PUBLIC_PATH);

include_once(CORE_PATH . 'GestyMVC.php');
GestyMVC::run();
