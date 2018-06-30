<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;


class filterItem
{
    public $filter = array();
    public $type = 'f';

    public function __construct($type = 'f')
    {
        $this->type = $type;
    }

    public function addArray($item)
    {
        $this->filter[] = $item;
    }

    public function add($type, $property, $value)
    {
        $this->filter[] = array('type' => $type, 'property' => $property, 'value' => $value);
    }


    public function clear()
    {
        $this->filter = array();
    }

}

class catalogFront
    extends xModule
{
    public $additionalBones = array();


    public $signMatrix = array
    (
        'from' => '>=',
        'to' => '<=',
        'equal' => '=',
        'nonequal' => '<>',
        'rlike' => 'rlike',
        'like' => 'like',
        'alike' => 'alike'
    );

    public $currentShowNode;
    public $filter = array();
    public $_sku = null;
    public $skuSortParams = false;
    public $onpageMulti = array();

    public function __construct()
    {
        parent::__construct(__CLASS__);

        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {

            $this->_tree->cacheState($this->_config['cacheTree']['tree']);
            $this->nativeSelectObjectsFilters = $this->_commonObj->nativeSelectObjectsFilters;
            $this->_commonObj->_sku->cacheState($this->_config['cacheTree']['sku']);
            $this->_commonObj->_searchForms->cacheState($this->_config['cacheTree']['searchForms']);
            $this->_commonObj->_propertySetsTree->cacheState($this->_config['cacheTree']['propertySetsTree']);
            $this->_sku = $this->_commonObj->_sku;
        }

        if ($this->_config['boostTree']) {

            $this->_tree->startBooster();
            $this->_tree->setTreeBoosted();
            $this->_sku->startBooster();
            $this->_sku->setTreeBoosted();
        }

        $this->getFilter();

    }

    public function getFilter()
    {

        if (isset($_REQUEST['s'])) {
            $this->filter['s'] = $_REQUEST['s'];
        }

        if (isset($_REQUEST['f'])) {
            $this->filter['f'] = $_REQUEST['f'];
        }

    }

    public function checkInFilter($tree, $type, $property, $value = null)
    {


        if (!trim($value)) {
            if (isset($this->filter[$tree][$type][$property])) {

                return $this->filter[$tree][$type][$property];

            } else {
                return false;

            }
        }
        if(!empty($this->filter[$tree][$type][$property])) {
            if (is_array($this->filter[$tree][$type][$property])) {
                $logic = in_array($value, $this->filter[$tree][$type][$property]);
            } else {
                $logic = trim($val = $this->filter[$tree][$type][$property]) == trim($value);
            }
        }


        if ($logic) {
            return true;
        }

        return false;

    }

    public function getRelativeSkuByProps($id, $propsFilters)
    {

        $relativeFilter[] = array('@netid', '=', $id);

        if ($propsFilters) {
            foreach ($propsFilters as $prop => $filter) {
                $relativeFilter[] = array($prop, '=', $filter);
            }
        }

        $objects = $this->_sku->selectStruct('*')->selectParams('*')->addWhere($relativeFilter)->run();


        $skuObjects = $this->_commonObj->skuHandleFront($objects);
        return $skuObjects;

    }

    public function createFilter(filterItem $filterItem, $addToCurrent = true, $destanationLink = false)
    {

        if ($addToCurrent) {
            $filter = $this->filter;
        } else {
            $filter = array();
        }


        foreach ($filterItem->filter as $item) {

            if (isset($item['override'])) {
                if (isset($filter['s'])) unset($filter['s'][$item['type']]);
                if (isset($filter['f'])) unset($filter['f'][$item['type']]);
            }

            if (!isset($item['tree'])) {
                $tree = $filterItem->type;
            } else {
                $tree = $item['tree'];
            }

          if (!empty($filter[$tree][$item['type']])&&is_array($filter[$tree][$item['type']])) {
                $filter[$tree][$item['type']][$item['property']] = $item['value'];
           }
		   
		   if ('sort'==$item['type']){
			   $filter[$tree][$item['type']][$item['property']] = $item['value'];
		   }
        }


        $c = $this->buildFilter($filter, $destanationLink);

        if($c)$c = $this->_commonObj->buildUrlTransformation($c);

        return $c;

    }

    public function buildFilter($filter, $destinationLink = false)
    {
        if (isset($filter)) {

            $z='';
            if(!empty($filter)) {
                $z = http_build_query($filter);
                $z = preg_replace('/\%5B\d+\%5D/', '%5B%5D', $z);
                if ($z) $z = '/?' . urldecode($z);
            }


            if (!$destinationLink) $destinationLink = $this->destinationLinkActionPaths;

            return $destinationLink . $z;
        }


    }

    public function removeFilter($filterType, $propertyName = false, $sku = false)
    {
        if (!empty($this->filter)) {
            if ($sku) {
                $ft = 's';
            } else {
                $ft = 'f';
            }

            if ($propertyName) {
                unset($this->filter[$ft][$filterType][$propertyName]);
            } else {
                unset($this->filter['s'][$filterType]);
                unset($this->filter['f'][$filterType]);

            }
        }
    }

    public function convertParamKeyToArray($params)
    {
        if (isset($params)) {
            foreach ($params as $param => $value) {
                $paramsExploded = explode('.', $param);
                $newParams[$paramsExploded[0]][$paramsExploded[1]] = $value;
            }

            return $newParams;
        }
    }

    public function setSeoData($object)
    {

        $data = XRegistry::get('EVM')->fire($this->_moduleName . '.setSeoData', array('object' => $object));

        if (!empty($data)) {
            $object = $data;
        }

        XRegistry::get('TPA')->setSeoData($object);

    }

    public function showCatalogServer($params)
    {
                
        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        $this->getFilter();

        if (!$pInfo['requestAction']) {
            if ($secondary = $params['params']['secondaryAction']) {
                if ($params['params']['showBasicPointId']) {
                    $params['secondary']['showBasicPointId'] = $params['params']['showBasicPointId'];
                }

                $params['params'] = $params['secondary'];

                return $this->dispatchFrontAction($secondary, $params);
            }

        }


    }

    public function _blockSearchpriceto($params)
    {

        $params['sign'] = '<=';
        return $this->_blockSearchpricefrom($params);


    }

    public function _blockSearchpricefrom($params)
    {    
        $ishop = xCore::loadCommonClass('ishop');
        $mainCurrency = true;
        if ($currencies = $ishop->getCurrenciesList(true, $mainCurrency)) {
            if ($params['treeType'] == 's') {
                $tree = new X4\Classes\XTreeEngine('catalog_sku', XRegistry::get('XPDO'), 0);
                $tree->cacheState($this->_config['cacheTree']['sku']);

            } else {
                $tree = new X4\Classes\XTreeEngine('catalog_container', XRegistry::get('XPDO'), 0);
                $tree->cacheState($this->_config['cacheTree']['tree']);
            }
            $nodes = array();

            if (!$params['sign']) {
                $sign = '>=';
            } else {
                $sign = $params['sign'];
            }

            $key = key($params['selectValues']);
            $searchValue = $params['selectValues'][$key];


            if ($params['treeType'] == 's') {

                $nodes = $tree->selectStruct(array('id'))->where(array($key . '__in__' . $_SESSION['cacheable']['currency']['basic'], $sign, $searchValue),
                    array('@netid', '=', $this->prefeched))->format('keyval', 'id')->run();

            } else {
                $nodes = $tree->selectStruct(array('id'))->where(array($key . '__in__' . $_SESSION['cacheable']['currency']['basic'], $sign, $searchValue))->format('keyval', 'id')->run();
            }

            if (!empty($nodes)) {

                $nodes = array_keys($nodes);

                if (is_array($params['searchIntersection']) && !empty($params['searchIntersection'])) {
                    $nodes = array_intersect($nodes, $params['searchIntersection']);
                }

                if (!empty($nodes)) {
                    return $nodes;
                }
                return false;

            } else {
                return false;

            }


        }

    }

    public function search($params)
    {
        $params['params']['searchMode'] = true;
        return $this->show($params);

    }

    public function show($params)
    {
            
        $this->getFilter();
        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        $resultEVM=xRegistry::get('EVM')->fire($this->_moduleName . '.beforeShow', array('pInfo' => $pInfo, 'params' => $params));
        
        if(!empty($resultEVM['pInfo']))
        {
            $pInfo  = $resultEVM['pInfo'];
        }
        
        if (isset($pInfo['requestActionPath'])) {
            $treePath = XARRAY::clearEmptyItems(explode('/', $pInfo['requestActionPath']));
        }

        $pointNode = $this->_tree->getNodeInfo($params['params']['showBasicPointId'], true);

        $pointNode['path'][] = $pointNode['id'];


        if (count($treePath) > 0) {
            if (!($node = $this->basicPathToId($treePath, $pointNode['path']))) {
                xRegistry::get('TPA')->showError404Page();

            } elseif ($node['disabled'] == 1) {
                xRegistry::get('TPA')->showError404Page();
            }


        } else {

            $node = $pointNode;

        }


        $this->currentShowNode = $params['node'] = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', $node['id']))->jsonDecode()->run();

        $params['params']['destinationLink'] = XRegistry::get('TPA')->pageLinkHost;

        $this->destinationLinkActionPaths = $params['params']['destinationLinkActionPaths'] = $params['params']['destinationLink'] . $pInfo['requestActionPath'];

        $this->registerBones($node, $params['params']['showBasicPointId'], $params['params']['destinationLink']);

        $resultEVM=xRegistry::get('EVM')->fire($this->_moduleName . '.show', array('node' => $this->currentShowNode, 'params' => $params));

        if(!empty($resultEVM['node']))
        {
            $node  = $resultEVM['node'];
        }
        unset($params['fullActionData']['mainServerAction']);
                                           
        if ($node['obj_type'] == '_CATOBJ') {
            $pInfo = XRegistry::get('TPA')->requestActionSub = 'showObject';

            return $this->dispatchFrontAction('showObject', $params);
        } else {
            $pInfo = XRegistry::get('TPA')->requestActionSub = 'showCategory';

            return $this->dispatchFrontAction('showCategory', $params);

        }
    }

    public function basicPathToId($treePath, $pointPath)
    {

        //TODO если объект не найден сваливаемся в 404
        $node = $this->_tree->idByBasicPath($treePath, array
        (
            '_CATOBJ',
            '_CATGROUP'
        ), false, $pointPath);


        return $node;
    }

    public function registerBones($node, $basicPoint, $destinationPage)
    {
        $npath = $node['path'];
        $npath[] = $node['id'];

        $basicPosition = array_search($basicPoint, $npath);

        if ($basicPosition !== false) {
            $npath = array_slice($npath, $basicPosition + 1);
            $path = $this->_tree->selectStruct(array('id'))->selectParams('*')->getBasicPath('/', true, $basicPoint)->where(array('@id', '=', $npath))->format('keyval', 'id')->run();

            if ($path) {

                $pages = XRegistry::get('pagesFront');
                foreach ($npath as $pathId) {
                    $pathElement = $path[$pathId];
                    $pathElement['link'] = $destinationPage . '/' . $pathElement['pointBasicPathValue'];

                    $pages->pushAdditionalBones($pathElement);
                }
            }
        }


    }

    public function getObjectsByFilterInner($filter, $linkId, $startPage, $onPage)
    {

        if ($filter['filterPack'] = $filter) {

            if ($linkId) {

                $filter['serverPageDestination'] = $this->createPageDestination($linkId);
                $pages = xCore::loadCommonClass('pages');

                if ($module = $pages->getModuleByAction($linkId, 'showCatalogServer')) {
                    $filter['showBasicPointId'] = $module['params']['showBasicPointId'];
                }
            }

            $params['startpage'] = ($startPage) ? $startPage : 0;
            $params['onpage'] = ($onPage) ? $onPage : 50;


            if ($catObjects = $this->selectObjects($filter)) {
                if ($catObjects['objects']) {

                    return $catObjects['objects'];

                } else {

                    return false;
                }

            }
        }


    }

    public function selectObjects($params)
    {


        if ($changedParams = XRegistry::get('EVM')->fire($this->_moduleName . '.onSelectObjects', array('params' => $params))) {
            $params = $changedParams;
        }

        $this->_commonObj->currentFilterParams = $params;

        $mark = md5($_SESSION['cacheable']['currency']['id'] . print_r($params, true));

        if ($this->_config['cacheInnerResources']) {
            if ($ext = XCache::serializedRead('catalogSelectObjects', $mark)) {
                return $ext;
            }
        }

        $this->prefetchObjects($params['filterPack']);

        
        $this->_tree->dropQuery();
        
        $trees['f'] = $this->_tree->selectStruct('*')->selectParams('*')->jsonDecode()->preventSingleResult();

        if ($this->_sku) {
            $trees['s'] = $this->_sku->selectStruct('*')->jsonDecode();
        }


        if (is_array($params['filterPack'])) {
            foreach ($params['filterPack'] as $treeType => $pack) {
                $searchIntersection[$treeType] = $this->iterateFilter($trees[$treeType], $pack, $treeType);
            }

        }


        $this->currentSKUFiltered = null;


        if ($searchIntersection['s'][1] === false) return;

        if (is_array($searchIntersection['s'][1]) && !empty($searchIntersection['s'][1])) {
            $trees['s']->intersectWith($searchIntersection['s'][1]);

        }

        if (is_array($searchIntersection['f'][1]) && !empty($searchIntersection['f'][1])) {
            $trees['f']->intersectWith($searchIntersection['f'][1]);
        }


        if ($params['filterPack']['s']) {

            if ($skuResult = $trees['s']->run()) {

                $skuResultNetid = XARRAY::asKeyVal($skuResult, 'netid');

                $this->currentSKUFiltered = XARRAY::asKeyVal($skuResult, 'id');

                $trees['f']->intersectWith($skuResultNetid);

            } elseif (!$this->skuSortParams) {

                return;
            }

        }


        $bpTree = $trees['f']->getBasicPath('/', true, $params['showBasicPointId']);


        if (!$this->skuSortParams) {
            $result = $bpTree->limit($params['startpage'], $params['onpage'])->run();

        } else {

            $result = $bpTree->format('keyval', 'id')->run();
        }


        $nodesAllCount = $this->_tree->nodesAllCount;

        if ($changedResult = XRegistry::get('EVM')->fire($this->_moduleName . ':onSelectObjectsAfter', array('result' => $result, 'instance' => $this, 'params' => $params))) {
            $result = $changedResult['objects'];
            $nodesAllCount = $changedResult['count'];

        }

        $this->fullNodeIntersection = $trees['f']->nodeIntersectAll;

        if ($this->skuSortParams) {
            $sorted = $this->skuSortingProcess($result);
            $result = array_slice($sorted, $params['startpage'], $params['onpage']);
        }


        $result = $this->_commonObj->convertToPSGAll($result, array(
            'doNotExtractSKU' => $params['doNotExtractSKU'],
            'showBasicPointId' => $params['showBasicPointId'],
            'serverPageDestination' => $params['serverPageDestination'],
            'applyFilterOnSku' => $params['applyFilterOnSku']));


        $ext = array
        (
            'objects' => $result,
            'count' => $nodesAllCount,
            'fullNodeIntersection' => $this->fullNodeIntersection
        );


        XCache::serializedWrite($ext, 'catalogSelectObjects', $mark);

        return $ext;


    }

    private function prefetchObjects($filter)
    {

        if (!empty($filter['f']['ancestor'])) {

            $prefeched = $this->_tree->selectStruct(array('id'))->childs($filter['f']['ancestor']['ancestor'], $filter['f']['ancestor']['endleafs'])->where(array('@obj_type', '=', $filter['f']['ancestor']['objType']))->format('keyval', 'id')->run();
            $this->prefeched = array_keys($prefeched);
        }
    }

    public function iterateFilter($tree, $pack, $treeType)
    {
        $searchIntersection=null;

        if (isset($pack)) {
            foreach ($pack as $stype => $selectValues) {

                if (in_array($stype, $this->nativeSelectObjectsFilters)) {
                    $tree = $this->_blockSearchDefault($tree, $stype, $selectValues);
                } elseif ($searchIntersection !== false) {

                    $searchIntersection = XNameSpaceHolder::call('module.' . $this->_moduleName . '.front',
                        '_blockSearch' . $stype,
                        array('selectValues' => $selectValues, 'searchIntersection' => $searchIntersection,
                            'treeType' => $treeType, 'tree' => $tree));
                }
            }

            return array($tree, $searchIntersection);
        }

    }

    public function _blockSearchDefault($treeObject, $stype, $selectValues)
    {
        switch ($stype) {
            case 'to':
            case 'from':
            case 'equal':
            case 'nonequal':
            case 'like':
            case 'rlike':
            case 'alike':

                $treeObject->addWhere($this->prepareSelectParamsValues($selectValues, $this->signMatrix[$stype]));
                break;

            case 'ancestor':
                if (!$selectValues['endleafs'])
                    $selectValues['endleafs'] = 1;

                if ($selectValues['objType']) {
                    $treeObject->addWhere(array('@obj_type', '=', $selectValues['objType']));
                }
                $treeObject->childs($selectValues['ancestor'], $selectValues['endleafs']);

                break;

            case 'sort':

                foreach ($selectValues as $param => $value) {

                    $svExpl = explode('-', $value);

                    if (isset($svExpl[1])) {
                        $cast = $svExpl[1];
                    } else {
                        $cast = 'signed';
                    }

                    if ($treeObject->treeName == 'catalog_sku') {
                        $this->skuSortParams = array('param' => $param, 'order' => $svExpl[0], 'cast' => $cast);

                    } else {

                        $treeObject->sortby($param, $svExpl[0], $cast);
                    }
                }

                break;

            case 'multisort':
                break;
        }

        return $treeObject;
    }

    protected function prepareSelectParamsValues($selectValues, $sign = '=')
    {

        $selectValues = XARRAY::clearEmptyItems($selectValues, false, true);

        if (!empty($selectValues)) {
            while (list($key, $v) = each($selectValues)) {
                $value = XSTRING::dateRecognize($v);

                if ($sign == 'like') {

                    if (is_array($value)) {

                        foreach ($value as $val) {
                            $temp[] = '%' . $val . '%';
                        }

                        $value = $temp;

                    } else {

                        $value = '%' . $value . '%';
                    }

                }
                $values[] = array
                (
                    $key,
                    $sign,
                    $value
                );


            }
        }

        return $values;
    }

    public function skuSortingProcess($fullNodeInterSection)
    {

        $sortedOrder = array();
        $nodesIds = array_keys($fullNodeInterSection);
        $sortedBySku = $this->_sku->selectParams('*')->selectStruct('*')->where(array('@netid', '=', $nodesIds))->sortBy($this->skuSortParams['param'], $this->skuSortParams['order'], $this->skuSortParams['cast'])->run();

        if (!empty($sortedBySku)) {

            foreach ($sortedBySku as $sortSku) {

                if (!in_array($sortSku['netid'], $sortedOrder)) {
                    $sortedOrder[] = $sortSku['netid'];
                    $sortedList[] = $fullNodeInterSection[$sortSku['netid']];
                }
            }

            return $sortedList;
        }

    }

    protected function prepareSelectStructValues($selectValues, $structValue, $sign = '=')
    {
        $selectValues = XARRAY::clearEmptyItems($selectValues);

        if (!empty($selectValues)) {
            return array($structValue);
        }
    }


}

