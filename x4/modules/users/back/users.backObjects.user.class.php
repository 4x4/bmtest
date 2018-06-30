<?php

trait _USER

{
    public function onEdit_USER($params)
    {
       
        $data = $this->_tree->getNodeInfo($params['id'],true);
        $data['params']['userGroup'] = $this->_tree->readNodeParam($data['ancestor'], 'Name');
        $data['params']['login'] = $data['basic'];
        $mPermission=array();
        
        
        if(!empty($data['params']['permissions']))
        {
            foreach($data['params']['permissions']  as $key=>$permission)
            {
                if(strstr($key,'__'))
                {
                    
                    $mPermission[]=XARRAY::convertArrayToDots($key,$permission);    
                    unset($data['params']['permissions'][$key]);
                }    
                
            }    
        }
        
        if(!empty($mPermission))
        { 
            foreach($mPermission as $permission)
            {
                $data['params']['permissions']=$data['params']['permissions']+ $permission;    
            }
        }
        
        $modules=xCore::discoverModules();
                
        $this->result['modules']=$this->discoverAccess($modules);                
        $this->result['data'] = $data['params'];
    }
    
    
    
    public function discoverAccess($modules)
    {
        
        $access=array();
        
        foreach($modules as  $moduleKey=>&$moduleItem)
        {
            $instance=xCore::loadCommonClass($moduleKey);
            
            if(method_exists($instance,'getACL'))
            {
                $acl=$instance->getACL();  
                
                foreach($acl as $aclItem)
                {
                    $moduleItem['acl'][$aclItem]=$instance->translateWord($aclItem);    
                }
                
            }else{
                
                $moduleItem['acl']=array();
            }
        }
        
        return $modules;
        
        
    }

    

    public function onSaveEdited_USER($params)
    {
                
        
        if ($params['data']) {

            $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data']);
            return new okResult('user-edited-saved');
        } else {
            return new badResult('user-not-saved');
        }

    }


    public function onSave_USER($params)
    {
                            
        $this->_tree->setUniqType(2);
        $existed = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->where(array('@basic', '=', $params['data']['login']))->run();
        $existedEmail = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->where(array('email', '=', $params['data']['email']))->run();

  
        if (!$existed && !$existedEmail) {
            
            $params['data']['password'] = md5(strrev($params['data']['password']));
            
            if ($id = $this->_tree->initTreeObj($params['data']['userGroup'], $params['data']['login'], '_USER', $params['data'])) {
                return new okResult('user-saved');
            }
        } else {
            return new badResult('user-already-exists');

        }

    }


    public function onCreate_USER($params)
    {

        if ($groups = $this->getGroups()) {
            $this->result['data']['userGroup'] = XHTML::arrayToXoadSelectOptions($groups, null, true);
        }

    }

    public function changeUserPassword($params)
    {

        $password = md5(strrev($params['data']['newPassword']));

        if ($id = $this->_tree->writeNodeParam($params['data']['id'], 'password', $password)) ;
        {
            return new okResult('new-password-saved');
        }

    }

    public function usersTable($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        
        

        $opt = array(
            'showNodesWithObjType' => array(
                '_USER'
            ),
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                'basic' => array(),
                '>name' => array(),
                '>surname' => array(),
                '>email' => array(),
                '>active' => array()
            )
        );

        if (!$params['page']) $params['page'] = 1;
        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);


    }


}

?>