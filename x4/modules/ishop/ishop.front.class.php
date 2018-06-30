<?php

use X4\Classes\XPDO;
use X4\Classes\XRegistry;

include_once(xConfig::get('PATH', 'MODULES') . 'ishop/ishop.front.storage.class.php');
include_once(xConfig::get('PATH', 'MODULES') . 'ishop/ishop.frontExtension.userPanel.class.php');
include_once(xConfig::get('PATH', 'MODULES') . 'ishop/ishop.front.payment.class.php');


class ishopFront
    extends xModule
{
    use userPanel;

    public $cartStorage;
    public $paysystem;
    public $userFields = array
    (
        'street',
        'house',
        'name',
        'surname',
        'lastname',
        'city',
        'room',
        'index',
        'phone',
        'email'
    );

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $cartStorage = $this->_config['cartStorage'];

        $this->cartStorage = new $cartStorage;
        $this->tunes = $this->_commonObj->getTunes();


    }

    private function rehandleSkuFrontObject($obj)
    {
        if (!empty($obj['skuObject'])) {
            $catalog = xCore::loadCommonClass('catalog');
            $skuObj = $catalog->_sku->getNodeInfo($obj['skuObject']['id']);
            $obj['skuObject'] = current($catalog->skuHandleFront(array($skuObj)));
            $obj['price'] = $obj['skuObject']['params'][$this->tunes['pricePropertySKU']]['value'];
        }
        return $obj;

    }


    public function order($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);

        if (is_array($_POST['order'])) {
            foreach ($_POST['order'] as $id => $item) {
                $obj = $this->cartStorage[$id];
                if (is_numeric($item['count'])) {
                    $obj['count'] = $item['count'];
                }

                if (isset($item['comments'])) {
                    $obj['comments'] = $item['comments'];
                }

                $this->cartStorage[$id] = $obj;
            }
        }

        foreach ($this->cartStorage as $key => $obj) {
            $obj = $this->rehandleSkuFrontObject($obj);
            $this->cartStorage[$key] = $obj;
        }

        if ($_SESSION['siteuser']['authorized']) {
            $this->_TMS->addMassReplace('ishopOrder', array
            (
                'username' => $_SESSION['siteuser']['userdata']['Name'],
                'useremail' => $_SESSION['siteuser']['userdata']['Email'],
                'company' => $_SESSION['siteuser']['userdata']['Company'],
                'phone' => $_SESSION['siteuser']['userdata']['Phone'],
                'site' => $_SESSION['siteuser']['userdata']['Site']
            ));
        }

        $this->_TMS->addMassReplace('ishopOrder', array(
            'objects' => $this->cartStorage->get(),
            'enableNotFinishedOrders' => $params['params']['enableNotFinishedOrders'],
            'orderLink' => XRegistry::get('TPA')->pageLinkHost . '/~submitOrder'
        ));

        return $this->_TMS->parseSection('ishopOrder');
    }

    public function processUserData($data)
    {
        $userFields = $this->userFields;

        foreach ($userFields as $field) {
            if (isset($data[$field]) && $value = $data[$field]) {
                $userData[$field] = $value;
            } elseif ($_SESSION['siteuser']['userdata']) {
                $userData[$field] = $_SESSION['siteuser']['userdata'][$field];
            }
        }

        return $userData;
    }

    public function calculateDelivery($deliveryId)
    {

        $delivery = $this->_commonObj->getDeliveryData($deliveryId);

        $orderInfo = $this->calculateCart();

        if (isset($delivery['params']['from']) && isset($delivery['params']['to'])) {
            if (($orderInfo['orderSum'] > $delivery['params']['from']) && ($orderInfo['orderSum'] < $delivery['params']['to'])) {
                $deliveryCost = $delivery['params']['deliveryCost'];

            } else {

                $deliveryCost = 0;
            }

        } else {

            $deliveryCost = $delivery['params']['deliveryCost'];
        }

        return array('deliveryInfo' => $delivery, 'deliveryCost' => $deliveryCost, 'orderSumWithDelivery' => ($deliveryCost + $orderInfo['orderSum']));

    }

    public function goodsToOrder($orderDataExternal)
    {
        if (!empty($this->cartStorage)) {


            $paysystem = $orderDataExternal['paysystem'];
            $userData = $this->processUserData($_POST);
            $orderInfo = $this->calculateCart();


            if (isset($orderDataExternal['delivery'])) {
                $deliveryId = $orderDataExternal['delivery'];
                $delivery = $this->_commonObj->getDeliveryData($orderDataExternal['delivery']);
                $delivery['params']['deliveryTime'] = isset($orderDataExternal['deliveryTime']) ? $orderDataExternal['deliveryTime'] : 0;
            }


            $status = $this->tunes['defaultOrderStatus'];


            if ($statusesListFull = $this->_commonObj->getStatusesList(true)) {
                $statusesNames = XARRAY::arrToLev($statusesListFull, 'id', 'params', 'Name');
                $statuses = XARRAY::arrToKeyArr($statusesListFull, 'id', 'basic');
            }


            if (isset($orderDataExternal['status']) && !empty($orderDataExternal['status'])) {
                $defaultNotFinished = $this->tunes['notFinshedStatus'];

                if ($orderDataExternal['status'] == $statuses[$defaultNotFinished]) {
                    $status = $defaultNotFinished;
                }

            }


            $delivery = $this->calculateDelivery($deliveryId);

            $orderData = array
            (
                'id' => 'null',
                'date' => time(),
                'name' => $userData['name'],
                'client_id' => isset($this->userId) ? (int)$this->userId : 0,
                'client_guest_id' => isset($this->guestUserId) ? (int)$this->guestUserId : 0,
                'currency_id' => $_SESSION['cacheable']['currency']['id'],
                'street' => $userData['street'],
                'house' => $userData['house'],
                'city' => $userData['city'],
                'room' => $userData['room'],
                'index' => $userData['index'],
                'phone' => $userData['phone'],
                'email' => $userData['email'],
                'delivery_id' => $deliveryId,
                'total_sum' => $orderInfo['orderSum'],
                'delivery_price' => $delivery['deliveryCost'],
                'status' => $status,
                'status_name' => $statusesNames[$status],
                'comments' => isset($orderDataExternal['comments']) ? $orderDataExternal['comments'] : $_SESSION['siteuser']['orderData']['comments'],
                'paysystem' => $paysystem,
                'paysystem_order_num' => $paysystemOrderNum,
                'hash' => Common::generateHash(),
                'orderType' => $orderDataExternal['orderType'],
                'promo' => $orderDataExternal['promo'],
                'store_id' => $orderDataExternal['store_id'],
                'notes' => isset($orderDataExternal['notes']) ? $orderDataExternal['notes'] : ''
            );


            if (XPDO::insertIN('ishop_orders', $orderData)) {
                $lid = XPDO::getLastInserted();

                $ordNum = (int)$lid;


                $orderData['sums'] = $sums = $this->_commonObj->proccessOrderSum($orderData);

                $orderData['paysystem_order_num'] = $ordNum;

                $orderData['delivery'] = $delivery;

                $this->_TMS->addMassReplace('ishop_cart_email', $orderData);
                $this->_TMS->addMassReplace('ishop_cart_email', array('id' => $lid));
                $this->_TMS->addMassReplace('ishop_cart_email', $sums);

                $this->_TMS->generateSection($this->tunes['emailSubject'], 'emailSubject');

                $this->_TMS->addMassReplace('emailSubject', $orderData);

                $this->_TMS->addMassReplace('emailSubject', array('id' => $lid));


                $emailSubject = $this->_TMS->parseSection('emailSubject');

                XPDO::updateIN('ishop_orders', (int)$lid, array('paysystem_order_num' => $ordNum));

                $orderData['id'] = $lid;

                if (!empty($this->cartStorage)) {
                    foreach ($this->cartStorage as $id => $obj) {
                        $orderItem = array
                        (
                            'id' => 'null',
                            'order_id' => $lid,
                            'cat_id' => $obj['realid'],
                            'count' => $obj['count'],
                            'name' => $obj['object']['_main']['Name'],
                            'comments' => $obj['comments'],
                            'price' => $obj['price']
                        );

                        if (isset($obj['skuObject'])) {
                            $orderItem['sku_serialized'] = serialize($obj['skuObject']);
                        }

                        XPDO::insertIN('ishop_orders_goods', $orderItem);
                        unset ($orderItem['sku_serialized']);

                        $orderItem['sum'] = $obj['price'] * $obj['count'];
                        $this->_TMS->addMassReplace('ishop_cart_object_email', $obj);
                        $this->_TMS->addMassReplace('ishop_cart_object_email', $orderItem);
                        $this->_TMS->parseSection('ishop_cart_object_email', true);
                    }
                }


                $this->orderData = $orderData;

                $this->_TMS->addMassReplace('ishop_cart_email', $orderData);

                if ($this->tunes['notifyAdmin']) {
                    $m = xCore::incModuleFactory('Mail');
                    $m->From($this->tunes['emailNotifyFrom']);
                    $m->To($this->tunes['emailNotifyList']);
                    $m->Content_type('text/html');
                    $m->Subject($emailSubject);
                    $m->Body($this->_TMS->parseSection('ishop_cart_email'), xConfig::get('GLOBAL', 'siteEncoding'));
                    $m->Priority(2);
                    $m->Send();
                }


                if ($this->tunes['notifyUser']) {
                    $m = xCore::incModuleFactory('Mail');
                    $m->From($this->tunes['emailNotifyFrom']);
                    $m->To($userData['email']);
                    $m->Content_type('text/html');
                    $m->Subject($emailSubject);
                    $m->Body($this->_TMS->parseSection('ishop_cart_email'), xConfig::get('GLOBAL', 'siteEncoding'));
                    $m->Priority(2);
                    $m->Send();
                }

                return true;
            }
        }
    }

    public function createGuestUser($data)
    {
        if (XPDO::insertIN('ishop_orders_clients_guest', $data)) {
            return XPDO::getLastInserted();
        }
    }

    public function submitOrder($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);


        if (!empty($this->cartStorage)) {

            if ($_SESSION['siteuser']['authorized']) {
                $this->userId = $_SESSION['siteuser']['id'];

            } else {

                if ($_POST['name'] && $_POST['email'] && $_POST['phone']) {
                    $userData = $this->processUserData($_POST);

                    $guestData = array
                    (
                        'name' => $userData['name'],
                        'surname' => $userData['surname'],
                        'lastname' => $userData['lastname'],
                        'email' => $userData['email']
                    );

                    $guestData['id'] = 'NULL';
                    $this->guestUserId = $this->createGuestUser($guestData);

                } else {

                    return $this->_TMS->parseSection('ishop_order_submit_user_info_failed');

                }
            }

            if ($this->goodsToOrder($_POST)) {
                XRegistry::get('EVM')->fire($this->_moduleName . '.goodsToOrder:after', array('orderData' => $this->orderData, 'cart' => $this->cartStorage));

                if (isset($_SESSION['siteuser']['cart'])) unset($_SESSION['siteuser']['cart']);


                if (!empty($this->orderData['paysystem'])) {

                    if ($paysystem = $this->paysystemCall($this->orderData['paysystem'])) {
                        if (method_exists($paysystem, 'processOrder')) {
                            $paymentProccesed = $paysystem->processOrder($this->orderData, $userData, $this);

                            if (!empty($paymentProccesed['orderNum'])) {

                                XPDO::updateIN('ishop_orders', (int)$this->orderData['id'], array('paysystem_order_num' => $paymentProccesed['orderNum']));
                            }

                        }
                    }

                }

                $this->cartStorage->clear();

                $this->_TMS->addMassReplace('ishop_order_submit_ok', $this->orderData);
                $this->_TMS->addMassReplace('ishop_order_submit_ok', $paymentProccesed);

                return $this->_TMS->parseSection('ishop_order_submit_ok');

            } else {

                return $this->_TMS->parseSection('ishop_order_submit_failed');
            }
        }
    }


    public function paysystemCall($paysystemName)
    {
        static $paysystem;

        if (empty($paysystem)) {

            $systemFile = xConfig::get('PATH', 'MODULES') . 'ishop/paysystems/' . $paysystemName . '/' . $paysystemName . '.php';
            include_once($systemFile);
            $paysystemName = $paysystemName . 'Payment';
            if (file_exists($systemFile)) $paysystem = new $paysystemName($this);
        }

        return $paysystem;

    }

    public function _calculateOrder()
    {
        $allCount = $itemsCount = $itemSum = $orderSum = 0;


        if (count($this->cartStorage) > 0) {
            foreach ($this->cartStorage as $key => $obj) {
                $itemSum = $obj['price'] * $obj['count'];
                $orderSum += $itemSum;
                $allCount += $obj['count'];
            }

            $itemsCount = count($this->cartStorage);
        }

        $mainCurrency = $this->_commonObj->getCurrentCurrency();
        $mainCurrency=current($mainCurrency);
        if ($_SESSION['currency']['rate']) {
            $orderSum = $orderSum * $_SESSION['currency']['rate'];
        }

        $order = array
        (
            'orderSum' => $orderSum,
            'orderSumFormatted' => number_format($orderSum,$mainCurrency['divider'], $mainCurrency['separator'], ' '),
            'allCount' => $allCount,
            'itemsCount' => $itemsCount,

        );

        return $order;
    }


    public function calculateCart()
    {

        $orderSum = 0;
        $allCount = 0;

        if ($this->cartStorage) {
            foreach ($this->cartStorage as $key => $obj) {
                $obj['priceSum'] = $obj['price'] * $obj['count'];
                $orderSum += $obj['priceSum'];
                $allCount += $obj['count'];
                $this->cartStorage[$key] = $obj;
            }
        }

        return array
        (
            'orderSum' => $orderSum,
            'count' => count($this->cartStorage),
            'allCount' => $allCount
        );
    }


    public function showBasket($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);
        $catalog = xCore::loadCommonClass('catalog');

        if (count($this->cartStorage) > 0) {
            $pages = xCore::loadCommonClass('pages');
            $catalog = xCore::loadCommonClass('catalog');
            $catalogPage = $pages->createPagePath($params['params']['catalogServerPage']);
            $currentPageLink = XRegistry::get('TPA')->pageLinkHost;

            foreach ($this->cartStorage as $key => $obj) {
                if (!$obj['object']['_main']['link']) {
                    $obj['object']['_main']['link'] = $catalog->buildLink($obj['object']['_main']['id'], $params['params']['catalogServerPage']);
                }

                $obj['removeLink'] = $currentPageLink . '/~remove/?id=' . $key;

                $obj = $this->rehandleSkuFrontObject($obj);

                $this->cartStorage[$key] = $obj;
            }


            $cartInfo = $this->calculateCart();

            $links = array
            (
                'orderLink' => $currentPageLink . '/~order',
                'removeAll' => $currentPageLink . '/~removeall',
                'removeSelected' => $currentPageLink . '/~remove',
                'catalogPageLink' => $catalogPage,
            );

            $cartInfo = array_merge($cartInfo, $links);
            $this->_TMS->addReplace('ishopCart', 'objects', $this->cartStorage->get());
            $this->_TMS->addMassReplace('ishopCart', $cartInfo);
            return $this->_TMS->parseSection('ishopCart');
        } else {
            return $this->_TMS->parseSection('cartEmpty');
        }
    }

    public function showBasketStatus($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $pages = xCore::loadCommonClass('pages');

        $this->_TMS->addMassReplace('showBasketStatus',
            array('cartPageLink' => $pages->createPagePath($params['params']['basketPage'])));
        $this->_TMS->addMassReplace('showBasketStatus', $this->_calculateOrder());

        $this->_TMS->addMassReplace('showBasketStatus', array('objects' => $this->cartStorage->get()));
        return $this->_TMS->parseSection('showBasketStatus');
    }


    public function addToCart($id, $count = 1, $isSku = false, $extendedData = array())
    {
        $realId = $id;


        $catalog = xCore::loadCommonClass('catalog');

        if (!$count) $count = 1;

        if (isset($this->cartStorage[$id])) {
            $obj = $this->cartStorage[$id];

            if (isset($extendedData['updateCartCount'])) {
                $obj['count'] = $count;
            } else {
                $obj['count'] += $count;
            }

            $_SESSION['ishop']['lastAdded'] = $obj;

            return $this->cartStorage[$id] = $obj;

        } else {

            if ($isSku) {
                $skuObj = $catalog->_sku->getNodeInfo($realId);
                $skuObj = $catalog->skuHandleFront(array($skuObj));
                $skuObj = $skuObj[0];
                $catId = $skuObj['netid'];
                $price = $skuObj['params'][$this->tunes['pricePropertySKU']];
                $priceValue = $price['value'];
                $catObject = $catalog->_tree->getNodeInfo($catId);
            } else {
                $catId = $realId;
                $catObject = $catalog->_tree->getNodeInfo($catId);
                $priceValue = $price = $catObject['params'][$_SESSION['userPriceCategory']];

            }


            $pages = xCore::loadCommonClass('pages');
            $objectInfo = $catalog->convertToPSG($catObject);


            if ($extendedData['outerPrice']) $price['value'] = $extendedData['outerPrice'];

            $evmResult = XRegistry::get('EVM')->fire($this->_moduleName . '.addToCart:beforeStorageSet', array('extendedData' => $extendedData, 'object' => $objectInfo, 'skuObject' => $skuObj));
            if ($evmResult) extract($evmResult, EXTR_OVERWRITE);


            if ($catObject) {
                $addObj = $this->cartStorage[$id] = array
                (
                    'count' => $count,
                    'isSKU' => $isSku,
                    'price' => (float)str_replace(array(',', ' '), array('.', ''), $priceValue),
                    'realid' => $realId,
                    'extendedData' => $extendedData,
                    'object' => $objectInfo,
                    'skuObject' => $skuObj
                );


                $_SESSION['ishop']['lastAdded'] = $addObj;

                return $addObj;

            }
        }
    }

    public function remove($params)
    {
        if (is_array($_POST['order']['remove'])) {
            foreach ($_POST['order']['remove'] as $rem) {
                unset ($_SESSION['siteuser']['cart'][$rem]);
            }
        }

        if (isset($this->cartStorage[$_GET['id']])) {
            unset ($this->cartStorage[$_GET['id']]);
        }

        return $this->showBasket($params);
    }

    public function removeall($params)
    {
        $this->cartStorage->clear();
        return $this->showBasket($params);
    }

    public function paymentSubmit($params)
    {
        $paysystem = $this->paysystemCall($_GET['paysystem']);
        if (method_exists($paysystem, 'paymentSubmit')) {
            return $paysystem->paymentSubmit($params);

        }


    }

    public function paymentFail($params)
    {
        $paysystem = $this->paysystemCall($_GET['paysystem']);

        if (method_exists($paysystem, 'paymentFail')) {
            return $paysystem->paymentFail($params);
        }


    }


    public function paymentSuccess($params)
    {
        $paysystem = $this->paysystemCall($_GET['paysystem']);

        if (method_exists($paysystem, 'paymentSuccess')) {
            return $paysystem->paymentSuccess($params);
        }

    }


    public function getOrderGoods($orderId)
    {
        if ($orderId) {
            if ($ishopGoods = XPDO::selectIN('*', 'ishop_orders_goods', $where = ' order_id = ' . $orderId)) {
                foreach ($ishopGoods as $k => &$v) {
                    if ($v['sku_serialized']) {
                        $v['sku'] = unserialize($v['sku_serialized']);
                        $v['sum'] = $v['price'] * $v['count'];
                        $ishopGoods[$k] = $v;
                    }
                }

                return $ishopGoods;
            }
        }
    }

    public function getClientOrders($params)
    {
        if (is_array($params['status'])) {
            $status = 'status in (' . implode(',', $params['status']) . ') AND ';
        } elseif ($params['status']) {
            $status = 'status=' . $params['status'] . ' AND ';
        }

        if ($params['userId']) {
            $statuses = $this->_commonObj->getStatusesList();

            if ($ishopOrders = XPDO::selectIN('*', 'ishop_orders',
                $status . ' client_id =' . $params['userId'] . ' order by date DESC')
            ) {
                foreach ($ishopOrders as $k => &$v) {
                    $v['status'] = $statuses[$v['status']];
                    $v['paysystem'] = $this->_commonObj->getPaySystemName($v['paysystem']);
                    $ishopOrders[$k] = $v;
                }

                return $ishopOrders;
            }
        }
    }

    public function finishEditedOrderSubmit($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);

        if (!empty($_GET['hash'])) {
            $order = $this->_commonObj->getOrderDataByHash($_GET['hash']);
            $order['sums'] = $this->_commonObj->proccessOrderSum($order);

            $this->_commonObj->setOrderPaysystem($order['id'], $_POST['paysystem']);
            $this->_commonObj->setOrderStatus($order['id'], $this->tunes['defaultOrderStatus']);

            $this->_TMS->addMassReplace('finishEditedOrderSubmitOk', array('order' => $order));


            if ($order['client_guest_id']) {
                $user = $this->_commonObj->getGuestCustomer($order['client_guest_id']);
            }


            $this->_TMS->addMassReplace('finishEditedOrderMailSubject', array('order' => $order));
            $emailSubject = $this->_TMS->parseSection('finishEditedOrderMailSubject');


            $this->_TMS->addMassReplace('finishEditedOrderMail', array('user' => $user, 'order' => $order));
            $orderMailText = $this->_TMS->parseSection('finishEditedOrderMail');


            if (!empty($_POST['paysystem'])) {

                if ($paysystem = $this->paysystemCall($_POST['paysystem'])) {
                    if (method_exists($paysystem, 'processOrder')) {

                        $paymentProccesed = $paysystem->processOrder($order, $user, $this);

                        if (!empty($paymentProccesed['orderNum'])) {

                            XPDO::updateIN('ishop_orders', (int)$order['id'], array('paysystem_order_num' => $paymentProccesed['orderNum']));
                        }

                    }
                }

            }


            if ($this->tunes['notifyAdmin']) {
                $m = xCore::incModuleFactory('Mail');
                $m->From($this->tunes['emailNotifyFrom']);
                $m->To($this->tunes['emailNotifyList']);
                $m->Content_type('text/html');
                $m->Subject($emailSubject);
                $m->Body($orderMailText, xConfig::get('GLOBAL', 'siteEncoding'));
                $m->Priority(2);
                $m->Send();
            }

            if (isset($user)) {

                if ($this->tunes['notifyUser']) {
                    $m = xCore::incModuleFactory('Mail');
                    $m->From($this->tunes['emailNotifyFrom']);
                    $m->To($user['email']);
                    $m->Content_type('text/html');
                    $m->Subject($emailSubject);
                    $m->Body($orderMailText, xConfig::get('GLOBAL', 'siteEncoding'));
                    $m->Priority(2);
                    $m->Send();
                }

            }


            $this->_TMS->addMassReplace('finishEditedOrderSubmitOk', $order);
            $this->_TMS->addMassReplace('finishEditedOrderSubmitOk', $paymentProccesed);


            return $this->_TMS->parseSection('finishEditedOrderSubmitOk');
        }

    }

    public function finishEditedOrder($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);
        $pages = xCore::loadCommonClass('pages');

        $order = $this->_commonObj->getOrderDataByHash($_GET['hash']);

        if (!empty($order) && $order['status'] == $this->tunes['editedStatus']) {

            if ($order['client_guest_id']) {
                $user = $this->_commonObj->getGuestCustomer($order['client_guest_id']);

            } elseif ($order['client_id']) {
                $user = $this->_commonObj->getRegisteredClientsRange(array($order['client_id']));
                $userData = $user[0];
                $user = array();
                $user['name'] = $userData['params']['name'] . ' ' . $userData['params']['surname'] . ' ' . $userData['params']['patronymic'];
                $user['email'] = $userData['params']['email'];

            }

            $sums = $this->_commonObj->proccessOrderSum($order);
            $goods = $this->_commonObj->getGoodOrders($order['id']);

            $deliveries = $this->_commonObj->getDeliveryList();
            $order['delivery'] = $deliveries[$order['delivery_id']];


            $pages = xCore::loadCommonClass('pages');
            $link = $pages->createPagePath($this->tunes['notFinishedOrdersUrl']);
            $link .= '/~finishEditedOrderSubmit/?hash=' . $order['hash'];


            $this->_TMS->addMassReplace('finishEditedOrder', array('submitFinishedOrderLink' => $link,
                'objects' => $goods, 'orderSums' => $sums, 'order' => $order, 'client' => $user));
            return $this->_TMS->parseSection('finishEditedOrder');

        } else {

            $this->_TMS->addMassReplace('finishEditedOrderFail', array('error' => 'order-does-not-exist'));
            return $this->_TMS->parseSection('finishEditedOrderFail');
        }

    }


    public function updateCartCount($params)
    {
        if (!empty($params['id']) && !empty($params['count'])) {
            if (isset($this->cartStorage[$params['id']])) {
                $obj = $this->cartStorage[$params['id']];
                $obj['count'] = $params['count'];
                $this->cartStorage[$params['id']] = $obj;
            }
        }

        if (isset($params['callback']) && is_array($params['callback'])) {
            $callBacksCount = count($params['callback']);
            for ($k = 0; $k <= $callBacksCount; $k++) {
                $func = trim($params['callback'][$k]);
                if (method_exists($this, $func)) {
                    $this->$func();
                }
            }
        } else if (isset($params['callback']) && is_string($params['callback'])) {
            $func = trim($params['callback']);
            if (method_exists($this, $func)) {
                $this->$func();
            }
        }
    }

}
