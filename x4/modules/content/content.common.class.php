<?php

class contentCommon
    extends xModuleCommon implements xCommonInterface
{

    public $_useTree = true;

    public function __construct()
    {

        parent::__construct(__CLASS__);

        $this->_tree->setObject('_ROOT', array('Name'));
        $this->_tree->setObject('_CONTENTGROUP', array
        (
            'Name',
            'Title',
            'Keywords',
            'Description',
            'Disable',
            'Template',
            'Comment'
        ), array('_ROOT'));

        $this->_tree->setObject('_CONTENT', null, array('_CONTENTGROUP'));

        $this->_tree->setObject('_FIELD', null, array('_CONTENT'));
    }


    public function boostTree($params)
    {

        if ($this->_config['boostTree']) {
            $this->_tree->startBooster();
            $this->_tree->boostById(1);

        }


    }


    public function defineFrontActions()
    {
        $this->defineAction('showContent', array('cacheMode' => 'static'));
        $this->defineAction('showContentsList');
        $this->defineAction('contentServer', array('serverActions' => array('showContentGroupsList', 'showContent', 'showContentsList')));
    }


}
