<?php

  use X4\Classes\XNameSpaceHolder;

class mfFront extends xPlugin
{
    
    private $listenerInstance;
    
    public function __construct($listenerInstance)
    {      
        $this->listenerInstance=$listenerInstance;                 
        parent::__construct(__FILE__); 
        
        XNameSpaceHolder::addMethodsToNS('module.catalog.front', array('testme'), $this);
        
        
    }
    
    
      public function testme($params)
    {
                 
         $objectInfo=$this->_module->_tree->selectStruct('*')->selectParams('*')->where(array
            (
            '@id',
            '=',
            104362
            ))->jsonDecode()->run();

              
         
        $objectInfo =$this->_module->_commonObj->convertToPSG($objectInfo, array
            (
            'serverPageDestination' => $params['params']['destinationLink'],
            'getSku'                => true
            ));
            
         
         $this->_module->setSeoData($objectInfo);
        
              
        $this->_module->loadModuleTemplate('show_single.showObject.html');
        
        $currentPage=X4\Classes\XRegistry::get('TPA')->getCurrentPage();
     
        $categorLink=$this->_module->_commonObj->buildLink($objectInfo['_main']['ancestor'],$currentPage['id']);
        
        $this->_module->_TMS->addMassReplace('catalogObject',array('categoryLink'=>$categorLink));
  
        $this->_module->_TMS->addReplace('catalogObject', 'object', $objectInfo);
     
     
        return $this->_module->_TMS->parseSection('catalogObject');
        
    }
    
    
    
    
}

?>