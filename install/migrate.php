<?php
require('../boot.php');

require_once(xConfig::get('PATH','ADM') . 'adm.class.php');
use Ifsnop\Mysqldump as IMysqldump;
use X4\Classes\Install;
use X4\Classes\PDOImporter;
use X4\Classes\XRegistry;

session_start(); 
header('Content-Type: text/html; charset=utf-8');


$importer=new PDOImporter();
$importer::dropTables(XRegistry::get('XPDO'));
$importer->importSQL(PATH_.'sql/data.sql',XRegistry::get('XPDO'));
    
$install=new Install();
$install->transformDomains(array('lifecell.bi'=>$_SERVER['SERVER_ADDR']));
$install->runModuleInstallers();

$adm=new AdminPanel();
$adm->clearCache(true);
echo "Импорт успешно завершен";
