<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class usersCommon
    extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {

        parent::__construct(__CLASS__);

        $this->_tree->setObject('_ROOT', array('LastModified'));

        $this->_tree->setObject('_USERSGROUP', array
        (        
            'Name'
                        
        ), array('_ROOT'));

        $this->_tree->setObject('_SUPERADMIN', array
        (         
            'password',
            'name',
            'email',
            'surname',
            'patronymic',
            'phone',
            'avatar',
            'active'
        ), array('_ROOT'));

        $this->_tree->setObject('_USER', array
        (            
            'password',
            'name',
            'surname',
            'patronymic',
            'phone',
            'avatar',
            'active',
            'email',
            'permissions'

        ), array('_USERSGROUP'));


        $this->rolesTree = new X4\Classes\XTreeEngine('usersRoles_container', XRegistry::get('XPDO'));

        $this->rolesTree->setObject('_ROOT', array('LastModified'));

        $this->rolesTree->setObject('_ROLE', array('Name'), '_ROOT');

        $this->rolesTree->setObject('_MODULE', array('is_accesible'), '_ROLE');

        $this->rolesTree->setObject('_PERMISSION', array('module', 'obj_id', 'read', 'write', 'delete', 'deep'), '_ROLE');

        $this->rv = array('update' => 1, 'delete' => 2, 'add' => 4, 'read' => 8);

    }


    public function loadRoles($node)
    {
        
         return $node['params']['permissions'];

    }


    public function defineFrontActions(){}

    public function checkAndLoadUser($login, $password)
    {
        
        $login = $this->_tree->selectParams('*')->selectStruct('*')->where(array('active','=',1),array('@basic', '=', $login), array('@obj_type', '=', array('_USER', '_SUPERADMIN')))->jsonDecode()->run();
        
        if ($node = $login[0]) {
            if ($node['params']['password'] == md5(strrev($password))) {

                $_SESSION['user'] = array('id' => $node['id'], 'login'=>$node['basic'], 'type' => $node['obj_type'], 'email' => $node['params']['email']);
                $_SESSION['authcode']=md5($node['params']['email'].$node['obj_type']);

                if ($node['obj_type'] != '_SUPERADMIN') {                                        
                        $_SESSION['user']['moduleAccess']=$this->loadRoles($node);
                }
                return true;

            }

        } else {
            return false;
        }

    }
}


