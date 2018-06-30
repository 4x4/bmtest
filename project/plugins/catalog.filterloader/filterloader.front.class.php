<?php
class filterloaderFront extends xPlugin
{
    
    public $listenerInstance;
    
    public function __construct($listenerInstance)
    {      
        $this->listenerInstance=$listenerInstance;                 
        parent::__construct(__FILE__);            
        
    }

   
    
}
