<?php
error_reporting(0);
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');

use X4\Classes\XRegistry;
use X4\Classes\AltoRouter;
use X4\Classes\XApi;

require_once('boot.php');

XRegistry::get('EVM')->fire('apiBoot');
xConfig::set('GLOBAL','currentMode','api');
require_once(xConfig::get('PATH','ADM') . 'adm.class.php');

$router = new altoRouter();

$apiController=new XApi();

if(xConfig::get('GLOBAL','apiAuthEnabled'))
{

    $apiController->auth(xConfig::get('GLOBAL','apiBasicAuthLogin'),xConfig::get('GLOBAL','apiBasicAuthPassword'));
}
    
$router->map('GET|POST|DELETE|PATCH|PUT|HEAD|OPTIONS','/~api/[*:api]/[*:module]/[a:action]/[*:trailing]?','apiController#route' , 'api');

$router->map('GET', '/~api/[*:api]/[*:module]', 'apiController#document', 'apiDocs');

$match = $router->match();

if ($match === false) {
    
    $apiController->error500();
    echo "url error:\r\n";
    echo "should be /~api/[*:api]/[*:module]/[*:action]/[*:trailing] format";
    echo "\r\n";
    echo "<br/>";
    echo "use /~api/[*:api]/[*:module] for docs";
    die();

} else {
                        
    list( $controller, $action ) = explode( '#', $match['target'] );

    if ( method_exists ($apiController, $action))  
    {
        $result = call_user_func_array(array($apiController, $action), array($match['params']));
        echo $result;

    } else {

        $apiController->error500();
        echo "route is not defined\r\n";        
        die();
    }
}
