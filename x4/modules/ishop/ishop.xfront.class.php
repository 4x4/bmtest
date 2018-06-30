<?php

class ishopXfront extends ishopFront
{


    public function addProductToCart($params)
    {
       
        $this->addToCart($params['id'], $params['count'], $params['isSku'], $params['extendedData']);
        $this->result['cart'] = $this->_calculateOrder();
        
        
    }

    public function getCartInfo($params)
    {
        $this->result['cartInfo'] = $this->_calculateOrder();

    }


    public function getCart($params)
    {
        $cart = $this->cartStorage->get();

        if ($cart) {
            $this->result['cartItems'] = $cart;

        }
    }

    public function getLastCartItems($params)
    {
        if (isset($_SESSION['ishop']['lastAdded'])) {
            $this->result['cartItems'] = $_SESSION['ishop']['lastAdded'];

        } else {

            $this->result['cartItems'] = array();
        }
    }

    public function calculateCartWithDelivery($params)
    {
        $this->result['delivery'] = $this->calculateDelivery($params['id']);

    }

    public function setOrderData($params)
    {

        if (!isset($_SESSION['siteuser']['orderData'])) {
            $_SESSION['siteuser']['orderData'] = array();
        }

        $_SESSION['siteuser']['orderData'] = array_merge($_SESSION['siteuser']['orderData'], $params['data']);

    }


    public function getCurrentCurrency()
    {
        $this->result['currency'] = $_SESSION['cacheable']['currency'];
    }


    public function submitOrderAsync($params)
    {
        $this->result['orderSubmited'] = true;
    }



}


