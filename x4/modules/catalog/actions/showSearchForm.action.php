<?php

use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;


class showSearchFormAction extends xAction

{
    var $fields = array();
    var $assemblyPoint;
    var $potencialObjects = array();
    var $lastRelativeSku = array();
    var $selectSku;
    var $selectObject;

    public function getSearchFormFields($formId)
    {
        if ($fields = $this->_commonObj->_searchForms->selectStruct('*')->selectParams('*')->childs($formId)->run()) {
            $this->fields = array_merge($this->fields, $fields);
        }
    }

    public function processSearchFields($path, $searchProperty)
    {
        if ($objectFormHolders = $this->_tree->dropQuery()->selectStruct(array(
            'id'
        ))->selectParams('*')->where(array(
            $searchProperty, '<>', ''
        ), array('@id', '=', $path
        ))->run()
        ) {
            foreach ($objectFormHolders as $holder) {
                $this->getSearchFormFields($holder['params'][$searchProperty]);

                if (!empty($this->fields) && !$this->assemblyPoint) {
                    $this->assemblyPoint = $holder['id'];
                }
            }
        }

        if ($this->fields) {
            $priorityOrder = XARRAY::arrToLev($this->fields, 'id', 'params', 'priority');
            $fields = XARRAY::arrToKeyArr($this->fields, 'id', 'params');

            arsort($priorityOrder);
            $sortedFields = XARRAY::sortArrayByArray($fields, array_keys($priorityOrder));

            return $sortedFields;
        }
    }

    public function fetchProperties($propertySet, $property)
    {

        if ($pset = $this->_commonObj->findPsetByName($propertySet)) {
            $id = array_search($propertySet, $this->_commonObj->psetIdToNameStorage);
            if ($this->_commonObj->psetInfoStorage[$id]['params']['isSKU']) {
                $this->selectSku = true;
                $pset[$property]['isSKU'] = true;
            } else {
                $this->selectObject = true;
            }

            return $pset[$property];
        }
    }

    public function fetchRelativeObects()
    {


        $tree = $this->_tree;

        $values = $tree->selectStruct(array('id'))->childs($this->assemblyPoint)->format('valval', 'id', 'id')->run();

        if ($this->selectObject && $values)
        {
            $this->relativeObjects = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
                '@id',
                '=',
                $values
            ))->run();
        }


        if ($this->selectSku && $values) {
            $this->relativeSku = $this->_commonObj->findRelativeSku($values, true);
            $this->lastRelativeSku = $this->_commonObj->lastRelativeSku;
        }

    }

    public function renderLogicItem($filterItem)
    {

        $stack = array('comparsion' => $filterItem['field']['comparsionType'],
            'type' => $filterItem['field']['propertyData']['params']['type'],
            'propertySet' => $filterItem['field']['propertySet'],
            'property' => $filterItem['field']['property']
        );

        $stackLength = count($stack);

        for ($i = 0; $i < $stackLength; $i++) {
            $path = implode('-', $stack);

            if ($this->_TMS->isSectionDefined($path)) {
                $this->_TMS->addMassReplace($path, $filterItem);
                return $this->_TMS->parseSection($path);
            }

            array_pop($stack);
        }

    }


    private function markerFilter($filterEq)
    {

        array_walk($filterEq, function (&$item) {
            $item = md5(print_r($item, true));

        });
        return $filterEq;
    }

    public function onCacheRead($params)
    {

        $this->compareFilterState();
        return $params['cache']['callResult'];
    }

    public function compareFilterState()
    {

        static $call;

        if (isset($call)) return;

        $call = true;
        if (isset($_GET['f']['equal'])) {
            $fEqual = $this->markerFilter($_GET['f']['equal']);
        } else {
            $fEqual = array();
        }
        if (isset($_GET['f']['like'])) {
            $fLike = $this->markerFilter($_GET['f']['like']);
        } else {
            $fLike = array();
        }
        if (isset($_GET['s']['equal'])) {
            $fEqual = $this->markerFilter($_GET['s']['equal']);
        } else {
            $sEqual = array();
        }
        if (isset($_GET['s']['like'])) {
            $sLike = $this->markerFilter($_GET['s']['like']);
        } else {
            $sLike = array();
        }


        $compareArray = array_merge($fEqual, $sEqual, $sLike, $fLike);

        if (!isset($_SESSION['cacheable']['lastFilter'])) {
            $_SESSION['cacheable']['lastFilter'] = $compareArray;

        } else {

            if (count($compareArray) > 0) {

                foreach ($compareArray as $key => $hash) {
                    if ($_SESSION['cacheable']['lastFilter'][$key] != $hash) {
                        $this->lastChanged = $key;

                    }
                }

                $_SESSION['cachable']['lastFilter'] = $compareArray;
            }

        }

    }

    public function potentialStackCreate($relative, $requestFilter, $param = 'id')
    {
        foreach ($relative as &$object) {
            if (!empty($requestFilter['equal'])) {
                foreach ($requestFilter['equal'] as $property => $eq) {

                    if (!empty($object['params'][$property])) {
                        foreach ($eq as $val) {
                            if ($object['params'][$property] == $val) {
                                $this->potencialObjects[] = $object[$param];
                            }
                        }

                    }


                }
            }

            if (!empty($requestFilter['like'])) {
                foreach ($requestFilter['like'] as $property => $eq) {

                    if (!empty($object['params'][$property])) {
                        foreach ($eq as $val) {
                            if (strstr($object['params'][$property], '"' . $val . '"')) {
                                $this->potencialObjects[] = $object[$param];
                            }
                        }

                    }
                }
            }
        }


    }

    public function potentialCalculation()
    {

        $requestFilter = $this->filter;


        if (!empty($requestFilter)) {

            $relative = $this->relativeObjects;

            if (!empty($relative) && isset($requestFilter['f'])) {
                $this->potentialStackCreate($relative, $requestFilter['f']);
            }

            $relative = $this->lastRelativeSku;

            if (!empty($relative) && isset($requestFilter['s'])) {

                $this->potentialStackCreate($relative, $requestFilter['s'], 'netid');
            }

        }

    }

    public function run($params)
    {

		
		
        $this->selectSku = false;

        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        if($pInfo['requestAction']=='search'&&!$params['params']['applyOnlySearchResults']){
            return;
        }
        
		//$params['params']['applyOnlyInListing']=1;
		
        if ($pInfo['requestActionSub'] == 'showObject') {
            return;
        }

        if ($params['params']['pointNode']) {
            $pointNode = $this->_tree->getNodeInfo($params['params']['pointNode']);

        } else {

            $pointNode = $this->currentShowNode;
            $useDynamicAssemblyPoint = true;
        }


        if ($pointNode) {

            $this->loadModuleTemplate($params['params']['SearchTemplate']);

            $fullPath = $pointNode['path'];
            $fullPath[] = $pointNode['id'];


            if (!$sortedSearchFields = $this->processSearchFields($fullPath, $params['params']['searchProperty'])) return;

            foreach ($sortedSearchFields as $propertyKey => &$field) {

                if (!$field['propertyData'] = $this->fetchProperties($field['propertySet'], $field['property'])) {
                    unset($sortedSearchFields[$propertyKey]);
                }
            }

            if ($useDynamicAssemblyPoint) {

                $this->assemblyPoint = $pointNode['id'];
            }


            $this->fetchRelativeObects();

            /*create matrix*/
            $htmlArray = [];
            $destination = $this->createPageDestination($params['params']['DestinationPage']);


            if ($params['params']['useFixedLink']) {

                $outerLink = $this->_commonObj->buildLink($this->assemblyPoint, $params['params']['DestinationPage']);
            }

            $this->compareFilterState();
            $this->potencialObjects = null;
            $this->potentialCalculation();
            $this->fullNodeIntersection = $this->potencialObjects;

            foreach ($sortedSearchFields as $fieldData) {

                if ($comparsion = $this->_commonObj->comparsionTypes[$fieldData['comparsionType']]) {
                    if ($fieldData['propertyData']['isSKU']) {
                        $objects = $this->relativeSku;

                    } else {

                        $objects = $this->relativeObjects;
                    }


                    $pth = $fieldData['propertySet'] . '.' . $fieldData['property'] . $fieldData['sort'];

                    if (isset($fieldData['propertyData']['isSKU']) && $fieldData['propertyData']['isSKU']) {
                        $gpth = $fieldData['property'];
                    } else {
                        $gpth = $fieldData['propertySet'] . '.' . $fieldData['property'];
                    }

                    $outerParams = array('outerLink' => $outerLink, 'lastChanged' => $this->lastChanged, 'sortActive' => $params['params']['sortActive']);


                    $hyperMatrix[$pth] = array('filterValues' => $comparsion->handleProcessing($fieldData, $objects, $gpth, $outerParams), 'field' => $fieldData);

                    if (!empty($hyperMatrix[$pth]['filterValues'])) {
                        $htmlArray[$pth] = $this->renderLogicItem($hyperMatrix[$pth]);
                    }


                }
            }


            $this->_TMS->addMassReplace('showSearchForm', array('request' => $params['request'], 'fieldsArray' => $htmlArray,'matrix'=>$hyperMatrix, 'fields' => implode('', $htmlArray)));
            return $this->_TMS->parseSection('showSearchForm');


        }
    }
}

