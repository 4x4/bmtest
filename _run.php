<?php
11
error_reporting(0);
session_start();

use X4\Classes\XRegistry;

require('boot.php');

xCore::websiteLockerListener();

XRegistry::get('EVM')->fire('zero-boot');

xConfig::addToBranch('PATH',
    array('fullBaseUrlHost' => CHOST . $_SERVER['REQUEST_URI'],
        'fullBaseUrl' => $_SERVER['REQUEST_URI'],
        'baseUrl' => $_SERVER['REQUEST_URI']));

xConfig::set('GLOBAL', 'currentMode', 'front');
XRegistry::get('EVM')->fire('boot');
xCore::listenToXoad();
XRegistry::set('TPA', $TPA = new pageAgregator($generationTimeStart));
$TPA->setRenderMode(RENDERMODE);

echo $TPA->dispatcher();