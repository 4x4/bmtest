<?php

//dummy module for DI plugins
class extendCommon
    extends xModuleCommon implements xCommonInterface
{


    public function __construct()
    {
        parent::__construct(__CLASS__);

    }

    public function defineFrontActions()
    {
    }


}
