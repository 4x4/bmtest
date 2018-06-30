<?php

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 pages module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/pages",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *
 *    @SWG\Definition(
 *     definition="Url",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"},
 *           @SWG\Property(description="Page url to recieve in headless format in json format",property="url", type="string", example="/")
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
use X4\Classes\MultiSectionHL;
use X4\Classes\XCache;


class pagesApiJson
    extends xModuleApi
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    /**
     * @SWG\Post(
     *     path="/headlessRoute",
     *     summary="Get headless page version",
     *     operationId="headlessRoute",
     *     produces={"application/json"},
     *     tags={"headless"},
     * 
     *     @SWG\Parameter(
     *         name="url",
     *         in="body",
     *         description="Page url to recieve in headless format in json format",
     *         @SWG\Schema(ref="#/definitions/Url")
     *     ),     
     * 
     *     @SWG\Response(response=200, description="Normal json response")
     * )
     */

    public function headlessRoute($params, $data)
    {    
      
        $generationTimeStart = Common::getmicrotime();

        $_SERVER['REQUEST_URI']=$data['url'];

        XRegistry::get('EVM')->fire('zero-boot');

        $data['url']=$_SERVER['REQUEST_URI'];
        xConfig::set('GLOBAL', 'currentMode', 'front');
        XRegistry::get('EVM')->fire('boot');
        XRegistry::set('TPA', $TPA = new pageAgregator(0));
        $TPA->setRenderMode('HEADLESS');


        if (empty($data['url'])) {
            $url = '/';

        } else {
            $url = $data['url'];
        }

        $w = parse_url($url);

        if ($w['query']) {
            parse_str($w['query'], $_GET);
            $_REQUEST = $_GET;
        }

        
        if($data['segment']){            
            $_SESSION['siteuser']['segmentation']=$data['segment'];            
        }

        $TPA->preventMainTemplateProcessing=true;
        $TPA->executePage($url);

		$generationTimeEnd = Common::getmicrotime();   
        
        $document=array('document' => array('modules' => $TPA->headlessSlotz,
                                            'headlessCacheEnabled'=>$TPA->headlessCache,                                         
                                            'generationTime'=>($generationTimeEnd-$generationTimeStart)));
        
        XRegistry::get('EVM')->fire('beforeHeadlessDocumentOutput',array('document'=>$document,'data'=>$data));
        
        return $document;                                         
        


    }


}

