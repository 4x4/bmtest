<?php

class contentListener extends xListener implements xModuleListener
{


    public function __construct()
    {
        parent::__construct('content');
        $this->_EVM->on('pages.back:slotModuleInitiated', 'onSlotModuleSave', $this);
        $this->_EVM->on('AdminPanel:afterCacheClear', 'boostTreeListener', $this);

    }


    public function boostTreeListener($params)
    {
        $this->boostTree($params);

    }

    


}