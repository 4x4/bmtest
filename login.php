<?php       

use X4\Classes\XRegistry;

error_reporting(0);

require('boot.php');
require(xConfig::get('PATH','ADM') . 'adm.class.php');
XRegistry::set('ADM',$adm=new AdminPanel());

session_start();              
@session_destroy();   
@session_start();   

$_SESSION['lang']=xConfig::get('GLOBAL','defaultLanguage');      
  
if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if($_POST['login']&&$_POST['password'])
        {   
                    if(xCore::loadCommonClass('users')->checkAndLoadUser($_POST['login'],$_POST['password']))
                    {
                        header('location: admin.php?');     
                        exit;
                    }
        }
        
        echo   $adm->showLogin(true);
        
    }else{
        
        echo   $adm->showLogin();    
    }
