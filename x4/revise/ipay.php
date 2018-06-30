<?php
error_reporting(0);
require('boot.php');


include(PATH_ . 'x4/modules/ishop/paysystems/erip/erip.php');
//'cdktLKGG20sdm' - тест
//'cljd476FbHJDSR99jjswqMFRd852x' - прод
$salt= 	'cljd476FbHJDSR99jjswqMFRd852x';

/*$_REQUEST['XML']='<?xml version="1.0" encoding="windows-1251" ?>
<ServiceProvider_Request>
  <DateTime>20160415121050</DateTime>
  <Version>1</Version>
  <RequestType>ServiceInfo</RequestType>
  <PersonalAccount>2124</PersonalAccount>
  <Currency>933</Currency>
  <RequestId>42882</RequestId>
  <ServiceInfo>
    <Agent>369</Agent>
  </ServiceInfo>';

*/
Common::writeLog($_REQUEST['XML']);

$EP=new  eripPayment();
$EP->init($_REQUEST['XML'],$salt,'http://avex.by');
?>