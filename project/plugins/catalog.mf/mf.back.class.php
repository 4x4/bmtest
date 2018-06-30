<?php
class mfBack  extends xPluginBack
{
    public function __construct($name,$module)
    {     
        parent::__construct($name,$module);
        $this->_listener->defineFrontActions($this->_module->_commonObj);              
        //X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterBeforeClear', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClear', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClearSlash', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClearCategory', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClearXLS', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterSymbols', $this);
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterAvail', $this);
        
        X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClearSpecial', $this);
              X4\Classes\XNameSpaceHolder::addMethodsToNS('module.' . $this->_moduleName . '.back', '_importFilterAfterClearAnd', $this);
        
    }  
    
    
    public function _importFilterAfterAvail($params,$value,$context)
    {
        
        $params['oldRow']['sku.stock1']=intval($params['oldRow']['sku.stock1']);
        $params['row']['sku.stock2']=intval($params['row']['sku.stock2']);
        
        if($params['oldRow']['sku.stock1']>0 or $params['row']['sku.stock2']>0)
        {
            return 'В наличии';    
        }else
        {
            return false;    
            
        }
             
    }
    
    public function filterIt($text)
    {
        return str_replace(array("®","™"," ", ",", ", ","&reg;","&amp;reg;","'","&trade;","&amp;trade;",".","+","&",'№','\\','+'), array('','',"-", "", "","","","","","","-","-","","","","-"),$text);
    }
    
   public function _importFilterAfterSymbols($params,$value,$context)
   { 
        return $this->filterIt($params['row'][$params['rowName']]);          
   } 
   
  
    
     public function _importFilterAfterClearAnd($params,$value,$context)
   { 

        
                $basic=str_replace(array('&','+'),array('@and','@plus'),$params['extRow']['tovarbase.series']);   
                return $basic;                
          
   }  
  
     public function _importFilterAfterClearCategory($params,$value,$context)
   { 

                 
                $basic=strtolower(XCODE::translit($params['oldRow']['Name']));
                $basic=str_replace(array(' ',',','/','.','+',')','('),array('-','','-','-','-','',''),$basic);   
                return  '/media/icons/'.$basic.'.png';                           
          
   }  
    
   
   
      public function _importFilterAfterClearSlash($params,$value,$context)
   { 
                
                $parentObject=$params['context']->importedObjects[$params['parent']];  
                
                $params['oldRow']['tovarbase.model']=str_replace(array('/','+',')','('),array('-','-','',''),$params['oldRow']['tovarbase.model']);  
                
                
           
                if($params['psetData']['params']['type']=='fileFolder')
               {
                  
                  $model='';
                  
                  }elseif($params['psetData']['params']['type']=='file')
                  {
                     
                     $ext='.xlsx';
                     $model=$params['oldRow']['tovarbase.model'];
                 
                 }else{
                     
                     $ext='.jpg';
                     $model=$params['oldRow']['tovarbase.model'];
                 }
                               
            
                $prepared=$this->filterIt($params['oldRow']['old.tovarbase.brand'].'/'.$params['oldRow']['tovarbase.model'].'/'.$model).$ext;                                
                return  '/media/cat/'.$prepared;                           
          
   }  
   
   
   
   public function _importFilterAfterClear($params,$value,$context)
   { 
                
                $parentObject=$params['context']->importedObjects[$params['parent']];  
               
                $parentObject['oldRow']['tovarbase.model']=str_replace(array(' ',',','/','.',')','('),array('-','','-','-','',''),$parentObject['oldRow']['tovarbase.model']);  
                $params['row']['sku.model']=str_replace(array(' ',',','/','.','(',')'),array('-','','-','-','',''),$params['row']['sku.model']);  
				
                $prepared=$this->filterIt($parentObject['oldRow']['old.tovarbase.brand'].'/'.$parentObject['oldRow']['tovarbase.model'].'/'.$params['row']['sku.model']).'.jpg';                                
                return  '/media/cat/'.$prepared;                           
          
   }  
   

   
   public function _importFilterAfterClearXLS($params,$value,$context)
   {
                
                 
                $info = new SplFileInfo($params['extRow'][$params['rowName']]);
       
               if($params['psetData']['params']['type']!='fileFolder')
               {
                        if($ext=$info->getExtension())
                        {
                            $ext='.'.$info->getExtension();
                            $rev=str_replace($ext,'',$params['extRow'][$params['rowName']]);    
                        } else{
                            $rev=$params['extRow'][$params['rowName']];
                            $ext='';
                        }        
                 
                 }else{
                     
                     $rev=$params['extRow'][$params['rowName']];
                 }
               
                
                
                $prepared=$this->filterIt($rev);                                
                return  $prepared.$ext;                                    
        
   }
   
    public function _importFilterAfterClearSpecial($params,$value,$context)
   { 
         
                $prepared=$this->filterIt($params['row']['specials.model']).'.jpg';                                
                return  '/media/specials/'.$prepared;                           
          
   }  

}

?>