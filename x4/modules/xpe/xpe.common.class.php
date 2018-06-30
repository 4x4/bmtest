<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;
use X4\Classes\XTreeEngine;


class xpeCommon
    extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->_tree->setLevels(4);
        $this->_tree->setCacheParams('tree', 3600);

        $this->_tree->setObject('_ROOT', array
        (
            'Active'
        ));


        $this->_tree->setObject('_SCHEMEGROUP', array
        (
            'Alias'

        ), array('_ROOT'));


        $this->_tree->setObject('_SCHEMEITEM', array
        (
            'Alias',
            'Type',
            'Options',
            'OptionsData',
            'StorageParamName',
            'ValueType'

        ), array('_SCHEMEGROUP'));


        $this->_xpeRoles = new XTreeEngine('xperoles_container', XRegistry::get('XPDO'));
        $this->_xpeRoles->setLevels(4);
        $this->_xpeRoles->setUniqType(1);

        $this->_xpeRoles->setObject('_CAMPAIGN', array
        (
            'Alias',
            'start',
            'end'

        ), array(
            '_ROOT'
        ));


        $this->_xpeRoles->setObject('_XPEROLE', array
        (
            'Alias',
            'conditions'

        ), array(
            '_CAMPAIGN'
        ));



        $this->_xpeRoles->setObject('_AFFECTOR', null, array(
            '_XPEROLE'
        ));


    }


    public function defineFrontActions()
    {

    }


}
