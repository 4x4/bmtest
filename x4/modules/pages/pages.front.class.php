<?php

use X4\Classes\XPDO;
use X4\Classes\XRegistry;

class pagesFront extends xModule
{
    public $menuAncestor;
    public $devideByRows;
    public $langVersion;
    public $bones = array();
    public $moduleOrder = array();
    public $moveLink;

    public $additionalBones = array();

    public function __construct()
    {
        parent::__construct(__CLASS__);
        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {
            $this->_tree->cacheState($this->_config['cacheTree']['tree']);
            if ($this->_config['boostTree']) {
                $this->_tree->startBooster();
                $this->_tree->setTreeBoosted();

            }
        }

    }

    public function getPageIdByPath($path)
    {

        $treePath = XARRAY::clearEmptyItems(explode('/', $path), true);
        $this->root = $this->_tree->getNodeInfo(1);


        $this->domain = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
            '@basic',
            '=',
            HTTP_HOST
        ), array(
            '@ancestor',
            '=',
            1
        ))->singleResult()->run();

        if(empty($this->domain)){
            throw new Exception('no-domain-detected');
        }
        
        if (!$langVersions = $this->getLangVersions($this->domain['id'])) {
            throw new Exception('no-lang-versions-detected');
        }


        $langVersionsStack = XARRAY::arrToKeyArr($langVersions, 'id', 'basic');

        if (!$treePath) {
            if (!$this->domain['params']['StartPage']) {
                $this->langVersion = current($langVersions);

            } else {
                $this->langVersion = $langVersions[$langVersionsStack[$this->domain['params']['StartPage']]];
            }


            $this->_commonObj->nativeLangVersion = $this->langVersion['basic'];


            if (!$this->langVersion['params']['StartPage']) throw new Exception('no-start-page-selected-for-this-lang-version');
            // return; //здесь заглушка - по умолчанию должен выбирать первую страницу

            $this->page = $this->_tree->getNodeInfo($this->langVersion['params']['StartPage']);


            if ($this->pageFinalPoint() === false) {
                return false;
            } else {
                return true;
            }

        } else {

            if (($lang = array_search($treePath[0], $langVersionsStack) !== false) && $langVersions[$treePath[0]]) {
                $this->langVersion = $langVersions[$treePath[0]];

                if (!isset($this->domain['params']['StartPage'])) throw new Exception('no-start-page-selected-for-this-domain');

                foreach ($langVersions as $lKey => $lVersion) {

                    if ($this->domain['params']['StartPage'] == $lVersion['id']) {

                        $this->_commonObj->nativeLangVersion = $lVersion['basic'];
                    }
                }

            } else {

                if (!isset($this->domain['params']['StartPage'])) throw new Exception('no-start-page-selected-for-this-domain');

                foreach ($langVersions as $lKey => $lVersion) {
                    if ($this->domain['params']['StartPage'] == $lVersion['id']) {
                        $this->langVersion = $langVersions[$lKey];
                    }
                }
                          
                          
              

                        array_unshift($treePath, $this->langVersion['basic']);
                        $this->_commonObj->nativeLangVersion = $this->langVersion['basic'];
            }


            array_unshift($treePath, HTTP_HOST);
            if (!$node = $this->_tree->idByBasicPath($treePath, array(
                '_DOMAIN',
                '_LVERSION',
                '_PAGE',
                '_GROUP'
            ), true)
            ) {
                return false;
            }

            $this->page = $this->_tree->getNodeInfo($node['id']);

            if ($this->page['obj_type'] == '_PAGE') {
                if ($this->_tree->readNodeParam($this->page['ancestor'], 'startPage') == $this->page['id']) {

                    if (XRegistry::get('TPA')->pathParams)
                        $pathParams = '/~' . XRegistry::get('TPA')->pathParams;
                        
                        $this->moveLink=$this->_commonObj->createPagePath($this->page['ancestor'], true) . $pathParams;
                        
                        
                        if(XRegistry::get('TPA')->renderMode=='HEADLESS')
                          {
                                
                               throw new Exception('headless-301-rebuild');
                                
                              
                          }else
                          {
                            XRegistry::get('TPA')->move301Permanent($this->moveLink);      
                          }
                        
                }
            }


            if ($this->pageFinalPoint() === false) {
                return false;
            } else {
                $bones = array_slice($this->page['path'], 3);
                $bones[] = $this->page['id'];

                XRegistry::get('TPA')->setSeoData(array(
                        'Title' => $this->page['params']['Title'],
                        'Description' => $this->page['params']['Description'],
                        'Keywords' => $this->page['params']['Keywords'],
                        'Meta' => $this->page['params']['Meta']
                    )
                );

                if (!empty($bones)) {


                    
                    $this->bones = $this->_tree->selectStruct('*')->selectParams('*')->getBasicPath('/', false)->where(array(
                        '@id',
                        '=',
                        $bones
                    ))->format('keyval', 'id')->run();
                }


                array_unshift($this->bones, $this->langVersion);

                return true;
            }
        }

    }

    public function getLangVersions($domainId = null)
    {
        $where[] = array(
            '@obj_type',
            '=',
            '_LVERSION'
        );

        if ($domainId) {
            $where[] = array(
                '@ancestor',
                '=',
                $domainId
            );
        }

        return $this->_tree->selectStruct('*')->selectParams('*')->getBasicPath('/', false)->where($where, true)->format('keyval', 'basic')->run();
    }

    private function pageFinalPoint()
    {
        $e = false;
        while (!$e) {
            if (in_array($this->page['obj_type'], array(
                '_GROUP',
                '_ROOT',
                '_LVERSION',
                '_DOMAIN'
            ))) {

                if (!empty($this->page['params']['StartPage'])) {
                    $this->page = $this->_tree->getNodeInfo($this->page['params']['StartPage']);
                    if ($this->page['params']['DisableGlobalLink'])
                        return false;
                } else {
                    //редиректа нет ->страница не существует
                    return false;
                }
            } else {
                $e = true;
            }
        }

        return null;

    }

    public function getSlotzCrotch($tplSlotz)
    {
        if ($sPath = $this->page['path']) {
            $sPath[] = $this->page['id'];

            if ($result = $this->_tree->selectStruct('*')->selectParams('*')->childs($sPath, 2)->where(array(
                '@obj_type',
                '=',
                array(
                    '_MODULE',
                    '_SLOT'
                )
            ))->asTree()->run()
            ) {

                foreach ($sPath as $pathPoint) {

                    if ($eSlots = $result->fetchArray($pathPoint)) {
                        foreach ($eSlots as $id => $slot) {

                            if ($eModules = $result->fetchArray($id)) {
                                foreach ($eModules as $kid => $module) {
                                    if (in_array($slot['basic'], $tplSlotz)) {
                                        $modules[$slot['basic']][] = $kid;
                                        $this->execModules[$kid] = $module;
                                        $this->modulesOrder[$kid] = $module['params']['_Priority'];
                                    }
                                }
                            }
                        }
                    }
                }

                if (isset($this->modulesOrder)) {
                    arsort($this->modulesOrder);
                }

                return $modules;
            }
        }
    }

    public function pushAdditionalBones($bone)
    {
        $this->additionalBones[] = $bone;
    }


    public function getRewrites()
    {
        return XPDO::selectIN('*', 'routes');
    }


    public function showMap($params)
    {
        $this->mapMode = true;
        $params['showGroupId'] = 1;
        $map = $this->showLevelMenu($params);
        $this->mapMode = false;
        return $map;
    }


}


