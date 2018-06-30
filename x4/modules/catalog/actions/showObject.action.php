<?php


use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showObjectAction extends xAction
{

    public function run($params)
    {

     
        if(!empty($params['params']['showObjectId']))
        {
            $params['node']['id']=(int)$params['params']['showObjectId'];
        }
        
        $objectInfo = $this->_tree->selectParams('*')->getBasicPath('/', true, $params['params']['showBasicPointId'])->where(array
        (
            '@id',
            '=',
            $params['node']['id']
        ))->jsonDecode()->run();


        $objectInfo = $this->_commonObj->convertToPSG($objectInfo, array
        (
            'serverPageDestination' => $params['params']['destinationLink'],
            'getSku' => true
        ));

        $this->setDataCache('objectInfo', $objectInfo);

        $this->setSeoData($objectInfo);


        $eventResult = XRegistry::get('EVM')->fire($this->_moduleName . '.showObject:objectReady', array('object' => $objectInfo));

        if (isset($eventResult['object'])) {
            $objectInfo = $eventResult['object'];
        }

        $this->loadModuleTemplate($params['params']['objectTemplate']);

        $currentPage = XRegistry::get('TPA')->getCurrentPage();

        $categorLink = $this->_commonObj->buildLink($objectInfo['_main']['ancestor'], $currentPage['id']);

        $this->_TMS->addMassReplace('catalogObject', array('categoryLink' => $categorLink));

        $this->_TMS->addReplace('catalogObject', 'object', $objectInfo);


        return $this->_TMS->parseSection('catalogObject');
    }


    public function onCacheRead($action)
    {

        $dataCache = $action['cache']['cacheData'];
        $this->setSeoData($dataCache['objectInfo']);
        $this->fullNodeIntersection = $dataCache['fullNodeIntersection'];

        return $action['cache']['callResult'];

    }

}

