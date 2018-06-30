<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XTreeEngine;

require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.catgroup.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.catobj.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.propertyset.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.propertysetgroup.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.searchelement.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.searchform.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.backObjects.urltransform.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/back/catalog.importData.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/catalog.filterIndexer.class.php');

class catalogBack extends xModuleBack
{

    use _CATOBJ, _CATGROUP, _PROPERTYSET, _PROPERTYSETGROUP, _SEARCHELEMENT, _SEARCHFORM, _URLTRANSFORM, importData;

    public $lastCopied = array();

    var $indexMoveStep = 500;

    public function __construct()
    {

        parent::__construct(__CLASS__);
        XRegistry::get('EVM')->on('module.' . $this->_moduleName . '.back:afterCopyObj', 'onCopiedObjects', $this);
    }


    public function getIndexingParams($params)
    {
        $this->result['items'] = $this->_tree->selectCount()->where(array('@obj_type', '=', '_CATOBJ'))->childs($params['id'])->run();
    }




    public function fastIndexing($params)
    {

        if (!empty($params['IndexParams'])) {
            $indexParams = explode(',', $params['IndexParams']);
            $skuExtract = array('doNotExtractSKU' => true);
        }

        if (!empty($params['IndexParams'])) {
            $indexParamsSku = explode(',', $params['IndexParamsSku']);
            $skuExtract = array('doNotExtractSKU' => false);
        }

        if (empty($params['start'])) $params['start'] = 0;

        $this->result=$this->_commonObj->fastIndexing($params['id'],$indexParams,$params['start'],$this->indexMoveStep,$skuExtract,$indexParamsSku,$params['isFullIndex']);

    }


    public function filterIndexes($params)
    {
        $indexer=new filterIndexer();
        $indexer->index();
        return new okResult();
    }

    public function onCopiedObjects($params)
    {

        $tree = $params['data']['tree'];
        if ($tree->treeStructName == '_tree_catalog_container_struct') {
            $this->lastCopied = $this->lastCopied + $params['data']['newToOld'];

        }

    }


    public function propertySetGroupAnalysis($ancestor)
    {
        if ($psgList = $this->_tree->selectParams(array(
            'PropertySetGroup'
        ))->childs($ancestor, 1)->format('valparams', 'id', 'PropertySetGroup')->run()
        ) {
            $psgList = array_unique($psgList);
            $emSet = array_shift($psgList);

            $intersected = $this->_commonObj->getPropertyGroupSerialized($emSet);

            if (!empty($psgList)) {
                foreach ($psgList as $psg) {
                    $psgData = $this->_commonObj->getPropertyGroupSerialized($psg);
                    $intersected['sets'] = array_intersect_key($intersected['sets'], $psgData['sets']);
                }
                $intersected['setsInfo'] = array_intersect_key($psgData['setsInfo'], $intersected['sets']);
            }

            return $intersected;

        }

    }

    public function getImportData($params)
    {
        $this->result['importData']['objectType'] = $this->result['importData']['categoryType'] = $this->getPsetsGroupList();

    }


    public function getStockData($objectId)
    {
        try {
            $ishop = xCore::moduleFactory('ishop.back');
            $id = $ishop->_commonObj->getBranchId('STORE');
            $items = $ishop->_tree->selectStruct('*')->selectParams('*')->childs($id, 1)->run();


        } catch (Exception $e) {
            return false;
        }

    }



    public function catGroupList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));


        $columns = array(
            'obj_type' => array(
                'transformList' => array(
                    '_CATOBJ' => 'leaf.gif',
                    '_CATGROUP' => 'folder.gif'
                )
            ),
            'id' => array(),
            'disabled' => array(),
            '>Name' => array()
        );

        $PSGAnalysed = $this->propertySetGroupAnalysis($params['id']);
        $common = $this->_commonObj;
        if (isset($PSGAnalysed)) {
            foreach ($PSGAnalysed['sets'] as $setName => $set) {
                if (isset($set)) {
                    foreach ($set as $propertyName => $property) {
                        $keyPath = $setName . '.' . $propertyName;

                        if ($property['params']['isListingReady']) {
                            $propertyObject = $common->getPropertyType($property['params']['type']);

                            $columnName = $setName . '.' . $propertyName;


                            $columns['>' . $columnName] = array();

                            $columns = $propertyObject->onListingPrepare($columns, $columnName);

                            $columnInfo[$keyPath] = $property['params'];
                        }
                    }
                }
            }
        }


        $opt = array(
            'showNodesWithObjType' => array(
                '_CATOBJ',
                '_CATGROUP'
            ),
            'columns' => $columns,
            'onRecord' => function ($extvals) use ($columnInfo, $common) {
                foreach ($columnInfo as $name => $clmn) {
                    $propertyObject = $common->getPropertyType($clmn['type']);
                    $extvals[$name] = $propertyObject->onListingView($extvals[$name], $name, $clmn, $extvals);
                }

                return $extvals;

            },
            'zeroLead' => true,
            'onPage' => $params['onPage']


        );


        $source->setOptions($opt);


        $this->result = $source->createView($params['id'], $params['page'], 1);
        $this->result['columnsInfo'] = $columnInfo;


    }


    public function skuSetAnalysis($id)
    {

        $node = $this->_commonObj->_sku->getNodeInfo($id);
        $psetId = $this->_commonObj->_propertySetsTree->selectStruct(array('id'))->where(array('@basic', '=', $node['basic']))->singleResult()->run();

        if ($properties = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams('*')->childs($psetId['id'], 2)->run()) {

            $this->skuSetNode = $node;
            return $properties;

        }


    }

    public function changeAncestorGridSku($params)
    {
        $params['tree'] = $this->_commonObj->_sku;

        $this->changeAncestorGrid($params);
    }


    public function getObjectBySku($params)
    {
        $node=$this->_commonObj->_sku->getNodeStruct($params['kid']);
        $this->result['netid']=$node['netid'];

    }

    public function skuGroupList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_sku
        ));


        $columns = array(
            'obj_type' => array(
                'transformList' => array(
                    '_SKUOBJ' => 'leaf.gif',
                    '_SKUGROUP' => 'folder.gif'
                )
            ),
            'id' => array(),
            '>Name' => array()
        );

        if ($properties = $this->skuSetAnalysis($params['id'])) {

            $params['onPage'] = $this->_config['skuGroupListItemsPerPage'];

            foreach ($properties as $propertyName => $property) {
                $keyPath = $this->skuSetNode['basic'] . '.' . $property['basic'];

                if ($property['params']['isListingReady']) {
                    $columns['>' . $property['basic']] = array();
                    $columnInfo[$keyPath] = $property['params'];
                }

            }

        }

        $common = $this->_commonObj;

        $opt = array(
            'showNodesWithObjType' => array(
                '_SKUOBJ',
                '_SKUGROUP'
            ),
            'columns' => $columns,
            'onRecord' => function ($extvals) use ($columnInfo, $common) {

                foreach ($columnInfo as $name => $clmn) {
                    $propertyObject = $common->getPropertyType($clmn['type']);
                    $extvals[$name] = $propertyObject->onListingView($extvals[$name], $name, $clmn);
                }

                return $extvals;

            },
            'onPage' => $params['onPage']


        );

        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);
        $this->result['columnsInfo'] = $columnInfo;


    }


    public function propertyLinksList($params)
    {

        $this->propertySetsList(false);
        $sets = $this->result;

        if ($params['id'] && $links = $this->_commonObj->_propertySetsTreeGroup->selectStruct(array(
                'basic'
            ))->where(array(
                '@ancestor',
                '=',
                $params['id']
            ))->run()
        ) {

            foreach ($links as $link) {
                if ($row = $sets['data_set']['rows'][$link['basic']]) {
                    $this->result['propertyLinksList']['data_set']['rows']['0' . $link['basic']] = $row;

                    unset($sets['data_set']['rows'][$link['basic']]);

                }
            }

        }

        unset($this->result['data_set']);
        $this->result['propertySetsList'] = $sets;

    }


    public function treeDynamicXLSObjSKU($params)
    {
        $node = $this->_tree->getNodeStruct($params['id']);

        if ($node['obj_type'] == '_CATOBJ') {

            $resultBasic = $this->_commonObj->_sku->selectParams(array('Name'))->selectStruct(array
            (
                'id',
                'basic',
                'obj_type'
            ))->where(array
            (
                '@netid',
                '=',
                $params['id']
            ))->format('keyval', 'id')->run();


            $this->result['data_set'] = Common::gridFormatFromTree($resultBasic, array('Name', 'id'), true);


        } else {


            $source = Common::classesFactory('TreeJsonSource', array(
                $this->_tree
            ));
            $opt = array(
                'imagesIcon' => array(
                    '_CATGROUP' => 'folder.gif'
                ),
                'gridFormat' => true,
                'showNodesAsParents' => array('_CATOBJ'),
                'showNodesWithObjType' => array(
                    '_ROOT',
                    '_CATGROUP',
                    '_CATOBJ'

                ),

                'columns' => array(
                    '>Name' => array(),
                    'id' => array()
                )
            );


            $source->setOptions($opt);
            $this->result = $source->createView($params['id']);

        }

    }


    public function treeDynamicXLSsku($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_sku
        ));
        $opt = array(
            'imagesIcon' => array(
                '_SKUGROUP' => 'folder.gif'
            ),
            'zeroLead' => true,
            'gridFormat' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                '_SKUGROUP'

            ),

            'endLeafs' => array(
                '_SKUOBJ'
            ),
            'columns' => array(
                '>Name' => array(),
                'id' => array()
            )
        );
        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'imagesIcon' => array(
                '_CATGROUP' => 'folder.gif'
            ),
            'gridFormat' => true,
            'zeroLead' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                '_CATGROUP'
                //   '_CATOBJ'
            ),
            'endLeafs' => array(
                '_CATOBJ'
            ),
            'columns' => array(
                '>Name' => array(),
                'id' => array()
            )
        );

        if ($params['getObjects']) $opt['showNodesWithObjType'][] = '_CATOBJ';


        $source->setOptions($opt);
        $this->result = $source->createView((int)$params['id']);
    }


    public function treeDynamicXLSPosition($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'imagesIcon' => array(
                '_CATGROUP' => 'folder.gif'
            ),
            'gridFormat' => true,
            'zeroLead' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                '_CATGROUP'
                //   '_CATOBJ'
            ),
            'endLeafs' => array(
                '_CATOBJ'
            ),
            'columns' => array(
                '>Name' => array(),
                'id' => array(),
                'rate' => array()
            )
        );

        if ($params['getObjects']) $opt['showNodesWithObjType'][] = '_CATOBJ';


        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }


    public function getSkuLinkList($selected = null)
    {
        $plist = $this->_commonObj->_sku->selectParams(array(
            'Name'
        ))->childs(1, 1)->format('valparams', 'id', 'Name')->run();
        return XHTML::arrayToXoadSelectOptions($plist, $selected, true);
    }


    private function objParamConverter($catObjData)
    {
        $paramSet = array();

        foreach ($catObjData as $objParam => $objVal) {
            if (is_array($objVal)) {
                if ($lined = XARRAY::convertArrayToDots($objParam, $objVal, '.'))
                    $paramSet = array_merge($paramSet, $lined);
            } else {
                $paramSet[$objParam] = $objVal;
            }
        }

        return $paramSet;

    }


    public function skuInit($skuData, $skuLink, $objId)
    {

        $ids = array_keys($skuData);

        if ($oldNodes = $this->_commonObj->_sku->selectStruct(array('id'))->where(array('@netid', '=', $objId))->format('valval', 'id', 'id')->run()) {
            $delete = array_diff($oldNodes, $ids);

            $this->_commonObj->_sku->delete()->where(array('@id', '=', $delete))->run();
        }


        if ($nodeExist = array_intersect($ids, $oldNodes)) {
            foreach ($nodeExist as $node) {
                $this->_commonObj->_sku->reInitTreeObj($node, '%SAME%', $skuData[$node], '_SKUOBJ', $objId);
                unset($skuData[$node]);
            }
        }

        if (count($skuData)) {
            foreach ($skuData as $skuId => $sku) {
                $skuTransformId[$skuId] = $this->_commonObj->_sku->initTreeObj($skuLink, '%SAMEASID%', '_SKUOBJ', $sku, $objId);
            }

            return $skuTransformId;
        }


    }


    public function getPsetsList($selected = null, $asBasic = false)
    {
        if ($asBasic) {
            $format = 'basic';
        } else {
            $format = 'id';
        }
        $plist = $this->_commonObj->_propertySetsTree->selectStruct(array(
            'basic'
        ))->selectParams('alias')->childs(1, 1)->format('valparams', $format, 'alias')->run();

        return XHTML::arrayToXoadSelectOptions($plist, $selected, true);
    }


    public function getPsetsGroupList($selected = null, $asBasic = false)
    {

        if ($asBasic) {
            $format = 'basic';
        } else {
            $format = 'id';
        }
        $plist = $this->_commonObj->_propertySetsTreeGroup->selectStruct(array(
            'basic'
        ))->selectParams('alias')->childs(1, 1)->format('valparams', $format, 'alias')->run();
        
        return XHTML::arrayToXoadSelectOptions($plist, $selected, true);
    }
    
    public function getPsetsGroupListFront($params)
    {
        
        $this->result['psetsGroupList']=$this->getPsetsGroupList($params['selected']); 
    }


    public function typeHandlerProccessOnSave($psg, $paramSet)
    {
        if ($psg = $this->_commonObj->getPropertyGroupSerialized($psg)) {
            foreach ($psg['sets'] as $setName => $set) {
                foreach ($set as $propertyName => $property) {
                    $propertyPath = $setName . '.' . $propertyName;
                    if ($ptype = $this->_commonObj->getPropertyType($property['params']['type'])) {
                        $paramSet[$propertyPath] = $ptype->handleTypeOnSave($property, $paramSet[$propertyPath], $paramSet, $propertyPath);
                    }

                }

            }

        }

        return $paramSet;

    }


    public function typeHandlerProccess($psg, $object)
    {


        if ($psg = $this->_commonObj->getPropertyGroupSerialized($psg)) {
            foreach ($psg['sets'] as $setName => $set) {
                foreach ($set as $propertyName => $property) {
                    $propertyPath = $setName . '.' . $propertyName;

                    if ($ptype = $this->_commonObj->getPropertyType($property['params']['type'])) {
                        $object['params'][$propertyPath] = $ptype->handleTypeOnEdit($property, $object['params'][$propertyPath], $object);
                    }
                }

            }

        }

        return $object;

    }


    public function copySku()
    {

        if ($this->lastCopied) {

            $relativeSku = $this->_commonObj->_sku->selectStruct('*')->selectParams('*')->where(array('@netid', '=', array_keys($this->lastCopied)))->format('keyval', 'id')->run();


            foreach ($relativeSku as $sku) {

                if ($sid = $this->lastCopied[$sku['netid']]['id']) {
                    $this->_commonObj->_sku->initTreeObj($sku['ancestor'], '%SAMEASID%', '_SKUOBJ', $sku['params'], $sid);
                }
            }

        }


    }

    public function iterateImportJson($node, $ancestor, $tContext, $extdata)
    {

        if ($node['obj_type'] == '_CATGROUP') {
            $ancestor = $this->importTransitions[$ancestor];
            $this->importTransitions[$node['id']] = $this->_tree->initTreeObj($ancestor, $node['basic'], '_CATGROUP', $node['params']);

        } else {

            $ancestor = $this->importTransitions[$ancestor];
            $newId = $this->_tree->initTreeObj($ancestor, $node['basic'], '_CATOBJ', $node['params']);

            if ($this->importJsonSku[$node['id']]) {

                foreach ($this->importJsonSku[$node['id']] as $sku) {
                    $this->_commonObj->_sku->initTreeObj($sku['ancestor'], '%SAMEASID%', '_SKUOBJ', $sku['params'], $newId);
                }
            }


        }

    }

    public function importDataJson($params)
    {
        $path = xConfig::get('PATH', 'MEDIA') . 'import/';

        if (XFILES::isWritable($path)) {

            $fileData = file_get_contents($path . $params['name']);
            $data = json_decode($fileData, true);
            $localTree = new X4\Classes\xteTree($data['categories'], $data['startNode']);
            $localTree->sortTreeItems();
            $this->importTransitions[$data['startNode']] = $params['id'];
            $this->importJsonSku = $data['sku'];
            $localTree->recursiveStep($data['startNode'], $this, 'iterateImportJson');

            $this->pushMessage('file ' . $path . $params['name'] . ' imported');

        } else {

            $this->pushError('folder ' . $path . $params['name'] . ' is-not-readable');
        }


    }

    public function exportDataJson($params)
    {

        $path = xConfig::get('PATH', 'MEDIA') . 'import/';

        if (XFILES::isWritable($path)) {

            $categories = $this->_tree->selectStruct('*')->selectParams('*')->childs((int)$params['id'])->asTree()->run();

            $data = $this->_tree->selectStruct('*')->childs((int)$params['id'])->where(array('@obj_type', '=', '_CATOBJ'))->format('valval', 'id', 'id')->run();


            $relativeSku = $this->_commonObj->_sku->selectStruct('*')->selectParams('*')->where(array('@netid', '=', $data))->run();

            if (!empty($relativeSku)) {
                foreach ($relativeSku as $skuObject) {
                    $skuGrouped[$skuObject['netid']][] = $skuObject;
                }
            }


            $data = json_encode(array('categories' => $categories->nodes, 'startNode' => (int)$params['id'], 'sku' => $skuGrouped));

            $filepath = $path . date('d-m-Y-H-i-s') . '.json';

            XFILES::fileWrite($filepath, $data);

            $this->pushMessage('file ' . $filepath . ' written');

        } else {

            $this->pushError('folder ' . $path . ' is-not-writable');
        }

    }


    public function copyCatObj($params)
    {

        $this->copyObj($params, $this->_tree);
        $this->copySku();
    }

    public function deleteSku($params)
    {
        $this->deleteObj($params, $this->_commonObj->_sku);
    }


    public function getPropertiesData()
    {

        if (isset($this->_commonObj->propertyTypes)) {
            foreach ($this->_commonObj->propertyTypes as $propObjectType => $propObject) {
                $templates[$propObjectType] = array(
                    'backOptionsTemplate' => $propObject->renderBackOptionsTemplate(),
                    'backTemplate' => $propObject->renderBackTemplate()
                );
            }
            $this->result['propertiesData'] = $templates;
        }
    }

    public function setSingleProperty($params)
    {
        
       if(!empty($params['id'])&&!empty($params['property']))
       {                  
           $this->_tree->writeNodeParam($params['id'], $params['property'],$params['value']);
           return new okResult();
       }
       return badResult();
    }

    public function getSkuForObject($objId)
    {
        $skuData = $this->_commonObj->_sku->selectStruct(array('id'))->selectParams('*')->where(array('@netid', '=', $objId))->format('valval', 'id', 'params')->run();

        $skuDataPrepared = array();
        foreach ($skuData as $skuIndex => $skuObject) {
            $skuDataPrepared['0' . $skuIndex] = $skuObject;
        }

        return $skuDataPrepared;
    }


    public function getPropertySetSKU($params)
    {

        $skuGroup = $this->_commonObj->_sku->getNodeInfo($params['setId']);
        $skuSet = $this->_commonObj->_propertySetsTree->selectStruct(array(
            'id'
        ))->where(array('@ancestor', '=', 1), array(
            '@basic',
            '=',
            $skuGroup['basic']
        ))->run();
        $setId = $skuSet[0]['id'];


        $allSets = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams('*')->childs($setId, 3)->asTree()->run();

        $setsInfoSend = array();

        if (isset($allSets)) {
            while (list($k, $v) = $allSets->fetch($setId)) {


                if ($options = $allSets->fetchArray($v['id'])) {
                    $options = end($options);
                    $v['options'] = $options['params'];

                }
                 

                if ($ptype = $this->_commonObj->getPropertyType($v['params']['type'])) {

                    $v = $ptype->handleTypeBack($v);

                }

                $setsData[$skuGroup['basic']][$v['basic']] = $v;
            }

        }
        $setsInfoSend[$skuGroup['basic']] = $skuGroup;


        $this->result['psetGroup'] = array(
            'sets' => $setsData,
            'setsInfo' => $setsInfoSend
        );


    }


    public function getPropertyGroup($params)
    {

        $psetGroup = $this->_commonObj->getPropertyGroupSerialized($params['psetGroupId']);

        if (isset($psetGroup['sets'])) {
            foreach ($psetGroup['sets'] as &$propertySet) {
                foreach ($propertySet as &$property) {
                    if ($ptype = $this->_commonObj->getPropertyType($property['params']['type'])) {

                        $property = $ptype->handleTypeBack($property,$params['objectData']);

                    }

                }
            }
        }

        $this->result['psetGroup'] = $psetGroup;
    }

    public function createRootSkuGroup($basic, $params)
    {

        return $objId = $this->_commonObj->_sku->initTreeObj(1, $basic, '_SKUGROUP', $params);

    }

    public function rebuildPropSetsSerialized($psetId)
    {
        if ($plist = $this->_commonObj->_propertySetsTreeGroup->selectStruct(array(
            'ancestor'
        ))->where(array(
            '@basic',
            '=',
            $psetId
        ))->run()
        ) {
            foreach ($plist as $groupNode) {
                $this->_commonObj->createPropertyGroupSerialized($groupNode['ancestor']);
            }

        }

    }


    public function onSearchInModule($params)
    {
        $params['word'] = urldecode($params['word']);

        $resultBasic = $this->_tree->selectParams(array('Name'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            '@basic',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();

        $resultName = $this->_tree->selectParams(array('Name'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            'Name',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();

        XARRAY::arrayMergePlus($resultBasic, $resultName, true);

        $this->result['searchResult'] = Common::gridFormatFromTree($resultBasic, array
        (
            'id',
            'obj_type',
            'Name',
            'basic'
        ));

    }


    public function getPsetListInitialData()
    {
        $this->result['data']['propertySet'] = $this->getPsetsList(null, true);
        $comparsions = $this->_commonObj->getComparsionTypes(null, true);
        $comparsions = array_keys($comparsions);
        $this->result['data']['comparsionType'] = XHTML::arrayToXoadSelectOptions(array_combine($comparsions, $comparsions), $selected, true);

    }


    public function onAction_showCatalogServer($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
            $dtc = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $params['data']['params']['showBasicPointId']))->run();
            $this->result['actionDataForm']['showBasicPoint'] = $dtc['paramPathValue'];


        }
        $this->result['actionDataForm']['objectTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showObject.html'));


        $this->result['actionDataForm']['secondaryAction'] = XHTML::arrayToXoadSelectOptions($this->_commonObj->getServerActionsFull($params['action']), $selected, true);
        $this->result['actionDataForm']['categoryTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['categoryTemplate'], array('.showCategory.html'));
    }


    public function onAction_showSmartSearchForm($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

        }

        $pages = xCore::loadCommonClass('pages');

        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer', $selected);

        $this->result['actionDataForm']['SearchTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showSmartSearchForm.html'));

    }


    public function onSlotModuleSave_showCatalogServer($params)
    {
        $pages = xCore::loadCommonClass('pages');
        $contentPageLink = $pages->createPagePath($params['data']['pageId'], true);
        $source = $contentPageLink . '/(?!~)(.*?)';
        $destination = $contentPageLink . '/~show$1/';
        $pages->createNewRoute($source, $destination);
    }


    public function onAction_showCatalogMenu($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
            $dtc = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $params['data']['params']['sourceCatGroupId']))->run();
            $this->result['actionDataForm']['sourceCatGroup'] = $dtc['paramPathValue'];
        }

        $pages = xCore::loadCommonClass('pages');

        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');

        $this->result['actionDataForm']['menuTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showCatalogMenu.html'));

    }


    public function onAction_showKeyWordSearch($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
            $dtc = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $params['data']['params']['showGroupId']))->run();
            $this->result['actionDataForm']['showGroup'] = $dtc['paramPathValue'];


        }

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showKeyWordSearch.html'));

    }


    public function onAction_showCategory($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
            $dtc = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', (int)$params['data']['params']['showGroupId']))->run();
            $this->result['actionDataForm']['showGroup'] = $dtc['paramPathValue'];


        }

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
        $this->result['actionDataForm']['categoryTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['categoryTemplate'], array('.showCategory.html'));

    }

    
     public function onAction_showObject($params)
    {
        if (isset($params['data']['params'])) 
        {
            $this->result['actionDataForm'] = $params['data']['params'];
            $dtc = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', (int)$params['data']['params']['showObjectId']))->run();
            $this->result['actionDataForm']['showObject'] = $dtc['paramPathValue'];

        }

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['objectTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showObject.html'));
                
    }


    public function onAction_showFilterApplyCatalog($params)
    {
        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showFilterApplyCatalog.html'));
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
    }


    public function onAction_showCompareServer($params)
    {
        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showCompareServer.html'));
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
    }


    public function onAction_showReactMenu($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

        }

        $pages = xCore::loadCommonClass('pages');

        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');

        $this->result['actionDataForm']['menuTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showCatalogMenu.html', '.showReactMenu.html'));

    }


    public function onAction_showCurrentSelectedFilters($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

        }

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showCurrentSelectedFilters.html'));


    }

    public function onAction_showSearchForm($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

        }

        $pages = xCore::loadCommonClass('pages');
        if ($searchFormsProperties = $this->_commonObj->_propertySetsTree->selectStruct(array('id', 'basic', 'ancestor'))->selectParams('*')->where(array('type', '=', 'searchForm'))->run()) {

            foreach ($searchFormsProperties as $sfprp) {
                $ancestor = $this->_commonObj->_propertySetsTree->getNodeInfo($sfprp['ancestor']);
                $searchFormsPropData[$ancestor['basic'] . '.' . $sfprp['basic']] = $sfprp['params']['alias'];
            }

        }

        $this->result['actionDataForm']['searchProperty'] = XHTML::arrayToXoadSelectOptions($searchFormsPropData, false, true);

        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');

        $this->result['actionDataForm']['SearchTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['objectTemplate'], array('.showSearchForm.html'));

    }


    public function getWidgetStat($params)
    {

        $stats['catobjs'] = $this->_tree->selectCount()->where(array('@obj_type', '=', '_CATOBJ'))->run();
        $stats['catgroups'] = $this->_tree->selectCount()->where(array('@obj_type', '=', '_CATGROUP'))->run();
        $stats['sku'] = $this->_commonObj->_sku->selectCount()->where(array('@obj_type', '=', '_SKUOBJ'))->run();
        $this->result['data'] = $stats;

    }


}
