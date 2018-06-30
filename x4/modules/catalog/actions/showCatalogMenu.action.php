<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showCatalogMenuAction extends xAction
{
    
     public function runHeadless($params)
    {

        $this->menuAncestor = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', $params['params']['sourceCatGroupId']))->run();

        $selectObjTypes = array('_CATGROUP');

        $params['params']['sourceCatGroupId'] = (int)$params['params']['sourceCatGroupId'];



        if ($params['params']['getObjects']) {
            $selectObjTypes[] = '_CATOBJ';
        }


        if ($params['params']['startFromSelected']) {

            if (!$params['params']['upperLevel'])
                $params['params']['upperLevel'] = 1;


            $res = $this->_tree->selectStruct('*')->where(array('@obj_type', '=', $selectObjTypes))->childs($this->currentShowNode['id'], 1)->run();

            if ($params['params']['showChildsObjectOnSelected'] && $res) {
                $params['params']['sourceCatGroupId'] = $this->currentShowNode['id'];

            } else {

                $allLength = count($this->currentShowNode['path']);
                $upperLevel = $allLength - $params['params']['upperLevel'];
                $params['params']['sourceCatGroupId'] = $this->currentShowNode['path'][$upperLevel];
            }

            if ($params['params']['showBasicPointId'] == $params['params']['sourceCatGroupId']) return;
        }

        $pages = xCore::loadCommonClass('pages');

        $catDestinationLink = $this->createPageDestination($params['params']['DestinationPage']);

        if ($module = $pages->getModuleByAction($params['params']['DestinationPage'], 'showCatalogServer')) {
            $basicPoint = (int)$module['params']['showBasicPointId'];
        }


        if ($menuSource = $this->_tree->selectStruct('*')->selectParams(
            '*')->getBasicPath('/',
            true,
            $basicPoint)->childs($params['params']['sourceCatGroupId'],
            $params['params']['Levels'])->where(array
        (
            '@obj_type',
            '=',
            $selectObjTypes
        ))->asTree()->run()
        ) {
            $menuSource->recursiveStep($params['params']['sourceCatGroupId'], $this, 'clearDisabled');
            $menuSource->recursiveStep($params['params']['sourceCatGroupId'], $this, 'psgConvert');
            
            return   $menuSource->tree;      
        }

        
    }
    
     public function psgConvert($node, $ancestor, $tContext, $extdata)
    {
          $b=$node;
          if($node['obj_type']=='_CATOBJ'){
            $tContext->remove($node['id']);
            $pagesCommon=xRegistry::get('pagesFront');
            $b=$this->_tree->selectStruct('*')->selectParams('*')->childs($node['id'],1)->
                            where(array('@basic', '=',$pagesCommon->langVersion['basic']))->jsonDecode()->singleResult()->run();


            }

            if(!empty($b['id']))
            {
                $tContext->tree[$ancestor][$b['id']] = $this->_commonObj->convertToPSG($node);
            }
        
    }

    public function run($params)
    {

        $this->loadModuleTemplate($params['params']['menuTemplate']);

        $this->menuAncestor = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', $params['params']['sourceCatGroupId']))->run();

        $selectObjTypes = array('_CATGROUP');

        $params['params']['sourceCatGroupId'] = (int)$params['params']['sourceCatGroupId'];


        if ($params['params']['getObjects']) {
            $selectObjTypes[] = '_CATOBJ';
        }


        if ($params['params']['startFromSelected']) {

            if (!$params['params']['upperLevel'])
                $params['params']['upperLevel'] = 1;


            $res = $this->_tree->selectStruct('*')->where(array('@obj_type', '=', $selectObjTypes))->childs($this->currentShowNode['id'], 1)->run();

            if ($params['params']['showChildsObjectOnSelected'] && $res) {
                $params['params']['sourceCatGroupId'] = $this->currentShowNode['id'];

            } else {


                $allLength = count($this->currentShowNode['path']);
                $upperLevel = $allLength - $params['params']['upperLevel'];
                $params['params']['sourceCatGroupId'] = $this->currentShowNode['path'][$upperLevel];
            }

            if ($params['params']['showBasicPointId'] == $params['params']['sourceCatGroupId']) return;
        }

        $pages = xCore::loadCommonClass('pages');

        $catDestinationLink = $this->createPageDestination($params['params']['DestinationPage']);

        if ($module = $pages->getModuleByAction($params['params']['DestinationPage'], 'showCatalogServer')) {
            $basicPoint = (int)$module['params']['showBasicPointId'];
        }


        if ($menuSource = $this->_tree->selectStruct('*')->selectParams(
            '*')->getBasicPath('/',
            true,
            $basicPoint)->childs($params['params']['sourceCatGroupId'],
            $params['params']['Levels'])->where(array
        (
            '@obj_type',
            '=',
            $selectObjTypes
        ))->asTree()->run()
        ) {
            $menuSource->recursiveStep($params['params']['sourceCatGroupId'], $this, 'clearDisabled');
            $menu = $this->renderMultiLevelMenu($menuSource, (int)$params['params']['sourceCatGroupId'], $catDestinationLink);


        }

        return $menu;
    }




    public function clearDisabled($node, $ancestor, $tContext, $extdata)
    {
        if (isset($node['params']['Disabled']) && $node['params']['Disabled']) {
            $tContext->remove($node['id']);
        }
    }


    public function renderMultiLevelMenu($menuSource, $startNode, $catDestinationPageLink, $level = 0, $anc = null)
    {

        $menuLength = $menuSource->countBranch($startNode);
        $k = 0;
        $menuBuffer='';

        while (list(, $menuItem) = $menuSource->fetch($startNode)) {
            $pubMenuItem = array();

            $pubMenuItem['_num'] = ++$k;

            $menuItem = $this->_commonObj->convertToPSG($menuItem);

            $pubMenuItem['link'] = $catDestinationPageLink . '/' . $menuItem['_main']['pointBasicPathValue'];

            if ($menuSource->hasChilds($menuItem['_main']['id'])) {
                $pubMenuItem['submenu'] = $this->renderMultiLevelMenu($menuSource, $menuItem['_main']['id'],
                    $catDestinationPageLink, $level + 1,
                    $startNode);
            }


            if(!empty($this->currentShowNode['path'])) {
                if (in_array($menuItem['_main']['id'], $this->currentShowNode['path'])) {
                    $pubMenuItem['branch'] = 1;
                }
            }

            if ($this->currentShowNode['id'] == $menuItem['_main']['id']) {
                $pubMenuItem['selected'] = 1;
            }

            if (($menuItem['_main']['objType'] == '_CATGROUP')) {
                $pubMenuItem['group'] = 1;
            }

            if ($k == 1) {
                $pubMenuItem['first'] = 1;
            } elseif ($k == $menuLength) {
                $pubMenuItem['last'] = 1;
            }

            $itemSection = $this->findLevelSection($level, '_menu_item_level');
            $this->_TMS->clearSectionFelds($itemSection);

            $menuItem['_menu'] = $pubMenuItem;

            $this->_TMS->addMassReplace($itemSection, $menuItem);
            $menuBuffer .= $this->_TMS->parseSection($itemSection);
        }

        $exitSection = $this->findLevelSection($level, '_menu_main_level');


        if (!($ancestorLevelItem = $menuSource->nodes[$startNode])) {
            $ancestorLevelItem = $this->menuAncestor;
        }

        $ancestorLevelItem = $this->_commonObj->convertToPSG($ancestorLevelItem);

        $this->_TMS->addMassReplace($exitSection, array
        (
            'menuItems' => $menuBuffer,
            'name' => $ancestorLevelItem['_main']['Name'],
            'id' => $ancestorLevelItem['_main']['id'],
            'basic' => $ancestorLevelItem['_main']['basic'],
            'params' => $ancestorLevelItem
        ));

        $main = $this->_TMS->parseSection($exitSection);
        return $main;
    }

    private function findLevelSection($level, $section)
    {

        if ($level < 0) return;

        if ($this->_TMS->isSectionDefined($section . $level)) {
            return $section . $level;
        } elseif ($level > 0) {
            return $this->findLevelSection($level - 1, $section);
        } else {
            if (!$this->_TMS->isSectionDefined($section . '0')) {
                return;
            } else {
                return $this->findLevelSection(0, $section);
            }
        }
    }

}
