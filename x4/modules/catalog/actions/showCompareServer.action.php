<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showCompareServerAction extends xAction
    {
        
        public $result;
        
        public function __construct()
        {            
               parent::__construct('catalog');
               xNameSpaceHolder::addMethodsToNS('module.'.$this->_moduleName.'.xfront', array('addComparse','removeComparseItem','getComparseItems','getComparseCount'), $this);
            
        }
        
        public function run($params)
        {
            return $this->render($params);        
        }    
        
        
        public function addByUrl ($params)
        {
            if(!empty($params['request']['requestActionPath']))
            {
                $items=substr($params['request']['requestActionPath'],1);
                
                if(!empty($params['params']['delimeter'])){
                    
                    $delimeter=$params['params']['delimeter'];    
                    
                }else{
                    
                    $delimeter='-vs-';
                }
                
                
                $items=explode($delimeter,$items);
                
                $items=$this->_tree->selectStruct('*')->where(array('@basic','=',$items))->run();
                
                
                   
                if(!empty($items))
                {
                    
                    unset($_SESSION['catalog']['comparsedata']);
                    
                    foreach($items as $item){
                        
                        $_SESSION['catalog']['comparsedata'][$item['id']]=$item['id'];
                        
                    }    
                }
                
                
            }
            return $this->render($params);      
        }
        
        public function render($params)
        {        
            $this->loadModuleTemplate($params['params']['Template']);
            
            
            if(!empty($_SESSION['catalog']['comparsedata']))
            {
             
                reset($_SESSION['catalog']['comparsedata']);
                $element=current($_SESSION['catalog']['comparsedata']);   
                $element=$this->_tree->getNodeInfo($element);
                $PG = $this->_commonObj->getPropertyGroupSerialized($element['params']['PropertySetGroup']);
                   
                   
                $objects=$this->getObjectsByFilterInner(array('f'=>array('equal'=>array('@id'=>$_SESSION['catalog']['comparsedata']))),$params['params']['DestinationPage']);
				//DebugBreak();
                $cnt=count($objects); //обрезаем кол-во объектов до 5
                if ($cnt>4){
                    for($i=5;$i<$cnt;$i++)
                        unset($objects[$i]);
                }
                $this->setSeoData(array("compare"=>$objects));   
                if(!empty($objects))
                {
                  
                  $i=0;  
                  foreach($PG['sets'] as $setName=>$set)
                  {
                       
                       $outSet=array();
                       $PG['setsInfo'][$setName]['params']['name']=$PG['setsInfo'][$setName]['basic'];
                       $outSet['info']=$PG['setsInfo'][$setName]['params']; 
                                                               
                       foreach($set as $fieldName=>$field)
                       {
                           
                           if(!$field['params']['isComparse'])
                           {
                               $field['params']['name']=$field['basic'];
                               $outSet['items'][$fieldName]['field']=$field['params'];
                               
                                   foreach($objects as $object)
                                   {                  
                                                              
                                        $outSet['items'][$fieldName]['values'][]= $object[$setName][$fieldName];
                                   }
                           }
                     
                       }
                     
                     
                      $matrix[$i]=$outSet;
                      $i++;
                  }
                      
                }
                      
                $this->_TMS->addMassReplace('showCompareServer',array('count'=>count($objects),'objects'=>$objects,'compareData'=>$matrix));
                return  $this->_TMS->parseSection('showCompareServer');
                
            }else{ 
                //DebugBreak();
                $this->setSeoData(array("compare"=>"no")); 
                return  $this->_TMS->parseSection('emptyComparsion');
            }
            
        
        }
        
        public function remove($params)
        {
            
            if(isset($_GET['id']))unset($_SESSION['catalog']['comparsedata'][$_GET['id']]);
            return $this->render($params);
            
        }
     
     
        public function removeAll($params)
        {
                
            unset($_SESSION['catalog']['comparsedata']);
            return $this->render($params);    
            
        }
       
        
		
        //ajax call  

		public function removeComparseItem($params)
		{			
		
			unset($_SESSION['catalog']['comparsedata'][$params['id']]);			
						
			$this->getComparseCount($params);			
		}
		
		public function getComparseItems($params)
		{			
		
			$this->result['items']=$_SESSION['catalog']['comparsedata'];			
						
			
		}
		
		
  		//ajax call   
		public function getComparseCount($params)
		{		
			
			if(is_array($_SESSION['catalog']['comparsedata']))
			{
				$this->result['count']=count($_SESSION['catalog']['comparsedata']);				
			}else{
					$this->result['count']=0;
			}
		}
		 //ajax call   
         public function addComparse($params)
        {
                   	
                if (!isset($_SESSION['catalog']['comparsedata'])){$_SESSION['catalog']['comparsedata'] = array();}

                if (is_array($params['id'])) 
                {
                      foreach ($params['id'] as $id) 
                      {
                        $propertySetGroup = $this->_tree->readNodeParam($id, 'PropertySetGroup');
                      
                        $_SESSION['catalog']['comparsedata'][$id] = $id;
                    }
                    
                } else {
                       
		
                    $propertySetGroup = $this->_tree->readNodeParam($params['id'], 'PropertySetGroup');

                    if ($_SESSION['catalog']['comparselast'] != $propertySetGroup) 
                    {
                        $_SESSION['catalog']['comparselast'] = $propertySetGroup;
                        unset ($_SESSION['catalog']['comparsedata']);
                    }

                    $_SESSION['catalog']['comparsedata'][$params['id']] = $params['id'];

                    
					
                }
				
				$this->result['count']=count($_SESSION['catalog']['comparsedata']);
        }
             
                    
    }

?>

