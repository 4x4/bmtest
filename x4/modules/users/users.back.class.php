<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

require(xConfig::get('PATH', 'MODULES') . 'users/back/users.backObjects.user.class.php');
require(xConfig::get('PATH', 'MODULES') . 'users/back/users.backObjects.usersgroup.class.php');

class usersBack extends xModuleBack
{
    use _USER, _USERSGROUP;

    public function __construct()
    {
        parent::__construct(__CLASS__);
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


    public function deleteUser($params)
    {
        $this->deleteObj($params, $this->_tree);
    }

    public function getGroups()
    {

        $groups = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->childs(1, 1)->run();
        return XARRAY::arrToLev($groups, 'id', 'params', 'Name');
    }


    public function treeDynamicXLSUsers($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $connectAttributes = function ($set) {

            if (!isset($set['Name'])) {
                $set['Name'] = $set['name'] . ' [' . $set['surname'] . ' ' . $set['email'] . ']';
            }

            return $set;
        };


        $options = array
        (
            'imagesIcon' => array('_USER' => 'leaf.gif', '_USERSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'onRecord' => $connectAttributes,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_USERSGROUP',
                '_USER'
            ),
            'columns' => array('>Name' => array(), '>name' => array(), '>surname' => array(), '>email' => array())

        );

        $source->setOptions($options);

        $this->result = $source->createView($params['id'], $params['page']);


    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array('_USERSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_USERSGROUP'
            ),
            'columns' => array('>Name' => array())

        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id'], $params['page']);
    }


}