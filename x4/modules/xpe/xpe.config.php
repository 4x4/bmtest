<?php
if (class_exists('MongoDB\Driver\Manager')) {
	$driver='mongoDriver';
}elseif(class_exists('MongoClient')){
	$driver='mongo';
}else{	
	$driver=false;
}


xConfig::pushConfig(array(

    'iconClass' => 'i-data2',
    'admSortIndex' => 50,
	'disable'=>true,
    'defaultStorage' => $driver,
    //'defaultStorageConfig'=>array('db'=>'admin','collection'=>'test','host'=>'10.17.0.84:27017')
    'defaultStorageConfig' => array('db' => 'admin', 'collection' => 'test', 'host' => '127.0.0.1:27017')
));
