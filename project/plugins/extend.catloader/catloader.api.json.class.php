<?php
use X4\Classes\XRegistry;

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 catloader module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/extend.catloader",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *
 * @SWG\Definition(
 *     definition="syncArray",
 *     type="array",     
 *     allOf={
 *       @SWG\Schema(  
 *            @SWG\Property(property="own", type="string"),
 *           @SWG\Property(property="price", type="number"), 
 *           @SWG\Property(property="priceRetail", type="number"), 
 *           @SWG\Property(property="stock", type="number"),
 *           @SWG\Property(property="stockNext", type="string"),
 *           @SWG\Property(property="stockTimeNext", type="string"),
 *           @SWG\Property(property="ean", type="string"),
 *           @SWG\Property(property="num", type="string"), 
 *           @SWG\Property(property="vendor", type="string"),  
 *           @SWG\Property(property="timeget", type="number")  
 *       )
 *    }
 *    ),

 *
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 *)
 */
 
class catloaderApiJson extends catloaderFront
{
    
    public $listenerInstance;
    public $skuFolder=3593580;
    public function __construct($listenerInstance)
    {      
        $this->listenerInstance=$listenerInstance;                 
        parent::__construct(__FILE__); 		
    }
    

    /**
     * @SWG\Post(
     *     path="/sync",
     *     summary="syncs stock",
     *     operationId="sync",
     *     produces={"application/json"},
     * 
     * 
     *     @SWG\Parameter(
     *         name="syncArray",
     *         in="body",
     *         description="syncArray",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/syncArray"),
     *     ),
     *     @SWG\Response(response=200, description="sync stock data ok"),
     * )
     */

    private function  getSKURootData()
    {



    }
    
    public function sync($params,$data)
    {
        $catalog=xCore::moduleFactory('catalog.front');

        $eans=XARRAY::arrToKeyArr($data,'id','ean');

        $eans=XARRAY::clearEmptyItems($eans);
        $products=array();

        if($eans) {

            $products = $catalog->_tree->selectAll()->where(array('tovarbase.ean', '=', $eans))->format('paramsval', 'tovarbase.ean', 'id')->run();
        }




        $vnums=XARRAY::arrToKeyArr($data,'id','vnum');
        $vnums=XARRAY::clearEmptyItems($vnums);



		
        if($vnums) {
            $productsAdd = $catalog->_tree->selectAll()->where(array('tovarbase.vnum', '=',$vnums))->format('paramsval', 'tovarbase.vnum', 'id')->run();
            $products=$productsAdd+$products;
        }



        $eansOut=array();

		if(!empty($products)) {

           $relativeSku = $catalog->_commonObj->findRelativeSku($products, true);

           $singlesSku=XARRAY::arrToLev($relativeSku,'id','params','Name');
           $singlesSku=array_flip($singlesSku);

            foreach ($data as $item) {

                $signature=md5($item['ean'].' '.$item['own']);
                $item['Name']=$signature;
                $item['price__currency']=1091;
                $item['price']=round($item['price']);
                if(!$item['ean']){
                    $vnum = md5($item['vendor'] . $item['num']);
                }else{
                    $vnum=$item['ean'];
                }




                if(!empty($products[$vnum])) {

                    if(empty($singlesSku[$item['Name']])) {
                        $synced = $catalog->_commonObj->_sku->initTreeObj($this->skuFolder, '%SAMEASID%', '_SKUOBJ', $item, $products[$vnum]);
                        $updated=0;
                    }else{

                         $catalog->_commonObj->_sku->reInitTreeObj($singlesSku[$item['Name']], '%SAME%', $item);
                         $synced=$singlesSku[$item['Name']];
                         $updated=1;
                    }

                    if ($synced) {
                        $eansOut[$item['own']][$synced] = array('ean' => $item['ean'], 'vnum' => $vnum,'updated'=>$updated);
                    }
                }
            }

            Common::writeLog($eansOut);

            return $eansOut;
		}


        return array('error'=>true);
    }
        
}
