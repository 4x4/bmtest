<?php

class searchCommon
    extends xModuleCommon implements xCommonInterface
{

    public $_useTree = false;


    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function defineFrontActions()
    {


        $this->defineAction('showSearchForm');
        $this->defineAction('searchServer', array('serverActions' => array('find')));
    }
}
