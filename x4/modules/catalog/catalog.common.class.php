<?php

use X4\Classes\XPDO;
use X4\Classes\XRegistry;
use X4\Classes\XTreeEngine;


require(xConfig::get('PATH', 'MODULES') . 'catalog/catalog.properties.class.php');
require(xConfig::get('PATH', 'MODULES') . 'catalog/catalog.comparsions.class.php');

class catalogCommon extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;
    public $propertyTypes = array();
    public $currentFilterParams = array();
    public $currentPSG;
    public $regularComparsion = array('from', 'to', 'pricefrom', 'priceto');
    private $currencyRebuildChunk = 500;

    public $nativeSelectObjectsFilters = array('nonequal', 'equal', 'ancestor', 'from', 'to', 'sort', 'like', 'rlike', 'lrlike', 'alike');
    public $selectObjectsFiltersAdd = array('pricefrom', 'priceto');

    public function getPropertyType($name)
    {
        return $this->propertyTypes[$name];
    }

    public function addPropertyType($name, $obj)
    {
        $this->propertyTypes[$name] = $obj;
    }

    public function addComparsionType($name, $obj)
    {
        $this->comparsionTypes[$name] = $obj;
    }

    public function __construct()
    {

        parent::__construct(__CLASS__);


        $this->_tree->setObject('_ROOT', array(
            'Name'
        ));
        $this->_tree->setObject('_CATGROUP', null, array(
            '_ROOT',
            '_CATGROUP'
        ));
        $this->_tree->setObject('_CATOBJ', null, array(
            '_CATGROUP'
        ));


        $this->_propertySetsTree = new XTreeEngine('catalog_properties', XRegistry::get('XPDO'));
        $this->_propertySetsTree->setLevels(4);
        $this->_propertySetsTree->setUniqType(1);
        $this->_propertySetsTree->setObject('_PROPERTYSET', array(
            'alias',
            'isSKU',
            'pset'
        ), array(
            '_ROOT'
        ));
        $this->_propertySetsTree->setObject('_PROPERTY', array(
            'default',
            'type',
            'tags',
            'alias',
            'isComparse',
            'isObligatorily',
            'isListingReady'
        ), array(
            '_PROPERTYSET'
        ));

        $this->_propertySetsTree->setObject('_OPTIONS', null, array(
            '_PROPERTY'
        ));


        //sku uses non-uniq basic
        $this->_sku = new XTreeEngine('catalog_sku', XRegistry::get('XPDO'), 0);
        $this->_sku->setLevels(4);
        $this->_sku->setUniqType(1);
        $this->_sku->setObject('_ROOT', array(
            'Name'
        ));
        $this->_sku->setObject('_SKUGROUP', null, array(
            '_ROOT',
            '_SKUGROUP'
        ));
        $this->_sku->setObject('_SKUOBJ', null, array(
            '_SKUGROUP'
        ));

        $this->_propertySetsTreeGroup = new XTreeEngine('catalog_propertygroups', XRegistry::get('XPDO'));
        $this->_propertySetsTreeGroup->setLevels(3);
        $this->_propertySetsTreeGroup->setUniqType(1);
        $this->_propertySetsTreeGroup->setObject('_PROPERTYSETGROUP', array(
            'alias',
            'skuLink',
            'listSequence',
            'itemSequence'
        ), array(
            '_ROOT'
        ));
        $this->_propertySetsTreeGroup->setObject('_PROPERTYSETLINK', null, array(
            '_PROPERTYSETGROUP'
        ));

        $this->_searchForms = new XTreeEngine('catalog_searchforms', XRegistry::get('XPDO'));
        $this->_searchForms->setLevels(3);
        $this->_searchForms->setUniqType(1);

        $this->_searchForms->setObject('_SEARCHFORM', array(
            'Name'
        ), array(
            '_ROOT',
            '_SEARCHFORMGROUP'
        ));
        $this->_searchForms->setObject('_SEARCHFORMGROUP', array(
            'Name'
        ), array(
            '_ROOT'
        ));
        $this->_searchForms->setObject('_SEARCHELEMENT', null, array(
            '_SEARCHFORM'
        ));


        $this->addPropertyType('input', new inputProperty());
        $this->addPropertyType('textarea', new textareaProperty());
        $this->addPropertyType('connection', new connectionProperty());
        $this->addPropertyType('connectionSKU', new connectionSKUProperty());
        $this->addPropertyType('fileFolder', new fileFolderProperty());
        $this->addPropertyType('checkbox', new checkboxProperty());
        $this->addPropertyType('selector', new selectorProperty());
        $this->addPropertyType('image', new imageProperty());
        $this->addPropertyType('file', new fileProperty());
        $this->addPropertyType('fuser', new fuserProperty());
        $this->addPropertyType('date', new dateProperty());
        $this->addPropertyType('currency', new currencyProperty());
        $this->addPropertyType('currencyIshop', new currencyIshopProperty());
        $this->addPropertyType('searchForm', new searchFormProperty());
        $this->addPropertyType('stock', new stockProperty());
        $this->addPropertyType('table', new tableProperty());
        $this->addComparsionType('sort', new sortComparsion());
        $this->addComparsionType('equal', new equalComparsion());
        $this->addComparsionType('interval', new intervalComparsion());
        $this->addComparsionType('_main', new mainProperty());


        Common::loadDriver('XCache', 'XCacheFileDriver');

    }


    private function getFastIndexSubLine($value, $index)
    {
        if (is_array($value)) {
            $outSubLine = '';

            foreach ($value as $item) {

                foreach ($index as $indexItem) {
                    $item = $item[$indexItem];
                }

                $outSubLine .= $item . ' ';

            }

            return $outSubLine;
        }

    }


    public function getFastIndexLine($object, $indexParams, $indexParamsSku = null)
    {

        static $indexDecomposed = array();

        if (empty($indexDecomposed)) {
            foreach ($indexParams as $index) {
                $indexDecomposed[] = explode('.', $index);

            }
        }

        foreach ($indexDecomposed as $index) {
            $value = $object;
            $indexCopy = $index;

            foreach ($index as $indexItem) {

                if (!empty($value[0])) {

                    $value = $this->getFastIndexSubLine($value, $indexCopy);
                    break;

                } else {
                    $value = $value[$indexItem];
                }
                array_shift($indexCopy);
            }


            $values[] = $value;
        }


        if (!empty($indexParamsSku) && is_array($object['_sku'])) {

            foreach ($object['_sku'] as $itemSku) {
                foreach ($indexParamsSku as $indexItemSku) {
                    $skuValuesString[] = $itemSku['params'][$indexItemSku];
                }
            }

            $values[] = implode(' ', $skuValuesString);
        }

        return implode(' ', $values);

    }


    public function fastIndexing($id, $indexParams, $start, $moveStep, $skuExtract, $indexParamsSku, $isFullIndex)
    {


        $objects = true;
        $i = 0;

        while (!empty($objects)) {

            $objects = $this->_tree->selectParams('*')->selectStruct('*')->where(array('@obj_type', '=', '_CATOBJ'))->childs($id)->limit($i * $moveStep, $moveStep)->run();

            $objects = $this->convertToPSGAll($objects, $skuExtract);

            $i++;

            if (!empty($objects)) {
                $z = 0;
                foreach ($objects as $obj) {
                    $z++;
                    $indexString = $this->getFastIndexLine($obj, $indexParams, $indexParamsSku);

                    if (empty($indexString)) {
                        $indexString = '';
                    }

                    if ($isFullIndex) {
                        $baseForm = XSTRING::Words2BaseForm($indexString);
                    }

                    $indexStringTemp = XRegistry::get('EVM')->fire('catalog:onObjectIndex', array('instance' => $this, 'i' => $i, 'z' => $z, 'indexString' => $indexString, 'object' => $obj, '$baseForm' => $baseForm));


                    if (!empty($indexStringTemp['indexString'])) {
                        $indexString = $indexStringTemp['indexString'];

                    } else {

                        $indexString = $indexString . ' ' . $baseForm;
                    }


                    $this->_tree->writeNodeParam($obj['_main']['id'], 'Base', $indexString);
                }


                $result['indexed'] = $start + $moveStep;
                $result['ready'] = false;
                XRegistry::get('EVM')->fire('catalog:onObjectIndexIterationFinished', array('instance' => $this));

            } else {

                $result['ready'] = true;
                break;
            }

        }

        return $result;

    }


    public function getComparsionTypes()
    {
        return $this->comparsionTypes;
    }


    public function setDefaultUserPrice()
    {

        if (!$_SESSION['siteuser']['authorized']) {

            if (xCore::isModuleExists('ishop')) {
                $ishop = xCore::moduleFactory('ishop.front');
                $_SESSION['userPriceCategory'] = $ishop->tunes['priceProperty'];
            }

        }
    }


    public function boostTree($params)
    {

        if ($this->_config['boostTree']) {

            $this->_tree->startBooster();
            xteBooster::clear();
            $this->_tree->boostById(1);
            $this->_sku->startBooster();
            $this->_sku->boostById(1);
        }


    }

    public function getPropertyGroupSerialized($propertyGroupId)
    {
        return XCacheFileDriver::serializedRead($this->_moduleName . '-psets', $propertyGroupId, false);
    }


    public function createPropertyGroupSerializedAll()
    {

        if ($plist = $this->_propertySetsTreeGroup->selectStruct(array(
            'id'
        ))->childs(1, 1)->run()
        ) {
            foreach ($plist as $groupNode) {

                $this->createPropertyGroupSerialized($groupNode['id']);
            }

        }

    }

    private function rebuildOneIcurrency($typeTree, $rate, $ids, $property)
    {

        if (is_array($ids)) {
            $chunks = array_chunk($ids, $this->currencyRebuildChunk, true);

            foreach ($chunks as $chunk) {
                $insertArr = array();

                foreach ($chunk as $node => $element) {

                    foreach ($this->currenciesList as $currId => $currency) {

                        $value = $element[$property] * $rate[$element[$property . '__currency']] / $currency['rate'];
                        $insertArr[] = "(NULL, '$node', '" . $property . "__in__{$currency['currencyId']}', '" . $value . "')";
                    }

                }

                $query = "insert into`{$this->$typeTree->treeParamName}` (`id` , `node_name` , `parameter` , `value`) values " . implode(',', $insertArr);

                $this->$typeTree->PDO->exec($query);

            }
        }


    }


    public function rebuildIcurrencyFields($params)
    {

        $currenciesTypes = $this->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array(
            'type',
            '=',
            'currencyIshop'
        ))->run();

        if (!empty($currenciesTypes)) {

            $_commonObjIshop = xCore::loadCommonClass('ishop');
            $currenciesList = $_commonObjIshop->getCurrenciesList(true);
            $rateType = 'rate';

            $this->currenciesListShort = $_commonObjIshop->getCurrenciesList();
            $this->currenciesList = $currenciesList;
            $currenciesMatrix = XARRAY::askeyval($currenciesList, $rateType);

            foreach ($currenciesTypes as $type) {

                $ancestor = $this->_propertySetsTree->getNodeInfo($type['path'][1]);

                $typeTree = $ancestor['params']['isSKU'] ? '_sku' : '_tree';


                if ($typeTree == '_sku') {
                    $typePath = $type['basic'];
                } else {
                    $typePath = $ancestor['basic'] . '.' . $type['basic'];
                }

                $currencyParam = $typePath . "__currency";

                foreach ($currenciesList as $currId => $clist) {
                    $q = "delete from `{$this->$typeTree->treeParamName}` where `parameter`='{$typePath}__in__" . $this->currenciesListShort[$currId] . '\'';
                    $pdoResult = $this->{$typeTree}->PDO->query($q);
                }

                $q = "select *  from `{$this->$typeTree->treeParamName}` where `parameter`='{$typePath}' or `parameter`='{$currencyParam}'";

                $pdoResult = $this->{$typeTree}->PDO->query($q);

                while ($row = $pdoResult->fetch(PDO::FETCH_ASSOC)) {
                    $ext[$typeTree][$typePath][$row['node_name']][$row['parameter']] = $row['value'];
                }
            }


            if (!empty($ext)) {

                $index=0;
                foreach ($ext as $tree => $currencyExt) {

                    $index++;

                    XRegistry::get('EVM')->fire('catalog:onPricesReindex', array('index' => $index));

                    foreach ($currencyExt as $property => $currency) {
                        $this->rebuildOneIcurrency($tree, $currenciesMatrix, $currency, $property);
                    }

                }
            }


        }

    }

    public function createPropertyGroupSerialized($propertyGroupId)
    {
        if ($sets = $this->_propertySetsTreeGroup->selectStruct(array(
            'id',
            'basic',
            'obj_type',
            'ancestor'
        ))->childs($propertyGroupId, 1)->run()
        ) {
            $setsId = XARRAY::asKeyVal($sets, 'basic');

            $allSets = $this->_propertySetsTree->selectStruct('*')->selectParams('*')->childs($setsId, 3)->asTree()->run();
            $setsInfo = $this->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array(
                '@id',
                '=',
                $setsId
            ))->format('keyval', 'id')->run();

            $setsInfoSend = array();

            if ($sets && $allSets) {
                foreach ($sets as $set) {

                    while (list($k, $v) = $allSets->fetch($set['basic'])) {
                        if ($options = $allSets->fetchArray($v['id'])) {
                            $options = current($options);
                            $v['options'] = $options['params'];

                        }
                        $setsData[$setsInfo[$set['basic']]['basic']][$v['basic']] = $v;
                    }

                    $setsInfoSend[$setsInfo[$set['basic']]['basic']] = $setsInfo[$set['basic']];
                }

            }


            $setGroup = $this->_propertySetsTreeGroup->getNodeInfo($propertyGroupId);


            if (XCacheFileDriver::serializedWrite(array(
                'sets' => $setsData,
                'setsInfo' => $setsInfoSend,
                'setGroupParams' => $setGroup['params']
            ), $this->_moduleName . '-psets', $propertyGroupId, false)
            )
                return true;
        }
    }


    private function additionalParams($section, $arrayParams, $tParams, $catalogObjectParams, $pointParams = true)
    {
        foreach ($arrayParams as $param) {
            if ($pointParams) {
                $key = $section . '.' . $param;
            } else {
                $key = $param;
            }
            $tParams[$section][$param] = isset($catalogObjectParams[$key]) ? $catalogObjectParams[$key] : null;
        }
        return $tParams;
    }


    public function skuHandleFront($skuObjects)
    {
        foreach ($skuObjects as &$skuObject) {

            if (!$skuSet = $this->skuPsetStorage[$skuObject['path'][1]]) {

                if (empty($skuObject['path'])) return;

                $skuGroup = $this->_sku->getNodeStruct($skuObject['path'][1]);
                $skuSet = $this->skuPsetStorage[$skuGroup['id']] = $this->findPsetByName($skuGroup['basic']);

            }

            foreach ($skuSet as $propertyName => $property) {
                $propertyObject = $this->getPropertyType($property['params']['type']);

                if (method_exists($propertyObject, 'handleTypeFront')) {
                    if (isset($skuObject['params'][$propertyName]))
                        $skuObject['params'][$propertyName] = $propertyObject->handleTypeFront($skuObject['params'][$propertyName], $property, $skuObject);
                }

            }

        }

        return $skuObjects;

    }


    public function findRelativeSku($objectsArray, $doNotGroup = false, $applyFilter = false, $doNotHandleFront = false)
    {

        $catalog = xCore::moduleFactory('catalog.front');

        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {
            if (!empty($this->currentFilterParams) && $applyFilter) {
                $addFilter = md5(print_r($this->currentFilterParams, true));
            }
            $mark = Common::createMark(serialize($objectsArray) . $_SESSION['cacheable']['currency']['id'] . $doNotGroup . $addFilter . $applyFilter . serialize($catalog->currentSKUFiltered));

            if ($this->_config['cacheInnerResources']) {
                if ($skuGrouped = XCacheFileDriver::serializedRead($this->_moduleName . '-relativeSku', $mark)) {
                    $this->lastRelativeSku = XCacheFileDriver::serializedRead($this->_moduleName . '-relativeSku', $mark . '-clear');
                    return $skuGrouped;
                }
            }

        }

        $this->lastRelativeSku = $this->_sku->selectStruct('*')->selectParams('*')->where(array('@netid',
            '=',
            $objectsArray
        ))->run();

        if (!empty($this->lastRelativeSku)) {

            XCacheFileDriver::serializedWrite($this->lastRelativeSku, $this->_moduleName . '-relativeSku', $mark . '-clear');

            if (!$doNotHandleFront) {
                $skuObjects = $this->skuHandleFront($this->lastRelativeSku);
            } else {
                $skuObjects = $this->lastRelativeSku;
            }

            if (isset($skuObjects) && (!$doNotGroup)) {

                foreach ($skuObjects as $skuObject) {
                    if ($applyFilter && !empty($catalog->currentSKUFiltered)) {

                        if (in_array($skuObject['id'], $catalog->currentSKUFiltered)) {
                            $skuGrouped[$skuObject['netid']][] = $skuObject;
                        }

                    } else {

                        $skuGrouped[$skuObject['netid']][] = $skuObject;
                    }


                }

            } else {
                $skuGrouped = $skuObjects;
            }


            XCacheFileDriver::serializedWrite($skuGrouped, $this->_moduleName . '-relativeSku', $mark);

            return $skuGrouped;
        }
    }


    public function findPsetByName($name)
    {

        if (!($id = $this->psetIdToNameStorage[$name])) {

            $pset = $this->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array(
                '@basic',
                '=',
                $name
            ), array(
                '@ancestor',
                '=',
                1
            ))->singleResult()->run();

            if (empty($pset)) return;

            $this->psetInfoStorage[$pset['id']] = $pset;
            $this->psetIdToNameStorage[$pset['id']] = $pset['basic'];


            if ($bulkPset = $this->_propertySetsTree->selectStruct('*')->selectParams('*')->childs($pset['id'])->format('keyval', 'basic')->run()) {

                $bulkMap = XARRAY::arrToKeyArr($bulkPset, 'id', 'basic');
                reset($bulkPset);

                while (list($k, $psetData) = each($bulkPset)) {
                    if ($psetData['obj_type'] == '_OPTIONS') {
                        $bulkPset[$bulkMap[$psetData['ancestor']]]['options'] = $psetData['params'];
                        unset($bulkPset[$k]);
                    }
                }
                reset($bulkPset);


                return $this->psetStorage[$pset['id']] = $bulkPset;
            }


        } else {

            return $this->psetStorage[$id];

        }

    }

    public function convertToPSGAll($catalogObjects, $options = null)
    {
        if (isset($catalogObjects)) {


            foreach ($catalogObjects as $k => $catalogObject) {
                $objectPsg = $catalogObject['params']['PropertySetGroup'];

                $this->retrivePSG($objectPsg);

                $catalogObjects[$k] = $this->convertToPSG($catalogObject, $options);

                if (isset($this->currentPSG[$objectPsg]['setGroupParams']['skuLink'])) {
                    $skuToExtract[] = $catalogObject['id'];
                }
            }


            if (isset($skuToExtract) && !$options['doNotExtractSKU']) {
                $skuObjects = $this->findRelativeSku($skuToExtract, false, $options['applyFilterOnSku']);

                foreach ($catalogObjects as $k => $catalogObject) {
                    $catalogObjects[$k]['_sku'] = $skuObjects[$catalogObjects[$k]['_main']['id']];
                }

            }

            return $catalogObjects;
        }
    }


    public function retrivePSG($objectPsg)
    {
        if (!is_array($this->currentPSG[$objectPsg])) {
            if ($this->currentPSG[$objectPsg] = $this->getPropertyGroupSerialized($objectPsg)) {

                if (isset($this->currentPSG[$objectPsg]['setGroupParams']['skuLink']) && $this->currentPSG[$objectPsg]['setGroupParams']['skuLink']) {
                    $skuGroup = $this->_sku->getNodeStruct($this->currentPSG[$objectPsg]['setGroupParams']['skuLink']);


                    $this->skuPsetStorage[$this->currentPSG[$objectPsg]['setGroupParams']['skuLink']] = $this->findPsetByName($skuGroup['basic']);

                }

                foreach ($this->currentPSG[$objectPsg]['sets'] as $skey => $set) {
                    $this->psetStorage[$this->currentPSG[$objectPsg]['setsInfo'][$skey]['id']] = $set;
                    $this->psetIdToNameStorage[$this->currentPSG[$objectPsg]['setsInfo'][$skey]['id']] = $skey;
                }
            }

        }

    }


    private function getTransformRulesCache($rules)
    {
        static $cache = false;


        if ($cache === false) {
            $cache = XCacheFileDriver::serializedRead($this->_moduleName . '-transforms', 'staticTransformsRules', false);
        }

        if (empty($cache)) {
            $cache = xPDO::selectIN('*', 'catalog_url_transform', '', 'order by priority asc');

            $outCache['regular'] = array();

            foreach ($cache as $val) {
                $outCache['all'][$val['id']] = $val;

                if (in_array($val['comparsion'], $this->regularComparsion)) {
                    $outCache['regular'][] = $val;
                }

            }

            $cache = $outCache;

            XCacheFileDriver::serializedWrite($cache, $this->_moduleName . '-transforms', 'staticTransformsRules');
        }


        if (!empty($rules)) {
            foreach ($rules as $ruleId) {
                if (isset($cache['all'][$ruleId])) {
                    $outAll[] = $cache['all'][$ruleId];
                }
            }


        }

        if (empty($outAll)) {
            $out = $cache['regular'];

        } else {

            $out = array_merge($cache['regular'], $outAll);
        }

        $out = XARRAY::sortByField($out, 'priority', 'asc');


        return $out;

    }


    private function getRegularRules($rules)
    {

        $regularStack = $this->getTransformRulesCache($rules);

        if (!empty($regularStack)) {
            foreach ($regularStack as $regular) {
                $regularOut[$regular['id']] = $regular;
            }
        }

        return $regularOut;

    }

    private function getTransforms($explodedQuery, $type = 'from')
    {
        static $cache;

        $mark = Common::createMark($explodedQuery, $type);

        if (!empty($cache[$mark])) return $cache[$mark];

        $transform = XPDO::selectIN('*', 'catalog_url_transform_list', ' `' . $type . '` in ("' . implode('","', $explodedQuery) . '")', 'order by id');

        if (!empty($transform)) {

            $transformMatrix = array();

            foreach ($transform as $item) {
                $transformMatrix[$item['rule_id']][$item['to']] = $item['from'];
            }

            $cache[$mark] = $transformMatrix;
            return $transformMatrix;
        }

    }

    private function getPropertyObjectByField($pset, $field)
    {
        $pset = $this->findPsetByName($pset);

        if (!empty($pset)) {
            if (!empty($field)) {
                $property = $pset[$field];
            }

            return array($property, $this->getPropertyType($property['params']['type']));

        }

    }

    public function buildUrlReverseTransformation($url)
    {
        static $urlCache = array();
        $urlMd = md5($url);

        if (!empty($urlCache[$urlMd])) {
            return $urlCache[$urlMd];
        }

        if (strstr($url, '/--')) {
            $parsedUrl = parse_url($url);
            $urlExploded = explode('/--', $parsedUrl['path']);

            $query = $urlExploded[1];

            $explodedQuery = explode('--', $query);

            $explodedQueryTemp = $explodedQuery = array_combine($explodedQuery, $explodedQuery);

            $transformMatrix = $this->getTransforms($explodedQuery, 'to');

            if (!empty($transformMatrix)) {
                $transformKeys = array_keys($transformMatrix);
            }

            $rules = $this->getRegularRules($transformKeys);

            $rulesRegular = array_keys($rules);

            if (!empty($transformMatrix)) {
                $rulesRegular = array_diff($rulesRegular, $transformKeys);
            }


            if (!empty($transformMatrix)) {
                foreach ($transformMatrix as $id => $directTransformation) {
                    foreach ($directTransformation as $dKey => $directTransformationItem) {
                        $transformed[] = $directTransformationItem;
                        unset($explodedQuery[$dKey]);
                    }

                }

            }

            if (!empty($rulesRegular)) {
                foreach ($rulesRegular as $id) {
                    $regItem = $rules[$id];

                    $exploded = explode('.', $regItem['field']);

                    $propertyPair = $this->getPropertyObjectByField($exploded[0], $exploded[1]);

                    $propertyObject = $propertyPair[1];

                    if (in_array($regItem['comparsion'], $this->regularComparsion) && !empty($propertyObject)) {
                        if ($transformTry = $propertyObject->handleRegularReverseUrlTransformation($regItem, $explodedQuery)) {
                            $transformed[] = $transformTry;
                        }
                    }
                }
            }

            $urlCache[$urlMd] = $urlExploded['0'] . '/?' . implode('&', $transformed) . '&' . $parsedUrl['query'];
            return $urlCache[$urlMd];


        } else {

            $urlCache[$urlMd] = $url;
            return $urlCache[$urlMd];
        }

    }

    public function buildUrlTransformation($url)
    {

        $url = str_replace(array('%5B', '%5D', '?&'), array('[', ']', '?'), $url);
        $url = preg_replace('/\[\d+\]/u', '[]', $url);

        $parsedUrl = parse_url($url);

        if (!empty($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parsedQuery);
            $explodedQuery = explode('&', $parsedUrl['query']);
            $transformMatrix = $this->getTransforms($explodedQuery);

            if (!empty($transformMatrix)) {
                $transformKeys = array_keys($transformMatrix);
                $rules = $this->getRegularRules($transformKeys);
            }


            if (!empty($rules)) {
                foreach ($rules as $regItem) {

                    if (in_array($regItem['comparsion'], $this->regularComparsion)) {

                        if ($regItem['tree'] == 's') {
                            $exploded = explode('.', $regItem['field']);
                            $field = $exploded[1];

                        } else {

                            $field = $regItem['field'];
                        }

                        $filterValue = $regItem['tree'] . '[' . $regItem['comparsion'] . '][' . $field . ']';

                        if (isset($parsedQuery[$regItem['tree']][$regItem['comparsion']][$field])) {
                            $value = $parsedQuery[$regItem['tree']][$regItem['comparsion']][$field];

                            if (!empty($regItem['transform_url'])) {
                                $transformation = strtolower(str_replace(array('{%F:value%}'), array($value), $regItem['transform_url']));
                                $transformed[$transformation] = $regItem['tree'] . '[' . $regItem['comparsion'] . '][' . $field . ']=' . $value;
                            }

                            unset($parsedQuery[$regItem['tree']][$regItem['comparsion']][$field]);
                        }

                    } else {
                        if (isset($transformMatrix[$regItem['id']])) {
                            foreach ($transformMatrix[$regItem['id']] as $key => $directTransform) {
                                $transformed[$key] = $directTransform;
                            }
                        }
                    }
                }

            }

            if (!empty($transformed)) {
                foreach ($explodedQuery as $urlExplodeElement) {
                    if (!in_array($urlExplodeElement, $transformed)) {
                        $urlGetTail[] = $urlExplodeElement;
                    }
                }

                $tail = '';

                $urlGetTail = XARRAY::clearEmptyItems($urlGetTail);

                if (isset($urlGetTail) && !empty($urlGetTail)) {
                    $tail = '?' . implode('&', $urlGetTail);
                }

                $preTail = '--' . (implode('--', array_keys($transformed))) . $tail;

            } else {
                $preTail = '?' . $parsedUrl['query'];
            }

        }

        $urlPrefix = '';

        if (isset($parsedUrl['scheme'])) {
            $urlPrefix = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        }
        $rUrl = $urlPrefix . $parsedUrl['path'] . $preTail;

        return $rUrl;
    }

    public function buildLink($nodeId, $destinationPageId, $excludeHost = false)
    {
        $pages = xCore::loadCommonClass('pages');

        $destination = $pages->createPagePath($destinationPageId, $excludeHost);

        if ($module = $pages->getModuleByAction($destinationPageId, 'showCatalogServer')) {

            $path = $this->_tree->selectStruct(array(
                'id'
            ))->selectParams('*')->getBasicPath('/', true, (int)$module['params']['showBasicPointId'])->where(array(
                '@id',
                '=',
                $nodeId
            ))->run();


        }
        return $destination . '/' . $path['pointBasicPathValue'];

    }

    private function sequenceConvert($seq)
    {
        if (!empty($seq)) {

            $data = explode("\n", $seq);
            $data = array_map('trim', $data);
            return $data;
        }

    }

    public function convertToPSG($catalogObject, $options = null)
    {
        static $tParamsKeys;

        if (!isset($tParamsKeys))
            $tParamsKeys = array(
                'path',
                'basicPath',
                'basic',
                'basicPathValue',
                'pointBasicPath',
                'pointBasicPathValue'
            );

        $objectPsg = $catalogObject['params']['PropertySetGroup'];

        $this->retrivePSG($objectPsg);

        $tParams = array();

        foreach ($this->currentPSG[$objectPsg]['sets'] as $setName => $set) {
            foreach ($set as $propertyName => $property) {
                $propertyObject = $this->getPropertyType($property['params']['type']);
                $key = $setName . '.' . $propertyName;

                if (!empty($propertyObject)) {
                    if (isset($catalogObject['params'][$key])) {
                        $tParams[$setName][$propertyName] = $propertyObject->handleTypeFront($catalogObject['params'][$key], $property, $catalogObject, $setName);
                    }
                }
            }
        }

        $tParams = $this->additionalParams('_main', array(
            'Name',
            'PropertySetGroup'
        ), $tParams, $catalogObject['params'], false);


        $tParams = $this->additionalParams('seo', array(
            'basic',
            'Title',
            'Keywords',
            'Description',
            'Canonical'
        ), $tParams, $catalogObject['params']);


        $tParams['_main']['id'] = $catalogObject['id'];

        $tParams['_main']['skuLink'] = $this->currentPSG[$objectPsg]['setGroupParams']['skuLink'];
        $tParams['_main']['listSequence'] = $this->sequenceConvert($this->currentPSG[$objectPsg]['setGroupParams']['listSequence']);
        $tParams['_main']['itemSequence'] = $this->sequenceConvert($this->currentPSG[$objectPsg]['setGroupParams']['itemSequence']);


        if ($userPriceCategory = $_SESSION['userPriceCategory']) {
            $userPriceCategoryExpl = explode('.', $userPriceCategory);

            if (!empty($tParams[$userPriceCategoryExpl[0]][$userPriceCategoryExpl[1]])) {
                $tParams['_main']['userPrice'] = $tParams[$userPriceCategoryExpl[0]][$userPriceCategoryExpl[1]];
            }

        }


        foreach ($tParamsKeys as $key) {
            if (isset($catalogObject[$key])) {
                $tParams['_main'][$key] = $catalogObject[$key];
            }

        }


        if ($catalogObject['netid'] != 0) {

            $tmp = $this->_tree->selectStruct(array(
                'id'
            ))->selectParams('*')->getBasicPath('/', true, $options['showBasicPointId'])->where(array(
                '@id',
                '=',
                $catalogObject['netid']
            ))->run();
            $catalogObject['pointBasicPathValue'] = $tmp['pointBasicPathValue'];
        }


        if (isset($catalogObject['pointBasicPathValue'])) {
            $catalogObject['pointBasicPathValue'] = '/' . $catalogObject['pointBasicPathValue'];
        }

        $pointBasicPathValue = isset($catalogObject['pointBasicPathValue']) ? $catalogObject['pointBasicPathValue'] : '';
        if (!empty($options['serverPageDestination'])) {
            $tParams['_main']['link'] = $options['serverPageDestination'] . $pointBasicPathValue;
        }
        $tParams['_main']['objType'] = $catalogObject['obj_type'];
        $tParams['_main']['ancestor'] = $catalogObject['ancestor'];


        if (isset($options['getSku'])) {
            if ($sku = $this->findRelativeSku(array(
                $catalogObject['id']
            ))
            ) {
                $tParams['_sku'] = $sku[$catalogObject['id']];
            }
        }


        return $tParams;

    }

    public function rebuildUrlTransformMatrix($ruleId = null)
    {

        if ($ruleId) $ruleId = (int)$ruleId;

        $rewriteList = XPDO::selectIN('*', 'catalog_url_transform', $ruleId);

        if (!empty($rewriteList)) {
            foreach ($rewriteList as $item) {
                $itemExploded = explode('.', $item['field']);
                $propertyPair = $this->getPropertyObjectByField($itemExploded[0], $itemExploded[1]);
                $propertyObject = $propertyPair[1];
                if (!empty($propertyObject)) {

                    $output = $propertyObject->handleUrlTransformation($item, $propertyPair[0]);

                    if (!empty($output)) {
                        XPDO::multiInsertIN('catalog_url_transform_list', $output);
                    }
                }


            }
        }

    }

    public function truncateTransformList()
    {

        $XPDO = XRegistry::get('XPDO');
        $XPDO->query('truncate catalog_url_transform_list');
    }

    public function clearFieldsUrlTransform($field)
    {
        XPDO::deleteIN('catalog_url_transform_list', 'rule_id=' . $field);
    }

    public function getACL()
    {
        return array('deleteObjectsRights', 'copyObjectsRights',
            'createNewObject', 'editProperties', 'editFilters', 'urlTransformationEdit',
            'editSearchForms', 'objectIndexation');
    }

    public function defineFrontActions()
    {
        $this->defineAction('showCategory');
        $this->defineAction('showObject');

        $this->defineAction('showCatalogServer', array(
            'serverActions' => array(
                'showCategory',
                'showCatalogMenu',
                'show',
                'search'
            )
        ));

        $this->defineAction('showCatalogMenu');
        $this->defineAction('showSearchForm');
        $this->defineAction('showCurrentSelectedFilters');
        $this->defineAction('showKeyWordSearch');
        $this->defineAction('showReactMenu');
        $this->defineAction('showFilterApplyCatalog');
        $this->defineAction('showCompareServer', array(
                'serverActions' => array(
                    'remove',
                    'addByUrl',
                    'removeAll'

                )
            )
        );


    }
}
