<?php
  use X4\Classes\XNameSpaceHolder;
  use X4\Classes\xRegistry;
class mfListener extends xListener  implements xPluginListener
{
    public function __construct()
    {
 
        parent::__construct('catalog.mf');        
        $this->_EVM->on('catalog.front:afterInit','afterModuleInit',$this);          
		$this->_EVM->on('catalog.setSeoData','setSeo',$this);          
        $this->_EVM->on('fusers.userLogin','switchCurrency',$this);           
        $this->_EVM->on('fusers.userLogout','switchCurrency',$this);


        $this->_EVM->on('catalog.onModuleCacheWrite','catalogCacheWrite',$this);
        $this->_EVM->on('catalog.onModuleCacheRead','catalogCacheRead',$this);


//        $this->_EVM->on('agregator:onSetSeoData','setSeoPlusData',$this);
        $this->useModuleTplNamespace();
        $this->useModuleXfrontNamespace();
    }


    public function catalogCacheWrite($params)
    {
        if($params['data']['actionData']['params']['dispatchedAction']=='showCategory' or $params['data']['actionData']['params']['dispatchedAction']=='showObject')
        {
            $params['data']['cache']['cacheData']['TPA']=xRegistry::get('TPA')->getGlobalField(array('key'=>array('useOneColumn','categoryName','categoryAncestor','categoryId')));

            return $params['data']['cache'];
        }

    }

    public function catalogCacheRead($params)
    {

        if($params['data']['actionData']['params']['dispatchedAction']=='showCategory' or $params['data']['actionData']['params']['dispatchedAction']=='showObject')
        {

            if(!empty($params['data']['cache']['cacheData']['TPA']))
            {
                foreach($params['data']['cache']['cacheData']['TPA'] as $key=>$value)
                {

                    xRegistry::get('TPA')->setGlobalField(array("$key"=>$value));
                }
            }
        }


    }



    public function switchCurrency($params)
    {
        $ishop=xCore::moduleFactory('ishop.front');
        
        $_SESSION['cacheable']['currency']=$ishop->_commonObj->getMainCurrency();
        
    }  
    
 
    
    function ceiling($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }
  
  

    
  
    
    public function filterIt($text)
    {
        return str_replace(array("®","™"," ", ",", ", ","&reg;","&amp;reg;","'","&trade;","&amp;trade;",".","+","&",'№',"/","+"), array('','',"-", "", "","","","","","","-","-","","","-","-"),$text);
    }
    
    public function onBasicImport($params)
    {

        $basic=$params['data']['params']['basic'];
        
        if(empty($basic))
        {
          $basic=$params['data']['basic'];
        }
        
        return array('basic'=>$this->filterIt($basic));
        
    }
    
    public function setSeo($params)
	{
			
			
			 $object=$params['data']['object'];
			
             $pages=xCore::moduleFactory('pages.front');
			
             if($pages->page['basic']=='index')
			 {			 
				$object['seo']['Title']='';
				return $object;			 
			 }
			
            
            $z=(strrpos($object['_main']['link'],$pages->page['basic'])===false);

            
            if(!$object['seo']['Title'])
			{
                
					if($object['_main']['objType']=='_CATOBJ') 
					{
							
							$object['seo']['Title']=$object['_main']['Name'];	
							
					} elseif(($object['_main']['objType'] == '_CATGROUP')&&!$z) 
					{
				               
							$object['seo']['Title']=$object['_main']['Name'];
						
					}
			}
			
			return $object;
		
	}
	
     public function afterModuleInit($moduleInstance)
      {                             
        $this->defineFrontActions($moduleInstance['data']['instance']->_commonObj);   
      }
      
      
                                
      public function defineFrontActions(xCommonInterface $moduleInstance)
        {                            
            $moduleInstance->addServerAction('showCatalogServer','testme');
        }
        
        
   
        
    
}

