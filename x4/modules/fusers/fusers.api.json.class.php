<?php

/**
 *
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="X4 Fusers module API",
 *     version="1.0.0"
 *   ),
 *     schemes={"http","https"},
 *     basePath="/~api/json/fusers",
 *     consumes={"application/json"},
 *     produces={"application/json"},
 *
 *    @SWG\Definition(
 *     definition="loginData",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"login","password"},
 *           @SWG\Property(property="login", type="string"),
 *           @SWG\Property(property="password", type="string")
 *       )
 *    }
 *    ),
 * 
 *   @SWG\Definition(
 *     definition="saveUserObject",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"}, 
 *           @SWG\Property(property="login", type="string", example="sample@login.com"),* 
 *           @SWG\Property(property="password", type="string", example="current-password"), 
 *           @SWG\Property(property="newPassword", type="string", example=""),
 *           @SWG\Property(property="email", type="string", example="sample@email.com"),
 *           @SWG\Property(property="params", ref="#/definitions/userParams")
 *
 *       )
 *    }
 *    ), 
 * 
 *   @SWG\Definition(
 *     definition="userObject",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"ancestor"},
 *           @SWG\Property(property="login", type="string", example="sample@email.com"),
 *           @SWG\Property(property="password", type="string", example="mypassword"),
 *           @SWG\Property(property="userGroupId", type="string", example=""),
 *           @SWG\Property(property="email", type="string", example="sample@email.com"),
 *           @SWG\Property(property="params", ref="#/definitions/userParams")
 *
 *       )
 *    }
 *    ), 
 * 
 *  @SWG\Definition(
 *     definition="userParams",
 *     type="array",
 *     allOf={
 *       @SWG\Schema(
 *            @SWG\Property(property="name", type="string", example="ivan"),
 *            @SWG\Property(property="surname", type="string", example="ivanov"),
 *            @SWG\Property(property="patronymic", type="string", example="ivanovich"),
 *            @SWG\Property(property="phone", type="string", example="phone"),
 *            @SWG\Property(property="address", type="string", example="address"),
 *            @SWG\Property(property="avatar", type="string", example="avatar"),
 *            @SWG\Property(property="active", type="boolean", example="true"),
 *            @SWG\Property(property="site", type="string", example="site")
 *       )
 *    }
 *    ),
 * 
 *    @SWG\Definition(
 *     definition="password",
 *     type="object",
 *     allOf={
 *       @SWG\Schema(
 *           required={"password"},
 *           @SWG\Property(property="password", type="string")
 *       )
 *    }
 *    ),
 * 
 *     @SWG\Definition(
 *         definition="Error",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     )
 *
 * )
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;




class fusersApiJson
    extends xModuleApi
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    
	private function getFacebookUser($token)
	{
			$fb = new \Facebook\Facebook([
							'app_id' => '446836772377719',
							'app_secret' => '0de32f9ce786c478248b3dc327075d69',
							'default_graph_version' => 'v2.10'
						]);

					try {
						$response = $fb->get('/me', $token);
					} catch(\Facebook\Exceptions\FacebookResponseException $e) 
					{
					  // When Graph returns an error
					   return  $this->error($e->getMessage(), 400);			  
					   
					  
					} catch(\Facebook\Exceptions\FacebookSDKException $e) {			  
					   return $this->error($e->getMessage(), 400);			  					  
					}

					return  $response->getGraphUser();
	}
     
    /**
     * @SWG\Post(
     *     path="/loginViaFacebook",
     *     summary="Front user Login facebook",
     *     operationId="loginViaFacebook",
     *     produces={"application/json"},
     *    @SWG\Parameter(
     *         name="userObject",
     *         in="body",
     *         description="user object",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/userObject")),
     *    
     *     @SWG\Response(response=200, description="Auth data")
     * )
     */
     
      public function loginViaFacebook($params,$data)
     {       
		 
         if(!empty($data['login']))
         {			
			
			 $user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@basic', '=', $data['login']))->singleResult()->run();   			  
             
			 $me=$this->getFacebookUser($data['access-token']);
			
             if (!empty($user)) 
             {    					
					$user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@basic', '=', $me['id']))->singleResult()->run();

					if (!$user) {
						return $this->error('User does not exists', 500);
					}
					
					
					 if ($user['params']['active']) 
					 {                  											
						$this->_tree->writeNodeParam($user['id'],'authToken',$data['access-token']);         
						$r=array('authorized'=>true,'additionalFields'=>$additional,'user'=>$user,'userId'=>$user['id'],'authToken'=>$data['access-token']);
						
						return $r;
				
					 }
			}else{						
					$dataCreate['email']=$dataCreate['login']=$me['id'];						
                    $dataCreate['password']=uniqid();
					$dataCreate['params']['active']=1;
					$dataCreate['params']['name']=$me['name'];

                    return $this->createUser($params,$dataCreate);
                }
               
         }else{
            
            return $this->error('login not provided', 400);
           
        }   
     }
     

    /**
     * @SWG\Post(
     *     path="/login",
     *     summary="Front user Login",
     *     operationId="Login",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="body",
     *         description="User login",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/loginData")
     *     ),
     *    
     *     @SWG\Response(response=200, description="Auth data")
     * )
     */
     
     
     public function login($params,$data)
     {                      
	
          if (!$user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@basic', '=', $data['login']))->singleResult()->run()) {
            $user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('email', '=', $data['login']))->singleResult()->run();
        }
		
	
		 
        if (!$user) {
            return $this->error('User does not exists', 500);
        }
 	 
		 
        $password = md5(strrev($data['password']));
        
        if (($user['params']['password'] == $password) && ($user['params']['active'])) {
          
        /*    $additional = $this->_tree->selectParams('*')->childs($user['id'],1)->run();
                if(!empty($additional[0])) {
                    $additional= $additional[0]['params'];
                }
	*/
         $token=uniqid();
                  
         $this->_tree->writeNodeParam($user['id'],'authToken',$token);
     
         return array('authorized'=>true,'additionalFields'=>$additional,'user'=>$user,'authToken'=>$token);
               
        }else{
            
            return $this->error('Wrong credentials', 400);
            
        }
  
     }
     
     
      /**
     * @SWG\Post(
     *     path="/createUser",
     *     summary="Creates user account",
     *     operationId="createUser",
     *     produces={"application/json"},
     *    @SWG\Parameter(
     *         name="userObject",
     *         in="body",
     *         description="user object",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/userObject"),
     *     ),     
     *     @SWG\Response(response=200, description="Registration result")
     * )
     */
     
     
     public function createUser($params,$data)
     {          
			
 		
         if(empty($data['userGroupId']))
         {             
               $userGroupId = $this->_commonObj->_tree->readNodeParam(1, 'defaultRegisteredGroup');  
           }else{
               $userGroupId= $data['userGroupId'];
           }
		  
		   
             
           $checkedData = $this->_commonObj->checkUserLoginAndEmail($data['login'], $data['email']);
           
            if ((!$checkedData['isLogin']) && (!$checkedData['isEmail'])) {

                   $data['params']['password'] = md5(strrev($data['password']));
                   $data['params']['email'] =$data['email'];                         
                   $data['params']['active']=(bool)$data['params']['active'];
                   
                    if ($uid = $this->_tree->initTreeObj($userGroupId, $data['login'], '_FUSER', $data['params'])) {
						
						
						
                        if (!empty($data['additionalFields']))
                        {
                            $this->_commonObj->initAdditionalFields($uid, $data['additionalFields']);
                        }
                    }
                    
                    $token=uniqid();
                  
                    $this->_tree->writeNodeParam($uid,'authToken',$token);                      
                    
                    return array('registered'=>true,'userId'=>$uid,'authToken'=>$token);
            }else{
                  
                    return $this->error('User account already exists', 400);
                 
            }
     }
     
	
	
          
      /**
     * @SWG\Post(
     *     path="/saveUser",
     *     summary="saves user account",
     *     operationId="saveUser",
     *     produces={"application/json"},
     *    @SWG\Parameter(
     *         name="saveUserObject",
     *         in="body",
     *         description="saveUserObject object",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/saveUserObject"),
     *     ),     
     *     @SWG\Response(response=200, description="Registration result")
     * )
     */
     
     
     public function saveUser($params,$data)
     {          
        
        $checkedData = $this->_commonObj->checkUserLoginAndEmail(null, $data['email']);
		
        $loged=$this->login(null,$data);
        
        if(!$loged['authorized'])
        {
           return $this->error('auth error', 500); 
        }
        
        if(!empty($checkedData['isEmail'])) {
            if($checkedData['isEmail']['id'] == $loged['user']['id']) {
                unset($checkedData['isEmail']);
            }
        }


        if(!empty($data['additionalFields'])) {
            foreach($this->_config['additionalFields'] as $k => $v) {
                if(!isset($data['additionalFields'][$v['fieldName']]) && $v['type'] == 'checkbox') {
                    $data['additionalFields'][$v['fieldName']] = '';
                }
            }
        }
           $data['params']['password'] = md5(strrev($data['params']['password']));
		   
        if (!$checkedData['isEmail']) {
            
            if ($this->_tree->reInitTreeObj($loged['user']['id'], '%SAME%', $data['params'])) {
            
                    if(!empty($data['additionalFields'])) {
                        
                        $this->_commonObj->initAdditionalFields($loged['user']['id'],$data['additionalFields']);
         
                    }
                    
                    return array('saved'=>true);
                    
            } else {
                  
                return $this->error('Internal error', 400);
                
            }
        } else {
             
            return $this->error('Email already exists', 400);
            
        }

     }
	 
	 
	   /**
     * @SWG\Get(
     *     path="/getUserById/id/{id}",
     *     summary="Gets user by Id",
     *     operationId="getUserById",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="user id",
     *         required=true
     *     ),
     *     @SWG\Response(response=200, description="gets user by id"),
     * )
     */
	 
	 public function getUserById($params)
	 {
		   if (isset($params['id'])) {
			   
			return $this->_commonObj->_tree->getNodeInfo($params['id']);	
		   
		   }else {

            return $this->error('Id is not provided', 400);
        }
	 }


  
}
