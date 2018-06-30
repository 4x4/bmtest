<?php

class ishopTpl extends xTpl implements xModuleTpl
{

    public function __construct($module)
    {
        parent::__construct($module);
    }

    public function getDeliveryList()
    {
        $id = $this->_commonObj->getBranchId('DELIVERY');

        if ($deliveryList = $this->_tree->selectStruct('*')->selectParams('*')->childs($id)->format('keyval', 'basic', 'params')->run()) {
            return $deliveryList;
        }

    }


    public function getCurrenciesList($params)
    {
        return $this->_commonObj->getCurrenciesList(true);

    }

    public function getStocksList()
    {
        return $this->_commonObj->getStocksList();
    }

    public function toCurrency($params)
    {
        if (!empty($params['to']) && !empty($params['from'])) {
            $currencies = $this->_commonObj->getCurrenciesList(true);
            $currenciesMap = XARRAY::asKeyVal($currencies, 'currencyId');
            $currenciesMap = array_flip($currenciesMap);
            $to = $currenciesMap[$params['to']];
            $from = $currenciesMap[$params['from']];
            return ($currencies[$to]['rate'] / $currencies[$from]['rate']) * $params['value'];
        }
    }

    public function getCart($params)
    {
         return $this->cartStorage->get();
    }

    public function getPaysystemsList()
    {
        $ancestor = $this->_commonObj->getBranchId('PAYSYSTEM');
        $data = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@ancestor', '=', $ancestor), array('active', '=', 1))->sortby('priority', 'desc')->run();
        return $data;
    }


    public function calculateShopCart($params)
    {
        return $this->calculateCart();
    }

    public function getCurrencyById($params)
    {
        if (!empty($params['id'])) {
            $currency = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@obj_type', '=', '_CURRENCY'), array('@id', '=', $params['id']))->singleResult()->run();
            return $currency;
        }
    }

    public function getDeliveryByBasic($params)
    {
        if (!empty($params['basic'])) {
            $delivery = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@obj_type', '=', '_DELIVERY'), array('@basic', '=', $params['basic']))->singleResult()->run();
            return $delivery;
        }
    }

    public function getCurrentCurrency()
    {
        return $_SESSION['cacheable']['currency'];
    }

    public function getUserOrderGoods($params)
    {
        $orderGoods = $this->getOrderGoods($params['id']);

        if (!empty($orderGoods)) {
            foreach ($orderGoods as $key => $value) {
                if (!empty($value['sku_serialized'])) {
                    $orderGoods[$key]['sku_serialized'] = unserialize($value['sku_serialized']);
                }
            }

            reset($orderGoods);

            return $orderGoods;
        } else {
            return false;
        }
    }


    public function transformToCurrencyFormat($params)
    {
        if (!empty($params['toMain'])) {
            $currency = $this->_commonObj->getCurrentCurrency();
            $currency = current($currency);

            $decimals = $params['decimals'] ? $params['decimals'] : $currency['divider'];
            $decPoint = $currency['separator'];
        } else {
            $decimals = !empty($params['decimals']) ? $params['decimals'] : 0;
            $decPoint = !empty($params['decPoint']) ? $params['decPoint'] : '.';
        }

        $thousandsSep = !empty($params['thousandsSep']) ? $params['thousandsSep'] : ' ';

        return number_format($params['value'], $decimals, $decPoint, $thousandsSep);
    }


    public function incart($params)
    {
        if (!empty($_SESSION['siteuser']['cart'][$params['id']])) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaySystemName($params)
    {
        if (!empty($params['basic']) && $paySystemName = $this->_commonObj->getPaySystemName(trim($params['basic']))) {
            return $paySystemName;
        }
    }


    public function getAssist($params)
    {

        $this->loadModuleTemplate('assist.paysystem.html');
        $data = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@obj_type', '=', '_PAYSYSTEM'), array('@basic', '=', 'assist'))->singleResult()->run();
        $data = $data['params'];

        $data['cartItems'] = $this->cartStorage->get();
        $data['OrderNumber'] = $_SESSION['siteuser']['OrderNumber'] = substr(session_id(), 0, 5) . time();


        $order = $this->calculateCart();
        $data['OrderAmount'] = $order['orderSum'];
        $this->_TMS->addMassReplace('assist', $data);

        return $this->_TMS->parseSection('assist');
    }

}
