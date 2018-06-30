<?php

class webpayPayment extends payment
{
    public function __construct($ishopInstance)
    {
        parent::__construct();
        $this->ishopInstance = $ishopInstance;
    }


    public function processOrder($orderData, $userData)
    {


        $this->ishopInstance->loadModuleTemplate('webpay.paysystem.html');

        $paysystemData = $this->ishopInstance->_commonObj->getPaysystemData('webpay');
        $webpayData = $paysystemData['params'];


        if (!$this->ishopInstance->cartStorage->get()) {

            $webpayData['cartItems'] = $this->ishopInstance->_commonObj->getGoodOrders($orderData['id']);

        } else {

            $webpayData['cartItems'] = $this->ishopInstance->cartStorage->get();
        }


        $webpayData['wsb_seed'] = time();
        $_SESSION['siteuser']['paysystems']['webpay_order_num'] = $webpayData['wsb_order_num'] = $webpayData['wsb_seed'] . '-' . substr(session_id(), 0, 6);


        if (!empty($orderData['delivery_price'])) $webpayData['wsb_shipping_price'] = (float)$orderData['delivery_price'];
        if (!empty($orderData['discount_sum'])) $webpayData['wsb_discount_price'] = $orderData['discount_sum'];


        $webpayData['wsb_total'] = $orderData['sums']['orderTotalSum'];

        $webpayData['wsb_signature'] = sha1($webpayData['wsb_seed'] . $webpayData['wsb_storeid'] . $webpayData['wsb_order_num'] . $webpayData['wsb_test'] . $webpayData['wsb_currency_id'] . $webpayData['wsb_total'] . $webpayData['secret_key']);

        $webpayData['wsb_email'] = $userData['email'];
        $webpayData['wsb_phone'] = $userData['phone'];

        $this->ishopInstance->_TMS->addMassReplace('webpay', $webpayData);


        return array('orderNum' => $webpayData['wsb_order_num'], 'paysystemHtml' => $this->ishopInstance->_TMS->parseSection('webpay'));

    }


    public function paymentSuccess($params)
    {
        $this->ishopInstance->loadModuleTemplate('webpay.paysystem.html');
        $orderData = $this->processPayment();
        $this->ishopInstance->_TMS->addMassReplace('success', array('orderData' => $orderData));
        return $this->ishopInstance->_TMS->parseSection('success');

    }

    public function paymentFail($params)
    {
        $this->ishopInstance->loadModuleTemplate('webpay.paysystem.html');
        return $this->ishopInstance->parseSection('fail');
    }


    private function processPayment()
    {
        $orderId = false;

        if (isset($_POST['site_order_id'])) $orderId = $_POST['site_order_id'];
        if (isset($_POST['wsb_tid'])) $tid = $_POST['wsb_tid'];

        if (isset($_GET['wsb_order_num'])) $orderId = $_GET['wsb_order_num'];
        if (isset($_GET['wsb_tid'])) $tid = $_GET['wsb_tid'];


        if ($orderId) {
            $order = $this->ishopInstance->_commonObj->getOrderByPaysystem($orderId);

            if (!empty($order)) {

                $order['payment_date'] = $orderData['payment_date'] = (isset($_POST['LMI_SYS_PAYMENT_DATE'])) ? $_POST['LMI_SYS_PAYMENT_DATE'] : time();
                $order['status'] = $orderData['status'] = $this->ishopInstance->tunes['payedStatus'];
                XPDO::updateIN('ishop_orders', (int)$order['id'], $orderData);

                return $order;
            }
        } else {
            return false;

        }

    }

    public function paymentSubmit($params)
    {
        $this->ishopInstance->loadModuleTemplate('webpay.paysystem.html');
        $this->logData();

        if ($this->processPayment()) {
            header('Status: 200 Ok');
            die();

        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            die();
        }

    }

    private function logData()
    {
        $d = date('Y M j G-i-s');
        $post = print_r($_POST, true);
        $f = fopen('rateslog/' . $d . '.txt', 'a+');
        fwrite($f, ">>" . $post . "<<");
        fclose($f);

    }


}

?>