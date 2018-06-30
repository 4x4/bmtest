<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class tasksCron extends tasksBack
{

    public function __construct()
    {
        parent::__construct();
    }


    public function clearCache($params)
    {
        $adm = new AdminPanel();
        $adm->clearCache(true);
        return true;
    }

}
