<?php

class ishopListener extends xListener implements xModuleListener
{

    public function __construct()
    {
        parent::__construct('ishop');

        $this->_EVM->on('boot', 'setupTunes', $this);
        $this->_EVM->on('boot', 'setupMainCurrency', $this);
        $this->_EVM->on('fusers.setupPriceCategory:after', 'rebuildCart', $this);
        $this->_EVM->on('fusers.userLogin', 'rebuildCart', $this);
        $this->_EVM->on('fusers.userLogout','rebuildCart', $this);


    }

    public function rebuildCart($data)
    {
        $ishop = xCore::moduleFactory('ishop.front');

        if (!empty($ishop->cartStorage)) {
            foreach ($ishop->cartStorage as $id => $obj) {
                $dta[$id] = $obj['count'];
            }
            $ishop->cartStorage->clear();
            foreach ($dta as $id => $count) {
                $ishop->addToCart($id, $count);
            }
        }
    }

    public function setupMainCurrency()
    {
        $_commonObj = xCore::loadCommonClass($this->execClassName);
        $_commonObj->setupMainCurrency();
    }

    public function setupTunes($params)
    {
        $_commonObj = xCore::loadCommonClass($this->execClassName);
        $tunes = $_commonObj->getTunes();

        if ($tunes['cartStorageType']) {
            xConfig::setSubParam('MODULES', 'ishop', 'cartStorage', $tunes['cartStorageType']);
        }
    }
}
