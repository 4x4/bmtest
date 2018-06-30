<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showKeyWordSearchAction extends xAction
{
    public $result;
    public $searchDeep = 5;

    public function __construct()
    {
        parent::__construct('catalog');
        XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.xfront', array(
            'getKeywordSearch'
        ), $this);
    }

    public function run($params)
    {

        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();
        $module = XRegistry::get('pagesFront')->_commonObj->getModuleByAction($params['params']['DestinationPage'], 'showCatalogServer');
        $destination = $this->createPageDestination($params['params']['DestinationPage']);
        $this->loadModuleTemplate($params['params']['Template']);
        $this->_TMS->addMassReplace('showKeyWordSearch', array(
            'name' => 'f[like][Base]',
            'action' => $destination . '/~search/',
            'moduleId' => $params['base']['moduleId']
        ));
        return $this->_TMS->parseSection('showKeyWordSearch');
    }

    //ajax call   
    public function getKeywordSearch($params)
    {
        $pages = xCore::loadCommonClass('pages');
        $action = $pages->_tree->getNodeInfo($params['moduleId']);
        $moduleCatalog = $pages->getModuleByAction($action['params']['DestinationPage'], 'showCatalogServer');
        $this->loadModuleTemplate($action['params']['Template']);


        if (is_array($action) && $params['keyword']) {
            $result = $this->_tree->selectParams('*')->selectStruct('*')->getBasicPath('/', true, $moduleCatalog['params']['showBasicPointId'])->where(array(
                'Base',
                'LIKE',
                '%' . $params['keyword'] . '%'
            ))->format('keyval', 'id')->limit(0, $action['params']['smartOnPage']);

            if (!empty($action['params']['showGroupId'])) {
                $result->childs((int)$action['params']['showGroupId'], $this->searchDeep);
            }

            $result = $result->run();


            $resultTemp = $this->_EVM->fire('catalog:showKeyWordSearch.after', array('instance' => $this, 'action' => $action, 'moduleCatalog' => $moduleCatalog, 'result' => $result, 'params' => $params));

            if (!empty($resultTemp)) {
                $result = $resultTemp;
            }


            if (!empty($result)) {
                $destination = $this->createPageDestination($action['params']['DestinationPage']);
                $resultPSG = $this->_commonObj->convertToPSGAll($result, array(
                    'doNotExtractSKU' => false,
                    'serverPageDestination' => $destination
                ));

                $itemsCount = 0;

                if ($action['params']['groupByCategories']) {
                    $ancestors = array();
                    if (!$action['params']['groupOffset']) {
                        $action['params']['groupOffset'] = 1;
                    }

                    foreach ($result as $item) {
                        $pathLength = count($item['path']) - $action['params']['groupOffset'];
                        $ancestors[] = $item['path'][$pathLength];
                    }

                    if (!empty($ancestors)) {
                        $ancestors = array_unique($ancestors);
                        $resultAncestors = $this->_tree->selectParams('*')->selectStruct('*')->getBasicPath('/', true, $moduleCatalog['params']['showBasicPointId'])->where(array(
                            '@id',
                            '=',
                            $ancestors
                        ))->format('keyval', 'id')->run();
                        $resultAncestorsPSG = $this->_commonObj->convertToPSGAll($resultAncestors, array(
                            'serverPageDestination' => $destination
                        ));
                        //counting in every ancestor
                        foreach ($ancestors as $ancestor) {
                            $ancestorCounts[$ancestor] = $this->_tree->selectCount()->childs($ancestor, ($action['params']['groupOffset'] + 1))->where(array(
                                'Base',
                                'like',
                                '%' . $params['keyword'] . '%'
                            ))->run();
                            $resultAncestorsPSG[$ancestor]['_main']['link'] .= '/~search/?f[like][Base]=' . $params['keyword'];
                        }
                        foreach ($resultPSG as $item) {
                            $pathLength = count($item['_main']['path']) - $action['params']['groupOffset'];
                            $dataResult[$item['_main']['path'][$pathLength]]['ancestor'] = $resultAncestorsPSG[$item['_main']['path'][$pathLength]];
                            $dataResult[$item['_main']['path'][$pathLength]]['items'][] = $item;
                            $dataResult[$item['_main']['path'][$pathLength]]['count'] = $ancestorCounts[$item['_main']['path'][$pathLength]];
                            $itemsCount++;
                        }
                    }
                } else {
                    $dataResult = $resultPSG;
                }

                $this->_TMS->addMassReplace('smartResults', array(
                    'objects' => $dataResult,
                    'allResultsLink' => $destination . '/~search/?f[like][Base]=' . $params['keyword'],
                    'objectsCount' => $itemsCount
                ));
                $this->result['html'] = $this->_TMS->parseSection('smartResults');
            } else {
                $this->result['html'] = $this->_TMS->parseSection('noResults');
            }
        }
    }
}

