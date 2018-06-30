<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class catalogInstall extends catalogCommon
{

    public function __construct()
    {
        parent::__construct();
    }

    public function run($installedDomain)
    {
        $this->_tree->writeNodeParams(1, array('Name'=>$installedDomain));
		
    }
	


}
