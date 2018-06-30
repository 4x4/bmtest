<?php
/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 ishop module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/ishop",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *
 * @SWG\Definition(
 *     definition="extendedData",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 *
 *   @SWG\Definition(
 *     definition="cartObject",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"productId"},
 *           @SWG\Property(property="id", type="integer", example="109"),
 *           @SWG\Property(property="count", type="integer", example="1"),
 *           @SWG\Property(property="isSku", type="bool", example="0"),
 *           @SWG\Property(property="extendedData", ref="#/definitions/extendedData")
 *
 *       )
 *    }
 *    ),
 * 
 *   @SWG\Definition(
 *     definition="orderDataExternal",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           @SWG\Property(property="id", type="string", example="paysystem"),
 *           @SWG\Property(property="delivery", type="integer", example="32"),
 *           @SWG\Property(property="deliveryTime", type="integer", example="112312331"),
 *           @SWG\Property(property="status",type="integer", example="12"),
 *           @SWG\Property(property="comments", type="string", example="comments here"),
 *           @SWG\Property(property="store_id", type="integer", example="22"),
 *           @SWG\Property(property="notes", type="string", example="notes here"),
 *           @SWG\Property(property="promo", type="string", example="909WER32"),
 *           @SWG\Property(property="orderType", type="string", example="notes here"),
 *       )
 *    }
 *    ),
 *
 *
 *   @SWG\Definition(
 *     definition="submitOrderData",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"name,email,phone"},
 *           @SWG\Property(property="doNotClearCartItems", type="bool", example="0"),
 *           @SWG\Property(property="name", type="string", example="Иван"),
 *           @SWG\Property(property="email", type="string", example="test@test.by"),
 *           @SWG\Property(property="phone", type="string", example="+375297211111"),
 *           @SWG\Property(property="street", type="string", example="пр. Независимости"),
 *           @SWG\Property(property="surname", type="string", example="Иванов"),
 *           @SWG\Property(property="lastname", type="string", example="Иванович"),
 *           @SWG\Property(property="city", type="string", example="Минск"),
 *           @SWG\Property(property="room", type="string", example="12"),
 *           @SWG\Property(property="index", type="string", example="222000"),
 *           @SWG\Property(property="orderData", ref="#/definitions/orderDataExternal")
 *       )
 *    }
 *    ),
 *
 *   @SWG\Definition(
 *     definition="add2CartOrderItem",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           @SWG\Property(property="id", type="integer", example="121186"),
 *           @SWG\Property(property="count", type="integer", example="1"),
 *           @SWG\Property(property="isSku", type="bool", example="0"),
 *       )
 *    }
 *    ),
 *
 *   @SWG\Definition(
 *     definition="add2CartOrderInfoData",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"name,email,phone"},
 *           @SWG\Property(property="id", type="string", example="paysystem"),
 *           @SWG\Property(property="delivery", type="integer", example="1099"),
 *           @SWG\Property(property="deliveryTime", type="integer", example="112312331"),
 *           @SWG\Property(property="status",type="integer", example="1079"),
 *           @SWG\Property(property="comments", type="string", example="Product added"),
 *           @SWG\Property(property="store_id", type="integer", example="1107"),
 *           @SWG\Property(property="notes", type="string", example="Notes here"),
 *           @SWG\Property(property="promo", type="string", example="909WER32"),
 *           @SWG\Property(property="orderType", type="string", example="default"),
 *       )
 *    }
 *    ),
 *
 *   @SWG\Definition(
 *     definition="add2CartOrderInfo",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"name,email,phone"},
 *           @SWG\Property(property="name", type="string", example="Иван"),
 *           @SWG\Property(property="email", type="string", example="test@test.by"),
 *           @SWG\Property(property="phone", type="string", example="+375297211111"),
 *           @SWG\Property(property="street", type="string", example="пр. Независимости"),
 *           @SWG\Property(property="surname", type="string", example="Иванов"),
 *           @SWG\Property(property="lastname", type="string", example="Иванович"),
 *           @SWG\Property(property="city", type="string", example="Минск"),
 *           @SWG\Property(property="room", type="string", example="12"),
 *           @SWG\Property(property="index", type="string", example="222000"),
 *           @SWG\Property(property="orderData", ref="#/definitions/add2CartOrderInfoData")
 *       )
 *    }
 *    ),
 *
 *   @SWG\Definition(
 *     definition="addToCartAndOrder",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"orderItem,orderInfo"},
 *           @SWG\Property(property="orderType", type="string", example="device"),
 *           @SWG\Property(property="orderItem", ref="#/definitions/add2CartOrderItem"),
 *           @SWG\Property(property="orderInfo", ref="#/definitions/add2CartOrderInfo"),
 *       )
 *    }
 *    )
 *
 *
 *)
 *
 **/

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class ishopApiJson
    extends xModuleApi
{
    public $ishopFront;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->ishopFront=xCore::moduleFactory('ishop.front');
    }

     /**
     * @SWG\Get(
     *     path="/getCartInfo",
     *     summary="gets cart info",
     *     operationId="getCartInfo",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="cart info")
     * )
     */

    public function getCartInfo($params)
    {
        return $this->ishopFront->_calculateOrder();

    }


     /**
     * @SWG\Get(
     *     path="/getCartItems",
     *     summary="gets all items in cart",
     *     operationId="getCartItems",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="items")
     * )
     */


     public function getCartItems($params)
     {
         return  $this->ishopFront->cartStorage->get();

     }


     /**
     * @SWG\Get(
     *     path="/removeCartItem/id/{id}",
     *     summary="removeCartItem by Id",
     *     operationId="removeCartItem",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="cart item id",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/cartObject"),
     *     ),
     *
     *     @SWG\Response(response=200, description="item removed")
     * )
     */

     public function removeCartItem($params)
     {

           if (isset($this->ishopFront->cartStorage[$params['id']]))
           {
                unset ($this->ishopFront->cartStorage[$params['id']]);

                return $this->ishopFront->cartStorage->get();

           }else{

                return $this->error(__FUNCTION__ . 'operation failed object id is not defined', 400);
            }

     }

     /**
     *  @SWG\Get(
     *     path="/removeAllCartItems",
     *     summary="remove all cart otems",
     *     operationId="removeAllCartItems",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="item removed")
     *      )
     */


    public function removeAllCartItems($params)
    {
        $this->ishopFront->cartStorage->clear();

    }


    /**
     * @SWG\Post(
     *     path="/submitOrder",
     *     summary="Submits order",
     *     operationId="submitOrder",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="submitOrderData",
     *         in="body",
     *         description="submit user order object",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/submitOrderData"),
     *     ),
     *     @SWG\Response(response=200, description="Order created"),
     * )
     */


    public function submitOrder($params,$data){

         if (!empty($this->ishopFront->cartStorage)) {

            if ($_SESSION['siteuser']['authorized']) {
                $this->ishopFront->userId = $_SESSION['siteuser']['id'];

            } else {

                if ($data['name'] or $data['phone']) {

                    $userData = $this->ishopFront->processUserData($data);

                    $guestData = array
                    (
                        'name' => $userData['name'],
                        'surname' => $userData['surname'],
                        'lastname' => $userData['lastname'],
                        'email' => $userData['email']
                    );

                    $guestData['id'] = 'NULL';
                    $this->guestUserId = $this->ishopFront->createGuestUser($guestData);

                } else {

                     return $this->error(__FUNCTION__ . ' not enough user data ', 400);

                }
            }

            if ($this->ishopFront->goodsToOrder($data['orderData'])) {

                XRegistry::get('EVM')->fire('ishop.goodsToOrder:after', array('orderData' => $this->ishopFront->orderData,
                'cart' =>  $this->ishopFront->cartStorage));

                if (isset($_SESSION['siteuser']['cart'])) unset($_SESSION['siteuser']['cart']);

                if (!empty($this->ishopFront->orderData['paysystem'])) {

                    if ($paysystem = $this->ishopFront->paysystemCall($this->orderData['paysystem'])) {
                        if (method_exists($paysystem, 'processOrder')) {
                            $paymentProccesed = $paysystem->processOrder($this->orderData, $userData, $this);

                            if (!empty($paymentProccesed['orderNum'])) {

                                XPDO::updateIN('ishop_orders', (int)$this->ishopFront->orderData['id'], array('paysystem_order_num' => $paymentProccesed['orderNum']));
                            }

                        }
                    }

                }

                    if(!(intval($data['doNotClearCartItems'])))
                    {
                        $this->ishopFront->cartStorage->clear();
                    }

                return array('orderSubmitted'=>true);

            } else {

                  return $this->error(__FUNCTION__ . 'not enough user data ', 400);
            }
        }



    }

        private function processUserData($extData)
    {
        $userFields = $this->ishopFront->userFields;

        foreach ($userFields as $field) {
            if (isset($extData[$field]) && $value = $extData[$field]) {
                $userData[$field] = $value;
            } elseif ($_SESSION['siteuser']['userdata']) {
                $userData[$field] = $_SESSION['siteuser']['userdata'][$field];
            }
        }

        return $userData;
    }



     /**
     * @SWG\Get(
     *     path="/getStoresList",
     *     summary="gets all stores",
     *     operationId="getStoresList",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="stores")
     * )
     */

     public function getStoresList($params)
     {
        $id = $this->_commonObj->getBranchId('STORE');
         return  $this->_commonObj->_tree->selectStruct('*')->selectParams('*')->childs($id,1)->run();
     }


     /**
     * @SWG\Post(
     *     path="/addToCart",
     *     summary="add to cart",
     *     operationId="addToCart",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="item",
     *         in="body",
     *         description="cart item",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/cartObject"),
     *     ),
     *
     *     @SWG\Response(response=200, description="order set")
     * )
     */

    public function addToCart($params,$data)
    {

        if (isset($data['id'])) {
            try {

                if(empty($data['count']))
                {
                    $data['count']=1;
                }

                $ishop=xCore::moduleFactory('ishop.front');
                $id=$ishop->addToCart($data['id'], $data['count'],(bool)$data['isSku'], $data['extendedData']);
                return array('id'=>$id);

            } catch (Exception $e) {
                  return $this->error(__FUNCTION__ . ' adding failed', 400);
            }

        }else{
            return $this->error(__FUNCTION__ . 'operation failed object id is not defined', 400);
        }
    }





    /**
     * @SWG\Post(
     *     path="/addToCartAndOrder",
     *     summary="Add to cart and submit order",
     *     operationId="add2CartOrder",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="addToCartAndOrder",
     *         in="body",
     *         description="items data for order",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/addToCartAndOrder"),
     *     ),
     *     @SWG\Response(response=200, description="order status result"),
     * )
     */

    public function addToCartAndOrder($params, $data)
    {
        $this->addToCart($params,$data['orderItem']);
        $this->submitOrder($params,$data['orderInfo']);
    }


      /**
     * @SWG\Get(
     *     path="/changeOrderStatus/orderId/{id}/status/{status}",
     *     summary="Set order status",
     *     operationId="changeOrderStatus",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order id ",
     *         required=true
     *     ),
     *
     *     @SWG\Parameter(
     *         name="status",
     *         in="path",
     *         description="status id (you can set any status list from cms interface)",
     *         required=true
     *     ),
     *
     *     @SWG\Response(response=200, description="order set")
     * )
     */

    public function changeOrderStatus($params)
    {
        if (isset($params['id'])) {

            try {
                $this->_commonObj->setOrderStatus($params['id'], $params['status']);
                return array('result' => true);
            } catch (Exception $e) {
                return $this->error(__FUNCTION__ . ' change status writing failed', 'changeOrderStatus-fail');
            }


        }
    }

     /**
     * @SWG\Get(
     *     path="/getOrders/fromDate/{fromDate}/toDate/{toDate}/status/{status}",
     *     summary="Get orders by date interval",
     *     operationId="getOrders",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="fromDate",
     *         in="path",
     *         description="order interval starts from(UNIX timestamp)",
     *         required=true
     *     ),
     *
     *     @SWG\Parameter(
     *         name="toDate",
     *         in="path",
     *         description="order interval ends with (UNIX timestamp)",
     *         required=false
     *     ),
     *
     *     @SWG\Parameter(
     *         name="status",
     *         in="path",
     *         description="order status id",
     *         required=false
     *     ),
     *
     *     @SWG\Response(response=200, description="order list result"),
     * )
     */

    public function getOrders($params)
    {

            if(!empty($params['toDate'])){
              $endPeriod=' and date<'.$params['toDate'];
            }

             if(!empty($params['status']) && $params['status']){
              $endPeriod=' and status='.$params['status'];
             }

               $fusers = xCore::loadCommonClass('fusers');
              $orders = XPDO::selectIN('*', 'ishop_orders', 'date>'.$params['fromDate'].$endPeriod, 'order by date desc');

               if(!empty($orders)){

                   $clients = XARRAY::arrToKeyArr($orders, 'id', 'client_id');
                   $clientsGuest = XARRAY::arrToKeyArr($orders, 'id', 'client_guest_id');


                    if ($clients) {
                        if ($clientsInfo = $this->_commonObj->getRegisteredClientsRange($clients)) {

                            foreach ($clientsInfo as $k => $client) {


                                $client['extData']=$fusers->_tree->selectStruct('*')->selectParams('*')->childs($client['id'])->run();
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

           

                    if ($statuses = $this->_commonObj->getStatusesList(true)) {
                        $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
                    }

                   foreach($orders as $setItem){

                        $setItem['status'] = $statuses[$setItem['status']];
                        $setItem['total_sum'] = number_format($setItem['total_sum'], 2, '.', '');
                        $setItem['dateFormatted'] = date('d-m-Y H:i:s',$setItem['date']);


                    $setItem['address'] = $setItem['city'] . ' ' . $setItem['street'] . ' ' . $setItem['house'] . ' ' . $setItem['room'];
                    unset($setItem['city'], $setItem['street'], $setItem['house'], $setItem['room']);


                    if (isset($setItem['client_id']))
                    {

                        $client = $clientsRegistered[$setItem['client_id']];
                        unset($client['params']['password']);
                        $setItem['clientData'] = $client;
                        unset($setItem['client_guest_id']);

                    } else {

                        $client = $guestClients[$setItem['client_guest_id']];
                        $setItem['clientData'] = $client;
                        unset($setItem['client_id']);

                    }

                    $data[] = array('order'=>$setItem,'goods'=>$this->getOrderGoods($setItem['id']));

                   }


                 return $data;
               }

    }

     private function getOrderGoods($orderId)
    {
        return XPDO::selectIN('*', 'ishop_orders_goods', 'order_id='.$orderId);
    }


     private function getGuestClientsRange($range)
    {
        return XPDO::selectIN('*', 'ishop_orders_clients_guest', $range);
    }




}


