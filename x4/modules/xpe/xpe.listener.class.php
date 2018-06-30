<?php
use X4\Classes\XRegistry;

require(xConfig::get('PATH', 'MODULES') . 'xpe/xpe.boot.php');

class xpeListener extends xListener implements xModuleListener
{


    public function __construct()
    {
        parent::__construct('xpe');

        $this->_EVM->on('boot', 'start', $this);


    }

    public function start()
    {
        $this->userEngage();
    }


}
