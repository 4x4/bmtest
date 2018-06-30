<?php

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 news module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/news",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *

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


class newsApiJson
    extends xModuleApi
{
    public function __construct()
    {
     
        parent::__construct(__CLASS__);
    }




    /**
     * @SWG\Get(
     *     path="/getLatestNews/categoryId/{categoryId}/limit/{limit}",
     *     summary="Get latest news",
     *     operationId="getLatestNews",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="categoryId",
     *         in="path",
     *         type="integer",
     *         description="Category id",
     *         example="1001",  
     *         required=true
     * 
     *     ),
     *      @SWG\Parameter(
     *         name="limit",
     *         in="path",
     *         type="integer",
     *         description="limit number",
     *         example="5",
     *         required=true
     *     ),
     *     @SWG\Response(response=200, description="News items"),
     * )
     */

    public function getLatestNews($params)
    {
        
        if (empty($params['categoryId'])) {
            return $this->error('categoryId not provided', 400);
        }

        
        return $this->_commonObj->selectNewsInterval(array($params['categoryId']), $startRow = 0, $params['limit'], $where = '', $order = 'DESC');
    }


}
