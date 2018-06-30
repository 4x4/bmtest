<?php

class formsListener extends xListener implements xModuleListener
{
    public function __construct()
    {
        parent::__construct('forms');

        $this->_EVM->on('pages.back:slotModuleInitiated', 'onSlotModuleSave', $this);
        $this->_EVM->on('AdminPanel:afterCacheClear', 'boostTreeListener', $this);
    }
}
?>
