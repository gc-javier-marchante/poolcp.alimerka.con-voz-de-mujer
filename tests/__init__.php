<?php
include_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'paths.php');
include_once(CORE_PATH . 'GestyMVC.php');
GestyMVC::initialize();

if (!defined('LANG')) {
    define('LANG', 'es');
}
