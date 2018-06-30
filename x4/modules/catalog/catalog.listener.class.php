<?php

class catalogListener extends xListener implements xModuleListener
{


    public function __construct()
    {
        parent::__construct('catalog');     
        $this->setDefaultUserPrice();
        $this->_EVM->on('zero-boot', 'urlReverseTransform', $this);
        $this->_EVM->on('AdminPanel:afterInit', 'serializeAllPropertyGroup', $this);
        $this->_EVM->on('AdminPanel:afterCacheClear', 'serializeAllPropertyGroup', $this);
     //   $this->_EVM->on('AdminPanel:afterCacheClear', 'refreshUrlTransform', $this);
       // $this->_EVM->on('ishop:afterCurrencyChange', 'rebuildIshopPrices', $this);
        //$this->_EVM->on('ishop:afterCurrentCoursesChange', 'rebuildIshopPrices', $this);
        //$this->_EVM->on('module.catalog.back:afterImport', 'rebuildIshopPrices', $this);
        $this->_EVM->on('AdminPanel:afterCacheClear', 'boostTreeListener', $this);        
        

    }

    public function refreshUrlTransform($params)
    {

        $this->truncateTransformList();
        $this->rebuildUrlTransformMatrix();
    }


	public function urlReverseTransform($params)
    {
	    
			if(!$_REQUEST['xoadCall'])
			{
					 $transformed=$this->buildUrlTransformation($_SERVER['REQUEST_URI']);
					
					
					if($_SERVER['REQUEST_URI']!==$transformed)
					{
							header("HTTP/1.1 301 Moved Permanently");
							header("Location: $transformed");
							exit(); 
					}
			}
		
             $_SERVER['REQUEST_URI']=$request=$this->buildUrlReverseTransformation($_SERVER['REQUEST_URI']);
            
			
			if(!strstr($_SERVER['REQUEST_URI'],'~search'))
            {
                $parsedUrl=parse_url($request);             
                if(!empty($parsedUrl['query']))
                {
                    
                    parse_str($parsedUrl['query'], $parsedRequest);
                    $_GET=array_replace_recursive($_GET,$parsedRequest);
                    $_REQUEST=array_replace_recursive($_REQUEST,$parsedRequest);
                }
            }
             
    }

    public function rebuildIshopPrices($params)
    {
        $this->rebuildIcurrencyFields($params);
    }


    public function boostTreeListener($params)
    {
        $this->boostTree($params);

    }

    public function serializeAllPropertyGroup($params)
    {

        if (!$_REQUEST['xoadCall'] or ($params['event'] == 'AdminPanel:afterCacheClear')) {
            $this->createPropertyGroupSerializedAll();
        }
    }


}