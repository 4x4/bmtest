<?php

use X4\Classes\XNameSpaceHolder;

class catloaderFront extends xPlugin
{
    
    public  $listenerInstance;
    
    public function __construct($listenerInstance)
    {      
        $this->listenerInstance=$listenerInstance;                 
        parent::__construct(__FILE__); 
    }
    
    
}
