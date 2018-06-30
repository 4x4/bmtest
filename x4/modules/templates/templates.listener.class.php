<?php

class templatesListener extends xListener implements xModuleListener
{

    public function __construct()
    {
        parent::__construct('templates');

        $this->_EVM->on('AdminPanel:afterCacheClear', 'refreshMainTplProxy', $this);

    }


    public function refreshMainTplProxy($params)
    {

        $this->refreshMainTpls();
    }


}
