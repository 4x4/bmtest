<?php

use X4\Classes\TableJsonSource;
use X4\Classes\XPDO;

require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.delivery.class.php');
require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.paysystem.class.php');
require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.status.class.php');
require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.tunes.class.php');
require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.currency.class.php');
require(xConfig::get('PATH', 'MODULES') . 'ishop/back/ishop.backObjects.store.class.php');

class ishopBack extends xModuleBack
{
    use _DELIVERY, _PAYSYSTEM, _STATUS, _TUNES, _CURRENCY, _STORE;

    public $orderStatus;

    public function __construct()
    {

        parent::__construct(__CLASS__);
        $this->tunes = $this->_commonObj->getTunes();
        $this->_EVM->on('ishop:afterEditedStatusChanged', 'notifyUserOnEditedStatusChanged', $this);
    }


    public function getGuestClientsRange($range)
    {
        return XPDO::selectIN('*', 'ishop_orders_clients_guest', $range);
    }


    public function setStatusList($params)
    {

        XPDO::updateIN('ishop_orders', (int)$params['id'], array('status' => $params['status']));
        return new okResult();
    }

    public function getStatusList($params)
    {

        if ($statuses = $this->_commonObj->getStatusesList(true)) {
            $this->result['statuses'] = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
        }
    }

    public function resultSetChanger()
    {

        $that = $this;

        $resultSetChanger = function ($set) use (&$that) {

            if (is_array($set)) {
                $clients = XARRAY::arrToKeyArr($set, 'id', 'client_id');
                $clientsGuest = XARRAY::arrToKeyArr($set, 'id', 'client_guest_id');
            }

            if ($statuses = $this->_commonObj->getStatusesList(true)) {
                $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
            }


            if ($clients) {


                if ($clientsInfo = $this->_commonObj->getRegisteredClientsRange($clients)) {

                    foreach ($clientsInfo as $k => $client) {
                        $clientsRegistered[$client['id']] = $client;
                    }

                }
            }


            if ($clientsGuest) {
                if ($clientsGuestInfo = $this->getGuestClientsRange($clientsGuest)) {
                    foreach ($clientsGuestInfo as $k => $client) {
                        $guestClients[$client['id']] = $client;
                    }

                }
            }


            if (isset($set)) {
                foreach ($set as $setItem) {

                    $setItem['status'] = $statuses[$setItem['status']];
                    $setItem['total_sum'] = number_format($setItem['total_sum'], 2, '.', ' ');


                    $setItem['address'] = $setItem['city'] . ' ' . $setItem['street'] . ' ' . $setItem['house'] . ' ' . $setItem['room'];
                    unset($setItem['city'], $setItem['street'], $setItem['house'], $setItem['room']);

                    if (isset($setItem['client_guest_id'])) {
                        $client = $guestClients[$setItem['client_guest_id']];
                        $setItem['client_guest_id'] = $client['surname'] . ' ' . $client['name'] . ' ' . $client['lastname'];
                        unset($setItem['client_id']);

                    } else {

                        $client = $clientsRegistered[$setItem['client_id']];
                        $setItem['client_id'] = $client['params']['surname'] . ' ' . $client['params']['name'] . ' ' . $client['params']['patronymic'];
                        unset($setItem['client_guest_id']);

                    }


                    $data[] = array('data' => array_values($setItem), 'id' => $setItem['id']);

                }
            }

            return $data;
        };

        return $resultSetChanger;

    }


    public function exportData($params)
    {

        $source = Common::classesFactory('TableJsonSource', array());
        $whereFilter = array();


        if ($params['filter']['ordersStartDate']) {
            $whereFilter[] = array('date', '>', strtotime($params['filter']['ordersStartDate']));
        }

        if ($params['filter']['ordersEndDate']) {
            $whereFilter[] = array('date', '<', strtotime($params['filter']['ordersEndDate']));
        }

        $whereFilter[] = array('status', '=', $params['filter']['statuses']);
        $whereFilter[] = array('orderType', '=', $params['filter']['orderType']);

        $opt = array(

            'onResultSet' => $this->resultSetChanger(),
            'vanillaFormat' => 1,
            'table' => 'ishop_orders',
            'whereFilter' => $whereFilter,
            'order' => array('id', 'desc'),
            'idAsNumerator' => 'id',
            'onPage' => false,
            'columns' => array(
                'id' => array(),
                'date' => array('onAttribute' => TableJsonSource::$fromTimeStamp, 'onAttributeParams' => array('format' => 'd.m.y H:i:s')),
                'orderType' => array('transformList' => $this->_config['orderTypes']),
                'client_id' => array(),
                'client_guest_id' => array(),
                'address' => array(),
                'city' => array(),
                'street' => array(),
                'house' => array(),
                'room' => array(),
                'phone' => array(),
                'total_sum' => array(),
                'status' => array()
            )

        );


        $source->setOptions($opt);
        unset($this->result['data']);

        $this->result = $source->createView(1, false);

        $rebuilded = $this->_EVM->fire('ishop.back:beforeCsvGenerated', array('instance' => $this, 'data' => $this->result['data_set']['rows']));

        if (!empty($rebuilded)) {
            $this->result['data_set']['rows'] = $rebuilded;
        }


        if (!empty($this->result['data_set']['rows'])) {

            $this->array2csv($this->result['data_set']['rows']);

            $this->result['file'] = 'order-export-' . date('d-m-y') . '.csv';

        } else {

            $this->result['file'] = '';
        }


    }


    public function array2csv(array $array)
    {

        if (count($array) == 0) {
            return null;
        }
        $df = fopen(xConfig::get('PATH', 'EXPORT') . 'order-export-' . date('d-m-y') . '.csv', 'w');

        foreach ($array as $row) {

            foreach ($row['data'] as &$element) {
                $element = iconv("UTF-8", "CP1251", $element);
            }

            fputcsv($df, $row['data']);
        }

        fclose($df);

    }


    public function ordersTable($params)
    {

        $source = Common::classesFactory('TableJsonSource', array());

        if (!$params['page']) $params['page'] = 1;

        $opt = array(

            'onResultSet' => $this->resultSetChanger(),
            'vanillaFormat' => 1,
            'table' => 'ishop_orders',
            'order' => array('id', 'desc'),
            'idAsNumerator' => 'id',
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                'date' => array('onAttribute' => TableJsonSource::$fromTimeStamp, 'onAttributeParams' => array('format' => 'd.m.y H:i:s')),
                'orderType' => array('transformList' => $this->_config['orderTypes']),
                'client_id' => array(),
                'client_guest_id' => array(),
                'address' => array(),
                'city' => array(),
                'street' => array(),
                'house' => array(),
                'room' => array(),
                'phone' => array(),
                'total_sum' => array(),
                'status' => array()
            )

        );


        $source->setOptions($opt);
        unset($this->result['data']);


        $this->result = $source->createView(1, $params['page']);


    }


    public function deleteStatus($params)
    {
        $this->deleteObj($params, $this->_tree);
    }


    public function getCurrentCourses($params)
    {
        require(xConfig::get('PATH', 'MODULES') . 'ishop/exchange/exchangeRateNBRB.class.php');

        $ex = new ExchangeRateNBRB(time() + 86500);

        $rates = $ex->getRates();

        if ($currenciesList = $this->_commonObj->getCurrenciesList()) {

            foreach ($currenciesList as $id => $currency) {

                if ($currentCur = $rates[$currency]) {

                    $cur = $this->_EVM->fire('ishop:afterNBRBCurrencyChange', array('instance' => $this, 'currencyId' => $id, 'currency' => $currency, 'currentCurrency' => $currentCur));

                    if (!empty($cur['currency'])) {
                        $currentCur = $cur['currency'];
                    }


                    $this->_tree->writeNodeParam($id, 'rate', $currentCur);

                    $newCurrMatrix[$id]['rate'] = $currentCur;

                }


            }

            $this->_EVM->fire('ishop:afterCurrentCoursesChange', array('instance' => $this, 'currMatrix' => $newCurrMatrix));
            $this->pushMessage('courses-changed');
        }


    }


    public function deleteOrder($params)
    {

        if (is_array($params['id'])) {
            $id = implode($params['id'], "','");
            $w = 'in (\'' . $id . '\')';
        }

        $this->_PDO->exec('DELETE FROM ishop_orders_goods WHERE ishop_orders_goods.order_id ' . $w);
        $q = 'DELETE FROM ishop_orders WHERE  ishop_orders.id ' . $w;

        $this->result['deletedList'] = $this->_PDO->exec($q);
    }


    public function deleteOrderGood($params)
    {

        $id = $params['id'];
        $this->result['deletedList'] = XPDO::deleteIN('ishop_orders_goods', $id);

    }


    public function saveGoodsParts($params)
    {

        $id = $params['id'];
        $data[$params['part']] = $params['value'];
        XPDO::updateIN('ishop_orders_goods', (int)$id, $data);
        $this->recalculateOrderSum($params['orderId']);
        $this->pushMessage('saved');

    }

    public function saveOrder($params)
    {

        $id = $params['id'];
        $notify = $params['data']['notifyClient'];
        unset($params['data']['notifyClient']);
        XPDO::updateIN('ishop_orders', (int)$id, $params['data']);
        $params['data']['notifyClient'] = $notify;
        $this->_EVM->fire('ishop:afterEditedStatusChanged', array('instance' => $this, 'params' => $params));


        $this->pushMessage('order-saved');

    }


    public function notifyUserOnEditedStatusChanged($params)
    {

        if (($params['data']['params']['data']['status'] == $this->tunes['editedStatus']) && ($params['data']['params']['data']['notifyClient'])) {

            $pages = xCore::loadCommonClass('pages');
            $module = $pages->getModuleByAction($this->tunes['notFinishedOrdersUrl'], 'showBasket');
            $this->loadModuleTemplate($module['params']['Template'], 'Front');
            $orderData = $this->_commonObj->getOrderData($params['data']['params']['id']);

            if (!empty($orderData['client_guest_id'])) {
                $customerData = $this->_commonObj->getGuestCustomer($orderData['client_guest_id']);
            }


            $orderFinishLink = $pages->createPagePath($this->tunes['notFinishedOrdersUrl']);
            $orderFinishLink .= '/~finishEditedOrder/?hash=' . $orderData['hash'];

            $sums = $this->_commonObj->proccessOrderSum($orderData);
            $this->_TMS->addMassReplace('orderEditedMail', array('orderFinishLink' => $orderFinishLink, 'cutomerData' => $customerData, 'orderData' => $orderData, 'orderSums' => $sums));

            $m = xCore::incModuleFactory('Mail');
            $m->From($this->tunes['emailNotifyFrom']);

            $m->To(array($customerData['email'], $this->tunes['emailNotifyList']));


            $m->Content_type('text/html');


            $this->_TMS->addMassReplace('orderEditedMailSubject', array('orderData' => $orderData));
            $subject = $this->_TMS->parseSection('orderEditedMailSubject');
            $m->Subject($subject);
            $body = $this->_TMS->parseSection('orderEditedMail');

            $m->Body($body, xConfig::get('GLOBAL', 'siteEncoding'));
            $m->Priority(2);
            $m->Send();

        }
    }

    public function getOrderFilterData($params)
    {

        if ($statuses = $this->_commonObj->getStatusesList(true)) {
            $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
            $this->result['filterData']['statuses'] = XHTML::arrayToXoadSelectOptions($statuses, $order['status']);
        }

        $this->result['filterData']['orderType'] = XHTML::arrayToXoadSelectOptions($this->_config['orderTypes'], $order['orderType']);


    }

    public function recalculateOrderSum($orderId)
    {
        $orderTotalSum = XPDO::selectIN('sum(price*count) as priceSum', 'ishop_orders_goods', "order_id=" . $orderId . "");
        $data['total_sum'] = $orderTotalSum[0]['priceSum'];
        XPDO::updateIN('ishop_orders', (int)$orderId, $data);
    }


    public function onSearchInModule($params)
    {

        $params['word'] = urldecode($params['word']);

        $source = Common::classesFactory('TableJsonSource', array());

        if (!$params['page']) $params['page'] = 1;

        $opt = array(
            'customSqlQuery' => "SELECT b . * FROM  `ishop_orders_clients_guest` a LEFT JOIN  `ishop_orders` b ON ( a.id = b.client_guest_id )  WHERE
        ((a.name LIKE  '%{$params['word']}%' or a.email LIKE '%{$params['word']}%') and b.id IS NOT NULL) or (b.id LIKE '%" . $params['word'] . "%' or b.phone LIKE '%" . $params['word'] . "%' or b.address LIKE '%" . $params['word'] . "%') ",
            'onResultSet' => $this->resultSetChanger(),
            'vanillaFormat' => 1,
            'table' => 'ishop_orders',

            'order' => array('id', 'desc'),
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                'date' => array('onAttribute' => TableJsonSource::$fromTimeStamp, 'onAttributeParams' => array('format' => 'd.m.y H:i:s')),
                'orderType' => array('transformList' => $this->_config['orderTypes']),
                'client_id' => array(),
                'client_guest_id' => array(),
                'address' => array(),
                'phone' => array(),
                'total_sum' => array(),
                'status' => array()
            )

        );

        $source->setOptions($opt);
        $view = $source->createView(1, $params['page']);
        unset($view['data']);
        $this->result['searchResult'] = $view['data_set'];


    }


    public function editOrder($params)
    {

        $order = $this->_commonObj->getOrderData($params['id']);

        if (!empty($order)) {


            $this->loadModuleTemplate('editOrder');


            if ($paySystemName = $this->_commonObj->getPaySystemName($order['paysystem'])) {
                $order['paysystem'] = $paySystemName;
            }

            $currencyList = $this->_commonObj->getCurrenciesList();
            $order['currency'] = $currencyList[$order['currency_id']];


            $order['orderSums'] = $this->_commonObj->proccessOrderSum($order);

            $this->result['formData']['orderType'] = XHTML::arrayToXoadSelectOptions($this->_config['orderTypes'], $order['orderType']);

            $this->_TMS->addMassReplace('order', $order);

            if ($statuses = $this->_commonObj->getStatusesList(true)) {
                $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
                $this->result['formData']['status'] = XHTML::arrayToXoadSelectOptions($statuses, $order['status']);
            }

            if (!empty($order['store_id'])) {
                $store = $this->_commonObj->_tree->getNodeInfo($order['store_id']);
                $order['store_address'] = $store['params']['Name'] . ' [' . $store['params']['storeAddress'] . ']';
            }

            $deliveries = $this->_commonObj->getDeliveryList();


            if (!empty($deliveries)) {
                $deliveries = XARRAY::asKeyVal($deliveries, 'Name');
                $this->result['formData']['delivery_id'] = XHTML::arrayToXoadSelectOptions($deliveries, $order['delivery_id']);
            }


            $orderLead = ishopCommon::getOrderLeadData($params['id']);

            $this->_TMS->addMassReplace('order', $order);
            $this->_TMS->addMassReplace('order', $orderLead);

            if ($order['client_guest_id']) {
                $user = $this->_commonObj->getGuestCustomer($order['client_guest_id']);
                $user['name'] = $user['surname'] . ' ' . $user['name'] . ' ' . $user['lastname'];

            } elseif ($order['client_id']) {


                $user = $this->_commonObj->getRegisteredClientsRange(array($order['client_id']));
                $userData = $user[0];
                $user = array();

                $user['name'] = $userData['params']['name'] . ' ' . $userData['params']['surname'] . ' ' . $userData['params']['patronymic'];
                $user['email'] = $userData['params']['email'];

            }

            $this->_TMS->addMassReplace('order', $user);

            $this->result['order'] = $this->_TMS->parseSection('order');
        }
    }


    public function loadGoodsData($params)
    {
        $source = Common::classesFactory('TableJsonSource', array());

        $where = ' order_id = ' . $params['id'];

        $tunes = $this->tunes;

        $onAtrrubuteSerialized = function ($set, $value) use ($tunes) {
            $value = unserialize($value);

            if ($tunes['skuPropertiesList']) {
                if ($skuPropertiesList = explode(',', $tunes['skuPropertiesList'])) {
                    $vStr = '';

                    foreach ($skuPropertiesList as $prpItem) {
                        $vStr .= ' ' . $value['params'][$prpItem];
                    }
                }
            }
            return $value['params']['Name'] . $vStr;
        };

        $onAtrrubuteSum = function ($set) {
            $set['sum'] = $set['count'] * $set['price'];

            return $set;
        };

        $opt = array(
            'onPage' => 500,
            'table' => 'ishop_orders_goods',
            'where' => $where,
            'idAsNumerator' => 'id',
            'onRecord' => $onAtrrubuteSum,
            'columns' => array(
                'id' => array(),
                'name' => array(),
                'sku_serialized' => array('onAttribute' => $onAtrrubuteSerialized),
                'count' => array(),
                'price' => array()
            )

        );

        $source->setOptions($opt);

        $this->result = $source->createView();


    }


    public function onAction_showCurrencyList($params)
    {

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showCurrencyList.html'));

    }


    public function onAction_showBasket($params)
    {

        if (isset($params['data']['params'])) {
        }
        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['catalogServerPage'] = $pages->getPagesByModuleServerSelector('showCatalogServer');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showBasket.html'));

    }


    public function onAction_showBasketStatus($params)
    {

        if (isset($params['data']['params'])) {
        }
        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['basketPage'] = $pages->getPagesByModuleServerSelector('showBasket');
        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.showBasketStatus.html'));

    }


    public function getWidgetGraph($params)
    {

        $q = 'SET sql_mode = ""';

        $pdoResult = $this->_PDO->query($q);

        $q = 'SELECT from_unixtime( date, "%Y-%m-%d" ) as date , sum( total_sum ) as sum FROM `ishop_orders` GROUP BY date DIV 86400 ORDER BY date DESC LIMIT 90;';

        $pdoResult = $this->_PDO->query($q);
        if ($results = $pdoResult->fetchAll(PDO::FETCH_ASSOC)) {
            $this->result['data'] = array_reverse($results);
        }

    }

    public function getWidgetStat($params)
    {

        $orderTotalSum = XPDO::selectIN('count(*) as orderCount', 'ishop_orders');
        $this->result['orders'] = $orderTotalSum[0]['orderCount'];


        $orderPayed = XPDO::selectIN('count(*) as payed', 'ishop_orders', "status='payed'");
        $this->result['payed'] = $orderPayed[0]['payed'];


        $sum = XPDO::selectIN('sum( total_sum ) as sum', 'ishop_orders', "status='payed'");
        $this->result['sumpayed'] = number_format($sum[0]['sum'], 0, ' ', ' ');

        $sum = XPDO::selectIN('sum( total_sum ) as sum', 'ishop_orders');
        $this->result['sumpotencial'] = number_format($sum[0]['sum'], 0, ' ', ' ');


        $orderSaled = XPDO::selectIN('SUM(count) as saled', 'ishop_orders_goods');
        $this->result['saled'] = $orderSaled[0]['saled'];

        $clients = XPDO::selectIN('COUNT( DISTINCT client_id ) AS clientsNumber', 'ishop_orders');
        $registeredNum = $clients[0]['clientsNumber'];


        $clients = XPDO::selectIN('COUNT( DISTINCT client_guest_id ) AS clientsNumber', 'ishop_orders');
        $nonRegisteredIdNum = $clients[0]['clientsNumber'];

        $this->result['clients'] = (int)$nonRegisteredIdNum + (int)$registeredNum - 2;

    }


}


