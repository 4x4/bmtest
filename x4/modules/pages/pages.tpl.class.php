<?php

class pagesTpl extends xTpl implements xModuleTpl
{
    //aliasSets

    public function __construct($module)
    {
        parent::__construct($module);
    }


    public function getCurrentLangVersion()
    {

        return $this->langVersion;
    }


    public function getMenuTreeElement($id)
    {
        return $this->menuSource[$id];
    }


    

}