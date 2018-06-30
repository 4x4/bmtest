<?php

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 catalog module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/catalog",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *                
 *   @SWG\Definition(

 *     definition="CatalogObjectsBulk",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"},
 *           @SWG\Property(property="ancestor", type="integer", example="1"),
 *           @SWG\Property(property="objects", ref="#/definitions/CatalogObject") 
 *       )
 *    }
 *    ),        
 *
 *   @SWG\Definition(
 *     definition="CatalogObject",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"},
 *           @SWG\Property(property="ancestor", type="integer", example="1"),
 *           @SWG\Property(property="basic", type="string", example="basic-test"),
 *           @SWG\Property(property="objType", type="string", example="_CATGROUP"),
 *           @SWG\Property(property="params", ref="#/definitions/CatalogObjectParams")
 *
 *       )
 *    }
 *    ),
 *
 * @SWG\Definition(
 *     definition="CatalogObjectParams",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 *
 *    @SWG\Definition(
 *     definition="changeAncestorParams",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="id", type="integer", example="890"),
 *           @SWG\Property(property="toAncestor", type="integer", example="900")
 *       )
 *    }
 *    ),
 *
 *    @SWG\Definition(
 *     definition="searchQuery",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="field", type="string", example="@id"),
 *           @SWG\Property(property="sign", type="string", example="="),
 *           @SWG\Property(property="value", type="string", example="1")
 *       )
 *    }
 *    ),
 * @SWG\Definition(
 *     definition="query",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="selectStruct", ref="#/definitions/selectStruct"),
 *           @SWG\Property(property="selectParams", ref="#/definitions/selectParams"),
 *           @SWG\Property(property="childs", ref="#/definitions/childs"),
 *           @SWG\Property(property="where", ref="#/definitions/where"),
 *           @SWG\Property(property="format", ref="#/definitions/format"),
 *       )
 *    }
 *    ),
 *     @SWG\Definition(
 *     definition="selectParams",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 * 
 * 
 * 

 *   @SWG\Definition(
 *     definition="transformUrl",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"url"},
 *           @SWG\Property(property="url", type="string",example="/store/smartphones?f[like][tovarbase.brand][]=60917&f[like][tovarbase.brand][]=60915")
 *       )
 *    }
 *    ),
 
 
 *      @SWG\Definition(
 *     definition="selectStruct",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 *
 *      @SWG\Definition(
 *     definition="childs",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="ancestor", type="integer", example="1"),
 *           @SWG\Property(property="level", type="integer"),
 *       )
 *    }
 *    ),
 *
 *      @SWG\Definition(
 *     definition="where",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 *      @SWG\Definition(
 *     definition="format",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *           required={"anyParam"},
 *           @SWG\Property(property="anyParam", type="string")
 *       )
 *    }
 *    ),
 * 
 *   @SWG\Definition(
 *     definition="addSet",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"},
 *           @SWG\Property(property="ancestor", type="integer", example="1"),
 *           @SWG\Property(property="basic", type="string", example="basic-test"), 
 *           @SWG\Property(property="params", ref="#/definitions/CatalogObjectParams")
 *
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
 *
 * )
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;


class catalogApiJson
    extends xModuleApi
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }




    /**
     * @SWG\Get(
     *     path="/getChilds/id/{id}",
     *     summary="Get catalog category childs",
     *     operationId="getChilds",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="Category id",
     *         required=true
     *     ),
     *     @SWG\Response(response=200, description="Child catalog items"),
     * )
     */

    public function getChilds($params)
    {
        if (empty($params['id'])) {
            return $this->error('Id is not provided', 400);
        }

        return $this->_tree->selectStruct('*')->selectParams('*')->childs($params['id'])->run();
    }




    /**
     * @SWG\Get(
     *     path="/getObject/id/{id}/getSku/{getSku}",
     *     summary="Get catalog object with SKU",
     *     operationId="getObject",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path", 
     *         description="Object id",
     *         required=true
     *     ),
     *
     *   @SWG\Parameter(
     *         name="getSku",
     *         in="path",
     *         example="false", 
     *         description="get sku"
     *     ),
     *     @SWG\Response(response=200, description="Gets object info"),
     * )
     */


    public function getObject($params)
    {
        if (isset($params['id'])) {

            $obj = $this->_tree->selectStruct('*')->selectParams('*')->where(array
            (
                '@id',
                '=',
                $params['id']

            ))->run();


            if ($params['getSku']) {
                $obj['sku'] = $this->_commonObj->_sku->selectParams('*')->selectStruct('*')->where(array
                (
                    '@netid', '=', $obj['id']
                ))->run();
            }

            return $obj;
        } else {

            return $this->error('Id is not provided', 400);

        }
    }


    /**
     * @SWG\Put(
     *     path="/setSkuParams/id/{id}",
     *     summary="Set SKU params",
     *     operationId="setSkuParams",
     *     produces={"application/json"},
     *
     *    @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="sku id",
     *         required=true
     *     ),
     *
     *     @SWG\Parameter(
     *         name="objectParams",
     *         in="body",
     *         description="object params",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/CatalogObjectParams")
     *     ),
     *     @SWG\Response(response=200, description="Params setted"),
     * )
     */


    public function setSkuParams($params, $data)
    {
        try {
            $this->_commonObj->_sku->writeNodeParams($params['id'], $data);
            return array('result' => true);

        } catch (Exception $e) {
            return $this->error('sku params writing failed', 400);
        }
    }


    /**
     * @SWG\Put(
     *     path="/setObjectParams/id/{id}",
     *     summary="Sets any catalog object Params",
     *     operationId="setObjectParams",
     *     produces={"application/json"},
     *
     *    @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="sku id",
     *         required=true
     *     ),
     *
     *     @SWG\Parameter(
     *         name="objectParams",
     *         in="body",
     *         description="object params",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/CatalogObjectParams"),
     *     ),
     *     @SWG\Response(response=200, description="Params setted"),
     * )
     */


    public function setObjectParams($params, $data)
    {
        try {
            $this->_tree->writeNodeParams($params['id'], $data);
            return array('result' => true);
        } catch (Exception $e) {
            return $this->error('Catalog object params writing failed', 400);
        }
    }


    /**
     * @SWG\Post(
     *     path="/search",
     *     summary="Search in catalog using params object",
     *     operationId="search",
     *     produces={"application/json"},
     *
     *
     *     @SWG\Parameter(
     *         name="query",
     *         in="body",
     *         description="search query as array",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/searchQuery"),
     *     ),
     *     @SWG\Response(response=200, description="query results"),
     * )
     */


    public function search($params, $data)
    {
        return $this->_tree->selectStruct('*')->selectParams('*')->where(array($data['field'],$data['sign'],$data['value']))->run();
    }


    /**
     * @SWG\Post(
     *     path="/createNewObject",
     *     summary="Creates new catalog object",
     *     operationId="createNewObject",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="CatalogObject",
     *         in="body",
     *         description="Catalog object",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/CatalogObject"),
     *     ),
     *     @SWG\Response(response=200, description="Object created"),
     * )
     */


  public function createNewObject($params, $data)
    {
        
        if (empty($data['ancestor'])) {
            return $this->error('Id is not provided', 400);
        }
        if( $data['objType']=='_SKUOBJ'){
			  
			  $psg=$this->_tree->readNodeParam($data['ancestor'],'PropertySetGroup');
			  $folder=$this->_commonObj->_propertySetsTreeGroup->readNodeParam($psg, 'skuLink');
			  $this->_commonObj->_sku->initTreeObj($folder, '%SAMEASID%', '_SKUOBJ', $data['params'], $data['ancestor']);
			
		}else{
			$objId = $this->_tree->initTreeObj($data['ancestor'], $data['basic'], $data['objType'], $data['params']);
		}
        
		
        if (!$objId) {
            throw new Exception('object-not-created');
        }
        
        return array('id'=>$objId);


    }


    /**
     * @SWG\Post(
     *     path="/treeQuery",
     *     summary="Search in catalog using params object",
     *     operationId="treeQuery",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="selectStruct",
     *         in="body",
     *         description="selectStruct params - you can use * to select all",
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/query")
     *     ),
     *     @SWG\Response(response=200, description="Object created")
     * )
     */


    public function query($params, $data)
    {
        $tree = $this->_tree->dropQuery();

        if (!empty($data['selectStruct'])) {
            $tree = $tree->selectStruct($data['selectStruct']);
        }

        if (!empty($data['selectParams'])) {
            $tree = $tree->selectParams($data['selectParams']);
        }

        if (!empty($data['childs'])) {
            $tree = $tree->childs($data['childs']['ancestor'],$data['childs']['level']);
        }

        if (!empty($data['where'])) {
            foreach ($data['where'] as $whereItem) {
                $tree = $tree->addWhere($whereItem);
            }
        }

        return $tree->run();

    }

    /**
     * @SWG\Delete(
     *     path="/deleteObject/id/{id}",
     *     summary="Deletes catalog object",
     *     operationId="deleteObject",
     *     produces={"application/json"},
     *
     *
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Delete catalog object",
     *         required=true
     *     ),
     *     @SWG\Response(response=200, description="Object deleted"),
     * )
     */


    public function deleteObject($params)
    {
        if (empty($params['id'])) {
            return $this->error('Id is not provided', 400);
        }

        $this->_tree->delete()->where(array('@id', '=', $params['id']))->run();
    }

    
      /**
     * @SWG\Get(
     *     path="/getPropetyGroups",
     *     summary="Get all propertyGroups",
     *     operationId="getPropetyGroups",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="propertyGroups items"),
     * )
     */
    
    
     public function getPropetyGroups($params)
    {
    
        $result=$this->_commonObj->_propertySetsTreeGroup->selectAll()->childs(1)->asTree()->run();
        $result->recursiveStep(1, $this, '_propertySetsTreeGroupSubTree', $extdata);
        return $this->innerTree;

    }
    
    public function _propertySetsTreeGroupSubTree($node, $ancestor, $tContext, $extdata)
    {
        
        if($node['obj_type']=='_PROPERTYSETGROUP')
        {            
            $node['PROPERTYSETLINKS']=array();
            $this->innerTree[$node['id']]=$node;
        }else{
            
            $this->innerTree[$node['ancestor']]['PROPERTYSETLINKS'][]=$node;
        }

    }
    
    
    /**
     * @SWG\Get(
     *     path="/getPropertySetsList",
     *     summary="Get property set list",
     *     operationId="getPropertySetsList",
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="getPropertySets items"),
     * )
     */
    


    public function  getPropertySetsList($params)
    {
             $items=$this->_commonObj->_propertySetsTree->selectAll()->childs(1,1)->run();
             return $items;
    }

    
    
    
    /**
     * @SWG\Get(
     *     path="/getPropertySet/id/{id}",
     *     summary="Gets properties by given property set id",
     *     operationId="getPropertySet",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="PropertySetId id",
     *         required=true
     *     ),
     *     produces={"application/json"},
     *     @SWG\Response(response=200, description="gets properties list"),
     * )
     */
    


    public function  getPropertySet($params)
    {
             $items=$this->_commonObj->_propertySetsTree->selectAll()->childs($params['id'],2)->run();
             return $items;
    }
    
     /**
     * @SWG\Post(
     *     path="/addProperty",
     *     summary="Add property by set id",
     *     operationId="addProperty",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="property",
     *         in="body",
     *         description="adds property to set",
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/addSet")
     *     ),
     *     @SWG\Response(response=200, description="Object created")
     * )
     */
    
    
    public function  addProperty($params,$data)
    {
        
        if (empty($data['ancestor'])) {
            return $this->error('Id is not provided', 400);
        }
        
        $objId = $this->_commonObj->_propertySetsTree->initTreeObj($data['ancestor'], $data['basic'], '_PROPERTY', $data['params']);
        
        
         if (!empty($data['options'])) 
                {
                    $this->_commonObj->_propertySetsTree->initTreeObj($objId, '%SAMEASID%', '_OPTIONS', $data['options']);
                }
                
        if (!$objId) {
            
            throw new Exception('object-not-created');
        }    
            
        return array('id'=>$objId);
   
    }
    
    
    
       /**
     * @SWG\Get(
     *     path="/deleteProperty",
     *     summary="deletes property by id",
     *     operationId="deleteProperty",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="property id",
     *         required=true
     *     ),
     *     @SWG\Response(response=200, description="Object created")
     * )
     */
    
    
    public function  deleteProperty($params)
    {                        
    
        if (empty($params['id'])) {
            return $this->error('Id is not provided', 400);
        }
        
        $this->_commonObj->_propertySetsTree->delete()->where(array('id','==',$params['id']))-run();     
   
    }
    
    

    /**
     * @SWG\Put(
     *     path="/changeAncestor",
     *     summary="changes ancestor for any node",
     *     operationId="changeAncestor",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="changeAncestorParams",
     *         in="body",
     *         description="move params",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/changeAncestorParams")
     *     ),
     *     @SWG\Response(response=200, description="Params setted"),
     * )
     */


    public function changeAncestor($params, $data)
    {
        try {
            $this->_commonObj->_tree->changeAncestor($data['id'],$data['ancestor']);
            return array('result' => true);

        } catch (Exception $e) {
            return $this->error('can\'t change ancestor', 400);
        }
    }
    
    
    
    
         /**
     * @SWG\Post(
     *     path="/transformToUrl",
     *     summary="Transforms url",
     *     operationId="transformToUrl",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="url",
     *         in="body",
     *         description="url",
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/transformUrl")
     *     ),
     *     @SWG\Response(response=200, description="Url created")
     * )
     */
     
      public function transformToUrl($params,$data)
    {
        if(!empty($data['url'])){
            $url=$this->_commonObj->buildUrlTransformation($data['url']);
            return array('url'=>$url);
        }else{
            
            return $this->error('transform url failed', 400);
        }
    }

    
    /**
     * @SWG\Post(
     *     path="/transformToUrlReverse",
     *     summary="Transforms url reverse",
     *     operationId="transformToUrlReverse",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="url",
     *         in="body",
     *         description="url",
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/transformUrl")
     *     ),
     *     @SWG\Response(response=200, description="Url created")
     * )
     */

    public function transformToUrlReverse($params)
    {
        if(!empty($data['url']))
        {
            $url=$this->_commonObj->buildUrlReverseTransformation($data['url']);
            return array('url'=>$url);
            
        }else{
            
            return $this->error('transform url failed', 400);
        }
    }
    
    
    
     /**
     * @SWG\Post(
     *     path="/createBulkObjects",
     *     summary="Create new  object via bulk",
     *     operationId="createBulkObjects",
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="CatalogObjectsBulk",
     *         in="body",
     *         description="Catalog objects bulk",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/CatalogObjectsBulk"),
     *     ),
     *     @SWG\Response(response=200, description="Object created"),
     * )
     */


    public function createBulkObjects($params, $data)
    {
        
        if (empty($data['ancestor'])) {
            return $this->error('Id is not provided', 400);
        }
        
        $objects=$this->_tree->selectStruct('*')->selectParams('*')->childs($data['ancestor'],1)->format('valval','basic','params')->run();
        
        $objId=array();
        
         if(!empty($data['objects'])){
            foreach($data['objects'] as $object)
            {
                $objId[] = $this->_tree->initTreeObj($object['ancestor'], $object['basic'], $object['objType'], $object['params']);        
                        
            }
         }
        
    
        return array('id'=>$objId);


    }


}
