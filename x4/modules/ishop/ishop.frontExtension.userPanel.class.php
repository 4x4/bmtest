<?php

trait userPanel
{
    public function userPanelOrders($params, $source)
    {
        if ($orders = $this->getClientOrders(array('userId' => $_SESSION['siteuser']['id']))) {
            $ishop = xCore::moduleFactory('ishop.front');

            if ($statusesListFull = $ishop->_commonObj->getStatusesList(true)) {
                $statusesNames = XARRAY::arrToLev($statusesListFull, 'basic', 'params', 'Name');

                foreach ($orders as $key => $value) {
                    if (!empty($statusesNames[$value['status']])) {
                        $orders[$key]['statusAlias'] = $statusesNames[$value['status']];
                    }
                }

                reset($orders);
                unset($statusesNames, $statusesListFull);
            }

            $source->_TMS->addMassReplace('userPanelOrders', array('orders' => $orders));
        }

        $source->_TMS->parseSection('userPanelOrders', true);
    }


    public function userPanelSavedCart($params, $source)
    {

        $fuser = xCore::loadCommonClass('fuser');
        if ($fuser->isUserAuthorized()) {


        }

    }

}

