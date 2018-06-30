<?php

class showLevelMenuAction
    extends xAction
{
    public function run($params)
    {
        // $params['dynamicAdapt']=true;       TODO !

        $this->loadModuleTemplate($params['params']['menuTemplate']);

        if (isset($params['params']['objectInRows'])) {
            $this->devideByRows = (int)$params['params']['objectInRows'];
        } else {
            $this->devideByRows = 0;
        }
        $params['params']['showGroupId'] = (int)$params['params']['showGroupId'];
        $this->menuAncestor = $this->_tree->getNodeInfo($params['params']['showGroupId']);

        if ($params['params']['dynamicAdapt']) {

            $cPath = array_slice($this->page['path'], 2);
            $cPath = array_reverse($cPath);

            if (count($cPath) > $params['params']['upLevel']) {
                $params['params']['showGroupId'] = $cPath[$params['params']['upLevel']];

            } else {

                $params['params']['showGroupId'] = array_shift($cPath);

            }

        }


        if ($menuSource = $this->_tree->selectStruct('*')->selectParams('*')->getBasicPath()->childs($params['params']['showGroupId'], $params['params']['levels'])->where(array(
            '@obj_type',
            '=',
            array(
                '_LVERSION',
                '_GROUP',
                '_PAGE',
                '_LINK'
            )
        ))->asTree()->run()
        ) {
            $menuSource->recursiveStep($params['params']['showGroupId'], $this, 'clearDisabled');
            $menu = $this->renderMultiLevelMenu($menuSource, (int)$params['params']['showGroupId']);
        }

        return $menu;

    }

    public function renderMultiLevelMenu($menuSource, $startNode, $level = 0, $anc = null)
    {
        if ($this->mapMode && ($level > 1)) $level = 1;


        $menuLength = $menuSource->countBranch($startNode);
        $menuBuffer = '';
        $k = 0;
        while (list(, $menuItem) = $menuSource->fetch($startNode)) {

            if ($menuItem['params']['Visible']) {
                continue;
            }

            $pubMenuItem = array();
            $pubMenuItem = $menuItem['params'];
            $pubMenuItem['_num'] = ++$k;
            
            if ($menuItem['obj_type'] == '_LINK') {
                if (!empty($menuItem['params']['linkId'])) {
                    $pubMenuItem['link'] = $this->_commonObj->createPagePath($menuitem['params']['linkId']);

                } else {


                    $pubMenuItem['link'] = $menuItem['params']['Link'];

                    unset($pubMenuItem['Link']);

                }


            } else {
                $pubMenuItem['link'] = $this->_commonObj->linkCreator($menuItem['basicPath']);
            }


            $pubMenuItem['ancestor'] = $startNode;
            $pubMenuItem['basic'] = $menuItem['basic'];
            $pubMenuItem['id'] = $menuItem['id'];


            if ($menuSource->hasChilds($menuItem['id'])) {
                $pubMenuItem['submenu'] = $this->renderMultiLevelMenu($menuSource, $menuItem['id'], $level + 1, $startNode);
            }

            
            if ($menuItem['obj_type'] == '_LINK') {
                if ($this->page['id'] == (int)$menuItem['params']['connectedPageId']) {
               
                    $pubMenuItem['selected'] = 1;
                }

                if (in_array((int)$menuItem['params']['connectedPageId'], $this->page['path'])) {
                    $pubMenuItem['branch'] = 1;
                }
            } else {
                if ($this->page['id'] == $menuItem['id']) {
               
                    $pubMenuItem['selected'] = 1;
                }

                if (in_array($menuItem['id'], $this->page['path'])) {
                    $pubMenuItem['branch'] = 1;
                }
            }

            

            if (($menuItem['obj_type'] == '_GROUP')) {
                $pubMenuItem['group'] = 1;
            }

            if ($k == 1) {
                $pubMenuItem['first'] = 1;

            } elseif ($k == $menuLength) {
                $pubMenuItem['last'] = 1;

            }

            $itemSection = $this->findLevelSection($level, '_menu_item_level');
            $this->_TMS->clearSectionFelds($itemSection);

            $this->_TMS->addMassReplace($itemSection, $pubMenuItem);
            $menuBuffer .= $this->_TMS->parseSection($itemSection);


            if (($this->devideByRows) && ($k % (int)$this->devideByRows == 0)) {
                $mainLevelSection = $this->findLevelSection($level, '_menu_main_level');
                $this->_TMS->addReplace($mainLevelSection, 'menu_items', $menuBuffer);
                $menuBuffer = '';

                $menuDbuff .= $this->_TMS->parseSection($mainLevelSection);
                $this->_TMS->killField($mainLevelSection, 'menu_items');

                $exitSection = $this->findLevelSection($level, '_menu_divide_container');
            }
        }

        if (($this->devideByRows) && (($k % (int)$this->devideByRows != 0))) {

            $mainLevelSection = $this->findLevelSection($level, '_menu_main_level');
            $this->_TMS->addReplace($mainLevelSection, 'menu_items', $menuBuffer);

            $menuDbuff .= $this->_TMS->parseSection($mainLevelSection);

            $exitSection = $this->findLevelSection($level, '_menu_divide_container');

            $menuBuffer = $menuDbuff;
        } elseif (!$this->devideByRows) {
            $exitSection = $this->findLevelSection($level, '_menu_main_level');
        } else {
            $menuBuffer = $menuDbuff;
        }


        $this->_TMS->addMassReplace($exitSection, array(
            'menuItems' => $menuBuffer,
            'Name' => $this->menuAncestor['params']['Name'],
            'id' => $this->menuAncestor['id'],
            'basic' => $this->menuAncestor['basic']
        ));


        //    $this->_TMS->addMassReplace($exitSection, $branch_item);
        $main = $this->_TMS->parseSection($exitSection);
        return $main;

    }

    private function findLevelSection($level, $section)
    {
        if ($this->_TMS->isSectionDefined($section . $level)) {
            return $section . $level;
        } elseif ($level > 0) {
            return $this->findLevelSection($level - 1, $section);
        } else {

            return $this->findLevelSection(0, $section);
        }
    }

    public function clearDisabled($node, $ancestor, $tContext, $extdata)
    {
        if ($node['params']['NotVisibleMenu']) {
            $tContext->remove($node['id']);
        }
    }

}

?>