<?php
use X4\Classes\XRegistry;

class pagesListener extends xListener implements xModuleListener
{


    public function __construct()
    {
        parent::__construct('pages');

        $this->_EVM->on('AdminPanel:afterCacheClear', 'boostTreeListener', $this);
        $this->_EVM->on('apiBoot', 'checkForHeadlessMode', $this);
    }


    public function checkForHeadlessMode($params)
    {

        if (strstr($_SERVER['REQUEST_URI'], '/pages/route/')) {
            XRegistry::set('TMS', new X4\Classes\MultiSectionHL());
        }

    }

    public function boostTreeListener($params)
    {
        $this->boostTree($params);

    }


}
