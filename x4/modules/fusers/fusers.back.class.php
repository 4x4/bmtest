<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

require(xConfig::get('PATH', 'MODULES') . 'fusers/back/fusers.backObjects.fuser.class.php');
require(xConfig::get('PATH', 'MODULES') . 'fusers/back/fusers.backObjects.fusersgroup.class.php');

class fusersBack
    extends xModuleBack
{

    use _FUSER, _FUSERSGROUP;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function onOptions()
    {
        $data = $this->_tree->getNodeInfo(1);
        $groups = $this->getGroups();
        $this->result['options']['defaultUnregisteredGroup'] = XHTML::arrayToXoadSelectOptions($groups, $data['params']['defaultUnregisteredGroup'], true);
        $this->result['options']['defaultRegisteredGroup'] = XHTML::arrayToXoadSelectOptions($groups, $data['params']['defaultRegisteredGroup'], true);
    }

    public function onSaveOptions($params)
    {
        $this->_tree->writeNodeParams(1, $params['data']);
        return new okResult('options-saved');
    }


    public function onSearchInModule($params)
    {
        $params['word'] = urldecode($params['word']);

        $resultBasic = $this->_tree->selectParams(array('name', 'surname', 'email'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            'email',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();


        $resultLogin = $this->_tree->selectParams(array('name', 'surname', 'email'))->selectStruct(array
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

        $resultName = $this->_tree->selectParams(array('name', 'surname', 'email'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            'surname',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();

        XARRAY::arrayMergePlus($resultBasic, $resultName, true);
        XARRAY::arrayMergePlus($resultBasic, $resultLogin, true);

        $this->result['searchResult'] = Common::gridFormatFromTree($resultBasic, array
        (
            'id',
            'obj_type',
            'basic',
            'name',
            'surname',
            'email'

        ));

    }


    public function deleteFuser($params)
    {
        $this->deleteObj($params, $this->_tree);
    }

    public function switchFuserActivity($params)
    {
        $state = ($params['state']) ? 1 : '';
        $this->_tree->writeNodeParam($params['id'], 'active', $state);

        XRegistry::get('EVM')->fire($this->_moduleName . '.switchFuserActivity', array('params' => $params));
    }

    public function getGroups()
    {
        $groups = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->childs(1, 1)->run();
        return XARRAY::arrToLev($groups, 'id', 'params', 'Name');
    }


    public function treeDynamicXLSFusers($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));


        $connectAttributes = function ($set) {

            if (!isset($set['Name'])) {
                $set['Name'] = $set['name'] . ' [' . $set['surname'] . ' ' . $set['email'] . ']';
            }

            return $set;
        };


        $opt = array
        (
            'imagesIcon' => array('_FUSER' => 'leaf.gif', '_FUSERSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'onRecord' => $connectAttributes,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_FUSERSGROUP',
                '_FUSER'
            ),
            'columns' => array('>Name' => array(), '>name' => array(), '>surname' => array(), '>email' => array())

        );

        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);


    }


    public function treeDynamicXLS($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array('_FUSERSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_FUSERSGROUP'
            ),
            'columns' => array('>Name' => array())

        );

        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);
    }


    public function onAction_userPanel($params)
    {
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.userPanel.html'));

    }

      public function onAction_showUserPriceCategory($params)
    {
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showUserPriceCategory.html'));

    }



    public function onAction_showAuthPanel($params)
    {
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showAuthPanel.html'));
        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['userPanelPage'] = $pages->getPagesByModuleServerSelector('userPanel');

    }


}
