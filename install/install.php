<?php
require('../boot.php');
require_once(xConfig::get('PATH','ADM') . 'adm.class.php');
use Ifsnop\Mysqldump as IMysqldump;
use X4\Classes\Install;
use X4\Classes\PDOImporter;
use X4\Classes\XRegistry;
error_reporting(0);

session_start(); 

header('Content-Type: text/html; charset=utf-8');

$install=new Install();

function showDomains()
{    
    global $install;
    $domains=$install->getCurrentInstalledDomains();
    
    echo '<h3>Выберите домен источник</h3><form method="POST">';
    foreach($domains as $domain)
    {
        echo '<input name="domain" type="radio" value='.$domain['basic'].'>'.$domain['basic'] ."\r\n";            
    }
    echo '<br/><input type="submit"></form>';    
}

if($_POST['domain'])
{
    $install->transformDomains(array($_POST['domain']=>HTTP_HOST));
    $install->runModuleInstallers();

    $adm=new AdminPanel();
    $adm->clearCache(true);

    echo '<p>DOMAIN REINSTALLED '.$_POST['domain'].'</p>';
    echo '<p>CACHE  CLEARED</p>';

}
//$importer=new PDOImporter();
//$importer->importSQL(PATH_.'sql/migrate.sql',XRegistry::get('XPDO')); 

/*
try {
    $dump = new IMysqldump\Mysqldump('mysql:host='.xConfig::get('DB','DB_HOST').';dbname='.xConfig::get('DB','DB_NAME'), xConfig::get('DB','DB_USER'),xConfig::get('DB','DB_PASS'));
    $dump->start(PATH_.'sql/migrate.sql');    
} catch (\Exception $e) {
    echo 'mysqldump-php error: ' . $e->getMessage();
}
*/

showDomains();   






?>