<?php
use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showCategoryAction extends xAction
{

    public $_props;

    public function build($params)
    {

        $this->page = $_GET['page'];

        $onpage = explode(',', $params['params']['onPage']);

        if (count($onpage) > 1) {
            $this->_props['onpageMulti']=$this->onpageMulti = $onpage;
        }

        $_moduleId = $params['base']['moduleId'];

        if (isset($_GET['onpage'])) {
            $_SESSION['cacheable']['catalog']['onpage'][$_moduleId] = $_GET['onpage'];

        } elseif (!isset($_SESSION['cacheable']['catalog']['onpage'][$_moduleId])) {

            $_SESSION['cacheable']['catalog']['onpage'][$_moduleId] = $onpage[0];
        }

        $onpage = $_SESSION['cacheable']['catalog']['onpage'][$_moduleId];


        if ($params['params']['showGroupId']) {
            $params['node'] = $this->_tree->getNodeInfo((int)$params['params']['showGroupId'], true);
        }
        
        if (!empty($params['node'])) {
            
            $this->currentShowNode=$categoryInfo = $params['node'];

            $startPage = isset($this->page) ? $this->page : 0;

            $objTypes = array('_CATOBJ');

            if ($params['params']['getCategories']) {
                $objTypes[] = '_CATGROUP';
            }
            

            $filter['filterPack'] = array();

            $filter['filterPack']['f'] = array
            (
                'ancestor' => array('ancestor' => $categoryInfo['id'], 'objType' => $objTypes, 'endleafs' => $params['params']['endleafs'])
            );


            if (!empty($params['params']['filter'])) {
                $params['params']['filter'] = json_decode($params['params']['filter'], true);
                $filter['filterPack'] = array_replace_recursive($params['params']['filter'], $filter['filterPack']);
            }


            if (isset($this->filter) && !empty($this->filter)) {
                $filter['filterPack'] = array_replace_recursive($this->filter, $filter['filterPack']);

            } else {

                $this->filter = $params['params']['filter'];
            }


            $filter['applyFilterOnSku'] = $params['params']['applyFilterOnSku'];
            $filter['doNotExtractSKU'] = $params['params']['doNotExtractSKU'];
            $this->_props['startPage']=$filter['startpage'] = $startPage;
            $this->_props['onPage']=$filter['onpage'] = $onpage;


            if ($params['params']['DestinationPage']) {

                $filter['serverPageDestination'] = $this->createPageDestination($params['params']['DestinationPage']);
            } else {
                $filter['serverPageDestination'] = $params['params']['destinationLink'];
            }

            if (empty($params['params']['destinationLinkActionPaths'])) {$this->_props['destinationLinkActionPaths']=$params['params']['destinationLinkActionPaths'] = $filter['serverPageDestination'];}else{
                
                $this->_props['destinationLinkActionPaths']=$params['params']['destinationLinkActionPaths'];
            }

            
            if ($params['params']['showBasicPointId']) {
                $filter['showBasicPointId'] = $params['params']['showBasicPointId'];

            } elseif ($module = XRegistry::get('pagesFront')->_commonObj->getModuleByAction($params['params']['DestinationPage'], 'showCatalogServer')) {
                $filter['showBasicPointId'] = $module['params']['showBasicPointId'];
            }

            $this->_props['categoryInfo']= $this->_commonObj->convertToPSG($categoryInfo, array('serverPageDestination' => $filter['serverPageDestination']));

            $this->setSeoData($this->_props['categoryInfo']);

            $this->setDataCache('categoryInfo', $this->_props['categoryInfo']);
            
            $data = XRegistry::get('EVM')->fire($this->_moduleName . '.showCategory:onBeforeSelectObjects', array('instance'=>$this,'filter' => $filter));
           
            if(empty($data['preventDefault'])){
                                    
                $this->_props['catObjects'] = $this->selectObjects($filter);
                
            }else{
                
                $this->_props['catObjects']=$data['catObjects'];
            }

            
            $this->_props['catObjects']=$this->_props['catObjects'];
            
            $this->setDataCache('fullNodeIntersection', $this->_props['catObjects']['fullNodeIntersection']);

            $this->fullNodeIntersection = $this->_props['catObjects']['fullNodeIntersection'];

            if ($params['params']['searchMode']) {

                $params['params']['serverPageDestination'] = $filter['serverPageDestination'];
                $this->_props['categoriesAgregated'] = $this->categoryAgregation($params['params']);

            }
            
            
        }
        
    }
    
    
    public function run($params)
    {        
        $this->loadModuleTemplate($params['params']['categoryTemplate']);
        
        if ($this->_props['catObjects']['count'] > 0) 
           {
                $this->_TMS->addMassReplace('catalogCategoryList',
                    array(
                        'count' => $this->_props['catObjects']['count'],
                        'objects' => $this->_props['catObjects']['objects'],
                        'category'=> $this->_props['categoryInfo'],
                        'onPageMulti'=>$this->_props['onpageMulti'],
                        'onPage'=>$this->_props['onPage'],
                        'searchMode' => $params['params']['searchMode'],
                        'existInCategories' => $this->_props['categoriesAgregated']
                    )
                );
            } else {

                if (!strstr($_SERVER['QUERY_STRING'], 'xoadCall')) 
                {
                    header("HTTP/1.0 404 Not Found");
                }

                $this->setDataCache('404', true);
                $this->_TMS->parseSection('catalogEmpty', true);

            }

            Common::parseNavPages($this->_props['catObjects']['count'], $this->_props['onPage'], $this->_props['startPage'],$this->_props['destinationLinkActionPaths'], $this->_TMS);
            return $this->_TMS->parseSection('catalogCategoryList');
        
    }
    
    public function runHeadless($params)
    { 

        if ($this->_props['catObjects']['count'] > 0){
        $props=array(
                        'count' => $this->_props['catObjects']['count'],
                        'objects' => $this->_props['catObjects']['objects'],
                        'category'=> $this->_props['categoryInfo'],
                        'searchMode' => $params['params']['searchMode'],
                        'existInCategories' => $this->_props['categoriesAgregated']
                    );
        
        $props['paginator']=Common::parseNavPagesHeadless($this->_props['catObjects']['count'], $this->_props['onPage'], $this->_props['startPage'],$this->_props['destinationLinkActionPaths']);
        
        }else{        
            
            $props=array('catalogEmpty'=>true,
                         'category'=> $this->_props['categoryInfo']);
        }
        
        return $props;        
    }


    public function onCacheRead($action)
    {
        $dataCache = $action['cache']['cacheData'];

        if (!strstr($_SERVER['QUERY_STRING'], 'xoadCall')) {
            if ($dataCache['404']) {
                header("HTTP/1.0 404 Not Found");
            }
        }

        $this->setSeoData($dataCache['categoryInfo']);
        $this->fullNodeIntersection = $dataCache['fullNodeIntersection'];

        return $action['cache']['callResult'];

    }

    protected function categoryAgregation($params)
    {

        if (!empty($this->fullNodeIntersection)) {

            $pInfo = XRegistry::get('TPA')->getRequestActionInfo();
            $nodesPathes = $this->_tree->selectStruct(array('path', 'id'))->where(array('@id', '=', $this->fullNodeIntersection))->format('valval', 'id', 'path')->run();

            if (!empty($nodesPathes)) {

                if (empty($params['agregateToLevel'])) $params['agregateToLevel'] = 1;

                foreach ($nodesPathes as $path) {
                    $level = count($path) - $params['agregateToLevel'];
                    $countIn[] = $path[$level];
                }

                $categoriesCount = array_count_values($countIn);
                $result = $this->_tree->selectStruct('*')->selectParams('*')->getBasicPath('/', true, $params['showBasicPointId'])->where(array('@id', '=', array_keys($categoriesCount)))->run();

                $result = $this->_commonObj->convertToPSGAll($result, array(
                    'doNotExtractSKU' => $params['doNotExtractSKU'],
                    'serverPageDestination' => $params['serverPageDestination'],
                    'applyFilterOnSku' => $params['applyFilterOnSku']));

                if (!empty($result)) {
                    foreach ($result as &$item) {
                        $item['count'] = $categoriesCount[$item['_main']['id']];
                        $item['_main']['searchLink'] = $item['_main']['link'] . '?' . $pInfo['requestActionQuery'];
                    }

                    return $result;
                }

            }


        }
    }

}
