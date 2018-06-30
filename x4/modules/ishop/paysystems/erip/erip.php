<?php

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");
error_log("Hello, errors!");

class eripPayment
{
    public function validate()
    {
        return true;
    }

    public function checkSign()
    {
        $XML = $this->request;
        $XML = preg_replace('/^.*\<\?xml/sim', '<?xml', $XML);
        $XML = preg_replace('/\<\/ServiceProvider_Request\>.*/sim', '</ServiceProvider_Request>', $XML);

        if (get_magic_quotes_gpc()) {
            $XML = stripslashes($XML);
        }

        $signature = '';


        if (preg_match('/SALT\+MD5\:\s(.*)/', $_SERVER['HTTP_SERVICEPROVIDER_SIGNATURE'], $matches)) {
            $signature = $matches[1];
        }


        if (strcasecmp(md5($this->DS_SALT . $XML), $signature)) {
            $msgError = "Некорректная цифровая подпись";
            $this->sendError($msgError);
        }
    }

    public function init($request, $salt, $shopSite)
    {

        $this->ishop = xCore::moduleFactory('ishop.back');
        $this->paySystem = $this->ishop->_commonObj->getPaysystemData('erip');


        $this->DS_SALT = $salt;
        $this->request = $request;
        $this->SHOP_SITE = $shopSite;

        $this->checkSign();

        $xml = substr($this->request, strpos($this->request, "<ServiceProvider_Request>"));
        $xml = iconv("WINDOWS-1251", "UTF-8", $xml);
        $this->requestArray = $this->xml2array($xml);


        $orderNumber = $this->requestArray['ServiceProvider_Request']['PersonalAccount'];
        $this->requestId = $this->requestArray['ServiceProvider_Request']['RequestId'];
        Common::writeLog('order number get:' . $orderNumber);

        if ($this->DS_SALT == "" || (!$orderNumber)) {
            $msg_error = "Некорректный запрос";
            $this->sendError($msg_error);
        }


        $this->order = $this->ishop->_commonObj->getOrderByPaysystem($orderNumber);

        Common::writeLog($this->order);

        if (empty($this->order)) {
            $msg_error = "Заказ с данным номером отсутствует на сайте ";
            $this->sendError($msg_error);
        }


        $this->orderNumber = $orderNumber;

        $orderExpire = $this->order['date'] + $this->paySystem['params']['timeInterval'];

        if ($orderExpire < time()) {
            $msg_error = "Заказ просрочен. Оформите заказ заново на сайте " . $this->SHOP_SITE;
            $this->sendError($msg_error);

        }


        switch ($this->requestArray['ServiceProvider_Request']['RequestType']) {
            case 'ServiceInfo':


                $this->serviceInfo();

                break;

            case 'TransactionStart':
                $this->checkRequestId();
                $this->transactionStart();

                break;

            case 'TransactionResult':
                $this->checkRequestId();
                $this->transactionResult();

                break;

            default:
                $msg_error = "Некорректный тип запроса: " . $request['ServiceProvider_Request']['RequestType'];

                $this->sendError();
        }
    }

    public function sendErrorMail()
    {
        $mailsubject = sprintf("Ошибка оплаты заказа N %d через %s", $order_no, $PAYMENT_SYSTEM);
        $mailbody
            .= "<br><br><a href=\"{$this->SHOP_SITE}/admin/emarket/order_edit/{$order_no}\"/>Просмотреть заказ N {$order_no}</a>";
        $this->sendMail($mailbody);
    }

    public function sendSuccessMail()
    {
        $mailbody = ($mailbody == '') ? $mailsubject : $mailbody;
        $mailbody
            .= "<br><br><a href=\"{$this->SHOP_SITE}/admin/emarket/order_edit/{$order_no}\"/>Просмотреть заказ N {$order_no}</a>";
        $this->sendMail($mailbody);
    }

    public function sendMail($mailbody)
    {

        $m = xCore::incModuleFactory('Mail');
        $adminEmail = xConfig::get('GLOBAL', 'admin_email');
        $m->From($adminEmail);
        $m->To(array($adminEmail));
        $m->Content_type('text/html');
        $m->Subject('Оплата IPAY(ЕРИП)');
        $m->Body($mailbody, xConfig::get('GLOBAL', 'siteEncoding'));
        $m->Priority(2);
        $m->Send();

    }


    public function checkOrderValid($targetStatus = null)
    {
        if (empty($targetStatus)) {
            $targetStatus = $this->paySystem['params']['readyToPayStatus'];
        }

        if ($this->order['status'] != $targetStatus) {

            $msg_error = sprintf("Заказ номер %d не разрешен для оплаты. Обратитесь в тех. поддержку сайта " . $this->SHOP_SITE, $this->orderNumber);

            switch ($this->order['status']) {
                case $this->paySystem['params']['cancelStatus']:

                    $msg_error = sprintf("Заказ номер %d отменен. Сформируйте заказ заново на сайте", $this->orderNumber);

                    break;

                case $this->paySystem['params']['orderPayedStatus']:

                    $msg_error = sprintf("Заказ номер %d уже оплачен", $this->orderNumber);

                    break;

                case $this->paySystem['params']['transactionBlockingStatus']:

                    $msg_error = sprintf("Заказ %d находится  в процессе оплаты ", $this->orderNumber);

                    break;

            }

            $this->sendError($msg_error);
        }

    }

    public function checkRequestId()
    {
        $requestId = XCache::serializedRead('erip', $this->orderNumber);

        if (empty($requestId)) {
            XCache::serializedWrite($this->requestId, 'erip', $this->orderNumber);
            $requestId = $this->requestId;
        } else if ($this->requestId != $requestId) {
            $msg_error = sprintf("Сессия заказа %d уже стартовала ранее", $this->orderNumber);
            $this->sendError($msg_error);
        }
    }

    public function serviceInfo()
    {

        $this->checkOrderValid();

        $answer = $this->getXMLtemplate('service_info');

        $this->order['orderSums']['orderTotalSum'] = $this->order['orderSums']['orderTotalSum'];

        $answer = preg_replace('~%ORDER_NUMBER%~sim', $this->order['paysystem_order_num'], $answer);
        $answer = preg_replace('~%AMOUNT%~sim', $this->order['orderSums']['orderTotalSum'], $answer);
        $answer = preg_replace('~%ORDER_INFO%~sim', "", $answer);

        $answer = preg_replace('~%AMOUNT_PRECISION%~sim', 2, $answer);
        $answer = preg_replace('~%FIRSTNAME%~sim', $this->order['client']['name'], $answer);
        $answer = preg_replace('~%PATRONYMIC%~sim', $this->order['client']['lastname'], $answer);
        $answer = preg_replace('~%SURNAME%~sim', $this->order['client']['surname'], $answer);
        $answer = preg_replace('~%CITY%~sim', "", $answer);
        $answer = preg_replace('~%STREET%~sim', "", $answer);
        $answer = preg_replace('~%HOUSE%~sim', "", $answer);
        $answer = preg_replace('~%BUILDING%~sim', "", $answer);
        $answer = preg_replace('~%APARTAMENT%~sim', "", $answer);
        $this->sendAnswer($answer);

    }

    public function sendAnswer($answer)
    { //

        Common::WriteLog($this->DS_SALT);

        $answer = iconv("UTF-8", "WINDOWS-1251", $answer);

        $md5 = md5($this->DS_SALT . $answer);

        header('Content-Type: text/html; charset=windows-1251');
        header("ServiceProvider-Signature: SALT+MD5: $md5");

        Common::WriteLog($answer);

        echo $answer;
        die();
    }

    public function getXMLtemplate($tpl)
    {
        return file_get_contents(PATH_ . "x4/modules/ishop/paysystems/erip/xml/" . $tpl . ".xml");

    }

    public function sendError($msgError)
    {

        $msgError = iconv("UTF-8", "WINDOWS-1251", $msgError);

        $answer = $this->getXMLtemplate('error');

        $answer = preg_replace('~%ERROR%~sim', $msgError, $answer);

        $md5 = md5($this->DS_SALT . $answer);

        header("ServiceProvider-Signature: SALT+MD5: $md5");
        header('Content-Type: text/html; charset=windows-1251');

        Common::WriteLog($answer);
        echo $answer;
        die();


    }


    public function transactionResult()
    {

        $this->checkOrderValid($this->paySystem['params']['transactionBlockingStatus']);

        if (!empty($this->requestArray['ServiceProvider_Request']['TransactionResult']['ErrorText'])) {

            $answer = $this->getXMLtemplate('transaction_result_cancel');
            $answer = preg_replace('~%ORDER_NUMBER%~sim', $this->order['paysystem_order_num'], $answer);

            Common::WriteLog($answer);

            try {

                $this->ishop->_commonObj->setOrderStatus($this->order['id'], $this->paySystem['params']['cancelStatus']);

            } catch (Exception $e) {
                $msg_error = sprintf("Ошибка установки статуса отмененного заказа %d", $this->orderNumber);
                $mailbody = $msg_error;
                $this->sendError($mailbody);
            }

            $this->sendAnswer($answer);
        }

        $answer = $this->getXMLtemplate("transaction_result_success");
        $answer = preg_replace('~%SHOP_SITE%~sim', $this->SHOP_SITE, $answer);
        $answer = preg_replace('~%ORDER_INFO%~sim',
            "Заказ успешно оплачен. Спасибо за покупку. Информацию о доставке товаров Вы можете уточнить на сайте "
            . $this->SHOP_SITE,
            $answer);


        try {
            $requestId = XCache::clear('erip', $this->orderNumber);
            $this->ishop->_commonObj->setOrderStatus($this->order['id'], $this->paySystem['params']['orderPayedStatus']);
        } catch (Exception $e) {
            $msg_error = sprintf("Ошибка установки статуса успешной оплаты заказа %d", $order_no);
            $mailbody = $msg_error;
            $this->sendError($mailbody);
        }


        $this->sendAnswer($answer);
    }


    public function transactionStart()
    {
        /*   if ($this->order->GetPaymentStatus() <> $ORDER_STATUS_CREATED)
               {
               $msg_error=sprintf("Заказ номер %d не разрешен для оплаты. Обратитесь в тех. поддержку сайта " . $this->SHOP_SITE,
                                  $order_no);

               switch ($this->order->GetPaymentStatus())
                   {
                   case $ORDER_STATUS_WAITING_FOR_PAY:
                       $msg_error=sprintf("Заказ номер %d находится в процессе оплаты", $order_no);

                       break;

                   case $ORDER_STATUS_PAYED:
                       $msg_error=sprintf("Заказ номер %d уже оплачен", $order_no);

                       break;

                   case $ORDER_STATUS_CANCELED:
                       if ($ORDER_STATUS_CANCELED <> $ORDER_STATUS_CREATED)
                           {
                           $msg_error=sprintf("Заказ номер %d был отменен", $order_no);
                           }

                       break;
                   }

               $this->sendError($msg_error);
               }

           if (($this->order->status_id <> $ORDER_STATUS_VERIFYED) && $ORDER_NOT_VERIFYED_PAY_ALLOW <> "1")
               {
               $msg_error
                        =sprintf(
                             "Заказ номер %d не разрешен для оплаты, т.к. не проверено наличие товаров на складе. Обратитесь в тех. поддержку сайта "
                                 . $this->SHOP_SITE,
                             $order_no);
               $mailbody=$msg_error;
               $this->sendErrorMail($msg_error);
               }

           */


        $this->order['orderSums']['orderTotalSum'] = $this->order['orderSums']['orderTotalSum'];


        if ($this->order['orderSums']['orderTotalSum'] <> $this->requestArray['ServiceProvider_Request']['TransactionStart']['Amount']) {
            $msg_error = "Прислана некорректная сумма заказа: " . $this->requestArray['ServiceProvider_Request']['TransactionStart']['Amount'];
            $mailbody = $msg_error;
            $this->sendErrorMail($msg_error);
        }


        $answer = $this->getXMLtemplate("transaction_start");
        $answer = preg_replace('~%TRX_ID%~sim', $this->order['paysystem_order_num'], $answer);
        $auth_ident = $this->requestArray['ServiceProvider_Request']['TransactionStart']['AuthorizationType_attr']['Ident'];
        $payment_system_trx_id = $this->requestArray['ServiceProvider_Request']['TransactionStart']['TransactionId'];

        $this->ishop->_commonObj->setOrderStatus($this->order['id'], $this->paySystem['params']['transactionBlockingStatus']);

        Common::writeLog($answer);

        /*try
            {
            $this->order->setPaymentStatus($ORDER_STATUS_WAITING_FOR_PAY);
            $this->order->payment_document_num=$payment_system_trx_id;
            }
        catch( Exception $e )
            {
            $msg_error
                     =sprintf("Невозможно начать оплату заказа %d. Обратитесь в тех. поддержку сайта " . $this->SHOP_SITE,
                              $order_no);
            $mailbody=$msg_error;
            $this->sendErrorMail($msg_error);
            }
		*/

        $this->sendAnswer($answer);
    }


    public function ReplPayUrl($url, $srv_no, $order_id, $amount, $provider_url)
    {
        $url = preg_replace('~!SRV_NO!~sim', $srv_no, $url);
        $url = preg_replace('~!ORDER_NO!~sim', $order_id, $url);
        $url = preg_replace('~!PROVIDER_URL!~sim', $provider_url, $url);
        $url = preg_replace('~!AMOUNT!~sim', $amount, $url);
        return $url;
    }

    public function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!function_exists('xml_parser_create')) {
            return array();
        }

        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values)
            return;

        $xml_array = array();

        $parents = array();

        $opened_tags = array();

        $arr = array();

        $current =& $xml_array;

        $repeated_tag_index = array();

        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);

            $result = array();

            $attributes_data = array();

            if (isset($value)) {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value;
            }

            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val;
                }
            }

            if ($type == "open") {
                $parent[$level - 1] =& $current;

                if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;

                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;

                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current =& $current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array
                        (
                            $current[$tag],
                            $result
                        );

                        $repeated_tag_index[$tag . '_' . $level] = 2;

                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }

                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current =& $current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;

                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                } else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }

                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array
                        (
                            $current[$tag],
                            $result
                        );

                        $repeated_tag_index[$tag . '_' . $level] = 1;

                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }

                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                }
            } elseif ($type == 'close') {
                $current =& $parent[$level - 1];
            }
        }

        return ($xml_array);
    }
}

;
?>
