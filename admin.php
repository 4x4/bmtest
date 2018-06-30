<?php
session_start();
error_reporting(0);

use X4\Classes\XRegistry;

require_once('boot.php');
xConfig::set('GLOBAL', 'currentMode', 'back');
require_once(xConfig::get('PATH', 'ADM') . 'adm.class.php');


XRegistry::set('ADM', $adm = new AdminPanel());
XRegistry::get('EVM')->fire('AdminPanel:afterInit', array('instance' => $adm));
echo $adm->listen();
exit;    
