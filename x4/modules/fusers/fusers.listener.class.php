<?php
class fusersListener extends xListener implements xModuleListener{
  

          
    public function __construct()
    {        
        parent::__construct('fusers');                
        $this->_EVM->on('boot','listen',$this);
        
    }

        
    public function listen($params)
    {            
        if(isset($_REQUEST['redirectAfterAuth']))
        {
            $_SESSION['redirectAfterAuth']=base64_decode($_REQUEST['redirectAfterAuth']);    
        }
        
    }
    
    
    
}
