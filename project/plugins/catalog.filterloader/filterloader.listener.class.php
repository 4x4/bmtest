<?php
use X4\Classes\XNameSpaceHolder;

class filterloaderListener extends xListener  implements xPluginListener
{
    
    public function __construct()
    {
 
        parent::__construct('catalog.filterloader');
        
        $this->useModuleTplNamespace();
        $this->useModuleXfrontNamespace();
                
                
    }  
    
}          