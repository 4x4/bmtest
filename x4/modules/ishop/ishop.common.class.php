<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class ishopCommon extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->_tree->setLevels(5);

        $this->_tree->setObject('_ROOT', array(
            'Name'
        ));

        $this->_tree->setObject('_TUNESBRANCH', array(
            'Name'
        ), array(
            '_ROOT'
        ));

        $this->_tree->setObject('_STORE', null, array(
            '_TUNESBRANCH'
        ));

        $this->_tree->setObject('_DELIVERY', null, array(
            '_TUNESBRANCH'
        ));

        $this->_tree->setObject('_PAYSYSTEM', null, array(
            '_TUNESBRANCH'
        ));

        $this->_tree->setObject('_STATUS', null, array(
            '_TUNESBRANCH'
        ));

        $this->_tree->setObject('_TUNES', null, array(
            '_TUNESBRANCH'
        ));

        $this->_tree->setObject('_CURRENCY', null, array(
            '_TUNESBRANCH'
        ));

        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {

            $this->_tree->cacheState(true);
        }

    }

    public function getBranchId($branch)
    {
        $result = $this->_tree->selectStruct('*')->where(array(
            '@basic',
            '=',
            $branch
        ))->run();

        return $result[0]['id'];
    }

    public function createTunesBranch($branchName)
    {
        $id = $this->getBranchId($branchName);

        if (!$id) {
            $id = $this->_tree->initTreeObj(1, $branchName, '_TUNESBRANCH');
        }

        return $id;
    }

    public function getDeliveryData($basic)
    {
        $ancestor = $this->getBranchId('DELIVERY');

        if ($delivery = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
            '@ancestor',
            '=',
            $ancestor
        ), array(
            '@basic',
            '=',
            $basic
        ))->singleResult()->run()
        ) {
            return $delivery;
        }
    }


    public function getDeliveryList()
    {

        $id = $this->getBranchId('DELIVERY');

        if ($deliveryList = $this->_tree->selectStruct('*')->selectParams('*')->childs($id)->format('keyval', 'basic', 'params')->run()) {
            return $deliveryList;
        }

    }


    public function getPaysystemData($basic)
    {
        $ancestor = $this->createTunesBranch('PAYSYSTEM');
        return $data = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
            '@ancestor',
            '=',
            $ancestor
        ), array(
            '@basic',
            '=',
            $basic
        ))->singleResult()->run();
    }

    public function getMainCurrency($basic = null)
    {

        $curSelector = $this->_tree->selectStruct('*')->selectParams('*');

        if (!$basic) {
            return $curSelector->where(array(
                '@obj_type',
                '=',
                '_CURRENCY'
            ), array(
                'isMain',
                '=',
                '1'
            ))->singleResult()->run();

        } else {

            return $curSelector->where(array(
                '@obj_type',
                '=',
                '_CURRENCY'
            ), array(
                '@basic',
                '=',
                $basic
            ))->singleResult()->run();
        }

    }

    public function getCurrenciesList($getAllData = false, &$mainCurrency = false)
    {

        $currInstance = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
            '@obj_type',
            '=',
            '_CURRENCY'
        ));

        if ($getAllData) {
            $currInstance->format('keyval', 'id');

        } else {
            $currInstance->format('valval', 'id', 'basic');
        }

        if ($currency = $currInstance->run()) {

            foreach ($currency as $key => $cur) {
                if (!empty($cur['isMain']) && $mainCurrency) {
                    $mainCurrency = $cur;
                }

                if (!empty($cur['basic']))
                    $basic = $cur['basic'];

                if (!$getAllData) {
                    $extCurrency[$key] = $cur;
                } else {
                    $extCurrency[$key] = $cur['params'];
                    $extCurrency[$key]['currencyId'] = $basic;
                }
            }


            return $extCurrency;
        }
    }


    public function proccessOrderSum($orderData)
    {

        $result['goodsTotalSum'] = $orderData['total_sum'];

        if (!empty($orderData['delivery_price'])) {
            $result['orderTotalSumWithoutDiscount'] = $result['orderTotalSum'] = $orderData['delivery_price'] + $orderData['total_sum'];
        } else {
            $result['orderTotalSum'] = $orderData['total_sum'];
        }

        if (!empty($orderData['discount_sum'])) {
            $result['orderTotalSum'] -= $orderData['discount_sum'];
            $result['orderTotalSumWithDiscount'] = $result['goodsTotalSum'] - $orderData['discount_sum'];
        }

        return $result;

    }


    public function getStatusesList($getFullData = false)
    {
        $list = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
            '@obj_type',
            '=',
            '_STATUS'
        ));

        if (!$getFullData)
            $list->format('valval', 'id', 'basic');

        if ($statuses = $list->run()) {
            return $statuses;
        }
    }

    public function getPaySystemName($paySystemName)
    {
        static $paySystems;

        if (!$paySystems) {
            $paysystemPath = xConfig::get('PATH', 'MODULES') . 'ishop/paysystems/';

            $systems = XFILES::filesList(xConfig::get('PATH', 'MODULES') . 'ishop/paysystems/', $types = 'directories');
            if (!empty($systems)) {
                foreach ($systems as $system) {
                    $file = $paysystemPath . $system . '/' . $system . '.paysystem.html';

                    $paySystems[$system . '.paysystem.html'] = xModuleBack::getTemplateAlias($file);
                }
            }

        }

        return $paySystems[$paySystemName . '.paysystem.html'];
    }


    public function getOrderStatus($id, $status)
    {
        $order = XPDO::selectIN(array(
            'status'
        ), 'ishop_orders', "id=" . (int)$id . "");
        return $order[0];
    }


    public function setOrderStatus($id, $status)
    {
        XPDO::updateIN('ishop_orders', (int)$id, array(
            'status' => $status
        ));
    }

    public function setOrderPaysystem($id, $system)
    {
        XPDO::updateIN('ishop_orders', (int)$id, array(
            'paysystem' => $system
        ));
    }

    public function getTunes()
    {
        if ($tunes = $this->_tree->selectParams('*')->where(array(
            '@obj_type',
            '=',
            '_TUNES'
        ), array(
            '@basic',
            '=',
            'tunesObject'
        ))->singleResult()->run()
        ) {
            return $tunes['params'];
        }
    }


    public function setupMainCurrency()
    {
        if ($_GET['setCurrentCurrency']) {
            $_SESSION['cacheable']['currency'] = $this->getMainCurrency($_GET['setCurrentCurrency']);
        }

        if (!isset($_SESSION['cacheable']['currency'])){
            
            $_SESSION['cacheable']['currency'] = $this->getMainCurrency();
            
        }
            


    }

    public function getCurrentCurrency()
    {
        if (isset($_SESSION['cacheable']['currency'])) {

            $mainCurrency[$_SESSION['cacheable']['currency']['id']] = $_SESSION['cacheable']['currency']['params'];

        } else {
            $mainCurrency = $this->_tree->selectStruct(array(
                'id'
            ))->selectParams('*')->where(array(
                '@obj_type',
                '=',
                '_CURRENCY'
            ), array(
                'isMain',
                '=',
                '1'
            ))->format('valval', 'id', 'params')->run();
            if (!$mainCurrency) {
                throw new Exception('main-currency-is-not-set-in-ishop-module;');
            }
        }
        return $mainCurrency;
    }


    public function getOrderData($id)
    {
        $order = XPDO::selectIN('*', 'ishop_orders', "id=" . $id . "");
        return $order[0];
    }


    public function getOrderDataByHash($hash)
    {
        $order = XPDO::selectIN('*', 'ishop_orders', "hash='" . $hash . "'");
        return $order[0];
    }


    public function getRegisteredClientsRange($clients)
    {
        $fusers = xCore::loadCommonClass('fusers');
        if ($clients = array_filter($clients)) {
            return $clients = $fusers->_tree->selectParams('*')->where(array(
                '@id',
                '=',
                $clients
            ))->run();

        }

    }

    public function getGuestCustomer($id)
    {

        if (!empty($id)) {
            if ($user = XPDO::selectIN('*', 'ishop_orders_clients_guest', (int)$id)) {
                return $user[0];
            }
        }

    }


    public function getGoodOrders($orderId)
    {

        if ($goods = XPDO::selectIN('*', 'ishop_orders_goods', 'order_id=' . $orderId)) {
            foreach ($goods as &$good) {
                if (!empty($good['sku_serialized'])) {
                    $good['skuObject'] = unserialize($good['sku_serialized']);
                    unset($good['sku_serialized']);
                }

                $good['priceSum'] = $good['price'] * $good['count'];
            }

            return $goods;
        }


    }

    public function getStockData($objId)
    {
        $query = "select store_id,items from `ishop_stock_items` where obj_id={$objId}";

        $pdoResult = xRegistry::get('XPDO')->query($query);

        $result=array();

        while ($row = $pdoResult->fetch(\PDO::FETCH_ASSOC))
        {
            $result[$row['store_id']]=$row['items'];
        }

        return $result;
    }

    /**
     * Get stock list saved in  STORE branch
     * @return array
     */

    public  function getStocksList()
    {
        $id = $this->getBranchId('STORE');
        $items=$this->_tree->selectStruct('*')->selectParams('*')->childs($id,1)->run();
        return $items;

    }

    /**
     * Get aggregated stock data from stock table
     * @param $objId
     * @return array
     */

    public function getStockAgregated($objId)
    {
        $items=$this->getStocksList();

        if(!empty($items)){

            $stockData=$this->getStockData($objId);

            foreach($items as $item)
            {
                $outItem['stockValue']=$stockData[$item['storeId']]?$stockData[$item['storeId']]:0;
                $outItem['innerId']=$item['id'];
                $outItem['stockId']=$item['basic'];
                $outItem['stockName']=$item['params']['Name'];
                $outItem['stockAddress']=$item['storeAddress'];
                $stockState[$item['basic']]=$outItem;
            }

        }

        return $stockState;
    }

    /**
     * Set stock array data into table
     * @param $objId
     * @param $stockData
     * @param string $itemsType
     */

    public function setStockData($objId,$stockData,$itemsType='f')
    {
        $currentStock=$this->getStockData($objId);

        if(!empty($currentStock)) {
            foreach ($currentStock as $stockItem) {
                if (!empty($stockData[$stockItem['store_id']])) {
                    $updateStock[$stockItem['store_id']] = $stockData[$stockItem['store_id']];
                }
            }

            if(!empty($updateStock)){
                $insertStock=array_diff_key($stockData, $updateStock);
            }else{
                $insertStock=$stockData;
            }


        }

        if(!empty($updateStock)) {
            foreach ($updateStock as $stockId => $value) {
                $express = ' store_id=' . $stockId . ' and obj_id=' . $objId;
                XPDO::updateIN('ishop_stock_items', $express, array('items_type'=>$itemsType,'items'=>$value));

            }
        }


        if(!empty($insertStock))
        {
            $stockArray=array();
            foreach ($insertStock as $stockId => $value) {
                $stockArray[]=array('id'=>'NULL','store_id'=>$stockId,'obj_id'=>$objId,'items_type'=>$itemsType,'items'=>$value);
            }

            XPDO::multiInsertIN($stockArray);
        }


    }

    static function getOrderLeadData($orderID)
    {
    
        $orderLead = XPDO::selectIN('*', 'ishop_orders_lead', "order_id='" . $orderID . "'");
        if(!empty($orderLead[0]))
        {
            return $orderLead[0];   
            
        }
    }

    public function getOrderByPaysystem($id)
    {
        $order = XPDO::selectIN('*', 'ishop_orders', "paysystem_order_num='" . $id . "'");

        if (!empty($order)) {

            if ($order[0]['client_guest_id']) {
                $user = $this->getGuestCustomer($order[0]['client_guest_id']);
            }

            $order[0]['client'] = $user;

            $order[0]['orderSums'] = $this->proccessOrderSum($order[0]);


            $order[0]['deliveryData'] = $this->getDeliveryData($order[0]['delivery_id']);

            return $order[0];
        }

        return false;
    }

    /*
    function getDiscoutsSchemes() { return XARRAY::arr_to_lev($this->discount_scheme_tree->GetChildsParam(1, '%', true),
    'id',
    'params',
    'Name'); }
    
    function getDiscountScheme($id) { return XARRAY::arrToKeyArr(
    XARRAY::askeyval(
    $this->discount_scheme_tree->GetChildsParam($id, '%', true),
    'params'),
    'catid',
    'discount'); }*/

    public function defineFrontActions()
    {
        $this->defineAction('showBasketStatus');
        $this->defineAction('showCurrencyList');
        $this->defineAction('showBasket', array(
            'serverActions' => array(
                'order',
                'remove',
                'removeall',
                'cart',
                'addtocart',
                'paymentSubmit',
                'paymentSuccess',
                'paymentFail',
                'submitOrder',
                'finishEditedOrder',
                'finishEditedOrderSubmit'
            )
        ));
    }
}
