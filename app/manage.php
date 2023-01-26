<?php
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'paths.php');
include_once(CORE_PATH . 'GestyMVC.php');
GestyMVC::initialize();
GestyMVCCLI::run();