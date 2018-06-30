<?php
use X4\Classes\XRegistry;

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 BMW module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/catalog.mf",
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
 
class mfApiJson extends mfFront
{
    
    public $listenerInstance;
    public $POIFolder=120639;
    public function __construct($listenerInstance)
    {      
        $this->listenerInstance=$listenerInstance;                 
        parent::__construct(__FILE__); 		
    }
    
	
	 
    /**
     * @SWG\Get(
     *     path="/searchPOI/param/{param}/value/{name}",
     *     summary="searches for POI",
     *     operationId="searchPOI",
     *     produces={"application/json"},
     * 
     * 
     *     @SWG\Parameter(
     *         name="name",
     *         in="path",
     *         description="",
     *         required=true     
     *     ),
     *     @SWG\Response(response=200, description="gets POI ARRAY"),
     * )
     */

     
     
    public function searchPOI($params,$data)
    {
		 if($params['value']&&$params['param']){
			$result=$this->_module->_tree->selectAll()->childs($this->POIFolder)->where(array($params['param'],'like','%'.$params['value'].'%'))->run();
		 }
         
		 if(!empty($result))
         {
             return $result;
			 
         }else{
		     return array('error'=>'POI not found');
		 }
    }
        
}
